<?php
session_start();

// Kullanıcı giriş yapmış mı kontrol et
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// Session mesajlarını al
$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';

// Mesajları gösterdikten sonra session'dan sil
if (isset($_SESSION['success_message'])) unset($_SESSION['success_message']);
if (isset($_SESSION['error_message'])) unset($_SESSION['error_message']);

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

$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;

// Kullanıcı bilgilerini al
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $_SESSION['username']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $name = $row['name'] ?? '';
    $email = $row['email'];
    $height = $row['height'];
    $weight = $row['weight'];
    $bmi = $row['bmi'];
    $fitness_goal = $row['fitness_goal'];
    $experience_level = $row['experience_level'];
    $preferred_exercises = $row['preferred_exercises'] ?? 'Esneklik ve Mobilite';
    $workout_days = $row['workout_days'];
    $workout_duration = $row['workout_duration'];
    $target_weight = $row['target_weight'];
    $target_set_date = $row['target_set_date'];
    $target_achieved_date = $row['target_achieved_date'];
    $show_name_in_success = $row['show_name_in_success'];
    $show_username_in_success = $row['show_username_in_success'];
    $profile_picture = $row['profile_picture'] ?? 'images/default_profile.png';
} else {
    header("Location: index.php");
    exit();
}
$stmt->close();

// İstatistik: İlerleme yüzdesi hesaplama
$progress_percentage = ($target_weight && $weight) ? 
    round((abs($weight - $target_weight) / ($weight > $target_weight ? $weight : $target_weight)) * 100, 2) : 0;

// BMI kategorisini belirle
$bmi_category = '';
$bmi_icon = '';
if ($bmi < 18.5) {
    $bmi_category = '<span class="text-warning">Zayıf</span>';
    $bmi_icon = 'fa-person-running';
} elseif ($bmi >= 18.5 && $bmi < 25) {
    $bmi_category = '<span class="text-success">Normal</span>';
    $bmi_icon = 'fa-heart';
} elseif ($bmi >= 25 && $bmi < 30) {
    $bmi_category = '<span class="text-warning">Fazla Kilolu</span>';
    $bmi_icon = 'fa-dumbbell';
} else {
    $bmi_category = '<span class="text-danger">Obez</span>';
    $bmi_icon = 'fa-weight-scale';
}

// Tercih edilen egzersizler için Türkçe karşılıklar
$preferred_exercises_tr = [
    'flexibility' => 'Esneklik ve Mobilite',
    'strength' => 'Kuvvet',
    'cardio' => 'Kardiyo',
    'hiit' => 'HIIT',
    'yoga' => 'Yoga',
    'pilates' => 'Pilates',
    'crossfit' => 'CrossFit',
    'bodyweight' => 'Vücut Ağırlığı',
    'weightlifting' => 'Ağırlık Kaldırma',
    'endurance' => 'Dayanıklılık'
];

// Tercih edilen egzersizleri Türkçe'ye çevir
$preferred_exercises_display = isset($preferred_exercises_tr[$preferred_exercises]) 
    ? $preferred_exercises_tr[$preferred_exercises] 
    : $preferred_exercises;

// Fitness hedefleri için Türkçe karşılıklar
$fitness_goals_tr = [
    '' => 'Belirtilmemiş',
    'weight_loss' => 'Kilo Verme',
    'muscle_gain' => 'Kas Kazanımı',
    'general_fitness' => 'Genel Fitness',
    'endurance' => 'Dayanıklılık'
];

// Deneyim seviyeleri için Türkçe karşılıklar
$experience_levels_tr = [
    '' => 'Belirtilmemiş',
    'beginner' => 'Başlangıç',
    'intermediate' => 'Orta Düzey',
    'advanced' => 'İleri Düzey'
];

$conn->close();
?>

