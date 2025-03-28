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

// Düzenlenecek kullanıcıyı al
if (!isset($_GET['id'])) {
    header("Location: admin.php");
    exit();
}

$user_id = $_GET['id'];
$stmt = $conn->prepare("SELECT id, username, name, email, is_admin FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$edit_user = $result->fetch_assoc();

if (!$edit_user) {
    header("Location: admin.php");
    exit();
}

// Hata ve başarı mesajları için değişkenler
$success_message = null;
$error_message = null;

// Kullanıcı güncelleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;

    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, is_admin = ? WHERE id = ?");
    $stmt->bind_param("ssii", $name, $email, $is_admin, $user_id);
    
    if ($stmt->execute()) {
        $success_message = "Kullanıcı başarıyla güncellendi!";
    } else {
        $error_message = "Kullanıcı güncellenirken bir hata oluştu: " . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitMate - Kullanıcı Düzenle</title>
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
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
        /* Özel loading ekranı stilleri */
        #edit-user-loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(0, 0, 0, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.5s ease;
            opacity: 1;
        }
        .edit-user-loading-logo {
            width: 100px;
            height: 100px;
            animation: spin 0.5s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        #edit-user-loading-screen.hidden {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
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
    <div id="edit-user-loading-screen">
        <img src="images/logo2.png" alt="Edit User Loading Logo" class="edit-user-loading-logo">
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

    <div class="content">
        <section class="admin-section">
            <div class="container">
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

                <h2 class="text-center mb-5">Kullanıcı Düzenle</h2>
                <div class="admin-card">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Kullanıcı Adı (Değiştirilemez)</label>
                            <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($edit_user['username']); ?>" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="name" class="form-label">Ad</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($edit_user['name'] ?? ''); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">E-posta</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($edit_user['email'] ?? ''); ?>">
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_admin" name="is_admin" <?php echo $edit_user['is_admin'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_admin">Admin mi?</label>
                        </div>
                        <button type="submit" class="btn btn-green w-100">Kaydet</button>
                        <a href="admin.php" class="btn btn-red w-100 mt-3">Geri Dön</a>
                    </form>
                </div>
            </div>
        </section>
    </div>

    <footer class="text-center">
        <div class="container">
            <p class="mb-0">© 2025 FitMate. Tüm hakları saklıdır.</p>
            <p class="mb-0">İletişim: <a href="mailto:info@fitmate.com">info@fitmate.com</a> | Tel: <a href="tel:0123456789">0123 456 789</a></p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/core.js"></script>
    <script src="js/theme.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loadingScreen = document.getElementById('edit-user-loading-screen');
            if (loadingScreen) {
                setTimeout(() => {
                    loadingScreen.classList.add('hidden');
                    setTimeout(() => {
                        loadingScreen.style.display = 'none';
                    }, 500);
                }, 1000);
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