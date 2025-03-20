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

// Kullanıcı güncelleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;

    $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, is_admin = ? WHERE id = ?");
    $stmt->bind_param("ssii", $name, $email, $is_admin, $user_id);
    $stmt->execute();

    header("Location: admin.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitMate - Kullanıcı Düzenle</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        html, body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        body {
            display: flex;
            flex-direction: column;
        }

        .content {
            flex: 1 0 auto;
            padding-bottom: 60px;
        }

        footer {
            flex-shrink: 0;
            width: 100%;
        }

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

        .nav-link {
            color: #fff !important;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .nav-link:hover {
            color: #00ddeb !important;
        }

        .admin-section {
            padding: 40px 0;
        }

        .admin-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            background: #fff;
            padding: 20px;
        }

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

        @media (max-width: 768px) {
            .navbar-logo {
                height: 50px;
                max-width: 200px;
            }

            .admin-section {
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
        <!-- Kullanıcı Düzenleme Bölümü -->
        <section class="admin-section">
            <div class="container">
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
                        <button type="submit" class="btn btn-primary">Kaydet</button>
                        <a href="admin.php" class="btn btn-secondary">İptal</a>
                    </form>
                </div>
            </div>
        </section>
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
</body>
</html>