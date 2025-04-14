<?php
session_start();
require_once 'config.php';

// Kullanıcı girişi kontrolü
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Geri bildirim gönderme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message = $_POST['message'];
    $user_id = $_SESSION['user_id'];
    
    $stmt = $conn->prepare("INSERT INTO feedback (user_id, message) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $message);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Geri bildiriminiz başarıyla gönderildi.";
    } else {
        $_SESSION['error_message'] = "Geri bildirim gönderilirken bir hata oluştu.";
    }
    
    header("Location: feedback.php");
    exit();
}

// Geri bildirim silme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_feedback'])) {
    $feedback_id = $_POST['feedback_id'];
    $user_id = $_SESSION['user_id'];
    
    // Sadece kendi geri bildirimlerini silebilir
    $stmt = $conn->prepare("DELETE FROM feedback WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $feedback_id, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Geri bildiriminiz başarıyla silindi.";
    } else {
        $_SESSION['error_message'] = "Geri bildirim silinirken bir hata oluştu.";
    }
    
    header("Location: feedback.php");
    exit();
}

// Kullanıcının geri bildirimlerini getir
$user_id = $_SESSION['user_id'];
$query = "
    SELECT f.*, a.username as admin_username
    FROM feedback f 
    LEFT JOIN users a ON f.replied_by = a.id
    WHERE f.user_id = ?
    ORDER BY f.created_at DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="tr" data-theme="<?php echo isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Geri Bildirim - FitMate</title>
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/navbar.css">
    <style>
        .feedback-container {
            padding: 2rem 0;
            margin-top: 76px;
            min-height: calc(100vh - 76px);
            background: linear-gradient(135deg, var(--bg-color) 0%, var(--card-bg) 100%);
        }

        .feedback-card {
            background: var(--card-bg);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px var(--shadow-color);
            border: 2px solid #000;
            transition: all 0.3s ease;
        }

        [data-theme='dark'] .feedback-card {
            border-color: #fff;
        }

        .feedback-card:hover {
            border-color: var(--primary-btn-bg);
            box-shadow: 0 0 15px rgba(var(--primary-btn-bg-rgb), 0.3);
            transform: translateY(-5px);
        }

        .feedback-card.aos-init.aos-animate {
            animation: cardBorderAnimation 2s infinite;
        }

        @keyframes cardBorderAnimation {
            0% {
                border-color: #000;
                box-shadow: 0 0 0 rgba(var(--primary-btn-bg-rgb), 0);
            }
            50% {
                border-color: var(--primary-btn-bg);
                box-shadow: 0 0 15px rgba(var(--primary-btn-bg-rgb), 0.3);
            }
            100% {
                border-color: #000;
                box-shadow: 0 0 0 rgba(var(--primary-btn-bg-rgb), 0);
            }
        }

        [data-theme='dark'] .feedback-card.aos-init.aos-animate {
            animation: cardBorderAnimationDark 2s infinite;
        }

        @keyframes cardBorderAnimationDark {
            0% {
                border-color: #fff;
                box-shadow: 0 0 0 rgba(var(--primary-btn-bg-rgb), 0);
            }
            50% {
                border-color: var(--primary-btn-bg);
                box-shadow: 0 0 15px rgba(var(--primary-btn-bg-rgb), 0.3);
            }
            100% {
                border-color: #fff;
                box-shadow: 0 0 0 rgba(var(--primary-btn-bg-rgb), 0);
            }
        }

        .feedback-form textarea {
            min-height: 150px;
            resize: vertical;
        }

        .feedback-item {
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .feedback-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
            margin-bottom: 0;
        }

        .admin-reply {
            background: var(--bg-color);
            border-radius: 10px;
            padding: 1rem;
            margin-top: 1rem;
        }

        .text-muted {
            color: #6c757d !important;
        }

        [data-theme='dark'] .text-muted {
            color: #fff !important;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="feedback-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="feedback-card" data-aos="fade-up">
                        <h2 class="text-center mb-4">Geri Bildirim Gönder</h2>
                        
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
                        
                        <form method="POST" class="feedback-form">
                            <div class="mb-3">
                                <label for="message" class="form-label">Mesajınız</label>
                                <textarea class="form-control" id="message" name="message" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Gönder</button>
                        </form>
                    </div>

                    <div class="feedback-card" data-aos="fade-up" data-aos-delay="100">
                        <h2 class="text-center mb-4">Geri Bildirimlerim</h2>
                        
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($feedback = $result->fetch_assoc()): ?>
                                <div class="feedback-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <p class="mb-1"><?php echo nl2br(htmlspecialchars($feedback['message'])); ?></p>
                                            <small class="text-muted"><?php echo date('d.m.Y H:i', strtotime($feedback['created_at'])); ?></small>
                                        </div>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('Bu geri bildirimi silmek istediğinize emin misiniz?');">
                                            <input type="hidden" name="feedback_id" value="<?php echo $feedback['id']; ?>">
                                            <button type="submit" name="delete_feedback" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                    
                                    <?php if ($feedback['admin_reply']): ?>
                                        <div class="admin-reply">
                                            <h6 class="mb-2">Admin Yanıtı (<?php echo $feedback['admin_username']; ?>):</h6>
                                            <p class="mb-1"><?php echo nl2br(htmlspecialchars($feedback['admin_reply'])); ?></p>
                                            <small class="text-muted"><?php echo date('d.m.Y H:i', strtotime($feedback['replied_at'])); ?></small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-center text-muted">Henüz geri bildirim bulunmuyor.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="js/theme.js"></script>
    <script>
        AOS.init({
            duration: 1000,
            once: true
        });
    </script>
</body>
</html> 