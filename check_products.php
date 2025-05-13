<?php
// Include database connection
require_once 'db_connection.php';

// Set error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Product Verification</h1>";

try {
    $pdo = getProductConnection();
    
    // Get all products
    $stmt = $pdo->query("
        SELECT p.*, b.name AS brand_name, c.name AS category_name
        FROM products p
        JOIN brands b ON p.brand_id = b.id
        JOIN categories c ON p.category_id = c.id
        ORDER BY p.id ASC
    ");
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get distinct brands
    $stmt = $pdo->query("SELECT * FROM brands ORDER BY name ASC");
    $brands = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Distinct Brands</h2>";
    echo "<p>Total brand count: " . count($brands) . "</p>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Brand Name</th></tr>";
    
    foreach ($brands as $brand) {
        echo "<tr>";
        echo "<td>" . $brand['id'] . "</td>";
        echo "<td>" . htmlspecialchars($brand['name']) . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    // Display product details
    echo "<h2>Products</h2>";
    echo "<p>Total product count: " . count($products) . "</p>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Name</th><th>Brand</th><th>Category</th><th>Price (₺)</th><th>Stock</th><th>Image Path</th></tr>";
    
    foreach ($products as $product) {
        echo "<tr>";
        echo "<td>" . $product['id'] . "</td>";
        echo "<td>" . htmlspecialchars($product['name']) . "</td>";
        echo "<td>" . htmlspecialchars($product['brand_name']) . "</td>";
        echo "<td>" . htmlspecialchars($product['category_name']) . "</td>";
        echo "<td>" . number_format($product['price'], 2) . " ₺</td>";
        echo "<td>" . $product['stock'] . "</td>";
        echo "<td>" . htmlspecialchars($product['thumbnail']) . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<p><a href='product_list.php'>Return to Product List</a></p>";
    
} catch (PDOException $e) {
    echo "<h2>Error</h2>";
    echo "<p>An error occurred: " . htmlspecialchars($e->getMessage()) . "</p>";
    error_log('Error in check_products.php: ' . $e->getMessage());
}
?> 