<?php
require_once dirname(__DIR__) . '/includes/db_connection.php';

try {
    // user_id sütununu ekle
    $conn->query("ALTER TABLE programs ADD COLUMN user_id INT AFTER trainer_id");
    
    // is_active sütununu ekle
    $conn->query("ALTER TABLE programs ADD COLUMN is_active TINYINT(1) DEFAULT 1 AFTER user_id");
    
    // Mevcut programları aktif yap
    $conn->query("UPDATE programs SET is_active = 1 WHERE is_active IS NULL");
    
    echo "Programs tablosu başarıyla güncellendi!";
} catch (Exception $e) {
    echo "Hata oluştu: " . $e->getMessage();
}
?> 