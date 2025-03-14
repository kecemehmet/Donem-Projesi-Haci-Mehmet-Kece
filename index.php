<?php
session_start(); // Oturumu başlat
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0"); // Önbellek önleme
header("Pragma: no-cache");
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fitness App - Anasayfa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            display: flex;
            flex-direction: column;
        }
        .content {
            flex: 1;
            padding-bottom: 60px;
        }
        footer {
            flex-shrink: 0;
        }
        .hero-section {
            background: url('https://images.unsplash.com/photo-1517836357463-d25dfeac3438') no-repeat center center;
            background-size: cover;
            color: white;
            text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.7);
            padding: 100px 0;
        }
        .feature-card {
            transition: transform 0.2s;
        }
        .feature-card:hover {
            transform: scale(1.05);
        }
        .navbar-logo {
    height: 80px; /* Logoyu büyütmek için yüksekliği artırdık */
    width: 250px; /* Genişliği orantılı tut */
}
    </style>
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <img src="images/logo1.png" alt="Fitness App Logo" class="navbar-logo">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Anasayfa</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['username'])): ?>
                        <!-- Kullanıcı oturum açmışsa -->
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">Hoş Geldin, <?php echo $_SESSION['username']; ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Çıkış Yap</a>
                        </li>
                    <?php else: ?>
                        <!-- Kullanıcı oturum açmamışsa -->
                        <li class="nav-item">
                            <a class="nav-link" href="register.html">Kayıt Ol</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Giriş Yap</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section text-center">
        <div class="container">
            <h1 class="display-4">FitMate Hoş Geldiniz!</h1>
            <p class="lead">Kişisel fitness hedeflerinize ulaşmak için size özel antrenman programları sunuyoruz.</p>
            <?php if (isset($_SESSION['username'])): ?>
                <a href="dashboard.php" class="btn btn-primary btn-lg mt-3">Dashboard</a>
            <?php else: ?>
                <a href="register.html" class="btn btn-primary btn-lg mt-3">Hemen Kayıt Ol</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Özellikler -->
    <div class="content">
        <div class="container my-5">
            <h2 class="text-center mb-4">Neden FitMate?</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card feature-card shadow text-center">
                        <img src="https://images.unsplash.com/photo-1517838277536-f5f99be501cd" class="card-img-top" alt="Kişisel Program" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title">Kişisel Programlar</h5>
                            <p class="card-text">Hedeflerinize ve seviyenize uygun özel antrenman planları.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card feature-card shadow text-center">
                        <img src="https://images.unsplash.com/photo-1534438327276-14e5300c3a48" class="card-img-top" alt="Esnek Seçenekler" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title">Esnek Seçenekler</h5>
                            <p class="card-text">Kardiyo, kuvvet veya takım sporları arasından seçim yapın.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card feature-card shadow text-center">
                        <img src="https://images.unsplash.com/photo-1576678927484-cc907957088c" class="card-img-top" alt="Kolay Kullanım" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title">Kolay Kullanım</h5>
                            <p class="card-text">Profilinizi güncelleyin, programınızı hemen alın.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-4">
        <div class="container">
            <p class="mb-0">© 2025 FitMate. Tüm hakları saklıdır.</p>
            <p class="mb-0">İletişim: info@fitnessapp.com | Tel: 0123 456 789</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>