<?php
session_start();

// Kullanıcı giriş yapmış mı kontrol et
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// Admin kontrolü (oturumdan al)
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === 1;

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
$sql = "SELECT * FROM users WHERE username=?";
$stmt = $conn->prepare($sql);
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
    $profile_picture = $row['profile_picture'] ?? 'images/default_profile.png';
    $user_id = $row['id'];
} else {
    echo "Kullanıcı bulunamadı!";
    exit();
}
$stmt->close();

// Geri Bildirim Bildirimlerini Al
$feedback_notifications = $conn->query("SELECT * FROM feedback WHERE user_id = $user_id ORDER BY created_at DESC");

// Okunmamış Bildirim Sayısını Al
$unread_feedback_count = $conn->query("SELECT COUNT(*) as count FROM feedback WHERE user_id = $user_id AND response_status != 'read'")->fetch_assoc()['count'];

// Geri Bildirimi Silme
if (isset($_GET['delete_feedback'])) {
    $feedback_id = $_GET['delete_feedback'];
    $stmt = $conn->prepare("DELETE FROM feedback WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $feedback_id, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: dashboard.php?success=Geri bildirim başarıyla silindi!");
    exit();
}

// Geri Bildirimi Okundu Olarak İşaretle
if (isset($_GET['mark_read'])) {
    $feedback_id = $_GET['mark_read'];
    $stmt = $conn->prepare("UPDATE feedback SET response_status = 'read' WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $feedback_id, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: dashboard.php?success=Geri bildirim okundu olarak işaretlendi!");
    exit();
}

// Özel Antrenman Programını Al
$custom_workout = $conn->query("SELECT * FROM custom_workout_programs WHERE user_id = $user_id");
$custom_program = [];
if ($custom_workout->num_rows > 0) {
    while ($row = $custom_workout->fetch_assoc()) {
        $custom_program[$row['day']] = [
            'activity' => $row['activity'],
            'image' => $row['image'] ?? 'https://www.ekinhukuk.com.tr/wp-content/uploads/2022/08/dinlenme-sureleri.jpg'
        ];
    }
}

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

    if (in_array($preferred_exercises, array_keys($exercise_types))) {
        $base_plan = array_merge($exercise_types[$preferred_exercises], $base_plan);
    }

    $intensity = [
        "beginner" => "Hafif ",
        "intermediate" => "Orta ",
        "advanced" => "Yoğun "
    ];

    for ($i = 0; $i < $workout_days; $i++) {
        $exercise = $intensity[$experience_level] . $base_plan[$i % count($base_plan)];
        $daily_duration = round($workout_duration / $workout_days);
        $program[$days[$i]] = [
            "activity" => "$exercise ($daily_duration dk)",
            "image" => $images[$base_plan[$i % count($base_plan)]] ?? $images["Dinlenme"]
        ];
    }

    for ($i = $workout_days; $i < 7; $i++) {
        $program[$days[$i]] = [
            "activity" => "Dinlenme",
            "image" => $images["Dinlenme"]
        ];
    }

    return $program;
}

$weekly_program = count($custom_program) > 0 ? $custom_program : createWorkoutProgram($bmi, $fitness_goal, $experience_level, $preferred_exercises, $workout_days, $workout_duration);

