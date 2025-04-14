<?php
session_start();
require_once 'includes/db_connection.php';

if (isset($_SESSION['username'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Hata kontrolü
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Ad Soyad gerekli";
    }
    
    if (empty($username)) {
        $errors[] = "Kullanıcı adı gerekli";
    }
    
    if (empty($email)) {
        $errors[] = "E-posta adresi gerekli";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Geçerli bir e-posta adresi girin";
    }
    
    if (empty($password)) {
        $errors[] = "Şifre gerekli";
    } elseif (strlen($password) < 6) {
        $errors[] = "Şifre en az 6 karakter olmalı";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Şifreler eşleşmiyor";
    }
    
    // Kullanıcı adı ve e-posta kontrolü
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $errors[] = "Bu kullanıcı adı veya e-posta adresi zaten kullanılıyor";
    }
    $stmt->close();
    
    if (empty($errors)) {
        // Şifreyi hashle
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Kullanıcıyı kaydet
        $stmt = $conn->prepare("
            INSERT INTO users (name, username, email, password, created_at, fitness_goal, experience_level, height, weight, bmi)
            VALUES (?, ?, ?, ?, NOW(), '', '', 0, 0, 0)
        ");
        $stmt->bind_param("ssss", $name, $username, $email, $hashed_password);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Kayıt başarılı! Şimdi giriş yapabilirsiniz.";
            header("Location: login.php");
            exit;
        } else {
            $errors[] = "Kayıt sırasında bir hata oluştu";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="tr" data-theme="<?php echo isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitMate - Kayıt Ol</title>
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- AOS Animasyon Kütüphanesi -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Google Fonts (Modern bir font için) -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome (Simgeler için) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/navbar.css">
    <style>
        .register-container {
            padding: 2rem 0;
            margin-top: 76px;
            min-height: calc(100vh - 76px);
            background: linear-gradient(135deg, var(--bg-color) 0%, var(--card-bg) 100%);
        }

        .register-header {
            text-align: center;
            margin-bottom: 3rem;
            position: relative;
        }

        .register-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            background: linear-gradient(45deg, var(--primary-btn-bg), var(--secondary-btn-bg));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: titleAnimation 2s ease-in-out infinite;
        }

        .register-card {
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

        [data-theme='dark'] .register-card {
            border-color: #fff; /* Dark modda beyaz border */
        }

        .register-card:hover {
            transform: translateY(-5px);
            /* Hover durumunda tema rengini kullanmaya devam edebiliriz */
            border-color: var(--primary-btn-bg);
            box-shadow: 0 10px 20px var(--shadow-color);
        }

        [data-theme='dark'] .register-card:hover {
            /* Dark mod hover için de tema rengi */
            border-color: var(--primary-btn-bg);
        }

        .register-card::before {
            /* Bu pseudo-elementi isterseniz kaldırabilir veya stilini değiştirebilirsiniz */
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
            /* Dark modda focus rengi aynı kalabilir veya özelleştirilebilir */
            border-color: var(--primary-btn-bg);
            box-shadow: 0 0 0 0.25rem rgba(var(--primary-btn-bg-rgb), 0.25);
        }

        /* Input Group için border ayarı (şifre alanları) */
        .input-group .form-input {
            /* Grup içindeki inputun altındaki margin'i kaldır */
            margin-bottom: 0;
        }

        .input-group {
            position: relative;
            margin-bottom: 1rem;
            /* Grup olarak border eklemek istiyorsanız buraya ekleyebilirsiniz,
               ancak genellikle sadece inputa eklemek daha yaygındır. */
            /* border: 2px solid #000; */
            /* border-radius: 10px; */
        }

        .register-btn {
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

        .register-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .register-btn::before {
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

        .register-btn:hover::before {
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
            .register-title {
                font-size: 2rem;
            }
        }

        /* Dark modda başlık altındaki text-muted için renk */
        [data-theme='dark'] .register-header .text-muted {
            color: rgba(255, 255, 255, 0.8) !important; /* Tam beyaz yerine hafif soluk beyaz */
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="register-container">
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

            <div class="register-header" data-aos="fade-up">
                <h1 class="register-title">Kayıt Ol</h1>
                <p class="text-muted">FitMate ailesine katılın ve sağlıklı yaşam yolculuğunuza başlayın</p>
            </div>

            <div class="row justify-content-center">
                <div class="col-md-6">
                    <form action="register.php" method="POST" id="registerForm">
                        <div class="register-card" data-aos="fade-up" data-aos-delay="100">
                            <div class="mb-3">
                                <label for="name" class="form-label">Ad Soyad</label>
                                <input type="text" class="form-input" id="name" name="name" required>
                            </div>

                            <div class="mb-3">
                                <label for="username" class="form-label">Kullanıcı Adı</label>
                                <input type="text" class="form-input" id="username" name="username" required minlength="3" maxlength="20" pattern="[a-zA-Z0-9_]+" title="Sadece harf, rakam ve alt çizgi kullanabilirsiniz">
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">E-posta Adresi</label>
                                <input type="email" class="form-input" id="email" name="email" required>
                            </div>

                            <div class="input-group">
                                <label for="password" class="form-label">Şifre</label>
                                <input type="password" class="form-input" id="password" name="password" required minlength="6">
                                <i class="fas fa-eye password-toggle" onclick="togglePassword('password')"></i>
                            </div>

                            <div class="input-group">
                                <label for="confirm_password" class="form-label">Şifre Tekrar</label>
                                <input type="password" class="form-input" id="confirm_password" name="confirm_password" required minlength="6">
                                <i class="fas fa-eye password-toggle" onclick="togglePassword('confirm_password')"></i>
                            </div>

                            <button type="submit" class="register-btn">
                                <i class="fas fa-user-plus me-2"></i>
                                Kayıt Ol
                            </button>

                            <div class="text-center mt-3">
                                <p class="mb-0">Zaten hesabınız var mı? <a href="login.php" class="text-decoration-none">Giriş Yapın</a></p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
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

        // Form doğrulama
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Şifreler eşleşmiyor!');
            }
        });
    </script>
</body>
</html>