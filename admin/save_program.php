<?php
require_once dirname(__DIR__) . '/includes/db_connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Geçersiz istek metodu']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$data) {
        throw new Exception('Geçersiz veri formatı');
    }
    
    // Program bilgilerini kaydet
    $program_query = "INSERT INTO programs (title, description, category, difficulty_level, trainer_id) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($program_query);
    $stmt->bind_param("ssssi", 
        $data['title'],
        $data['description'],
        $data['category'],
        $data['difficulty_level'],
        $data['trainer_id']
    );
    
    if (!$stmt->execute()) {
        throw new Exception('Program kaydedilemedi: ' . $stmt->error);
    }
    
    $program_id = $conn->insert_id;
    
    // Egzersizleri kaydet
    $exercise_query = "INSERT INTO program_exercises (program_id, day_number, exercise_order, exercise_name, sets, reps, weight, duration) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($exercise_query);
    
    foreach ($data['exercises'] as $exercise) {
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
    
    echo json_encode([
        'success' => true,
        'message' => 'Program başarıyla kaydedildi',
        'program_id' => $program_id
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?> 