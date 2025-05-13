<?php
// Include database connection
require_once 'db_connection.php';

// Set error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Database Update Script</h1>";

try {
    $pdo = getProductConnection();
    
    // Step 1: Fix the brands structure - reorganize brands to be independent of categories
    echo "<h2>Fixing Brands Structure</h2>";
    
    // 1.1: Create a temporary table to store unique brands
    $pdo->exec("DROP TABLE IF EXISTS temp_brands");
    $pdo->exec("CREATE TABLE temp_brands (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // 1.2: Insert unique brand names into the temp table
    $pdo->exec("INSERT IGNORE INTO temp_brands (name) 
                SELECT DISTINCT name FROM brands");
    
    // 1.3: Create a mapping of old brand IDs to new brand IDs
    $stmt = $pdo->query("
        SELECT b.id AS old_id, tb.id AS new_id, b.name 
        FROM brands b
        JOIN temp_brands tb ON b.name = tb.name
    ");
    $brandMapping = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Output brand mapping for reference
    echo "<p>Brand Mapping:</p><pre>";
    print_r($brandMapping);
    echo "</pre>";
    
    // 1.4: Update products table to use new brand IDs (temporary solution using update statements)
    foreach ($brandMapping as $mapping) {
        $stmt = $pdo->prepare("
            UPDATE products 
            SET brand_id = ? 
            WHERE brand_id = ?
        ");
        $stmt->execute([$mapping['new_id'], $mapping['old_id']]);
    }
    
    // 1.5: Drop old brands table and rename temp_brands
    $pdo->exec("DROP TABLE brands");
    $pdo->exec("RENAME TABLE temp_brands TO brands");
    
    // 1.6: Re-add the foreign key constraint (this needs to be modified in a real environment)
    // Note: In a production environment, you might need to carefully handle this
    $pdo->exec("ALTER TABLE products ADD CONSTRAINT fk_brand FOREIGN KEY (brand_id) REFERENCES brands(id)");
    
    echo "<p>Brand structure successfully fixed.</p>";
    
    // Step 2: Update product names and image paths based on provided list
    echo "<h2>Updating Product Names and Images</h2>";
    
    // Product mapping - ID => [name, image]
    $productUpdates = [
        1 => ['Phonak Audeo Paradise', 'images/1-phonak-audeo-paradise.png'],
        2 => ['Signia Pure 312 X', 'images/2-signia-pure-312-x.png'],
        3 => ['Oticon More 1', 'images/3-oticon-more-1.png'],
        4 => ['Widex Moment 440', 'images/4-widex-moment-440.png'],
        5 => ['Rayovac Size 10', 'images/5-rayovac-size-10.png'],
        6 => ['Duracell Size 312', 'images/6-duracell-size-312.png'],
        7 => ['Energizer Size 13', 'images/7-energizer-size-13.png'],
        8 => ['Powerone Size 675', 'images/8-powerone-size-675.png'],
        9 => ['Phonak Earmold Silicone', 'images/9-phonak-earmold-silicone.png'],
        10 => ['Oticon Earmold Acrylic', 'images/10-oticon-earmold-acrylic.png'],
        11 => ['Signia Earmold Vented', 'images/11-signia-earmold-vented.png'],
        12 => ['Widex Earmold Baby', 'images/12-widex-earmold-baby.png'],
        13 => ['Oticon Cleaning Kit', 'images/13-oticon-cleaning-kit.png'],
        14 => ['Phonak Dry Bricks', 'images/14-phonak-dry-bricks.png'],
        15 => ['Signia Brush', 'images/15-signia-brush.png'],
        16 => ['Widex Spray', 'images/16-widex-spray.png'],
        17 => ['Phonak Clip Line', 'images/17-phonak-clip-line.png'],
        18 => ['Oticon Connectivity Module', 'images/18-oticon-connectivity-module.png'],
        19 => ['Signia Battery Tool', 'images/19-signia-battery-tool.png'],
        20 => ['Widex Case Travel', 'images/20-widex-case-travel.png']
    ];
    
    // Update each product
    foreach ($productUpdates as $id => $data) {
        $stmt = $pdo->prepare("
            UPDATE products 
            SET name = ?, thumbnail = ? 
            WHERE id = ?
        ");
        $stmt->execute([$data[0], $data[1], $id]);
        
        echo "<p>Updated product ID $id to: {$data[0]} with image: {$data[1]}</p>";
    }
    
    // Step 3: Convert prices to Turkish Lira (using an exchange rate of approximately 1 USD = 30 TRY for example)
    echo "<h2>Converting Prices to Turkish Lira</h2>";
    
    // Set exchange rate
    $exchangeRate = 30.0; // 1 USD = 30 TRY (example rate)
    
    // Update all prices
    $stmt = $pdo->prepare("
        UPDATE products 
        SET price = price * ?
    ");
    $stmt->execute([$exchangeRate]);
    
    echo "<p>Converted all prices to Turkish Lira using exchange rate: $exchangeRate</p>";
    
    // Update the price display format in PHP files to show ₺ instead of $
    echo "<h2>Updating Price Display in Files</h2>";
    
    // Files to update
    $filesToUpdate = [
        'product_list.php',
        'selling_interface.php',
        'add_product.php'
    ];
    
    foreach ($filesToUpdate as $file) {
        $filePath = __DIR__ . '/' . $file;
        
        if (file_exists($filePath)) {
            $content = file_get_contents($filePath);
            
            // Replace $ with ₺ for price display
            $content = str_replace('$<span', '₺<span', $content);
            $content = str_replace('$${', '₺${', $content);
            $content = str_replace("value.replace('$'", "value.replace('₺'", $content);
            $content = str_replace("parentElement.textContent = '$'", "parentElement.textContent = '₺'", $content);
            $content = str_replace('`$${', '`₺${', $content);
            
            file_put_contents($filePath, $content);
            echo "<p>Updated currency symbol in file: $file</p>";
        } else {
            echo "<p>File not found: $file</p>";
        }
    }
    
    echo "<h2>Update Complete!</h2>";
    echo "<p>The database has been successfully updated:</p>";
    echo "<ul>";
    echo "<li>Brand structure fixed to eliminate duplicates</li>";
    echo "<li>Product names and image paths updated</li>";
    echo "<li>Prices converted to Turkish Lira</li>";
    echo "<li>Currency symbols updated in PHP files</li>";
    echo "</ul>";
    echo "<p><a href='product_list.php'>Return to Product List</a></p>";
    
} catch (PDOException $e) {
    echo "<h2>Error</h2>";
    echo "<p>An error occurred: " . htmlspecialchars($e->getMessage()) . "</p>";
    error_log('Error in update_database.php: ' . $e->getMessage());
}
?> 