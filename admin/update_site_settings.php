<?php
require_once '../includes/db_connection.php';

// Admin kontrolü
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $site_title = $_POST['site_title'] ?? '';
    $site_description = $_POST['site_description'] ?? '';
    $contact_email = $_POST['contact_email'] ?? '';

    // Verileri güncelle
    $stmt = $conn->prepare("UPDATE site_settings SET 
        site_title = ?, 
        site_description = ?, 
        contact_email = ? 
        WHERE id = 1");
    
    $stmt->bind_param("sss", $site_title, $site_description, $contact_email);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = 'Site ayarları başarıyla güncellendi';
        $_SESSION['message_type'] = 'success';
    } else {
        $_SESSION['message'] = 'Site ayarları güncellenirken bir hata oluştu';
        $_SESSION['message_type'] = 'error';
    }
    
    $stmt->close();
}

header('Location: settings.php');
exit; 