<!DOCTYPE html>
<html lang="tr" data-theme="<?php echo isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitMate - Profilim</title>
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/navbar.css">
    <style>
        .profile-header {
            text-align: center;
            margin-bottom: 2rem;
            padding: 2rem 1rem;
        }
        .profile-header-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 1rem;
            border: 3px solid var(--primary-btn-bg);
        }
        .profile-header-info {
            margin-top: 1rem;
        }
        .profile-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .profile-stat-card {
            background: var(--card-bg);
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .profile-stat-card i {
            font-size: 2rem;
            color: var(--primary-btn-bg);
            margin-bottom: 1rem;
        }
        .profile-progress {
            height: 10px;
            background: #e9ecef;
            border-radius: 5px;
            margin: 1rem 0;
            overflow: hidden;
        }
        .profile-progress-bar {
            height: 100%;
            background: var(--primary-btn-bg);
            border-radius: 5px;
            transition: width 0.3s ease;
        }
        .profile-info-section {
            background: var(--card-bg);
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .profile-info-section h5 {
            margin-bottom: 1.5rem;
            color: var(--text-color);
        }
        .profile-info-section h5 i {
            margin-right: 0.5rem;
            color: var(--primary-btn-bg);
        }
        /* Animasyon stilleri */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideInUp {
            from {
                transform: translateY(50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .fade-in {
            animation: fadeIn 1s ease-out;
        }

        .slide-in-up {
            animation: slideInUp 1s ease-out;
        }

        .profile-stat-card {
            animation: slideInUp 1s ease-out;
            animation-fill-mode: both;
        }

        .profile-stat-card:nth-child(1) { animation-delay: 0.1s; }
        .profile-stat-card:nth-child(2) { animation-delay: 0.2s; }
        .profile-stat-card:nth-child(3) { animation-delay: 0.3s; }

        .profile-header-image {
            animation: fadeIn 1s ease-out;
        }

        .profile-progress-bar {
            transition: width 1.5s ease-in-out;
        }

        /* BMI kategorisi stilleri */
        .bmi-category {
            font-size: 1.2rem;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            display: inline-block;
            margin-top: 0.5rem;
            transition: all 0.3s ease;
            position: relative;
        }

        .text-warning .bmi-category {
            background-color: rgba(255, 193, 7, 0.1);
            border: 2px solid #ffc107;
        }

        .text-success .bmi-category {
            background-color: rgba(40, 167, 69, 0.1);
            border: 2px solid #28a745;
        }

        .text-danger .bmi-category {
            background-color: rgba(220, 53, 69, 0.1);
            border: 2px solid #dc3545;
        }

        /* Profil kartı animasyonları */
        .profile-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px var(--shadow-color);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .profile-card::before {
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

        .profile-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px var(--shadow-color);
        }

        .profile-card:hover::before {
            transform: translateX(100%);
        }

        /* Profil resmi animasyonları */
        .profile-image-container {
            position: relative;
            overflow: hidden;
            border-radius: 50%;
            margin: 0 auto 20px;
            width: 150px;
            height: 150px;
            border: 3px solid var(--primary-btn-bg);
            transition: all 0.3s ease;
        }

        .profile-image-container:hover {
            transform: scale(1.05);
            border-color: var(--secondary-btn-bg);
        }

        .profile-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: all 0.3s ease;
        }

        .profile-image:hover {
            filter: brightness(1.1);
        }

        /* İstatistik kartları animasyonları */
        .stat-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px var(--shadow-color);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            text-align: center;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            border: 2px solid transparent;
        }

        .stat-card::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px var(--shadow-color);
        }

        .stat-card:hover::after {
            opacity: 1;
        }

        /* İkon animasyonları */
        .stat-card i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .stat-card:hover i {
            transform: scale(1.2) rotate(10deg);
        }

        /* Progress bar animasyonları */
        .progress {
            height: 8px;
            border-radius: 4px;
            background: var(--border-color);
            overflow: hidden;
            margin: 10px 0;
        }

        .progress-bar {
            background: linear-gradient(45deg, var(--primary-btn-bg), var(--secondary-btn-bg));
            transition: width 1.5s ease-in-out;
            position: relative;
            overflow: hidden;
        }

        .progress-bar::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            animation: progressShine 2s infinite;
        }

        @keyframes progressShine {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        /* BMI kartı özel stilleri */
        .bmi-card {
            border-width: 2px;
        }

        .bmi-card.text-warning {
            border-color: #ffc107;
        }

        .bmi-card.text-success {
            border-color: #28a745;
        }

        .bmi-card.text-danger {
            border-color: #dc3545;
        }

        .bmi-card i {
            color: inherit;
        }

        .text-warning i { color: #ffc107; }
        .text-success i { color: #28a745; }
        .text-danger i { color: #dc3545; }

        /* Animasyonlar */
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .stat-card i {
            animation: pulse 2s infinite;
        }

        /* Grid düzeni */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }

        .navbar {
            background: var(--bg-color);
            color: var(--text-color);
        }

        .navbar-brand {
            color: var(--text-color);
        }

        .navbar-nav .nav-link {
            color: var(--text-color);
        }

        .navbar-nav .nav-link:hover {
            color: var(--primary-btn-bg);
        }

        .navbar-nav .nav-link.active {
            color: var(--primary-btn-bg);
        }

        .navbar-toggler {
            color: var(--text-color);
            border-color: var(--text-color);
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(0, 0, 0, 0.55)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        [data-theme='dark'] .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(255, 255, 255, 0.55)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }

        /* Light modda navbar yazıları siyah */
        :root {
            --navbar-text-color: #000;
        }

        [data-theme='dark'] {
            --navbar-text-color: #fff;
        }

        .navbar-brand,
        .navbar-nav .nav-link,
        .brand-text,
        .theme-toggle i {
            color: var(--navbar-text-color) !important;
        }

        .navbar-nav .nav-link:hover {
            color: var(--primary-btn-bg) !important;
        }

        .navbar-nav .nav-link.active {
            color: var(--primary-btn-bg) !important;
        }
    </style>
</head>
<body class="profile-page">
    <?php include 'includes/navbar.php'; ?>

    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo $_SESSION['success_message']; ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo $_SESSION['error_message']; ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
    </div>

    <!-- Profil İçeriği -->
    <div class="content">
        <div class="container profile-section">
            <?php /* Eski Alert Mesajı Kaldırıldı */ ?>

            <!-- Profil Başlığı -->
            <div class="profile-header">
                <div class="profile-card fade-in" data-aos="fade-up">
                    <div class="profile-image-container">
                        <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profil Resmi" class="profile-image">
                    </div>
                    <div class="profile-header-info">
                        <h2><?php echo htmlspecialchars($name); ?></h2>
                        <p><?php echo htmlspecialchars($_SESSION['username']); ?> | <?php echo htmlspecialchars($email); ?></p>
                    </div>
                </div>
            </div>

            <!-- İstatistikler -->
            <div class="stats-grid">
                <div class="stat-card" data-aos="fade-up" data-aos-delay="100">
                    <i class="fas fa-weight"></i>
                    <h3><?php echo htmlspecialchars($weight); ?> kg</h3>
                    <p>Mevcut Kilo</p>
                    <?php if ($target_weight): ?>
                    <div class="progress" style="width: 100%;">
                        <div class="progress-bar" role="progressbar" style="width: <?php echo $progress_percentage; ?>%" 
                             aria-valuenow="<?php echo $progress_percentage; ?>" aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                    <small class="mt-2">Hedef: <?php echo htmlspecialchars($target_weight); ?> kg</small>
                    <?php endif; ?>
                </div>

                <div class="stat-card" data-aos="fade-up" data-aos-delay="200">
                    <i class="fas fa-ruler-vertical"></i>
                    <h3><?php echo htmlspecialchars($height); ?> cm</h3>
                    <p>Boy</p>
                </div>

                <div class="stat-card bmi-card <?php 
                    if ($bmi < 18.5) echo 'text-warning';
                    elseif ($bmi >= 18.5 && $bmi < 25) echo 'text-success';
                    elseif ($bmi >= 25 && $bmi < 30) echo 'text-warning';
                    else echo 'text-danger';
                ?>" data-aos="fade-up" data-aos-delay="300">
                    <i class="fas <?php echo $bmi_icon; ?>"></i>
                    <h3><?php echo number_format($bmi, 1); ?></h3>
                    <p>Vücut Kitle İndeksi (BMI)</p>
                    <div class="bmi-category"><?php echo $bmi_category; ?></div>
                </div>
            </div>

            <!-- Fitness Bilgileri -->
            <div class="profile-info-section">
                <h5><i class="fas fa-heartbeat"></i> Fitness Bilgileri</h5>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Fitness Hedefi:</strong> <?php echo $fitness_goals_tr[$fitness_goal] ?? 'Belirtilmemiş'; ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Deneyim Seviyesi:</strong> <?php echo $experience_levels_tr[$experience_level] ?? 'Belirtilmemiş'; ?></p>
                    </div>
                </div>
            </div>

            <!-- Hedef Bilgileri -->
            <div class="profile-info-section">
                <h5><i class="fas fa-bullseye"></i> Hedef Bilgileri</h5>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Hedef Belirleme Tarihi:</strong> <?php echo $target_set_date ? date('d.m.Y', strtotime($target_set_date)) : 'Belirtilmemiş'; ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Hedef Başarı Tarihi:</strong> <?php echo $target_achieved_date ? date('d.m.Y', strtotime($target_achieved_date)) : 'Henüz ulaşılmadı'; ?></p>
                    </div>
                </div>
            </div>

            <!-- Butonlar -->
            <div class="text-center mt-4">
                <a href="update_profile.php" class="btn btn-green btn-profile me-2" data-aos="fade-up" data-aos-delay="400">
                    <i class="fas fa-edit me-2"></i>Profili Düzenle
                </a>
                <a href="dashboard.php" class="btn btn-red btn-profile" data-aos="fade-up" data-aos-delay="500">
                    <i class="fas fa-arrow-left me-2"></i>Geri Dön
                </a>
            </div>
        </div>
    </div>
    <br>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="js/theme.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            once: true
        });

        // Sayfa yüklendiğinde toast'ları göster
        document.addEventListener('DOMContentLoaded', function() {
            const toasts = document.querySelectorAll('.toast');
            toasts.forEach(toast => {
                const bsToast = new bootstrap.Toast(toast, {
                    delay: 3000
                });
                bsToast.show();
            });
        });
    </script>
</body>
</html> 