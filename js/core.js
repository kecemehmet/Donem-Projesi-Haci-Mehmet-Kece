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
    
    // Sayfa yüklendiğinde mevcut temayı uygula
    applyTheme();
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
        loadingScreen.style.display = 'none';
    }
});

// Sayfa yüklendiğinde mevcut temayı uygula
function applyTheme() {
    // localStorage'dan tema bilgisini kontrol et
    const savedTheme = localStorage.getItem('theme');
    const isDarkMode = localStorage.getItem('darkMode') === 'true';
    
    // Eski sistemden veya yeni sistemden tema bilgisini kullan
    if (savedTheme) {
        document.documentElement.setAttribute('data-theme', savedTheme);
        
        // İkonu güncelle
        const icons = document.querySelectorAll('.theme-toggle i');
        icons.forEach(icon => {
            icon.className = savedTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
        });
    } 
    else if (isDarkMode !== null) {
        document.documentElement.setAttribute('data-theme', isDarkMode ? 'dark' : 'light');
        
        // Siteyi dark-mode sınıfıyla güncelle (admin panel için)
        if (isDarkMode) {
            document.body.classList.add('dark-mode');
        } else {
            document.body.classList.remove('dark-mode');
        }
        
        // İkonu güncelle
        const icons = document.querySelectorAll('.theme-toggle i');
        icons.forEach(icon => {
            icon.className = isDarkMode ? 'fas fa-sun' : 'fas fa-moon';
        });
    }
}

// Tema değiştirme fonksiyonu
function setTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
    
    // Yeni darkMode sistemini güncelle
    localStorage.setItem('darkMode', theme === 'dark');
    
    // Siteyi dark-mode sınıfıyla güncelle (admin panel için)
    if (theme === 'dark') {
        document.body.classList.add('dark-mode');
    } else {
        document.body.classList.remove('dark-mode');
    }
    
    const icons = document.querySelectorAll('.theme-toggle i');
    icons.forEach(icon => {
        icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    });
}

// Sayfa yüklendiğinde
document.addEventListener('DOMContentLoaded', () => {
    // Kaydedilmiş temayı uygula
    applyTheme();

    // Tema değiştirme butonlarını dinle
    const themeToggles = document.querySelectorAll('.theme-toggle');
    themeToggles.forEach(toggle => {
        toggle.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            setTheme(newTheme);
        });
    });

    // Loading screen'i gizle
    const loadingScreen = document.getElementById('loading-screen');
    if (loadingScreen) {
        loadingScreen.style.display = 'none';
    }
});
