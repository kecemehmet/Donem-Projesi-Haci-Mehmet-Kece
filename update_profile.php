<?php
session_start();

// Kullanıcı giriş yapmış mı kontrol et
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

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
$message = '';
$message_type = '';

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
    $preferred_exercises = $row['preferred_exercises'];
    $workout_days = $row['workout_days'];
    $workout_duration = $row['workout_duration'];
    $target_weight = $row['target_weight'];
    $target_set_date = $row['target_set_date'];
    $target_achieved_date = $row['target_achieved_date'];
    $show_name_in_success = $row['show_name_in_success'];
    $show_username_in_success = $row['show_username_in_success'];
    $profile_picture = $row['profile_picture'] ?? 'images/default_profile.png';
} else {
    $message = "Kullanıcı bulunamadı!";
    $message_type = "danger";
}
$stmt->close();

// İstatistik: İlerleme yüzdesi hesaplama
$progress_percentage = ($target_weight && $weight) ? 
    round((abs($weight - $target_weight) / ($weight > $target_weight ? $weight : $target_weight)) * 100, 2) : 0;

// Form gönderildiğinde
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $height = $_POST['height'];
    $weight = $_POST['weight'];
    $fitness_goal = $_POST['fitness_goal'];
    $experience_level = $_POST['experience_level'];
    $preferred_exercises = $_POST['preferred_exercises'];
    $workout_days = (int)$_POST['workout_days'];
    $workout_duration = $_POST['workout_duration'];
    $target_weight = $_POST['target_weight'] !== '' ? $_POST['target_weight'] : null;
    $show_name_in_success = isset($_POST['show_name_in_success']) ? 1 : 0;
    $show_username_in_success = isset($_POST['show_username_in_success']) ? 1 : 0;

    // Profil resmi yükleme
    $profile_picture_updated = $profile_picture;
    if (isset($_FILES["profile_picture"]) && $_FILES["profile_picture"]["error"] == 0) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $new_file_name = $target_dir . $_SESSION['username'] . "_profile." . $imageFileType;

        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($imageFileType, $allowed_types)) {
            $message = "Yalnızca JPG, JPEG, PNG ve GIF dosyaları kabul edilir.";
            $message_type = "danger";
        } elseif ($_FILES["profile_picture"]["size"] > 5000000) {
            $message = "Dosya boyutu 5MB'ı aşamaz.";
            $message_type = "danger";
        } elseif (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $new_file_name)) {
            $profile_picture_updated = $new_file_name;
        } else {
            $message = "Profil resmi yüklenirken bir hata oluştu.";
            $message_type = "danger";
        }
    }

    // Tek kullanıcı güncelleme
    if ($weight < 40 || $weight > 150) {
        $message = "Kilo 40 kg ile 150 kg arasında olmalıdır!";
        $message_type = "danger";
    } elseif ($target_weight !== null && ($target_weight < 40 || $target_weight > 150)) {
        $message = "Hedef kilo 40 kg ile 150 kg arasında olmalıdır!";
        $message_type = "danger";
    } elseif ($workout_days <= 0) {
        $message = "Antrenman gün sayısı 0 veya negatif olamaz!";
        $message_type = "danger";
    } elseif ($workout_days > 7) {
        $message = "Antrenman gün sayısı 7'den büyük olamaz!";
        $message_type = "danger";
    } else {
        $bmi = $weight / (($height / 100) ** 2);
        if ($target_weight !== null && $target_set_date === null) {
            $target_set_date = date("Y-m-d");
        } elseif ($target_weight === null) {
            $target_set_date = null;
        }
        if ($target_weight !== null && $weight == $target_weight && $target_achieved_date === null) {
            $target_achieved_date = date("Y-m-d");
        } elseif ($weight != $target_weight) {
            $target_achieved_date = null;
        }

        $update_stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, height = ?, weight = ?, bmi = ?, fitness_goal = ?, experience_level = ?, preferred_exercises = ?, workout_days = ?, workout_duration = ?, target_weight = ?, target_set_date = ?, target_achieved_date = ?, show_name_in_success = ?, show_username_in_success = ?, profile_picture = ? WHERE username = ?");
        $update_stmt->bind_param("ssdddsssiidssiiss", $name, $email, $height, $weight, $bmi, $fitness_goal, $experience_level, $preferred_exercises, $workout_days, $workout_duration, $target_weight, $target_set_date, $target_achieved_date, $show_name_in_success, $show_username_in_success, $profile_picture_updated, $_SESSION['username']);
        
        if ($update_stmt->execute()) {
            $message = "Profil güncellendi!";
            $message_type = "success";
        } else {
            $message = "Hata: " . $conn->error;
            $message_type = "danger";
        }
        $update_stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitMate - Profil Güncelle</title>
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <style>
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
        .custom-alert-success { background-color: rgba(40, 167, 69, 0.9); }
        .custom-alert-danger { background-color: rgba(220, 53, 69, 0.9); }
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
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Anasayfa</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">Dashboard</a>
                    </li>
                    <?php if ($is_admin): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin.php">Admin Paneli</a>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item d-flex align-items-center">
                        <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profil Resmi" class="profile-pic me-2" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                        <a class="nav-link" href="dashboard.php">Hoş Geldin, <?php echo htmlspecialchars($_SESSION['username']); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Çıkış Yap</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Profil Güncelleme Formu -->
    <div class="content">
        <div class="container update-section">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <!-- Mesaj Alanı -->
                    <?php if ($message): ?>
                        <div class="custom-alert custom-alert-<?php echo htmlspecialchars($message_type); ?>" id="update-message">
                            <div class="custom-alert-content"><?php echo htmlspecialchars($message); ?></div>
                            <div class="custom-progress"><div class="custom-progress-bar"></div></div>
                        </div>
                    <?php endif; ?>

                    <div class="card update-card" data-aos="fade-up" data-aos-duration="1000">
                        <div class="card-body">
                            <h2 class="text-center">Profil Güncelle</h2>
                            <!-- İstatistik -->
                            <div class="alert alert-info text-center mb-4">
                                Hedef Kilo İlerlemesi: <?php echo $progress_percentage; ?>%
                            </div>

                            <!-- Mevcut Profil Resmi -->
                            <div class="text-center mb-4">
                                <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profil Resmi" class="profile-pic" style="width: 100px; height: 100px;">
                            </div>

                            <form action="update_profile.php" method="POST" enctype="multipart/form-data" novalidate>
                                <div class="mb-3">
                                    <label for="profile_picture" class="form-label">Profil Resmi (isteğe bağlı)</label>
                                    <input type="file" class="form-control" id="profile_picture" name="profile_picture" accept="image/*">
                                    <small class="form-text text-muted">Desteklenen formatlar: JPG, JPEG, PNG, GIF (maks. 5MB)</small>
                                </div>
                                <div class="mb-3">
                                    <label for="name" class="form-label">Adınız</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" placeholder="Adınızı girin (isteğe bağlı)">
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">E-posta</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="height" class="form-label">Boy (cm)</label>
                                    <input type="number" class="form-control" id="height" name="height" step="0.1" value="<?php echo htmlspecialchars($height); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="weight" class="form-label">Kilo (kg)</label>
                                    <input type="number" class="form-control" id="weight" name="weight" step="0.1" min="40" max="150" value="<?php echo htmlspecialchars($weight); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="target_weight" class="form-label">Hedef Kilo (kg)</label>
                                    <input type="number" class="form-control" id="target_weight" name="target_weight" step="0.1" min="40" max="150" value="<?php echo htmlspecialchars($target_weight ?? ''); ?>" placeholder="Hedef kilonuzu girin (isteğe bağlı)">
                                </div>
                                <div class="mb-3">
                                    <label for="fitness_goal" class="form-label">Fitness Hedefiniz</label>
                                    <select class="form-select" id="fitness_goal" name="fitness_goal" required>
                                        <option value="weight_loss" <?php if ($fitness_goal == "weight_loss") echo "selected"; ?>>Kilo Vermek</option>
                                        <option value="muscle_gain" <?php if ($fitness_goal == "muscle_gain") echo "selected"; ?>>Kas Kütlesi Artırmak</option>
                                        <option value="general_fitness" <?php if ($fitness_goal == "general_fitness") echo "selected"; ?>>Genel Sağlık ve Fitness</option>
                                        <option value="endurance" <?php if ($fitness_goal == "endurance") echo "selected"; ?>>Dayanıklılık Artırmak</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="experience_level" class="form-label">Spor Geçmişiniz</label>
                                    <select class="form-select" id="experience_level" name="experience_level" required>
                                        <option value="beginner" <?php if ($experience_level == "beginner") echo "selected"; ?>>Yeni Başlayan</option>
                                        <option value="intermediate" <?php if ($experience_level == "intermediate") echo "selected"; ?>>Orta Seviye</option>
                                        <option value="advanced" <?php if ($experience_level == "advanced") echo "selected"; ?>>İleri Seviye</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="preferred_exercises" class="form-label">Tercih Ettiğiniz Egzersiz Türleri</label>
                                    <select class="form-select" id="preferred_exercises" name="preferred_exercises" required>
                                        <option value="cardio" <?php if ($preferred_exercises == "cardio") echo "selected"; ?>>Kardiyo</option>
                                        <option value="strength" <?php if ($preferred_exercises == "strength") echo "selected"; ?>>Kuvvet Antrenmanları</option>
                                        <option value="flexibility" <?php if ($preferred_exercises == "flexibility") echo "selected"; ?>>Esneklik ve Mobilite</option>
                                        <option value="team_sports" <?php if ($preferred_exercises == "team_sports") echo "selected"; ?>>Takım Sporları</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="workout_days" class="form-label">Haftada Kaç Gün Antrenman Yapabilirsiniz?</label>
                                    <input type="number" class="form-control" id="workout_days" name="workout_days" min="1" max="7" value="<?php echo htmlspecialchars($workout_days); ?>" required>
                                    <small class="form-text text-muted">1 ile 7 arasında bir değer girin.</small>
                                </div>
                                <div class="mb-3">
                                    <label for="workout_duration" class="form-label">Antrenman Süresi (dk)</label>
                                    <input type="number" class="form-control" id="workout_duration" name="workout_duration" min="30" max="120" value="<?php echo htmlspecialchars($workout_duration); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Başarı Hikayelerinde Görünürlük</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="show_name_in_success" name="show_name_in_success" <?php if ($show_name_in_success) echo "checked"; ?>>
                                        <label class="form-check-label" for="show_name_in_success">Adımın başarı hikayelerinde gösterilmesine izin ver</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="show_username_in_success" name="show_username_in_success" <?php if ($show_username_in_success) echo "checked"; ?>>
                                        <label class="form-check-label" for="show_username_in_success">Kullanıcı adımın başarı hikayelerinde gösterilmesine izin ver</label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-green w-100">Güncelle</button>
                            </form>
                            <a href="dashboard.php" class="btn btn-red w-100 mt-3">Geri Dön</a>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="js/core.js"></script>
    <script src="js/theme.js"></script>
    <script>
        AOS.init({ once: false, offset: 50, duration: 1000 });
        window.addEventListener('load', function() {
            const loadingScreen = document.getElementById('loading-screen');
            if (loadingScreen) {
                setTimeout(() => {
                    loadingScreen.classList.add('hidden');
                    setTimeout(() => loadingScreen.style.display = 'none', 500);
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