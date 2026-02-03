<?php
// ========== 設定エリア ==========
$apiKey = '7Wi4PFuB4y7tsXlZ8IgvlrlD9984eiOmhVBszZJe3Vac0zQr5equJQQJ99CBACi0881XJ3w3AAAFACOGD4zv';
$endpoint = 'https://24jn0144vision.cognitiveservices.azure.com/';
// ===============================

ini_set('display_errors', 1);
error_reporting(E_ALL);

$endpointUrl = rtrim($endpoint, '/') . '/vision/v3.2/read/analyze';

$db = new SQLite3('receipts.db');
$db->exec("CREATE TABLE IF NOT EXISTS items (id INTEGER PRIMARY KEY AUTOINCREMENT, filename TEXT, item_name TEXT, price INTEGER, is_total INTEGER, created_at DATETIME)");

$results = [];
$csvData = [];
$csvData[] = ['ファイル名', '商品名/項目', '金額'];
$logContent = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['receipts'])) {
    $files = $_FILES['receipts'];
    $fileCount = count($files['name']);

    for ($i = 0; $i < $fileCount; $i++) {
        if ($files['error'][$i] === UPLOAD_ERR_OK) {
            $tmpName = $files['tmp_name'][$i];
            $originalName = $files['name'][$i];
            $imageData = file_get_contents($tmpName);

            $ocrText = callAzureOCR($endpointUrl, $apiKey, $imageData);
            $logContent .= "--- FILE: $originalName ---\n" . print_r($ocrText, true) . "\n\n";

            $parsedData = parseFamilyMartReceipt($ocrText);
            
            foreach ($parsedData['items'] as $item) {
                $stmt = $db->prepare('INSERT INTO items (filename, item_name, price, is_total, created_at) VALUES (:fname, :iname, :price, 0, datetime("now"))');
                $stmt->bindValue(':fname', $originalName); $stmt->bindValue(':iname', $item['name']); $stmt->bindValue(':price', $item['price']);
                $stmt->execute();
                $results[$originalName]['items'][] = $item;
                $csvData[] = [$originalName, $item['name'], $item['price']];
            }

            if ($parsedData['total'] > 0) {
                $stmt = $db->prepare('INSERT INTO items (filename, item_name, price, is_total, created_at) VALUES (:fname, :iname, :price, 1, datetime("now"))');
                $stmt->bindValue(':fname', $originalName); $stmt->bindValue(':iname', '合計'); $stmt->bindValue(':price', $parsedData['total']);
                $stmt->execute();
                $results[$originalName]['total'] = $parsedData['total'];
                $csvData[] = [$originalName, '合計', $parsedData['total']];
            }
        }
    }
    file_put_contents('ocr.log', $logContent, FILE_APPEND);
    $fp = fopen('output.csv', 'w'); fwrite($fp, "\xEF\xBB\xBF");
    foreach ($csvData as $fields) { fputcsv($fp, $fields); }
    fclose($fp);
}

function callAzureOCR($url, $key, $imageData) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url); curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/octet-stream', 'Ocp-Apim-Subscription-Key: ' . $key]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $imageData); curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); curl_setopt($ch, CURLOPT_HEADER, true);
    $response = curl_exec($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $headerSize);
    curl_close($ch);
    if (!preg_match('/Operation-Location: (.*)\r\n/i', $header, $matches)) return ["error" => "no header"];
    $operationUrl = trim($matches[1]);
    for ($i = 0; $i < 15; $i++) {
        sleep(1);
        $ch = curl_init(); curl_setopt($ch, CURLOPT_URL, $operationUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Ocp-Apim-Subscription-Key: ' . $key]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = json_decode(curl_exec($ch), true); curl_close($ch);
        if ($res['status'] === 'succeeded') return $res;
    }
    return $res;
}

