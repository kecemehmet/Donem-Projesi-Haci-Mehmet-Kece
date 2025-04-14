<?php
session_start();
require_once 'includes/db_connection.php';

// Admin kontrolü
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: index.php");
    exit();
}

// Aktif sekmeyi belirle
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';
?>

<!DOCTYPE html>
<html lang="tr" data-theme="<?php echo isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitMate - Admin Paneli</title>
    <link rel="icon" type="image/x-icon" href="/images/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/css/styles.css">
    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
    <style>
        /* Logo Stili */
        .navbar-brand img {
            height: 40px;
            width: auto;
        }

        /* Sidebar Dark Mode */
        [data-theme="dark"] .sidebar {
            background-color: var(--card-bg) !important;
            border-right: 1px solid var(--border-color);
        }

        [data-theme="dark"] .sidebar .nav-link {
            color: var(--text-color) !important;
        }

        [data-theme="dark"] .sidebar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        [data-theme="dark"] .sidebar .nav-link.active {
            background-color: var(--primary-btn-bg);
            color: white !important;
        }

        /* Dashboard başlık rengi */
        .card-header h6 {
            color: white !important;
        }

        /* Sidebar Genel Stiller */
        .sidebar {
            position: fixed;
            top: 68px;
            left: -240px; /* Başlangıçta gizli */
            bottom: 0;
            width: 240px;
            z-index: 100;
            padding: 1rem 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
            transition: all 0.3s ease;
            overflow-y: auto;
            background-color: var(--card-bg);
        }

        .sidebar.active {
            left: 0; /* Aktif olduğunda görünür */
        }

        .sidebar .nav-link {
            font-weight: 500;
            color: var(--text-color);
            padding: 0.5rem 1rem;
            margin: 0.2rem 0;
            border-radius: 0.25rem;
            transition: all 0.3s ease;
        }

        .sidebar .nav-link:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }

        .sidebar .nav-link.active {
            background-color: var(--primary-btn-bg);
            color: white !important;
        }

        .sidebar .nav-link i {
            margin-right: 0.5rem;
            width: 1.5rem;
            text-align: center;
        }

        /* Ana içerik alanı için margin */
        .main-content {
            margin-left: 0; /* Başlangıçta margin yok */
            padding-top: 68px;
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        .main-content.sidebar-active {
            margin-left: 240px; /* Sidebar aktif olduğunda margin ekle */
        }

        /* Toggle Buton Stilleri */
        .sidebar-toggle {
            position: fixed;
            top: 80px;
            left: 20px;
            z-index: 101;
            background: var(--primary-btn-bg);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .sidebar-toggle:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 8px rgba(0,0,0,0.3);
        }

        .sidebar-toggle i {
            transition: transform 0.3s ease;
        }

        .sidebar-toggle.active i {
            transform: rotate(180deg);
        }

        /* Responsive Düzenlemeler */
        @media (max-width: 991.98px) {
            .sidebar {
                position: fixed;
                left: -240px;
            }

            .sidebar.active {
                left: 0;
            }

            .main-content {
                margin-left: 0;
            }

            .main-content.sidebar-active {
                margin-left: 240px;
            }

            .sidebar-toggle {
                display: flex;
            }
        }

        @media (max-width: 767.98px) {
            .sidebar {
                position: fixed;
                left: -100%;
                width: 100%;
                top: 56px;
            }

            .sidebar.active {
                left: 0;
            }

            .main-content {
                margin-left: 0;
                padding-top: 56px;
            }

            .main-content.sidebar-active {
                margin-left: 0;
            }

            .sidebar-toggle {
                top: 68px;
                left: 15px;
            }

            /* Mobilde sidebar açıkken arka planı karartma */
            .sidebar.active + .main-content::before {
                content: '';
                position: fixed;
                top: 56px;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 99;
            }
        }

        /* Beyaz tema için genel yazı rengi */
        [data-theme='light'] {
            color: #000;
        }
        [data-theme='light'] .sidebar .nav-link,
        [data-theme='light'] .sidebar .nav-link.active,
        [data-theme='light'] .sidebar .nav-link:hover {
            color: #000 !important;
        }

        /* Beyaz tema için charts.js yazı rengi */
        [data-theme='light'] .chartjs-render-monitor {
            color: #000;
        }
    </style>
</head>
<body class="admin-panel">
    <?php include 'includes/navbar.php'; ?>

    <!-- Sidebar Toggle Butonu -->
    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-chevron-right"></i>
    </button>

    <!-- Sidebar -->
    <nav id="sidebar" class="sidebar">
        <div class="position-sticky">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link <?php echo $active_tab == 'dashboard' ? 'active' : ''; ?>" href="/admin/dashboard">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $active_tab == 'users' ? 'active' : ''; ?>" href="/admin/users">
                        <i class="fas fa-users"></i>
                        Kullanıcılar
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $active_tab == 'programs' ? 'active' : ''; ?>" href="/admin/programs">
                        <i class="fas fa-dumbbell"></i>
                        Programlar
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $active_tab == 'feedback' ? 'active' : ''; ?>" href="/admin/feedback">
                        <i class="fas fa-comments"></i>
                        Geri Bildirimler
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Ana İçerik -->
    <div class="main-content">
        <div class="container-fluid">
            <?php
            // İlgili sekmenin içeriğini yükle
            $tab_file = 'admin/' . $active_tab . '.php';
            if (file_exists($tab_file)) {
                include $tab_file;
            } else {
                echo '<div class="alert alert-danger mt-3">Sayfa bulunamadı.</div>';
            }
            ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="/js/theme.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Base URL ayarı
        const baseUrl = '/';
        
        // AOS Animasyonları
        AOS.init({
            duration: 800,
            once: true
        });

        // Charts.js yazı rengi ayarı
        if (document.documentElement.getAttribute('data-theme') === 'light') {
            Chart.defaults.color = '#000';
        }

        // Sidebar Toggle İşlevi
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.querySelector('.main-content');

        // LocalStorage'dan sidebar durumunu al
        const sidebarState = localStorage.getItem('sidebarState');
        if (sidebarState === 'active') {
            sidebar.classList.add('active');
            mainContent.classList.add('sidebar-active');
            sidebarToggle.classList.add('active');
        }

        // Sidebar'ı kapatma fonksiyonu
        function closeSidebar() {
            sidebar.classList.remove('active');
            mainContent.classList.remove('sidebar-active');
            sidebarToggle.classList.remove('active');
            localStorage.setItem('sidebarState', 'inactive');
        }

        // Sidebar'ı açma fonksiyonu
        function openSidebar() {
            sidebar.classList.add('active');
            mainContent.classList.add('sidebar-active');
            sidebarToggle.classList.add('active');
            localStorage.setItem('sidebarState', 'active');
        }

        sidebarToggle.addEventListener('click', () => {
            if (sidebar.classList.contains('active')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });

        // Sidebar linklerine tıklandığında paneli kapat
        const sidebarLinks = document.querySelectorAll('.sidebar .nav-link');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', () => {
                closeSidebar();
            });
        });

        // Sayfa yüklendiğinde URL'deki hash'i kontrol et ve ilgili sekmeyi aç
        document.addEventListener('DOMContentLoaded', function() {
            const hash = window.location.hash.substring(1);
            if (hash) {
                const tab = document.querySelector(`a[href="?tab=${hash}"]`);
                if (tab) {
                    tab.click();
                }
            }
        });

        // Sekme değişikliklerini takip et
        const tabLinks = document.querySelectorAll('.nav-link');
        tabLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                const tab = this.getAttribute('href').split('=')[1];
                window.history.pushState({}, '', `?tab=${tab}`);
            });
        });
    </script>
</body>
</html>