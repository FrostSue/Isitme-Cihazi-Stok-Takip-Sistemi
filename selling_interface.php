<?php
// Include session check to prevent unauthorized access
require_once 'check_session.php';
require_once 'db_connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Satış Arayüzü - Ürün Satış ve Stok Yönetimi</title>
    <link rel="shortcut icon" href="data:image/x-icon;," type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="animations.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .selling-container {
            max-width: 800px;
            margin: 50px auto;
        }
        
        .product-selection {
            margin-bottom: 30px;
        }
        
        .product-details {
            display: flex;
            margin-top: 30px;
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: var(--box-shadow);
            overflow: hidden;
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.3s ease, transform 0.3s ease;
        }
        
        .product-details.active {
            opacity: 1;
            transform: translateY(0);
        }
        
        .product-image {
            flex: 0 0 250px;
            background-color: #f9f9f9;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .product-image img {
            max-width: 100%;
            max-height: 250px;
            object-fit: contain;
        }
        
        .product-info {
            flex: 1;
            padding: 20px;
        }
        
        .product-price {
            font-size: 1.8rem;
            color: var(--primary-color);
            margin-bottom: 10px;
            font-weight: bold;
        }
        
        .product-stock {
            margin-bottom: 20px;
            font-size: 1.1rem;
        }
        
        .action-btn {
            margin-top: 20px;
        }
        
        .recently-sold {
            margin-top: 40px;
        }
        
        .recently-sold h3 {
            margin-bottom: 15px;
            color: var(--text-color);
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 10px;
        }
        
        .sold-item {
            display: flex;
            background-color: var(--white);
            border-radius: 5px;
            box-shadow: var(--box-shadow);
            margin-bottom: 10px;
            overflow: hidden;
            animation: fadeIn 0.3s ease;
        }
        
        .sold-item-image {
            flex: 0 0 80px;
            background-color: #f9f9f9;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .sold-item-image img {
            width: 60px;
            height: 60px;
            object-fit: cover;
        }
        
        .sold-item-info {
            flex: 1;
            padding: 10px 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .sold-item-name {
            font-weight: 500;
        }
        
        .sold-item-time {
            color: var(--text-light);
            font-size: 0.875rem;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .pulse {
            animation: pulse 1s infinite;
        }
        
        .btn:disabled {
            opacity: 0.5 !important;
            cursor: not-allowed !important;
            pointer-events: none !important;
            background-color: #ccc !important;
            border-color: #ccc !important;
        }
        
        option.out-of-stock {
            color: #e74c3c;
            font-style: italic;
        }
        
        .out-of-stock-badge {
            display: inline-block;
            background-color: #e74c3c;
            color: white;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 0.8rem;
            margin-left: 5px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-hearing"></i>
                    <span>İşitme Cihazı Stok Yönetim Paneli</span>
                </div>
                <div class="user-info">
                    <span class="user-name">Hoşgeldiniz, <?= htmlspecialchars($_SESSION['username']) ?></span>
                    <a href="product_list.php" class="btn btn-sm btn-primary">
                        <i class="fas fa-list"></i> Ürün Listesi
                    </a>
                    <a href="logout.php" class="btn btn-sm btn-danger">
                        <i class="fas fa-sign-out-alt"></i> Çıkış
                    </a>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Main Content -->
    <main class="container">
        <div class="selling-container">
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-shopping-cart"></i> Ürün Satış ve Stok Yönetimi</h2>
                </div>
                
                <div class="card-body">
                    <div class="product-selection">
                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label for="category-select">Kategori:</label>
                                    <select id="category-select" class="form-control">
                                        <option value="">Kategori Seç</option>
                                        <!-- Will be populated via JavaScript -->
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col">
                                <div class="form-group">
                                    <label for="brand-select">Marka:</label>
                                    <select id="brand-select" class="form-control" disabled>
                                        <option value="">Önce kategori seçin</option>
                                    </select>
                                    <small class="form-text text-muted">Sadece seçilen kategorideki markalar gösterilecektir</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="product-select">Ürün:</label>
                            <select id="product-select" class="form-control" disabled>
                                <option value="">Ürün Seç</option>
                            </select>
                        </div>
                    </div>
                    
                    <div id="product-details" class="product-details">
                        <div class="product-image">
                            <img id="product-image" src="https://via.placeholder.com/250" alt="Ürün Görseli">
                        </div>
                        <div class="product-info">
                            <h3 id="product-name">Ürün Adı</h3>
                            <div class="product-price">₺<span id="product-price">0.00</span></div>
                            <div class="product-stock">
                                <span>Stok: </span>
                                <span id="product-stock">0</span>
                                <span id="low-stock-badge" class="badge badge-warning" style="display: none;">Az Stok</span>
                            </div>
                            <button id="sell-btn" class="btn btn-primary action-btn">
                                <i class="fas fa-shopping-cart"></i> Bu Ürünü Sat
                            </button>
                        </div>
                    </div>
                    
                    <div id="recently-sold" class="recently-sold">
                        <h3>Son Satılan Ürünler</h3>
                        <div id="recently-sold-items">
                            <!-- Will be populated via JavaScript -->
                            <div class="no-data">Bugün satış yapılmadı.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // DOM elements
        const categorySelect = document.getElementById('category-select');
        const brandSelect = document.getElementById('brand-select');
        const productSelect = document.getElementById('product-select');
        const productDetails = document.getElementById('product-details');
        const productImage = document.getElementById('product-image');
        const productName = document.getElementById('product-name');
        const productPrice = document.getElementById('product-price');
        const productStock = document.getElementById('product-stock');
        const stockStatusBadge = document.getElementById('low-stock-badge');
        const sellBtn = document.getElementById('sell-btn');
        const recentlySoldItems = document.getElementById('recently-sold-items');
        
        // Constants
        const LOW_STOCK_THRESHOLD = <?= LOW_STOCK_THRESHOLD ?>;
        const RECENT_SALES_MAX = 5;
        
        // State
        let selectedProduct = null;
        let recentSales = [];
        
        // Try to load recent sales from localStorage
        try {
            const savedSales = localStorage.getItem('recentSales');
            if (savedSales) {
                recentSales = JSON.parse(savedSales);
                updateRecentSalesDisplay();
            }
        } catch (error) {
            console.error('Error loading recent sales:', error);
        }
        
        // Initial data loading
        loadCategories();
        
        // Event listeners
        categorySelect.addEventListener('change', handleCategoryChange);
        brandSelect.addEventListener('change', handleBrandChange);
        productSelect.addEventListener('change', handleProductChange);
        sellBtn.addEventListener('click', handleSellButtonClick);
        
        // Load all categories
        function loadCategories() {
            fetch('get_categories.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Clear and populate the dropdown
                        categorySelect.innerHTML = '<option value="">Kategori Seç</option>';
                        
                        data.categories.forEach(category => {
                            const option = document.createElement('option');
                            option.value = category.id;
                            option.textContent = category.name;
                            categorySelect.appendChild(option);
                        });
                    } else {
                        console.error('Error loading categories:', data.message);
                    }
                })
                .catch(error => console.error('Error loading categories:', error));
        }
        
        // Handle category selection change
        function handleCategoryChange() {
            const categoryId = categorySelect.value;
            
            // Reset dependent dropdowns
            brandSelect.innerHTML = '<option value="">Marka Seç</option>';
            brandSelect.disabled = true;
            productSelect.innerHTML = '<option value="">Ürün Seç</option>';
            productSelect.disabled = true;
            hideProductDetails();
            
            if (categoryId === '') {
                showNotification('Lütfen bir kategori seçin', 'warning');
                return;
            }
            
            // Show loading state
            brandSelect.innerHTML = '<option value="">Markalar yükleniyor...</option>';
            
            // Load brands for the selected category
            fetch(`get_brands.php?category_id=${categoryId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Clear and populate the dropdown
                        brandSelect.innerHTML = '<option value="">Marka Seç</option>';
                        
                        if (data.brands.length === 0) {
                            // No brands found for this category
                            brandSelect.innerHTML = '<option value="">Bu kategoride marka bulunamadı</option>';
                            brandSelect.disabled = true;
                            return;
                        }
                        
                        data.brands.forEach(brand => {
                            const option = document.createElement('option');
                            option.value = brand.id;
                            option.textContent = brand.name;
                            brandSelect.appendChild(option);
                        });
                        
                        // Enable the brand dropdown
                        brandSelect.disabled = false;
                    } else {
                        console.error('Error loading brands:', data.message);
                        brandSelect.innerHTML = '<option value="">Markalar yüklenirken hata oluştu</option>';
                    }
                })
                .catch(error => {
                    console.error('Error loading brands:', error);
                    brandSelect.innerHTML = '<option value="">Markalar yüklenirken hata oluştu</option>';
                });
        }
        
        // Handle brand selection change
        function handleBrandChange() {
            const brandId = brandSelect.value;
            const categoryId = categorySelect.value;
            
            // Reset product dropdown
            productSelect.innerHTML = '<option value="">Ürünler Yükleniyor...</option>';
            productSelect.disabled = true;
            hideProductDetails();
            
            if (brandId === '') {
                showNotification('Lütfen bir marka seçin', 'warning');
                return;
            }
            
            // Show loading state
            productSelect.innerHTML = '<option value="">Loading products...</option>';
            
            // Load products for the selected brand and category
            fetch(`get_product_by_brand.php?brand_id=${brandId}&category_id=${categoryId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Clear and populate the dropdown
                        productSelect.innerHTML = '<option value="">Ürün Seç</option>';
                        
                        data.products.forEach(product => {
                            const option = document.createElement('option');
                            option.value = product.id;
                            option.textContent = product.name;
                            
                            // Add out of stock indicator
                            if (product.stock <= 0) {
                                option.textContent += ' (Stokta Yok)';
                                option.classList.add('out-of-stock');
                            } else if (product.stock <= LOW_STOCK_THRESHOLD) {
                                option.textContent += ` (Sadece ${product.stock} adet kaldı)`;
                            }
                            
                            productSelect.appendChild(option);
                        });
                        
                        // Enable the product dropdown
                        productSelect.disabled = false;
                    } else {
                        console.error('Ürünleri yüklerken hata:', data.message);
                        productSelect.innerHTML = '<option value="">Ürünler yüklenirken hata oluştu</option>';
                    }
                })
                .catch(error => {
                    console.error('Error loading products:', error);
                    productSelect.innerHTML = '<option value="">Error loading products</option>';
                });
        }
        
        // Handle product selection change
        function handleProductChange() {
            const productId = productSelect.value;
            
            hideProductDetails();
            sellBtn.disabled = true;
            
            if (productId === '') {
                showNotification('Lütfen bir ürün seçin', 'warning');
                return;
            }
            
            // Fetch detailed product information
            fetch(`get_products.php?product_id=${productId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.products.length > 0) {
                        selectedProduct = data.products[0];
                        displayProductDetails(selectedProduct);
                    } else {
                        console.error('Error loading product details:', data.message || 'Product not found');
                    }
                })
                .catch(error => console.error('Error loading product details:', error));
        }
        
        // Display product details
        function displayProductDetails(product) {
            // Set image
            productImage.src = product.thumbnail || 'https://via.placeholder.com/250';
            productImage.alt = product.name;
            
            // Set text details
            productName.textContent = product.name;
            productPrice.textContent = `${parseFloat(product.price).toFixed(2)}`;
            
            // Show stock status with appropriate styling
            if (product.stock <= 0) {
                productStock.innerHTML = `0 <span class="out-of-stock-badge">Stokta Yok</span>`;
            } else {
                productStock.textContent = product.stock;
            }
            
            // Show low stock warning if needed
            if (product.stock <= LOW_STOCK_THRESHOLD && product.stock > 0) {
                stockStatusBadge.style.display = 'inline-block';
                stockStatusBadge.textContent = product.stock === 1 ? 'Son Ürün' : 'Az Stok';
            } else {
                stockStatusBadge.style.display = 'none';
            }
            
            // Ensure sell button is properly disabled for zero stock
            if (product.stock <= 0) {
                sellBtn.disabled = true;
                sellBtn.classList.remove('pulse');
            } else {
                sellBtn.disabled = false;
                
                // Animate button if low stock but not zero
                if (product.stock <= 3) {
                    sellBtn.classList.add('pulse');
                } else {
                    sellBtn.classList.remove('pulse');
                }
            }
            
            // Show the product details section
            productDetails.classList.add('active');
        }
        
        // Hide product details
        function hideProductDetails() {
            productDetails.classList.remove('active');
            selectedProduct = null;
        }
        
        // Handle sell button click
        function handleSellButtonClick() {
            if (!selectedProduct || selectedProduct.stock <= 0) return;
            
            const formData = new FormData();
            formData.append('product_id', selectedProduct.id);
            
            // Show loading state
            sellBtn.disabled = true;
            sellBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> İşleniyor...';
            
            // Send sell request
            fetch('sell_product.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update stock display
                    selectedProduct.stock = data.new_stock;
                    productStock.textContent = data.new_stock;
                    
                    // Update low stock warning
                    if (data.new_stock <= LOW_STOCK_THRESHOLD) {
                        stockStatusBadge.style.display = 'inline-block';
                        stockStatusBadge.textContent = data.new_stock === 1 ? 'Son Ürün' : 'Az Stok';
                    } else {
                        stockStatusBadge.style.display = 'none';
                    }
                    
                    // Properly disable sell button if out of stock
                    if (data.new_stock <= 0) {
                        sellBtn.disabled = true;
                        sellBtn.classList.remove('pulse');
                    } else {
                        sellBtn.disabled = false;
                        
                        // Animate if low stock
                        if (data.new_stock <= 3) {
                            sellBtn.classList.add('pulse');
                        } else {
                            sellBtn.classList.remove('pulse');
                        }
                    }
                    
                    // Add to recent sales
                    addRecentSale(selectedProduct);
                    
                    // Show success message
                    showNotification('Ürün başarıyla satıldı', 'success');
                    
                    // Update product in dropdown
                    updateProductOptionStock(selectedProduct.id, data.new_stock);
                    
                } else {
                    showNotification('Bu ürün artık stokta yok', 'error');
                }
            })
            .catch(error => {
                console.error('Ürün satılırken hata oluştu', error);
                showNotification('Ürün satılırken hata oluştu', 'error');
            })
            .finally(() => {
                // Reset button state but keep disabled if necessary
                sellBtn.innerHTML = '<i class="fas fa-shopping-cart"></i> Bu Ürünü Sat';
                
                // Make sure the button stays disabled if out of stock
                if (!selectedProduct || selectedProduct.stock <= 0) {
                    sellBtn.disabled = true;
                    sellBtn.classList.remove('pulse');
                } else {
                    sellBtn.disabled = false;
                    
                    // Re-add pulse if low stock
                    if (selectedProduct.stock <= 3) {
                        sellBtn.classList.add('pulse');
                    }
                }
            });
        }
        
        // Add a product to recent sales
        function addRecentSale(product) {
            const sale = {
                id: product.id,
                name: product.name,
                thumbnail: product.thumbnail,
                price: product.price,
                category: categorySelect.options[categorySelect.selectedIndex].text,
                brand: brandSelect.options[brandSelect.selectedIndex].text,
                timestamp: new Date().toISOString()
            };
            
            // Add to the beginning of the array
            recentSales.unshift(sale);
            
            // Limit to maximum number of recent sales
            if (recentSales.length > RECENT_SALES_MAX) {
                recentSales = recentSales.slice(0, RECENT_SALES_MAX);
            }
            
            // Save to localStorage
            try {
                localStorage.setItem('recentSales', JSON.stringify(recentSales));
            } catch (error) {
                console.error('Error saving recent sales:', error);
            }
            
            // Update the display
            updateRecentSalesDisplay();
        }
        
        // Update recent sales display
        function updateRecentSalesDisplay() {
            if (recentSales.length === 0) {
                recentlySoldItems.innerHTML = '<div class="no-data">Bugün satış yapılmadı.</div>';
                return;
            }
            
            recentlySoldItems.innerHTML = '';
            
            recentSales.forEach(sale => {
                const saleTime = new Date(sale.timestamp);
                const timeString = saleTime.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                const dateString = saleTime.toLocaleDateString();
                
                const soldItem = document.createElement('div');
                soldItem.className = 'sold-item';
                
                soldItem.innerHTML = `
                    <div class="sold-item-image">
                        <img src="${sale.thumbnail || 'https://via.placeholder.com/60'}" alt="${escapeHtml(sale.name)}">
                    </div>
                    <div class="sold-item-info">
                        <div>
                            <div class="sold-item-name">${escapeHtml(sale.name)}</div>
                            <div class="sold-item-category">${escapeHtml(sale.category)} - ${escapeHtml(sale.brand)}</div>
                        </div>
                        <div>
                            <div class="sold-item-price">₺${parseFloat(sale.price).toFixed(2)}</div>
                            <div class="sold-item-time">Bugün saat ${timeString}</div>
                        </div>
                    </div>
                `;
                
                recentlySoldItems.appendChild(soldItem);
            });
        }
        
        // Update product option in dropdown after selling
        function updateProductOptionStock(productId, newStock) {
            const option = Array.from(productSelect.options).find(opt => opt.value === productId.toString());
            
            if (option) {
                if (newStock <= 0) {
                    option.disabled = true;
                    option.textContent = option.textContent.replace(/\(.*\)/, '(Stokta Yok)');
                } else if (newStock <= LOW_STOCK_THRESHOLD) {
                    option.textContent = option.textContent.replace(/\(.*\)/, `(Sadece ${newStock} adet kaldı)`);
                }
            }
        }
        
        // Show notification
        function showNotification(message, type = 'info') {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'error' ? 'error' : type === 'success' ? 'success' : 'info'}`;
            notification.style.position = 'fixed';
            notification.style.top = '20px';
            notification.style.right = '20px';
            notification.style.zIndex = '1000';
            notification.style.maxWidth = '300px';
            notification.style.boxShadow = '0 4px 8px rgba(0,0,0,0.2)';
            notification.style.transition = 'all 0.3s ease';
            notification.style.transform = 'translateX(400px)';
            
            // Add icon based on type
            const icon = type === 'error' ? 'exclamation-circle' : type === 'success' ? 'check-circle' : 'info-circle';
            notification.innerHTML = `<i class="fas fa-${icon}"></i> ${message}`;
            
            // Add to the DOM
            document.body.appendChild(notification);
            
            // Animate in
            setTimeout(() => {
                notification.style.transform = 'translateX(0)';
            }, 10);
            
            // Remove after delay
            setTimeout(() => {
                notification.style.transform = 'translateX(400px)';
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 3000);
        }
        
        // Helper function: Escape HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    });
    </script>
    <script src="animations.js"></script>
    <script src="header_animation.js"></script>
</body>
</html> 