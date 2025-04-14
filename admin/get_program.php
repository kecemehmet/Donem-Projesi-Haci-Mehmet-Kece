<?php
require_once dirname(__DIR__) . '/includes/db_connection.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Program ID gerekli']);
    exit;
}

try {
    $program_id = intval($_GET['id']);
    
    // Program bilgilerini getir
    $program_query = "SELECT * FROM programs WHERE id = ?";
    $stmt = $conn->prepare($program_query);
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Program bulunamadı');
    }
    
    $program = $result->fetch_assoc();
    
    // Program egzersizlerini getir
    $exercises_query = "SELECT * FROM program_exercises WHERE program_id = ? ORDER BY day_number, exercise_order";
    $stmt = $conn->prepare($exercises_query);
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $exercises_result = $stmt->get_result();
    
    $exercises = [];
    while ($exercise = $exercises_result->fetch_assoc()) {
        $exercises[] = [
            'day_number' => $exercise['day_number'],
            'exercise_order' => $exercise['exercise_order'],
            'exercise_name' => $exercise['exercise_name'],
            'sets' => $exercise['sets'],
            'reps' => $exercise['reps'],
            'weight' => $exercise['weight'],
            'duration' => $exercise['duration']
        ];
    }
    
    $program['exercises'] = $exercises;
    
    echo json_encode([
        'success' => true,
        'program' => $program
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?> 