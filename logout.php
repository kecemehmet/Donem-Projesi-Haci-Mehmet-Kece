<?php
session_start();

// Tüm oturum verilerini temizle
$_SESSION = array();

// Oturum çerezini sil
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Oturumu sonlandır
session_destroy();

// Başarılı çıkış mesajını ayarla
session_start();
$_SESSION['message'] = "Başarıyla çıkış yaptınız.";
$_SESSION['message_type'] = "success";

// Login sayfasına yönlendir
header("Location: login.php");
exit();
?> 