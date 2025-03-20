<?php
// Oturum süresini uzat
ini_set('session.gc_maxlifetime', 3600);
session_set_cookie_params(3600);
session_start();

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

// Admin kontrolü
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;

// İlk 2 harfi gösterip geri kalanını gizleyen fonksiyon
function maskString($string) {
    if (strlen($string) <= 2) {
        return $string;
    }
    return substr($string, 0, 2) . "*****";
}

// Hedeflerine ulaşan kullanıcıları al
$success_stories = [];
$success_query = "SELECT id, username, name, target_set_date, target_achieved_date, show_name_in_success, show_username_in_success 
                 FROM users 
                 WHERE target_weight IS NOT NULL 
                 AND target_achieved_date IS NOT NULL 
                 AND weight = target_weight 
                 ORDER BY target_achieved_date DESC";
$success_result = $conn->query($success_query);

if ($success_result->num_rows > 0) {
    while ($row = $success_result->fetch_assoc()) {
        $set_date = new DateTime($row['target_set_date']);
        $achieved_date = new DateTime($row['target_achieved_date']);
        $interval = $set_date->diff($achieved_date);
        $days = $interval->days;

        $success_stories[] = [
            'id' => $row['id'],
            'username' => $row['username'],
            'name' => $row['name'],
            'target_set_date' => $row['target_set_date'],
            'target_achieved_date' => $row['target_achieved_date'],
            'days' => $days,
            'show_name_in_success' => $row['show_name_in_success'],
            'show_username_in_success' => $row['show_username_in_success']
        ];
    }
    $success_stories = array_slice($success_stories, 0, 5);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitMate - Anasayfa</title>
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <!-- Loading Screen -->
    <div id="loading-screen">
        <img src="images/logo2.png" alt="FitMate Logo" class="loading-logo">
    </div>

<!-- Navbar -->
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">
            <img src="images/logo2.png" alt="Fitness App Logo" class="navbar-logo">
        </a>
        <!-- Tema simgesi ve hamburger menü yan yana -->
        <div class="d-flex align-items-center">
            <button class="nav-link btn theme-toggle" id="theme-toggle" title="Tema Değiştir">
                <i class="fas fa-moon"></i>
            </button>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="index.php">Anasayfa</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">Dashboard</a>
                </li>
                <?php if ($is_admin): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="admin.php">Admin Paneli</a>
                    </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav align-items-center">
                <?php if (isset($_SESSION['username'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Hoş Geldin, <?php echo htmlspecialchars($_SESSION['username']); ?></a>
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

    <!-- İçerik (Değişmeden kalabilir) -->
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
                            if ($story['show_name_in_success'] && $story['show_username_in_success'] && $story['name']) {
                                $display_name = htmlspecialchars($story['name'] . " (" . $story['username'] . ")");
                            } elseif ($story['show_name_in_success'] && $story['name']) {
                                $display_name = htmlspecialchars($story['name']);
                            } elseif ($story['show_username_in_success']) {
                                $display_name = htmlspecialchars($story['username']);
                            } else {
                                $masked_name = $story['name'] ? maskString($story['name']) : "AdYok";
                                $masked_username = maskString($story['username']);
                                $display_name = htmlspecialchars($masked_name . " (" . $masked_username . ")");
                            }
                            ?>
                            <div class="col-md-4 mb-4">
                                <div class="card success-card" data-aos="fade-up">
                                    <div class="card-body">
                                        <i class="fas fa-trophy mb-3"></i>
                                        <h5 class="card-title"><?php echo $display_name; ?></h5>
                                        <p class="card-text">
                                            Hedefine <strong><?php echo $story['days']; ?> gün</strong> içinde ulaştı! 🎉
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

    <!-- Harici JS Dosyaları -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="js/core.js"></script>
    <script src="js/theme.js"></script>
    <script>
        // AOS Başlatma
        AOS.init({
            once: false,
            offset: 50,
            duration: 1000
        });

        // Yükleme Ekranı Kontrolü
        window.addEventListener('load', function() {
            const loadingScreen = document.getElementById('loading-screen');
            if (loadingScreen) {
                setTimeout(() => {
                    loadingScreen.classList.add('hidden');
                    setTimeout(() => {
                        loadingScreen.style.display = 'none';
                    }, 500);
                }, 500);
            }
        });
    </script>
</body>
</html>