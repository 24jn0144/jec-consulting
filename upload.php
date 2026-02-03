<?php
// ========== 設定エリア (ここを自分のAzure情報に書き換える) ==========
$apiKey = '7Wi4PFuB4y7tsXlZ8IgvlrlD9984eiOmhVBszZJe3Vac0zQr5equJQQJ99CBACi0881XJ3w3AAAFACOGD4zv'; 
$endpoint = 'https://24jn0144vision.cognitiveservices.azure.com/'; 
// 例: https://24jn0144vision.cognitiveservices.azure.com/
// ================================================================

// エラー表示設定
ini_set('display_errors', 1);
error_reporting(E_ALL);

// エンドポイントの調整 (OCR機能のURL)
// 最後にスラッシュがあれば削除し、APIパスを追加
$endpoint = rtrim($endpoint, '/') . '/vision/v3.2/read/analyze';

// データベース接続 (SQLiteを使用 - ファイルベースなので設定不要で無料)
$db = new SQLite3('receipts.db');
$db->exec("CREATE TABLE IF NOT EXISTS items (id INTEGER PRIMARY KEY AUTOINCREMENT, filename TEXT, item_name TEXT, price INTEGER, is_total INTEGER, created_at DATETIME)");

$results = [];
$csvData = [];
$logContent = "";

// CSVのヘッダー
$csvData[] = ['ファイル名', '商品名/項目', '金額'];

// POSTリクエスト処理
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['receipts'])) {
    
    $files = $_FILES['receipts'];
    $fileCount = count($files['name']);

    for ($i = 0; $i < $fileCount; $i++) {
        if ($files['error'][$i] === UPLOAD_ERR_OK) {
            $tmpName = $files['tmp_name'][$i];
            $originalName = $files['name'][$i];
            
            // 1. 画像を読み込んでバイナリデータにする
            $imageData = file_get_contents($tmpName);

            // 2. Azure AI Vision APIを呼び出す
            $ocrText = callAzureOCR($endpoint, $apiKey, $imageData);
            
            // ログ用に生データを保存
            $logContent .= "--- FILE: $originalName ---\n";
            $logContent .= print_r($ocrText, true) . "\n\n";

            // 3. 解析処理（ファミリーマート特化）
            $parsedData = parseFamilyMartReceipt($ocrText);
            
            // 結果を保存・表示用に格納
            foreach ($parsedData['items'] as $item) {
                // DB保存
                $stmt = $db->prepare('INSERT INTO items (filename, item_name, price, is_total, created_at) VALUES (:fname, :iname, :price, 0, datetime("now"))');
                $stmt->bindValue(':fname', $originalName);
                $stmt->bindValue(':iname', $item['name']);
                $stmt->bindValue(':price', $item['price']);
                $stmt->execute();

                // 表示・CSV用
                $results[$originalName][] = $item;
                $csvData[] = [$originalName, $item['name'], $item['price']];
            }

            if ($parsedData['total']) {
                // 合計もDB保存
                $stmt = $db->prepare('INSERT INTO items (filename, item_name, price, is_total, created_at) VALUES (:fname, :iname, :price, 1, datetime("now"))');
                $stmt->bindValue(':fname', $originalName);
                $stmt->bindValue(':iname', '合計');
                $stmt->bindValue(':price', $parsedData['total']);
                $stmt->execute();

                $results[$originalName][] = ['name' => '合計', 'price' => $parsedData['total'], 'is_total' => true];
                $csvData[] = [$originalName, '合計', $parsedData['total']];
            }
        }
    }

    // 4. ログファイルへの書き出し
    file_put_contents('ocr.log', $logContent, FILE_APPEND);

    // 5. CSVファイルの生成
    $fp = fopen('output.csv', 'w');
    // BOMをつけてExcelで文字化けしないようにする
    fwrite($fp, "\xEF\xBB\xBF");
    foreach ($csvData as $fields) {
        fputcsv($fp, $fields);
    }
    fclose($fp);
}

// --- 関数定義エリア ---

