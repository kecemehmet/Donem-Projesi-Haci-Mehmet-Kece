<?php
require_once __DIR__ . '/../includes/db_connection.php';

// Oturum kontrolü
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Admin kontrolü
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
    exit;
}

// JSON verisini al
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['program_id'])) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz veri']);
    exit;
}

$program_id = intval($data['program_id']);

try {
    // İşlemi transaction içinde gerçekleştir
    $conn->begin_transaction();
    
    // Önce program egzersizlerini sil
    $delete_exercises = "DELETE FROM program_exercises WHERE program_id = ?";
    $stmt = $conn->prepare($delete_exercises);
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    
    // Sonra programı sil
    $delete_program = "DELETE FROM programs WHERE id = ?";
    $stmt = $conn->prepare($delete_program);
    $stmt->bind_param("i", $program_id);
    
    if ($stmt->execute()) {
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Program başarıyla silindi']);
    } else {
        throw new Exception('Program silinemedi: ' . $stmt->error);
    }
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?> 