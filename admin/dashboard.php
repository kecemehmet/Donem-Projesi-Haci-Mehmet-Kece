<?php
// Bu dosya admin.php tarafından include edilir
// Gerekli admin kontrolleri admin.php'de yapıldı varsayılıyor

// Veritabanından hızlı istatistikler çekilebilir (opsiyonel)
// Örnek: Toplam kullanıcı sayısı
$total_users_query = $conn->query("SELECT COUNT(*) as count FROM users");
$total_users = $total_users_query ? $total_users_query->fetch_assoc()['count'] : 0;

// Örnek: Toplam program sayısı
$total_programs_query = $conn->query("SELECT COUNT(*) as count FROM programs");
$total_programs = $total_programs_query ? $total_programs_query->fetch_assoc()['count'] : 0;

// Örnek: Yeni geri bildirim sayısı
$new_feedback_query = $conn->query("SELECT COUNT(*) as count FROM feedback WHERE created_at > DATE_SUB(NOW(), INTERVAL 1 DAY)");
$new_feedback = $new_feedback_query ? $new_feedback_query->fetch_assoc()['count'] : 0;

// Son 7 günlük kullanıcı kayıtları
$user_registrations_query = $conn->query("
    SELECT DATE(created_at) as date, COUNT(*) as count 
    FROM users 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date
");
$user_registrations = [];
$dates = [];
while ($row = $user_registrations_query->fetch_assoc()) {
    $user_registrations[] = $row['count'];
    $dates[] = date('d M', strtotime($row['date']));
}

// Son 7 günlük program oluşturma istatistiklerini al
$programQuery = "
    SELECT 
        DATE(created_at) as date,
        COUNT(*) as count
    FROM programs
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date ASC
";
$programStats = $conn->query($programQuery);
$programData = [];
while ($row = $programStats->fetch_assoc()) {
    $programData[$row['date']] = $row['count'];
}

// Son 7 günlük geri bildirim istatistiklerini al
$feedbackQuery = "
    SELECT 
        DATE(created_at) as date,
        COUNT(*) as count
    FROM feedback
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date ASC
";
$feedbackStats = $conn->query($feedbackQuery);
$feedbackData = [];
while ($row = $feedbackStats->fetch_assoc()) {
    $feedbackData[$row['date']] = $row['count'];
}

// Son 7 günün tarihlerini oluştur
$dates = [];
$userCounts = [];
$programCounts = [];
$feedbackCounts = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $dates[] = date('d.m', strtotime($date));
    $userCounts[] = isset($user_registrations[$i]) ? $user_registrations[$i] : 0;
    $programCounts[] = isset($programData[$date]) ? $programData[$date] : 0;
    $feedbackCounts[] = isset($feedbackData[$date]) ? $feedbackData[$date] : 0;
}

