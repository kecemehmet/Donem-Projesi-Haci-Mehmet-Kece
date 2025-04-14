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

// Admin kontrolü ve kullanıcı bilgilerini alma
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
$profile_picture = 'images/default_profile.png'; // Varsayılan değer

if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $stmt = $conn->prepare("SELECT profile_picture FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $profile_picture = $row['profile_picture'] ?? 'images/default_profile.png';
    }
    $stmt->close();
}

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

require_once 'config.php';
$current_page = basename($_SERVER['PHP_SELF']);
$theme_class = isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'light' ? 'navbar-light' : 'navbar-dark';
?>

<!DOCTYPE html>
<html lang="tr" data-theme="<?php echo isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitMate - Fitness Yolculuğunuz Başlıyor</title>
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <!-- Önce harici CSS dosyaları -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Sonra kendi CSS dosyalarımız -->
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/navbar.css">
    <?php if ($is_admin): ?>
    <link rel="stylesheet" href="css/admin.css">
    <?php endif; ?>
    <style>
        /* Hero Section Styles */
        .hero-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
            padding: 100px 0;
            background: linear-gradient(135deg, var(--bg-color) 0%, var(--card-bg) 100%);
            color: var(--text-color);
            padding: 4rem 0;
            text-align: center;
            border-bottom: 2px solid var(--border-color);
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            background: linear-gradient(45deg, var(--primary-btn-bg), var(--secondary-btn-bg));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: titleAnimation 2s ease-in-out infinite;
        }

        .hero-description {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .hero-image {
            position: relative;
            animation: floatAnimation 3s ease-in-out infinite;
        }

        .hero-image img {
            max-width: 100%;
            height: auto;
            border-radius: 20px;
            box-shadow: 0 10px 30px var(--shadow-color);
        }

        /* Feature Cards */
        .feature-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px var(--shadow-color);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border: 2px solid var(--border-color);
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px var(--shadow-color);
            border-color: var(--primary-btn-bg);
        }

        .feature-card:hover::before {
            transform: translateX(100%);
        }

        .feature-icon {
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
            color: var(--primary-btn-bg);
            transition: all 0.3s ease;
        }

        .feature-card:hover .feature-icon {
            transform: scale(1.2) rotate(10deg);
            color: var(--secondary-btn-bg);
        }

        /* Statistics Section */
        .stats-section {
            background: var(--card-bg);
            padding: 80px 0;
            position: relative;
            overflow: hidden;
        }

        .stat-item {
            text-align: center;
            padding: 2rem;
            position: relative;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
            background: linear-gradient(45deg, var(--primary-btn-bg), var(--secondary-btn-bg));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-label {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        /* Programs Section */
        .programs-section {
            padding: 80px 0;
            background: linear-gradient(135deg, var(--bg-color) 0%, var(--card-bg) 100%);
        }

        .program-card {
            background: var(--card-bg);
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px var(--shadow-color);
            transition: all 0.3s ease;
            position: relative; 
        }

        .program-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px var(--shadow-color);
        }

        .program-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            transition: all 0.3s ease;
        }

        .program-card:hover .program-image {
            transform: scale(1.1);
        }

        .program-content {
            padding: 1.5rem;
        }

        /* Animations */
        @keyframes titleAnimation {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        @keyframes floatAnimation {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        /* Animated Background */
        .animated-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            opacity: 0.1;
            pointer-events: none;
        }

        .animated-bg::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent 45%, var(--primary-btn-bg) 50%, transparent 55%);
            animation: shimmer 6s linear infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-50%) translateY(-50%) rotate(0deg); }
            100% { transform: translateX(-50%) translateY(-50%) rotate(360deg); }
        }

        /* Call to Action Button */
        .cta-btn {
            padding: 1rem 2rem;
            font-size: 1.2rem;
            font-weight: 600;
            border-radius: 50px;
            background: linear-gradient(45deg, var(--primary-btn-bg), var(--secondary-btn-bg));
            color: #fff;
            border: none;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .cta-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.2), transparent);
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }

        .cta-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px var(--shadow-color);
            color: #fff;
        }

        .cta-btn:hover::before {
            transform: translateX(100%);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }

            .hero-description {
                font-size: 1rem;
            }

            .stat-number {
                font-size: 2.5rem;
            }
        }

        /* Success Stories Section Styles */
        .success-stories-section {
            background: var(--bg-color);
            position: relative;
            overflow: hidden;
        }

        .success-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 2rem;
            height: 100%;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            box-shadow: 0 4px 6px var(--shadow-color);
        }

        .success-card:hover {
            transform: translateY(-10px);
            border-color: var(--primary-btn-bg);
            box-shadow: 0 10px 20px var(--shadow-color);
        }

        .success-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }

        .success-card:hover::before {
            transform: translateX(100%);
        }

        .success-icon {
            font-size: 2.5rem;
            color: var(--primary-btn-bg);
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
        }

        .success-card:hover .success-icon {
            transform: scale(1.2) rotate(10deg);
            color: var(--secondary-btn-bg);
        }

        .success-content h4 {
            font-size: 1.25rem;
            margin-bottom: 1rem;
            color: var(--text-color);
        }

        .achievement {
            font-size: 1.1rem;
            margin-bottom: 1rem;
            color: var(--text-color);
        }

        .achievement strong {
            color: var(--primary-btn-bg);
            font-weight: 700;
        }

        .success-date {
            color: var(--text-muted);
            font-style: italic;
        }

        .empty-success {
            padding: 3rem;
            text-align: center;
        }

        .empty-success i {
            font-size: 4rem;
            color: var(--primary-btn-bg);
            margin-bottom: 1.5rem;
            animation: bounce 2s infinite;
        }

        .empty-success h4 {
            color: var(--text-color);
            margin-bottom: 1rem;
        }

        .empty-success p {
            color: var(--text-muted);
            font-size: 1.2rem;
            margin-bottom: 2rem;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        /* Features Section Styles */
        .features-section {
            padding: 4rem 0;
            background: var(--bg-color);
            color: var(--text-color);
        }

        .cta-section {
            background: var(--bg-color);
            color: var(--text-color);
            padding: 4rem 0;
            text-align: center;
            border-top: 2px solid var(--border-color);
        }

        .cta-card {
            background: var(--card-bg);
            padding: 2rem;
            border-radius: 10px;
            text-align: center;
            max-width: 600px;
            margin: 0 auto;
            border: 2px solid var(--border-color);
        }

        .cta-card:hover {
            border-color: var(--primary-btn-bg);
        }

        .navbar {
            border-bottom: 2px solid var(--border-color);
        }

        .footer {
            border-top: 2px solid var(--border-color);
        }

        /* Light tema için border renkleri */
        :root {
            --border-color: #e0e0e0;
        }

        /* Dark tema için border renkleri */
        [data-theme='dark'] {
            --border-color: #333;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="animated-bg"></div>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-content" data-aos="fade-right">
                    <h1 class="hero-title">Fitness Yolculuğunuzda Yanınızdayız</h1>
                    <p class="hero-description">Kişiselleştirilmiş antrenman programları, beslenme tavsiyeleri ve profesyonel rehberlik ile hedeflerinize ulaşın.</p>
                    <a href="register.php" class="cta-btn">Hemen Başlayın <i class="fas fa-arrow-right ms-2"></i></a>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="hero-image">
                        <img src="images/fitness-hero.png" alt="Fitness" class="img-fluid">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section py-5">
        <div class="container">
            <h2 class="text-center mb-5" data-aos="fade-up">Neden FitMate?</h2>
            <div class="row">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-card">
                        <i class="fas fa-dumbbell feature-icon"></i>
                        <h3>Kişiselleştirilmiş Programlar</h3>
                        <p>Size özel hazırlanan antrenman programları ile hedeflerinize daha hızlı ulaşın.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-card">
                        <i class="fas fa-heart feature-icon"></i>
                        <h3>Sağlıklı Yaşam</h3>
                        <p>Beslenme önerileri ve yaşam tarzı tavsiyeleri ile sağlıklı bir yaşam sürün.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-card">
                        <i class="fas fa-chart-line feature-icon"></i>
                        <h3>İlerleme Takibi</h3>
                        <p>Detaylı raporlar ve grafikler ile gelişiminizi adım adım takip edin.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row">
                <div class="col-md-4" data-aos="fade-up">
                    <div class="stat-item">
                        <div class="stat-number" data-target="1000">0</div>
                        <div class="stat-label">Mutlu Üye</div>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="stat-item">
                        <div class="stat-number" data-target="50">0</div>
                        <div class="stat-label">Uzman Eğitmen</div>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="stat-item">
                        <div class="stat-number" data-target="100">0</div>
                        <div class="stat-label">Program Çeşidi</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Programs Section -->
    <section class="programs-section">
        <div class="container">
            <h2 class="text-center mb-5" data-aos="fade-up">Popüler Programlar</h2>
            <div class="row">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="program-card">
                        <img src="images/program1.png" alt="Program 1" class="program-image">
                        <div class="program-content">
                            <h3>Kilo Verme Programı</h3>
                            <p>Sağlıklı ve kalıcı kilo verme için özel hazırlanmış program.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="program-card">
                        <img src="images/program2.jpg" alt="Program 2" class="program-image">
                        <div class="program-content">
                            <h3>Kas Kazanma Programı</h3>
                            <p>Kas kütlenizi artırmak için bilimsel temelli antrenman programı.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="program-card">
                        <img src="images/program3.jpg" alt="Program 3" class="program-image">
                        <div class="program-content">
                            <h3>Genel Fitness Programı</h3>
                            <p>Genel sağlık ve fitness için dengeli bir program.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Başarı Hikayeleri Section -->
    <section class="success-stories-section py-5">
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
                        <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                            <div class="success-card">
                                <div class="success-icon">
                                    <i class="fas fa-trophy"></i>
                                </div>
                                <div class="success-content">
                                    <h4><?php echo $display_name; ?></h4>
                                    <p class="achievement">Hedefine <strong><?php echo $story['days']; ?> gün</strong> içinde ulaştı! 🎉</p>
                                    <div class="success-date">
                                        <small>Başarı Tarihi: <?php echo date('d.m.Y', strtotime($story['target_achieved_date'])); ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center" data-aos="fade-up">
                        <div class="empty-success">
                            <i class="fas fa-flag-checkered mb-3"></i>
                            <h4>Henüz hedeflerine ulaşan kullanıcı yok.</h4>
                            <p>İlk olmak ister misin? 🚀</p>
                            <a href="register.php" class="cta-btn mt-3">Hemen Başla</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="js/core.js"></script>
    <script src="js/theme.js"></script>
    <script src="js/chart.min.js"></script>
    <script>
        // AOS animasyonlarını başlat
        AOS.init();

        // Sayaç animasyonu
        const counters = document.querySelectorAll('.stat-number');
        counters.forEach(counter => {
            const target = parseInt(counter.getAttribute('data-target'));
            const duration = 2000; // 2 saniye
            const increment = target / (duration / 16); // 60 FPS

            let current = 0;
            const updateCounter = () => {
                if (current < target) {
                    current += increment;
                    counter.textContent = Math.floor(current);
                    requestAnimationFrame(updateCounter);
                } else {
                    counter.textContent = target;
                }
            };
            updateCounter();
        });
    </script>
</body>
</html>