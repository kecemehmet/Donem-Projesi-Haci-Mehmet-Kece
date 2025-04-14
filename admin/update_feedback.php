<?php
// Veritabanı bağlantısı
require_once dirname(__DIR__) . '/includes/db_connection.php';

// Admin kontrolü
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../login.php');
    exit;
}

// Feedback tablosunu oluştur
$create_table = "
CREATE TABLE IF NOT EXISTS feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('pending', 'answered') DEFAULT 'pending',
    admin_reply TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

if ($conn->query($create_table)) {
    $_SESSION['message'] = "Feedback tablosu başarıyla oluşturuldu.";
    $_SESSION['message_type'] = "success";
} else {
    $_SESSION['message'] = "Feedback tablosu oluşturulurken bir hata oluştu: " . $conn->error;
    $_SESSION['message_type'] = "danger";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $feedback_id = $_POST['feedback_id'] ?? 0;
    $admin_reply = $_POST['admin_reply'] ?? '';

    if ($feedback_id && $admin_reply) {
        // Feedback'i güncelle
        $stmt = $conn->prepare("UPDATE feedback SET admin_reply = ?, status = 'answered', updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("si", $admin_reply, $feedback_id);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Geri bildirim başarıyla yanıtlandı.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Geri bildirim yanıtlanırken bir hata oluştu.";
            $_SESSION['message_type'] = "danger";
        }
    } else {
        $_SESSION['message'] = "Geçersiz veri gönderildi.";
        $_SESSION['message_type'] = "danger";
    }
}

header('Location: feedback.php');
exit; 