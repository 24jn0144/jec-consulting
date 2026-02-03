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
            
            $logContent .= "--- FILE: $originalName ---\n";
            $logContent .= print_r($ocrText, true) . "\n\n";

            $parsedData = parseFamilyMartReceipt($ocrText);
            
            foreach ($parsedData['items'] as $item) {
                $stmt = $db->prepare('INSERT INTO items (filename, item_name, price, is_total, created_at) VALUES (:fname, :iname, :price, 0, datetime("now"))');
                $stmt->bindValue(':fname', $originalName);
                $stmt->bindValue(':iname', $item['name']);
                $stmt->bindValue(':price', $item['price']);
                $stmt->execute();

                $results[$originalName][] = $item;
                $csvData[] = [$originalName, $item['name'], $item['price']];
            }

            if ($parsedData['total'] > 0) {
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
    file_put_contents('ocr.log', $logContent, FILE_APPEND);
    $fp = fopen('output.csv', 'w');
    fwrite($fp, "\xEF\xBB\xBF");
    foreach ($csvData as $fields) { fputcsv($fp, $fields); }
    fclose($fp);
}

function callAzureOCR($url, $key, $imageData) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/octet-stream', 'Ocp-Apim-Subscription-Key: ' . $key]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $imageData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    $response = curl_exec($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $headerSize);
    curl_close($ch);
    if (!preg_match('/Operation-Location: (.*)\r\n/i', $header, $matches)) return ["error" => "no header"];
    $operationUrl = trim($matches[1]);
    for ($i = 0; $i < 15; $i++) {
        sleep(1);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $operationUrl);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Ocp-Apim-Subscription-Key: ' . $key]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = json_decode(curl_exec($ch), true);
        curl_close($ch);
        if ($res['status'] === 'succeeded') return $res;
    }
    return $res;
}

function parseFamilyMartReceipt($ocrResult) {
    $items = []; $total = 0;
    if (!isset($ocrResult['analyzeResult']['readResults'][0]['lines'])) return ['items' => [], 'total' => 0];
    $lines = $ocrResult['analyzeResult']['readResults'][0]['lines'];
    foreach ($lines as $line) {
        $text = $line['text'];
        $cleanText = str_replace(['軽', '*', '＊', '(', ')', '（', '）'], '', $text);
        $cleanText = trim($cleanText);
        if (preg_match('/合計/u', $cleanText)) {
            if (preg_match('/([0-9,]+)/', $cleanText, $matches)) {
                $total = (int)str_replace(',', '', $matches[1]);
            }
            continue;
        }
        if (preg_match('/(電話|登録番号|日時|レジ|領収証|対象|支払|残高|お釣り|ファミ|クーポン|％|個)/u', $cleanText)) continue;
        if (preg_match('/^(.+?)\s*¥?([0-9,]+)$/u', $cleanText, $matches)) {
            $name = trim($matches[1]);
            $price = (int)str_replace(',', '', $matches[2]);
            if (is_numeric($name) || mb_strlen($name) <= 1) continue;
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
    <title>解析結果</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <h2 class="mb-4">解析結果</h2>
        <?php if (empty($results)): ?>
            <div class="alert alert-warning">解析データがありません。もう一度アップロードしてください。</div>
        <?php else: ?>
            <?php foreach ($results as $filename => $fileItems): ?>
                <div class="card mb-4 shadow-sm">
                    <div class="card-header bg-success text-white">ファイル: <?php echo htmlspecialchars($filename); ?></div>
                    <div class="card-body">
                        <p class="lead">
                            <?php 
                            $formatted = [];
                            foreach ($fileItems as $item) {
                                $formatted[] = htmlspecialchars($item['name']) . "　¥" . number_format($item['price']);
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
