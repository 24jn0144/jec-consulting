<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>株式会社Jecコンサルティング | 未来を創造するパートナー</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* ベーススタイル */
        body {
            font-family: 'Noto Sans JP', sans-serif;
            color: #333;
            line-height: 1.8;
            overflow-x: hidden;
        }

        /* ナビゲーション */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .navbar-brand {
            font-weight: 700;
            color: #0d6efd !important;
            font-size: 1.5rem;
        }

        /* ヒーローセクション */
        .hero {
            position: relative;
            height: 100vh;
            background: linear-gradient(135deg, #0d6efd 0%, #000000 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            clip-path: polygon(0 0, 100% 0, 100% 90%, 0 100%);
        }
        .hero h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 0 4px 10px rgba(0,0,0,0.3);
            animation: fadeInDown 1.5s ease;
        }
        .hero p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
            animation: fadeInUp 1.5s ease;
        }
        .btn-custom {
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: bold;
            transition: all 0.3s;
            animation: fadeInUp 2s ease;
        }
        .btn-primary-custom {
            background: white;
            color: #0d6efd;
            border: none;
        }
        .btn-primary-custom:hover {
            background: #f8f9fa;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }

        /* セクション共通 */
        section {
            padding: 80px 0;
        }
        .section-title {
            text-align: center;
            margin-bottom: 50px;
            font-weight: 700;
            position: relative;
        }
        .section-title::after {
            content: '';
            display: block;
            width: 60px;
            height: 3px;
            background: #0d6efd;
            margin: 15px auto 0;
        }

        /* サービスカード */
        .service-card {
            border: none;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            background: white;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            transition: all 0.3s;
            height: 100%;
        }
        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        .service-icon {
            font-size: 3rem;
            color: #0d6efd;
            margin-bottom: 20px;
        }

        /* お問い合わせフォーム */
        .contact-section {
            background: #f8f9fa;
        }
        .form-control {
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.25);
            border-color: #0d6efd;
        }

        /* フッター */
        footer {
            background: #212529;
            color: white;
            padding: 30px 0;
            text-align: center;
        }

        /* アニメーション定義 */
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* スマホ調整 */
        @media (max-width: 768px) {
            .hero h1 { font-size: 2rem; }
            .hero { clip-path: polygon(0 0, 100% 0, 100% 95%, 0 100%); }
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#"><i class="fas fa-chart-line me-2"></i>Jec Consulting</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="#home">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <header id="home" class="hero">
        <div class="container">
            <h1>ビジネスの限界を突破する</h1>
            <p>株式会社Jecコンサルティングは、あなたのビジョンを現実にする戦略的パートナーです。<br>最新のテクノロジーと確かな知見で、確実な成長を支援します。</p>
            <a href="#contact" class="btn btn-primary-custom btn-custom">無料相談を申し込む</a>
        </div>
    </header>

    <section id="about">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Meeting" class="img-fluid rounded shadow">
                </div>
                <div class="col-lg-6">
                    <h2 class="section-title text-start ms-lg-4" style="margin-left: 0!important;">Why Choose Us?</h2>
                    <p class="lead ms-lg-4">「結果」にコミットする、プロフェッショナル集団。</p>
                    <p class="ms-lg-4">私たちJecコンサルティングは、単なる助言者ではありません。お客様のチームの一員として課題に向き合い、IT導入から経営戦略の立案まで、包括的なソリューションを提供します。変わりゆく市場環境の中で、常に最適解を導き出します。</p>
                </div>
            </div>
        </div>
    </section>

    <section id="services" class="bg-light">
        <div class="container">
            <h2 class="section-title">Our Services</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="service-card">
                        <i class="fas fa-laptop-code service-icon"></i>
                        <h4>IT・DX支援</h4>
                        <p>最新のデジタルツール導入により、業務効率化と生産性向上を実現します。</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="service-card">
                        <i class="fas fa-chart-pie service-icon"></i>
                        <h4>経営戦略コンサル</h4>
                        <p>データに基づいた市場分析と戦略立案で、競合優位性を確立します。</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="service-card">
                        <i class="fas fa-users service-icon"></i>
                        <h4>人材育成・研修</h4>
                        <p>次世代のリーダーを育成し、組織全体のパフォーマンスを底上げします。</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="contact" class="contact-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h2 class="section-title">Contact Us</h2>
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-5">
                            <form onsubmit="return submitForm(event)">
                                <div class="mb-3">
                                    <label for="name" class="form-label">お名前</label>
                                    <input type="text" class="form-control" id="name" placeholder="例：山田 太郎" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">メールアドレス</label>
                                    <input type="email" class="form-control" id="email" placeholder="name@example.com" required>
                                </div>
                                <div class="mb-3">
                                    <label for="message" class="form-label">お問い合わせ内容</label>
                                    <textarea class="form-control" id="message" rows="5" required></textarea>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">送信する</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <p class="mb-0">&copy; 2024 Jec Consulting Co., Ltd. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // フォーム送信時の擬似的な動作
        function submitForm(event) {
            event.preventDefault(); // 実際の送信を防ぐ
            const name = document.getElementById('name').value;
            alert(`${name}様、お問い合わせありがとうございます。\n（※これはデモ画面のため、実際には送信されません）`);
            document.querySelector('form').reset();
        }
    </script>
</body>
</html>