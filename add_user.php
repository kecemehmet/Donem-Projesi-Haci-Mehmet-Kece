<?php
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
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
$logged_in_user = $_SESSION['username'];
$stmt = $conn->prepare("SELECT is_admin FROM users WHERE username = ?");
$stmt->bind_param("s", $logged_in_user);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
if (!$user || $user['is_admin'] != 1) {
    header("Location: index.php");
    exit();
}

// Form gönderildiğinde
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Şifreyi hash'le
    $height = floatval($_POST['height']);
    $weight = floatval($_POST['weight']);
    $bmi = $weight / (($height / 100) * ($height / 100)); // BMI hesaplama
    $fitness_goal = $_POST['fitness_goal'];
    $experience_level = $_POST['experience_level'];
    $preferred_exercises = $_POST['preferred_exercises'];
    $workout_days = intval($_POST['workout_days']);
    $workout_duration = intval($_POST['workout_duration']);
    $target_weight = floatval($_POST['target_weight']);
    $target_set_date = $_POST['target_set_date'];
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;

    // Kullanıcı ekleme sorgusu
    $stmt = $conn->prepare("INSERT INTO users (username, password, email, height, weight, bmi, fitness_goal, experience_level, preferred_exercises, workout_days, workout_duration, target_weight, target_set_date, name, is_admin) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssffssssiiidssi", $username, $password, $email, $height, $weight, $bmi, $fitness_goal, $experience_level, $preferred_exercises, $workout_days, $workout_duration, $target_weight, $target_set_date, $name, $is_admin);

    if ($stmt->execute()) {
        header("Location: admin.php?success=1");
    } else {
        $error = "Kullanıcı eklenirken bir hata oluştu: " . $conn->error;
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitMate - Yeni Kullanıcı Ekle</title>
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
                    <li class="nav-item">
                        <a class="nav-link active" href="admin.php">Admin Paneli</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
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
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card register-card" data-aos="fade-up" data-aos-duration="1000">
                        <div class="card-body">
                            <h2 class="text-center mb-4">Yeni Kullanıcı Ekle</h2>
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                            <?php endif; ?>
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
                                <div class="mb-3">
                                    <label for="target_set_date" class="form-label">Hedef Belirleme Tarihi</label>
                                    <input type="date" class="form-control" id="target_set_date" name="target_set_date" required>
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
</body>
</html>