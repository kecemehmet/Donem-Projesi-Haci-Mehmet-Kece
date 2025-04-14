<?php
// Veritabanı bağlantısı
require_once dirname(__DIR__) . '/includes/db_connection.php';

// Oturum kontrolü
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Admin kontrolü
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../login.php');
    exit;
}

$query = "
    SELECT 
        id,
        username,
        email,
        DATE_FORMAT(created_at, '%d.%m.%Y %H:%i') as formatted_date,
        created_at,
        is_banned,
        is_admin,
        experience_level,
        fitness_goal
    FROM users 
    WHERE is_admin = 0 
    ORDER BY created_at DESC, id DESC
";

// Sorguyu çalıştır
$users = $conn->query($query);

// Hata kontrolü
if (!$users) {
    die("Sorgu hatası: " . $conn->error);
}

// Deneyim seviyesi ve fitness hedefi için Türkçe karşılıklar
$experience_levels = [
    'beginner' => 'Başlangıç',
    'intermediate' => 'Orta Düzey',
    'advanced' => 'İleri Düzey'
];

$fitness_goals = [
    'weight_loss' => 'Kilo Verme',
    'muscle_gain' => 'Kas Kazanımı',
    'general_fitness' => 'Genel Fitness'
];

// Programları getir
$programs_query = "
    SELECT 
        p.*,
        u.username as trainer_name,
        DATE_FORMAT(p.created_at, '%d.%m.%Y %H:%i') as formatted_date
    FROM programs p
    LEFT JOIN users u ON p.trainer_id = u.id
    ORDER BY p.created_at DESC
";

$programs = $conn->query($programs_query);

if (!$programs) {
    die("Sorgu hatası: " . $conn->error);
}

// Aktif sekmeyi belirle
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'users';
?>

