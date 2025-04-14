<?php
// Veritabanı bağlantısı
require_once dirname(__DIR__) . '/includes/db_connection.php';

// Admin kontrolü
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../login.php');
    exit;
}

$query = "
    SELECT 
        id,
        username,
        email,
        DATE_FORMAT(created_at, '%d.%m.%Y %H:%i') as formatted_date,
        created_at,
        is_banned,
        is_admin,
        experience_level,
        fitness_goal
    FROM users 
    WHERE is_admin = 0 
    ORDER BY created_at DESC, id DESC
";

// Sorguyu çalıştır
$users = $conn->query($query);

// Hata kontrolü
if (!$users) {
    die("Sorgu hatası: " . $conn->error);
}

// Deneyim seviyesi ve fitness hedefi için Türkçe karşılıklar
$experience_levels = [
    'beginner' => 'Başlangıç',
    'intermediate' => 'Orta Düzey',
    'advanced' => 'İleri Düzey'
];

$fitness_goals = [
    'weight_loss' => 'Kilo Verme',
    'muscle_gain' => 'Kas Kazanımı',
    'general_fitness' => 'Genel Fitness'
];
?>

<div class="container-fluid py-4">
    <!-- Toast bildirimleri için konteyner -->
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="successToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-check-circle me-2"></i>
                        <span id="successToastMessage"></span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Kapat"></button>
            </div>
        </div>
        
        <div id="errorToast" class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <span id="errorToastMessage"></span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Kapat"></button>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Kullanıcı Yönetimi</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="fas fa-user-plus"></i> Yeni Kullanıcı Ekle
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ad Soyad</th>
                    <th>E-posta</th>
                    <th>Kayıt Tarihi</th>
                    <th>Deneyim</th>
                    <th>Hedef</th>
                    <th>Durum</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $users->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo $user['formatted_date']; ?></td>
                    <td><?php echo $experience_levels[$user['experience_level']] ?? 'Başlangıç'; ?></td>
                    <td><?php echo $fitness_goals[$user['fitness_goal']] ?? 'Genel Fitness'; ?></td>
                    <td>
                        <span class="badge <?php echo $user['is_banned'] ? 'bg-danger' : 'bg-success'; ?>">
                            <?php echo $user['is_banned'] ? 'Yasaklı' : 'Aktif'; ?>
                        </span>
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="editUser(<?php echo $user['id']; ?>)" title="Düzenle">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-<?php echo $user['is_banned'] ? 'success' : 'warning'; ?>" 
                                onclick="toggleBan(<?php echo $user['id']; ?>, <?php echo $user['is_banned']; ?>)"
                                title="<?php echo $user['is_banned'] ? 'Yasağı Kaldır' : 'Yasakla'; ?>">
                            <i class="fas fa-<?php echo $user['is_banned'] ? 'unlock' : 'ban'; ?>"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteUser(<?php echo $user['id']; ?>)" title="Sil">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Kullanıcı Düzenleme Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Kullanıcı Düzenle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
            </div>
            <div class="modal-body">
                <form id="editUserForm" onsubmit="updateUser(event)">
                    <input type="hidden" id="edit_user_id" name="user_id">
                    <div class="mb-3">
                        <label for="edit_username" class="form-label">Kullanıcı Adı</label>
                        <input type="text" class="form-control" id="edit_username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">E-posta</label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_password" class="form-label">Yeni Şifre (Opsiyonel)</label>
                        <input type="password" class="form-control" id="edit_password" name="password">
                        <small class="text-muted">Şifreyi değiştirmek istemiyorsanız boş bırakın</small>
                    </div>
                    <div class="modal-footer px-0 pb-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary">Kaydet</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="<?php echo dirname(__DIR__); ?>/js/theme.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            once: true
        });
    </script>
</body>
</html>

