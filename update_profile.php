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

// Kullanıcı bilgilerini al (hazırlıklı ifadelerle)
$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

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
    $target_weight = $row['target_weight'];
    $target_set_date = $row['target_set_date'];
    $target_achieved_date = $row['target_achieved_date'];
} else {
    echo "Kullanıcı bulunamadı!";
    exit();
}

// Form gönderildiğinde verileri güncelle (hazırlıklı ifadlerle)
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
    $target_weight = isset($_POST['target_weight']) && $_POST['target_weight'] !== '' ? $_POST['target_weight'] : null;

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

    $update_stmt = $conn->prepare("UPDATE users SET email = ?, height = ?, weight = ?, bmi = ?, fitness_goal = ?, experience_level = ?, preferred_exercises = ?, workout_days = ?, workout_duration = ?, target_weight = ?, target_set_date = ?, target_achieved_date = ? WHERE username = ?");
    $update_stmt->bind_param("sdddsssiidsss", $email, $height, $weight, $bmi, $fitness_goal, $experience_level, $preferred_exercises, $workout_days, $workout_duration, $target_weight, $target_set_date, $target_achieved_date, $username);

    if ($update_stmt->execute()) {
        echo "<script>alert('Profil başarıyla güncellendi!'); window.location.href='dashboard.php';</script>";
    } else {
        echo "Hata: " . $conn->error;
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
            min-height: 100vh; /* Sayfanın minimum yüksekliğini tam ekran yapar */
        }

        body {
            display: flex;
            flex-direction: column;
        }

        .content {
            flex: 1 0 auto; /* İçeriğin flex büyümesini sağlar */
            padding-bottom: 60px;
        }

        footer {
            flex-shrink: 0; /* Footer'ın küçülmesini engeller */
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

        /* Profil Güncelleme Formu */
        .update-section {
            padding: 60px 0;
        }

        .update-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            background: #fff;
            padding: 30px;
        }

        .update-card h2 {
            font-size: 2rem;
            font-weight: 600;
            color: #1a3c34;
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: 500;
            color: #1a3c34;
        }

        .form-control, .form-select {
            border-radius: 10px;
            border: 1px solid #ced4da;
            padding: 10px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: #00ddeb;
            box-shadow: 0 0 5px rgba(0, 221, 235, 0.3);
            outline: none;
        }

        .btn-success {
            background: #00ddeb;
            border: none;
            padding: 12px 30px;
            font-size: 1.1rem;
            font-weight: 500;
            border-radius: 50px;
            transition: transform 0.3s ease, background 0.3s ease;
        }

        .btn-success:hover {
            background: #00b7c2;
            transform: translateY(-3px);
        }

        .text-center a {
            color: #00ddeb;
            text-decoration: none;
            font-weight: 500;
        }

        .text-center a:hover {
            text-decoration: underline;
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

            .update-card h2 {
                font-size: 1.8rem;
            }

            .update-section {
                padding: 40px 0;
            }
        }

        @media (max-width: 576px) {
            .navbar-logo {
                height: 40px;
                max-width: 150px;
            }

            .update-card {
                padding: 20px;
            }

            .update-card h2 {
                font-size: 1.5rem;
            }

            .update-section {
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
                        <a class="nav-link" href="index.php">Anasayfa</a>
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

    <!-- Profil Güncelleme Formu -->
    <div class="content">
        <div class="container update-section">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card update-card" data-aos="fade-up" data-aos-duration="1000">
                        <div class="card-body">
                            <h2 class="text-center">Profil Güncelle</h2>
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
                                    <label for="target_weight" class="form-label">Hedef Kilo (kg)</label>
                                    <input type="number" class="form-control" id="target_weight" name="target_weight" step="0.1" value="<?php echo $target_weight ? $target_weight : ''; ?>" placeholder="Hedef kilonuzu girin (isteğe bağlı)">
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
            once: false, // Animasyonlar her kaydırmada tekrar oynar
            offset: 50, // Animasyonun tetiklenme mesafesini azaltır
            duration: 1000 // Animasyon süresi
        });

        // Sayfanın yüklenmesi tamamlandığında AOS'u yenile
        window.addEventListener('load', function() {
            AOS.refresh();
        });

        // Sayfanın boyutları değiştiğinde AOS'u yenile
        window.addEventListener('resize', function() {
            AOS.refresh();
        });

        // Sayfayı kaydırdığında AOS'u yenile
        window.addEventListener('scroll', function() {
            AOS.refresh();
        });
    </script>
</body>
</html> 