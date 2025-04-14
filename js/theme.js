// Tema değiştirme fonksiyonu
function toggleTheme() {
    const body = document.body;
    const currentTheme = body.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    body.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    
    // Navbar'ı güncelle
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        navbar.classList.remove('navbar-light', 'navbar-dark');
        navbar.classList.add(newTheme === 'dark' ? 'navbar-dark' : 'navbar-light');
    }
    
    // Tema butonunun ikonunu güncelle
    const themeIcon = document.querySelector('.theme-toggle i');
    if (themeIcon) {
        themeIcon.className = newTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    }

    // Charts.js yazı rengi ayarı
    if (newTheme === 'light') {
        Chart.defaults.color = '#000';
    } else {
        Chart.defaults.color = '#fff';
    }
}

// Sayfa yüklendiğinde tema tercihini kontrol et
document.addEventListener('DOMContentLoaded', () => {
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.body.setAttribute('data-theme', savedTheme);
    
    // Navbar'ı ayarla
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        navbar.classList.remove('navbar-light', 'navbar-dark');
        navbar.classList.add(savedTheme === 'dark' ? 'navbar-dark' : 'navbar-light');
    }
    
    // Tema butonunun ikonunu ayarla
    const themeIcon = document.querySelector('.theme-toggle i');
    if (themeIcon) {
        themeIcon.className = savedTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    }
    
    // Tema değiştirme butonuna tıklama olayı ekle
    const themeToggle = document.querySelector('.theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', toggleTheme);
    }

    // Ekran boyutu değiştiğinde butonun konumunu güncelle
    function updateButtonPosition() {
        const themeToggle = document.querySelector('.theme-toggle');
        const mobileContainer = document.querySelector('.navbar-toggler').parentElement;
        const desktopContainer = document.querySelector('.navbar-nav.ms-auto li:first-child');
        
        if (window.innerWidth < 992) {
            // Mobil görünüm
            if (themeToggle.parentElement !== mobileContainer) {
                mobileContainer.insertBefore(themeToggle, mobileContainer.firstChild);
            }
        } else {
            // Masaüstü görünüm
            if (themeToggle.parentElement !== desktopContainer) {
                const li = document.createElement('li');
                li.className = 'nav-item';
                li.appendChild(themeToggle);
                desktopContainer.parentElement.insertBefore(li, desktopContainer);
            }
        }
    }

    // Sayfa yüklendiğinde ve ekran boyutu değiştiğinde butonun konumunu güncelle
    updateButtonPosition();
    window.addEventListener('resize', updateButtonPosition);
});