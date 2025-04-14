<?php
$current_year = date('Y');
?>

<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h5>FitMate</h5>
                <p>Sağlıklı yaşam için profesyonel fitness çözümleri.</p>
            </div>
            <div class="col-md-4">
                <h5>Hızlı Bağlantılar</h5>
                <ul class="footer-links">
                    <li><a href="index.php">Ana Sayfa</a></li>
                    <li><a href="profile.php">Profilim</a></li>
                    <li><a href="calculate_bmi.php">BMI Hesapla</a></li>
                    <li><a href="view_program.php">Programım</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h5>İletişim</h5>
                <ul class="footer-contact">
                    <li><i class="fas fa-envelope"></i> info@fitmate.com</li>
                    <li><i class="fas fa-phone"></i> +90 555 123 4567</li>
                    <li><i class="fas fa-map-marker-alt"></i> Yozgat, Türkiye</li>
                </ul>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-12 text-center">
                <p class="copyright">© <?php echo $current_year; ?> FitMate. Tüm hakları saklıdır.</p>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script src="assets/js/theme.js"></script>
<script>
    AOS.init({
        duration: 1000,
        once: true
    });
</script>
</body>
</html>

<style>
.footer {
    background: var(--card-bg);
    padding: 3rem 0;
    color: var(--text-color);
    margin-top: auto;
    border-top: 1px solid var(--border-color);
}

.footer h5 {
    color: var(--text-color);
    font-weight: 600;
    margin-bottom: 1.5rem;
}

.footer p {
    color: var(--text-muted);
}

.footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-links li {
    margin-bottom: 0.5rem;
}

.footer-links a {
    color: var(--text-muted);
    text-decoration: none;
    transition: color 0.3s ease;
}

.footer-links a:hover {
    color: var(--primary-btn-bg);
}

.footer-contact {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-contact li {
    color: var(--text-muted);
    margin-bottom: 0.5rem;
}

.footer-contact li i {
    color: var(--primary-btn-bg);
    margin-right: 0.5rem;
    width: 20px;
}

.copyright {
    color: var(--text-muted);
    margin: 0;
    padding-top: 1.5rem;
}

hr {
    border-color: var(--border-color);
    margin: 2rem 0;
}

/* Dark mode uyumlu stiller */
[data-theme='dark'] .footer {
    background: var(--card-bg);
    border-top: 1px solid var(--border-color);
}

[data-theme='dark'] .footer h5 {
    color: var(--text-color);
}

[data-theme='dark'] .footer p,
[data-theme='dark'] .footer-links a,
[data-theme='dark'] .footer-contact li,
[data-theme='dark'] .copyright {
    color: var(--text-muted);
}

[data-theme='dark'] .footer-links a:hover {
    color: var(--primary-btn-bg);
}
</style> 