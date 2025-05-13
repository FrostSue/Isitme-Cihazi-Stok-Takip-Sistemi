<?php
// Include session check to prevent unauthorized access
require_once 'check_session.php';
require_once 'db_connection.php';

$product = null;
$error = '';
$success = '';

// Check if editing an existing product
$edit_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    $pdo = getProductConnection();
    
    // If form submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get form data
        $category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
        $brand_id = isset($_POST['brand_id']) ? (int)$_POST['brand_id'] : 0;
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $stock = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;
        $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
        $thumbnail = isset($_POST['thumbnail']) ? trim($_POST['thumbnail']) : '';
        $current_thumbnail = isset($_POST['current_thumbnail']) ? trim($_POST['current_thumbnail']) : '';
        
        // Basic validation
        if ($category_id <= 0) {
            $error = 'Lütfen geçerli bir kategori seçin.';
        } elseif ($brand_id <= 0) {
            $error = 'Lütfen geçerli bir marka seçin.';
        } elseif (empty($name)) {
            $error = 'Ürün adı gereklidir.';
        } elseif ($stock < 0) {
            $error = 'Stok negatif olamaz.';
        } elseif ($price <= 0) {
            $error = 'Fiyat sıfırdan büyük olmalıdır.';
        } else {
            // Handle file upload if provided
            if (isset($_FILES['thumbnail_file']) && $_FILES['thumbnail_file']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = 'uploads/';
                
                // Create uploads directory if it doesn't exist
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                // Generate unique filename
                $filename = uniqid() . '_' . basename($_FILES['thumbnail_file']['name']);
                $upload_file = $upload_dir . $filename;
                
                // Move uploaded file
                if (move_uploaded_file($_FILES['thumbnail_file']['tmp_name'], $upload_file)) {
                    $thumbnail = $upload_file;
                } else {
                    $error = 'Dosya yüklenemedi.';
                }
            }
            
            if (empty($error)) {
                if ($edit_id > 0) {
                    // Use current thumbnail if no new one is provided
                    if (empty($thumbnail) && !empty($current_thumbnail)) {
                        $thumbnail = $current_thumbnail;
                    }
                    
                    // Update existing product
                    $stmt = $pdo->prepare("
                        UPDATE products 
                        SET category_id = ?, brand_id = ?, name = ?, stock = ?, price = ?, 
                            thumbnail = CASE WHEN ? = '' THEN thumbnail ELSE ? END
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $category_id, $brand_id, $name, $stock, $price, 
                        $thumbnail, $thumbnail, $edit_id
                    ]);
                    $success = 'Ürün başarıyla güncellendi.';
                } else {
                    // Add new product
                    $stmt = $pdo->prepare("
                        INSERT INTO products (category_id, brand_id, name, stock, price, thumbnail)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$category_id, $brand_id, $name, $stock, $price, $thumbnail]);
                    $success = 'Ürün başarıyla eklendi.';
                }
                
                // Redirect to product list after short delay
                header("Refresh: 1; URL=product_list.php");
            }
        }
    }
    
    // If editing, fetch the product details
    if ($edit_id > 0) {
        $stmt = $pdo->prepare("
            SELECT p.*, c.name AS category_name, b.name AS brand_name
            FROM products p
            JOIN categories c ON p.category_id = c.id
            JOIN brands b ON p.brand_id = b.id
            WHERE p.id = ?
        ");
        $stmt->execute([$edit_id]);
        $product = $stmt->fetch();
        
        if (!$product) {
            $error = 'Ürün bulunamadı.';
        }
    }
    
    // Get all categories for dropdown
    $stmt = $pdo->prepare("SELECT id, name FROM categories ORDER BY name ASC");
    $stmt->execute();
    $categories = $stmt->fetchAll();
    
    // Get all brands for dropdown (removed the category_id filter since brands are independent)
    $brands = [];
    $stmt = $pdo->prepare("SELECT id, name FROM brands ORDER BY name ASC");
    $stmt->execute();
    $brands = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $error = 'Veritabanı hatası oluştu. Lütfen daha sonra tekrar deneyin.';
    error_log('Error in add_product.php: ' . $e->getMessage());
}

