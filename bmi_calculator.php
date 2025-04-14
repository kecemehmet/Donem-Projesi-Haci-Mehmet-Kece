<?php
// Form gönderildiğinde
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $height = floatval($_POST['height']);
    $weight = floatval($_POST['weight']);
    $fitness_goal = $_POST['fitness_goal'];
    $experience_level = $_POST['experience_level'];
    
    // BMI hesapla
    $height_m = $height / 100;
    $bmi = $weight / ($height_m * $height_m);
    $bmi = round($bmi, 1);
    
    // Veritabanını güncelle
    $stmt = $conn->prepare("
        UPDATE users 
        SET height = ?, weight = ?, bmi = ?, fitness_goal = ?, experience_level = ?
        WHERE id = ?
    ");
    $stmt->bind_param("ddsssi", $height, $weight, $bmi, $fitness_goal, $experience_level, $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        // Profil sayfasına yönlendir
        header("Location: profile.php");
        exit;
    } else {
        $error = "Bilgileriniz güncellenirken bir hata oluştu";
    }
    $stmt->close();
} 