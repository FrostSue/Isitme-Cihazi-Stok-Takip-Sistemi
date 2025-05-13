<?php
session_start();

// Required constants and functions
require_once 'db_connection.php';

// Define session timeout (30 minutes)
define('SESSION_TIMEOUT', 30 * 60);

// Check if the user is logged in
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['username'])) {
    // If no session exists, redirect to login
    header('Location: login.php');
    exit;
}

// Check if session has expired based on last activity
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
    // Session has expired
    session_unset();
    session_destroy();
    header('Location: login.php?error=expired');
    exit;
}

// Update last activity time
$_SESSION['last_activity'] = time();

// Also validate session from database for extra security (but don't make it critical)
try {
    $pdo = getAdminConnection();
    
    $stmt = $pdo->prepare("SELECT s.id FROM sessions s 
                           JOIN admins a ON s.admin_id = a.id 
                           WHERE s.admin_id = ? 
                           AND s.session_id = ?");
    
    $stmt->execute([$_SESSION['admin_id'], session_id()]);
    
    if ($stmt->rowCount() === 0) {
        // Session not found in database, recreate it
        $cleanupStmt = $pdo->prepare("DELETE FROM sessions WHERE admin_id = ?");
        $cleanupStmt->execute([$_SESSION['admin_id']]);
        
        $insertStmt = $pdo->prepare("INSERT INTO sessions (admin_id, session_id) VALUES (?, ?)");
        $insertStmt->execute([$_SESSION['admin_id'], session_id()]);
    }
    
} catch (PDOException $e) {
    // Just log the error, don't invalidate the session
    error_log('Session validation error: ' . $e->getMessage());
} 