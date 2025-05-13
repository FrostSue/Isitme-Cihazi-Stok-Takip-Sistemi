<?php
// Include session check to prevent unauthorized access
require_once 'check_session.php';
require_once 'db_connection.php';

// Set response header to JSON
header('Content-Type: application/json');

try {
    // Get connection to the product database
    $pdo = getProductConnection();
    
    // Prepare and execute the query to get all categories
    $stmt = $pdo->prepare("SELECT id, name FROM categories ORDER BY name ASC");
    $stmt->execute();
    
    // Fetch all categories
    $categories = $stmt->fetchAll();
    
    // Return JSON response
    echo json_encode([
        'success' => true,
        'categories' => $categories
    ]);
    
} catch (PDOException $e) {
    // Return error message
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching categories'
    ]);
    
    // Log the error (don't expose in production)
    error_log('Error in get_categories.php: ' . $e->getMessage());
} 