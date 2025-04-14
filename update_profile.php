<?php
session_start();
require_once 'config.php';

// Kullanıcı girişi kontrolü
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Kullanıcı bilgilerini al
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Varsayılan değerleri ayarla
$user = array_merge([
    'name' => '',
    'email' => '',
    'height' => '',
    'weight' => '',
    'target_weight' => '',
    'fitness_goal' => 'weight_loss',
    'experience_level' => 'beginner',
    'show_name_in_success' => 0,
    'show_username_in_success' => 0,
    'profile_picture' => 'images/default_profile.png'
], $user);

// Form gönderildiğinde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $name = $_POST['name'];
    $height = $_POST['height'];
    $weight = $_POST['weight'];
    $target_weight = $_POST['target_weight'];
    $fitness_goal = $_POST['fitness_goal'];
    $experience_level = $_POST['experience_level'];
    $show_name_in_success = isset($_POST['show_name_in_success']) ? 1 : 0;
    $show_username_in_success = isset($_POST['show_username_in_success']) ? 1 : 0;
    
    $errors = [];
    
    // Kullanıcı adı kontrolü
    if (empty($username)) {
        $errors[] = "Kullanıcı adı boş olamaz.";
    } elseif (strlen($username) < 3) {
        $errors[] = "Kullanıcı adı en az 3 karakter olmalıdır.";
    }
    
    // E-posta kontrolü
    if (empty($email)) {
        $errors[] = "E-posta adresi boş olamaz.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Geçerli bir e-posta adresi giriniz.";
    }
    
    // Fiziksel bilgiler kontrolü
    if ($weight < 40 || $weight > 150) {
        $errors[] = "Kilo 40 kg ile 150 kg arasında olmalıdır!";
    }
    if ($target_weight && ($target_weight < 40 || $target_weight > 150)) {
        $errors[] = "Hedef kilo 40 kg ile 150 kg arasında olmalıdır!";
    }
    if ($height <= 0) {
        $errors[] = "Boy değeri 0'dan büyük olmalıdır!";
    }
    
    // Hata yoksa güncelleme yap
    if (empty($errors)) {
        // BMI hesapla
        $bmi = $height > 0 ? round($weight / (($height / 100) ** 2), 1) : 0;
        
        // Kullanıcı adı ve e-posta güncelleme
        $update_query = "UPDATE users SET username = ?, email = ?, name = ?, height = ?, weight = ?, 
                        target_weight = ?, fitness_goal = ?, experience_level = ?, bmi = ?,
                        show_name_in_success = ?, show_username_in_success = ? WHERE id = ?";
        $params = [$username, $email, $name, $height, $weight, $target_weight, $fitness_goal, 
                  $experience_level, $bmi, $show_name_in_success, $show_username_in_success, $user_id];
        $types = "sssddsssddii";
        
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Değişiklikler başarıyla kaydedildi";
            $_SESSION['message_type'] = 'success';
            header("Location: profile.php");
            exit;
        } else {
            $_SESSION['error_message'] = "Değişiklikler başarıyla kaydedildi";
            $_SESSION['message_type'] = 'success';
            header("Location: profile.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr" data-theme="<?php echo isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Güncelle - FitMate</title>
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/navbar.css">
    <style>
        .profile-container {
            padding: 2rem 0;
            margin-top: 76px;
            min-height: calc(100vh - 76px);
            background: linear-gradient(135deg, var(--bg-color) 0%, var(--card-bg) 100%);
        }

        .profile-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px var(--shadow-color);
            border: 2px solid #000;
            transition: all 0.3s ease;
        }

        [data-theme='dark'] .profile-card {
            border-color: #fff;
        }

        .profile-card:hover {
            border-color: var(--primary-btn-bg);
            box-shadow: 0 0 15px rgba(var(--primary-btn-bg-rgb), 0.3);
            transform: translateY(-5px);
        }

        .profile-card.aos-init.aos-animate {
            animation: cardBorderAnimation 2s infinite;
        }

        @keyframes cardBorderAnimation {
            0% {
                border-color: #000;
                box-shadow: 0 0 0 rgba(var(--primary-btn-bg-rgb), 0);
            }
            50% {
                border-color: var(--primary-btn-bg);
                box-shadow: 0 0 15px rgba(var(--primary-btn-bg-rgb), 0.3);
            }
            100% {
                border-color: #000;
                box-shadow: 0 0 0 rgba(var(--primary-btn-bg-rgb), 0);
            }
        }

        [data-theme='dark'] .profile-card.aos-init.aos-animate {
            animation: cardBorderAnimationDark 2s infinite;
        }

        @keyframes cardBorderAnimationDark {
            0% {
                border-color: #fff;
                box-shadow: 0 0 0 rgba(var(--primary-btn-bg-rgb), 0);
            }
            50% {
                border-color: var(--primary-btn-bg);
                box-shadow: 0 0 15px rgba(var(--primary-btn-bg-rgb), 0.3);
            }
            100% {
                border-color: #fff;
                box-shadow: 0 0 0 rgba(var(--primary-btn-bg-rgb), 0);
            }
        }

        .profile-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin: 0 auto 1rem;
            background: linear-gradient(45deg, var(--primary-btn-bg), var(--secondary-btn-bg));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: #fff;
            position: relative;
            overflow: hidden;
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-avatar i {
            position: absolute;
            bottom: 0;
            right: 0;
            background: var(--primary-btn-bg);
            color: #fff;
            padding: 5px;
            border-radius: 50%;
            cursor: pointer;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--text-color);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-title i {
            color: var(--primary-btn-bg);
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--text-color);
        }

        .form-control {
            background: var(--input-bg);
            border: 2px solid #000;
            color: var(--text-color);
            padding: 0.8rem;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        [data-theme='dark'] .form-control {
            border-color: #fff;
        }

        .form-control:focus {
            border-color: var(--primary-btn-bg);
            box-shadow: 0 0 0 0.25rem rgba(var(--primary-btn-bg-rgb), 0.25);
            background-color: var(--input-bg);
            color: var(--text-color);
        }

        .form-select {
            background: var(--input-bg);
            border: 2px solid #000;
            color: var(--text-color);
            padding: 0.8rem;
            border-radius: 10px;
        }

        [data-theme='dark'] .form-select {
            border-color: #fff;
        }

        .form-select:focus {
            border-color: var(--primary-btn-bg);
            box-shadow: 0 0 0 0.25rem rgba(var(--primary-btn-bg-rgb), 0.25);
            background-color: var(--input-bg);
            color: var(--text-color);
        }

        .form-select option {
            background-color: var(--card-bg);
            color: var(--text-color);
            padding: 10px;
        }

        [data-theme='dark'] .form-select option {
            background-color: var(--card-bg);
            color: var(--text-color);
        }

        [data-theme='dark'] .form-select option:hover {
            background-color: var(--primary-btn-bg);
            color: #fff;
        }

        .form-check-input {
            background-color: var(--input-bg);
            border: 2px solid var(--border-color);
        }

        .form-check-input:checked {
            background-color: var(--primary-btn-bg);
            border-color: var(--primary-btn-bg);
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="profile-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="profile-card" data-aos="fade-up">
                        <div class="profile-header">
                            <div class="profile-avatar">
                                <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profil Resmi">
                                <i class="fas fa-camera"></i>
                            </div>
                            <h2>Profil Güncelle</h2>
                        </div>
                        
                        <?php if (isset($_SESSION['success_message'])): ?>
                            <div class="alert alert-success">
                                <?php 
                                echo $_SESSION['success_message'];
                                unset($_SESSION['success_message']);
                                ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <h5 class="section-title"><i class="fas fa-user-circle"></i> Kişisel Bilgiler</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Kullanıcı Adı</label>
                                        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Ad Soyad</label>
                                        <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">E-posta</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>

                            <h5 class="section-title"><i class="fas fa-weight"></i> Fiziksel Bilgiler</h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="weight" class="form-label">Kilo (kg)</label>
                                        <input type="number" step="0.1" class="form-control" id="weight" name="weight" value="<?php echo $user['weight']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="height" class="form-label">Boy (cm)</label>
                                        <input type="number" class="form-control" id="height" name="height" value="<?php echo $user['height']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="target_weight" class="form-label">Hedef Kilo (kg)</label>
                                        <input type="number" step="0.1" class="form-control" id="target_weight" name="target_weight" value="<?php echo $user['target_weight']; ?>">
                                    </div>
                                </div>
                            </div>

                            <h5 class="section-title"><i class="fas fa-bullseye"></i> Fitness Hedefleri</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="fitness_goal" class="form-label">Fitness Hedefi</label>
                                        <select class="form-select" id="fitness_goal" name="fitness_goal">
                                            <option value="weight_loss" <?php echo $user['fitness_goal'] == 'weight_loss' ? 'selected' : ''; ?>>Kilo Vermek</option>
                                            <option value="muscle_gain" <?php echo $user['fitness_goal'] == 'muscle_gain' ? 'selected' : ''; ?>>Kas Kazanmak</option>
                                            <option value="maintain" <?php echo $user['fitness_goal'] == 'maintain' ? 'selected' : ''; ?>>Mevcut Kiloyu Korumak</option>
                                            <option value="endurance" <?php echo $user['fitness_goal'] == 'endurance' ? 'selected' : ''; ?>>Dayanıklılık Artırmak</option>
                                            <option value="general_fitness" <?php echo $user['fitness_goal'] == 'general_fitness' ? 'selected' : ''; ?>>Genel Fitness</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="experience_level" class="form-label">Deneyim Seviyesi</label>
                                        <select class="form-select" id="experience_level" name="experience_level">
                                            <option value="beginner" <?php echo $user['experience_level'] == 'beginner' ? 'selected' : ''; ?>>Başlangıç</option>
                                            <option value="intermediate" <?php echo $user['experience_level'] == 'intermediate' ? 'selected' : ''; ?>>Orta</option>
                                            <option value="advanced" <?php echo $user['experience_level'] == 'advanced' ? 'selected' : ''; ?>>İleri</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <h5 class="section-title"><i class="fas fa-shield-alt"></i> Gizlilik Ayarları</h5>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" role="switch" id="show_name_in_success" name="show_name_in_success" <?php echo $user['show_name_in_success'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="show_name_in_success">Başarı hikayelerinde adımı göster</label>
                            </div>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" role="switch" id="show_username_in_success" name="show_username_in_success" <?php echo $user['show_username_in_success'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="show_username_in_success">Başarı hikayelerinde kullanıcı adımı göster</label>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">Güncelle</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="js/theme.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            once: true
        });

        // Profil resmi yükleme
        document.querySelector('.profile-avatar i').addEventListener('click', function() {
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = 'image/*';
            input.onchange = function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.querySelector('.profile-avatar img').src = e.target.result;
                    }
                    reader.readAsDataURL(file);
                }
            };
            input.click();
        });
    </script>
</body>
</html>