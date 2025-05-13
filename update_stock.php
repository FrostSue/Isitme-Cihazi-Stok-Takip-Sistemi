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
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$add_stock = isset($_POST['add_stock']) ? (int)$_POST['add_stock'] : 0;
$csrf_token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';

// Validate CSRF token
if (empty($csrf_token) || !isset($_SESSION['csrf_token']) || $csrf_token !== $_SESSION['csrf_token']) {
    echo json_encode([
        'success' => false,
        'message' => 'Geçersiz güvenlik tokeni'
    ]);
    exit;
}

// Validate input
if ($product_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Geçersiz ürün ID'
    ]);
    exit;
}

if ($add_stock <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Eklenecek stok miktarı sıfırdan büyük olmalıdır'
    ]);
    exit;
}

try {
    // Get connection to the product database
    $pdo = getProductConnection();
    
    // Check if product exists and get current stock
    $stmt = $pdo->prepare("SELECT stock, name FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        echo json_encode([
            'success' => false,
            'message' => 'Ürün bulunamadı'
        ]);
        exit;
    }
    
    // Calculate new stock
    $new_stock = $product['stock'] + $add_stock;
    
    // Update product stock
    $stmt = $pdo->prepare("UPDATE products SET stock = ? WHERE id = ?");
    $stmt->execute([$new_stock, $product_id]);
    
    // Return success
    echo json_encode([
        'success' => true,
        'message' => $product['name'] . ' için ' . $add_stock . ' adet stok eklendi',
        'new_stock' => $new_stock,
        'product_id' => $product_id
    ]);
    
} catch (PDOException $e) {
    // Return error message
    echo json_encode([
        'success' => false,
        'message' => 'Stok güncellenirken hata oluştu'
    ]);
    
    // Log the error
    error_log('Error in update_stock.php: ' . $e->getMessage());
}
?> 