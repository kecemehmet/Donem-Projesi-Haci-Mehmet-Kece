<?php
session_start();

// Admin kontrolü
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Veritabanı bağlantısı
$conn = new mysqli("localhost", "root", "", "fitness_db");
if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

// Admin kontrolü
$username = $_SESSION['username'];
$stmt = $conn->prepare("SELECT is_admin FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user || $user['is_admin'] != 1) {
    header("Location: index.php");
    exit();
}

// POST verilerini kontrol et
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $export_users = isset($_POST['export_users']);
    $export_feedback = isset($_POST['export_feedback']);
    $export_stats = isset($_POST['export_stats']);
    $include_headers = isset($_POST['include_headers']);
    
    // Dosya adı için timestamp
    $timestamp = date('Y-m-d_H-i-s');
    $filename = "fitmate_export_" . $timestamp . ".csv";
    
    // CSV başlıkları ayarla
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    
    // Çıktı için dosya işaretçisi oluştur
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM
    
    // Kullanıcı verileri
    if ($export_users) {
        fputcsv($output, ['KULLANICILAR']);
        if ($include_headers) {
            fputcsv($output, ['ID', 'Kullanıcı Adı', 'E-posta', 'Kayıt Tarihi', 'Son Giriş', 'Durum']);
        }
        
        $query = "SELECT id, username, email, created_at, last_login, 
                  CASE WHEN is_banned = 1 THEN 'Banlı' ELSE 'Aktif' END as status 
                  FROM users WHERE is_admin = 0";
        $result = $conn->query($query);
        
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, $row);
        }
        fputcsv($output, []); // Boş satır
    }
    
    // Geri bildirim verileri
    if ($export_feedback) {
        fputcsv($output, ['GERİ BİLDİRİMLER']);
        if ($include_headers) {
            fputcsv($output, ['ID', 'Kullanıcı', 'Geri Bildirim', 'Tarih', 'Durum', 'Admin Yanıtı']);
        }
        
        $query = "SELECT f.id, u.username, f.feedback_text, f.created_at, 
                  CASE WHEN f.admin_response IS NULL THEN 'Yanıt Bekliyor' ELSE 'Yanıtlandı' END as status,
                  f.admin_response
                  FROM feedback f 
                  LEFT JOIN users u ON f.user_id = u.id 
                  ORDER BY f.created_at DESC";
        $result = $conn->query($query);
        
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, $row);
        }
        fputcsv($output, []); // Boş satır
    }
    
    // İstatistik verileri
    if ($export_stats) {
        // Fitness hedefleri
        fputcsv($output, ['FİTNESS HEDEFLERİ']);
        if ($include_headers) {
            fputcsv($output, ['Hedef', 'Kullanıcı Sayısı']);
        }
        
        $query = "SELECT fitness_goal, COUNT(*) as count 
                  FROM users WHERE is_admin = 0 
                  GROUP BY fitness_goal";
        $result = $conn->query($query);
        
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, $row);
        }
        fputcsv($output, []); // Boş satır
        
        // Deneyim seviyeleri
        fputcsv($output, ['DENEYİM SEVİYELERİ']);
        if ($include_headers) {
            fputcsv($output, ['Seviye', 'Kullanıcı Sayısı']);
        }
        
        $query = "SELECT experience_level, COUNT(*) as count 
                  FROM users WHERE is_admin = 0 
                  GROUP BY experience_level";
        $result = $conn->query($query);
        
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, $row);
        }
        fputcsv($output, []); // Boş satır
        
        // Antrenman günleri
        fputcsv($output, ['ANTRENMAN GÜNLERİ']);
        if ($include_headers) {
            fputcsv($output, ['Gün Sayısı', 'Kullanıcı Sayısı']);
        }
        
        $query = "SELECT workout_days, COUNT(*) as count 
                  FROM users WHERE is_admin = 0 
                  GROUP BY workout_days 
                  ORDER BY workout_days";
        $result = $conn->query($query);
        
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, $row);
        }
    }
    
    fclose($output);
    exit();
}

$conn->close();
?> 