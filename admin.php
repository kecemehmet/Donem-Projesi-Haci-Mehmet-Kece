<?php
// Oturum süresini uzat (oturum başlatılmadan önce yapılmalı)
ini_set('session.gc_maxlifetime', 3600); // 1 saat
session_set_cookie_params(3600); // Çerez süresi 1 saat

session_start();

// Hata raporlamayı etkinleştir
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Kullanıcı giriş yapmış mı ve admin mi kontrol et
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
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

// Giriş yapmış kullanıcının admin olup olmadığını kontrol et
$logged_in_user = $_SESSION['username'];
$stmt = $conn->prepare("SELECT is_admin FROM users WHERE username = ?");
$stmt->bind_param("s", $logged_in_user);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user || $user['is_admin'] != 1) {
    header("Location: index.php");
    exit();
}

// Profil resmini oturumdan al, yoksa varsayılanı kullan
$profile_picture = $_SESSION['profile_picture'] ?? 'images/default_profile.png';

// Toplu işlem kontrolü (Kullanıcılar için)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['bulk_action'])) {
    if (isset($_POST['selected_users']) && !empty($_POST['selected_users'])) {
        $selected_users = $_POST['selected_users'];
        $action = $_POST['bulk_action'];

        if ($action == 'ban') {
            $stmt = $conn->prepare("UPDATE users SET is_banned = 1 WHERE id = ? AND is_admin = 0");
            foreach ($selected_users as $user_id) {
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
            }
            $stmt->close();
            header("Location: admin.php?success=Seçilen kullanıcılar banlandı!");
            exit();
        } elseif ($action == 'unban') {
            $stmt = $conn->prepare("UPDATE users SET is_banned = 0 WHERE id = ? AND is_admin = 0");
            foreach ($selected_users as $user_id) {
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
            }
            $stmt->close();
            header("Location: admin.php?success=Seçilen kullanıcıların banı kaldırıldı!");
            exit();
        } elseif ($action == 'delete') {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND is_admin = 0");
            foreach ($selected_users as $user_id) {
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
            }
            $stmt->close();
            header("Location: admin.php?success=Seçilen kullanıcılar silindi!");
            exit();
        }
    } else {
        header("Location: admin.php?error=Hiçbir kullanıcı seçilmedi!");
        exit();
    }
}

// Geri Bildirimi Silme
if (isset($_GET['delete_feedback'])) {
    $feedback_id = $_GET['delete_feedback'];
    $stmt = $conn->prepare("DELETE FROM feedback WHERE id = ?");
    $stmt->bind_param("i", $feedback_id);
    $stmt->execute();
    $stmt->close();
    header("Location: admin.php?success=Geri bildirim başarıyla silindi!");
    exit();
}

// Geri Bildirime Yanıt Verme (Ayrı bir kontrol bloğu)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['respond_feedback'])) {
    $feedback_id = $_POST['feedback_id'];
    $admin_response = $_POST['admin_response'];

    if (!empty($admin_response)) {
        $stmt = $conn->prepare("UPDATE feedback SET admin_response = ?, response_status = 'responded' WHERE id = ?");
        $stmt->bind_param("si", $admin_response, $feedback_id);
        $stmt->execute();
        $stmt->close();
        header("Location: admin.php?success=Yanıt başarıyla gönderildi!");
    } else {
        header("Location: admin.php?error=Yanıt alanı boş olamaz!");
    }
    exit();
}

// Kullanıcıları al
$users_query = "SELECT * FROM users WHERE is_admin = 0";
$users_result = $conn->query($users_query);

// Geri bildirimleri al
$feedback_query = "SELECT f.*, u.username FROM feedback f LEFT JOIN users u ON f.user_id = u.id ORDER BY f.created_at DESC";
$feedback_result = $conn->query($feedback_query);

