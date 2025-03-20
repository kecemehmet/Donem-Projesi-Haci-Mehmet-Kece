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

// Alert time
setTimeout(() => {
    const alert = document.querySelector('.alert');
    if (alert) alert.classList.remove('show');
}, 5000); // 5 saniye sonra kapanır

window.addEventListener('load', function() {
    const loadingScreen = document.getElementById('loading-screen');
    if (loadingScreen) {
        // 0.5 saniye sonra gizle
        setTimeout(() => {
            loadingScreen.classList.add('hidden');
            // Opacity geçişi tamamlandıktan sonra tamamen kaldır
            setTimeout(() => {
                loadingScreen.style.display = 'none';
                console.log('Yükleme ekranı gizlendi ve kaldırıldı'); // Hata ayıklama için
            }, 500); // CSS transition süresiyle eşleşiyor
        }, 500); // İlk gecikme
    } else {
        console.error('Yükleme ekranı bulunamadı'); // Hata ayıklama için
    }
});