// Kullanıcı deneyim seviyeleri
$experience_levels_query = $conn->query("
    SELECT 
        CASE 
            WHEN experience_level = 'beginner' THEN 'Başlangıç'
            WHEN experience_level = 'intermediate' THEN 'Orta Seviye'
            WHEN experience_level = 'advanced' THEN 'İleri Seviye'
            ELSE experience_level
        END as experience_level,
        COUNT(*) as count 
    FROM users 
    WHERE experience_level IS NOT NULL AND experience_level != ''
    GROUP BY experience_level
");
$experience_levels = [];
$experience_counts = [];
while ($row = $experience_levels_query->fetch_assoc()) {
    $experience_levels[] = $row['experience_level'];
    $experience_counts[] = $row['count'];
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Admin Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary">Paylaş</button>
            <button type="button" class="btn btn-sm btn-outline-secondary">Dışa Aktar</button>
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle">
            <i class="fas fa-calendar-alt"></i> Bu Hafta
        </button>
    </div>
</div>

<!-- İstatistik Kartları -->
<div class="row mb-4">
    <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
        <div class="stat-card admin-stat-card text-center p-3">
            <i class="fas fa-users fa-2x mb-3 text-primary"></i>
            <h4>Toplam Kullanıcı</h4>
            <p class="fs-3 fw-bold"><?php echo $total_users; ?></p>
        </div>
    </div>
    <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
        <div class="stat-card admin-stat-card text-center p-3">
            <i class="fas fa-dumbbell fa-2x mb-3 text-success"></i>
            <h4>Toplam Program</h4>
            <p class="fs-3 fw-bold"><?php echo $total_programs; ?></p>
        </div>
    </div>
    <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
        <div class="stat-card admin-stat-card text-center p-3">
            <i class="fas fa-comment-dots fa-2x mb-3 text-warning"></i>
            <h4>Yeni Geri Bildirim</h4>
            <p class="fs-3 fw-bold"><?php echo $new_feedback; ?></p>
        </div>
    </div>
</div>

<!-- Grafikler -->
<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0" style="color: white !important;">Son 7 Günlük İstatistikler</h6>
            </div>
            <div class="card-body">
                <canvas id="userProgramChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Kullanıcı Deneyim Seviyeleri</h6>
            </div>
            <div class="card-body">
                <canvas id="experienceChart"></canvas>
            </div>
        </div>
    </div>
</div>

<style>
.admin-stat-card {
    background: var(--card-bg);
    border-radius: 15px;
    padding: 1.5rem;
    text-align: center;
    transition: all 0.3s ease;
    border: 1px solid var(--border-color);
    margin-bottom: 1rem;
    box-shadow: var(--card-shadow);
}

.admin-stat-card:hover {
    transform: translateY(-5px);
    border-color: var(--primary-btn-bg);
    box-shadow: 0 8px 15px var(--shadow-color);
}

.card {
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 15px;
    box-shadow: var(--card-shadow);
}

.card-title {
    color: var(--text-color);
    margin-bottom: 1rem;
}

/* Chart container için ek stiller */
.card-body {
    padding: 1.5rem;
    color: var(--text-color);
}

.card-body > div {
    position: relative;
    width: 100%;
    height: 300px;
}

.card-header {
    background: var(--card-bg);
    border-bottom: 1px solid var(--border-color);
}

.card-header h6.text-white {
    color: #ffffff !important;
    margin: 0;
    padding: 1rem;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Kullanıcı ve Program Grafiği
    const userProgramCtx = document.getElementById('userProgramChart').getContext('2d');
    new Chart(userProgramCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($dates); ?>,
            datasets: [
                {
                    label: 'Kullanıcı Kayıtları',
                    data: <?php echo json_encode($userCounts); ?>,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    tension: 0.1,
                    fill: true
                },
                {
                    label: 'Program Oluşturma',
                    data: <?php echo json_encode($programCounts); ?>,
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.1)',
                    tension: 0.1,
                    fill: true
                },
                {
                    label: 'Geri Bildirimler',
                    data: <?php echo json_encode($feedbackCounts); ?>,
                    borderColor: 'rgb(255, 205, 86)',
                    backgroundColor: 'rgba(255, 205, 86, 0.1)',
                    tension: 0.1,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            backgroundColor: 'transparent',
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        color: 'white',
                        font: {
                            size: 12,
                            weight: 'bold'
                        }
                    }
                },
                title: {
                    display: true,
                    text: 'Son 7 Günlük İstatistikler',
                    color: 'white',
                    font: {
                        size: 14,
                        weight: 'bold'
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        color: 'white',
                        font: {
                            size: 12,
                            weight: 'bold'
                        }
                    },
                    grid: {
                        color: 'var(--border-color)',
                        drawBorder: false
                    }
                },
                x: {
                    ticks: {
                        color: 'white',
                        font: {
                            size: 12,
                            weight: 'bold'
                        }
                    },
                    grid: {
                        color: 'var(--border-color)',
                        drawBorder: false
                    }
                }
            }
        }
    });

    // Deneyim Seviyeleri Grafiği
    const experienceCtx = document.getElementById('experienceChart').getContext('2d');
    new Chart(experienceCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($experience_levels); ?>,
            datasets: [{
                data: <?php echo json_encode($experience_counts); ?>,
                backgroundColor: [
                    'rgb(255, 99, 132)',
                    'rgb(54, 162, 235)',
                    'rgb(255, 205, 86)',
                    'rgb(75, 192, 192)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        color: 'white'
                    }
                },
                title: {
                    display: true,
                    text: 'Kullanıcı Deneyim Seviyeleri',
                    color: 'white'
                }
            },
            cutout: '70%'
        }
    });
});
</script> 