$conn->close();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitMate - Dashboard</title>
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <style>
    /* Mevcut stiller korunuyor, yalnızca ilgili bölümler güncelleniyor */
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
            display: none;
        }
    }
    .feedback-section .card {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 15px;
    }
    #charCount {
        font-size: 0.9rem;
        color: #6c757d;
    }
    .invalid-feedback-custom {
        display: none;
        color: #dc3545;
    }
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
        background-color: rgba(40, 167, 69, 0.9);
    }
    .custom-alert-danger {
        background-color: rgba(220, 53, 69, 0.9);
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
    /* Geri Bildirim Simgesi Stilleri */
    .feedback-icon {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background-color: #3b82f6;
        color: #fff;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        cursor: pointer;
        transition: transform 0.3s ease, background-color 0.3s ease;
        z-index: 1000;
    }
    .feedback-icon:hover {
        transform: scale(1.1);
        background-color: #1e3a8a;
    }
    .feedback-icon .badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background-color: #ef4444;
        color: #fff;
        font-size: 0.75rem;
        padding: 4px 8px;
        border-radius: 50%;
    }
    /* Modern Modal Stilleri */
    .modern-modal .modal-content {
        background: linear-gradient(135deg, #1a1a1a 0%, #2c2c2c 100%);
        color: #fff;
        border: none;
        border-radius: 15px;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.5);
    }
    .modern-modal .modal-header {
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        background: rgba(255, 255, 255, 0.05);
    }
    .modern-modal .modal-title {
        font-weight: 600;
        color: #fff;
    }
    .modern-modal .modal-body {
        background: rgba(255, 255, 255, 0.03);
    }
    .modern-modal .modal-footer {
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        background: rgba(255, 255, 255, 0.05);
    }
    .modern-modal .table {
        color: #fff;
    }
    .modern-modal .table thead {
        background: rgba(255, 255, 255, 0.1);
    }
    .modern-modal .table tbody tr {
        background: rgba(255, 255, 255, 0.05);
        transition: background 0.3s ease;
    }
    .modern-modal .table tbody tr:hover {
        background: rgba(255, 255, 255, 0.15);
    }
    .modern-modal .badge {
        padding: 6px 12px;
        border-radius: 12px;
        font-weight: 500;
    }
    .modern-modal .badge.bg-warning {
        background-color: #f59e0b !important;
        color: #fff;
    }
    .modern-modal .badge.bg-success {
        background-color: #10b981 !important;
        color: #fff;
    }
    .modern-modal .badge.bg-info {
        background-color: #3b82f6 !important;
        color: #fff;
    }
    .modern-modal .btn-modern {
        padding: 5px 10px;
        font-size: 0.85rem;
        border-radius: 20px;
        transition: all 0.3s ease;
    }
    .modern-modal .btn-primary {
        background-color: #3b82f6;
        border: none;
    }
    .modern-modal .btn-primary:hover {
        background-color: #1e3a8a;
    }
    .modern-modal .btn-danger {
        background-color: #ef4444;
        border: none;
    }
    .modern-modal .btn-danger:hover {
        background-color: #b91c1c;
    }
    .modern-modal .btn-secondary {
        background-color: #4b5563;
        border: none;
    }
    .modern-modal .btn-secondary:hover {
        background-color: #374151;
    }
    .modern-modal .feedback-link {
        color: #60a5fa;
        text-decoration: none;
        opacity: 0.6; /* Silik görünüm */
        transition: opacity 0.3s ease;
    }
    .modern-modal .feedback-link:hover {
        color: #93c5fd;
        opacity: 1;
        text-decoration: underline;
    }
    /* Silik yazıların rengini sabitleme */
    .modern-modal .feedback-link,
    .modern-modal .feedback-link:hover {
        color: #000 !important; /* Her zaman siyah */
    }
    /* Detay Modal Stilleri */
    .modern-modal .feedback-text-container {
        background: rgba(255, 255, 255, 0.1);
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 10px;
    }
