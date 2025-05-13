<?php
// Include session check to prevent unauthorized access
require_once 'check_session.php';
require_once 'db_connection.php';

// Set response header to JSON
header('Content-Type: application/json');

try {
    $pdo = getProductConnection();
    
    // Get counts for dashboard stats
    $stmt = $pdo->query("SELECT COUNT(*) FROM products");
    $totalProducts = $stmt->fetchColumn();
    
    // Get count of low stock products (greater than 0 but less than or equal to threshold)
    $stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE stock > 0 AND stock <= " . LOW_STOCK_THRESHOLD);
    $lowStockCount = $stmt->fetchColumn();
    
    // Get count of out of stock products
    $stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE stock = 0");
    $outOfStockCount = $stmt->fetchColumn();
    
    // Return success with counts
    echo json_encode([
        'success' => true,
        'totalProducts' => $totalProducts,
        'lowStockCount' => $lowStockCount,
        'outOfStockCount' => $outOfStockCount
    ]);
    
} catch (PDOException $e) {
    // Return error message
    echo json_encode([
        'success' => false,
        'message' => 'Stok sayıları alınırken hata oluştu'
    ]);
    
    // Log the error
    error_log('Error in get_stock_counts.php: ' . $e->getMessage());
}
?> 