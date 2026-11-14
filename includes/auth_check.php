<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// For module-specific pages
if (isset($required_role)) {
    if ($_SESSION['role'] !== $required_role) {
        header('Location: ../unauthorized.php');
        exit();
    }
}

// For module access checking
if (isset($required_module)) {
    if ($_SESSION['role'] !== 'admin') {
        require_once '../includes/db_config.php';
        $stmt = $conn->prepare("SELECT 1 FROM user_privileges WHERE user_id = ? AND module = ?");
        $stmt->bind_param("is", $_SESSION['user_id'], $required_module);
        $stmt->execute();
        $has_access = $stmt->get_result()->num_rows > 0;
        
        if (!$has_access) {
            header('Location: ../unauthorized.php');
            exit();
        }
    }
}
?>