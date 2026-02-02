<?php
// エラーを表示する設定（デバッグ用）
ini_set('display_errors', 1);
error_reporting(E_ALL);

$success = false;

// POSTリクエストが来た場合のみ処理を実行
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // データの取得とサニタイズ（安全対策）
    $name = htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars($_POST['email'], ENT_QUOTES, 'UTF-8');
    $message = htmlspecialchars($_POST['message'], ENT_QUOTES, 'UTF-8');
    $date = date("Y/m/d H:i:s");

    // CSVファイルのパス
    $filename = 'contacts.csv';

    // 配列にまとめる
    $data = [$date, $name, $email, $message];

    // ファイルを追記モードで開く
    $fp = fopen($filename, 'a');
    
    // ファイルロック（同時書き込み防止）
    if (flock($fp, LOCK_EX)) {
        // 日本語（Excel）用にBOMをつけるかどうかは環境によりますが、今回は標準的なCSV書き込み
        // Excelで文字化けしないようSJIS変換する場合はmb_convert_encodingを使いますが、
        // Azure環境(Linux)を想定しUTF-8で保存します。
        fputcsv($fp, $data);
        flock($fp, LOCK_UN);
        $success = true;
    }
    fclose($fp);
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>送信完了 | 株式会社Jecコンサルティング</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .card { border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .icon-box { width: 80px; height: 80px; background: #d1e7dd; color: #0f5132; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 40px; margin: 0 auto 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card p-5 text-center">
                    <?php if ($success): ?>
                        <div class="icon-box"><i class="fas fa-check"></i></div>
                        <h2 class="mb-3">送信完了</h2>
                        <p class="text-muted mb-4"><?php echo $name; ?>様、お問い合わせありがとうございます。<br>担当者より折り返しご連絡いたします。</p>
                    <?php else: ?>
                         <h2 class="mb-3 text-danger">送信エラー</h2>
                         <p class="text-muted mb-4">申し訳ありません。送信中にエラーが発生しました。</p>
                    <?php endif; ?>
                    <a href="website.php" class="btn btn-primary w-100">トップページに戻る</a>
                </div>
            </div>
        </div>
    </div>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</body>

</html>