function parseFamilyMartReceipt($ocrResult) {
    $items = []; $total = 0;
    if (!isset($ocrResult['analyzeResult']['readResults'][0]['lines'])) return ['items' => [], 'total' => 0];
    $lines = $ocrResult['analyzeResult']['readResults'][0]['lines'];
    
    $currentName = ""; 
    $findTotalNext = false; // 「合計」の次の行に数字があるか監視するフラグ

    foreach ($lines as $line) {
        $text = trim($line['text']);

        // --- A. 合計金額の抽出 (超執念Ver) ---
        // 1. 「合計」という文字自体が含まれているか
        if (preg_match('/合\s*計/u', $text)) {
            // 同じ行に数字があればそれを合計にする
            if (preg_match('/([0-9,]{2,})/', $text, $m)) {
                $total = (int)str_replace(',', '', $m[1]);
            } else {
                // 同じ行になければ「次の行に数字があるはず」とフラグを立てる
                $findTotalNext = true;
            }
            continue; 
        }

        // 2. 「合計」のすぐ後に現れた数字を合計として捕まえる
        if ($findTotalNext && preg_match('/^[¥＊\*]*\s*([0-9,]{2,})$/', $text, $m)) {
            $total = (int)str_replace(',', '', $m[1]);
            $findTotalNext = false; // 捕まえたらフラグを下ろす
            continue;
        }

        // --- B. ゴミ排除リスト ---
        if (preg_match('/(東京都|新宿区|北新宿|FamilyMart|年|月|日|:[0-9]{2}|領収|店|責No|番号|レジ|電話|T[0-9]{10}|お買上|証|マネー|支払|残高|クレジット|現金|お釣り|対象|消費税|thefamhay)/ui', $text)) {
            $currentName = ""; 
            continue;
        }

        // --- C. 商品名と価格の検知 ---
        if (preg_match('/([*¥＊])\s*([0-9,]+)/u', $text, $m)) {
            $price = (int)str_replace(',', '', $m[2]);
            $parts = explode($m[1], $text);
            $extraName = trim($parts[0]);
            $fullName = $currentName . $extraName;

            $name = str_replace(['(軽)', '軽', '*', '¥', '＊', '(', ')', '（', '）'], '', $fullName);
            $name = trim($name);

            if (mb_strlen($name) >= 2 && !preg_match('/^[0-9\-]+$/', $name)) {
                $items[] = ['name' => $name, 'price' => $price];
            }
            $currentName = ""; 
        } 
        elseif (!preg_match('/^[¥\*・\s0-9\-]+$/', $text) && mb_strlen($text) > 1) {
            $currentName .= $text;
        }
    }
    
    // 【最終手段】もし「合計」が見つからなかったら、商品の単価ではない「一番下の大きな数字」を合計にする
    if ($total == 0 && !empty($items)) {
        // OCR結果を逆から辿って、最初に見つけた3桁以上の数字を拾う（保険）
        for ($i = count($lines) - 1; $i >= 0; $i--) {
            if (preg_match('/([0-9,]{3,})/', $lines[$i]['text'], $m)) {
                $total = (int)str_replace(',', '', $m[1]);
                break;
            }
        }
    }

    return ['items' => $items, 'total' => $total];
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>解析結果</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <h2 class="mb-4">解析結果</h2>
        <?php if (empty($results)): ?>
            <div class="alert alert-warning">解析データがありません。もう一度アップロードしてください。</div>
        <?php else: ?>
            <?php foreach ($results as $filename => $data): ?>
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-success text-white">ファイル: <?php echo htmlspecialchars($filename); ?></div>
                    <div class="card-body">
                        <p class="lead">
                            <?php 
                            $formatted = [];
                            if (!empty($data['items'])) {
                                foreach ($data['items'] as $item) {
                                    $formatted[] = htmlspecialchars($item['name']) . "　¥" . number_format($item['price']);
                                }
                            }
                            if (isset($data['total']) && $data['total'] > 0) {
                                $formatted[] = "合計　¥" . number_format($data['total']);
                            }
                            echo implode(", ", $formatted); 
                            ?>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <div class="mt-4 row">
            <div class="col-md-6"><a href="output.csv" class="btn btn-success w-100" download>CSVダウンロード</a></div>
            <div class="col-md-6"><a href="ocr.log" class="btn btn-secondary w-100" download>ocr.logを表示</a></div>
        </div>
        <div class="mt-3 text-center"><a href="index.php">戻る</a></div>
    </div>
</body>
</html>