</style>
</head>
<body>
    <div id="loading-screen">
        <img src="images/logo2.png" alt="FitMate Logo" class="loading-logo">
    </div>

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
                        <a class="nav-link active" href="dashboard.php">Dashboard</a>
                    </li>
                    <?php if ($is_admin): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="admin.php">Admin Paneli</a>
                        </li>
                    <?php endif; ?>
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

    <div class="content">
        <div class="container mt-5">
            <!-- Mesaj Alanı -->
            <?php if (isset($_GET['success'])): ?>
                <div class="custom-alert custom-alert-success" id="update-message">
                    <div class="custom-alert-content">
                        <?php echo htmlspecialchars($_GET['success']); ?>
                    </div>
                    <div class="custom-progress">
                        <div class="custom-progress-bar"></div>
                    </div>
                </div>
            <?php elseif (isset($_GET['feedback_success'])): ?>
                <div class="custom-alert custom-alert-success" id="update-message">
                    <div class="custom-alert-content">
                        Geri bildiriminiz başarıyla gönderildi! Teşekkür ederiz.
                    </div>
                    <div class="custom-progress">
                        <div class="custom-progress-bar"></div>
                    </div>
                </div>
            <?php elseif (isset($_GET['feedback_error'])): ?>
                <div class="custom-alert custom-alert-danger" id="update-message">
                    <div class="custom-alert-content">
                        <?php echo htmlspecialchars($_GET['feedback_error']); ?>
                    </div>
                    <div class="custom-progress">
                        <div class="custom-progress-bar"></div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="card user-info-card" data-aos="fade-up" data-aos-duration="1000">
                        <div class="card-body text-center">
                            <input type="hidden" id="username" value="<?php echo htmlspecialchars($username); ?>">
                            <input type="hidden" id="bmi" value="<?php echo number_format($bmi, 2); ?>">
                            <input type="hidden" id="fitness_goal" value="<?php echo ucfirst($fitness_goal); ?>">
                            <input type="hidden" id="workout_days" value="<?php echo $workout_days; ?>">

                            <h2>Hoş Geldiniz, <?php echo htmlspecialchars($username); ?>!</h2>
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
                            <div class="alert alert-info mt-4" role="alert" style="border-radius: 15px; background: linear-gradient(135deg, #e0f7fa 0%, #b2ebf2 100%); color: #1a3c34;">
                                <i class="fas fa-info-circle me-2"></i>
                                Vücut Kitle Endeksi (BMI) hesaplamalarımız, Dünya Sağlık Örgütü (WHO) standartlarına uygun olarak gerçekleştirilmektedir.
                            </div>
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
                                <a href="update_profile.php" class="btn btn-primary" style="color: #fff !important;">Profil Güncelle</a>
                            </div>
                            <div class="mt-4 text-center">
                                <h5>Başarılarınızı Paylaşın!</h5>
                                <button class="btn btn-primary" onclick="shareOnX()">
                                    <img src="images/x-logo.svg" alt="X Logo" style="width: 20px; height: 20px; vertical-align: middle;"> X'te Paylaş
                                </button>
                                <button class="btn btn-primary" onclick="shareOnInstagram()">
                                    <i class="fab fa-instagram"></i> Instagram'da Paylaş
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="workout-section">
                        <h4 class="text-center" data-aos="fade-up" data-aos-duration="800">
                            <?php echo count($custom_program) > 0 ? 'Özel Antrenman Programınız' : 'Haftalık Antrenman Programınız'; ?>
                        </h4>
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

                    <!-- Geri Bildirim Bölümü -->
                    <div class="feedback-section mt-5" data-aos="fade-up" data-aos-duration="1000">
                        <h4 class="text-center">Geribildirim Gönderin</h4>
                        <div class="card">
                            <div class="card-body">
                                <form id="feedbackForm" action="submit_feedback.php" method="POST">
                                    <div class="mb-3">
                                        <label for="feedback_text" class="form-label">Görüşleriniz</label>
                                        <textarea class="form-control" id="feedback_text" name="feedback_text" rows="4" placeholder="Bize önerilerinizi veya düşüncelerinizi yazabilirsiniz (en az 60 karakter)..." required></textarea>
                                        <div id="charCount">0/60</div>
                                        <div id="feedbackError" class="invalid-feedback-custom">Geribildirim en az 60 karakter olmalıdır!</div>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">Geribildirim Gönder</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Geri Bildirim Simgesi -->
    <div class="feedback-icon" data-bs-toggle="modal" data-bs-target="#feedbackNotificationsModal">
        <i class="fas fa-comment-dots fa-lg"></i>
        <?php if ($unread_feedback_count > 0): ?>
            <span class="badge"><?php echo $unread_feedback_count; ?></span>
        <?php endif; ?>
    </div>

