<?php
require_once 'includes/db_connection.php';

// Veritabanını oluştur
$create_db = "CREATE DATABASE IF NOT EXISTS fitness_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
$conn->query($create_db);

// Veritabanını seç
$conn->select_db('fitness_db');

// SQL dosyasını oku ve çalıştır
$sql = file_get_contents('sql/create_tables.sql');

// Her bir SQL komutunu ayrı ayrı çalıştır
$commands = array_filter(explode(';', $sql), 'trim');
$success = true;
$errors = [];

foreach ($commands as $command) {
    try {
        if (trim($command)) {
            $conn->query($command);
        }
    } catch (Exception $e) {
        $success = false;
        $errors[] = $e->getMessage();
    }
}

// Users tablosunu oluştur
$sql = "
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_admin TINYINT(1) DEFAULT 0,
    is_banned TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Users tablosu oluşturuldu veya zaten mevcut<br>";
} else {
    echo "Hata: " . $conn->error . "<br>";
}

// Mevcut users tablosunu güncelle
$alter_sql = "
ALTER TABLE users 
MODIFY created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
MODIFY updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
";

if ($conn->query($alter_sql) === TRUE) {
    echo "Users tablosu güncellendi<br>";
} else {
    echo "Hata: " . $conn->error . "<br>";
}

// Sonucu göster
if ($success) {
    echo "Veritabanı ve tablolar başarıyla oluşturuldu!<br>";
    echo "<a href='admin.php' class='btn btn-primary mt-3'>Admin Paneline Git</a>";
} else {
    echo "Bazı hatalar oluştu:<br>";
    foreach ($errors as $error) {
        echo "- " . htmlspecialchars($error) . "<br>";
    }
}

$conn->close();
?> 