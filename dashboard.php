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

// Bağlantı kontrolü
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
    $target_weight = $row['target_weight']; // Hedef kiloyu çek
} else {
    echo "Kullanıcı bulunamadı!";
    exit();
}

$conn->close();

// Antrenman programını oluşturma fonksiyonu
function createWorkoutProgram($bmi, $fitness_goal, $experience_level, $preferred_exercises, $workout_days, $workout_duration) {
    $program = [];
    $days = ["Pazartesi", "Salı", "Çarşamba", "Perşembe", "Cuma", "Cumartesi", "Pazar"];
    $exercise_types = [
        "cardio" => ["Koşu", "Bisiklet", "Yüzme", "HIIT"],
        "strength" => ["Göğüs ve Triceps", "Sırt ve Biceps", "Bacak ve Omuz", "Tüm Vücut Kuvvet"],
        "flexibility" => ["Yoga", "Esneme", "Pilates"],
        "team_sports" => ["Basketbol", "Futbol", "Voleybol"]
    ];
    $images = [
        "Koşu" => "https://images.unsplash.com/photo-1502224562085-639556652f33",
        "Bisiklet" => "https://blog.korayspor.com/wp-content/uploads/2023/08/Sabit-Bisiklet-Yag-Yakar-Mi.jpg",
        "Yüzme" => "https://www.acibadem.com.tr/hayat/Images/YayinMakaleler/yuzmenin-faydalari_733414_1.png",
        "HIIT" => "https://images.contentstack.io/v3/assets/blt45c082eaf9747747/blta585249cb277b1c3/5fdcfa83a703d10ab87e827b/HIIT.jpg?format=pjpg&auto=webp&quality=76&width=1232",
        "Göğüs ve Triceps" => "https://www.macfit.com/wp-content/uploads/2023/01/gogus-buyutme-yontemleri.jpg",
        "Sırt ve Biceps" => "https://minio.yalispor.com.tr/sneakscloud/blog/baslik_61c0523488163.jpg",
        "Bacak ve Omuz" => "https://shreddedbrothers.com/uploads/blogs/ckeditor/files/bacak-kas%C4%B1(2).jpg",
        "Tüm Vücut Kuvvet" => "https://images.unsplash.com/photo-1517838277536-f5f99be501cd",
        "Yoga" => "https://dansakademi.com.tr/uploads/2021/11/yoga-nedir.jpg",
        "Esneme" => "https://dansakademi.com.tr/uploads/2021/11/stretching-hareketleri.jpg",
        "Pilates" => "https://minio.yalispor.com.tr/yalispor/blog/pilates-topuyla-egzersizler_5efde689e2ee9.jpg",
        "Basketbol" => "https://images.unsplash.com/photo-1546519638-7e78a986d479",
        "Futbol" => "https://images.unsplash.com/photo-1579952363873-27f3b8e24a18",
        "Voleybol" => "https://images.unsplash.com/photo-1612872087720-48736c2a4a3e",
        "Dinlenme" => "https://www.ekinhukuk.com.tr/wp-content/uploads/2022/08/dinlenme-sureleri.jpg"
    ];

    // Fitness hedefine göre temel egzersiz planı
    if ($fitness_goal == "weight_loss") {
        $base_plan = array_merge($exercise_types["cardio"], ["Hafif Kuvvet"]);
        if ($bmi >= 25) $base_plan[] = "Düşük Tempolu Kardiyo";
    } elseif ($fitness_goal == "muscle_gain") {
        $base_plan = $exercise_types["strength"];
        if ($bmi < 18.5) $base_plan[] = "Ekstra Protein Odaklı Dinlenme";
    } elseif ($fitness_goal == "general_fitness") {
        $base_plan = array_merge($exercise_types["cardio"], $exercise_types["strength"], $exercise_types["flexibility"]);
    } elseif ($fitness_goal == "endurance") {
        $base_plan = $exercise_types["cardio"];
        $base_plan[] = "Uzun Süreli Düşük Tempo";
    }

    // Kullanıcının tercih ettiği egzersiz türünü önceliklendirme
    if (in_array($preferred_exercises, array_keys($exercise_types))) {
        $base_plan = array_merge($exercise_types[$preferred_exercises], $base_plan);
    }

    // Deneyim seviyesine göre yoğunluk ayarı
    $intensity = [
        "beginner" => "Hafif ",
        "intermediate" => "Orta ",
        "advanced" => "Yoğun "
    ];

    // Programı gün sayısına göre oluştur
    for ($i = 0; $i < $workout_days; $i++) {
        $exercise = $intensity[$experience_level] . $base_plan[$i % count($base_plan)];
        $daily_duration = round($workout_duration / $workout_days);
        $program[$days[$i]] = [
            "activity" => "$exercise ($daily_duration dk)",
            "image" => $images[$base_plan[$i % count($base_plan)]] ?? $images["Dinlenme"]
        ];
    }

    // Kalan günleri dinlenme ile doldur
    for ($i = $workout_days; $i < 7; $i++) {
        $program[$days[$i]] = [
            "activity" => "Dinlenme",
            "image" => $images["Dinlenme"]
        ];
    }

    return $program;
}

