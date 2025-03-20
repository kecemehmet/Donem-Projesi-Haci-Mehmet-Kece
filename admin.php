<?php
session_start();

// Veritabanı bağlantısı
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fitness_db";
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
$stmt = $conn->prepare("SELECT id, is_admin FROM users WHERE username = ?");
$stmt->bind_param("s", $logged_in_user);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
if (!$user || $user['is_admin'] != 1) {
    header("Location: index.php");
    exit();
}

// Kullanıcı yasaklama/yasak kaldırma
if (isset($_GET['ban_id'])) {
    $ban_id = $_GET['ban_id'];
    if ($ban_id != $user['id']) {
        $stmt = $conn->prepare("UPDATE users SET is_banned = 1 WHERE id = ?");
        $stmt->bind_param("i", $ban_id);
        $stmt->execute();
    }
    header("Location: admin.php");
    exit();
}
if (isset($_GET['unban_id'])) {
    $unban_id = $_GET['unban_id'];
    $stmt = $conn->prepare("UPDATE users SET is_banned = 0 WHERE id = ?");
    $stmt->bind_param("i", $unban_id);
    $stmt->execute();
    header("Location: admin.php");
    exit();
}

// Kullanıcı silme işlemi
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    if ($delete_id != $user['id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
    }
    header("Location: admin.php");
    exit();
}

// Kullanıcıları al
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$filter_goal = isset($_GET['filter_goal']) ? $conn->real_escape_string($_GET['filter_goal']) : '';
$search_query = "%$search%";
$where_clause = "WHERE (username LIKE ? OR name LIKE ?)";
if ($filter_goal) {
    $where_clause .= " AND fitness_goal = ?";
}
$users_query = "SELECT id, username, name, email, is_admin, bmi, fitness_goal, target_weight, target_achieved_date, is_banned 
                FROM users 
                $where_clause 
                ORDER BY id DESC";
$stmt = $conn->prepare($users_query);
if ($filter_goal) {
    $stmt->bind_param("sss", $search_query, $search_query, $filter_goal);
} else {
    $stmt->bind_param("ss", $search_query, $search_query);
}
$stmt->execute();
$users_result = $stmt->get_result();

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
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <!-- Loading Screen -->
    <div id="loading-screen">
        <img src="images/logo2.png" alt="FitMate Logo" class="loading-logo">
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
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
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
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4>Kullanıcı Yönetimi</h4>
                        <a href="add_user.php" class="btn btn-primary"><i class="fas fa-user-plus"></i> Yeni Kullanıcı</a>
                    </div>

                    <!-- Arama ve Filtre Formu -->
                    <form method="GET" class="mb-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" name="search" class="form-control" placeholder="Kullanıcı ara (ad veya kullanıcı adı)" value="<?php echo htmlspecialchars($search); ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <select name="filter_goal" class="form-select">
                                    <option value="">Tüm Fitness Hedefleri</option>
                                    <option value="weight_loss" <?php echo $filter_goal == 'weight_loss' ? 'selected' : ''; ?>>Kilo Vermek</option>
                                    <option value="muscle_gain" <?php echo $filter_goal == 'muscle_gain' ? 'selected' : ''; ?>>Kas Kütlesi Artırmak</option>
                                    <option value="general_fitness" <?php echo $filter_goal == 'general_fitness' ? 'selected' : ''; ?>>Genel Fitness</option>
                                    <option value="endurance" <?php echo $filter_goal == 'endurance' ? 'selected' : ''; ?>>Dayanıklılık</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter"></i> Filtrele</button>
                            </div>
                        </div>
                    </form>

                    <?php if ($users_result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Kullanıcı Adı</th>
                                        <th>Ad</th>
                                        <th>E-posta</th>
                                        <th>BMI</th>
                                        <th>Fitness Hedefi</th>
                                        <th>Hedef Kilo</th>
                                        <th>Durum</th>
                                        <th>Admin</th>
                                        <th>Yasak</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $users_result->fetch_assoc()): ?>
                                        <tr <?php echo $row['target_achieved_date'] ? 'class="table-success"' : ($row['is_banned'] ? 'class="table-danger"' : ''); ?>>
                                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                                            <td><?php echo htmlspecialchars($row['username']); ?></td>
                                            <td><?php echo htmlspecialchars($row['name'] ?? 'Belirtilmemiş'); ?></td>
                                            <td><?php echo htmlspecialchars($row['email'] ?? 'Belirtilmemiş'); ?></td>
                                            <td><?php echo number_format($row['bmi'], 1); ?></td>
                                            <td>
                                                <?php 
                                                $goals = [
                                                    'weight_loss' => 'Kilo Vermek',
                                                    'muscle_gain' => 'Kas Kütlesi',
                                                    'general_fitness' => 'Genel Fitness',
                                                    'endurance' => 'Dayanıklılık'
                                                ];
                                                echo $goals[$row['fitness_goal']] ?? $row['fitness_goal'];
                                                ?>
                                            </td>
                                            <td><?php echo $row['target_weight'] ? number_format($row['target_weight'], 1) . ' kg' : '-'; ?></td>
                                            <td>
                                                <?php if ($row['target_achieved_date']): ?>
                                                    <span class="badge bg-success"><i class="fas fa-check"></i> Ulaşıldı</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning text-dark"><i class="fas fa-hourglass-half"></i> Devam Ediyor</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $row['is_admin'] ? 'bg-primary' : 'bg-secondary'; ?>">
                                                    <?php echo $row['is_admin'] ? 'Evet' : 'Hayır'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $row['is_banned'] ? 'bg-danger' : 'bg-success'; ?>">
                                                    <?php echo $row['is_banned'] ? 'Evet' : 'Hayır'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="edit_user.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-action btn-sm" title="Detay"><i class="fas fa-info-circle"></i></a>
                                                <a href="admin.php?delete_id=<?php echo $row['id']; ?>" class="btn btn-danger btn-action btn-sm" onclick="return confirm('Bu kullanıcıyı silmek istediğinizden emin misiniz?');"><i class="fas fa-trash"></i></a>
                                                <?php if ($row['is_banned']): ?>
                                                    <a href="admin.php?unban_id=<?php echo $row['id']; ?>" class="btn btn-success btn-action btn-sm" onclick="return confirm('Bu kullanıcının yasağını kaldırmak istediğinizden emin misiniz?');"><i class="fas fa-lock-open"></i></a>
                                                <?php else: ?>
                                                    <a href="admin.php?ban_id=<?php echo $row['id']; ?>" class="btn btn-warning btn-action btn-sm" onclick="return confirm('Bu kullanıcıyı yasaklamak istediğinizden emin misiniz?');"><i class="fas fa-ban"></i></a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted">Kullanıcı bulunamadı.</p>
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

    <!-- Harici JS Dosyaları -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/core.js"></script>
    <script src="js/admin.js"></script>
    <script src="js/theme.js"></script>
    <!-- Yükleme Ekranı Kontrolü -->
    <script>
        window.addEventListener('load', function() {
            const loadingScreen = document.getElementById('loading-screen');
            if (loadingScreen) {
                setTimeout(() => {
                    loadingScreen.classList.add('hidden');
                    setTimeout(() => {
                        loadingScreen.style.display = 'none';
                        console.log('Yükleme ekranı gizlendi ve kaldırıldı');
                    }, 500);
                }, 500);
            } else {
                console.error('Yükleme ekranı bulunamadı');
            }
        });
    </script>
</body>
</html>