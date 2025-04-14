<?php
require_once 'db_connection.php';

// Site ayarlarını al
$site_settings = $conn->query("SELECT * FROM site_settings WHERE id = 1")->fetch_assoc();
$site_title = $site_settings['site_title'] ?? 'FitMate';
$site_description = $site_settings['site_description'] ?? 'FitMate - Kişisel Fitness Asistanınız';
$contact_email = $site_settings['contact_email'] ?? 'admin@fitmate.com';
?>
<!DOCTYPE html>
<html lang="tr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($site_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($site_description); ?>">
    <meta name="author" content="FitMate">
    <meta name="contact" content="<?php echo htmlspecialchars($contact_email); ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- AOS Animasyon -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    
    <!-- Dark Mode CSS -->
    <link rel="stylesheet" href="css/dark-mode.css">
</head>
<body>
    <!-- Navbar -->
    <?php include 'navbar.php'; ?>

    <div class="d-flex align-items-center">
        <button class="btn btn-link me-2" id="themeToggle">
            <i class="fas fa-moon"></i>
        </button>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 