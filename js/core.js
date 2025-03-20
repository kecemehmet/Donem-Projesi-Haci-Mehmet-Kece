// AOS Animasyonlarını Başlat
AOS.init({
    once: false, // Animasyonlar her kaydırmada tekrar oynar
    offset: 50,  // Animasyonun tetiklenme mesafesini azaltır
    duration: 1000 // Animasyon süresi
});

// Sayfanın yüklenmesi tamamlandığında AOS'u yenile
window.addEventListener('load', function() {
    AOS.refresh();
});

// Sayfanın boyutları değiştiğinde AOS'u yenile
window.addEventListener('resize', function() {
    AOS.refresh();
});

// Sayfayı kaydırdığında AOS'u yenile
window.addEventListener('scroll', function() {
    AOS.refresh();
});