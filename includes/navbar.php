<?php
$current_page = basename($_SERVER['PHP_SELF']);
$theme_class = isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'light' ? 'navbar-light' : 'navbar-dark';
?>
<nav class="navbar navbar-expand-lg <?php echo $theme_class; ?>">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <img src="/images/logo2.png" alt="FitMate Logo" width="40" height="40" class="me-2">
            <span class="brand-text">FitMate</span>
        </a>
        <style>
            .theme-toggle i {
                transition: transform 0.3s ease;
            }
            .theme-toggle:hover i {
                transform: rotate(180deg);
            }
            @media (min-width: 992px) {
                .theme-toggle-mobile {
                    display: none;
                }
            }
            @media (max-width: 991px) {
                .theme-toggle-desktop {
                    display: none;
                }
            }
            .mobile-menu {
                display: flex;
                align-items: center;
                gap: 1rem;
            }
            .theme-toggle {
                color: var(--text-color);
                padding: 0.5rem;
                border: none;
                background: none;
                cursor: pointer;
                transition: transform 0.3s ease;
            }
            .theme-toggle:hover {
                transform: rotate(180deg);
            }
            @media (max-width: 767.98px) {
                .navbar-nav .theme-toggle {
                    display: none;
                }
            }
            @media (min-width: 768px) {
                .mobile-menu .theme-toggle {
                    display: none;
                }
            }
        </style>
        <div class="d-flex align-items-center ms-auto">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>" href="/index.php">
                        <i class="fas fa-home"></i> Ana Sayfa
                    </a>
                </li>
                <?php if (isset($_SESSION['username'])): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" href="/dashboard.php">
                            <i class="fas fa-tachometer-alt"></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'view_program.php' ? 'active' : ''; ?>" href="/view_program.php">
                            <i class="fas fa-dumbbell me-2"></i>Antrenman Programım
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'profile.php' ? 'active' : ''; ?>" href="/profile.php">
                            <i class="fas fa-user me-2"></i>Profilim
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'calculate_bmi.php' ? 'active' : ''; ?>" href="/calculate_bmi.php">
                            <i class="fas fa-calculator"></i> BMI Hesapla
                        </a>
                    </li>
                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="http://localhost/admin.php">
                                <i class="fas fa-user-shield me-2"></i>Admin
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
            <?php if (isset($_SESSION['username'])): ?>
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <button class="btn btn-link me-2 theme-toggle" id="theme-toggle" title="Tema Değiştir">
                            <i class="fas fa-sun"></i>
                        </button>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt"></i> Çıkış
                        </a>
                    </li>
                </ul>
            <?php else: ?>
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'register.php' ? 'active' : ''; ?>" href="register.php">
                            <i class="fas fa-user-plus"></i> Kayıt Ol
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page == 'login.php' ? 'active' : ''; ?>" href="login.php">
                            <i class="fas fa-sign-in-alt"></i> Giriş Yap
                        </a>
                    </li>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- Mobil Menü -->
<div class="mobile-menu d-md-none">
    <button class="btn btn-link nav-link theme-toggle" id="theme-toggle-mobile" title="Tema Değiştir">
        <i class="fas fa-moon"></i>
    </button>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <i class="fas fa-bars"></i>
    </button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const themeToggle = document.getElementById('theme-toggle');
    const html = document.documentElement;
    const icon = themeToggle.querySelector('i');

    // Tema durumunu kontrol et
    if (localStorage.getItem('theme') === 'dark') {
        html.setAttribute('data-theme', 'dark');
        icon.classList.replace('fa-moon', 'fa-sun');
    } else {
        html.removeAttribute('data-theme');
        icon.classList.replace('fa-sun', 'fa-moon');
    }

    // Tema değiştirme fonksiyonu
    function toggleTheme() {
        if (html.getAttribute('data-theme') === 'dark') {
            html.removeAttribute('data-theme');
            localStorage.setItem('theme', 'light');
            icon.classList.replace('fa-sun', 'fa-moon');
        } else {
            html.setAttribute('data-theme', 'dark');
            localStorage.setItem('theme', 'dark');
            icon.classList.replace('fa-moon', 'fa-sun');
        }
    }

    // Tema değiştirme butonuna event listener ekle
    themeToggle.addEventListener('click', toggleTheme);
});
</script> 