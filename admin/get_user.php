<?php
ob_start(); // Çıktı tamponlaması başlat
session_start();
require_once dirname(__DIR__) . '/includes/db_connection.php';

// Tüm çıktıyı temizle
ob_clean();

// JSON başlığını ayarla
header('Content-Type: application/json');

// Admin kontrolü
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    echo json_encode([
        'success' => false,
        'message' => 'Yetkisiz erişim'
    ]);
    exit;
}

// ID kontrolü
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Geçersiz kullanıcı ID'
    ]);
    exit;
}

$user_id = intval($_GET['id']);

try {
    // Kullanıcı bilgilerini al
    $stmt = $conn->prepare("SELECT id, username, email FROM users WHERE id = ? AND is_admin = 0 LIMIT 1");
    if (!$stmt) {
        throw new Exception("Sorgu hazırlanamadı");
    }
    
    $stmt->bind_param("i", $user_id);
    if (!$stmt->execute()) {
        throw new Exception("Sorgu çalıştırılamadı");
    }
    
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'user' => $user
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Kullanıcı bulunamadı'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Sunucu hatası: ' . $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
} 