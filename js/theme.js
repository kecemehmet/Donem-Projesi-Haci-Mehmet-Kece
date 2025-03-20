// Tema durumunu kontrol et ve uygula
function setTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
    updateThemeIcon(theme); // Tema simgesini güncelle
}

// Tema ikonunu güncelle
function updateThemeIcon(theme) {
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.innerHTML = theme === 'dark' ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
    }
}

// Sayfa yüklendiğinde temayı kontrol et
document.addEventListener('DOMContentLoaded', () => {
    const savedTheme = localStorage.getItem('theme') || 'light';
    setTheme(savedTheme);

    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            setTheme(newTheme);
        });
    } else {
        console.error('Tema butonu bulunamadı! Lütfen HTML’de id="theme-toggle" olduğundan emin olun.');
    }
});