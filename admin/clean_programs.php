<?php
require_once __DIR__ . '/../includes/db_connection.php';

try {
    // Programları temizle
    $delete_exercises = "DELETE FROM program_exercises";
    $conn->query($delete_exercises);
    
    $delete_programs = "DELETE FROM programs";
    $conn->query($delete_programs);
    
    echo "Tüm programlar başarıyla silindi!";
    echo "<br><a href='programs.php'>Programlar sayfasına dön</a>";
} catch (Exception $e) {
    echo "Hata oluştu: " . $e->getMessage();
}
?> 