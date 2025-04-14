<?php
require_once '../includes/db_connection.php';

// Admin kontrolü
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $max_file_size = $_POST['max_file_size'] ?? 5;
    $auto_backup = $_POST['auto_backup'] ?? 'weekly';

    // Verileri güncelle
    $stmt = $conn->prepare("UPDATE site_settings SET 
        max_file_size = ?, 
        auto_backup = ? 
        WHERE id = 1");
    
    $stmt->bind_param("is", $max_file_size, $auto_backup);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = 'Sistem ayarları başarıyla güncellendi';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Sistem ayarları güncellenirken bir hata oluştu';
        $_SESSION['message_type'] = 'error';
    }
    
    $stmt->close();
}

header('Location: settings.php');
exit; 