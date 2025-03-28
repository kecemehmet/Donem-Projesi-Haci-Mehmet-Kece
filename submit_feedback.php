<?php
session_start();

// Kullanıcı giriş yapmış mı kontrol et
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// Veritabanı bağlantı bilgileri
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

// Kullanıcının ID'sini al
$username = $_SESSION['username'];
$stmt_user = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt_user->bind_param("s", $username);
$stmt_user->execute();
$result = $stmt_user->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $user_id = $user['id'];
} else {
    header("Location: dashboard.php?feedback_error=" . urlencode("Kullanıcı bulunamadı!"));
    exit();
}
$stmt_user->close();

// Formdan gelen verileri al
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $feedback_text = trim($_POST['feedback_text']); // Boşlukları temizle

    // Minimum 60 karakter kontrolü
    if (strlen($feedback_text) < 60) {
        header("Location: dashboard.php?feedback_error=" . urlencode("Geri bildirim en az 60 karakter olmalıdır!"));
        exit();
    }

    // SQL injection önlemek için hazırlıklı sorgu kullan
    $stmt = $conn->prepare("INSERT INTO feedback (user_id, feedback_text) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $feedback_text);

    if ($stmt->execute()) {
        // Başarılıysa dashboard'a geri dön ve bir başarı mesajı göster
        header("Location: dashboard.php?feedback_success=1");
    } else {
        // Hata varsa dashboard'a geri dön ve hata mesajı göster
        header("Location: dashboard.php?feedback_error=" . urlencode("Geri bildirim gönderilirken bir hata oluştu: " . $conn->error));
    }

    $stmt->close();
}

$conn->close();
?>