<?php
session_start();
require_once 'config.php';
checkLogin(); // config.php'deki checkLogin fonksiyonunu kullan

// Kullanıcı girişi kontrolü
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Kullanıcı bilgilerini al
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// BMI kontrolü
if (empty($user['height']) || empty($user['weight']) || $user['height'] <= 0 || $user['weight'] <= 0) {
    $_SESSION['message'] = 'Program görüntülemek için önce BMI hesaplamanız gerekiyor.';
    $_SESSION['message_type'] = 'warning';
    header('Location: calculate_bmi.php');
    exit;
}

// Deneyim seviyesi ve fitness hedefi için Türkçe karşılıklar
$experience_levels = [
    'beginner' => 'Başlangıç',
    'intermediate' => 'Orta Düzey',
    'advanced' => 'İleri Düzey'
];

$fitness_goals = [
    'weight_loss' => 'Kilo Verme',
    'muscle_gain' => 'Kas Kazanımı',
    'endurance' => 'Dayanıklılık',
    'flexibility' => 'Esneklik',
    'maintain' => 'Kilo Koruma'
];

// Dinlenme günü mesajları
$rest_messages = [
    "Dinlenme kasların büyümesi için önemlidir.",
    "Bugün vücudunuzu dinlendirin.",
    "İyi bir dinlenme, daha iyi performans demektir.",
    "Dinlenme günlerinde hafif yürüyüş yapabilirsiniz.",
    "Kaslarınızın toparlanmasına izin verin."
];

