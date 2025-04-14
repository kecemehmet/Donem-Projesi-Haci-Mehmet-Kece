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

if (!isset($data['program_id']) || !isset($data['is_active'])) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz veri']);
    exit;
}

$program_id = intval($data['program_id']);
$is_active = intval($data['is_active']);

try {
    // Program durumunu güncelle
    $update_query = "UPDATE programs SET is_active = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ii", $is_active, $program_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Program durumu başarıyla güncellendi']);
    } else {
        throw new Exception('Program durumu güncellenemedi: ' . $stmt->error);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?> 