<?php
session_start();
require_once 'db_connection.php';

// Delete the session from database if possible
try {
    if (isset($_SESSION['admin_id'])) {
        $pdo = getAdminConnection();
        
        // Delete by session ID (more specific)
        $stmt = $pdo->prepare("DELETE FROM sessions WHERE session_id = ?");
        $stmt->execute([session_id()]);
        
        // Also clean up expired and orphaned sessions
        $cleanupStmt = $pdo->prepare("DELETE FROM sessions WHERE admin_id = ? AND session_id != ?");
        $cleanupStmt->execute([$_SESSION['admin_id'], session_id()]);
    }
} catch (PDOException $e) {
    // Just log the error, but continue with logout
    error_log('Logout error: ' . $e->getMessage());
}

// Unset all session variables
$_SESSION = [];

// If a session cookie is used, destroy it
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to login page
header('Location: login.php');
exit; 