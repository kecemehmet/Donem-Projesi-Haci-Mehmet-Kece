<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Oturum başlatılmadan önce ayarları yap (isteğe bağlı)
ini_set('session.gc_maxlifetime', 3600); // 1 saat
session_set_cookie_params(3600); // Çerez süresi 1 saat
session_start(); // Oturumu başlat

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

    // Form verilerini al ve string'e çevir
    $name = $_POST['name'] ?? '';
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $height = (string) floatval($_POST['height']);
    $weight = (string) floatval($_POST['weight']);
    $bmi = (string) ($weight / (($height / 100) * ($height / 100)));
    $fitness_goal = $_POST['fitness_goal'];
    $experience_level = $_POST['experience_level'];
    $preferred_exercises = $_POST['preferred_exercises'];
    $workout_days = intval($_POST['workout_days']);
    $workout_duration = intval($_POST['workout_duration']);
    $target_weight = (string) floatval($_POST['target_weight']);
    $target_set_date = date('Y-m-d'); // Bugünün tarihi sabit olarak ayarlanır

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
        $stmt->bind_param("sssssssssiiiss", 
            $username, 
            $password, 
            $email, 
            $height, 
            $weight, 
            $bmi, 
            $fitness_goal, 
            $experience_level, 
            $preferred_exercises, 
            $workout_days, 
            $workout_duration, 
            $target_weight, 
            $target_set_date, 
            $name
        );

        if ($stmt->execute()) {
            // Oturum başlat ve kullanıcıyı giriş yapmış gibi ayarla
            $_SESSION['username'] = $username;
            $_SESSION['is_admin'] = 0; // Yeni kullanıcı admin değil
            session_regenerate_id(true); // Güvenlik için oturum ID'sini yenile
            
            // Dashboard'a yönlendir
            header("Location: dashboard.php");
            exit();
        } else {
            echo "Hata: " . $stmt->error;
        }
    }

    $stmt->close();
    $conn->close();
}
?>