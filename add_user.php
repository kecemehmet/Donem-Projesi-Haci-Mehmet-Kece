<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Oturum ayarları
ini_set('session.gc_maxlifetime', 3600); // 1 saat
session_set_cookie_params(3600); // Çerez süresi 1 saat
session_start();

// Admin kontrolü
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
$logged_in_user = $_SESSION['username'];
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fitness_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

$stmt = $conn->prepare("SELECT is_admin, profile_picture FROM users WHERE username = ?");
$stmt->bind_param("s", $logged_in_user);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
if (!$user || $user['is_admin'] != 1) {
    header("Location: index.php");
    exit();
}

$profile_picture = $user['profile_picture'] ?? 'images/default_profile.png';

// Hata ve başarı mesajları için değişkenler
$success_message = null;
$error_message = null;

// Form gönderildiğinde
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Şifreyi hash'le
    $height = (string) floatval($_POST['height']);
    $weight = (string) floatval($_POST['weight']);
    $bmi = (string) ($weight / (($height / 100) * ($height / 100))); // BMI hesaplama
    $fitness_goal = $_POST['fitness_goal'];
    $experience_level = $_POST['experience_level'];
    $preferred_exercises = $_POST['preferred_exercises'];
    $workout_days = intval($_POST['workout_days']);
    $workout_duration = intval($_POST['workout_duration']);
    $target_weight = (string) floatval($_POST['target_weight']);
    $target_set_date = date('Y-m-d'); // Bugünün tarihi, örneğin 2025-03-21
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;

    // Tarih formatını kontrol et (hata ayıklama)
    if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $target_set_date)) {
        $error_message = "Geçersiz tarih formatı: " . $target_set_date;
    } else {
        // Kullanıcı adı ve e-posta kontrolü
        $stmt = $conn->prepare("SELECT is_banned FROM users WHERE email = ? OR username = ?");
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if ($row['is_banned']) {
                $error_message = "Bu e-posta adresi yasaklıdır. Başka bir e-posta kullanın!";
            } else {
                $error_message = "Bu kullanıcı adı veya e-posta zaten kullanılıyor!";
            }
        } else {
            // Kullanıcı ekleme sorgusu (15 değişken)
            $stmt = $conn->prepare("INSERT INTO users (username, password, email, height, weight, bmi, fitness_goal, experience_level, preferred_exercises, workout_days, workout_duration, target_weight, target_set_date, name, is_admin) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssssiiissi", 
                $username, 
                $password, 
                $email, 
                $height, 
                $weight, 
                $bmi, 
                $fitness_goal, 
                $experience_level, 
                $preferred_exercises, 
                $workout_days, 
                $workout_duration, 
                $target_weight, 
                $target_set_date, 
                $name, 
                $is_admin
            );

            if ($stmt->execute()) {
                $success_message = "Kullanıcı başarıyla eklendi!";
            } else {
                $error_message = "Kullanıcı eklenirken bir hata oluştu: " . $stmt->error;
            }
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitMate - Yeni Kullanıcı Ekle</title>
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <style>
        /* Mobil menü için profil resmi stil */
        .navbar-toggler-profile {
            border: none;
            padding: 0;
        }
        .navbar-toggler-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary-btn-bg);
            transition: transform 0.3s ease;
        }
        .navbar-toggler-profile img:hover {
            transform: scale(1.1);
        }
        @media (min-width: 992px) {
            .navbar-toggler-profile {
                display: none; /* Büyük ekranlarda gizle */
            }
        }
        /* Mesaj Stilleri */
        .custom-alert {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            color: #fff;
            opacity: 0.9;
            z-index: 1050;
            min-width: 250px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            animation: slideIn 0.5s ease-out;
        }
        .custom-alert-success {
            background-color: rgba(40, 167, 69, 0.9); /* Yeşil */
        }
        .custom-alert-danger {
            background-color: rgba(220, 53, 69, 0.9); /* Kırmızı */
        }
        .custom-alert-content {
            font-size: 1rem;
        }
        .custom-progress {
            height: 4px;
            background-color: rgba(255, 255, 255, 0.3);
            border-radius: 2px;
            margin-top: 8px;
            overflow: hidden;
        }
        .custom-progress-bar {
            height: 100%;
            background-color: #fff;
            animation: progress 5s linear forwards;
        }
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 0.9; }
        }
        @keyframes progress {
            from { width: 100%; }
            to { width: 0; }
        }
    </style>
