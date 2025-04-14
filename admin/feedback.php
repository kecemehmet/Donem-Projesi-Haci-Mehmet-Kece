<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';

// Admin kontrolü
if (!isAdmin()) {
    header('Location: ../index.php');
    exit();
}

// Feedback silme
if (isset($_POST['delete_feedback'])) {
    $feedback_id = $_POST['feedback_id'];
    
    $stmt = $conn->prepare("DELETE FROM feedback WHERE id = ?");
    $stmt->bind_param("i", $feedback_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Geri bildirim başarıyla silindi.";
    } else {
        $_SESSION['error_message'] = "Geri bildirim silinirken bir hata oluştu.";
    }
    
    echo "<script>window.location.href = '../admin.php?tab=feedback';</script>";
    exit();
}

// Admin yanıtını silme
if (isset($_POST['delete_reply'])) {
    $feedback_id = $_POST['feedback_id'];
    
    $stmt = $conn->prepare("UPDATE feedback SET admin_reply = NULL, replied_at = NULL, replied_by = NULL WHERE id = ?");
    $stmt->bind_param("i", $feedback_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Yanıt başarıyla silindi.";
    } else {
        $_SESSION['error_message'] = "Yanıt silinirken bir hata oluştu.";
    }
    
    echo "<script>window.location.href = '../admin.php?tab=feedback';</script>";
    exit();
}

// Feedback yanıtı gönderme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply'])) {
    $feedback_id = $_POST['feedback_id'];
    $reply = $_POST['reply'];
    
    $stmt = $conn->prepare("UPDATE feedback SET admin_reply = ?, replied_at = NOW(), replied_by = ? WHERE id = ?");
    $stmt->bind_param("sii", $reply, $_SESSION['user_id'], $feedback_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Yanıt başarıyla gönderildi.";
    } else {
        $_SESSION['error_message'] = "Yanıt gönderilirken bir hata oluştu.";
    }
    
    echo "<script>window.location.href = '../admin.php?tab=feedback';</script>";
    exit();
}

// Feedback'leri getir
$query = "
    SELECT f.*, u.username, a.username as admin_username
    FROM feedback f 
    JOIN users u ON f.user_id = u.id 
    LEFT JOIN users a ON f.replied_by = a.id
    ORDER BY f.created_at DESC
";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Geri Bildirimler - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .admin-reply {
            background: var(--bg-color);
            border-radius: 10px;
            padding: 1rem;
            margin-top: 1rem;
            color: var(--text-color);
        }

        .admin-reply h6 {
            color: var(--text-color);
            font-weight: 600;
        }

        .admin-reply small {
            color: var(--text-color);
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['success_message'];
                unset($_SESSION['success_message']);
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['error_message'];
                unset($_SESSION['error_message']);
                ?>
            </div>
        <?php endif; ?>
        
        <div class="feedback-list">
            <?php while ($feedback = $result->fetch_assoc()): ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-user-circle fa-2x"></i>
                                </div>
                                <div>
                                    <h5 class="mb-0"><?php echo $feedback['username']; ?></h5>
                                    <small class="text-muted"><?php echo date('d.m.Y H:i', strtotime($feedback['created_at'])); ?></small>
                                </div>
                            </div>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Bu geri bildirimi silmek istediğinize emin misiniz?');">
                                <input type="hidden" name="feedback_id" value="<?php echo $feedback['id']; ?>">
                                <button type="submit" name="delete_feedback" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                        <p class="mb-3"><?php echo nl2br(htmlspecialchars($feedback['message'])); ?></p>
                        
                        <?php if ($feedback['admin_reply']): ?>
                            <div class="admin-reply p-3 rounded" style="background: var(--card-bg); color: var(--text-color);">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-2" style="color: var(--text-color);">Admin Yanıtı (<?php echo $feedback['admin_username']; ?>):</h6>
                                        <p class="mb-0" style="color: var(--text-color);"><?php echo nl2br(htmlspecialchars($feedback['admin_reply'])); ?></p>
                                        <small style="color: var(--text-color); opacity: 0.8;"><?php echo date('d.m.Y H:i', strtotime($feedback['replied_at'])); ?></small>
                                    </div>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Admin yanıtını silmek istediğinize emin misiniz?');">
                                        <input type="hidden" name="feedback_id" value="<?php echo $feedback['id']; ?>">
                                        <button type="submit" name="delete_reply" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php else: ?>
                            <form method="POST" class="mt-3">
                                <input type="hidden" name="feedback_id" value="<?php echo $feedback['id']; ?>">
                                <div class="mb-3">
                                    <label for="reply" class="form-label">Yanıt Yaz</label>
                                    <textarea class="form-control" id="reply" name="reply" rows="3" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Yanıt Gönder</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
</body>
</html> 