function callAzureOCR($url, $key, $imageData) {
    // 1. Analyzeリクエスト (POST)
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/octet-stream',
        'Ocp-Apim-Subscription-Key: ' . $key
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $imageData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true); // ヘッダーも取得（Operation-Locationが必要）
    
    $response = curl_exec($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $headerSize);
    curl_close($ch);

    // Operation-Locationを取得
    if (!preg_match('/Operation-Location: (.*)\r\n/i', $header, $matches)) {
        return "Error: Operation-Location not found.";
    }
    $operationUrl = trim($matches[1]);

    // 2. 結果取得リクエスト (GET) - 処理完了まで待機
    $maxRetries = 10;
    $result = null;
    
    for ($i = 0; $i < $maxRetries; $i++) {
        sleep(2); // 少し待つ
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $operationUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Ocp-Apim-Subscription-Key: ' . $key
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $jsonResponse = curl_exec($ch);
        curl_close($ch);
        
        $data = json_decode($jsonResponse, true);
        if ($data['status'] === 'succeeded') {
            return $data;
        } elseif ($data['status'] === 'failed') {
            return "Error: Analysis failed.";
        }
    }
    return "Error: Timeout.";
}

function parseFamilyMartReceipt($ocrResult) {
    $items = [];
    $total = 0;
    
    if (!isset($ocrResult['analyzeResult']['readResults'][0]['lines'])) {
        return ['items' => [], 'total' => 0];
    }

    $lines = $ocrResult['analyzeResult']['readResults'][0]['lines'];
    
    foreach ($lines as $line) {
        $text = $line['text'];
        
        // ファミリーマートのレシート解析ロジック
        
        // 1. 合計金額の抽出 ("合 計" や "合計" を含む行)
        if (preg_match('/合\s*計/u', $text)) {
            // 数字だけ抜き出す (カンマ除去)
            if (preg_match('/([0-9,]+)/', $text, $matches)) {
                $total = (int)str_replace(',', '', $matches[1]);
            }
            continue; // 合計行は商品リストに入れない
        }

        // 2. 商品の抽出
        // 除外ワード（レシートのヘッダーやフッター）
        if (preg_match('/(電話|登録番号|日時|レジ|領収証|対象|支払|残高|お釣り|ファミ|クーポン)/u', $text)) {
            continue;
        }

        // 価格が含まれているかチェック ("¥" または 数字+軽 など)
        // 商品名は左側、価格は右側にあることが多いが、OCRでは1行で取れることが多い
        // パターン: [商品名] [価格][軽?]
        // 例: "ザバスプロテインフルー ¥247軽"
        
        if (preg_match('/(.*?)\s*¥?([0-9,]+)(軽)?$/u', $text, $matches)) {
            $name = trim($matches[1]);
            $priceStr = str_replace(',', '', $matches[2]);
            $price = (int)$priceStr;

            // 商品名が空、または数字だけの行は除外
            if (empty($name) || is_numeric($name)) continue;
            // 記号だけのゴミ除外
            if (mb_strlen($name) < 2) continue;

            $items[] = ['name' => $name, 'price' => $price];
        }
    }

    return ['items' => $items, 'total' => $total];
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>解析結果</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <h2 class="mb-4">解析結果</h2>
        
        <?php foreach ($results as $filename => $fileItems): ?>
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-success text-white">
                    ファイル名: <?php echo htmlspecialchars($filename); ?>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <?php foreach ($fileItems as $item): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center <?php echo isset($item['is_total']) ? 'fw-bold bg-light' : ''; ?>">
                                <?php echo htmlspecialchars($item['name']); ?>
                                <span class="badge bg-primary rounded-pill">¥<?php echo number_format($item['price']); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="mt-4 row">
            <div class="col-md-6">
                <a href="output.csv" class="btn btn-success w-100 btn-lg" download>CSVダウンロード</a>
            </div>
            <div class="col-md-6">
                <a href="ocr.log" class="btn btn-secondary w-100 btn-lg" download>OCRログ(ocr.log)確認</a>
            </div>
        </div>
        <div class="mt-3 text-center">
            <a href="index.php">戻る</a>
        </div>
    </div>
</body>
</html>