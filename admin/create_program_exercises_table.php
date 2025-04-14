<?php
require_once dirname(__DIR__) . '/includes/db_connection.php';

$sql = "CREATE TABLE IF NOT EXISTS program_exercises (
    id INT AUTO_INCREMENT PRIMARY KEY,
    program_id INT NOT NULL,
    day_number INT NOT NULL,
    exercise_order INT NOT NULL,
    exercise_name VARCHAR(255) NOT NULL,
    sets INT NOT NULL,
    reps INT NOT NULL,
    weight DECIMAL(5,2) DEFAULT 0,
    duration INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "Program exercises tablosu başarıyla oluşturuldu.";
} else {
    echo "Hata: " . $conn->error;
}

$conn->close();
?> 