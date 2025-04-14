<?php
session_start();
require_once '../includes/db_connection.php';

// Admin kontrolü
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
    exit;
}

// POST verilerini kontrol et
if (!isset($_POST['program_name']) || !isset($_POST['level']) || !isset($_POST['goal']) || !isset($_POST['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Eksik bilgi']);
    exit;
}

// Verileri temizle
$program_name = trim($_POST['program_name']);
$level = trim($_POST['level']);
$goal = trim($_POST['goal']);
$user_id = intval($_POST['user_id']);

// Kullanıcının varlığını kontrol et
$user_check = $conn->prepare("SELECT id FROM users WHERE id = ? AND is_admin = 0");
$user_check->bind_param("i", $user_id);
$user_check->execute();
$result = $user_check->get_result();

if ($result->num_rows === 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Geçersiz kullanıcı']);
    exit;
}

// Programı ekle
$stmt = $conn->prepare("INSERT INTO custom_workout_programs (user_id, program_name, level, goal, created_at) VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param("isss", $user_id, $program_name, $level, $goal);

if ($stmt->execute()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Program başarıyla eklendi']);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Program eklenirken bir hata oluştu']);
}

$stmt->close();
$conn->close();
?> 