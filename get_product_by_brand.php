<?php
// Include session check to prevent unauthorized access
require_once 'check_session.php';
require_once 'db_connection.php';

// Set response header to JSON
header('Content-Type: application/json');

// Get parameters from request
$brand_id = isset($_GET['brand_id']) ? (int)$_GET['brand_id'] : 0;
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

// Validate brand_id
if ($brand_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid brand ID'
    ]);
    exit;
}

try {
    // Get connection to the product database
    $pdo = getProductConnection();
    
    // Build the query
    $query = "SELECT id, name, stock, price, thumbnail FROM products WHERE brand_id = ?";
    $params = [$brand_id];
    
    // Add category filter if provided
    if ($category_id > 0) {
        $query .= " AND category_id = ?";
        $params[] = $category_id;
    }
    
    // Add order by
    $query .= " ORDER BY name ASC";
    
    // Prepare and execute the query
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    
    // Fetch all products for the brand
    $products = $stmt->fetchAll();
    
    // Return JSON response
    echo json_encode([
        'success' => true,
        'products' => $products
    ]);
    
} catch (PDOException $e) {
    // Return error message
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching products: ' . $e->getMessage()
    ]);
    
    // Log the error (don't expose in production)
    error_log('Error in get_product_by_brand.php: ' . $e->getMessage());
} 