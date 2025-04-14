<?php
if (!isAdmin()) {
    header('Location: ../index.php');
    exit();
}
?>
<div class="sidebar">
    <div class="sidebar-header">
        <h3>Admin Panel</h3>
    </div>
</div>

<style>
.sidebar {
    width: 250px;
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    background-color: var(--dark-color);
    color: var(--light-color);
    padding: 20px 0;
    z-index: 1000;
}

.sidebar-header {
    padding: 0 20px;
    margin-bottom: 20px;
}

.sidebar-header h3 {
    margin: 0;
    color: var(--light-color);
}

.main-content {
    margin-left: 250px;
    padding: 20px;
}

@media (max-width: 768px) {
    .sidebar {
        width: 100%;
        height: auto;
        position: relative;
    }
    
    .main-content {
        margin-left: 0;
    }
}
</style> 