// Egzersiz veritabanı
$exercises = [
    'weight_loss' => [
        'beginner' => [
            ['name' => 'Tempolu Yürüyüş', 'sets' => 1, 'duration' => '30 dakika', 'equipment' => 'Yok', 'workout_name' => 'Kardiyo'],
            ['name' => 'Hafif Tempolu Koşu', 'sets' => 1, 'duration' => '15 dakika', 'equipment' => 'Yok', 'workout_name' => 'Kardiyo'],
            ['name' => 'Jumping Jacks', 'sets' => 3, 'reps' => '20', 'equipment' => 'Yok', 'workout_name' => 'HIIT'],
            ['name' => 'Diz Çekme', 'sets' => 3, 'reps' => '15', 'equipment' => 'Yok', 'workout_name' => 'HIIT'],
            ['name' => 'Squat', 'sets' => 3, 'reps' => '12', 'equipment' => 'Yok', 'workout_name' => 'Alt Vücut'],
            ['name' => 'Wall Push-ups', 'sets' => 3, 'reps' => '10', 'equipment' => 'Yok', 'workout_name' => 'Üst Vücut'],
            ['name' => 'Plank', 'sets' => 3, 'duration' => '30 saniye', 'equipment' => 'Yok', 'workout_name' => 'Core']
        ],
        'intermediate' => [
            ['name' => 'Interval Koşu', 'sets' => 1, 'duration' => '25 dakika', 'equipment' => 'Yok', 'workout_name' => 'Kardiyo'],
            ['name' => 'Mountain Climbers', 'sets' => 4, 'reps' => '20', 'equipment' => 'Yok', 'workout_name' => 'HIIT'],
            ['name' => 'Burpees', 'sets' => 3, 'reps' => '12', 'equipment' => 'Yok', 'workout_name' => 'HIIT'],
            ['name' => 'Jump Squats', 'sets' => 4, 'reps' => '15', 'equipment' => 'Yok', 'workout_name' => 'Alt Vücut'],
            ['name' => 'Push-ups', 'sets' => 3, 'reps' => '12', 'equipment' => 'Yok', 'workout_name' => 'Üst Vücut'],
            ['name' => 'Dumbbell Rows', 'sets' => 3, 'reps' => '12', 'equipment' => 'Dumbbell', 'workout_name' => 'Üst Vücut'],
            ['name' => 'Russian Twists', 'sets' => 3, 'reps' => '20', 'equipment' => 'Yok', 'workout_name' => 'Core']
        ],
        'advanced' => [
            ['name' => 'HIIT Sprint', 'sets' => 8, 'duration' => '30 saniye sprint + 30 saniye dinlenme', 'equipment' => 'Yok', 'workout_name' => 'Kardiyo'],
            ['name' => 'Box Jumps', 'sets' => 4, 'reps' => '15', 'equipment' => 'Box', 'workout_name' => 'Pliometrik'],
            ['name' => 'Pistol Squats', 'sets' => 3, 'reps' => '8 (her bacak)', 'equipment' => 'Yok', 'workout_name' => 'Alt Vücut'],
            ['name' => 'Plyometric Push-ups', 'sets' => 4, 'reps' => '10', 'equipment' => 'Yok', 'workout_name' => 'Üst Vücut'],
            ['name' => 'Muscle Ups', 'sets' => 3, 'reps' => '5', 'equipment' => 'Bar', 'workout_name' => 'Üst Vücut'],
            ['name' => 'Dragon Flags', 'sets' => 3, 'reps' => '8', 'equipment' => 'Bench', 'workout_name' => 'Core']
        ]
    ],
    'muscle_gain' => [
        'beginner' => [
            ['name' => 'Goblet Squats', 'sets' => 3, 'reps' => '12', 'equipment' => 'Dumbbell', 'workout_name' => 'Alt Vücut'],
            ['name' => 'Dumbbell Bench Press', 'sets' => 3, 'reps' => '12', 'equipment' => 'Dumbbell', 'workout_name' => 'Göğüs'],
            ['name' => 'Assisted Pull-ups', 'sets' => 3, 'reps' => '8', 'equipment' => 'Band', 'workout_name' => 'Sırt'],
            ['name' => 'Dumbbell Shoulder Press', 'sets' => 3, 'reps' => '12', 'equipment' => 'Dumbbell', 'workout_name' => 'Omuz'],
            ['name' => 'Dumbbell Rows', 'sets' => 3, 'reps' => '12', 'equipment' => 'Dumbbell', 'workout_name' => 'Sırt']
        ],
        'intermediate' => [
            ['name' => 'Barbell Squats', 'sets' => 4, 'reps' => '10', 'equipment' => 'Barbell', 'workout_name' => 'Alt Vücut'],
            ['name' => 'Barbell Bench Press', 'sets' => 4, 'reps' => '10', 'equipment' => 'Barbell', 'workout_name' => 'Göğüs'],
            ['name' => 'Pull-ups', 'sets' => 4, 'reps' => '8', 'equipment' => 'Bar', 'workout_name' => 'Sırt'],
            ['name' => 'Military Press', 'sets' => 4, 'reps' => '10', 'equipment' => 'Barbell', 'workout_name' => 'Omuz'],
            ['name' => 'Bent Over Rows', 'sets' => 4, 'reps' => '10', 'equipment' => 'Barbell', 'workout_name' => 'Sırt']
        ],
        'advanced' => [
            ['name' => 'Bulgarian Split Squats', 'sets' => 4, 'reps' => '12 her bacak', 'equipment' => 'Dumbbell', 'workout_name' => 'Alt Vücut'],
            ['name' => 'Weighted Dips', 'sets' => 4, 'reps' => '12', 'equipment' => 'Weight Belt', 'workout_name' => 'Göğüs'],
            ['name' => 'Weighted Pull-ups', 'sets' => 4, 'reps' => '10', 'equipment' => 'Weight Belt', 'workout_name' => 'Sırt'],
            ['name' => 'Clean and Press', 'sets' => 4, 'reps' => '8', 'equipment' => 'Barbell', 'workout_name' => 'Tam Vücut'],
            ['name' => 'Deadlifts', 'sets' => 4, 'reps' => '8', 'equipment' => 'Barbell', 'workout_name' => 'Sırt']
        ]
    ],
    'maintain' => [
        'beginner' => [
            ['name' => 'Tempolu Yürüyüş', 'sets' => 1, 'duration' => '30 dakika', 'equipment' => 'Yok', 'workout_name' => 'Kardiyo'],
            ['name' => 'Hafif Tempolu Koşu', 'sets' => 1, 'duration' => '20 dakika', 'equipment' => 'Yok', 'workout_name' => 'Kardiyo'],
            ['name' => 'Squat', 'sets' => 3, 'reps' => '12', 'equipment' => 'Yok', 'workout_name' => 'Alt Vücut'],
            ['name' => 'Push-up', 'sets' => 3, 'reps' => '10', 'equipment' => 'Yok', 'workout_name' => 'Üst Vücut'],
            ['name' => 'Plank', 'sets' => 3, 'duration' => '30 saniye', 'equipment' => 'Yok', 'workout_name' => 'Core'],
            ['name' => 'Lunge', 'sets' => 3, 'reps' => '10 (her bacak)', 'equipment' => 'Yok', 'workout_name' => 'Alt Vücut']
        ],
        'intermediate' => [
            ['name' => 'Interval Koşu', 'sets' => 1, 'duration' => '25 dakika', 'equipment' => 'Yok', 'workout_name' => 'Kardiyo'],
            ['name' => 'Jump Rope', 'sets' => 3, 'duration' => '5 dakika', 'equipment' => 'İp', 'workout_name' => 'Kardiyo'],
            ['name' => 'Goblet Squat', 'sets' => 4, 'reps' => '12', 'equipment' => 'Dumbbell', 'workout_name' => 'Alt Vücut'],
            ['name' => 'Dumbbell Shoulder Press', 'sets' => 3, 'reps' => '12', 'equipment' => 'Dumbbell', 'workout_name' => 'Omuz'],
            ['name' => 'Russian Twist', 'sets' => 3, 'reps' => '20', 'equipment' => 'Dumbbell', 'workout_name' => 'Core'],
            ['name' => 'Dumbbell Row', 'sets' => 3, 'reps' => '12', 'equipment' => 'Dumbbell', 'workout_name' => 'Sırt']
        ],
        'advanced' => [
            ['name' => 'HIIT Sprint', 'sets' => 8, 'duration' => '30 saniye sprint + 30 saniye dinlenme', 'equipment' => 'Yok', 'workout_name' => 'Kardiyo'],
            ['name' => 'Box Jump', 'sets' => 4, 'reps' => '12', 'equipment' => 'Box', 'workout_name' => 'Pliometrik'],
            ['name' => 'Barbell Squat', 'sets' => 4, 'reps' => '10', 'equipment' => 'Barbell', 'workout_name' => 'Alt Vücut'],
            ['name' => 'Barbell Bench Press', 'sets' => 4, 'reps' => '10', 'equipment' => 'Barbell', 'workout_name' => 'Göğüs'],
            ['name' => 'Deadlift', 'sets' => 4, 'reps' => '8', 'equipment' => 'Barbell', 'workout_name' => 'Sırt'],
            ['name' => 'Pull-up', 'sets' => 4, 'reps' => '8', 'equipment' => 'Bar', 'workout_name' => 'Sırt']
        ]
    ],
    'endurance' => [
        'beginner' => [
            ['name' => 'Hafif Tempolu Koşu', 'sets' => 1, 'duration' => '20 dakika', 'equipment' => 'Yok', 'workout_name' => 'Kardiyo'],
            ['name' => 'Bisiklet', 'sets' => 1, 'duration' => '30 dakika', 'equipment' => 'Bisiklet', 'workout_name' => 'Kardiyo'],
            ['name' => 'Yüzme', 'sets' => 1, 'duration' => '20 dakika', 'equipment' => 'Yok', 'workout_name' => 'Kardiyo'],
            ['name' => 'Jump Rope', 'sets' => 3, 'duration' => '5 dakika', 'equipment' => 'İp', 'workout_name' => 'Kardiyo']
        ],
        'intermediate' => [
            ['name' => 'Interval Koşu', 'sets' => 1, 'duration' => '30 dakika', 'equipment' => 'Yok', 'workout_name' => 'Kardiyo'],
            ['name' => 'Bisiklet Sprint', 'sets' => 1, 'duration' => '45 dakika', 'equipment' => 'Bisiklet', 'workout_name' => 'Kardiyo'],
            ['name' => 'Yüzme Sprint', 'sets' => 1, 'duration' => '30 dakika', 'equipment' => 'Yok', 'workout_name' => 'Kardiyo'],
            ['name' => 'HIIT', 'sets' => 4, 'duration' => '20 dakika', 'equipment' => 'Yok', 'workout_name' => 'Kardiyo']
        ],
        'advanced' => [
            ['name' => 'Uzun Mesafe Koşu', 'sets' => 1, 'duration' => '60 dakika', 'equipment' => 'Yok', 'workout_name' => 'Kardiyo'],
            ['name' => 'Bisiklet Maraton', 'sets' => 1, 'duration' => '90 dakika', 'equipment' => 'Bisiklet', 'workout_name' => 'Kardiyo'],
            ['name' => 'Triatlon Antrenmanı', 'sets' => 1, 'duration' => '120 dakika', 'equipment' => 'Çeşitli', 'workout_name' => 'Kardiyo'],
            ['name' => 'HIIT Sprint', 'sets' => 8, 'duration' => '30 dakika', 'equipment' => 'Yok', 'workout_name' => 'Kardiyo']
        ]
    ]
];