$weekly_program = createWorkoutProgram($bmi, $fitness_goal, $experience_level, $preferred_exercises, $workout_days, $workout_duration);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitMate - Dashboard</title>
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

        /* Kullanıcı Bilgileri Kartı */
        .user-info-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            background: #fff;
            padding: 30px;
            margin-bottom: 40px;
        }

        .user-info-card h2 {
            font-size: 2rem;
            font-weight: 600;
            color: #1a3c34;
            margin-bottom: 20px;
        }

        .user-info {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
        }

        .info-item {
            background: linear-gradient(135deg, #1a3c34 0%, #2a5d53 100%);
            color: white;
            padding: 15px 20px;
            border-radius: 15px;
            font-size: 1rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: transform 0.3s ease;
        }

        .info-item:hover {
            transform: translateY(-5px);
        }

        .info-item i {
            font-size: 1.2rem;
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

        /* Antrenman Programı Bölümü */
        .workout-section {
            padding: 40px 0;
        }

        .workout-section h4 {
            font-size: 2rem;
            font-weight: 600;
            color: #1a3c34;
            margin-bottom: 40px;
        }

        .exercise-card {
            border: none;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: #fff;
        }

        .exercise-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
        }

        .exercise-card img {
            height: 200px;
            object-fit: cover;
        }

        .exercise-card .card-body {
            padding: 20px;
        }

        .exercise-card h5 {
            font-size: 1.3rem;
            font-weight: 600;
            color: #1a3c34;
        }

        .exercise-card p {
            color: #666;
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

            .user-info-card h2 {
                font-size: 1.8rem;
            }

            .info-item {
                font-size: 0.9rem;
                padding: 10px 15px;
            }

            .workout-section h4 {
                font-size: 1.8rem;
            }
        }

        @media (max-width: 576px) {
            .navbar-logo {
                height: 40px;
                max-width: 150px;
            }

            .user-info-card {
                padding: 20px;
            }

            .user-info-card h2 {
                font-size: 1.5rem;
            }

            .info-item {
                font-size: 0.8rem;
                padding: 8px 12px;
            }

            .workout-section {
                padding: 20px 0;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
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
                    <a class="nav-link active" href="dashboard.php">Dashboard</a>
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
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <!-- Kullanıcı Bilgileri -->
<!-- Kullanıcı Bilgileri -->
<div class="card user-info-card" data-aos="fade-up" data-aos-duration="1000">
    <div class="card-body text-center">
        <h2>Hoş Geldiniz, <?php echo $username; ?>!</h2>
        <div class="user-info mt-4">
            <div class="info-item">
                <i class="fas fa-weight"></i>
                <span>BMI: <?php echo number_format($bmi, 2); ?></span>
            </div>
            <div class="info-item">
                <i class="fas fa-bullseye"></i>
                <span>Hedef: <?php echo ucfirst($fitness_goal); ?></span>
            </div>
            <div class="info-item">
                <i class="fas fa-user-graduate"></i>
                <span>Seviye: <?php echo ucfirst($experience_level); ?></span>
            </div>
            <div class="info-item">
                <i class="fas fa-dumbbell"></i>
                <span>Tercih: <?php echo ucfirst($preferred_exercises); ?></span>
            </div>
            <div class="info-item">
                <i class="fas fa-calendar-week"></i>
                <span>Gün: <?php echo $workout_days; ?></span>
            </div>
            <div class="info-item">
                <i class="fas fa-clock"></i>
                <span>Süre: <?php echo $workout_duration; ?> dk</span>
            </div>
        </div>
        <!-- BMI Bilgi Kutusu -->
        <div class="alert alert-info mt-4" role="alert" style="border-radius: 15px; background: linear-gradient(135deg, #e0f7fa 0%, #b2ebf2 100%); color: #1a3c34;">
            <i class="fas fa-info-circle me-2"></i>
            Vücut Kitle Endeksi (BMI) hesaplamalarımız, Dünya Sağlık Örgütü (WHO) standartlarına uygun olarak gerçekleştirilmektedir.
        </div>
        <!-- Hedef Kilo Bilgi Kutusu -->
        <?php if ($target_weight && $weight): ?>
                                <div class="alert alert-success mt-4" role="alert" style="border-radius: 15px; background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); color: #1a3c34;">
                                    <i class="fas fa-bullseye me-2"></i>
                                    Hedef Kilonuz: <?php echo $target_weight; ?> kg | 
                                    <?php 
                                        $difference = $weight - $target_weight;
                                        if ($difference > 0) {
                                            echo "Hedefe ulaşmak için " . number_format($difference, 1) . " kg vermeniz gerekiyor.";
                                        } elseif ($difference < 0) {
                                            echo "Hedefe ulaşmak için " . number_format(abs($difference), 1) . " kg almanız gerekiyor.";
                                        } else {
                                            echo "Tebrikler! Hedef kilonuza ulaştınız.";
                                        }
                                    ?>
                                </div>
                            <?php endif; ?>
        <div class="mt-4">
            <a href="update_profile.php" class="btn btn-primary">Profil Güncelle</a>
        </div>
    </div>
</div>

                    <!-- Haftalık Antrenman Programı -->
                    <div class="workout-section">
                        <h4 class="text-center" data-aos="fade-up" data-aos-duration="800">Haftalık Antrenman Programınız</h4>
                        <div class="row">
                            <?php foreach ($weekly_program as $day => $data): ?>
                                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="<?php echo (array_search($day, array_keys($weekly_program)) * 100); ?>">
                                    <div class="card exercise-card">
                                        <img src="<?php echo $data['image']; ?>" class="card-img-top" alt="<?php echo $data['activity']; ?>">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo $day; ?></h5>
                                            <p class="card-text"><?php echo $data['activity']; ?></p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
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