// Page title
$page_title = $edit_id > 0 ? 'Ürün Düzenle' : 'Yeni Ürün Ekle';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - İşitme Cihazı Stok Yönetim Paneli</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="animations.css">
    <link rel="stylesheet" href="style.css">
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
    <main class="container" style="padding-top: 2rem;">
        <div class="card">
            <div class="card-header">
                <h2><?= htmlspecialchars($page_title) ?></h2>
                <a href="product_list.php" class="btn btn-primary">
                    <i class="fas fa-list"></i> Ürünlere Dön
                </a>
            </div>
            
            <div class="card-body">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>
                
                <div class="existing-products-section">
                    <h3 class="section-heading">Mevcut Ürünler</h3>
                    <div class="form-group">
                        <div class="input-group">
                            <input type="text" id="search-products" class="form-control" placeholder="Ürün ara...">
                            <button type="button" id="search-btn" class="btn btn-primary btn-sm">
                                <i class="fas fa-search"></i> Ara
                            </button>
                        </div>
                    </div>
                    
                    <div id="search-results" class="table-responsive" style="display: none;">
                        <table class="table table-striped compact-table">
                            <thead>
                                <tr>
                                    <th>Resim</th>
                                    <th>Ürün Adı</th>
                                    <th class="hide-on-small">Kategori</th>
                                    <th class="hide-on-small">Marka</th>
                                    <th>Fiyat</th>
                                    <th>Stok</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody id="product-results-body">
                                <!-- Will be populated via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                    
                    <hr>
                    <h3 class="section-heading">Yeni Ürün Ekle</h3>
                </div>
                
                <form class="compact-form" action="<?= $edit_id > 0 ? "add_product.php?id={$edit_id}" : 'add_product.php' ?>" method="post" enctype="multipart/form-data">
                    <div class="input-row">
                        <div class="input-col">
                            <div class="form-group">
                                <label for="category_id">Kategori:</label>
                                <select id="category_id" name="category_id" class="form-control" required>
                                    <option value="">Kategori Seçiniz</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?= $category['id'] ?>" <?= $product && $product['category_id'] == $category['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($category['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="input-col">
                            <div class="form-group">
                                <label for="brand_id">Marka:</label>
                                <div class="brand-select-wrapper">
                                    <select id="brand_id" name="brand_id" class="form-control" required style="margin-bottom: 5px;">
                                        <option value="">Marka Seçiniz</option>
                                        <?php foreach ($brands as $brand): ?>
                                            <option value="<?= $brand['id'] ?>" <?= $product && $product['brand_id'] == $brand['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($brand['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="brand-actions">
                                        <button type="button" id="add-new-brand" class="btn btn-sm btn-secondary action-btn">
                                            <i class="fas fa-plus"></i> Yeni Marka
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Add new brand form -->
                                <div id="new-brand-container" class="new-brand-form" style="display: none; margin-top: 10px;">
                                    <div class="input-group">
                                        <input type="text" id="new-brand-name" class="form-control" placeholder="Yeni marka adı">
                                        <div class="input-group-append">
                                            <button type="button" id="save-new-brand" class="btn btn-sm btn-success">
                                                <i class="fas fa-save"></i> Kaydet
                                            </button>
                                            <button type="button" id="cancel-new-brand" class="btn btn-sm btn-secondary">
                                                <i class="fas fa-times"></i> İptal
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="name">Ürün Adı:</label>
                        <input type="text" id="name" name="name" class="form-control" value="<?= $product ? htmlspecialchars($product['name']) : '' ?>" required>
                    </div>
                    
                    <div class="input-row">
                        <div class="input-col">
                            <div class="form-group">
                                <label for="price">Fiyat (₺):</label>
                                <input type="number" id="price" name="price" class="form-control" value="<?= $product ? htmlspecialchars($product['price']) : '0.00' ?>" min="0.01" step="0.01" required>
                            </div>
                        </div>
                        
                        <div class="input-col">
                            <div class="form-group">
                                <label for="stock">Stok:</label>
                                <input type="number" id="stock" name="stock" class="form-control" value="<?= $product ? htmlspecialchars($product['stock']) : '0' ?>" min="0" step="1" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="thumbnail_file">Ürün Görseli:</label>
                        <input type="file" id="thumbnail_file" name="thumbnail_file" class="form-control">
                        <small class="form-text text-muted">Mevcut bir görsel seçmezseniz, mevcut görsel korunacaktır.</small>
                    </div>
                    
                    <?php if ($product && !empty($product['thumbnail'])): ?>
                        <div class="form-group">
                            <label>Mevcut Görsel:</label>
                            <div>
                                <input type="hidden" name="current_thumbnail" value="<?= htmlspecialchars($product['thumbnail']) ?>">
                                <?php
                                // Check if it's a full URL or a local path
                                $thumbnailPath = $product['thumbnail'];
                                $isUrl = strpos($thumbnailPath, 'http://') === 0 || strpos($thumbnailPath, 'https://') === 0;
                                
                                if (!$isUrl && file_exists($thumbnailPath)) {
                                    // Local file exists, display it directly
                                    echo '<img src="' . htmlspecialchars($thumbnailPath) . '" alt="' . htmlspecialchars($product['name']) . '" class="thumbnail" style="width: 100px; height: 100px;">';
                                } elseif ($isUrl) {
                                    // External URL, try to display
                                    echo '<img src="' . htmlspecialchars($thumbnailPath) . '" alt="' . htmlspecialchars($product['name']) . '" class="thumbnail" style="width: 100px; height: 100px;" onerror="this.src=\'https://via.placeholder.com/100?text=Resim+Yok\'">';
                                } else {
                                    // File doesn't exist, show placeholder
                                    echo '<img src="https://via.placeholder.com/100?text=Resim+Yok" alt="Resim bulunamadı" class="thumbnail" style="width: 100px; height: 100px;">';
                                    echo '<p class="text-warning"><small>Mevcut görsele erişilemiyor: ' . htmlspecialchars($thumbnailPath) . '</small></p>';
                                }
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="thumbnail">Görsel URL (Opsiyonel):</label>
                        <input type="url" id="thumbnail" name="thumbnail" class="form-control" placeholder="Yeni bir URL girerseniz mevcut görsel değişecektir">
                        <small class="form-text text-muted">Dosya yüklemek yerine bir URL girebilirsiniz. Mevcut görseli korumak için boş bırakın.</small>
                    </div>
                    
                    <div class="form-group" style="margin-top: 1.5rem;">
                        <div class="button-container" style="display: flex; gap: 15px;">
                            <div class="button-box" style="flex: 1; background-color: #f8f9fa; border-radius: 10px; padding: 15px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                                <button type="submit" class="btn btn-primary animated-btn" style="width: 100%; padding: 0.7rem 1rem; background-color: #3498db; border-color: #3498db; font-weight: 600;">
                                    <i class="fas fa-save"></i> Kaydet
                                </button>
                                <div style="margin-top: 8px; font-size: 0.8rem; color: #666;">
                                    Tüm değişiklikleri kaydet
                                </div>
                            </div>
                            
                            <div class="button-box" style="flex: 1; background-color: #f8f9fa; border-radius: 10px; padding: 15px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
                                <a href="product_list.php" class="btn animated-btn" style="width: 100%; padding: 0.7rem 1rem; background-color: #e74c3c; border-color: #e74c3c; color: white; font-weight: 600;">
                                    <i class="fas fa-times"></i> İptal
                                </a>
                                <div style="margin-top: 8px; font-size: 0.8rem; color: #666;">
                                    Değişiklikleri iptal et
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const categorySelect = document.getElementById('category_id');
        const brandSelect = document.getElementById('brand_id');
        const addNewBrandBtn = document.getElementById('add-new-brand');
        const newBrandContainer = document.getElementById('new-brand-container');
        const newBrandName = document.getElementById('new-brand-name');
        const saveNewBrandBtn = document.getElementById('save-new-brand');
        const cancelNewBrandBtn = document.getElementById('cancel-new-brand');
        
        // Load brands when category changes
        categorySelect.addEventListener('change', function() {
            const categoryId = this.value;
            
            // Clear brand dropdown
            brandSelect.innerHTML = '<option value="">Marka Seçiniz</option>';
            
            // Disable the dropdown while loading
            brandSelect.disabled = true;
            addNewBrandBtn.disabled = true;
            
            if (!categoryId) {
                showNotification('Lütfen bir kategori seçiniz', 'warning');
                return;
            }
            
            // Hide new brand form
            if (newBrandContainer) {
                newBrandContainer.style.display = 'none';
            }
            
            // Show loading indicator
            brandSelect.innerHTML = '<option value="">Markalar yükleniyor...</option>';
            
            // Fetch brands for the selected category
            fetch(`get_brands.php?category_id=${categoryId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    // Clear loading message
                    brandSelect.innerHTML = '<option value="">Marka Seçiniz</option>';
                    
                    if (data.success) {
                        // Populate brand dropdown
                        if (data.brands && data.brands.length > 0) {
                            data.brands.forEach(brand => {
                                const option = document.createElement('option');
                                option.value = brand.id;
                                option.textContent = brand.name;
                                brandSelect.appendChild(option);
                            });
                            
                            // Enable the dropdown
                            brandSelect.disabled = false;
                            addNewBrandBtn.disabled = false;
                        } else {
                            brandSelect.innerHTML = '<option value="">Bu kategoride marka bulunamadı</option>';
                            addNewBrandBtn.disabled = false;
                        }
                    } else {
                        showNotification('Markalar yüklenirken hata oluştu', 'error');
                        brandSelect.innerHTML = '<option value="">Markalar yüklenemedi</option>';
                    }
                })
                .catch(error => {
                    console.error('Error loading brands:', error);
                    brandSelect.innerHTML = '<option value="">Markalar yüklenemedi</option>';
                    showNotification('Markalar yüklenirken hata oluştu', 'error');
                })
                .finally(() => {
                    // Always make sure the dropdown is enabled after the request completes
                    brandSelect.disabled = false;
                    addNewBrandBtn.disabled = false;
                });
        });
        
        // Show form to add new brand
        addNewBrandBtn.addEventListener('click', function() {
            if (!categorySelect.value) {
                showNotification('Önce bir kategori seçmelisiniz', 'warning');
                return;
            }
            
            newBrandContainer.style.display = 'block';
            newBrandName.focus();
        });
        
        // Hide form to add new brand
        cancelNewBrandBtn.addEventListener('click', function() {
            newBrandContainer.style.display = 'none';
            newBrandName.value = '';
        });
        
        // Save new brand
        saveNewBrandBtn.addEventListener('click', function() {
            const brandName = newBrandName.value.trim();
            const categoryId = categorySelect.value;
            
            if (!brandName) {
                showNotification('Marka adı boş olamaz', 'warning');
                return;
            }
            
            if (!categoryId) {
                showNotification('Önce bir kategori seçmelisiniz', 'warning');
                return;
            }
            
            // Disable buttons during save
            saveNewBrandBtn.disabled = true;
            saveNewBrandBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Kaydediliyor...';
            
            // Create form data
            const formData = new FormData();
            formData.append('brand_name', brandName);
            formData.append('category_id', categoryId);
            
            // Send request to save new brand
            fetch('add_brand.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Add new brand to dropdown and select it
                    const option = document.createElement('option');
                    option.value = data.brand_id;
                    option.textContent = data.brand_name;
                    brandSelect.appendChild(option);
                    brandSelect.value = data.brand_id;
                    
                    // Hide form
                    newBrandContainer.style.display = 'none';
                    newBrandName.value = '';
                    
                    showNotification('Yeni marka başarıyla eklendi', 'success');
                } else {
                    showNotification(data.message || 'Marka eklenirken hata oluştu', 'error');
                }
            })
            .catch(error => {
                console.error('Error adding brand:', error);
                showNotification('Marka eklenirken hata oluştu', 'error');
            })
            .finally(() => {
                // Re-enable button
                saveNewBrandBtn.disabled = false;
                saveNewBrandBtn.innerHTML = '<i class="fas fa-save"></i> Kaydet';
            });
        });
        
        // Function to show notifications
        function showNotification(message, type = 'info') {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `alert alert-${type === 'error' ? 'error' : type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'info'}`;
            notification.style.position = 'fixed';
            notification.style.top = '20px';
            notification.style.right = '20px';
            notification.style.zIndex = '1000';
            notification.style.maxWidth = '300px';
            notification.style.boxShadow = '0 4px 8px rgba(0,0,0,0.2)';
            notification.style.transition = 'all 0.3s ease';
            notification.style.transform = 'translateX(400px)';
            
            // Add icon based on type
            const icon = type === 'error' ? 'exclamation-circle' : type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle';
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
    });

    // Product search functionality
    const searchInput = document.getElementById('search-products');
    const searchBtn = document.getElementById('search-btn');
    const searchResults = document.getElementById('search-results');
    const productResultsBody = document.getElementById('product-results-body');

    searchBtn.addEventListener('click', function() {
        const searchTerm = searchInput.value.trim();
        
        if (!searchTerm) {
            showNotification('Lütfen bir arama terimi girin', 'warning');
            return;
        }
        
        // Show loading
        productResultsBody.innerHTML = '<tr><td colspan="7" class="text-center"><i class="fas fa-spinner fa-spin"></i> Yükleniyor...</td></tr>';
        searchResults.style.display = 'block';
        
        // Fetch products
        fetch(`get_products.php?search=${encodeURIComponent(searchTerm)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.products.length > 0) {
                    // Render products
                    renderSearchResults(data.products);
                } else {
                    productResultsBody.innerHTML = '<tr><td colspan="7" class="text-center">Ürün bulunamadı</td></tr>';
                }
            })
            .catch(error => {
                console.error('Error searching products:', error);
                productResultsBody.innerHTML = '<tr><td colspan="7" class="text-center">Ürün aranırken hata oluştu</td></tr>';
                showNotification('Ürün aranırken hata oluştu', 'error');
            });
    });

    // Press Enter to search
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            searchBtn.click();
        }
    });

    // Function to render search results
    function renderSearchResults(products) {
        productResultsBody.innerHTML = '';
        
        products.forEach(product => {
            const row = document.createElement('tr');
            
            // Add stock status classes
            if (product.stock === 0) {
                row.classList.add('out-of-stock-row');
            } else if (product.stock <= <?= LOW_STOCK_THRESHOLD ?>) {
                row.classList.add('low-stock-row');
            }
            
            // Format stock display
            let stockDisplay = product.stock;
            if (product.stock === 0) {
                stockDisplay = `<span class="out-of-stock">${product.stock} <span class="badge badge-danger">Yok</span></span>`;
            } else if (product.stock <= <?= LOW_STOCK_THRESHOLD ?>) {
                stockDisplay = `<span class="low-stock">${product.stock} <span class="badge badge-warning">Az</span></span>`;
            }
            
            row.innerHTML = `
                <td>
                    <img src="${product.thumbnail || 'https://via.placeholder.com/40'}" 
                         alt="${escapeHtml(product.name)}" 
                         class="product-image">
                </td>
                <td>${escapeHtml(product.name)}</td>
                <td class="hide-on-small">${escapeHtml(product.category_name)}</td>
                <td class="hide-on-small">${escapeHtml(product.brand_name)}</td>
                <td>₺${parseFloat(product.price).toFixed(2)}</td>
                <td>${stockDisplay}</td>
                <td>
                    <div class="compact-btn-group">
                        <a href="add_product.php?id=${product.id}" class="btn btn-sm btn-primary compact-btn" title="Düzenle">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-success compact-btn add-stock-btn" data-id="${product.id}" data-name="${escapeHtml(product.name)}" title="Stok Ekle">
                            <i class="fas fa-plus-circle"></i>
                        </button>
                    </div>
                </td>
            `;
            
            productResultsBody.appendChild(row);
        });
        
        // Add event listeners to add-stock buttons
        document.querySelectorAll('.add-stock-btn').forEach(button => {
            button.addEventListener('click', function() {
                const productId = this.dataset.id;
                const productName = this.dataset.name;
                
                // Show stock input dialog
                showAddStockDialog(productId, productName);
            });
        });
    }

    // Function to show add stock dialog
    function showAddStockDialog(productId, productName) {
        // Create modal for adding stock
        const modal = document.createElement('div');
        modal.className = 'modal-overlay active';
        modal.innerHTML = `
            <div class="modal">
                <div class="modal-header">
                    <h3 class="modal-title">Stok Ekle: ${escapeHtml(productName)}</h3>
                    <button type="button" class="modal-close">&times;</button>
                </div>
                <div class="modal-body">
                    <p style="margin-bottom: 15px; font-size: 16px;">Bu ürüne eklemek istediğiniz stok miktarını girin:</p>
                    <div class="form-group">
                        <label for="add-stock-amount">Eklenecek Stok Miktarı:</label>
                        <input type="number" id="add-stock-amount" class="form-control" value="1" min="1" style="text-align: center; font-weight: bold;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary cancel-stock-btn" style="min-width: 100px;">
                        <i class="fas fa-times"></i> İptal
                    </button>
                    <button type="button" class="btn btn-primary confirm-stock-btn" style="min-width: 120px; background-color: var(--secondary-color); border-color: var(--secondary-color);">
                        <i class="fas fa-check"></i> Stok Ekle
                    </button>
                </div>
            </div>
        `;
        
        // Add to DOM
        document.body.appendChild(modal);
        
        // Focus input
        const stockInput = document.getElementById('add-stock-amount');
        stockInput.focus();
        stockInput.select();
        
        // Close button
        const closeBtn = modal.querySelector('.modal-close');
        closeBtn.addEventListener('click', () => modal.remove());
        
        // Cancel button
        const cancelBtn = modal.querySelector('.cancel-stock-btn');
        cancelBtn.addEventListener('click', () => modal.remove());
        
        // Confirm button
        const confirmBtn = modal.querySelector('.confirm-stock-btn');
        confirmBtn.addEventListener('click', function() {
            const amount = parseInt(stockInput.value);
            
            if (isNaN(amount) || amount <= 0) {
                showNotification('Lütfen geçerli bir miktar girin', 'warning');
                return;
            }
            
            // Update stock
            updateProductStock(productId, amount);
            
            // Close modal
            modal.remove();
        });
        
        // Close when clicking outside
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.remove();
            }
        });
        
        // Close when pressing Escape
        document.addEventListener('keydown', function escapeHandler(e) {
            if (e.key === 'Escape') {
                modal.remove();
                document.removeEventListener('keydown', escapeHandler);
            }
        });
        
        // Submit on Enter
        stockInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                confirmBtn.click();
            }
        });
    }

    // Function to update product stock
    function updateProductStock(productId, amount) {
        // Prepare form data
        const formData = new FormData();
        formData.append('product_id', productId);
        formData.append('add_stock', amount);
        formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?>');
        
        // Send update request
        fetch('update_stock.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the stock display in the UI immediately
                updateStockDisplayInTable(productId, data.new_stock);
                
                showNotification(`${amount} adet stok başarıyla eklendi`, 'success');
            } else {
                showNotification(data.message || 'Stok güncellenirken hata oluştu', 'error');
            }
        })
        .catch(error => {
            console.error('Error updating stock:', error);
            showNotification('Stok güncellenirken hata oluştu', 'error');
        });
    }

    // Function to update the stock display in the table
    function updateStockDisplayInTable(productId, newStock) {
        // Find the row with this product ID
        const row = document.querySelector(`.add-stock-btn[data-id="${productId}"]`).closest('tr');
        
        if (!row) return;
        
        // Get the stock cell (6th column, index 5)
        const stockCell = row.cells[5];
        
        // Format the new stock display
        let stockDisplay = newStock;
        if (newStock === 0) {
            stockDisplay = `<span class="out-of-stock">${newStock} <span class="badge badge-danger">Yok</span></span>`;
            row.classList.add('out-of-stock-row');
            row.classList.remove('low-stock-row');
        } else if (newStock <= <?= LOW_STOCK_THRESHOLD ?>) {
            stockDisplay = `<span class="low-stock">${newStock} <span class="badge badge-warning">Az</span></span>`;
            row.classList.add('low-stock-row');
            row.classList.remove('out-of-stock-row');
        } else {
            row.classList.remove('low-stock-row', 'out-of-stock-row');
        }
        
        // Update the cell content
        stockCell.innerHTML = stockDisplay;
        
        // If we're on the edit page for this product, also update the stock input
        const stockInput = document.getElementById('stock');
        if (stockInput && window.location.href.includes(`id=${productId}`)) {
            stockInput.value = newStock;
        }
    }

    // Helper function to escape HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    </script>
    <script src="animations.js"></script>
    <script src="header_animation.js"></script>
</body>
</html> 