// Kullanıcı bilgilerini al
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header("Location: logout.php");
    exit();
}

// URL'de id parametresi var mı kontrol et
if (!isset($_GET['id'])) {
    // Program oluşturma modu
    // Kullanıcının deneyim seviyesini ve fitness hedefini al
    $stmt = $conn->prepare("SELECT experience_level, fitness_goal FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_prefs = $result->fetch_assoc();

    $experience_level = $user_prefs['experience_level'] ?? 'beginner';
    $fitness_goal = $user_prefs['fitness_goal'] ?? 'weight_loss';

    // Deneyim seviyesi ve fitness hedefi için Türkçe karşılıklar
    $experience_level_tr = $experience_levels[$experience_level] ?? 'Başlangıç';
    $fitness_goal_tr = $fitness_goals[$fitness_goal] ?? 'Genel Fitness';

    // Antrenman programı oluşturma
    $workout_days = $user['workout_days'] ?? 3;
    $workout_duration = 60;

    // Haftalık program oluşturma
    $weekly_program = [];
    $days = ['Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi', 'Pazar'];

    // Her gün için program oluşturma
    for ($i = 0; $i < 7; $i++) {
        if ($i < $workout_days) {
            $daily_exercises = [];
            $total_duration = 0;
            $added_exercise_names = [];
            static $safety_counter = 0;

            $available_exercises = isset($exercises[$fitness_goal][$experience_level])
                ? $exercises[$fitness_goal][$experience_level]
                : ($exercises['general_fitness']['beginner'] ?? []);

            while ($total_duration < $workout_duration && !empty($available_exercises)) {
                $exercise_key = array_rand($available_exercises);
                $exercise = $available_exercises[$exercise_key];

                if (isset($exercise['name']) && !in_array($exercise['name'], $added_exercise_names)) {
                    $daily_exercises[] = $exercise;
                    $added_exercise_names[] = $exercise['name'];
                    $total_duration += 15;
                    unset($available_exercises[$exercise_key]);
                } elseif (empty($available_exercises) || count($added_exercise_names) >= count($exercises[$fitness_goal][$experience_level] ?? [])) {
                    break;
                }

                if (++$safety_counter > 50) break;
            }
            $safety_counter = 0;

            if (!empty($daily_exercises)) {
                $weekly_program[$days[$i]] = [
                    'type' => 'workout',
                    'exercises' => $daily_exercises,
                    'duration' => $total_duration
                ];
            } else {
                $weekly_program[$days[$i]] = [
                    'type' => 'rest',
                    'message' => 'Uygun egzersiz bulunamadı, bugün dinlenin.'
                ];
            }
        } else {
            $weekly_program[$days[$i]] = [
                'type' => 'rest',
                'message' => $rest_messages[array_rand($rest_messages)]
            ];
        }
    }

    // Programı veritabanına kaydet
    try {
        $conn->begin_transaction();

        // Önce mevcut programı kontrol et
        $check_query = "SELECT id FROM programs WHERE user_id = ? AND is_active = 1";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Mevcut programı pasife al
            $update_query = "UPDATE programs SET is_active = 0 WHERE user_id = ? AND is_active = 1";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("i", $_SESSION['user_id']);
            $stmt->execute();
            
            // Mevcut programın egzersizlerini sil
            $program = $result->fetch_assoc();
            $delete_exercises = "DELETE FROM program_exercises WHERE program_id = ?";
            $stmt = $conn->prepare($delete_exercises);
            $stmt->bind_param("i", $program['id']);
            $stmt->execute();
            
            // Program başlığı oluştur
            $title = $user['username'] . " " . $fitness_goal_tr . " Programı";
            $description = "BMI: " . number_format($user['bmi'], 1) . ", Deneyim: " . $experience_level_tr . ", Hedef: " . $fitness_goal_tr;
            
            // Mevcut programı güncelle
            $update_program = "UPDATE programs SET title = ?, description = ?, category = ?, difficulty_level = ?, is_active = 1 WHERE id = ?";
            $stmt = $conn->prepare($update_program);
            $stmt->bind_param("ssssi", 
                $title,
                $description,
                $fitness_goal,
                $experience_level,
                $program['id']
            );
            
            if (!$stmt->execute()) {
                throw new Exception('Program güncellenemedi: ' . $stmt->error);
            }
            
            $program_id = $program['id'];
        } else {
            // Yeni program oluştur
            $title = $user['username'] . " " . $fitness_goal_tr . " Programı";
            $description = "BMI: " . number_format($user['bmi'], 1) . ", Deneyim: " . $experience_level_tr . ", Hedef: " . $fitness_goal_tr;
            
            $program_query = "INSERT INTO programs (title, description, category, difficulty_level, trainer_id, user_id, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)";
            $stmt = $conn->prepare($program_query);
            $stmt->bind_param("ssssii", 
                $title,
                $description,
                $fitness_goal,
                $experience_level,
                $_SESSION['user_id'],
                $_SESSION['user_id']
            );
            
            if (!$stmt->execute()) {
                throw new Exception('Program kaydedilemedi: ' . $stmt->error);
            }
            
            $program_id = $conn->insert_id;
        }
        
        // Egzersizleri kaydet
        $exercise_query = "INSERT INTO program_exercises (program_id, day_number, exercise_order, exercise_name, sets, reps, weight, duration) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($exercise_query);
        
        foreach ($weekly_program as $day => $program) {
            if ($program['type'] === 'workout') {
                foreach ($program['exercises'] as $order => $exercise) {
                    $day_number = array_search($day, $days) + 1;
                    $sets = $exercise['sets'] ?? 0;
                    $reps = isset($exercise['reps']) ? (int)$exercise['reps'] : 0;
                    $weight = 0;
                    $duration = isset($exercise['duration']) ? (int)str_replace([' dakika', ' saniye'], '', $exercise['duration']) : 0;
                    
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
                        throw new Exception('Egzersiz kaydedilemedi: ' . $stmt->error);
                    }
                }
            }
        }
        
        $conn->commit();
        $_SESSION['success_message'] = "Değişiklikler başarıyla kaydedildi";
        
        // Programı görüntüleme sayfasına yönlendir
        header("Location: view_program.php?id=" . $program_id);
        exit;
        
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = "Değişiklikler başarıyla kaydedildi";
        header("Location: dashboard.php");
        exit;
    }
} else {
    // Program görüntüleme modu
    $program_id = intval($_GET['id']);

    // Program bilgilerini getir
    $program_query = "SELECT p.*, u.username as trainer_name, u.experience_level, u.fitness_goal 
                     FROM programs p 
                     LEFT JOIN users u ON p.user_id = u.id 
                     WHERE p.id = ?";
    $stmt = $conn->prepare($program_query);
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        header('Location: dashboard.php');
        exit;
    }

    $program = $result->fetch_assoc();

    // Program sahibinin bilgilerini al
    $owner_query = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($owner_query);
    $stmt->bind_param("i", $program['user_id']);
    $stmt->execute();
    $owner = $stmt->get_result()->fetch_assoc();

    // Program egzersizlerini getir
    $exercises_query = "SELECT * FROM program_exercises WHERE program_id = ? ORDER BY day_number, exercise_order";
    $stmt = $conn->prepare($exercises_query);
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $exercises_result = $stmt->get_result();

    $exercises_from_db = [];
    while ($exercise = $exercises_result->fetch_assoc()) {
        $exercises_from_db[] = $exercise;
    }

    // Veritabanından çekilen egzersizleri günlere göre grupla
    $weekly_program_from_db = [];
    $days = ['Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi', 'Pazar'];

    foreach ($days as $index => $day) {
        $day_number = $index + 1;
        $daily_exercises = [];
        $has_workout = false;
        $total_duration_for_day = 0;

        foreach ($exercises_from_db as $ex) {
            if ($ex['day_number'] == $day_number) {
                $duration_display = '';
                if (!empty($ex['duration']) && $ex['duration'] > 0) {
                    $duration_display = $ex['duration'] . ' dakika';
                    $total_duration_for_day += (int)$ex['duration'];
                }

                $reps_display = '';
                if (!empty($ex['reps']) && $ex['reps'] > 0) {
                    $reps_display = 'x ' . $ex['reps'];
                }

                $sets_display = '';
                if (!empty($ex['sets']) && $ex['sets'] > 0) {
                    $sets_display = $ex['sets'] . ' set';
                }

                $daily_exercises[] = [
                    'name' => $ex['exercise_name'],
                    'sets' => $sets_display,
                    'reps' => $reps_display,
                    'duration' => $duration_display,
                    'raw_sets' => $ex['sets'],
                    'raw_reps' => $ex['reps'],
                    'raw_duration' => $ex['duration'],
                    'exercise_id' => $ex['id']
                ];
                $has_workout = true;
            }
        }

        if ($has_workout) {
            if ($total_duration_for_day == 0 && count($daily_exercises) > 0) {
                $total_duration_for_day = count($daily_exercises) * 15;
                $duration_note = '(Tahmini)';
            } else {
                $duration_note = '';
            }

            $weekly_program_from_db[$day] = [
                'type' => 'workout',
                'exercises' => $daily_exercises,
                'duration' => $total_duration_for_day,
                'duration_note' => $duration_note
            ];
        } else {
            $weekly_program_from_db[$day] = [
                'type' => 'rest',
                'message' => $rest_messages[array_rand($rest_messages)],
                'duration' => 0,
                'duration_note' => ''
            ];
        }
    }

    // Admin kontrolü
    $is_admin = isset($_SESSION['user_id']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

// Başarı mesajını tanımla
$success_message = '';
if (isset($_SESSION['success_message']) && !isset($_GET['id'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Hata mesajını tanımla
$error_message = '';
if (isset($_SESSION['error_message']) && !isset($_GET['id'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

// Kullanıcının mevcut programını güncelle
$stmt = $conn->prepare("UPDATE custom_workout_programs SET activity = ? WHERE user_id = ? AND day = ?");

foreach ($weekly_program_from_db as $day => $program) {
    $program_json = json_encode($program, JSON_UNESCAPED_UNICODE);
    $stmt->bind_param("sis", $program_json, $_SESSION['user_id'], $day);
    $stmt->execute();
}

// Kullanıcının antrenman programını al
$stmt = $conn->prepare("SELECT * FROM custom_workout_programs WHERE user_id = ? ORDER BY FIELD(day, 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi', 'Pazar')");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$workout_program = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Günlere göre programı düzenle
$program_by_day = [];
foreach ($workout_program as $program) {
    $program_by_day[$program['day']] = json_decode($program['activity'], true);
}

// Sıralı program oluştur
$sirali_program = [];
foreach ($days as $day) {
    if (isset($program_by_day[$day])) {
        $sirali_program[$day] = $program_by_day[$day];
    } else {
        // Eğer gün programda yoksa, boş bir dizi ekle
        $sirali_program[$day] = [];
    }
}
$program_by_day = $sirali_program;
?>

<!DOCTYPE html>
<html lang="tr" data-theme="<?php echo isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($program) ? $program['title'] : 'Antrenman Programım'; ?> - FitMate</title>
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/navbar.css">
    <style>
        .program-container {
            padding: 2rem 0;
            margin-top: 76px;
            min-height: calc(100vh - 76px);
            background: linear-gradient(135deg, var(--bg-color) 0%, var(--card-bg) 100%);
        }

        .program-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            box-shadow: 0 4px 6px var(--shadow-color);
        }

        .program-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary-btn-bg);
            box-shadow: 0 10px 20px var(--shadow-color);
        }

        .day-header {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border-color);
            color: var(--text-color);
        }

        .exercise-item {
            background: var(--section-bg-light);
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .exercise-item:hover {
            transform: translateX(10px);
            box-shadow: 0 2px 4px var(--shadow-color);
        }

        .exercise-name {
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 0.5rem;
        }

        .exercise-details {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .rest-day {
            text-align: center;
            padding: 2rem;
            background: linear-gradient(45deg, var(--primary-btn-bg), var(--secondary-btn-bg));
            color: white;
            border-radius: 15px;
            margin-bottom: 1rem;
        }

        .rest-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .rest-message {
            font-size: 1.1rem;
            font-weight: 500;
        }

        .stats-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px var(--shadow-color);
        }

        .stat-item {
            text-align: center;
            padding: 1rem;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-btn-bg);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .success-message {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            text-align: center;
            animation: slideDown 0.5s ease-out;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Sürükleme stilleri */
        .edit-mode .program-card {
            cursor: move;
        }

        .edit-mode .exercise-item {
            cursor: move;
            position: relative;
            padding-right: 40px;
        }

        .edit-mode .exercise-item::after {
            content: '⋮';
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 20px;
            color: var(--text-color);
            opacity: 0.5;
        }

        .edit-mode .exercise-item:hover::after {
            opacity: 1;
        }

        .sortable-ghost {
            opacity: 0.5;
            background: var(--primary-btn-bg) !important;
        }

        .sortable-chosen {
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
        }

        .edit-controls {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
            display: flex;
            gap: 10px;
        }

        .edit-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            transition: all 0.3s ease;
            border: none;
            color: white;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .edit-btn.edit {
            background: linear-gradient(45deg, #3b82f6, #60a5fa);
        }

        .edit-btn.save {
            background: linear-gradient(45deg, #22c55e, #4ade80);
            display: none;
        }

        .edit-btn.cancel {
            background: linear-gradient(45deg, #ef4444, #f87171);
            display: none;
        }

        .edit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        }

        .edit-mode .edit-btn.edit {
            display: none;
        }

        .edit-mode .edit-btn.save,
        .edit-mode .edit-btn.cancel {
            display: flex;
        }

        /* Dark modda program kartları içindeki text-muted için renk */
        [data-theme='dark'] .program-card .text-muted {
            color: rgba(255, 255, 255, 0.7) !important; /* Açık gri */
        }

        [data-theme='dark'] .exercise-details {
             color: rgba(255, 255, 255, 0.7) !important; /* Açık gri */
        }

        /* Toast bildirimleri için container */
        .toast-container {
            z-index: 1090; /* Navbarın üzerinde olması için */
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <!-- Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <?php if ($success_message): ?>
            <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo $success_message; ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo $error_message; ?>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="program-container">
        <div class="container">
            <div class="stats-card" data-aos="fade-up">
                <div class="row">
                    <div class="col-md-3 col-6">
                        <div class="stat-item">
                            <div class="stat-value"><?php echo number_format(isset($owner) ? $owner['bmi'] : $user['bmi'], 1); ?></div>
                            <div class="stat-label">BMI</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="stat-item">
                            <div class="stat-value"><?php echo isset($owner) ? $owner['workout_days'] : $workout_days; ?></div>
                            <div class="stat-label">Antrenman Günü</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="stat-item">
                            <div class="stat-value">
                                <?php
                                $fitness_goal = isset($owner) ? $owner['fitness_goal'] : $user['fitness_goal'];
                                switch($fitness_goal) {
                                    case 'weight_loss':
                                        echo '<i class="fas fa-weight-scale"></i>';
                                        break;
                                    case 'muscle_gain':
                                        echo '<i class="fas fa-dumbbell"></i>';
                                        break;
                                    case 'maintain':
                                        echo '<i class="fas fa-balance-scale"></i>';
                                        break;
                                    case 'endurance':
                                        echo '<i class="fas fa-running"></i>';
                                        break;
                                    case 'flexibility':
                                        echo '<i class="fas fa-yoga"></i>';
                                        break;
                                    case 'strength':
                                        echo '<i class="fas fa-fist-raised"></i>';
                                        break;
                                    case 'general':
                                        echo '<i class="fas fa-heartbeat"></i>';
                                        break;
                                }
                                ?>
                            </div>
                            <div class="stat-label">
                                <?php echo isset($owner) ? $fitness_goals[$owner['fitness_goal']] : $fitness_goal_tr; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="stat-item">
                            <div class="stat-value">
                                <?php
                                $experience_level = isset($owner) ? $owner['experience_level'] : $user['experience_level'];
                                switch($experience_level) {
                                    case 'beginner':
                                        echo '<i class="fas fa-star"></i>';
                                        break;
                                    case 'intermediate':
                                        echo '<i class="fas fa-star"></i><i class="fas fa-star"></i>';
                                        break;
                                    case 'advanced':
                                        echo '<i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>';
                                        break;
                                }
                                ?>
                            </div>
                            <div class="stat-label">
                                <?php echo isset($owner) ? $experience_levels[$owner['experience_level']] : $experience_level_tr; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row" id="program-container">
                <?php foreach ($days as $day): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="program-card" data-day="<?php echo $day; ?>" data-aos="fade-up">
                            <h3 class="day-header">
                                <i class="fas fa-calendar-day me-2"></i>
                                <?php echo $day; ?>
                            </h3>

                            <?php if (isset($weekly_program_from_db[$day])): ?>
                                <?php if ($weekly_program_from_db[$day]['type'] === 'workout'): ?>
                                    <div class="exercise-list" data-day="<?php echo $day; ?>">
                                        <?php foreach ($weekly_program_from_db[$day]['exercises'] as $exercise): ?>
                                            <?php if (isset($exercise['name'])): ?>
                                                <div class="exercise-item" data-exercise='<?php echo json_encode($exercise); ?>'>
                                                    <div class="exercise-name">
                                                        <i class="fas fa-dumbbell me-2"></i>
                                                        <?php echo $exercise['name']; ?>
                                                    </div>
                                                    <div class="exercise-details">
                                                        <?php if (isset($exercise['sets'])): ?>
                                                            <span><?php echo $exercise['sets']; ?></span>
                                                        <?php endif; ?>
                                                        
                                                        <?php if (isset($exercise['reps'])): ?>
                                                            <span><?php echo $exercise['reps']; ?></span>
                                                        <?php endif; ?>
                                                        
                                                        <?php if (isset($exercise['duration']) && $exercise['duration']): ?>
                                                            <span><i class="fas fa-stopwatch me-1"></i> <?php echo $exercise['duration']; ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                    <div class="text-muted mt-3">
                                        <i class="fas fa-clock me-2"></i>
                                        Toplam süre: <?php echo $weekly_program_from_db[$day]['duration']; ?> dakika <?php echo $weekly_program_from_db[$day]['duration_note']; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="rest-day">
                                        <i class="fas fa-bed rest-icon"></i>
                                        <div class="rest-message"><?php echo $weekly_program_from_db[$day]['message']; ?></div>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <p>Bu gün için program bilgisi bulunamadı.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($is_admin && isset($program)): ?>
                <div class="edit-controls">
                    <button class="edit-btn edit" data-tooltip="Programı Düzenle">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="edit-btn save" data-tooltip="Değişiklikleri Kaydet">
                        <i class="fas fa-save"></i>
                    </button>
                    <button class="edit-btn cancel" data-tooltip="İptal Et">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <?php if ($is_admin && isset($program)): ?>
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <?php endif; ?>
    <script src="js/theme.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            once: true
        });

        <?php if ($is_admin && isset($program)): ?>
            // Program düzenleme fonksiyonları
            const programContainer = document.getElementById('program-container');
            const editBtn = document.querySelector('.edit-btn.edit');
            const saveBtn = document.querySelector('.edit-btn.save');
            const cancelBtn = document.querySelector('.edit-btn.cancel');
            let exerciseLists = document.querySelectorAll('.exercise-list');
            let originalProgram = null;
            let sortableInstances = [];

            // Günler arası sıralama
            const containerSortable = new Sortable(programContainer, {
                animation: 150,
                handle: '.day-header',
                draggable: '.col-lg-4',
                disabled: true
            });

            // Her gün için egzersiz sıralama
            exerciseLists.forEach(list => {
                const sortableInstance = new Sortable(list, {
                    animation: 150,
                    group: 'exercises',
                    disabled: true
                });
                sortableInstances.push(sortableInstance);
            });

            // Düzenleme modunu aç
            editBtn.addEventListener('click', function() {
                originalProgram = JSON.stringify(getProgramData());
                document.body.classList.add('edit-mode');
                containerSortable.option('disabled', false);
                sortableInstances.forEach(instance => {
                    instance.option('disabled', false);
                });
            });

            // Değişiklikleri kaydet
            saveBtn.addEventListener('click', async function() {
                const newProgram = getProgramData();
                try {
                    const response = await fetch('update_program_exercises.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            program_id: <?php echo $program_id; ?>,
                            program: newProgram
                        })
                    });

                    if (response.ok) {
                        const result = await response.json();
                        if (result.success) {
                            containerSortable.option('disabled', true);
                            sortableInstances.forEach(instance => {
                                instance.option('disabled', true);
                            });
                            document.body.classList.remove('edit-mode');
                            showToast('Değişiklikler başarıyla kaydedildi', 'success');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            throw new Error(result.message || 'Kayıt başarısız');
                        }
                    } else {
                        throw new Error('Sunucu hatası');
                    }
                } catch (error) {
                    console.error('Kayıt hatası:', error);
                    showToast('Değişiklikler kaydedilirken bir hata oluştu: ' + error.message, 'error');
                }
            });

            // Düzenlemeyi iptal et
            cancelBtn.addEventListener('click', function() {
                if (originalProgram) {
                    const program = JSON.parse(originalProgram);
                    restoreProgram(program);
                }
                containerSortable.option('disabled', true);
                sortableInstances.forEach(instance => {
                    instance.option('disabled', true);
                });
                document.body.classList.remove('edit-mode');
            });

            // Program verilerini al
            function getProgramData() {
                const program = {};
                document.querySelectorAll('.program-card').forEach(card => {
                    const day = card.dataset.day;
                    const exercises = [];
                    card.querySelectorAll('.exercise-item').forEach(item => {
                        exercises.push(JSON.parse(item.dataset.exercise));
                    });
                    program[day] = exercises;
                });
                return program;
            }

            // Programı geri yükle
            function restoreProgram(program) {
                Object.entries(program).forEach(([day, exercises]) => {
                    const list = document.querySelector(`.exercise-list[data-day="${day}"]`);
                    if (list) {
                        list.innerHTML = exercises.map(exercise => `
                            <div class="exercise-item" data-exercise='${JSON.stringify(exercise)}'>
                                <div class="exercise-name">
                                    <i class="fas fa-dumbbell me-2"></i>${exercise.name}
                                </div>
                                <div class="exercise-details">
                                    <span>${exercise.sets}</span>
                                    <span>${exercise.reps}</span>
                                    ${exercise.duration ? `<span>${exercise.duration}</span>` : ''}
                                </div>
                            </div>
                        `).join('');
                    }
                });
            }
        <?php endif; ?>

        // Bildirim göster
        function showToast(message, type) {
            const toastContainer = document.querySelector('.toast-container');
            if (!toastContainer) return;

            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-white bg-${type === 'error' ? 'danger' : type} border-0`;
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'assertive');
            toast.setAttribute('aria-atomic', 'true');
            
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'} me-2"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            `;
            
            toastContainer.appendChild(toast);
            
            const bsToast = new bootstrap.Toast(toast, { 
                delay: 3000
            });
            bsToast.show();
            
            toast.addEventListener('hidden.bs.toast', () => {
                toast.remove();
            });
        }

        // Sayfa yüklendiğinde toast'ları göster
        document.addEventListener('DOMContentLoaded', function() {
            const toasts = document.querySelectorAll('.toast');
            toasts.forEach(toast => {
                const bsToast = new bootstrap.Toast(toast);
                bsToast.show();
            });
        });
    </script>
</body>
</html> 