// İstatistik verilerini al
$fitness_goals = $conn->query("SELECT fitness_goal, COUNT(*) as count FROM users WHERE is_admin = 0 GROUP BY fitness_goal");
$experience_levels = $conn->query("SELECT experience_level, COUNT(*) as count FROM users WHERE is_admin = 0 GROUP BY experience_level");
$workout_days = $conn->query("SELECT workout_days, COUNT(*) as count FROM users WHERE is_admin = 0 GROUP BY workout_days");

$fitness_goals_data = ['weight_loss' => 0, 'muscle_gain' => 0, 'general_fitness' => 0, 'endurance' => 0];
$experience_levels_data = ['beginner' => 0, 'intermediate' => 0, 'advanced' => 0];
$workout_days_data = array_fill(1, 7, 0); // 1-7 gün için sıfırla

if ($fitness_goals) {
    while ($row = $fitness_goals->fetch_assoc()) {
        $fitness_goals_data[$row['fitness_goal']] = $row['count'];
    }
} else {
    error_log("Fitness goals query failed: " . $conn->error);
}

if ($experience_levels) {
    while ($row = $experience_levels->fetch_assoc()) {
        $experience_levels_data[$row['experience_level']] = $row['count'];
    }
} else {
    error_log("Experience levels query failed: " . $conn->error);
}

if ($workout_days) {
    while ($row = $workout_days->fetch_assoc()) {
        $workout_days_data[$row['workout_days']] = $row['count'];
    }
} else {
    error_log("Workout days query failed: " . $conn->error);
}

$conn->close();

