<?php
if (isset($_SESSION['user_id'])) {
    // Kullanıcının yanıtlanmış feedback'lerini getir
    $query = "
        SELECT f.*, u.username as admin_name 
        FROM feedback f 
        LEFT JOIN users u ON f.replied_by = u.id 
        WHERE f.user_id = ? AND f.admin_reply IS NOT NULL AND f.admin_reply != ''
        ORDER BY f.replied_at DESC
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $unread_messages = $result->num_rows;
}
?>

<div class="message-box" id="messageBox">
    <div class="message-box-header">
        <h6>Mesajlarım</h6>
        <button class="btn-close" id="closeMessageBox"></button>
    </div>
    <div class="message-box-body">
        <?php if (isset($unread_messages) && $unread_messages > 0): ?>
            <?php while ($message = $result->fetch_assoc()): ?>
                <div class="message-item">
                    <div class="message-content">
                        <p class="mb-1"><?php echo nl2br(htmlspecialchars($message['admin_reply'])); ?></p>
                        <small class="text-muted">
                            <?php echo date('d.m.Y H:i', strtotime($message['replied_at'])); ?>
                        </small>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="text-center py-3">
                <p class="mb-0">Henüz mesajınız bulunmuyor.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.message-box {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 300px;
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    display: none;
}

.message-box.show {
    display: block;
    animation: slideIn 0.3s ease-out;
}

.message-box-header {
    padding: 10px 15px;
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.message-box-body {
    max-height: 300px;
    overflow-y: auto;
    padding: 15px;
}

.message-item {
    margin-bottom: 15px;
    padding: 10px;
    background: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 8px;
}

.message-content {
    color: var(--text-color);
}

@keyframes slideIn {
    from {
        transform: translateY(100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.message-icon {
    position: fixed;
    bottom: 20px;
    right: 20px;
    width: 50px;
    height: 50px;
    background: var(--primary-btn-bg);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    z-index: 999;
}

.message-icon i {
    color: white;
    font-size: 1.2rem;
}

.message-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: red;
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
}
</style>

<div class="message-icon" id="messageIcon">
    <i class="fas fa-envelope"></i>
    <?php if (isset($unread_messages) && $unread_messages > 0): ?>
        <span class="message-badge"><?php echo $unread_messages; ?></span>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const messageBox = document.getElementById('messageBox');
    const messageIcon = document.getElementById('messageIcon');
    const closeButton = document.getElementById('closeMessageBox');

    messageIcon.addEventListener('click', function() {
        messageBox.classList.toggle('show');
    });

    closeButton.addEventListener('click', function() {
        messageBox.classList.remove('show');
    });

    // Mesaj kutusu dışına tıklandığında kapat
    document.addEventListener('click', function(event) {
        if (!messageBox.contains(event.target) && !messageIcon.contains(event.target)) {
            messageBox.classList.remove('show');
        }
    });
});
</script> 