</head>
<body>
    <div id="loading-screen">
        <img src="images/logo2.png" alt="FitMate Logo" class="loading-logo">
    </div>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <img src="images/logo2.png" alt="Fitness App Logo" class="navbar-logo">
            </a>
            <div class="d-flex align-items-center">
                <button class="nav-link btn theme-toggle" id="theme-toggle" title="Tema Değiştir">
                    <i class="fas fa-moon"></i>
                </button>
                <button class="navbar-toggler navbar-toggler-profile" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profil Resmi">
                </button>
            </div>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Anasayfa</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="admin.php">Admin Paneli</a>
                    </li>
                </ul>
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item d-flex align-items-center">
                        <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profil Resmi" class="profile-pic me-2">
                        <a class="nav-link" href="dashboard.php">Hoş Geldin, <?php echo htmlspecialchars($_SESSION['username']); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Çıkış Yap</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- İçerik -->
    <div class="content">
        <div class="container register-section">
            <!-- Mesaj Alanı -->
            <?php if ($success_message): ?>
                <div class="custom-alert custom-alert-success" id="update-message">
                    <div class="custom-alert-content">
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                    <div class="custom-progress">
                        <div class="custom-progress-bar"></div>
                    </div>
                </div>
            <?php elseif ($error_message): ?>
                <div class="custom-alert custom-alert-danger" id="update-message">
                    <div class="custom-alert-content">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                    <div class="custom-progress">
                        <div class="custom-progress-bar"></div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card register-card" data-aos="fade-up" data-aos-duration="1000">
                        <div class="card-body">
                            <h2 class="text-center mb-4">Yeni Kullanıcı Ekle</h2>
                            <form action="add_user.php" method="POST">
                                <!-- Temel Bilgiler -->
                                <div class="mb-3">
                                    <label for="name" class="form-label">Adı (Opsiyonel)</label>
                                    <input type="text" class="form-control" id="name" name="name">
                                </div>
                                <div class="mb-3">
                                    <label for="username" class="form-label">Kullanıcı Adı</label>
                                    <input type="text" class="form-control" id="username" name="username" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">E-posta</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Şifre</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="height" class="form-label">Boy (cm)</label>
                                    <input type="number" class="form-control" id="height" name="height" step="0.1" required>
                                </div>
                                <div class="mb-3">
                                    <label for="weight" class="form-label">Kilo (kg)</label>
                                    <input type="number" class="form-control" id="weight" name="weight" step="0.1" required>
                                </div>
                                <div class="mb-3">
                                    <label for="target_weight" class="form-label">Hedef Kilo (kg)</label>
                                    <input type="number" class="form-control" id="target_weight" name="target_weight" step="0.1" required>
                                </div>

                                <!-- Fitness Bilgileri -->
                                <div class="mb-3">
                                    <label for="fitness_goal" class="form-label">Fitness Hedefi</label>
                                    <select class="form-select" id="fitness_goal" name="fitness_goal" required>
                                        <option value="weight_loss">Kilo Vermek</option>
                                        <option value="muscle_gain">Kas Kütlesi Artırmak</option>
                                        <option value="general_fitness">Genel Sağlık ve Fitness</option>
                                        <option value="endurance">Dayanıklılık Artırmak</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="experience_level" class="form-label">Spor Geçmişi</label>
                                    <select class="form-select" id="experience_level" name="experience_level" required>
                                        <option value="beginner">Yeni Başlayan</option>
                                        <option value="intermediate">Orta Seviye</option>
                                        <option value="advanced">İleri Seviye</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="preferred_exercises" class="form-label">Tercih Edilen Egzersiz Türü</label>
                                    <select class="form-select" id="preferred_exercises" name="preferred_exercises" required>
                                        <option value="cardio">Kardiyo</option>
                                        <option value="strength">Kuvvet Antrenmanları</option>
                                        <option value="flexibility">Esneklik ve Mobilite</option>
                                        <option value="team_sports">Takım Sporları</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="workout_days" class="form-label">Haftada Kaç Gün Antrenman?</label>
                                    <input type="number" class="form-control" id="workout_days" name="workout_days" min="1" max="7" required>
                                </div>
                                <div class="mb-3">
                                    <label for="workout_duration" class="form-label">Antrenman Süresi (dk)</label>
                                    <input type="number" class="form-control" id="workout_duration" name="workout_duration" min="30" max="120" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Admin mi?</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_admin" name="is_admin">
                                        <label class="form-check-label" for="is_admin">Evet</label>
                                    </div>
                                </div>

                                <!-- Butonlar -->
                                <button type="submit" class="btn btn-green w-100">Kullanıcıyı Ekle</button>
                            </form>
                            <a href="admin.php" class="btn btn-red w-100 mt-3">Geri Dön</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
    <script>AOS.init();</script>
    <script src="js/core.js"></script>
    <script src="js/theme.js"></script>
    <script>
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

            const message = document.getElementById('update-message');
            if (message) {
                setTimeout(() => {
                    message.style.transition = 'opacity 0.5s ease-out';
                    message.style.opacity = '0';
                    setTimeout(() => message.remove(), 500);
                }, 5000);
            }
        });
    </script>
</body>
</html>