// Verileri JSON formatına çevirerek JavaScript'e aktar
$fitness_goals_json = json_encode(array_values($fitness_goals_data));
$experience_levels_json = json_encode(array_values($experience_levels_data));
$workout_days_json = json_encode(array_values($workout_days_data));
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitMate - Admin Paneli</title>
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="js/chart.min.js"></script> <!-- Yerel Chart.js -->
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .navbar-toggler-profile { border: none; padding: 0; }
        .navbar-toggler-profile img {
            width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary-btn-bg); transition: transform 0.3s ease;
        }
        .navbar-toggler-profile img:hover { transform: scale(1.1); }
        @media (min-width: 992px) { .navbar-toggler-profile { display: none; } }
        .navbar { background-color: #212529; }
        .navbar-brand img { width: 40px; transition: transform 0.3s ease; }
        .navbar-brand img:hover { transform: scale(1.1); }
        .nav-link { color: #fff !important; font-weight: 500; }
        .nav-link:hover { color: #ccc !important; }
        .profile-pic { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 2px solid #fff; }
        .admin-tabs .nav-link {
            color: #6b7280; font-weight: 600; padding: 12px 20px; border: none; background: rgb(0, 128, 255); transition: all 0.3s ease;
        }
        .admin-tabs .nav-link:hover { color: #fff; background: #3b82f6; }
        .admin-tabs .nav-link.active { color: #fff; background: #1e3a8a; border-bottom: 3px solid #60a5fa; }
        .btn-modern { padding: 8px 16px; font-size: 0.9rem; border-radius: 20px; transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 5px; }
        .btn-green { background-color: #10b981; color: #fff; border: none; }
        .btn-green:hover { background-color: #059669; color: #fff; }
        .btn-primary { background-color: #3b82f6; border: none; }
        .btn-primary:hover { background-color: #1e3a8a; color: #fff; }
        .btn-red { background-color: #ef4444; color: #fff; border: none; }
        .btn-red:hover { background-color: #b91c1c; color: #fff; }
        .btn-ban { background-color: #f97316; color: #fff; border: none; }
        .btn-ban:hover { background-color: #c2410c; color: #fff; }
        .btn-unban { background-color: #22c55e; color: #fff; border: none; }
        .btn-unban:hover { background-color: #15803d; color: #fff; }
        .admin-card { background: #fff; border-radius: 15px; padding: 20px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05); margin-top: 20px; }
        .table { border-radius: 10px; overflow: hidden; }
        .table thead { background: #1e3a8a; color: #fff; }
        .table tbody tr:hover { background: rgb(0, 124, 248); }
        .custom-alert {
            position: fixed; top: 20px; right: 20px; padding: 15px 20px; border-radius: 8px; color: #fff; opacity: 0.9; z-index: 1050; min-width: 250px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); animation: slideIn 0.5s ease-out;
        }
        .custom-alert-success { background-color: rgba(40, 167, 69, 0.9); }
        .custom-alert-danger { background-color: rgba(220, 53, 69, 0.9); }
        .custom-progress { height: 4px; background-color: rgba(255, 255, 255, 0.3); border-radius: 2px; margin-top: 8px; overflow: hidden; }
        .custom-progress-bar { height: 100%; background-color: #fff; animation: progress 5s linear forwards; }
        @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 0.9; } }
        @keyframes progress { from { width: 100%; } to { width: 0; } }
        footer { background: #212529; color: #fff; padding: 20px 0; }
        footer a { color: #ccc; text-decoration: none; }
        footer a:hover { color: #fff; }
        canvas { max-width: 400px; margin: 20px auto; display: block; }
        .chart-container { text-align: center; }
        /* Geri Bildirimler Tablosu için Stiller */
        .feedback-link {
            color: #60a5fa;
            text-decoration: none;
            opacity: 0.6;
            transition: opacity 0.3s ease;
        }
        .feedback-link:hover {
            color: #93c5fd;
            opacity: 1;
            text-decoration: underline;
        }
        /* Silik yazıların rengini sabitleme */
        .feedback-link,
        .feedback-link:hover {
            color: #000 !important; /* Her zaman siyah */
        }
        .feedback-text-container {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 10px;
            color: #000 !important; /* Geri bildirim detayındaki metinler siyah */
        }
        /* Modal içindeki başlıklar için de siyah renk */
        #feedbackModal .modal-body h6 {
            color: #000 !important;
        }
    </style>
</head>
<body>
    <div id="loading-screen">
        <img src="images/logo2.png" alt="FitMate Logo" class="loading-logo">
    </div>

    <!-- Üst Navbar -->
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
                <ul class="navbar-nav">
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

    <!-- Admin Paneli İçeriği -->
    <div class="content">
        <div class="container admin-section mt-5">
            <!-- Mesaj Alanı -->
            <?php if (isset($_GET['success'])): ?>
                <div class="custom-alert custom-alert-success" id="update-message">
                    <div class="custom-alert-content"><?php echo htmlspecialchars($_GET['success']); ?></div>
                    <div class="custom-progress"><div class="custom-progress-bar"></div></div>
                </div>
            <?php elseif (isset($_GET['error'])): ?>
                <div class="custom-alert custom-alert-danger" id="update-message">
                    <div class="custom-alert-content"><?php echo htmlspecialchars($_GET['error']); ?></div>
                    <div class="custom-progress"><div class="custom-progress-bar"></div></div>
                </div>
            <?php endif; ?>

            <h2 class="text-center mb-4" data-aos="fade-up">Admin Paneli</h2>
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <!-- Sekmeler -->
                    <ul class="nav nav-tabs admin-tabs" id="adminTab" role="tablist" data-aos="fade-up" data-aos-duration="1000">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab" aria-controls="users" aria-selected="true">
                                Kullanıcı Yönetimi
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="feedback-tab" data-bs-toggle="tab" data-bs-target="#feedback" type="button" role="tab" aria-controls="feedback" aria-selected="false">
                                Geri Bildirimler
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="stats-tab" data-bs-toggle="tab" data-bs-target="#stats" type="button" role="tab" aria-controls="stats" aria-selected="false">
                                İstatistikler
                            </button>
                        </li>
                    </ul>

                    <!-- Sekme İçerikleri -->
                    <div class="tab-content" id="adminTabContent">
                        <!-- Kullanıcı Yönetimi Sekmesi -->
                        <div class="tab-pane fade show active" id="users" role="tabpanel" aria-labelledby="users-tab">
                            <div class="admin-card">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h4 class="mb-0">Kullanıcı Listesi</h4>
                                    <a href="add_user.php" class="btn btn-green btn-modern"><i class="fas fa-user-plus"></i> Kullanıcı Ekle</a>
                                </div>
                                <form method="POST" action="admin.php" id="bulkActionForm">
                                    <div class="d-flex justify-content-end mb-3">
                                        <button type="submit" name="bulk_action" value="ban" class="btn btn-ban btn-modern me-2" onclick="return confirm('Seçilen kullanıcıları banlamak istediğinizden emin misiniz?');"><i class="fas fa-ban"></i> Toplu Banla</button>
                                        <button type="submit" name="bulk_action" value="unban" class="btn btn-unban btn-modern me-2" onclick="return confirm('Seçilen kullanıcıların banını kaldırmak istediğinizden emin misiniz?');"><i class="fas fa-lock-open"></i> Toplu Ban Kaldır</button>
                                        <button type="submit" name="bulk_action" value="delete" class="btn btn-red btn-modern" onclick="return confirm('Seçilen kullanıcıları silmek istediğinizden emin misiniz?');"><i class="fas fa-trash"></i> Toplu Sil</button>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th><input type="checkbox" id="selectAll"></th>
                                                    <th>Kullanıcı Adı</th>
                                                    <th>E-posta</th>
                                                    <th>BMI</th>
                                                    <th>Fitness Hedefi</th>
                                                    <th>Admin</th>
                                                    <th>Durum</th>
                                                    <th>İşlemler</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($user = $users_result->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><input type="checkbox" name="selected_users[]" value="<?php echo $user['id']; ?>" class="userCheckbox"></td>
                                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                        <td><?php echo number_format($user['bmi'], 2); ?></td>
                                                        <td><?php echo ucfirst($user['fitness_goal']); ?></td>
                                                        <td><?php echo $user['is_admin'] ? 'Evet' : 'Hayır'; ?></td>
                                                        <td><?php echo $user['is_banned'] ? 'Banlı' : 'Aktif'; ?></td>
                                                        <td>
                                                            <div class="btn-group" role="group">
                                                                <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-primary btn-modern"><i class="fas fa-edit"></i> Düzenle</a>
                                                                <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="btn btn-red btn-modern" onclick="return confirm('Bu kullanıcıyı silmek istediğinizden emin misiniz?');"><i class="fas fa-trash"></i> Sil</a>
                                                                <?php if ($user['is_banned']): ?>
                                                                    <a href="ban_user.php?id=<?php echo $user['id']; ?>&action=unban" class="btn btn-unban btn-modern" onclick="return confirm('Bu kullanıcının banını kaldırmak istediğinizden emin misiniz?');"><i class="fas fa-lock-open"></i> Ban Kaldır</a>
                                                                <?php else: ?>
                                                                    <a href="ban_user.php?id=<?php echo $user['id']; ?>&action=ban" class="btn btn-ban btn-modern" onclick="return confirm('Bu kullanıcıyı banlamak istediğinizden emin misiniz?');"><i class="fas fa-ban"></i> Banla</a>
                                                                <?php endif; ?>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Geri Bildirimler Sekmesi -->
                        <div class="tab-pane fade" id="feedback" role="tabpanel" aria-labelledby="feedback-tab">
                            <div class="admin-card">
                                <h4 class="mb-3">Geri Bildirimler</h4>
                                <?php if ($feedback_result->num_rows > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Kullanıcı Adı</th>
                                                    <th>Geri Bildirim</th>
                                                    <th>Durum</th>
                                                    <th>Tarih</th>
                                                    <th>İşlemler</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($feedback = $feedback_result->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($feedback['username'] ?? 'Anonim'); ?></td>
                                                        <td>
                                                            <a href="#" class="feedback-link" data-bs-toggle="modal" data-bs-target="#feedbackModal" data-feedback-id="<?php echo $feedback['id']; ?>" data-feedback="<?php echo htmlspecialchars($feedback['feedback_text']); ?>" data-response="<?php echo htmlspecialchars($feedback['admin_response'] ?? ''); ?>">
                                                                <?php echo substr(htmlspecialchars($feedback['feedback_text']), 0, 50) . (strlen($feedback['feedback_text']) > 50 ? '...' : ''); ?>
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
                                                            <div class="btn-group" role="group">
                                                                <a href="#" class="btn btn-primary btn-modern" data-bs-toggle="modal" data-bs-target="#respondModal" data-feedback-id="<?php echo $feedback['id']; ?>" data-feedback="<?php echo htmlspecialchars($feedback['feedback_text']); ?>" data-response="<?php echo htmlspecialchars($feedback['admin_response'] ?? ''); ?>" title="Yanıtla">
                                                                    <i class="fas fa-reply"></i> Yanıtla
                                                                </a>
                                                                <a href="admin.php?delete_feedback=<?php echo $feedback['id']; ?>" class="btn btn-red btn-modern" title="Sil" onclick="return confirm('Bu geri bildirimi silmek istediğinizden emin misiniz?');">
                                                                    <i class="fas fa-trash"></i> Sil
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p class="text-center">Henüz geri bildirim bulunmamaktadır.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- İstatistikler Sekmesi -->
                        <div class="tab-pane fade" id="stats" role="tabpanel" aria-labelledby="stats-tab">
                            <div class="admin-card">
                                <h4 class="mb-3 text-center">Kullanıcı İstatistikleri</h4>
                                <div class="row">
                                    <div class="col-md-6 chart-container">
                                        <h5 class="text-center">Fitness Hedefleri</h5>
                                        <canvas id="fitnessGoalsChart"></canvas>
                                    </div>
                                    <div class="col-md-6 chart-container">
                                        <h5 class="text-center">Deneyim Seviyeleri</h5>
                                        <canvas id="experienceLevelsChart"></canvas>
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-md-12 chart-container">
                                        <h5 class="text-center">Haftalık Antrenman Günleri</h5>
                                        <canvas id="workoutDaysChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Geri Bildirim Modal -->
    <div class="modal fade" id="feedbackModal" tabindex="-1" aria-labelledby="feedbackModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="feedbackModalLabel">Geri Bildirim Detayı</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
                </div>
                <div class="modal-body">
                    <h6>Geri Bildiriminiz:</h6>
                    <div class="feedback-text-container" id="feedbackText"></div>
                    <h6 class="mt-3">Admin Yanıtı:</h6>
                    <div class="feedback-text-container" id="adminResponse"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-red btn-modern" data-bs-dismiss="modal"><i class="fas fa-times"></i> Kapat</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Yanıt Modal -->
    <div class="modal fade" id="respondModal" tabindex="-1" aria-labelledby="respondModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="respondModalLabel">Geri Bildirime Yanıt Ver</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
                </div>
                <div class="modal-body">
                    <form action="admin.php" method="POST">
                        <input type="hidden" name="feedback_id" id="feedback_id">
                        <div class="mb-3">
                            <label for="feedback_text" class="form-label">Geri Bildirim</label>
                            <textarea class="form-control" id="feedback_text" rows="3" readonly></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="admin_response" class="form-label">Yanıtınız</label>
                            <textarea class="form-control" id="admin_response" name="admin_response" rows="3" placeholder="Yanıtınızı buraya yazın..."></textarea>
                        </div>
                        <button type="submit" name="respond_feedback" class="btn btn-primary w-100">Yanıt Gönder</button>
                    </form>
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
    <script src="js/theme.js"></script>
    <script>
        AOS.init({
            once: false,
            offset: 50,
            duration: 1000
        });

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

        // Modal için geri bildirim metnini doldurma
        document.addEventListener('DOMContentLoaded', function() {
            const feedbackLinks = document.querySelectorAll('.feedback-link');
            feedbackLinks.forEach(link => {
                link.addEventListener('click', function() {
                    const feedbackId = this.getAttribute('data-feedback-id');
                    const feedbackText = this.getAttribute('data-feedback');
                    const adminResponse = this.getAttribute('data-response');
                    document.getElementById('feedback_id').value = feedbackId;
                    document.getElementById('feedback_text').value = feedbackText;
                    document.getElementById('feedbackText').textContent = feedbackText;
                    document.getElementById('adminResponse').textContent = adminResponse;
                });
            });

            // Tümünü seç checkbox'ı
            const selectAll = document.getElementById('selectAll');
            const userCheckboxes = document.querySelectorAll('.userCheckbox');
            selectAll.addEventListener('change', function() {
                userCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });

            // En az bir checkbox seçili değilse butonları devre dışı bırak
            const form = document.getElementById('bulkActionForm');
            const buttons = form.querySelectorAll('button[type="submit"]');
            form.addEventListener('change', function() {
                const anyChecked = Array.from(userCheckboxes).some(cb => cb.checked);
                buttons.forEach(btn => {
                    btn.disabled = !anyChecked;
                });
            });

            // Grafik verilerini PHP'den al
            const fitnessGoalsData = <?php echo $fitness_goals_json; ?>;
            const experienceLevelsData = <?php echo $experience_levels_json; ?>;
            const workoutDaysData = <?php echo $workout_days_json; ?>;

            console.log("Fitness Goals Data:", fitnessGoalsData);
            console.log("Experience Levels Data:", experienceLevelsData);
            console.log("Workout Days Data:", workoutDaysData);

            // Grafikleri çizme fonksiyonu
            function drawCharts() {
                try {
                    const fitnessGoalsCtx = document.getElementById('fitnessGoalsChart');
                    if (fitnessGoalsCtx) {
                        new Chart(fitnessGoalsCtx, {
                            type: 'pie',
                            data: {
                                labels: ['Kilo Verme', 'Kas Kütlesi Artırma', 'Genel Fitness', 'Dayanıklılık'],
                                datasets: [{
                                    data: fitnessGoalsData,
                                    backgroundColor: ['#ef4444', '#10b981', '#3b82f6', '#f97316']
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: { position: 'bottom' }
                                }
                            }
                        });
                    } else {
                        console.error("Fitness Goals Chart canvas not found!");
                    }

                    const experienceLevelsCtx = document.getElementById('experienceLevelsChart');
                    if (experienceLevelsCtx) {
                        new Chart(experienceLevelsCtx, {
                            type: 'pie',
                            data: {
                                labels: ['Başlangıç', 'Orta', 'İleri'],
                                datasets: [{
                                    data: experienceLevelsData,
                                    backgroundColor: ['#ef4444', '#10b981', '#3b82f6']
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: { position: 'bottom' }
                                }
                            }
                        });
                    } else {
                        console.error("Experience Levels Chart canvas not found!");
                    }

                    const workoutDaysCtx = document.getElementById('workoutDaysChart');
                    if (workoutDaysCtx) {
                        new Chart(workoutDaysCtx, {
                            type: 'bar',
                            data: {
                                labels: ['1 Gün', '2 Gün', '3 Gün', '4 Gün', '5 Gün', '6 Gün', '7 Gün'],
                                datasets: [{
                                    label: 'Kullanıcı Sayısı',
                                    data: workoutDaysData,
                                    backgroundColor: '#3b82f6'
                                }]
                            },
                            options: {
                                responsive: true,
                                scales: {
                                    y: { beginAtZero: true }
                                }
                            }
                        });
                    } else {
                        console.error("Workout Days Chart canvas not found!");
                    }
                } catch (error) {
                    console.error("Chart.js error:", error);
                }
            }

            // Sekme değiştiğinde grafikleri çiz
            const statsTab = document.getElementById('stats-tab');
            statsTab.addEventListener('shown.bs.tab', function () {
                console.log("Stats tab shown, drawing charts...");
                drawCharts();
            });

            // İlk yüklemede de grafikleri çiz (eğer sekme açıksa)
            if (document.querySelector('#stats.tab-pane.show.active')) {
                drawCharts();
            }
        });
    </script>
</body>
</html>