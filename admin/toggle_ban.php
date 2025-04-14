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

if (!isset($data['user_id']) || !isset($data['current_status'])) {
    echo json_encode(['success' => false, 'message' => 'Eksik bilgi']);
    exit;
}

$user_id = intval($data['user_id']);
$new_status = $data['current_status'] ? 0 : 1;  // Mevcut durumun tersini al

// Kullanıcı durumunu güncelle
$stmt = $conn->prepare("UPDATE users SET is_banned = ? WHERE id = ? AND is_admin = 0");
$stmt->bind_param("ii", $new_status, $user_id);

if ($stmt->execute()) {
    $message = $new_status ? 'Kullanıcı yasaklandı' : 'Kullanıcı yasağı kaldırıldı';
    echo json_encode(['success' => true, 'message' => $message]);
} else {
    echo json_encode(['success' => false, 'message' => 'İşlem sırasında bir hata oluştu']);
}

$stmt->close();
$conn->close();
?> 