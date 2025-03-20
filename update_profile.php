<?php
session_start(); // Oturumu başlat

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

// Mesaj değişkeni tanımla
$message = '';
$message_type = ''; // 'success' veya 'danger' olarak kullanılacak

// Kullanıcı bilgilerini al (hazırlıklı ifadelerle)
$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $name = $row['name'];
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
} else {
    $message = "Kullanıcı bulunamadı!";
    $message_type = "danger";
}

// Form gönderildiğinde verileri güncelle (hazırlıklı ifadelerle)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $height = $_POST['height'];
    $weight = $_POST['weight'];
    $bmi = $weight / (($height / 100) ** 2); // Yeni BMI hesapla
    $fitness_goal = $_POST['fitness_goal'];
    $experience_level = $_POST['experience_level'];
    $preferred_exercises = $_POST['preferred_exercises'];
    $workout_days = $_POST['workout_days'];
    $workout_duration = $_POST['workout_duration'];
    $target_weight = isset($_POST['target_weight']) && $_POST['target_weight'] !== '' ? $_POST['target_weight'] : null;

    // Başarı hikayelerinde görünürlük tercihleri
    $show_name_in_success = isset($_POST['show_name_in_success']) ? 1 : 0;
    $show_username_in_success = isset($_POST['show_username_in_success']) ? 1 : 0;

    // Hedef kilo belirlenmişse ve daha önce bir tarih yoksa, target_set_date'i güncelle
    if ($target_weight !== null && $target_set_date === null) {
        $target_set_date = date("Y-m-d"); // Bugünün tarihi
    } elseif ($target_weight === null) {
        $target_set_date = null; // Hedef kilo silinirse tarihi de sıfırla
    }

    // Mevcut kilo hedef kiloya eşitse ve daha önce ulaşılmadıysa, target_achieved_date'i güncelle
    if ($target_weight !== null && $weight == $target_weight && $target_achieved_date === null) {
        $target_achieved_date = date("Y-m-d"); // Bugünün tarihi
    } elseif ($weight != $target_weight) {
        $target_achieved_date = null; // Hedef kilodan sapılırsa tarihi sıfırla
    }

    // Veritabanını güncelle
    $update_stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, height = ?, weight = ?, bmi = ?, fitness_goal = ?, experience_level = ?, preferred_exercises = ?, workout_days = ?, workout_duration = ?, target_weight = ?, target_set_date = ?, target_achieved_date = ?, show_name_in_success = ?, show_username_in_success = ? WHERE username = ?");
    $update_stmt->bind_param("ssdddsssiidssiis", $name, $email, $height, $weight, $bmi, $fitness_goal, $experience_level, $preferred_exercises, $workout_days, $workout_duration, $target_weight, $target_set_date, $target_achieved_date, $show_name_in_success, $show_username_in_success, $username);

    if ($update_stmt->execute()) {
        $message = "Profil başarıyla güncellendi!";
        $message_type = "success";
        // Güncellenen bilgileri tekrar yükle
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $name = $row['name'];
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
    } else {
        $message = "Hata: " . $conn->error;
        $message_type = "danger";
    }
    $update_stmt->close();
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitMate - Profil Güncelle</title>
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- AOS Animasyon Kütüphanesi -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
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
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Anasayfa</a>
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
                <ul class="navbar-nav">
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

    <!-- Profil Güncelleme Formu -->
    <div class="content">
        <div class="container update-section">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card update-card" data-aos="fade-up" data-aos-duration="1000">
                        <div class="card-body">
                            <h2 class="text-center">Profil Güncelle</h2>
<!-- Mesaj Alanı -->
<?php if ($message): ?>
    <div class="alert alert-<?php echo htmlspecialchars($message_type); ?> alert-dismissible fade show" role="alert" id="update-message">
        <?php echo htmlspecialchars($message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <div class="progress mt-2">
            <div class="progress-bar progress-bar-striped progress-bar-animated bg-<?php echo htmlspecialchars($message_type); ?>" role="progressbar" style="width: 100%;" id="message-timer"></div>
        </div>
    </div>
<?php endif; ?>
                            <form action="update_profile.php" method="POST">
                                <!-- Yeni eklenen isim alanı -->
                                <div class="mb-3">
                                    <label for="name" class="form-label">Adınız</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name ?? ''); ?>" placeholder="Adınızı girin (isteğe bağlı)">
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
                                    <input type="number" class="form-control" id="weight" name="weight" step="0.1" value="<?php echo htmlspecialchars($weight); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="target_weight" class="form-label">Hedef Kilo (kg)</label>
                                    <input type="number" class="form-control" id="target_weight" name="target_weight" step="0.1" value="<?php echo htmlspecialchars($target_weight ? $target_weight : ''); ?>" placeholder="Hedef kilonuzu girin (isteğe bağlı)">
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
                                </div>
                                <div class="mb-3">
                                    <label for="workout_duration" class="form-label">Antrenman Süresi (dk)</label>
                                    <input type="number" class="form-control" id="workout_duration" name="workout_duration" min="30" max="120" value="<?php echo htmlspecialchars($workout_duration); ?>" required>
                                </div>
                                <!-- Başarı Hikayelerinde Görünürlük Tercihleri -->
                                <div class="mb-3">
                                    <label class="form-label">Başarı Hikayelerinde Görünürlük</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="show_name_in_success" name="show_name_in_success" <?php if ($show_name_in_success) echo "checked"; ?>>
                                        <label class="form-check-label" for="show_name_in_success">
                                            Adımın başarı hikayelerinde gösterilmesine izin ver
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="show_username_in_success" name="show_username_in_success" <?php if ($show_username_in_success) echo "checked"; ?>>
                                        <label class="form-check-label" for="show_username_in_success">
                                            Kullanıcı adımın başarı hikayelerinde gösterilmesine izin ver
                                        </label>
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

    <!-- Harici JS Dosyaları -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="js/core.js"></script>
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

        // Mesajın otomatik kapanması ve animasyon
        const message = document.getElementById('update-message');
        if (message) {
            setTimeout(() => {
                message.classList.remove('show');
                setTimeout(() => {
                    message.remove(); // Mesajı DOM'dan tamamen kaldır
                }, 500); // Fade out animasyonu için süre
            }, 5000); // 5 saniye sonra kapanır
        }
    });
</script>
</body>
</html>