<style>
/* Tablo Stilleri */
.table {
    background-color: var(--card-bg);
    color: var(--text-color);
    border-color: var(--border-color);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.table thead th {
    background-color: var(--card-bg);
    color: var(--text-color);
    border-bottom-color: var(--border-color);
    font-weight: 600;
    padding: 1rem;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
}

.table tbody td {
    background-color: #ffffff;
    color: #000000;
    border-color: var(--border-color);
    padding: 1rem;
    vertical-align: middle;
}

[data-theme='dark'] .table tbody td {
    background-color: #2d2d2d;
    color: #ffffff;
}

.table-hover tbody tr:hover td {
    background-color: #f8f9fa;
    color: #000000;
    transform: scale(1.01);
    transition: all 0.3s ease;
}

[data-theme='dark'] .table-hover tbody tr:hover td {
    background-color: #3d3d3d;
    color: #ffffff;
}

/* Badge Stilleri */
.badge {
    padding: 0.5em 1em;
    font-weight: 500;
    border-radius: 20px;
    font-size: 0.85rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Buton Stilleri */
.btn {
    border-radius: 50px;
    padding: 0.4rem 0.8rem;
    font-weight: 500;
    transition: all 0.2s ease;
    font-size: 0.875rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin: 0.25rem;
    min-width: 40px;
    height: 40px;
    cursor: pointer;
}

.btn i {
    font-size: 1rem;
}

.btn-primary {
    background-color: var(--primary-btn-bg);
    border: none;
    color: white;
    box-shadow: 0 2px 4px rgba(var(--primary-rgb), 0.2);
}

.btn-primary:hover {
    background-color: var(--primary-btn-hover);
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(var(--primary-rgb), 0.3);
}

.btn-outline-primary {
    border: 2px solid var(--primary-btn-bg);
    color: var(--primary-btn-bg);
    background: transparent;
}

.btn-outline-primary:hover {
    background-color: var(--primary-btn-bg);
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(var(--primary-rgb), 0.2);
}

.btn-outline-danger {
    border: 2px solid #dc3545;
    color: #dc3545;
    background: transparent;
}

.btn-outline-danger:hover {
    background-color: #dc3545;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(220, 53, 69, 0.2);
}

.btn-outline-success {
    border: 2px solid #28a745;
    color: #28a745;
    background: transparent;
}

.btn-outline-success:hover {
    background-color: #28a745;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(40, 167, 69, 0.2);
}

.btn-outline-warning {
    border: 2px solid #ffc107;
    color: #ffc107;
    background: transparent;
}

.btn-outline-warning:hover {
    background-color: #ffc107;
    color: #000;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(255, 193, 7, 0.2);
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    min-width: 32px;
    height: 32px;
}

/* Responsive Buton Düzenlemeleri */
@media (max-width: 768px) {
    .btn {
        width: 40px;
        height: 40px;
        margin: 0.25rem;
    }
    
    .btn-sm {
        width: 32px;
        height: 32px;
    }
}

/* Modal Stilleri */
.modal-content {
    background-color: var(--card-bg);
    color: var(--text-color);
    border: none;
    border-radius: 16px;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
}

.modal-header {
    border-bottom: 1px solid var(--border-color);
    padding: 1.5rem;
}

.modal-footer {
    border-top: 1px solid var(--border-color);
    padding: 1.5rem;
}

/* Form Stilleri */
.form-control {
    background-color: var(--input-bg);
    border: 2px solid var(--border-color);
    color: var(--text-color);
    border-radius: 12px;
    padding: 0.75rem 1rem;
    transition: all 0.3s ease;
}

.form-control:focus {
    background-color: var(--input-bg);
    border-color: var(--primary-btn-bg);
    color: var(--text-color);
    box-shadow: 0 0 0 4px rgba(var(--primary-rgb), 0.1);
}

.form-label {
    color: var(--text-color);
    font-weight: 500;
    margin-bottom: 0.5rem;
}

/* Responsive Düzenlemeler */
@media (max-width: 768px) {
    .table-responsive {
        margin: 0 -1rem;
        border-radius: 0;
    }
    
    .table {
        border-radius: 0;
    }
    
    .btn-group {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .btn-group .btn {
        width: 100%;
        margin: 0.25rem 0;
    }
    
    .table tbody td {
        padding: 0.75rem;
    }
    
    .table thead th {
        padding: 0.75rem;
    }
}

/* Toast Stilleri */
.toast {
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    border: none;
}

.toast-body {
    padding: 1rem;
}
</style>

<script>
// Toast gösterme fonksiyonu
function showToast(type, message) {
    const toast = document.getElementById(type === 'success' ? 'successToast' : 'errorToast');
    const messageElement = document.getElementById(type === 'success' ? 'successToastMessage' : 'errorToastMessage');
    
    // Mesajı ayarla
    messageElement.textContent = message;
    
    // Toast nesnesini oluştur
    const bsToast = new bootstrap.Toast(toast, {
        animation: true,
        autohide: true,
        delay: 3000
    });
    
    // Toast'u göster
    bsToast.show();
}

async function editUser(userId) {
    try {
        const response = await fetch(`/admin/get_user.php?id=${userId}`);
        
        if (!response.ok) {
            throw new Error(`HTTP hata! Durum: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            // Form alanlarını doldur
            document.getElementById('edit_user_id').value = userId;
            document.getElementById('edit_username').value = data.user.username;
            document.getElementById('edit_email').value = data.user.email;
            
            // Modalı göster
            const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
            modal.show();
        } else {
            throw new Error(data.message || 'Bilinmeyen bir hata oluştu');
        }
    } catch (error) {
        console.error('Hata detayı:', error);
        showToast('error', `Kullanıcı bilgileri alınırken bir hata oluştu: ${error.message}`);
    }
}

async function updateUser(event) {
    event.preventDefault();
    
    try {
        const form = document.getElementById('editUserForm');
        const formData = new FormData(form);
        
        const response = await fetch('/admin/update_user.php', {
            method: 'POST',
            body: formData
        });
        
        if (!response.ok) {
            throw new Error(`HTTP hata! Durum: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            // Modalı kapat
            const modal = bootstrap.Modal.getInstance(document.getElementById('editUserModal'));
            modal.hide();
            
            // Başarı mesajını göster
            showToast('success', 'Kullanıcı başarıyla güncellendi');
            
            // Sayfayı 3 saniye sonra yenile
            setTimeout(() => location.reload(), 3000);
        } else {
            throw new Error(data.message || 'Güncelleme sırasında bir hata oluştu');
        }
    } catch (error) {
        console.error('Güncelleme hatası:', error);
        showToast('error', `Kullanıcı güncellenirken bir hata oluştu: ${error.message}`);
    }
}

async function toggleBan(userId, currentStatus) {
    try {
        const action = currentStatus ? 'yasağını kaldırmak' : 'yasaklamak';
        
        if (!confirm(`Bu kullanıcıyı ${action} istediğinizden emin misiniz?`)) {
            return;
        }
        
        const response = await fetch('/admin/toggle_ban.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                user_id: userId,
                current_status: currentStatus
            })
        });
        
        if (!response.ok) {
            throw new Error(`HTTP hata! Durum: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            showToast('success', currentStatus ? 'Kullanıcı yasağı kaldırıldı' : 'Kullanıcı yasaklandı');
            setTimeout(() => location.reload(), 3000);
        } else {
            throw new Error(data.message || 'İşlem sırasında bir hata oluştu');
        }
    } catch (error) {
        console.error('Yasaklama hatası:', error);
        showToast('error', `İşlem sırasında bir hata oluştu: ${error.message}`);
    }
}

async function deleteUser(userId) {
    try {
        if (!confirm('Bu kullanıcıyı silmek istediğinizden emin misiniz? Bu işlem geri alınamaz!')) {
            return;
        }
        
        const response = await fetch('/admin/delete_user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                user_id: userId
            })
        });
        
        if (!response.ok) {
            throw new Error(`HTTP hata! Durum: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            showToast('success', 'Kullanıcı başarıyla silindi');
            setTimeout(() => location.reload(), 3000);
        } else {
            throw new Error(data.message || 'Silme işlemi sırasında bir hata oluştu');
        }
    } catch (error) {
        console.error('Silme hatası:', error);
        showToast('error', `Silme işlemi sırasında bir hata oluştu: ${error.message}`);
    }
}
</script>