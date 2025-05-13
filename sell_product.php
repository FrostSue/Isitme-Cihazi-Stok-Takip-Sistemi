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

// Optional: Get quantity to decrement (defaults to 1)
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

// Validate input
if ($product_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Geçersiz ürün ID'
    ]);
    exit;
}

if ($quantity <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Miktar sıfırdan büyük olmalıdır'
    ]);
    exit;
}

try {
    // Get connection to the product database
    $pdo = getProductConnection();
    $pdo->beginTransaction();
    
    // Check current stock level
    $stmt = $pdo->prepare("SELECT stock, name FROM products WHERE id = ? FOR UPDATE");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'message' => 'Ürün bulunamadı'
        ]);
        exit;
    }
    
    // Check if enough stock
    if ($product['stock'] < $quantity) {
        $pdo->rollBack();
        echo json_encode([
            'success' => false,
            'message' => 'Yeterli stok yok. Sadece ' . $product['stock'] . ' adet mevcut.'
        ]);
        exit;
    }
    
    // Update stock
    $new_stock = $product['stock'] - $quantity;
    $stmt = $pdo->prepare("UPDATE products SET stock = ? WHERE id = ?");
    $stmt->execute([$new_stock, $product_id]);
    
    $pdo->commit();
    
    // Return success with updated stock information
    echo json_encode([
        'success' => true,
        'message' => $quantity . ' adet ' . $product['name'] . ' başarıyla satıldı',
        'new_stock' => $new_stock,
        'product_id' => $product_id,
        'low_stock' => $new_stock <= LOW_STOCK_THRESHOLD
    ]);
    
} catch (PDOException $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Return error message
    echo json_encode([
        'success' => false,
        'message' => 'Stok güncellenirken hata oluştu'
    ]);
    
    // Log the error (don't expose in production)
    error_log('Error in sell_product.php: ' . $e->getMessage());
} 