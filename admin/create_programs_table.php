<?php
require_once dirname(__DIR__) . '/includes/db_connection.php';

$sql = "CREATE TABLE IF NOT EXISTS programs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    category VARCHAR(50) NOT NULL,
    difficulty_level VARCHAR(20) NOT NULL,
    trainer_id INT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (trainer_id) REFERENCES users(id)
)";

if ($conn->query($sql) === TRUE) {
    echo "Programs tablosu başarıyla oluşturuldu.";
} else {
    echo "Hata: " . $conn->error;
}

$conn->close();
?> 