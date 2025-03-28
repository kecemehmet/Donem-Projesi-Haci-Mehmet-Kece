// AOS Animasyonlarını Başlat
console.log("AOS başlatılıyor...");
AOS.init({
    once: false,
    offset: 50,
    duration: 1000
});

// Sayfanın yüklenmesi tamamlandığında AOS'u yenile
window.addEventListener('load', function() {
    console.log("Sayfa yüklendi, AOS yenileniyor...");
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

// Alert zamanlayıcı
setTimeout(() => {
    const alert = document.querySelector('.alert');
    if (alert) {
        console.log("Alert 5 saniye sonra kapanıyor...");
        alert.classList.remove('show');
    }
}, 5000);

// Yükleme ekranı kontrolü
window.addEventListener('load', function() {
    const loadingScreen = document.getElementById('loading-screen');
    if (loadingScreen) {
        console.log("Yükleme ekranı bulundu, gizleniyor...");
        setTimeout(() => {
            loadingScreen.classList.add('hidden');
            setTimeout(() => {
                loadingScreen.style.display = 'none';
                console.log('Yükleme ekranı gizlendi ve kaldırıldı');
            }, 500);
        }, 500);
    } else {
        console.error('Yükleme ekranı bulunamadı');
    }
});
