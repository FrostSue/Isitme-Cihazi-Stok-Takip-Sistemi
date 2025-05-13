<?php
// Include session check to prevent unauthorized access
require_once 'check_session.php';
require_once 'db_connection.php';

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Get product connection for any immediate data needs
try {
    $pdo = getProductConnection();
    
    // Get counts for dashboard stats
    $stmt = $pdo->query("SELECT COUNT(*) FROM products");
    $totalProducts = $stmt->fetchColumn();
    
    // Modify this query to exclude products with 0 stock
    $stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE stock > 0 AND stock <= " . LOW_STOCK_THRESHOLD);
    $lowStockCount = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE stock = 0");
    $outOfStockCount = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM categories");
    $totalCategories = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM brands");
    $totalBrands = $stmt->fetchColumn();
    
} catch (PDOException $e) {
    $error = 'Database error occurred.';
    error_log('Error in product_list.php: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ürün Listesi - İşitme Cihazı Stok Yönetimi</title>
    <link rel="shortcut icon" href="data:image/x-icon;," type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="animations.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .dashboard-stats {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            flex: 1;
            min-width: 200px;
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: var(--box-shadow);
            padding: 20px;
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: var(--text-light);
            font-size: 0.9rem;
        }
        
        .icon-products { color: var(--primary-color); }
        .icon-categories { color: var(--secondary-color); }
        .icon-brands { color: var(--accent-color); }
        .icon-low-stock { color: var(--warning-color); }
        .icon-out-of-stock { color: var(--error-color); }
        
        .filters {
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: var(--box-shadow);
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 0.7rem;
            margin: 0 auto;
        }
        
        .action-buttons .btn {
            width: 44px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            transition: all 0.3s ease;
            padding: 0;
            border-radius: 8px;
        }
        
        .action-buttons .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        
        .product-image {
            height: 60px;
            width: 60px;
            object-fit: cover;
            border-radius: 4px;
            box-shadow: var(--box-shadow);
            display: block;
            margin: 0 auto;
        }
        
        .table-striped td {
            vertical-align: middle;
            text-align: center;
        }
        
        .table-striped th {
            text-align: center;
        }
        
        .modal-product-image {
            max-width: 100%;
            max-height: 300px;
            object-fit: contain;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        
        /* Add a specific style for disabled buttons to ensure they appear disabled */
        button:disabled, 
        .btn:disabled {
            opacity: 0.5 !important;
            cursor: not-allowed !important;
            pointer-events: none !important;
            background-color: #ccc !important;
            border-color: #aaa !important;
            color: #666 !important;
        }
        
        /* Add this to your existing styles in the <style> tag */
        .stat-card.clickable {
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .stat-card.clickable:hover {
            transform: translateY(-8px) !important;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }
        
        .stat-card.active {
            border: 2px solid var(--primary-color);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }
        
        /* Additional styles to highlight active filter */
        .filter-active {
            animation: pulse 1.5s infinite;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(52, 152, 219, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(52, 152, 219, 0); }
            100% { box-shadow: 0 0 0 0 rgba(52, 152, 219, 0); }
        }
        
        /* Add these styles to your existing style section */
        .out-of-stock-row {
            background-color: rgba(231, 76, 60, 0.07) !important;
        }
        
        .out-of-stock {
            color: var(--error-color);
            font-weight: bold;
        }
        
        /* Make sure the existing low-stock styles are correct */
        .low-stock {
            color: var(--warning-color);
            font-weight: bold;
        }
        
        .low-stock-row {
            background-color: rgba(243, 156, 18, 0.07) !important;
        }
        
        .table-striped tbody tr {
            transition: all 0.2s ease;
        }
        
        .table-striped tbody tr:hover {
            background-color: rgba(78, 84, 200, 0.05);
            transform: translateY(-2px);
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.08);
        }
        
        /* Add tooltip styles */
        [title] {
            position: relative;
            cursor: pointer;
        }
        
        /* Make product clickable elements have a pointer cursor */
        .product-clickable {
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .product-clickable:hover {
            color: var(--primary-color);
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
                    <a href="selling_interface.php" class="btn btn-sm btn-primary">
                        <i class="fas fa-shopping-cart"></i> Satış Arayüzü ve Stok Yönetimi
                    </a>
                    <a href="logout.php" class="btn btn-sm btn-danger">
                        <i class="fas fa-sign-out-alt"></i> Çıkış
                    </a>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Main Content -->
    <main class="container" style="padding-top: 2rem;">
        <!-- Dashboard Stats -->
        <div class="dashboard-stats">
            <div class="stat-card clickable" data-filter="all">
                <div class="stat-icon icon-products">
                    <i class="fas fa-boxes"></i>
                </div>
                <div class="stat-value"><?= $totalProducts ?></div>
                <div class="stat-label">Toplam Ürün</div>
            </div>
            
            <div class="stat-card clickable" data-filter="categories">
                <div class="stat-icon icon-categories">
                    <i class="fas fa-tags"></i>
                </div>
                <div class="stat-value"><?= $totalCategories ?></div>
                <div class="stat-label">Kategoriler</div>
            </div>
            
            <div class="stat-card clickable" data-filter="brands">
                <div class="stat-icon icon-brands">
                    <i class="fas fa-copyright"></i>
                </div>
                <div class="stat-value"><?= $totalBrands ?></div>
                <div class="stat-label">Markalar</div>
            </div>
            
            <div class="stat-card clickable" data-filter="low-stock">
                <div class="stat-icon icon-low-stock">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-value"><?= $lowStockCount ?></div>
                <div class="stat-label">Az Stoklu Ürünler</div>
            </div>
            
            <div class="stat-card clickable" data-filter="out-of-stock">
                <div class="stat-icon icon-out-of-stock">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-value"><?= $outOfStockCount ?></div>
                <div class="stat-label">Stokta Olmayan</div>
            </div>
        </div>
        
        <!-- Product List Card -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-list"></i> Ürünler</h2>
                <a href="add_product.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Yeni Ürün Ekle
                </a>
            </div>
            
            <!-- Filters -->
            <div class="filters">
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label for="search">Ara:</label>
                            <input type="text" id="search" class="form-control" placeholder="İsim, kategori veya marka ile ara...">
                        </div>
                    </div>
                    
                    <div class="col">
                        <div class="form-group">
                            <label for="filter-category">Kategori:</label>
                            <select id="filter-category" class="form-control">
                                <option value="">Tüm Kategoriler</option>
                                <!-- Will be populated via JavaScript -->
                            </select>
                        </div>
                    </div>
                    
                    <div class="col">
                        <div class="form-group">
                            <label for="filter-brand">Marka:</label>
                            <select id="filter-brand" class="form-control">
                                <option value="">Tüm Markalar</option>
                                <!-- Will be populated via JavaScript -->
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Product Table -->
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th width="90" class="text-center">Görsel</th>
                            <th class="sortable text-center" data-sort="name">Ürün Adı <i class="fas fa-sort"></i></th>
                            <th class="sortable text-center" data-sort="category_name">Kategori <i class="fas fa-sort"></i></th>
                            <th class="sortable text-center" data-sort="brand_name">Marka <i class="fas fa-sort"></i></th>
                            <th class="sortable text-center" data-sort="price">Fiyat <i class="fas fa-sort"></i></th>
                            <th class="sortable text-center" data-sort="stock">Stok <i class="fas fa-sort"></i></th>
                            <th width="175" class="text-center">İşlemler</th>
                        </tr>
                    </thead>
                    <tbody id="product-table-body">
                        <!-- Will be populated via JavaScript -->
                        <tr>
                            <td colspan="7" class="text-center">Ürünler yükleniyor...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="card-footer">
                <div id="pagination" class="pagination">
                    <!-- Will be populated via JavaScript -->
                </div>
                
                <div>
                    <span id="pagination-info">Yükleniyor...</span>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Product View Modal -->
    <div id="product-modal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title" id="modal-product-name">Ürün Detayları</h3>
                <button type="button" class="modal-close" id="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div style="text-align: center;">
                    <img id="modal-product-image" src="" alt="Product Image" class="modal-product-image">
                </div>
                <div class="row">
                    <div class="col">
                        <p><strong>Kategori:</strong> <span id="modal-product-category"></span></p>
                        <p><strong>Marka:</strong> <span id="modal-product-brand"></span></p>
                    </div>
                    <div class="col">
                        <p><strong>Fiyat:</strong> ₺<span id="modal-product-price"></span></p>
                        <p><strong>Stok:</strong> <span id="modal-product-stock"></span></p>
                    </div>
                </div>
                <p><strong>Eklenme:</strong> <span id="modal-product-created"></span></p>
                <p><strong>Son Güncelleme:</strong> <span id="modal-product-updated"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="modal-edit-btn">
                    <i class="fas fa-edit"></i> Düzenle
                </button>
                <button type="button" class="btn btn-danger" id="modal-delete-btn">
                    <i class="fas fa-trash"></i> Sil
                </button>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="delete-confirm-modal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Silmeyi Onayla</h3>
                <button type="button" class="modal-close" id="delete-modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <p>Bu <strong id="delete-product-name"></strong> ürününü silmek istediğinizden emin misiniz?</p>
                <p>Bu işlem geri alınamaz.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="delete-cancel-btn">
                    <i class="fas fa-times"></i> İptal
                </button>
                <button type="button" class="btn btn-danger" id="delete-confirm-btn">
                    <i class="fas fa-trash"></i> Sil
                </button>
            </div>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // DOM Elements
        const searchInput = document.getElementById('search');
        const categoryFilter = document.getElementById('filter-category');
        const brandFilter = document.getElementById('filter-brand');
        brandFilter.disabled = false; // Ensure brand filter is enabled by default
        const productTableBody = document.getElementById('product-table-body');
        const paginationContainer = document.getElementById('pagination');
        const paginationInfo = document.getElementById('pagination-info');
        
        // Product Modal Elements
        const productModal = document.getElementById('product-modal');
        const modalClose = document.getElementById('modal-close');
        const modalProductName = document.getElementById('modal-product-name');
        const modalProductImage = document.getElementById('modal-product-image');
        const modalProductCategory = document.getElementById('modal-product-category');
        const modalProductBrand = document.getElementById('modal-product-brand');
        const modalProductPrice = document.getElementById('modal-product-price');
        const modalProductStock = document.getElementById('modal-product-stock');
        const modalProductCreated = document.getElementById('modal-product-created');
        const modalProductUpdated = document.getElementById('modal-product-updated');
        const modalEditBtn = document.getElementById('modal-edit-btn');
        const modalDeleteBtn = document.getElementById('modal-delete-btn');
        
        // Delete Modal Elements
        const deleteConfirmModal = document.getElementById('delete-confirm-modal');
        const deleteModalClose = document.getElementById('delete-modal-close');
        const deleteProductName = document.getElementById('delete-product-name');
        const deleteCancelBtn = document.getElementById('delete-cancel-btn');
        const deleteConfirmBtn = document.getElementById('delete-confirm-btn');
        
        // State
        let currentPage = 1;
        let currentLimit = 10;
        let currentSort = 'name';
        let currentSortDir = 'ASC';
        let currentSearch = '';
        let currentCategoryId = '';
        let currentBrandId = '';
        let totalPages = 1;
        let selectedProductId = null;
        let currentStockFilter = '';  // To track which stock filter is active
        
        // Constants
        const CSRF_TOKEN = '<?= $_SESSION['csrf_token'] ?>';
        const LOW_STOCK_THRESHOLD = <?= LOW_STOCK_THRESHOLD ?>;
        
        // Initialize
        loadProducts();
        loadCategories();
        loadAllBrands();
        
        // Event Listeners
        searchInput.addEventListener('input', debounce(function() {
            currentSearch = searchInput.value.trim();
            currentPage = 1;
            loadProducts();
        }, 500));
        
        categoryFilter.addEventListener('change', function() {
            currentCategoryId = this.value;
            currentBrandId = '';
            currentPage = 1;
            
            // Reset brand filter
            brandFilter.innerHTML = '<option value="">Tüm Markalar</option>';
            
            // Load brands regardless of category selection
            if (currentCategoryId) {
                loadBrands(currentCategoryId);
            } else {
                // Load all brands when 'All Categories' is selected
                loadAllBrands();
            }
            
            loadProducts();
        });
        
        brandFilter.addEventListener('change', function() {
            currentBrandId = this.value;
            currentPage = 1;
            loadProducts();
        });
        
        document.querySelectorAll('.sortable').forEach(header => {
            header.addEventListener('click', function() {
                const sortField = this.dataset.sort;
                
                if (currentSort === sortField) {
                    // Toggle sort direction
                    currentSortDir = currentSortDir === 'ASC' ? 'DESC' : 'ASC';
                } else {
                    currentSort = sortField;
                    currentSortDir = 'ASC';
                }
                
                // Update sort indicators
                document.querySelectorAll('.sortable i').forEach(icon => {
                    icon.className = 'fas fa-sort';
                });
                
                const newIcon = currentSortDir === 'ASC' ? 'fa-sort-up' : 'fa-sort-down';
                this.querySelector('i').className = `fas ${newIcon}`;
                
                // Reload products with new sort
                loadProducts();
            });
        });
        
        // Modal event listeners
        modalClose.addEventListener('click', closeProductModal);
        deleteModalClose.addEventListener('click', closeDeleteModal);
        deleteCancelBtn.addEventListener('click', closeDeleteModal);
        
        // Add event listener to close modals when clicking on overlay
        productModal.addEventListener('click', function(e) {
            if (e.target === productModal) {
                closeProductModal();
            }
        });
        
        deleteConfirmModal.addEventListener('click', function(e) {
            if (e.target === deleteConfirmModal) {
                closeDeleteModal();
            }
        });
        
        // Add event listener to close modals with ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                if (productModal.classList.contains('active')) {
                    closeProductModal();
                }
                if (deleteConfirmModal.classList.contains('active')) {
                    closeDeleteModal();
                }
            }
        });
        
        modalEditBtn.addEventListener('click', function() {
            if (selectedProductId) {
                window.location.href = `add_product.php?id=${selectedProductId}`;
            }
        });
        
        modalDeleteBtn.addEventListener('click', function() {
            if (selectedProductId) {
                openDeleteModal(selectedProductId);
            }
        });
        
        deleteConfirmBtn.addEventListener('click', function() {
            if (selectedProductId) {
                deleteProduct(selectedProductId);
            }
        });
        
        // Add event listeners for stat cards
        document.querySelectorAll('.stat-card.clickable').forEach(card => {
            card.addEventListener('click', function() {
                const filterType = this.dataset.filter;
                
                // Remove active class from all cards
                document.querySelectorAll('.stat-card.clickable').forEach(c => {
                    c.classList.remove('active');
                });
                
                // Reset filters if clicking already active filter
                if (currentStockFilter === filterType && filterType !== 'all') {
                    currentStockFilter = '';
                    resetFilters();
                    loadProducts();
                    return;
                }
                
                // Add active class to clicked card
                this.classList.add('active');
                
                // Set the current filter
                currentStockFilter = filterType;
                
                // Apply the appropriate filter
                applyStockFilter(filterType);
            });
        });
        
        // Functions
        function loadProducts() {
            const params = new URLSearchParams({
                page: currentPage,
                limit: currentLimit,
                sort_by: currentSort,
                sort_dir: currentSortDir
            });
            
            if (currentSearch) params.append('search', currentSearch);
            if (currentCategoryId) params.append('category_id', currentCategoryId);
            if (currentBrandId) params.append('brand_id', currentBrandId);
            
            fetch(`get_products.php?${params.toString()}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderProducts(data.products);
                        renderPagination(data.pagination);
                    } else {
                        productTableBody.innerHTML = `<tr><td colspan="7" class="text-center">${data.message || 'Ürünleri yüklerken hata oluştu'}</td></tr>`;
                    }
                })
                .catch(error => {
                    console.error('Error loading products:', error);
                    productTableBody.innerHTML = '<tr><td colspan="7" class="text-center">Ürünleri yüklerken hata oluştu</td></tr>';
                });
        }
        
        function loadCategories() {
            fetch('get_categories.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        categoryFilter.innerHTML = '<option value="">Tüm Kategoriler</option>';
                        data.categories.forEach(category => {
                            const option = document.createElement('option');
                            option.value = category.id;
                            option.textContent = category.name;
                            categoryFilter.appendChild(option);
                        });
                    }
                })
                .catch(error => console.error('Error loading categories:', error));
        }
        
        function loadBrands(categoryId) {
            fetch(`get_brands.php?category_id=${categoryId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        brandFilter.innerHTML = '<option value="">Tüm Markalar</option>';
                        data.brands.forEach(brand => {
                            const option = document.createElement('option');
                            option.value = brand.id;
                            option.textContent = brand.name;
                            brandFilter.appendChild(option);
                        });
                        brandFilter.disabled = false;
                    }
                })
                .catch(error => console.error('Error loading brands:', error));
        }
        
        function loadAllBrands() {
            fetch('get_brands.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        brandFilter.innerHTML = '<option value="">Tüm Markalar</option>';
                        data.brands.forEach(brand => {
                            const option = document.createElement('option');
                            option.value = brand.id;
                            option.textContent = brand.name;
                            brandFilter.appendChild(option);
                        });
                        brandFilter.disabled = false;
                    }
                })
                .catch(error => console.error('Error loading brands:', error));
        }
        
        function renderProducts(products) {
            if (products.length === 0) {
                productTableBody.innerHTML = '<tr><td colspan="7" class="text-center">Ürün bulunamadı</td></tr>';
                return;
            }
            
            productTableBody.innerHTML = '';
            
            products.forEach(product => {
                const row = document.createElement('tr');
                // Only add low-stock-row class if stock is greater than 0 but low
                if (product.stock > 0 && product.stock <= LOW_STOCK_THRESHOLD) {
                    row.classList.add('low-stock-row');
                } else if (product.stock === 0) {
                    row.classList.add('out-of-stock-row');
                }
                
                // Make sure the stock display properly indicates the stock status
                let stockDisplay = product.stock;
                if (product.stock === 0) {
                    stockDisplay = `<span class="out-of-stock">${product.stock} <span class="badge badge-danger">Stokta Yok</span></span>`;
                } else if (product.stock <= LOW_STOCK_THRESHOLD) {
                    stockDisplay = `<span class="low-stock">${product.stock} <span class="badge badge-warning">Az Stok</span></span>`;
                }
                
                row.innerHTML = `
                    <td class="text-center">
                        <img src="${product.thumbnail || 'https://via.placeholder.com/60'}" 
                             alt="${escapeHtml(product.name)}" 
                             class="product-image product-clickable" data-id="${product.id}">
                    </td>
                    <td class="product-clickable text-center" data-id="${product.id}">
                        ${escapeHtml(product.name)}
                    </td>
                    <td class="text-center">${escapeHtml(product.category_name)}</td>
                    <td class="text-center">${escapeHtml(product.brand_name)}</td>
                    <td class="text-center">
                        <span class="editable" data-field="price" data-id="${product.id}">
                            ₺${parseFloat(product.price).toFixed(2)}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="editable ${product.stock === 0 ? 'out-of-stock' : (product.stock <= LOW_STOCK_THRESHOLD ? 'low-stock' : '')}" 
                              data-field="stock" 
                              data-id="${product.id}">
                            ${stockDisplay}
                        </span>
                    </td>
                    <td class="text-center">
                        <div class="action-buttons">
                            <button type="button" class="btn btn-sm btn-secondary view-btn" data-id="${product.id}" title="Görüntüle">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button type="button" class="btn btn-sm ${product.stock <= 0 ? 'btn-secondary' : 'btn-primary'} sell-btn" 
                                    data-id="${product.id}" 
                                    ${product.stock <= 0 ? 'disabled' : ''}
                                    title="${product.stock <= 0 ? 'Stokta Yok' : 'Sat'}">
                                <i class="fas fa-shopping-cart"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="${product.id}" title="Sil">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                `;
                
                productTableBody.appendChild(row);
            });
            
            attachProductEventListeners();
        }
        
        function renderPagination(pagination) {
            totalPages = pagination.total_pages;
            paginationInfo.textContent = `Sayfa ${pagination.page} / ${pagination.total_pages} (Toplam ${pagination.total_products} ürün)`;
            paginationContainer.innerHTML = '';
            
            // Add prev button
            const prevLi = document.createElement('li');
            prevLi.className = 'pagination-item';
            const prevLink = document.createElement('a');
            prevLink.className = 'pagination-link';
            prevLink.href = '#';
            prevLink.innerHTML = '<i class="fas fa-chevron-left"></i>';
            prevLink.addEventListener('click', function(e) {
                e.preventDefault();
                if (currentPage > 1) {
                    currentPage--;
                    loadProducts();
                }
            });
            prevLi.appendChild(prevLink);
            paginationContainer.appendChild(prevLi);
            
            // Page links
            let startPage = Math.max(1, currentPage - 2);
            let endPage = Math.min(totalPages, startPage + 4);
            
            if (endPage - startPage < 4 && startPage > 1) {
                startPage = Math.max(1, endPage - 4);
            }
            
            for (let i = startPage; i <= endPage; i++) {
                const pageLi = document.createElement('li');
                pageLi.className = 'pagination-item';
                const pageLink = document.createElement('a');
                pageLink.className = i === currentPage ? 'pagination-link active' : 'pagination-link';
                pageLink.href = '#';
                pageLink.textContent = i;
                pageLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    currentPage = i;
                    loadProducts();
                });
                pageLi.appendChild(pageLink);
                paginationContainer.appendChild(pageLi);
            }
            
            // Add next button
            const nextLi = document.createElement('li');
            nextLi.className = 'pagination-item';
            const nextLink = document.createElement('a');
            nextLink.className = 'pagination-link';
            nextLink.href = '#';
            nextLink.innerHTML = '<i class="fas fa-chevron-right"></i>';
            nextLink.addEventListener('click', function(e) {
                e.preventDefault();
                if (currentPage < totalPages) {
                    currentPage++;
                    loadProducts();
                }
            });
            nextLi.appendChild(nextLink);
            paginationContainer.appendChild(nextLi);
        }
        
        function attachProductEventListeners() {
            // Make product image and name clickable to view details
            document.querySelectorAll('.product-clickable').forEach(element => {
                element.addEventListener('click', function() {
                    const productId = this.dataset.id;
                    viewProduct(productId);
                });
            });
            
            // View buttons
            document.querySelectorAll('.view-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const productId = this.dataset.id;
                    viewProduct(productId);
                });
            });
            
            // Sell buttons - only enabled for products with stock
            document.querySelectorAll('.sell-btn').forEach(button => {
                button.addEventListener('click', function() {
                    if (!this.disabled) {
                        const productId = this.dataset.id;
                        sellProduct(productId);
                    }
                });
            });
            
            // Delete buttons
            document.querySelectorAll('.delete-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const productId = this.dataset.id;
                    openDeleteModal(productId);
                });
            });
            
            // Editable fields
            document.querySelectorAll('.editable').forEach(editable => {
                editable.addEventListener('click', function(event) {
                    // Only proceed if not already editing and if clicking on the field itself, not a badge
                    if (document.querySelector('.inline-edit-container')) return;
                    if (event.target.classList.contains('badge')) return;
                    
                    const field = this.dataset.field;
                    const productId = this.dataset.id;
                    let value = this.textContent.trim();
                    
                    // Remove any status text and currency symbol
                    if (field === 'price') {
                        value = value.replace('₺', '').trim();
                    } else if (field === 'stock') {
                        value = value.replace(/Stokta Yok|Az Stok/g, '').trim();
                    }
                    
                    // Create inline edit container
                    const container = document.createElement('div');
                    container.className = 'inline-edit-container';
                    
                    // Create the form
                    const form = document.createElement('div');
                    form.className = 'inline-edit-form';
                    
                    // Add title and field
                    const title = field === 'price' ? 'Fiyat Güncelleme' : 'Stok Güncelleme';
                    const placeholder = field === 'price' ? 'Yeni fiyat' : 'Yeni stok adedi';
                    const fieldDescription = field === 'price' ? 'Ürün fiyatını buradan güncelleyebilirsiniz.' : 'Ürün stok adedini buradan güncelleyebilirsiniz.';
                    
                    // Build the form content
                    form.innerHTML = `
                        <div class="form-group">
                            <label>${title}</label>
                            <small style="display: block; margin-bottom: 8px; color: #666;">${fieldDescription}</small>
                            <input type="number" id="edit-${field}" 
                                   value="${value}" 
                                   step="${field === 'price' ? '0.01' : '1'}"
                                   min="${field === 'price' ? '0.01' : '0'}"
                                   placeholder="${placeholder}">
                        </div>
                        <div class="inline-edit-buttons">
                            <button type="button" class="cancel-btn">İptal</button>
                            <button type="button" class="confirm-btn">Kaydet</button>
                        </div>
                    `;
                    
                    // Add to DOM
                    this.classList.add('editable-active');
                    container.appendChild(form);
                    this.appendChild(container);
                    
                    // Get the input and buttons
                    const input = form.querySelector('input');
                    const cancelBtn = form.querySelector('.cancel-btn');
                    const confirmBtn = form.querySelector('.confirm-btn');
                    
                    // Focus and select all text after a short delay
                    setTimeout(() => {
                        input.focus();
                        input.select();
                    }, 50);
                    
                    // Handle cancel button
                    cancelBtn.addEventListener('click', function() {
                        container.remove();
                        editable.classList.remove('editable-active');
                    });
                    
                    // Handle confirm button
                    confirmBtn.addEventListener('click', function() {
                        saveEditableField(input, field, productId, container, editable);
                    });
                    
                    // Handle Enter key
                    input.addEventListener('keypress', function(e) {
                        if (e.key === 'Enter') {
                            saveEditableField(input, field, productId, container, editable);
                        }
                    });
                    
                    // Handle Escape key
                    input.addEventListener('keyup', function(e) {
                        if (e.key === 'Escape') {
                            container.remove();
                            editable.classList.remove('editable-active');
                        }
                    });
                    
                    // Stop propagation to prevent body click handler from immediately closing
                    container.addEventListener('click', function(e) {
                        e.stopPropagation();
                    });
                    
                    // Close when clicking outside
                    const closeOnClickOutside = function(e) {
                        // If click is outside our container and our editable field
                        if (!container.contains(e.target) && !editable.contains(e.target) || e.target === editable) {
                            container.remove();
                            editable.classList.remove('editable-active');
                            document.removeEventListener('click', closeOnClickOutside);
                        }
                    };
                    
                    // Add the click listener with a small delay to prevent immediate trigger
                    setTimeout(() => {
                        document.addEventListener('click', closeOnClickOutside);
                    }, 100);
                });
            });
        }
        
        function saveEditableField(input, field, productId, container, editable) {
            const value = input.value.trim();
            
            // Validate input
            if (field === 'price' && (isNaN(value) || parseFloat(value) <= 0)) {
                showNotification('Fiyat sıfırdan büyük olmalı', 'error');
                input.focus();
                return;
            }
            
            if (field === 'stock' && (isNaN(value) || parseInt(value) < 0)) {
                showNotification('Stok negatif olamaz', 'error');
                input.focus();
                return;
            }
            
            // Show loading state
            const confirmBtn = container.querySelector('.confirm-btn');
            const cancelBtn = container.querySelector('.cancel-btn');
            
            input.disabled = true;
            confirmBtn.disabled = true;
            cancelBtn.disabled = true;
            confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            
            // Prepare form data
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append(field, value);
            formData.append('csrf_token', CSRF_TOKEN);
            
            // Send update request
            fetch('update_product_field.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update display value
                    if (field === 'price') {
                        editable.textContent = '₺' + parseFloat(value).toFixed(2);
                    } else if (field === 'stock') {
                        const stockNum = parseInt(value);
                        
                        // Update stock display with appropriate indicator
                        if (stockNum === 0) {
                            editable.innerHTML = `${stockNum} <span class="badge badge-danger">Stokta Yok</span>`;
                            editable.classList.remove('low-stock');
                            editable.classList.add('out-of-stock');
                            editable.closest('tr').classList.remove('low-stock-row');
                            editable.closest('tr').classList.add('out-of-stock-row');
                        } else if (stockNum > 0 && stockNum <= LOW_STOCK_THRESHOLD) {
                            editable.innerHTML = `${stockNum} <span class="badge badge-warning">Az Stok</span>`;
                            editable.classList.add('low-stock');
                            editable.classList.remove('out-of-stock');
                            editable.closest('tr').classList.add('low-stock-row');
                            editable.closest('tr').classList.remove('out-of-stock-row');
                        } else {
                            editable.textContent = stockNum;
                            editable.classList.remove('low-stock', 'out-of-stock');
                            editable.closest('tr').classList.remove('low-stock-row', 'out-of-stock-row');
                        }
                        
                        // Update sell button state
                        const sellBtn = editable.closest('tr').querySelector('.sell-btn');
                        if (sellBtn) {
                            if (stockNum <= 0) {
                                sellBtn.disabled = true;
                                sellBtn.classList.remove('btn-primary');
                                sellBtn.classList.add('btn-secondary');
                            } else {
                                sellBtn.disabled = false;
                                sellBtn.classList.add('btn-primary');
                                sellBtn.classList.remove('btn-secondary');
                            }
                        }
                        
                        // Update stock counters if stock status changed
                        updateStockCounters();
                    }
                    
                    editable.classList.remove('editable-active');
                    showNotification('Ürün başarıyla güncellendi', 'success');
                } else {
                    showNotification(data.message || 'Ürün güncellenirken hata oluştu', 'error');
                }
            })
            .catch(error => {
                console.error('Error updating product:', error);
                showNotification('Ürün güncellenirken hata oluştu', 'error');
            })
            .finally(() => {
                // Remove container
                container.remove();
            });
        }
        
        function viewProduct(productId) {
            fetch(`get_products.php?product_id=${productId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.products.length > 0) {
                        const product = data.products[0];
                        selectedProductId = productId; // Set the selected product ID
                        
                        // Populate modal with product details
                        modalProductImage.src = product.thumbnail || 'https://via.placeholder.com/300';
                        modalProductImage.alt = product.name;
                        modalProductName.textContent = product.name;
                        
                        // Update each field individually, don't use modalProductInfo which doesn't exist
                        modalProductCategory.textContent = product.category_name;
                        modalProductBrand.textContent = product.brand_name;
                        modalProductPrice.textContent = parseFloat(product.price).toFixed(2);
                        
                        // Set stock with appropriate styling
                        const stockSpan = document.getElementById('modal-product-stock');
                        stockSpan.textContent = product.stock;
                        if (product.stock <= LOW_STOCK_THRESHOLD) {
                            stockSpan.className = 'low-stock';
                            if (product.stock <= 0) {
                                stockSpan.innerHTML += ' <span class="badge badge-danger">Stokta Yok</span>';
                            } else {
                                stockSpan.innerHTML += ' <span class="badge badge-warning">Az Stok</span>';
                            }
                        } else {
                            stockSpan.className = '';
                        }
                        
                        // Format dates if available
                        if (product.created_at) {
                            modalProductCreated.textContent = new Date(product.created_at).toLocaleString();
                        } else {
                            modalProductCreated.textContent = 'Bilinmiyor';
                        }
                        
                        if (product.updated_at) {
                            modalProductUpdated.textContent = new Date(product.updated_at).toLocaleString();
                        } else {
                            modalProductUpdated.textContent = 'Bilinmiyor';
                        }
                        
                        // Show modal
                        productModal.classList.add('active');
                    } else {
                        showNotification('Ürün detayları yüklenirken hata oluştu', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error loading product details:', error);
                    showNotification('Ürün detayları yüklenirken hata oluştu', 'error');
                });
        }
        
        function sellProduct(productId) {
            // Prepare form data
            const formData = new FormData();
            formData.append('product_id', productId);
            
            // Disable all sell buttons temporarily
            document.querySelectorAll('.sell-btn').forEach(btn => {
                btn.disabled = true;
            });
            
            // Send sell request
            fetch('sell_product.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Find the stock display for this product
                    const stockElement = document.querySelector(`.editable[data-field="stock"][data-id="${productId}"]`);
                    if (stockElement) {
                        // Check previous state
                        const wasOutOfStock = stockElement.classList.contains('out-of-stock');
                        
                        // Update stock display
                        if (data.new_stock <= 0) {
                            stockElement.innerHTML = `${data.new_stock} <span class="badge badge-danger">Stokta Yok</span>`;
                            stockElement.classList.remove('low-stock');
                            stockElement.classList.add('out-of-stock');
                            stockElement.closest('tr').classList.remove('low-stock-row');
                            stockElement.closest('tr').classList.add('out-of-stock-row');
                        } else if (data.new_stock <= LOW_STOCK_THRESHOLD) {
                            stockElement.innerHTML = `${data.new_stock} <span class="badge badge-warning">Az Stok</span>`;
                            stockElement.classList.add('low-stock');
                            stockElement.classList.remove('out-of-stock');
                            stockElement.closest('tr').classList.add('low-stock-row');
                            stockElement.closest('tr').classList.remove('out-of-stock-row');
                        } else {
                            stockElement.textContent = data.new_stock;
                            stockElement.classList.remove('low-stock', 'out-of-stock');
                            stockElement.closest('tr').classList.remove('low-stock-row', 'out-of-stock-row');
                        }
                    }
                    
                    // Disable sell button if out of stock
                    const sellBtn = document.querySelector(`.sell-btn[data-id="${productId}"]`);
                    if (sellBtn && data.new_stock <= 0) {
                        sellBtn.disabled = true;
                        sellBtn.classList.remove('btn-primary');
                        sellBtn.classList.add('btn-secondary');
                    }
                    
                    // Update recent sales in localStorage for selling interface
                    updateRecentSalesInLocalStorage(productId);
                    
                    // Update stock counters
                    updateStockCounters();
                    
                    showNotification(data.message, 'success');
                } else {
                    showNotification(data.message || 'Ürün satılırken hata oluştu', 'error');
                }
            })
            .catch(error => {
                console.error('Error selling product:', error);
                showNotification('Ürün satılırken hata oluştu', 'error');
            })
            .finally(() => {
                // Re-enable sell buttons for products with stock
                document.querySelectorAll('.sell-btn').forEach(btn => {
                    const productId = btn.dataset.id;
                    const stockElement = document.querySelector(`.editable[data-field="stock"][data-id="${productId}"]`);
                    if (stockElement && parseInt(stockElement.textContent) > 0) {
                        btn.disabled = false;
                        btn.classList.add('btn-primary');
                        btn.classList.remove('btn-secondary');
                    }
                });
            });
        }
        
        // Function to update recent sales in localStorage for the selling interface
        function updateRecentSalesInLocalStorage(productId) {
            // Fetch product details first
            fetch(`get_products.php?product_id=${productId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.products.length > 0) {
                        const product = data.products[0];
                        
                        // Get current recent sales from localStorage
                        let recentSales = [];
                        try {
                            const savedSales = localStorage.getItem('recentSales');
                            if (savedSales) {
                                recentSales = JSON.parse(savedSales);
                            }
                        } catch (error) {
                            console.error('Error loading recent sales:', error);
                        }
                        
                        // Create new sale item
                        const sale = {
                            id: product.id,
                            name: product.name,
                            thumbnail: product.thumbnail,
                            price: product.price,
                            category: product.category_name,
                            brand: product.brand_name,
                            timestamp: new Date().toISOString()
                        };
                        
                        // Add to beginning of array
                        recentSales.unshift(sale);
                        
                        // Limit to 5 items
                        if (recentSales.length > 5) {
                            recentSales = recentSales.slice(0, 5);
                        }
                        
                        // Save back to localStorage
                        try {
                            localStorage.setItem('recentSales', JSON.stringify(recentSales));
                        } catch (error) {
                            console.error('Error saving recent sales:', error);
                        }
                    }
                })
                .catch(error => {
                    console.error('Error fetching product details for recent sales:', error);
                });
        }
        
        function openDeleteModal(productId) {
            // Get product name
            const productRow = document.querySelector(`.delete-btn[data-id="${productId}"]`).closest('tr');
            const productName = productRow.querySelector('td:nth-child(4)').textContent;
            
            // Populate delete modal
            deleteProductName.textContent = productName;
            selectedProductId = productId;
            
            // Show delete modal
            deleteConfirmModal.classList.add('active');
        }
        
        function deleteProduct(productId) {
            // Prepare form data
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('csrf_token', CSRF_TOKEN);
            
            // Get the product row to check its stock status before deletion
            const productRow = document.querySelector(`.delete-btn[data-id="${productId}"]`).closest('tr');
            const stockCell = productRow.querySelector('[data-field="stock"]');
            const isOutOfStock = stockCell.classList.contains('out-of-stock');
            const isLowStock = stockCell.classList.contains('low-stock') && !isOutOfStock;
            
            // Send delete request
            fetch('delete_product.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove product row from the table
                    productRow.remove();
                    
                    // Close modal
                    closeDeleteModal();
                    
                    // Update stock counters
                    updateStockCounters();
                    
                    // Reload products if table is now empty
                    if (productTableBody.children.length === 0) {
                        loadProducts();
                    }
                    
                    showNotification(data.message, 'success');
                } else {
                    showNotification(data.message || 'Ürün silinirken hata oluştu', 'error');
                }
            })
            .catch(error => {
                console.error('Error deleting product:', error);
                showNotification('Ürün silinirken hata oluştu', 'error');
            });
        }
        
        // Function to update the stock counters
        function updateStockCounters() {
            // Fetch updated counts from the server
            fetch('get_stock_counts.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update the counters in the DOM
                        document.querySelector('.stat-card[data-filter="all"] .stat-value').textContent = data.totalProducts;
                        document.querySelector('.stat-card[data-filter="low-stock"] .stat-value').textContent = data.lowStockCount;
                        document.querySelector('.stat-card[data-filter="out-of-stock"] .stat-value').textContent = data.outOfStockCount;
                    }
                })
                .catch(error => {
                    console.error('Error updating stock counters:', error);
                });
        }
        
        function closeProductModal() {
            productModal.classList.remove('active');
            selectedProductId = null;
        }
        
        function closeDeleteModal() {
            deleteConfirmModal.classList.remove('active');
        }
        
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
        
        // Helper function: Debounce
        function debounce(func, wait) {
            let timeout;
            return function() {
                const context = this;
                const args = arguments;
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(context, args), wait);
            };
        }
        
        // Function to apply stock filter
        function applyStockFilter(filterType) {
            // Reset other filters first
            currentSearch = '';
            searchInput.value = '';
            currentPage = 1;
            
            // Apply specific filters based on type
            switch (filterType) {
                case 'low-stock':
                    // Add a custom parameter for the low stock filter
                    loadProductsWithStockFilter('low');
                    showNotification('Az stoklu ürünler gösteriliyor', 'info');
                    break;
                    
                case 'out-of-stock':
                    // Add a custom parameter for the out of stock filter
                    loadProductsWithStockFilter('zero');
                    showNotification('Stokta olmayan ürünler gösteriliyor', 'info');
                    break;
                    
                case 'all':
                    // Reset filters and show all products
                    resetFilters();
                    loadProducts();
                    showNotification('Tüm ürünler gösteriliyor', 'info');
                    break;
                    
                case 'categories':
                    // Show categories dropdown as active
                    categoryFilter.classList.add('filter-active');
                    setTimeout(() => categoryFilter.classList.remove('filter-active'), 3000);
                    showNotification('Lütfen bir kategori seçin', 'info');
                    break;
                    
                case 'brands':
                    // Show brands dropdown as active
                    brandFilter.classList.add('filter-active');
                    setTimeout(() => brandFilter.classList.remove('filter-active'), 3000);
                    showNotification('Lütfen bir marka seçin', 'info');
                    break;
            }
        }
        
        // Function to load products with stock filter
        function loadProductsWithStockFilter(stockFilter) {
            const params = new URLSearchParams({
                page: currentPage,
                limit: currentLimit,
                sort_by: currentSort,
                sort_dir: currentSortDir,
                stock_filter: stockFilter // New parameter for stock filtering
            });
            
            if (currentCategoryId) params.append('category_id', currentCategoryId);
            if (currentBrandId) params.append('brand_id', currentBrandId);
            
            fetch(`get_products.php?${params.toString()}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderProducts(data.products);
                        renderPagination(data.pagination);
                    } else {
                        productTableBody.innerHTML = `<tr><td colspan="7" class="text-center">${data.message || 'Ürünleri yüklerken hata oluştu'}</td></tr>`;
                    }
                })
                .catch(error => {
                    console.error('Error loading products:', error);
                    productTableBody.innerHTML = '<tr><td colspan="7" class="text-center">Ürünleri yüklerken hata oluştu</td></tr>';
                });
        }
        
        // Function to reset all filters
        function resetFilters() {
            currentStockFilter = '';
            currentSearch = '';
            currentCategoryId = '';
            currentBrandId = '';
            searchInput.value = '';
            categoryFilter.value = '';
            brandFilter.value = '';
            
            // Remove active class from all stat cards
            document.querySelectorAll('.stat-card.clickable').forEach(card => {
                card.classList.remove('active');
            });
        }
    });
    </script>
    <script src="animations.js"></script>
    <script src="header_animation.js"></script>
</body>
</html> 