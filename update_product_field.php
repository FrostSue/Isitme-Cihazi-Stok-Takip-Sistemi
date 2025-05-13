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

// Get product ID and field to update
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$csrf_token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';

// Validate input
if ($product_id <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Geçersiz ürün ID'
    ]);
    exit;
}

// Validate CSRF token
if (empty($csrf_token) || !isset($_SESSION['csrf_token']) || $csrf_token !== $_SESSION['csrf_token']) {
    echo json_encode([
        'success' => false,
        'message' => 'Geçersiz güvenlik tokeni'
    ]);
    exit;
}

// Check which field to update
$allowed_fields = ['price', 'stock'];
$field = null;
$value = null;

foreach ($allowed_fields as $allowed_field) {
    if (isset($_POST[$allowed_field])) {
        $field = $allowed_field;
        $value = $_POST[$allowed_field];
        break;
    }
}

if ($field === null) {
    echo json_encode([
        'success' => false,
        'message' => 'Güncelleme için geçerli bir alan sağlanmadı'
    ]);
    exit;
}

// Validate value based on field
if ($field === 'price') {
    $value = (float)$value;
    if ($value <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Fiyat sıfırdan büyük olmalıdır'
        ]);
        exit;
    }
} elseif ($field === 'stock') {
    $value = (int)$value;
    if ($value < 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Stok negatif olamaz'
        ]);
        exit;
    }
}

try {
    // Get connection to the product database
    $pdo = getProductConnection();
    
    // Check if product exists
    $stmt = $pdo->prepare("SELECT id FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    
    if ($stmt->rowCount() === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Ürün bulunamadı'
        ]);
        exit;
    }
    
    // Update the field
    $stmt = $pdo->prepare("UPDATE products SET {$field} = ? WHERE id = ?");
    $stmt->execute([$value, $product_id]);
    
    // Return success
    echo json_encode([
        'success' => true,
        'message' => 'Ürün başarıyla güncellendi',
        'product_id' => $product_id,
        'field' => $field,
        'value' => $value,
        'low_stock' => $field === 'stock' && $value <= LOW_STOCK_THRESHOLD
    ]);
    
} catch (PDOException $e) {
    // Return error message
    echo json_encode([
        'success' => false,
        'message' => 'Ürün güncellenirken hata oluştu'
    ]);
    
    // Log the error (don't expose in production)
    error_log('Error in update_product_field.php: ' . $e->getMessage());
}