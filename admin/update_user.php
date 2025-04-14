<?php
session_start();
require_once '../includes/db_connection.php';

// Admin kontrolü
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim']);
    exit;
}

// POST verilerini kontrol et
if (!isset($_POST['user_id']) || !isset($_POST['username']) || !isset($_POST['email'])) {
    echo json_encode(['success' => false, 'message' => 'Eksik bilgi']);
    exit;
}

$user_id = intval($_POST['user_id']);
$username = trim($_POST['username']);
$email = trim($_POST['email']);
$password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

// Kullanıcı adı ve email benzersizlik kontrolü
$check = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ? AND is_admin = 0");
$check->bind_param("ssi", $username, $email, $user_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Bu kullanıcı adı veya e-posta adresi zaten kullanılıyor']);
    exit;
}

// Kullanıcıyı güncelle
if ($password) {
    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE id = ? AND is_admin = 0");
    $stmt->bind_param("sssi", $username, $email, $password, $user_id);
} else {
    $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ? AND is_admin = 0");
    $stmt->bind_param("ssi", $username, $email, $user_id);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Kullanıcı başarıyla güncellendi']);
} else {
    echo json_encode(['success' => false, 'message' => 'Güncelleme sırasında bir hata oluştu']);
}

$stmt->close();
$conn->close();
?> 