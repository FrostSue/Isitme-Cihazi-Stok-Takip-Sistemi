<?php
// This script updates the sessions table to fix session expiration issues

// Database connection constants from db_connection.php
define('DB_HOST', 'localhost');
define('DB_ADMIN_NAME', 'hearing_aid_admin');
define('DB_USER', 'root');
define('DB_PASS', '');

// Function to create database connection
function getConnection() {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_ADMIN_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    try {
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        throw new PDOException($e->getMessage(), (int)$e->getCode());
    }
}

// Start HTML output
echo '<!DOCTYPE html>
<html>
<head>
    <title>Update Sessions Table</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.6;
        }
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .info {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <h1>Sessions Table Update</h1>';

try {
    $pdo = getConnection();
    
    // 1. Check if sessions table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'sessions'");
    $sessionsTableExists = $stmt->rowCount() > 0;
    
    if ($sessionsTableExists) {
        echo '<div class="message info">Sessions table found. Preparing to update...</div>';
        
        // Backup existing sessions if any
        $stmt = $pdo->query("SELECT * FROM sessions");
        $existingSessions = $stmt->fetchAll();
        
        // Drop the current sessions table
        $pdo->exec("DROP TABLE sessions");
        echo '<div class="message success">Old sessions table dropped successfully.</div>';
    } else {
        echo '<div class="message info">No existing sessions table found. Will create a new one.</div>';
    }
    
    // Create new sessions table
    $pdo->exec("CREATE TABLE sessions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        admin_id INT NOT NULL,
        session_id VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE,
        INDEX (session_id)
    )");
    
    echo '<div class="message success">New sessions table created successfully.</div>';
    
    // Restore sessions if we had any
    if (isset($existingSessions) && !empty($existingSessions)) {
        $insertStmt = $pdo->prepare("INSERT INTO sessions (admin_id, session_id) VALUES (?, ?)");
        $restoredCount = 0;
        
        foreach ($existingSessions as $session) {
            try {
                $insertStmt->execute([$session['admin_id'], $session['session_id']]);
                $restoredCount++;
            } catch (PDOException $ex) {
                // Ignore individual session restore errors
            }
        }
        
        echo '<div class="message info">Restored ' . $restoredCount . ' session(s) from ' . count($existingSessions) . ' total.</div>';
    }
    
    echo '<div class="message success">Session table update completed successfully!</div>';
    
} catch (PDOException $e) {
    echo '<div class="message error">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
}

// Add link to login page
echo '<a href="login.php">Go to Login Page</a>
    <script src="animations.js"></script>
    <script src="header_animation.js"></script>
</body>
</html>'; 