<!-- Geri Bildirim Bildirimleri Modal -->
<div class="modal fade modern-modal" id="feedbackNotificationsModal" tabindex="-1" aria-labelledby="feedbackNotificationsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="feedbackNotificationsModalLabel">Geribildirimler</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body">
                <?php if ($feedback_notifications->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Geri Bildirim</th>
                                    <th>Durum</th>
                                    <th>Tarih</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($feedback = $feedback_notifications->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <a href="#" class="feedback-link" data-bs-toggle="modal" data-bs-target="#feedbackModal" data-feedback="<?php echo htmlspecialchars($feedback['feedback_text']); ?>" data-response="<?php echo htmlspecialchars($feedback['admin_response'] ?? 'Henüz yanıtlanmadı.'); ?>">
                                                <?php 
                                                    $feedback_text = htmlspecialchars($feedback['feedback_text']);
                                                    echo substr($feedback_text, 0, 50) . (strlen($feedback_text) > 50 ? '...' : ''); 
                                                ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?php
                                            if ($feedback['response_status'] == 'unresponded') {
                                                echo '<span class="badge bg-warning">Yanıtlanmadı</span>';
                                            } elseif ($feedback['response_status'] == 'responded') {
                                                echo '<span class="badge bg-success">Yanıtlandı</span>';
                                            } else {
                                                echo '<span class="badge bg-info">Okundu</span>';
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo date('d.m.Y H:i', strtotime($feedback['created_at'])); ?></td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <?php if ($feedback['response_status'] == 'responded'): ?>
                                                    <a href="dashboard.php?mark_read=<?php echo $feedback['id']; ?>" class="btn btn-primary btn-modern" title="Okundu Olarak İşaretle">
                                                        <i class="fas fa-check"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="dashboard.php?delete_feedback=<?php echo $feedback['id']; ?>" class="btn btn-danger btn-modern" title="Sil" onclick="return confirm('Bu geribildirimi silmek istediğinizden emin misiniz?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center">Henüz geribildiriminiz bulunmamaktadır.</p>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>

<!-- Geri Bildirim Detay Modal -->
<div class="modal fade modern-modal" id="feedbackModal" tabindex="-1" aria-labelledby="feedbackModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="feedbackModalLabel">Geri Bildirim Detayı</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body">
                <h6>Geri Bildiriminiz:</h6>
                <div class="feedback-text-container" id="feedbackText"></div>
                <h6 class="mt-3">Admin Yanıtı:</h6>
                <div class="feedback-text-container" id="adminResponse"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
            </div>
        </div>
    </div>
</div>

    <footer class="text-center">
        <div class="container">
            <p class="mb-0">© 2025 FitMate. Tüm hakları saklıdır.</p>
            <p class="mb-0">İletişim: <a href="mailto:info@fitmate.com">info@fitmate.com</a> | Tel: <a href="tel:0123456789">0123 456 789</a></p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="js/theme.js"></script>
    <script>
        AOS.init({ once: false, offset: 50, duration: 1000 });
        window.addEventListener('load', function() {
            AOS.refresh();
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
        window.addEventListener('resize', AOS.refresh);
        window.addEventListener('scroll', AOS.refresh);

        function shareOnX() {
            const username = document.getElementById('username')?.value || "Kullanıcı";
            const bmi = document.getElementById('bmi')?.value || "N/A";
            const fitnessGoal = document.getElementById('fitness_goal')?.value || "N/A";
            const workoutDays = document.getElementById('workout_days')?.value || "N/A";
            const text = `${username} olarak FitMate ile hedeflerime ilerliyorum! BMI: ${bmi}, Hedef: ${fitnessGoal}, Haftada ${workoutDays} gün antrenman! #FitMate #FitnessJourney`;
            window.open("https://x.com/intent/tweet?text=" + encodeURIComponent(text), "_blank");
        }

        function shareOnInstagram() {
            alert("Instagram'da paylaşmak için lütfen ekran görüntüsü alıp uygulamadan yükleyin!");
            window.open("https://www.instagram.com/", "_blank");
        }

        // Geri bildirim formu için karakter sayacı ve kontrol
        document.addEventListener('DOMContentLoaded', function() {
            const feedbackText = document.getElementById('feedback_text');
            const charCount = document.getElementById('charCount');
            const feedbackError = document.getElementById('feedbackError');
            const feedbackForm = document.getElementById('feedbackForm');

            feedbackText.addEventListener('input', function() {
                const length = this.value.length;
                charCount.textContent = `${length}/60`;
                if (length < 60) {
                    charCount.style.color = '#dc3545';
                    feedbackError.style.display = 'block';
                } else {
                    charCount.style.color = '#28a745';
                    feedbackError.style.display = 'none';
                }
            });

            feedbackForm.addEventListener('submit', function(e) {
                if (feedbackText.value.length < 60) {
                    e.preventDefault();
                    feedbackError.style.display = 'block';
                }
            });

            // Modal için geri bildirim metnini doldurma
            const feedbackLinks = document.querySelectorAll('.feedback-link');
            feedbackLinks.forEach(link => {
                link.addEventListener('click', function() {
                    const feedbackText = this.getAttribute('data-feedback');
                    const adminResponse = this.getAttribute('data-response');
                    document.getElementById('feedbackText').textContent = feedbackText;
                    document.getElementById('adminResponse').textContent = adminResponse;

                    // Bildirimler modalını kapat ve detay modalını aç
                    const notificationsModal = bootstrap.Modal.getInstance(document.getElementById('feedbackNotificationsModal'));
                    notificationsModal.hide();
                });
            });
        });
    </script>
</body>
</html>