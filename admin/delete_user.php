<?php
session_start();
require_once '../includes/db_connection.php';

// Admin kontrolü
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
    exit;
}

// JSON verilerini al
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Kullanıcı ID gerekli']);
    exit;
}

$user_id = intval($data['user_id']);

// Kullanıcıyı sil
$stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND is_admin = 0");
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Kullanıcı başarıyla silindi']);
} else {
    echo json_encode(['success' => false, 'message' => 'Silme işlemi sırasında bir hata oluştu']);
}

$stmt->close();
$conn->close();
?> 