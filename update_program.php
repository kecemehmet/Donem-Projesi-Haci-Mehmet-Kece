<?php
session_start();
require_once 'includes/db_connection.php';

header('Content-Type: application/json');

// Kullanıcının giriş yapmış olduğunu kontrol et
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Oturum açmanız gerekiyor']);
    exit;
}

// JSON verisini al
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Geçersiz veri']);
    exit;
}

try {
    // Program ID kontrolü
    if (!isset($data['program_id'])) {
        throw new Exception('Program ID gerekli');
    }
    
    $program_id = intval($data['program_id']);
    
    // İşlemi transaction içinde gerçekleştir
    $conn->begin_transaction();
    
    // Mevcut egzersizleri sil
    $delete_query = "DELETE FROM program_exercises WHERE program_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    
    // Yeni egzersizleri ekle
    $insert_query = "INSERT INTO program_exercises (program_id, day_number, exercise_order, exercise_name, sets, reps, weight, duration) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    
    foreach ($data['program'] as $exercise) {
        $stmt->bind_param("iiissidi",
            $program_id,
            $exercise['day_number'],
            $exercise['order'],
            $exercise['name'],
            $exercise['sets'],
            $exercise['reps'],
            $exercise['weight'],
            $exercise['duration']
        );
        
        if (!$stmt->execute()) {
            throw new Exception('Egzersiz kaydedilemedi: ' . $stmt->error);
        }
    }
    
    // İşlemi tamamla
    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => 'Program başarıyla güncellendi']);
    
} catch (Exception $e) {
    // Hata durumunda transaction'ı geri al
    if ($conn->connect_errno == 0) {
        $conn->rollback();
    }
    
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?> 