<?php
// Database connection constants
define('DB_HOST', 'localhost');
define('DB_ADMIN_NAME', 'hearing_aid_admin');
define('DB_PRODUCT_NAME', 'hearing_aid_products');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');
define('LOW_STOCK_THRESHOLD', 5);

/**
 * Create a PDO connection to the Admin database
 * 
 * @return PDO Database connection
 */
function getAdminConnection() {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_ADMIN_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    try {
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (\PDOException $e) {
        throw new \PDOException($e->getMessage(), (int)$e->getCode());
    }
}

/**
 * Create a PDO connection to the Product database
 * 
 * @return PDO Database connection
 */
function getProductConnection() {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_PRODUCT_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    try {
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (\PDOException $e) {
        throw new \PDOException($e->getMessage(), (int)$e->getCode());
    }
} 