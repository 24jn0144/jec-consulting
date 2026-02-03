<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>レシートOCR解析システム</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .link-section {
            border-top: 1px solid #dee2e6;
            padding-top: 20px;
            margin-top: 20px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <h1 class="text-center mb-4">レシート自動読み取りシステム</h1>
        <div class="card shadow-sm">
            <div class="card-body p-5">
                <form action="upload.php" method="post" enctype="multipart/form-data">
                    <div class="mb-4">
                        <label for="receipts" class="form-label">ファミリーマートのレシートを選択（複数可）</label>
                        <input type="file" class="form-control" id="receipts" name="receipts[]" multiple accept="image/*" required>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">解析開始</button>
                    </div>
                </form>

                <div class="link-section text-center">
                    <p class="text-muted mb-2 small">関連メニュー</p>
                    <a href="website.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left-circle"></i> 課題13-1, 2 (website.php) へ戻る
                    </a>
                </div>
            </div>
        </div>
        
        <div class="mt-4 text-center">
            <p>※Azure AI Visionを使用しています。</p>
        </div>
    </div>
</body>
</html>
