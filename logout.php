<?php
session_start(); // Oturumu başlat

// Oturumu temizle ve sonlandır
session_unset(); // Tüm oturum değişkenlerini temizle
session_destroy(); // Oturumu yok et

// Anasayfaya yönlendir
header("Location: index.php");
exit();
?>