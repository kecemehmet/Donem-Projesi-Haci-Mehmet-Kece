<?php
session_start(); // Oturumu başlat

// Kullanıcı giriş yapmış mı kontrol et
if (!isset($_SESSION['username'])) {
    header("Location: index.php"); // Giriş yapmamışsa giriş sayfasına yönlendir
    exit();
}

// Veritabanı bağlantı bilgileri
$servername = "localhost";
$username = "root"; // Varsayılan kullanıcı adı
$password = ""; // Varsayılan şifre
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
        "Sırt ve Biceps" => "https://images.unsplash.com/photo-1530822847156-097df0e673c4",
        "Bacak ve Omuz" => "https://images.unsplash.com/photo-1517832207067-4db24a2ae47c",
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
    $daily_duration = round($workout_duration / $workout_days); // Küsüratı yuvarla
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
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
        .exercise-card {
            transition: transform 0.2s;
        }
        .exercise-card:hover {
            transform: scale(1.05);
        }
        .user-info i {
            margin-right: 10px;
            color: #007bff;
        }
        .navbar-logo {
    height: 80px; /* Logoyu büyütmek için yüksekliği artırdık */
    width: 250px; /* Genişliği orantılı tut */
}
    </style>
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
        <a class="navbar-brand" href="index.php">
                <img src="images/logo1.png" alt="Fitness App Logo" class="navbar-logo">
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
                <div class="col-md-10">
                    <div class="card shadow mb-4">
                        <div class="card-body text-center">
                            <h2 class="card-title">Hoş Geldiniz, <?php echo $username; ?>!</h2>
                            <div class="row user-info mt-4">
                                <div class="col-md-4">
                                    <p><i class="fas fa-weight"></i>BMI: <?php echo number_format($bmi, 2); ?></p>
                                </div>
                                <div class="col-md-4">
                                    <p><i class="fas fa-bullseye"></i>Hedef: <?php echo ucfirst($fitness_goal); ?></p>
                                </div>
                                <div class="col-md-4">
                                    <p><i class="fas fa-user-graduate"></i>Seviye: <?php echo ucfirst($experience_level); ?></p>
                                </div>
                                <div class="col-md-4">
                                    <p><i class="fas fa-dumbbell"></i>Tercih: <?php echo ucfirst($preferred_exercises); ?></p>
                                </div>
                                <div class="col-md-4">
                                    <p><i class="fas fa-calendar-week"></i>Gün: <?php echo $workout_days; ?></p>
                                </div>
                                <div class="col-md-4">
                                    <p><i class="fas fa-clock"></i>Süre: <?php echo $workout_duration; ?> dk</p>
                                </div>
                            </div>
                            <div class="mt-3">
                                <a href="update_profile.php" class="btn btn-primary">Profil Güncelle</a>
                            </div>
                        </div>
                    </div>

                    <h4 class="text-center mb-4">Haftalık Antrenman Programınız</h4>
                    <div class="row">
                        <?php foreach ($weekly_program as $day => $data) { ?>
                            <div class="col-md-4 mb-4">
                                <div class="card exercise-card shadow">
                                    <img src="<?php echo $data['image']; ?>" class="card-img-top" alt="<?php echo $data['activity']; ?>" style="height: 200px; object-fit: cover;">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo $day; ?></h5>
                                        <p class="card-text"><?php echo $data['activity']; ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>