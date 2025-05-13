<?php
// Include session check to prevent unauthorized access
require_once 'check_session.php';
require_once 'db_connection.php';

// Set response header to JSON
header('Content-Type: application/json');

// Get category_id from request
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

try {
    // Get connection to the product database
    $pdo = getProductConnection();
    
    if ($category_id > 0) {
        // Get only brands that have products in the selected category
        $stmt = $pdo->prepare("
            SELECT DISTINCT b.id, b.name 
            FROM brands b
            JOIN products p ON b.id = p.brand_id
            WHERE p.category_id = ?
            ORDER BY b.name ASC
        ");
        $stmt->execute([$category_id]);
    } else {
        // If no category specified, get all brands
        $stmt = $pdo->query("SELECT id, name FROM brands ORDER BY name ASC");
    }
    
    // Fetch all brands
    $brands = $stmt->fetchAll();
    
    // Return JSON response
    echo json_encode([
        'success' => true,
        'brands' => $brands
    ]);
    
} catch (PDOException $e) {
    // Return error message
    echo json_encode([
        'success' => false,
        'message' => 'Markalar yüklenirken hata oluştu: ' . $e->getMessage()
    ]);
    
    // Log the error
    error_log('Error in get_brands.php: ' . $e->getMessage());
}