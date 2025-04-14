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

// Form gönderildiğinde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $height = $_POST['height'];
    $weight = $_POST['weight'];
    
    $stmt = $conn->prepare("UPDATE users SET height = ?, weight = ? WHERE id = ?");
    $stmt->bind_param("ddi", $height, $weight, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Boy ve kilo bilgileriniz başarıyla güncellendi.";
        header("Location: calculate_bmi.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Bilgiler güncellenirken bir hata oluştu.";
    }
}
?>

<!DOCTYPE html>
<html lang="tr" data-theme="<?php echo isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMI Hesapla - FitMate</title>
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/navbar.css">
    <style>
        .bmi-container {
            padding: 2rem 0;
            margin-top: 76px;
            min-height: calc(100vh - 76px);
            background: linear-gradient(135deg, var(--bg-color) 0%, var(--card-bg) 100%);
        }

        .bmi-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px var(--shadow-color);
        }

        .bmi-result {
            text-align: center;
            margin: 2rem 0;
        }

        .bmi-value {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
            background: linear-gradient(45deg, var(--primary-btn-bg), var(--secondary-btn-bg));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .bmi-category {
            display: inline-block;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            font-weight: 600;
            background: linear-gradient(45deg, var(--primary-btn-bg), var(--secondary-btn-bg));
            color: #fff;
        }

        .bmi-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: var(--primary-btn-bg);
        }

        .bmi-ranges {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-top: 2rem;
        }

        .bmi-range {
            padding: 0.5rem 1rem;
            border-radius: 5px;
            background: var(--bg-color);
            color: var(--text-muted);
            transition: all 0.3s ease;
        }

        .bmi-range.active {
            background: linear-gradient(45deg, var(--primary-btn-bg), var(--secondary-btn-bg));
            color: #fff;
            transform: scale(1.02);
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="bmi-container">
        <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="bmi-card" data-aos="fade-up">
                        <h2 class="text-center mb-4">BMI Hesaplama</h2>
                        
                        <?php if (isset($_SESSION['success_message'])): ?>
                            <div class="alert alert-success">
                                <?php 
                                echo $_SESSION['success_message'];
                                unset($_SESSION['success_message']);
                                ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (isset($_SESSION['error_message'])): ?>
                            <div class="alert alert-danger">
                                <?php 
                                echo $_SESSION['error_message'];
                                unset($_SESSION['error_message']);
                                ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="mb-4">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="height" class="form-label">Boy (cm)</label>
                                    <input type="number" class="form-control" id="height" name="height" value="<?php echo $user['height'] ?? ''; ?>" required>
                            </div>
                                <div class="col-md-6 mb-3">
                                    <label for="weight" class="form-label">Kilo (kg)</label>
                                    <input type="number" class="form-control" id="weight" name="weight" value="<?php echo $user['weight'] ?? ''; ?>" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Hesapla</button>
                        </form>
                        
                        <?php if ($bmi > 0): ?>
                            <div class="bmi-result">
                                <i class="fas <?php echo $bmi_icon; ?> bmi-icon"></i>
                                <div class="bmi-value"><?php echo $bmi; ?></div>
                                <div class="bmi-category"><?php echo $bmi_category; ?></div>
                                
                                <div class="bmi-ranges">
                                    <div class="bmi-range <?php echo $bmi < 18.5 ? 'active' : ''; ?>">Zayıf (<18.5)</div>
                                    <div class="bmi-range <?php echo $bmi >= 18.5 && $bmi < 25 ? 'active' : ''; ?>">Normal (18.5-24.9)</div>
                                    <div class="bmi-range <?php echo $bmi >= 25 && $bmi < 30 ? 'active' : ''; ?>">Kilolu (25-29.9)</div>
                                    <div class="bmi-range <?php echo $bmi >= 30 ? 'active' : ''; ?>">Obez (≥30)</div>
                                </div>
                            </div>
                        <?php endif; ?>
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
    </script>
</body>
</html> 