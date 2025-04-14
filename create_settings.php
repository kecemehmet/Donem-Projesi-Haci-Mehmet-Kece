<?php
// Veritabanı bağlantı bilgileri
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fitness_db";

try {
    // Veritabanı bağlantısı
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Bağlantı kontrolü
    if ($conn->connect_error) {
        die("Veritabanı bağlantı hatası: " . $conn->connect_error);
    }

    // Settings tablosunu oluştur
    $sql = "CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(255) NOT NULL UNIQUE,
        setting_value TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";

    if ($conn->query($sql) === TRUE) {
        echo "Settings tablosu başarıyla oluşturuldu.<br>";

        // Varsayılan ayarları ekle
        $default_settings = [
            'session_lifetime' => '120',
            'max_upload_size' => '10',
            'notify_new_user' => '1',
            'notify_feedback' => '1',
            'auto_backup' => '1',
            'backup_frequency' => 'daily'
        ];

        $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        
        foreach ($default_settings as $key => $value) {
            $stmt->bind_param("ss", $key, $value);
            if ($stmt->execute()) {
                echo "$key ayarı başarıyla eklendi.<br>";
            } else {
                echo "$key ayarı eklenirken hata oluştu: " . $stmt->error . "<br>";
            }
        }
        
        $stmt->close();
    } else {
        echo "Settings tablosu oluşturulurken hata: " . $conn->error;
    }

    $conn->close();
    
} catch (Exception $e) {
    echo "Hata: " . $e->getMessage();
}
?> 