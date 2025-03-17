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

// Kullanıcı bilgilerini al
$username = $_SESSION['username'];
$sql = "SELECT * FROM users WHERE username='$username'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $email = $row['email'];
    $height = $row['height'];
    $weight = $row['weight'];
    $bmi = $row['bmi'];
    $fitness_goal = $row['fitness_goal'];
    $experience_level = $row['experience_level'];
    $preferred_exercises = $row['preferred_exercises'];
    $workout_days = $row['workout_days'];
    $workout_duration = $row['workout_duration'];
} else {
    echo "Kullanıcı bulunamadı!";
    exit();
}

// Form gönderildiğinde verileri güncelle
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $height = $_POST['height'];
    $weight = $_POST['weight'];
    $bmi = $weight / (($height / 100) ** 2); // Yeni BMI hesapla
    $fitness_goal = $_POST['fitness_goal'];
    $experience_level = $_POST['experience_level'];
    $preferred_exercises = $_POST['preferred_exercises'];
    $workout_days = $_POST['workout_days'];
    $workout_duration = $_POST['workout_duration'];

    $update_sql = "UPDATE users SET 
        email='$email', 
        height=$height, 
        weight=$weight, 
        bmi=$bmi, 
        fitness_goal='$fitness_goal', 
        experience_level='$experience_level', 
        preferred_exercises='$preferred_exercises', 
        workout_days=$workout_days, 
        workout_duration=$workout_duration 
        WHERE username='$username'";

    if ($conn->query($update_sql) === TRUE) {
        echo "<script>alert('Profil başarıyla güncellendi!'); window.location.href='dashboard.php';</script>";
    } else {
        echo "Hata: " . $update_sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Güncelle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            display: flex;
            flex-direction: column;
        }
        .content {
            flex: 1;
            padding-bottom: 60px;
        }
        footer {
            flex-shrink: 0;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">FitMate</a>
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
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Hoş Geldin, <?php echo $username; ?></a>
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
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow">
                        <div class="card-body">
                            <h2 class="card-title text-center">Profil Güncelle</h2>
                            <form action="update_profile.php" method="POST">
                                <div class="mb-3">
                                    <label for="email" class="form-label">E-posta</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo $email; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="height" class="form-label">Boy (cm)</label>
                                    <input type="number" class="form-control" id="height" name="height" step="0.1" value="<?php echo $height; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="weight" class="form-label">Kilo (kg)</label>
                                    <input type="number" class="form-control" id="weight" name="weight" step="0.1" value="<?php echo $weight; ?>" required>
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
                                    <input type="number" class="form-control" id="workout_days" name="workout_days" min="1" max="7" value="<?php echo $workout_days; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="workout_duration" class="form-label">Antrenman Süresi (dk)</label>
                                    <input type="number" class="form-control" id="workout_duration" name="workout_duration" min="30" max="120" value="<?php echo $workout_duration; ?>" required>
                                </div>
                                <button type="submit" class="btn btn-success w-100">Güncelle</button>
                            </form>
                            <p class="mt-3 text-center"><a href="dashboard.php">Geri Dön</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-4">
        <div class="container">
            <p class="mb-0">© 2025 FitMate. Tüm hakları saklıdır.</p>
            <p class="mb-0">İletişim: info@fitnessapp.com | Tel: 0123 456 789</p>
        </div>
    </footer>
</body>
</html>