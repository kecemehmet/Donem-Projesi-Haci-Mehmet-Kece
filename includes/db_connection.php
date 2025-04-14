<?php
// Veritabanı bağlantı bilgileri
$host = 'localhost';
$dbname = 'fitness_db';
$username = 'root';
$password = '';

try {
    // PDO bağlantısı oluştur
    $conn = new mysqli($host, $username, $password, $dbname);
    
    // Bağlantı hatası kontrolü
    if ($conn->connect_error) {
        throw new Exception("Veritabanı bağlantı hatası: " . $conn->connect_error);
    }
    
    // Türkçe karakter desteği için karakter seti ayarı
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    // Hata durumunda kullanıcıya bilgi ver
    die("Veritabanına bağlanılamadı: " . $e->getMessage());
}

// Oturum başlat (eğer başlatılmamışsa)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?> 