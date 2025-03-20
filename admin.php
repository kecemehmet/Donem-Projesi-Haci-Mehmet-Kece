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

// Kullanıcının admin olup olmadığını kontrol et
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

// Kullanıcıları al
$users_query = "SELECT id, username, name, email, is_admin FROM users ORDER BY id DESC";
$users_result = $conn->query($users_query);

// Kullanıcı silme işlemi
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    // Kendisini silmesini engelle
    if ($delete_id != $user['id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
    }
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
    <title>FitMate - Admin Paneli</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome (Simgeler için) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        /* Genel Stil */
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

        .nav-link {
            color: #fff !important;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .nav-link:hover {
            color: #00ddeb !important;
        }

        /* Admin Paneli İçerik */
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

        .table {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
        }

        .table thead {
            background: linear-gradient(90deg, #1a3c34 0%, #2a5d53 100%);
            color: #fff;
        }

        .btn-action {
            padding: 5px 10px;
            font-size: 0.9rem;
            border-radius: 5px;
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
        <!-- Admin Paneli Bölümü -->
        <section class="admin-section">
            <div class="container">
                <h2 class="text-center mb-5">FitMate Admin Paneli</h2>
                <div class="admin-card">
                    <h4 class="mb-4">Kullanıcı Yönetimi</h4>
                    <?php if ($users_result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Kullanıcı Adı</th>
                                        <th>Ad</th>
                                        <th>E-posta</th>
                                        <th>Admin</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $users_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                                            <td><?php echo htmlspecialchars($row['name'] ?? 'Belirtilmemiş'); ?></td>
                                            <td><?php echo htmlspecialchars($row['email'] ?? 'Belirtilmemiş'); ?></td>
                                            <td><?php echo $row['is_admin'] ? 'Evet' : 'Hayır'; ?></td>
                                            <td>
                                                <a href="edit_user.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-action"><i class="fas fa-edit"></i> Düzenle</a>
                                                <a href="admin.php?delete_id=<?php echo $row['id']; ?>" class="btn btn-danger btn-action" onclick="return confirm('Bu kullanıcıyı silmek istediğinizden emin misiniz?');"><i class="fas fa-trash"></i> Sil</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center">Henüz kullanıcı bulunmamaktadır.</p>
                    <?php endif; ?>
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