<?php
session_start(); // Oturumu başlat

// Veritabanı bağlantı bilgileri
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fitness_db";

// Veritabanı bağlantısı
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

// Hedeflerine ulaşan kullanıcıları al
$success_stories = [];
$success_query = "SELECT username, target_set_date, target_achieved_date 
                 FROM users 
                 WHERE target_weight IS NOT NULL 
                 AND target_achieved_date IS NOT NULL 
                 AND weight = target_weight 
                 ORDER BY target_achieved_date DESC LIMIT 5";
$success_result = $conn->query($success_query);

if ($success_result->num_rows > 0) {
    while ($row = $success_result->fetch_assoc()) {
        $success_stories[] = [
            'username' => $row['username'],
            'target_set_date' => $row['target_set_date'],
            'target_achieved_date' => $row['target_achieved_date']
        ];
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitMate - Anasayfa</title>
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- AOS Animasyon Kütüphanesi -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Google Fonts (Modern bir font için) -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <!-- Font Awesome (Simgeler için) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Genel Stil */
        html, body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        body {
            display: flex;
            flex-direction: column;
        }

        .content {
            flex: 1 0 auto;
            padding-bottom: 60px;
        }

        footer {
            flex-shrink: 0;
            width: 100%;
        }

        /* Navbar */
        .navbar {
            background: linear-gradient(90deg, #1a3c34 0%, #2a5d53 100%);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            padding: 10px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-logo {
            height: 60px;
            width: auto;
            max-width: 250px;
            transition: transform 0.3s ease;
        }

        .navbar-logo:hover {
            transform: scale(1.05);
        }

        .navbar-brand {
            padding: 5px 15px;
            display: flex;
            align-items: center;
        }

        .nav-link {
            color: #fff !important;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .nav-link:hover {
            color: #00ddeb !important;
        }

        /* Hero Section */
        .hero-section {
            background: url('https://images.unsplash.com/photo-1517836357463-d25dfeac3438') no-repeat center center;
            background-size: cover;
            color: white;
            text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.7);
            padding: 120px 0;
            position: relative;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
        }

        .hero-content {
            position: relative;
            z-index: 1;
        }

        .btn-primary {
            background: #00ddeb;
            border: none;
            padding: 12px 30px;
            font-size: 1.1rem;
            font-weight: 500;
            border-radius: 50px;
            transition: transform 0.3s ease, background 0.3s ease;
        }

        .btn-primary:hover {
            background: #00b7c2;
            transform: translateY(-3px);
        }

        /* Özellikler Bölümü */
        .features-section {
            padding: 60px 0;
            background: #fff;
        }

        .feature-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            background: linear-gradient(135deg, #e0f7fa 0%, #b2ebf2 100%);
            padding: 20px;
            transition: transform 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
        }

        .feature-card i {
            font-size: 2.5rem;
            color: #00ddeb;
        }

        /* Hakkımızda Bölümü */
        .about-section {
            padding: 60px 0;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }

        .about-section img {
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            max-width: 100%;
            height: auto;
        }

        /* Kullanıcı Yorumları Bölümü */
        .testimonials-section {
            padding: 60px 0;
            background: #fff;
        }

        .testimonial-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            background: linear-gradient(135deg, #e0f7fa 0%, #b2ebf2 100%);
            padding: 20px;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .testimonial-card:hover {
            transform: translateY(-5px);
        }

        .testimonial-card img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
        }

        .testimonial-card p {
            font-style: italic;
            color: #1a3c34;
        }

        .testimonial-card h5 {
            color: #1a3c34;
            font-weight: 600;
        }

        /* Başarı Hikayeleri Bölümü */
        .success-stories-section {
            padding: 60px 0;
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        }

        .success-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            background: #fff;
            padding: 20px;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .success-card:hover {
            transform: translateY(-5px);
        }

        .success-card i {
            font-size: 2rem;
            color: #00ddeb;
        }

        .success-card h5 {
            color: #1a3c34;
            font-weight: 600;
        }

        /* Blog/İpuçları Bölümü */
        .blog-section {
            padding: 60px 0;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }

        .blog-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            background: #fff;
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .blog-card:hover {
            transform: translateY(-5px);
        }

        .blog-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .blog-card .card-body {
            padding: 20px;
        }

        .blog-card .card-title {
            color: #1a3c34;
            font-weight: 600;
        }

        .blog-card .btn {
            background: #00ddeb;
            border: none;
            border-radius: 50px;
            padding: 8px 20px;
            transition: background 0.3s ease;
        }

        .blog-card .btn:hover {
            background: #00b7c2;
        }

        /* CTA Bölümü */
        .cta-section {
            padding: 60px 0;
            background: linear-gradient(90deg, #1a3c34 0%, #2a5d53 100%);
            color: white;
            text-align: center;
        }

        .cta-section h2 {
            font-weight: 600;
        }

        /* Footer */
        footer {
            background: linear-gradient(90deg, #1a3c34 0%, #2a5d53 100%);
            color: white;
            padding: 20px 0;
            font-size: 0.9rem;
        }

        footer a {
            color: #00ddeb;
            text-decoration: none;
        }

        footer a:hover {
            text-decoration: underline;
        }

        /* Responsive Ayarlar */
        @media (max-width: 768px) {
            .navbar-logo {
                height: 50px;
                max-width: 200px;
            }

            .hero-section {
                padding: 80px 0;
            }

            .features-section, .about-section, .testimonials-section, .success-stories-section, .blog-section, .cta-section {
                padding: 40px 0;
            }

            .feature-card, .testimonial-card, .success-card, .blog-card {
                margin-bottom: 20px;
            }

            .about-section img {
                margin-bottom: 20px;
            }
        }

        @media (max-width: 576px) {
            .navbar-logo {
                height: 40px;
                max-width: 150px;
            }

            .hero-section {
                padding: 60px 0;
            }

            .hero-content h1 {
                font-size: 2rem;
            }

            .features-section, .about-section, .testimonials-section, .success-stories-section, .blog-section, .cta-section {
                padding: 20px 0;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <img src="images/logo2.png" alt="Fitness App Logo" class="navbar-logo">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Anasayfa</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['username'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">Hoş Geldin, <?php echo $_SESSION['username']; ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Çıkış Yap</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="register.html">Kayıt Ol</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Giriş Yap</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- İçerik -->
    <div class="content">
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="container hero-content text-center">
                <h1 class="display-4" data-aos="fade-up">FitMate ile Hedeflerine Ulaş!</h1>
                <p class="lead" data-aos="fade-up" data-aos-delay="100">Kişiselleştirilmiş fitness programınla sağlıklı bir yaşama adım at.</p>
                <?php if (!isset($_SESSION['username'])): ?>
                    <a href="register.html" class="btn btn-primary mt-3" data-aos="fade-up" data-aos-delay="200">Hemen Kayıt Ol</a>
                <?php else: ?>
                    <a href="dashboard.php" class="btn btn-primary mt-3" data-aos="fade-up" data-aos-delay="200">Dashboard'a Git</a>
                <?php endif; ?>
            </div>
        </section>

        <!-- Özellikler Bölümü -->
        <section class="features-section">
            <div class="container">
                <h2 class="text-center mb-5" data-aos="fade-up">Neden FitMate?</h2>
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="card feature-card text-center" data-aos="fade-up" data-aos-delay="100">
                            <div class="card-body">
                                <i class="fas fa-dumbbell mb-3"></i>
                                <h5 class="card-title">Kişiselleştirilmiş Programlar</h5>
                                <p class="card-text">Hedeflerine uygun antrenman ve beslenme planları.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card feature-card text-center" data-aos="fade-up" data-aos-delay="200">
                            <div class="card-body">
                                <i class="fas fa-chart-line mb-3"></i>
                                <h5 class="card-title">İlerleme Takibi</h5>
                                <p class="card-text">Gelişiminizi kolayca takip edin ve motive olun.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card feature-card text-center" data-aos="fade-up" data-aos-delay="300">
                            <div class="card-body">
                                <i class="fas fa-users mb-3"></i>
                                <h5 class="card-title">Topluluk Desteği</h5>
                                <p class="card-text">FitMate topluluğuyla birlikte hedeflerinize ulaşın.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Hakkımızda Bölümü -->
        <section class="about-section">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-md-6 mb-4" data-aos="fade-right">
                        <img src="images/hakkimizda.png" alt="Hakkımızda Görseli">
                    </div>
                    <div class="col-md-6" data-aos="fade-left">
                        <h2 class="mb-4">Biz Kimiz?</h2>
                        <p>FitMate olarak, herkesin sağlıklı ve aktif bir yaşam sürmesine yardımcı olmayı hedefliyoruz. Kişiselleştirilmiş fitness programlarımız ve destekleyici topluluğumuzla, hedeflerinize ulaşmanız için yanınızdayız.</p>
                        <p>Misyonumuz, fitness yolculuğunuzu kolaylaştırmak ve sizi motive etmek. Haydi, birlikte sağlıklı bir geleceğe adım atalım!</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Kullanıcı Yorumları Bölümü -->
        <section class="testimonials-section">
            <div class="container">
                <h2 class="text-center mb-5" data-aos="fade-up">Kullanıcılarımız Ne Diyor?</h2>
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="card testimonial-card" data-aos="fade-up" data-aos-delay="100">
                            <div class="card-body">
                                <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330" alt="Kullanıcı 1">
                                <p>"FitMate sayesinde 3 ayda hedef kiloma ulaştım! Programlar çok etkili ve motive edici."</p>
                                <h5>Ayşe K.</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card testimonial-card" data-aos="fade-up" data-aos-delay="200">
                            <div class="card-body">
                                <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e" alt="Kullanıcı 2">
                                <p>"Topluluk desteği harika! Her zaman motive oluyorum ve antrenmanlarım çok daha keyifli."</p>
                                <h5>Mehmet Y.</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card testimonial-card" data-aos="fade-up" data-aos-delay="300">
                            <div class="card-body">
                                <img src="https://images.unsplash.com/photo-1488426862026-3ee34a7d66df" alt="Kullanıcı 3">
                                <p>"FitMate ile fitness hedeflerime ulaşmak çok kolay oldu. Kesinlikle tavsiye ederim!"</p>
                                <h5>Elif S.</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Başarı Hikayeleri Bölümü -->
        <section class="success-stories-section">
            <div class="container">
                <h2 class="text-center mb-5" data-aos="fade-up">Başarı Hikayeleri</h2>
                <div class="row">
                    <?php if (count($success_stories) > 0): ?>
                        <?php foreach ($success_stories as $story): ?>
                            <?php
                            // Hedefe ulaşma süresini hesapla
                            $set_date = new DateTime($story['target_set_date']);
                            $achieved_date = new DateTime($story['target_achieved_date']);
                            $interval = $set_date->diff($achieved_date);
                            $days = $interval->days;
                            ?>
                            <div class="col-md-4 mb-4">
                                <div class="card success-card" data-aos="fade-up">
                                    <div class="card-body">
                                        <i class="fas fa-trophy mb-3"></i>
                                        <h5 class="card-title"><?php echo htmlspecialchars($story['username']); ?></h5>
                                        <p class="card-text">
                                            Hedefine <strong><?php echo $days; ?> gün</strong> içinde ulaştı! 🎉
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12 text-center">
                            <p data-aos="fade-up">Henüz hedeflerine ulaşan kullanıcı yok. İlk olmak ister misin? 🚀</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Blog/İpuçları Bölümü -->
        <section class="blog-section">
            <div class="container">
                <h2 class="text-center mb-5" data-aos="fade-up">Fitness İpuçları ve Blog</h2>
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="card blog-card" data-aos="fade-up" data-aos-delay="100">
                            <img src="https://blog.korayspor.com/wp-content/uploads/2023/08/Sabit-Bisiklet-Yag-Yakar-Mi.jpg" alt="Blog 1">
                            <div class="card-body">
                                <h5 class="card-title">Kardiyo ile Kilo Verme Rehberi</h5>
                                <p class="card-text">Kardiyo egzersizleriyle nasıl daha hızlı kilo verebileceğinizi öğrenin.</p>
                                <a href="#" class="btn">Devamını Oku</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card blog-card" data-aos="fade-up" data-aos-delay="200">
                            <img src="https://www.macfit.com/wp-content/uploads/2023/01/gogus-buyutme-yontemleri.jpg" alt="Blog 2">
                            <div class="card-body">
                                <h5 class="card-title">Kas Kütlesi Artırmanın 5 Yolu</h5>
                                <p class="card-text">Kuvvet antrenmanlarıyla kas kütlenizi nasıl artıracağınızı keşfedin.</p>
                                <a href="#" class="btn">Devamını Oku</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-4">
                        <div class="card blog-card" data-aos="fade-up" data-aos-delay="300">
                            <img src="https://images.unsplash.com/photo-1506126613408-eca07ce68773" alt="Blog 3">
                            <div class="card-body">
                                <h5 class="card-title">Esneklik ve Mobilite İçin Yoga</h5>
                                <p class="card-text">Yoga ile esnekliğinizi artırın ve sakatlanma riskini azaltın.</p>
                                <a href="#" class="btn">Devamını Oku</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Bölümü -->
        <section class="cta-section" data-aos="fade-up">
            <div class="container">
                <h2 class="mb-4">Hedeflerine Bugün Başla!</h2>
                <p class="lead mb-4">FitMate ile sağlıklı bir yaşam için ilk adımı at. Topluluğumuza katıl ve değişimi yaşa!</p>
                <?php if (!isset($_SESSION['username'])): ?>
                    <a href="register.html" class="btn btn-primary">Hemen Kayıt Ol</a>
                <?php else: ?>
                    <a href="dashboard.php" class="btn btn-primary">Dashboard'a Git</a>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="text-center">
        <div class="container">
            <p class="mb-0">© 2025 FitMate. Tüm hakları saklıdır.</p>
            <p class="mb-0">İletişim: <a href="mailto:info@fitmate.com">info@fitmate.com</a> | Tel: <a href="tel:0123456789">0123 456 789</a></p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animasyon Kütüphanesi -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // AOS Animasyonlarını Başlat
        AOS.init({
            once: false,
            offset: 50,
            duration: 1000
        });

        window.addEventListener('load', function() {
            AOS.refresh();
        });

        window.addEventListener('resize', function() {
            AOS.refresh();
        });

        window.addEventListener('scroll', function() {
            AOS.refresh();
        });
    </script>
</body>
</html>