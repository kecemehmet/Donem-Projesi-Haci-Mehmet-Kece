<?php
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

// Karakter seti ayarı
$conn->set_charset("utf8mb4");

// Zaman dilimi ayarı
date_default_timezone_set('Europe/Istanbul');

// Oturum başlatma
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hata raporlama
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Güvenlik fonksiyonları
function clean($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Kullanıcı giriş kontrolü
function checkLogin() {
    if (!isset($_SESSION['username']) || !isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
    
    global $conn;
    $stmt = $conn->prepare("SELECT id FROM users WHERE id = ? AND username = ?");
    $stmt->bind_param("is", $_SESSION['user_id'], $_SESSION['username']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        session_destroy();
        header("Location: login.php");
        exit();
    }
}

// Admin kontrolü
function checkAdmin() {
    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
        header("Location: index.php");
        exit();
    }
}
?> 