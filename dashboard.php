<?php
session_start();
require_once 'config.php';

// Kullanıcı girişi kontrolü
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Veritabanı bağlantısı ve kullanıcı verilerini çekme
$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Kullanıcının programlarını çek
$stmt = $conn->prepare("SELECT * FROM user_programs WHERE user_id = ?");
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$programs_result = $stmt->get_result();
$programs = [];
while ($program = $programs_result->fetch_assoc()) {
    $programs[] = $program;
}

// BMI hesaplama
$bmi = 0;
$bmi_category = "Belirtilmemiş";
$bmi_icon = "fa-question";

if (!empty($user['height']) && !empty($user['weight']) && $user['height'] > 0) {
    $height_m = $user['height'] / 100;
    $bmi = $user['weight'] / ($height_m * $height_m);
    $bmi = round($bmi, 1);

    // BMI kategorisi belirleme
    if ($bmi < 18.5) {
        $bmi_category = "Zayıf";
        $bmi_icon = "fa-person-running";
    } elseif ($bmi >= 18.5 && $bmi < 25) {
        $bmi_category = "Normal";
        $bmi_icon = "fa-heart";
    } elseif ($bmi >= 25 && $bmi < 30) {
        $bmi_category = "Kilolu";
        $bmi_icon = "fa-dumbbell";
    } else {
        $bmi_category = "Obez";
        $bmi_icon = "fa-weight-scale";
    }
}

// İlk kayıt için başlangıç ağırlığını ayarla (Eğer NULL ise)
if ($user['initial_weight'] === null && $user['weight'] !== null) {
    // Burada veritabanını güncelle
    $stmt_update_initial = $conn->prepare("UPDATE users SET initial_weight = ? WHERE id = ?");
    $stmt_update_initial->bind_param("di", $user['weight'], $user['id']);
    $stmt_update_initial->execute();
    $stmt_update_initial->close();
    // Güncellenen değeri $user dizisine de yansıt
    $user['initial_weight'] = $user['weight'];
}

// İlerleme yüzdesi hesaplama
$progress_percent = 0;
// Gerekli değerlerin varlığını ve sayısal olup olmadığını kontrol et
if (isset($user['target_weight']) && is_numeric($user['target_weight']) &&
    isset($user['initial_weight']) && is_numeric($user['initial_weight']) &&
    isset($user['weight']) && is_numeric($user['weight']))
{
    $initial_w = (float)$user['initial_weight'];
    $target_w = (float)$user['target_weight'];
    $current_w = (float)$user['weight'];

    $total_change_needed = $target_w - $initial_w;
    $current_change_made = $current_w - $initial_w;

    if ($total_change_needed == 0) {
        // Başlangıç ve hedef aynı
        $progress_percent = ($current_w == $target_w) ? 100 : 0;
    } else {
        // İlerleme yüzdesini hesapla (0 ile 100 arasında sınırla)
        // Kaybedilen/kazanılan kiloyu toplam hedefe oranla
        $progress = ($current_change_made / $total_change_needed) * 100;
        
        // Eğer hedef kilo almaksa ve kullanıcı kilo verdiyse progress negatif olur (0 olmalı)
        // Eğer hedef kilo vermekse ve kullanıcı kilo aldıysa progress negatif olur (0 olmalı)
        // Eğer hedefin tersi yönde ilerleme varsa (kilo alması gerekirken vermişse vb.), ilerlemeyi 0 kabul edebiliriz.
        // VEYA sadece ne kadar yol kat edildiğine bakabiliriz:
        $progress = (abs($current_change_made) / abs($total_change_needed)) * 100;
        
        $progress_percent = max(0, min(100, round($progress)));
    }
} else {
    // Hedef veya başlangıç kilosu belirtilmemişse ilerleme 0
    $progress_percent = 0;
}

// Kullanıcının eksik bilgilerini kontrol et
$missing_info = false;
$missing_fields = [];

if (empty($user['height']) || $user['height'] <= 0) {
    $missing_info = true;
    $missing_fields[] = 'boy';
}
if (empty($user['weight']) || $user['weight'] <= 0) {
    $missing_info = true;
    $missing_fields[] = 'kilo';
}
if (empty($user['fitness_goal'])) {
    $missing_info = true;
    $missing_fields[] = 'fitness hedefi';
}
if (empty($user['experience_level'])) {
    $missing_info = true;
    $missing_fields[] = 'deneyim seviyesi';
}

// Son program bilgilerini al
$last_program = null;
$program_query = "SELECT p.*, u.name as trainer_name 
                 FROM programs p 
                 LEFT JOIN users u ON p.trainer_id = u.id 
                 WHERE p.user_id = ? 
                 ORDER BY p.created_at DESC 
                 LIMIT 1";
