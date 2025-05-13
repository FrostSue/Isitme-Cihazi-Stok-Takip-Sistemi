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

// Get product ID from request
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

// Validate input
if ($product_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Geçersiz ürün ID'
    ]);
    exit;
}

// Get CSRF token
$csrf_token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';

// Validate CSRF token
if (empty($csrf_token) || !isset($_SESSION['csrf_token']) || $csrf_token !== $_SESSION['csrf_token']) {
    echo json_encode([
        'success' => false,
        'message' => 'Geçersiz güvenlik tokeni'
    ]);
    exit;
}

try {
    // Get connection to the product database
    $pdo = getProductConnection();
    
    // Check if product exists
    $stmt = $pdo->prepare("SELECT id, name, thumbnail FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        echo json_encode([
            'success' => false,
            'message' => 'Ürün bulunamadı'
        ]);
        exit;
    }
    
    // Delete the product
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    
    // If the product had a local image, delete it
    if (!empty($product['thumbnail']) && strpos($product['thumbnail'], 'http') !== 0 && file_exists($product['thumbnail'])) {
        unlink($product['thumbnail']);
    }
    
    // Return success
    echo json_encode([
        'success' => true,
        'message' => 'Ürün başarıyla silindi',
        'product_id' => $product_id
    ]);
    
} catch (PDOException $e) {
    // Return error message
    echo json_encode([
        'success' => false,
        'message' => 'Ürün silinirken hata oluştu'
    ]);
    
    // Log the error (don't expose in production)
    error_log('Error in delete_product.php: ' . $e->getMessage());
} 