<!DOCTYPE html>
<html lang="tr" data-theme="<?php echo isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Programlar - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="../css/style.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            padding-top: 20px;
        }
        .sidebar a {
            color: #f8f9fa;
            padding: 15px;
            display: block;
            text-decoration: none;
            transition: all 0.3s;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .sidebar a.active {
            background-color: #007bff;
        }
        .content {
            padding: 20px;
        }
        .card {
            margin-bottom: 20px;
            border: none;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #e3e6f0;
        }
        .btn-toolbar {
            margin-left: auto;
        }
        /* Dark mod için özel stiller */
        [data-theme='dark'] .card {
            background-color: var(--card-bg);
            border-color: var(--border-color);
        }

        [data-theme='dark'] .card-header {
            background-color: var(--card-header-bg);
            border-bottom-color: var(--border-color);
        }

        [data-theme='dark'] .table {
            color: var(--text-color);
        }

        [data-theme='dark'] .table-bordered {
            border-color: var(--border-color);
        }

        [data-theme='dark'] .table-bordered th,
        [data-theme='dark'] .table-bordered td {
            border-color: var(--border-color);
            color: var(--text-color);
            background-color: var(--card-bg);
        }

        [data-theme='dark'] .table thead th {
            background-color: var(--card-header-bg);
            color: var(--text-color);
            border-bottom-color: var(--border-color);
        }

        [data-theme='dark'] .table tbody tr:hover {
            background-color: var(--hover-bg);
        }

        [data-theme='dark'] .btn-primary {
            background-color: var(--primary-btn-bg);
            border-color: var(--primary-btn-bg);
        }

        [data-theme='dark'] .btn-danger {
            background-color: var(--danger-btn-bg);
            border-color: var(--danger-btn-bg);
            color: var(--text-color);
        }

        [data-theme='dark'] .btn-danger:hover {
            background-color: var(--danger-btn-hover-bg);
            border-color: var(--danger-btn-hover-bg);
            color: var(--text-color);
        }

        [data-theme='dark'] .btn-info {
            background-color: var(--info-btn-bg);
            border-color: var(--info-btn-bg);
            color: var(--text-color);
        }

        [data-theme='dark'] .btn-info:hover {
            background-color: var(--info-btn-hover-bg);
            border-color: var(--info-btn-hover-bg);
            color: var(--text-color);
        }

        [data-theme='dark'] .badge.bg-success {
            background-color: var(--success-bg) !important;
        }

        [data-theme='dark'] .badge.bg-secondary {
            background-color: var(--secondary-bg) !important;
        }

        [data-theme='dark'] .text-primary {
            color: var(--primary-text) !important;
        }

        [data-theme='dark'] .text-gray-800 {
            color: var(--text-color) !important;
        }

        [data-theme='dark'] .font-weight-bold {
            color: var(--text-color);
        }

        [data-theme='dark'] .card-header h6 {
            color: var(--text-color);
        }

        [data-theme='dark'] .card-header h6.text-primary {
            color: var(--text-color) !important;
        }

        .btn-group .btn {
            border-radius: 50% 45% 45% 50% !important;
            width: 30px;
            height: 30px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 5px;
            font-size: 0.8rem;
            box-shadow: none;
            border-width: 1px;
            min-width: 30px;
            min-height: 30px;
            flex: 0 0 auto;
        }

        .btn-group .btn.btn-outline-primary {
            color: #007bff;
            border-color: #007bff;
            background-color: transparent;
        }

        .btn-group .btn.btn-outline-primary:hover {
            color: white;
            background-color: #007bff;
        }

        .btn-group .btn.btn-outline-danger {
            color: #dc3545;
            border-color: #dc3545;
            background-color: transparent;
        }

        .btn-group .btn.btn-outline-danger:hover {
            color: white;
            background-color: #dc3545;
        }

        .btn-group .btn i {
            margin: 0;
            font-size: 0.8rem;
            line-height: 1;
        }

        .btn-group {
            display: flex;
            gap: 10px;
            flex-wrap: nowrap;
        }

        [data-theme='dark'] .btn-group .btn.btn-outline-primary {
            color: #007bff;
            border-color: #007bff;
        }

        [data-theme='dark'] .btn-group .btn.btn-outline-primary:hover {
            color: white;
            background-color: #007bff;
        }

        [data-theme='dark'] .btn-group .btn.btn-outline-danger {
            color: #dc3545;
            border-color: #dc3545;
        }

        [data-theme='dark'] .btn-group .btn.btn-outline-danger:hover {
            color: white;
            background-color: #dc3545;
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }
        .table {
            width: 100%;
            max-width: 100%;
            margin-bottom: 1rem;
            background-color: transparent;
        }
        @media (max-width: 768px) {
            .table thead {
                display: none;
            }
            .table tr {
                display: block;
                margin-bottom: 0.625rem;
            }
            .table td {
                display: block;
                text-align: right;
                font-size: 0.8em;
                border-bottom: 1px solid #ddd;
                position: relative;
                padding-left: 50%;
            }
            .table td::before {
                content: attr(data-label);
                position: absolute;
                left: 0;
                width: 50%;
                padding-left: 15px;
                font-weight: bold;
                text-align: left;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 sidebar">
                <h3 class="text-center text-white mb-4">Admin Panel</h3>
                <a href="?tab=users" class="<?php echo $active_tab == 'users' ? 'active' : ''; ?>">
                    <i class="fas fa-users me-2"></i> Kullanıcılar
                </a>
                <a href="?tab=programs" class="<?php echo $active_tab == 'programs' ? 'active' : ''; ?>">
                    <i class="fas fa-dumbbell me-2"></i> Programlar
                </a>
                <a href="?tab=feedback" class="<?php echo $active_tab == 'feedback' ? 'active' : ''; ?>">
                    <i class="fas fa-comment me-2"></i> Geri Bildirimler
                </a>
                <a href="?tab=settings" class="<?php echo $active_tab == 'settings' ? 'active' : ''; ?>">
                    <i class="fas fa-cog me-2"></i> Ayarlar
                </a>
                <a href="../dashboard.php" class="mt-5">
                    <i class="fas fa-arrow-left me-2"></i> Panele Dön
                </a>
                <a href="../logout.php" class="text-danger">
                    <i class="fas fa-sign-out-alt me-2"></i> Çıkış Yap
                </a>
            </div>
            <div class="col-md-10 content">
                <?php if ($active_tab == 'programs'): ?>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h1 class="h3 mb-0 text-gray-800">Programlar</h1>
                    </div>
                    <div class="col-md-6 text-end">
                        <a href="clean_programs.php" class="btn btn-danger" onclick="return confirm('Tüm programları silmek istediğinize emin misiniz? Bu işlem geri alınamaz!');">
                            <i class="fas fa-trash"></i> Tüm Programları Temizle
                        </a>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Kullanıcı Programları</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Başlık</th>
                                        <th>Kullanıcı</th>
                                        <th>Kategori</th>
                                        <th>Zorluk Seviyesi</th>
                                        <th>Durum</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Programları kullanıcı bilgileriyle birlikte getir
                                    $programs_query = "SELECT p.*, u.username AS user_name 
                                                      FROM programs p
                                                      LEFT JOIN users u ON p.user_id = u.id
                                                      ORDER BY p.created_at DESC";
                                    $result = $conn->query($programs_query);
                                    
                                    while ($program = $result->fetch_assoc()):
                                    ?>
                                    <tr>
                                        <td><?php echo $program['id']; ?></td>
                                        <td><?php echo htmlspecialchars($program['title']); ?></td>
                                        <td><?php echo htmlspecialchars($program['user_name']); ?></td>
                                        <td><?php echo htmlspecialchars($program['category']); ?></td>
                                        <td><?php echo htmlspecialchars($program['difficulty_level']); ?></td>
                                        <td>
                                            <?php if ($program['is_active']): ?>
                                                <span class="badge bg-success">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Pasif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="../view_program.php?id=<?php echo $program['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-sm btn-outline-danger" onclick="deleteProgram(<?php echo $program['id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function toggleActive(programId, activeStatus) {
        if (confirm(activeStatus ? 'Bu programı aktif yapmak istediğinize emin misiniz?' : 'Bu programı pasif yapmak istediğinize emin misiniz?')) {
            fetch('toggle_program_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    program_id: programId,
                    is_active: activeStatus
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Bir hata oluştu: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Hata:', error);
                alert('Bir hata oluştu.');
            });
        }
    }

    function deleteProgram(programId) {
        if (confirm('Bu programı silmek istediğinize emin misiniz? Bu işlem geri alınamaz!')) {
            fetch('delete_program.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    program_id: programId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Bir hata oluştu: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Hata:', error);
                alert('Bir hata oluştu.');
            });
        }
    }
    </script>
</body>
</html> 