$stmt = $conn->prepare($program_query);
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$program_result = $stmt->get_result();

if ($program_result->num_rows > 0) {
    $last_program = $program_result->fetch_assoc();
    // Null kontrolü ve varsayılan değerler
    $last_program['title'] = $last_program['title'] ?? 'Program Başlığı';
    $last_program['trainer_name'] = $last_program['trainer_name'] ?? 'Bilinmiyor';
    $last_program['created_at'] = $last_program['created_at'] ?? date('Y-m-d H:i:s');
}
?>

<!DOCTYPE html>
<html lang="tr" data-theme="<?php echo isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitMate - Dashboard</title>
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/navbar.css">
    <style>
        .dashboard-container {
            padding: 2rem 0;
            margin-top: 76px;
            min-height: calc(100vh - 76px);
            background: linear-gradient(135deg, var(--bg-color) 0%, var(--card-bg) 100%);
        }

        /* Dark tema için text-muted renk ayarı - sadece dashboard için */
        .dashboard-container [data-theme="dark"] .text-muted,
        [data-theme="dark"] .dashboard-container .text-muted {
            color: rgba(255, 255, 255, 0.7) !important;
        }

        [data-theme="dark"] .dashboard-container .who-reference small.text-muted {
            color: rgba(255, 255, 255, 0.7) !important;
        }

        [data-theme="dark"] .dashboard-container .action-description {
            color: rgba(255, 255, 255, 0.7) !important;
        }

        [data-theme="dark"] .dashboard-container .program-details {
            color: rgba(255, 255, 255, 0.7) !important;
        }

        .welcome-section {
        text-align: center;
            margin-bottom: 3rem;
            position: relative;
        }

        .welcome-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            background: linear-gradient(45deg, var(--primary-btn-bg), var(--secondary-btn-bg));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: titleAnimation 2s ease-in-out infinite;
        }

        .dashboard-card {
            background: var(--card-bg);
        border-radius: 15px;
            padding: 2rem;
            height: 100%;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            box-shadow: 0 4px 6px var(--shadow-color);
        }

        .dashboard-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }

        .dashboard-card:hover {
            transform: translateY(-10px);
            border-color: var(--primary-btn-bg);
            box-shadow: 0 10px 20px var(--shadow-color);
        }

        .dashboard-card:hover::before {
            transform: translateX(100%);
        }

        .card-icon {
            font-size: 2.5rem;
            color: var(--primary-btn-bg);
            margin-bottom: 1.5rem;
        transition: all 0.3s ease;
    }

        .dashboard-card:hover .card-icon {
            transform: scale(1.2) rotate(10deg);
            color: var(--secondary-btn-bg);
        }

        .card-title {
            font-size: 1.25rem;
            margin-bottom: 1rem;
            color: var(--text-color);
            font-weight: 600;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: linear-gradient(45deg, var(--primary-btn-bg), var(--secondary-btn-bg));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .progress-container {
            margin-top: 1rem;
        }

        .progress {
            height: 1rem;
            border-radius: 0.5rem;
            background: var(--bg-color);
            overflow: hidden;
            position: relative;
        }

        .progress-bar {
            background: linear-gradient(45deg, var(--primary-btn-bg), var(--secondary-btn-bg));
            position: relative;
            overflow: hidden;
            transition: width 1s ease;
        }

        .progress-bar::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 200%;
            height: 100%;
            background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            animation: shimmer 2s infinite;
        }

        .program-list {
            margin-top: 2rem;
        }

        .program-item {
            background: var(--card-bg);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }

        .program-item:hover {
            transform: translateX(10px);
            border-color: var(--primary-btn-bg);
            box-shadow: 0 4px 6px var(--shadow-color);
        }

        .program-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-color);
        }

        .program-details {
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .bmi-category {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-weight: 600;
            margin-top: 1rem;
            background: linear-gradient(45deg, var(--primary-btn-bg), var(--secondary-btn-bg));
        color: #fff;
            animation: pulse 2s infinite;
        }

        @keyframes titleAnimation {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .chart-container {
            position: relative;
            margin-top: 1rem;
            height: 300px;
        }

        @media (max-width: 768px) {
            .welcome-title {
                font-size: 2rem;
            }

            .stat-value {
                font-size: 1.5rem;
            }

            .dashboard-card {
                margin-bottom: 1rem;
            }
        }

        .action-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            margin-bottom: 1rem;
        }

        .action-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary-btn-bg);
            box-shadow: 0 8px 15px var(--shadow-color);
        }

        .action-icon {
            font-size: 2.5rem;
            color: var(--primary-btn-bg);
            margin-bottom: 1rem;
        }

        .action-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-color);
        }

        .action-description {
            font-size: 0.9rem;
            color: var(--text-muted);
            margin-bottom: 1rem;
        }

        .action-btn {
            display: inline-block;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            background: linear-gradient(45deg, var(--primary-btn-bg), var(--secondary-btn-bg));
        color: #fff;
            text-decoration: none;
        transition: all 0.3s ease;
    }

        .action-btn:hover {
            transform: scale(1.05);
            color: #fff;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .who-reference {
            border-top: 1px solid var(--border-color);
            padding-top: 1rem;
        }

        .bmi-ranges {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            font-size: 0.85rem;
        }

        .bmi-range {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            background: var(--bg-color);
            color: var(--text-muted);
            transition: all 0.3s ease;
        }

        .bmi-range.active {
            background: linear-gradient(45deg, var(--primary-btn-bg), var(--secondary-btn-bg));
            color: #fff;
            transform: scale(1.02);
            box-shadow: 0 2px 4px var(--shadow-color);
    }
        .toast-container {
            z-index: 1090; /* Navbarın üzerinde olması için */
    }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <!-- Toastlar buraya eklenecek -->
    </div>

    <div class="dashboard-container">
        <div class="container">
            <!-- Welcome Section -->
            <div class="welcome-section" data-aos="fade-up">
                <h1 class="welcome-title">Hoş Geldin, <?php echo htmlspecialchars($user['name'] ?? $user['username']); ?>!</h1>
                <p class="text-muted">Bugün nasıl hissediyorsun? İşte fitness yolculuğundaki son durumun.</p>
            </div>

            <!-- Quick Actions -->
            <div class="row mb-4">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="action-card">
                        <i class="fas fa-user-edit action-icon"></i>
                        <h3 class="action-title">Profil Görüntüle</h3>
                        <p class="action-description">Profilinizi görüntüleyin ve güncelleyin</p>
                        <a href="profile.php" class="action-btn">Profili Görüntüle</a>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="action-card">
                        <i class="fas fa-comments action-icon"></i>
                        <h3 class="action-title">Geri Bildirim</h3>
                        <p class="action-description">Deneyiminizi bizimle paylaşın</p>
                        <a href="feedback.php" class="action-btn">Geri Bildirim Yaz</a>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="action-card">
                        <i class="fas fa-calculator action-icon"></i>
                        <h3 class="action-title">BMI Hesapla</h3>
                        <p class="action-description">Vücut kitle indeksinizi hesaplayın</p>
                        <a href="calculate_bmi.php" class="action-btn">BMI Hesapla</a>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="row">
                <!-- BMI Card -->
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="dashboard-card">
                        <i class="fas <?php echo $bmi_icon; ?> card-icon"></i>
                        <h3 class="card-title">Vücut Kitle İndeksi (BMI)</h3>
                        <div class="stat-value"><?php echo $bmi; ?></div>
                        <div class="bmi-category">
                            <?php echo $bmi_category; ?>
                        </div>
                        <div class="who-reference mt-3">
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> WHO Sınıflandırması:
                            </small>
                            <div class="bmi-ranges mt-2">
                                <span class="bmi-range <?php echo $bmi < 18.5 ? 'active' : ''; ?>">Zayıf (<18.5)</span>
                                <span class="bmi-range <?php echo $bmi >= 18.5 && $bmi < 25 ? 'active' : ''; ?>">Normal (18.5-24.9)</span>
                                <span class="bmi-range <?php echo $bmi >= 25 && $bmi < 30 ? 'active' : ''; ?>">Kilolu (25-29.9)</span>
                                <span class="bmi-range <?php echo $bmi >= 30 ? 'active' : ''; ?>">Obez (≥30)</span>
                            </div>
                        </div>
                    </div>
                        </div>

                <!-- Weight Progress Card -->
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="dashboard-card">
                        <i class="fas fa-chart-line card-icon"></i>
                        <h3 class="card-title">Kilo İlerlemesi</h3>
                        <div class="stat-value"><?php echo $user['weight']; ?> kg</div>
                        <div class="progress-container">
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: <?php echo $progress_percent; ?>%"></div>
                            </div>
                            <p class="mt-2">Hedefe İlerleme: %<?php echo $progress_percent; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Target Weight Card -->
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="dashboard-card">
                        <i class="fas fa-bullseye card-icon"></i>
                        <h3 class="card-title">Hedef Kilo</h3>
                        <div class="stat-value"><?php echo $user['target_weight']; ?> kg</div>
                        <p>Başlangıç: <?php echo $user['initial_weight']; ?> kg</p>
                                        </div>
                                    </div>
                                        </div>

            <!-- Programs Section -->
            <div class="row mt-4">
                <div class="col-md-6 mb-4">
                    <div class="dashboard-card" data-aos="fade-up" data-aos-delay="300">
                        <i class="fas fa-dumbbell card-icon"></i>
                        <h3 class="card-title">Antrenman Programım</h3>
                        <?php if ($missing_info): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Antrenman programı oluşturmak için eksik bilgilerinizi tamamlamanız gerekiyor:
                                <ul class="mt-2 mb-0">
                                    <?php foreach ($missing_fields as $field): ?>
                                        <li><?php echo ucfirst($field); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <a href="calculate_bmi.php" class="btn btn-primary w-100">
                                <i class="fas fa-calculator me-2"></i>Eksik Bilgileri Tamamla
                            </a>
                        <?php elseif ($last_program): ?>
                            <h6 class="card-title"><?php echo htmlspecialchars($last_program['title']); ?></h6>
                            <p class="card-text">
                                <small class="text-muted">
                                    Eğitmen: <?php echo htmlspecialchars($last_program['trainer_name']); ?><br>
                                    Oluşturulma: <?php echo date('d.m.Y', strtotime($last_program['created_at'])); ?>
                                </small>
                            </p>
                            <a href="view_program.php?id=<?php echo $last_program['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-dumbbell me-2"></i>Programı Görüntüle
                            </a>
                        <?php else: ?>
                            <p class="text-muted mb-0">Henüz bir programınız bulunmuyor.</p>
                            <a href="create_program.php" class="btn btn-primary mt-3">
                                <i class="fas fa-plus me-2"></i>Yeni Program Oluştur
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Weight Chart -->
            <div class="row mt-4">
                <div class="col-12" data-aos="fade-up">
                    <div class="dashboard-card">
                        <h3 class="card-title">
                            <i class="fas fa-chart-area card-icon"></i>
                            Kilo Takibi
                        </h3>
                        <div class="chart-container">
                            <canvas id="weightChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/theme.js"></script>
    <script>
        // AOS Animasyonları
        AOS.init({
            duration: 1000,
            once: true
        });

        // Bildirim göster fonksiyonu (view_program.php'den kopyalandı)
        function showToast(message, type) {
            const toastContainer = document.querySelector('.toast-container');
            if (!toastContainer) return;

            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-white bg-${type === 'error' ? 'danger' : type} border-0`; 
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'assertive');
            toast.setAttribute('aria-atomic', 'true');

            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'} me-2"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            `;

            toastContainer.appendChild(toast);
            
            const bsToast = new bootstrap.Toast(toast, { 
                delay: 3000 // 3 saniye sonra otomatik kapanma
             });
            bsToast.show();

            toast.addEventListener('hidden.bs.toast', () => {
                toast.remove();
            });
        }

        // Sayfa yüklendiğinde session'daki mesajı kontrol et ve göster
        document.addEventListener('DOMContentLoaded', function() {
            <?php
            if (isset($_SESSION['message'])) {
                $message = json_encode($_SESSION['message']); // JS için encode et
                $message_type = json_encode($_SESSION['message_type'] ?? 'info'); // Tür yoksa info varsay
                echo "showToast($message, $message_type);";
                unset($_SESSION['message']); // Mesajı gösterdikten sonra sil
                unset($_SESSION['message_type']);
            }
            ?>
             // Kilo takip grafiği kodunu buraya taşı (DOMContentLoaded içine)
            const ctx = document.getElementById('weightChart').getContext('2d');
            const weightChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Başlangıç', 'Hafta 1', 'Hafta 2', 'Hafta 3', 'Hafta 4'], // Daha dinamik etiketler eklenebilir
                    datasets: [{
                        label: 'Kilo Takibi',
                        data: [
                            <?php echo $user['initial_weight'] ?? 'null'; ?>, // NULL ise JS null
                            <?php echo $user['weight'] ?? 'null'; ?> // Diğer haftalar için veri eklenebilir
                        ],
                        borderColor: getComputedStyle(document.documentElement).getPropertyValue('--primary-btn-bg').trim(),
                        tension: 0.4,
                        fill: true,
                        backgroundColor: 'rgba(var(--primary-btn-bg-rgb), 0.1)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: false,
                            grid: {
                                color: 'rgba(var(--text-color-rgb), 0.1)'
                            },
                            ticks: {
                                color: getComputedStyle(document.documentElement).getPropertyValue('--text-color').trim()
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(var(--text-color-rgb), 0.1)'
                            },
                            ticks: {
                                color: getComputedStyle(document.documentElement).getPropertyValue('--text-color').trim()
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>