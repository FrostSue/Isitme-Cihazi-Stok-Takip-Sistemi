CREATE DATABASE IF NOT EXISTS hearing_aid_products;
USE hearing_aid_products;

-- Categories table with translated names to Turkish
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Brands table (independent of categories)
CREATE TABLE IF NOT EXISTS brands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    brand_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    price DECIMAL(10,2) NOT NULL,
    thumbnail VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
    FOREIGN KEY (brand_id) REFERENCES brands(id) ON DELETE CASCADE,
    INDEX idx_stock (stock),
    INDEX idx_category (category_id),
    INDEX idx_brand (brand_id)
);

-- Sample data: Categories in Turkish
INSERT INTO categories (id, name) VALUES 
(1, 'İşitme Cihazı'),
(2, 'Pil'),
(3, 'Kulak Kalıbı'),
(4, 'Temizleme Kitleri'),
(5, 'Aksesuar');

-- Sample data: Brands (only once, regardless of category)
INSERT INTO brands (id, name) VALUES 
(1, 'Phonak'),
(2, 'Oticon'),
(3, 'Signia'),
(4, 'Widex'),
(5, 'Rayovac'),
(6, 'Duracell'),
(7, 'Energizer'),
(8, 'Powerone');

-- Sample data: Products with the correct IDs, names and image paths
INSERT INTO products (id, category_id, brand_id, name, stock, price, thumbnail) VALUES
-- Product ID 1-4: Hearing Aids
(1, 1, 1, 'Phonak Audeo Paradise', 15, 59999.70, 'images/1-phonak-audeo-paradise.png'),
(2, 1, 3, 'Signia Pure 312 X', 12, 65999.70, 'images/2-signia-pure-312-x.png'),
(3, 1, 2, 'Oticon More 1', 5, 68999.70, 'images/3-oticon-more-1.png'),
(4, 1, 4, 'Widex Moment 440', 7, 56999.70, 'images/4-widex-moment-440.png'),

-- Product ID 5-8: Battery Products
(5, 2, 5, 'Size 10 İşitme Cihazı Pili', 100, 1500.00, 'images/5-rayovac-size-10.png'),
(6, 2, 6, 'Size 312 İşitme Cihazı Pili', 80, 1650.00, 'images/6-duracell-size-312.png'),
(7, 2, 7, 'Size 13 İşitme Cihazı Pili', 70, 1800.00, 'images/7-energizer-size-13.png'),
(8, 2, 8, 'Size 675 İşitme Cihazı Pili', 60, 1950.00, 'images/8-powerone-size-675.png'),

-- Product ID 9-12: Earmold Products
(9, 3, 1, 'Silicone Kulak Kalıbı', 30, 3000.00, 'images/9-phonak-earmold-silicone.png'),
(10, 3, 2, 'Akrilik Kulak Kalıbı', 25, 2500.00, 'images/10-oticon-earmold-acrylic.png'),
(11, 3, 3, 'Havalandırmalı Kulak Kalıbı', 20, 2300.00, 'images/11-signia-earmold-vented.png'),
(12, 3, 4, 'Bebek Kulak Kalıbı', 15, 3500.00, 'images/12-widex-earmold-baby.png'),

-- Product ID 13-16: Cleaning Products
(13, 4, 2, 'İşitme Cihazı Temizleme Kit', 35, 900.00, 'images/13-oticon-cleaning-kit.png'),
(14, 4, 1, 'Nem Alma Tableti', 40, 600.00, 'images/14-phonak-dry-bricks.png'),
(15, 4, 3, 'Temizleme Fırçası', 60, 300.00, 'images/15-signia-brush.png'),
(16, 4, 4, 'Dezenfektan Sprey', 45, 450.00, 'images/16-widex-spray.png'),

-- Product ID 17-20: Accessories
(17, 5, 1, 'İşitme Cihazı Kordonu', 25, 7500.00, 'images/17-phonak-clip-line.png'),
(18, 5, 2, 'Bluetooth Bağlantı Modülü', 20, 9000.00, 'images/18-oticon-connectivity-module.png'),
(19, 5, 3, 'Pil Çıkarıcı', 30, 750.00, 'images/19-signia-battery-tool.png'),
(20, 5, 4, 'Taşıma Kutusu', 50, 1200.00, 'images/20-widex-case-travel.png'); 