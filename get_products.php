<?php
// Include session check to prevent unauthorized access
require_once 'check_session.php';
require_once 'db_connection.php';

// Set response header to JSON
header('Content-Type: application/json');

// Get request parameters with defaults
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort_by = isset($_GET['sort_by']) ? trim($_GET['sort_by']) : 'name';
$sort_dir = isset($_GET['sort_dir']) && strtoupper($_GET['sort_dir']) === 'DESC' ? 'DESC' : 'ASC';
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$brand_id = isset($_GET['brand_id']) ? (int)$_GET['brand_id'] : 0;
$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;
$stock_filter = isset($_GET['stock_filter']) ? trim($_GET['stock_filter']) : '';

// Validate parameters
if ($page < 1) $page = 1;
if ($limit < 1 || $limit > 100) $limit = 10;

// Calculate offset
$offset = ($page - 1) * $limit;

// Map sort fields to actual database columns
$sort_fields = [
    'name' => 'p.name',
    'category_name' => 'c.name',
    'brand_name' => 'b.name',
    'price' => 'p.price',
    'stock' => 'p.stock',
    'created_at' => 'p.created_at'
];

// Default sort
if (!array_key_exists($sort_by, $sort_fields)) {
    $sort_by = 'name';
}

try {
    // Get connection to the product database
    $pdo = getProductConnection();
    
    // If requesting a specific product
    if ($product_id > 0) {
        $stmt = $pdo->prepare("
            SELECT 
                p.id, p.name, p.stock, p.price, p.thumbnail, 
                p.category_id, p.brand_id, p.created_at, p.updated_at,
                c.name AS category_name, b.name AS brand_name
            FROM products p
            JOIN categories c ON p.category_id = c.id
            JOIN brands b ON p.brand_id = b.id
            WHERE p.id = ?
        ");
        $stmt->execute([$product_id]);
        $products = $stmt->fetchAll();
        
        foreach ($products as &$product) {
            $product['low_stock'] = $product['stock'] <= LOW_STOCK_THRESHOLD;
        }
        
        echo json_encode([
            'success' => true,
            'products' => $products
        ]);
        exit;
    }
    
    // Build the query for product listing
    $query = "
        SELECT 
            p.id, p.name, p.stock, p.price, p.thumbnail, 
            p.category_id, p.brand_id, p.created_at,
            c.name AS category_name, b.name AS brand_name
        FROM products p
        JOIN categories c ON p.category_id = c.id
        JOIN brands b ON p.brand_id = b.id
        WHERE 1=1
    ";
    
    $params = [];
    
    // Add search condition if provided
    if (!empty($search)) {
        $query .= " AND (
            p.name LIKE ? OR 
            c.name LIKE ? OR 
            b.name LIKE ?
        )";
        $search_param = "%{$search}%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }
    
    // Add category filter if provided
    if ($category_id > 0) {
        $query .= " AND p.category_id = ?";
        $params[] = $category_id;
    }
    
    // Add brand filter if provided
    if ($brand_id > 0) {
        $query .= " AND p.brand_id = ?";
        $params[] = $brand_id;
    }
    
    // Add stock filtering
    if ($stock_filter === 'low') {
        // Filter for low stock (greater than 0 but less than or equal to LOW_STOCK_THRESHOLD)
        $query .= " AND p.stock > 0 AND p.stock <= " . LOW_STOCK_THRESHOLD;
    } elseif ($stock_filter === 'zero') {
        // Filter for out of stock (stock = 0)
        $query .= " AND p.stock = 0";
    }
    
    // Get total count for pagination
    $count_stmt = $pdo->prepare("SELECT COUNT(*) FROM ({$query}) AS filtered_products");
    $count_stmt->execute($params);
    $total_products = $count_stmt->fetchColumn();
    
    // Add order and limit
    $sort_field = $sort_fields[$sort_by];
    $query .= " ORDER BY {$sort_field} {$sort_dir}";
    $query .= " LIMIT {$limit} OFFSET {$offset}";
    
    // Execute the main query
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    
    // Fetch products
    $products = $stmt->fetchAll();
    
    // Calculate total pages
    $total_pages = ceil($total_products / $limit);
    
    // Add low_stock flag for easier frontend handling
    foreach ($products as &$product) {
        $product['low_stock'] = $product['stock'] <= LOW_STOCK_THRESHOLD;
    }
    
    // Return JSON response
    echo json_encode([
        'success' => true,
        'products' => $products,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total_products' => $total_products,
            'total_pages' => $total_pages
        ]
    ]);
    
} catch (PDOException $e) {
    // Return error message
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching products: ' . $e->getMessage()
    ]);
    
    // Log the error (don't expose in production)
    error_log('Error in get_products.php: ' . $e->getMessage());
} 