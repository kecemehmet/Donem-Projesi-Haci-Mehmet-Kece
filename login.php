<?php
// Oturum süresini uzat (oturum başlatılmadan önce yapılmalı)
ini_set('session.gc_maxlifetime', 3600); // 1 saat
session_set_cookie_params(3600); // Çerez süresi 1 saat

session_start(); // Oturumu başlat

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fitness_db";

// Veritabanı bağlantısı
$conn = new mysqli($servername, $username, $password, $dbname);

// Bağlantı kontrolü
if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

if (isset($_SESSION['username'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';

if (isset($_SESSION['register_success'])) {
    $success = "Kayıt başarılı! Lütfen giriş yapın.";
    unset($_SESSION['register_success']);
}

// Form gönderildiğinde işlem yap
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    // Kullanıcıyı bul (Hazırlıklı sorgu ile)
    $stmt = $conn->prepare("SELECT id, password, is_admin, is_banned, profile_picture, name, height, weight, fitness_goal, experience_level FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['is_banned']) {
            $error = "Bu hesap yasaklanmıştır. Lütfen admin ile iletişime geçin.";
        } elseif (password_verify($password, $row['password'])) {
            $_SESSION['username'] = $username;
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['is_admin'] = $row['is_admin'];
            $_SESSION['profile_picture'] = $row['profile_picture'] ?? 'images/default_profile.png';
            $_SESSION['name'] = $row['name'];
            session_regenerate_id(true);
            
            // Kullanıcının BMI bilgileri boş mu kontrol et
            if (empty($row['height']) || empty($row['weight']) || empty($row['fitness_goal']) || empty($row['experience_level'])) {
                // BMI hesaplayıcıya yönlendir
                header("Location: calculate_bmi.php");
            } else {
                // Dashboard'a yönlendir
                header("Location: dashboard.php");
            }
            exit();
        } else {
            $error = "Hatalı şifre!";
        }
    } else {
        $error = "Kullanıcı bulunamadı!";
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="tr" data-theme="<?php echo isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitMate - Giriş Yap</title>
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/navbar.css">
    <style>
        .login-container {
            padding: 2rem 0;
            margin-top: 76px;
            min-height: calc(100vh - 76px);
            background: linear-gradient(135deg, var(--bg-color) 0%, var(--card-bg) 100%);
        }

        .login-header {
            text-align: center;
            margin-bottom: 3rem;
            position: relative;
        }

        .login-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            background: linear-gradient(45deg, var(--primary-btn-bg), var(--secondary-btn-bg));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: titleAnimation 2s ease-in-out infinite;
        }

        .login-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            border: 2px solid #000; /* Light modda siyah border */
            box-shadow: 0 4px 6px var(--shadow-color);
        }

        [data-theme='dark'] .login-card {
            border-color: #fff; /* Dark modda beyaz border */
        }

        .login-card::before {
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

        .login-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary-btn-bg);
            box-shadow: 0 10px 20px var(--shadow-color);
        }

        [data-theme='dark'] .login-card:hover {
            border-color: var(--primary-btn-bg);
        }

        .form-input {
            background: var(--input-bg);
            border: 2px solid #000; /* Light modda siyah border */
            color: var(--text-color);
            border-radius: 10px;
            padding: 0.8rem;
            width: 100%;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        [data-theme='dark'] .form-input {
            border-color: #fff; /* Dark modda beyaz border */
        }

        .form-input:focus {
            border-color: var(--primary-btn-bg);
            box-shadow: 0 0 0 0.25rem rgba(var(--primary-btn-bg-rgb), 0.25);
        }

        [data-theme='dark'] .form-input:focus {
            border-color: var(--primary-btn-bg);
            box-shadow: 0 0 0 0.25rem rgba(var(--primary-btn-bg-rgb), 0.25);
        }

        /* Input Group için border ayarı */
        .input-group .form-input {
            margin-bottom: 0;
        }

        .input-group {
            position: relative;
            margin-bottom: 1rem;
        }

        /* Dark modda başlık altındaki text-muted için renk */
        [data-theme='dark'] .login-header .text-muted {
            color: rgba(255, 255, 255, 0.8) !important;
        }

        .login-btn {
            background: linear-gradient(45deg, var(--primary-btn-bg), var(--secondary-btn-bg));
            border: none;
            color: #fff;
            padding: 1rem 2rem;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
            position: relative;
            overflow: hidden;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .login-btn::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 70%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .login-btn:hover::before {
            opacity: 1;
        }

        .success-message {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            animation: slideDown 0.5s ease-out;
        }

        .error-message {
            background: linear-gradient(45deg, #dc3545, #f86d6d);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            animation: slideDown 0.5s ease-out;
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text-muted);
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: var(--text-color);
        }

        @keyframes slideDown {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @keyframes titleAnimation {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        @media (max-width: 768px) {
            .login-title {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="login-container">
        <div class="container">
            <?php if ($error): ?>
            <div class="error-message" data-aos="fade-down">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="success-message" data-aos="fade-down">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $success; ?>
            </div>
            <?php endif; ?>

            <div class="login-header" data-aos="fade-up">
                <h1 class="login-title">Giriş Yap</h1>
                <p class="text-muted">FitMate hesabınıza giriş yapın ve sağlıklı yaşam yolculuğunuza devam edin</p>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-6">
                    <form action="login.php" method="POST" id="loginForm">
                        <div class="login-card" data-aos="fade-up" data-aos-delay="100">
                            <div class="mb-3">
                                <label for="username" class="form-label">Kullanıcı Adı</label>
                                <input type="text" class="form-input" id="username" name="username" required>
                            </div>

                            <div class="input-group">
                                <label for="password" class="form-label">Şifre</label>
                                <input type="password" class="form-input" id="password" name="password" required>
                                <i class="fas fa-eye password-toggle" onclick="togglePassword('password')"></i>
                            </div>

                            <button type="submit" class="login-btn">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Giriş Yap
                            </button>

                            <div class="text-center mt-3">
                                <p class="mb-0">Hesabınız yok mu? <a href="register.php" class="text-decoration-none">Kayıt Olun</a></p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="js/core.js"></script>
    <script src="js/theme.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            once: true
        });

        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = input.nextElementSibling;
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>