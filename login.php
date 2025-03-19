<?php
session_start(); // Oturumu başlat

$servername = "localhost";
$username = "root"; // Varsayılan kullanıcı adı
$password = ""; // Varsayılan şifre
$dbname = "fitness_db";

// Veritabanı bağlantısı
$conn = new mysqli($servername, $username, $password, $dbname);

// Bağlantı kontrolü
if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

// Form gönderildiğinde işlem yap
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Kullanıcıyı bul
    $sql = "SELECT * FROM users WHERE username='$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['username'] = $username; // Oturum başlat
            header("Location: dashboard.php"); // Dashboard’a yönlendir
            exit();
        } else {
            $error = "Hatalı şifre!";
        }
    } else {
        $error = "Kullanıcı bulunamadı!";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitMate - Giriş Yap</title>
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- AOS Animasyon Kütüphanesi -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Google Fonts (Modern bir font için) -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <!-- Font Awesome (Simgeler için) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Genel Stil */
        html, body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh; /* Sayfanın minimum yüksekliğini tam ekran yapar */
        }

        body {
            display: flex;
            flex-direction: column;
        }

        .content {
            flex: 1 0 auto; /* İçeriğin flex büyümesini sağlar */
            padding-bottom: 60px;
        }

        footer {
            flex-shrink: 0; /* Footer'ın küçülmesini engeller */
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

        .navbar-brand {
            padding: 5px 15px;
            display: flex;
            align-items: center;
        }

        .nav-link {
            color: #fff !important;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .nav-link:hover {
            color: #00ddeb !important;
        }

        /* Giriş Formu */
        .login-section {
            padding: 60px 0;
        }

        .login-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            background: #fff;
            padding: 30px;
        }

        .login-card h2 {
            font-size: 2rem;
            font-weight: 600;
            color: #1a3c34;
            margin-bottom: 20px;
        }

        .alert {
            border-radius: 10px;
            font-size: 0.9rem;
        }

        .form-label {
            font-weight: 500;
            color: #1a3c34;
        }

        .form-control {
            border-radius: 10px;
            border: 1px solid #ced4da;
            padding: 10px;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-control:focus {
            border-color: #00ddeb;
            box-shadow: 0 0 5px rgba(0, 221, 235, 0.3);
            outline: none;
        }

        .btn-primary {
            background: #00ddeb;
            border: none;
            padding: 12px 30px;
            font-size: 1.1rem;
            font-weight: 500;
            border-radius: 50px;
            transition: transform 0.3s ease, background 0.3s ease;
        }

        .btn-primary:hover {
            background: #00b7c2;
            transform: translateY(-3px);
        }

        .text-center a {
            color: #00ddeb;
            text-decoration: none;
            font-weight: 500;
        }

        .text-center a:hover {
            text-decoration: underline;
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

            .login-card h2 {
                font-size: 1.8rem;
            }

            .login-section {
                padding: 40px 0;
            }
        }

        @media (max-width: 576px) {
            .navbar-logo {
                height: 40px;
                max-width: 150px;
            }

            .login-card {
                padding: 20px;
            }

            .login-card h2 {
                font-size: 1.5rem;
            }

            .login-section {
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
                </ul>
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['username'])): ?>
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
                        <li class="nav-item">
                            <a class="nav-link" href="register.html">Kayıt Ol</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="login.php">Giriş Yap</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Giriş Formu -->
    <div class="content">
        <div class="container login-section">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card login-card" data-aos="fade-up" data-aos-duration="1000">
                        <div class="card-body">
                            <h2 class="text-center">Giriş Yap</h2>
                            <?php if (isset($error)) { ?>
                                <div class="alert alert-danger" role="alert">
                                    <?php echo $error; ?>
                                </div>
                            <?php } ?>
                            <form action="login.php" method="POST">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Kullanıcı Adı</label>
                                    <input type="text" class="form-control" id="username" name="username" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Şifre</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Giriş Yap</button>
                            </form>
                            <p class="mt-3 text-center">Hesabınız yok mu? <a href="register.html">Kayıt Ol</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
    <!-- AOS Animasyon Kütüphanesi -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // AOS Animasyonlarını Başlat
        AOS.init({
            once: false, // Animasyonlar her kaydırmada tekrar oynar
            offset: 50, // Animasyonun tetiklenme mesafesini azaltır
            duration: 1000 // Animasyon süresi
        });

        // Sayfanın yüklenmesi tamamlandığında AOS'u yenile
        window.addEventListener('load', function() {
            AOS.refresh();
        });

        // Sayfanın boyutları değiştiğinde AOS'u yenile
        window.addEventListener('resize', function() {
            AOS.refresh();
        });

        // Sayfayı kaydırdığında AOS'u yenile
        window.addEventListener('scroll', function() {
            AOS.refresh();
        });
    </script>
</body>
</html>