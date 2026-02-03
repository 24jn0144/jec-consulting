<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>レシートOCR解析システム</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
            </div>
        </div>
        
        <div class="mt-4 text-center">
            <p>※Azure AI Visionを使用しています。</p>
        </div>
    </div>
</body>
</html>