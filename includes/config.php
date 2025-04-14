<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Veritabanı bağlantısı
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fitness_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Veritabanı bağlantısı başarısız: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

// Tema değişkenleri
$theme = isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light';

// CSS değişkenleri
$css_variables = [
    'light' => [
        '--primary-color' => '#007bff',
        '--secondary-color' => '#6c757d',
        '--success-color' => '#28a745',
        '--danger-color' => '#dc3545',
        '--warning-color' => '#ffc107',
        '--info-color' => '#17a2b8',
        '--light-color' => '#f8f9fa',
        '--dark-color' => '#343a40',
        '--body-bg' => '#f8f9fa',
        '--text-color' => '#212529',
        '--card-bg' => '#ffffff',
        '--border-color' => '#dee2e6',
        '--shadow-color' => 'rgba(0, 0, 0, 0.15)',
        '--primary-btn-bg' => '#007bff',
        '--primary-btn-color' => '#ffffff',
        '--secondary-btn-bg' => '#6c757d',
        '--secondary-btn-color' => '#ffffff'
    ],
    'dark' => [
        '--primary-color' => '#0d6efd',
        '--secondary-color' => '#6c757d',
        '--success-color' => '#198754',
        '--danger-color' => '#dc3545',
        '--warning-color' => '#ffc107',
        '--info-color' => '#0dcaf0',
        '--light-color' => '#f8f9fa',
        '--dark-color' => '#212529',
        '--body-bg' => '#212529',
        '--text-color' => '#f8f9fa',
        '--card-bg' => '#2c3034',
        '--border-color' => '#373b3e',
        '--shadow-color' => 'rgba(0, 0, 0, 0.3)',
        '--primary-btn-bg' => '#0d6efd',
        '--primary-btn-color' => '#ffffff',
        '--secondary-btn-bg' => '#6c757d',
        '--secondary-btn-color' => '#ffffff'
    ]
];

// Hata raporlama
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Zaman dilimi
date_default_timezone_set('Europe/Istanbul');

// Güvenlik ayarları
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 1);
}

// XSS koruması
if (!headers_sent()) {
    header('X-XSS-Protection: 1; mode=block');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
}

// CSRF token oluşturma
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// CSRF token kontrolü
function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Admin kontrolü
function isAdmin() {
    return isset($_SESSION['user_id']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
}

// Kullanıcı kontrolü
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Güvenli çıkış
function logout() {
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit();
}
?> 