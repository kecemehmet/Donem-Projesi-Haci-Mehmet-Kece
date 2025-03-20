<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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

    // Form verilerini al
    $name = $_POST['name'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $height = floatval($_POST['height']);
    $weight = floatval($_POST['weight']);
    $bmi = $weight / (($height / 100) * ($height / 100));
    $fitness_goal = $_POST['fitness_goal'];
    $experience_level = $_POST['experience_level'];
    $preferred_exercises = $_POST['preferred_exercises'];
    $workout_days = intval($_POST['workout_days']);
    $workout_duration = intval($_POST['workout_duration']);
    $target_weight = floatval($_POST['target_weight']);
    $target_set_date = $_POST['target_set_date'];

    // E-posta ve kullanıcı adı kontrolü (yasaklı e-posta dahil)
    $stmt = $conn->prepare("SELECT is_banned FROM users WHERE email = ? OR username = ?");
    $stmt->bind_param("ss", $email, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['is_banned']) {
            echo "<script>alert('Bu e-posta adresi yasaklıdır. Başka bir e-posta kullanın!'); window.location.href='register.html';</script>";
        } else {
            echo "<script>alert('Bu kullanıcı adı veya e-posta zaten kullanılıyor!'); window.location.href='register.html';</script>";
        }
    } else {
        // Veritabanına ekle (hazırlıklı sorgu)
        $stmt = $conn->prepare("INSERT INTO users (username, password, email, height, weight, bmi, fitness_goal, experience_level, preferred_exercises, workout_days, workout_duration, target_weight, target_set_date, name) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssffssssiiids", $username, $password, $email, $height, $weight, $bmi, $fitness_goal, $experience_level, $preferred_exercises, $workout_days, $workout_duration, $target_weight, $target_set_date, $name);

        if ($stmt->execute()) {
            echo "<script>alert('Kayıt başarılı! BMI değeriniz: " . number_format($bmi, 2) . "'); window.location.href='index.php';</script>";
        } else {
            echo "Hata: " . $stmt->error;
        }
    }

    $stmt->close();
    $conn->close();
}
?>