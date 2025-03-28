<?php
session_start();

// Kullanıcı giriş yapmış mı ve admin mi kontrol et
if (!isset($_SESSION['username']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== 1) {
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

if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

// GET parametrelerini al
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($user_id <= 0 || !in_array($action, ['ban', 'unban'])) {
    header("Location: admin.php?error=" . urlencode("Geçersiz işlem!"));
    exit();
}

// Banlama veya ban kaldırma sorgusu
if ($action === 'ban') {
    $stmt = $conn->prepare("UPDATE users SET is_banned = 1 WHERE id = ?");
} else {
    $stmt = $conn->prepare("UPDATE users SET is_banned = 0 WHERE id = ?");
}

$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    header("Location: admin.php?success=" . urlencode("İşlem başarıyla gerçekleştirildi!"));
} else {
    header("Location: admin.php?error=" . urlencode("İşlem başarısız: " . $conn->error));
}

$stmt->close();
$conn->close();
?>