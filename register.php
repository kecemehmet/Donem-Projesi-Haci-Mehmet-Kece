<?php
error_reporting(E_ALL); // Tüm hataları göster
ini_set('display_errors', 1); // Hataları ekranda göster

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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

    // Form verilerini al
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Şifreyi hashle
    $height = $_POST['height'];
    $weight = $_POST['weight'];
    $bmi = $weight / (($height / 100) ** 2); // BMI hesapla
    $fitness_goal = $_POST['fitness_goal'];
    $experience_level = $_POST['experience_level'];
    $preferred_exercises = $_POST['preferred_exercises'];
    $workout_days = $_POST['workout_days'];
    $workout_duration = $_POST['workout_duration'];

    // Kullanıcı adı ve e-posta kontrolü
    $check_sql = "SELECT * FROM users WHERE username='$username' OR email='$email'";
    $check_result = $conn->query($check_sql);

    if ($check_result->num_rows > 0) {
        // Kullanıcı adı veya e-posta zaten kullanılıyor
        echo "<script>alert('Bu kullanıcı adı veya e-posta zaten kullanılıyor!'); window.location.href='register.html';</script>";
    } else {
        // Veritabanına ekle
        $sql = "INSERT INTO users (username, password, email, height, weight, bmi, fitness_goal, experience_level, preferred_exercises, workout_days, workout_duration)
                VALUES ('$username', '$password', '$email', $height, $weight, $bmi, '$fitness_goal', '$experience_level', '$preferred_exercises', $workout_days, $workout_duration)";

        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('Kayıt başarılı! BMI değeriniz: " . number_format($bmi, 2) . "'); window.location.href='index.php';</script>";
        } else {
            echo "Hata: " . $sql . "<br>" . $conn->error;
        }
    }

    $conn->close();
}
?>