<?php
session_start();
require_once 'config.php';
checkLogin();

// Sadece adminler bu işlemi yapabilir
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Bu işlem için yetkiniz yok.']);
    exit;
}

// JSON verisini al
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['program_id']) || !isset($data['program'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Geçersiz veri formatı.']);
    exit;
}

$program_id = intval($data['program_id']);
$program = $data['program'];

try {
    $conn->begin_transaction();

    // Programın varlığını ve admin yetkisini kontrol et
    $check_query = "SELECT p.* FROM programs p 
                   WHERE p.id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Program bulunamadı.');
    }

    // Mevcut egzersizleri sil
    $delete_query = "DELETE FROM program_exercises WHERE program_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $program_id);
    $stmt->execute();

    // Yeni egzersizleri ekle
    $insert_query = "INSERT INTO program_exercises (program_id, day_number, exercise_order, exercise_name, sets, reps, weight, duration) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);

    $days = ['Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi', 'Pazar'];

    foreach ($program as $day => $exercises) {
        $day_number = array_search($day, $days) + 1;
        
        foreach ($exercises as $order => $exercise) {
            $sets = isset($exercise['raw_sets']) ? intval($exercise['raw_sets']) : 0;
            $reps = isset($exercise['raw_reps']) ? intval($exercise['raw_reps']) : 0;
            $duration = isset($exercise['raw_duration']) ? intval($exercise['raw_duration']) : 0;
            $weight = 0; // Şimdilik ağırlık kullanmıyoruz

            $stmt->bind_param("iiissidi",
                $program_id,
                $day_number,
                $order,
                $exercise['name'],
                $sets,
                $reps,
                $weight,
                $duration
            );

            if (!$stmt->execute()) {
                throw new Exception('Egzersiz kaydedilirken bir hata oluştu: ' . $stmt->error);
            }
        }
    }

    $conn->commit();
    // Session mesajlarını temizle
    $_SESSION['success_message'] = null;
    $_SESSION['error_message'] = null;
    $_SESSION['message'] = null;
    $_SESSION['message_type'] = null;
    unset($_SESSION['success_message']);
    unset($_SESSION['error_message']);
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $conn->rollback();
    // Session mesajlarını temizle
    $_SESSION['success_message'] = null;
    $_SESSION['error_message'] = null;
    $_SESSION['message'] = null;
    $_SESSION['message_type'] = null;
    unset($_SESSION['success_message']);
    unset($_SESSION['error_message']);
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 