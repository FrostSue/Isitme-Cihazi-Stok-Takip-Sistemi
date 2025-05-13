<?php
// Include session check to prevent unauthorized access
require_once 'check_session.php';
require_once 'db_connection.php';

// Set response header to JSON
header('Content-Type: application/json');

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'İzin verilmeyen metod. POST kullanın.'
    ]);
    exit;
}

// Get data from request
$brand_name = isset($_POST['brand_name']) ? trim($_POST['brand_name']) : '';
$category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;

// Validate input
if (empty($brand_name)) {
    echo json_encode([
        'success' => false,
        'message' => 'Marka adı boş olamaz.'
    ]);
    exit;
}

if ($category_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Geçersiz kategori ID.'
    ]);
    exit;
}

try {
    // Get connection to the product database
    $pdo = getProductConnection();
    
    // Check if brand already exists (by name only)
    $stmt = $pdo->prepare("SELECT id FROM brands WHERE name = ?");
    $stmt->execute([$brand_name]);
    $existing_brand = $stmt->fetch();
    
    if ($existing_brand) {
        // Brand already exists, return existing ID
        echo json_encode([
            'success' => true,
            'message' => 'Marka zaten mevcut.',
            'brand_id' => $existing_brand['id'],
            'brand_name' => $brand_name
        ]);
        exit;
    }
    
    // Insert new brand (only name, no category_id)
    $stmt = $pdo->prepare("INSERT INTO brands (name) VALUES (?)");
    $stmt->execute([$brand_name]);
    
    // Get the new brand ID
    $brand_id = $pdo->lastInsertId();
    
    // Return success with new brand ID
    echo json_encode([
        'success' => true,
        'message' => 'Marka başarıyla eklendi.',
        'brand_id' => $brand_id,
        'brand_name' => $brand_name
    ]);
    
} catch (PDOException $e) {
    // Return error message
    echo json_encode([
        'success' => false,
        'message' => 'Marka eklenirken hata oluştu: ' . $e->getMessage()
    ]);
    
    // Log the error
    error_log('Error in add_brand.php: ' . $e->getMessage());
}
?> 