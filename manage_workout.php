<?php
session_start();

if (!isset($_SESSION['username']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: index.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fitness_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'];
    $workouts = $_POST['workouts'];

    // Önce kullanıcının mevcut programını sil
    $conn->query("DELETE FROM custom_workout_programs WHERE user_id = $user_id");

    // Yeni programı ekle
    $stmt = $conn->prepare("INSERT INTO custom_workout_programs (user_id, day, activity, image) VALUES (?, ?, ?, ?)");
    foreach ($workouts as $day => $data) {
        if (!empty($data['activity'])) {
            $activity = $data['activity'];
            $image = !empty($data['image']) ? $data['image'] : null;
            $stmt->bind_param("isss", $user_id, $day, $activity, $image);
            $stmt->execute();
        }
    }
    $stmt->close();

    header("Location: admin.php?success=Antrenman programı başarıyla kaydedildi!");
    exit();
}

$conn->close();
?>