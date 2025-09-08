<?php
session_start();

// Check if client is logged in
if (!isset($_SESSION['client']) || $_SESSION['user_type'] !== 'client') {
    header("Location: ../signin.php");
    exit();
}

require_once("../php/Product.php");
require_once("../php/Categorie.php");
require_once("../php/Marque.php");

// Pagination settings
$products_per_page = 12;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$current_page = max(1, $current_page);
$offset = ($current_page - 1) * $products_per_page;

// Get filter parameters
$selected_category = $_GET['category'] ?? '';
$selected_brand = $_GET['brand'] ?? '';
$search_term = $_GET['search'] ?? '';

// Get categories and brands for filter dropdowns
$categories = Categorie::afficher("categorie");
$brands = Marque::afficher("marque");

// Get filtered products with pagination using direct database query
try {
    $pdo = new PDO("mysql:host=localhost;dbname=stock2025", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Build the WHERE clause
    $where_conditions = [];
    $params = [];
    
    if ($selected_category) {
        $where_conditions[] = "p.id_cat = :category";
        $params[':category'] = $selected_category;
    }
    
    if ($selected_brand) {
        $where_conditions[] = "p.id_marque = :brand";
        $params[':brand'] = $selected_brand;
    }
    
    if ($search_term) {
        $where_conditions[] = "p.lib_pr LIKE :search";
        $params[':search'] = '%' . $search_term . '%';
    }
    
    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // Get total count for pagination
    $count_sql = "
        SELECT COUNT(*) as total 
        FROM produit p 
        JOIN categorie c ON p.id_cat = c.id_cat 
        JOIN marque m ON p.id_marque = m.id_marque 
        $where_clause
    ";
    
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total_products = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_products / $products_per_page);
    
    // Get products for current page
    $sql = "
        SELECT p.*, c.lib_cat, m.nom_marque 
        FROM produit p 
        JOIN categorie c ON p.id_cat = c.id_cat 
        JOIN marque m ON p.id_marque = m.id_marque 
        $where_clause
        ORDER BY p.num_pr 
        LIMIT :limit OFFSET :offset
    ";
    
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $products_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    $products = [];
    $total_pages = 0;
    $total_products = 0;
}

$active = array(0, "active", 0, 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Products - Haila Stock</title>
    
    <link rel="shortcut icon" type="image/x-icon" href="../assets/img/fav1.jpg">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/animate.css">
    <link rel="stylesheet" href="../assets/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        .product-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            overflow: hidden;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .product-image {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }
        .product-price {
            font-size: 1.5rem;
            font-weight: bold;
            color: #28a745;
        }
        .stock-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 2;
        }
        .filter-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .add-to-cart-btn {
            width: 100%;
            margin-top: 10px;
        }
        .product-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }
        .product-description {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
        .product-item {
            transition: transform 0.2s ease;
        }
        .product-item:hover {
            transform: translateY(-2px);
        }
        .pagination .page-link {
            border-radius: 5px;
            margin: 0 2px;
            border: 1px solid #dee2e6;
        }
        .pagination .page-item.active .page-link {
            background-color: #007bff;
            border-color: #007bff;
        }
        .view-toggle .btn.active {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }
        .loading-placeholder {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }
        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
    </style>
</head>

<body>
    <div id="global-loader">
        <div class="whirly-loader"></div>
    </div>

    <div class="main-wrapper">
        <?php require_once("header.php"); ?>
        <?php require_once("sidebar.php"); ?>

        <div class="page-wrapper">
            <div class="content">
                <div class="page-header">
                    <div class="add-item d-flex">
                        <div class="page-title">
                            <h4>Products</h4>
                        </div>
                    </div>
                    <ul class="table-top-head">
                        <li>
                            <a href="cart.php" class="btn btn-primary">
                                <i class="fas fa-shopping-cart"></i> View Cart
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Filter Section -->
                <div class="filter-section">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="search" class="form-label">Search Products</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="<?= htmlspecialchars($search_term) ?>" placeholder="Search products...">
                        </div>
                        <div class="col-md-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-control" id="category" name="category">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id_cat'] ?>" 
                                            <?= $selected_category == $category['id_cat'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['lib_cat']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="brand" class="form-label">Brand</label>
                            <select class="form-control" id="brand" name="brand">
                                <option value="">All Brands</option>
                                <?php foreach ($brands as $brand): ?>
                                    <option value="<?= $brand['id_marque'] ?>" 
                                            <?= $selected_brand == $brand['id_marque'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($brand['nom_marque']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="products.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Results Info -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="text-muted">
                            Showing <?= count($products) ?> of <?= $total_products ?> products
                            <?php if ($current_page > 1): ?>
                                (Page <?= $current_page ?> of <?= $total_pages ?>)
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="changeView('grid')" id="gridView">
                                <i class="fas fa-th"></i> Grid
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="changeView('list')" id="listView">
                                <i class="fas fa-list"></i> List
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="row" id="productsContainer">
                    <?php if (empty($products)): ?>
                        <div class="col-12">
                            <div class="alert alert-info text-center">
                                <i class="fas fa-info-circle"></i> No products found matching your criteria.
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($products as $product): ?>
                            <div class="col-lg-3 col-md-4 col-sm-6 mb-4 product-item">
                                <div class="card product-card h-100">
                                    <div class="position-relative">
                                        <img src="../<?= ltrim(htmlspecialchars($product['pr_image']), './') ?>" 
                                             class="product-image" alt="<?= htmlspecialchars($product['lib_pr']) ?>" loading="lazy">
                                        <?php if ($product['qte_stock'] > 0): ?>
                                            <span class="badge bg-success stock-badge">
                                                <i class="fas fa-check"></i> In Stock
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-danger stock-badge">
                                                <i class="fas fa-times"></i> Out of Stock
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="product-title"><?= htmlspecialchars($product['lib_pr']) ?></h5>
                                        <p class="product-description">
                                            <?= htmlspecialchars(substr($product['desc_pr'], 0, 100)) ?>
                                            <?= strlen($product['desc_pr']) > 100 ? '...' : '' ?>
                                        </p>
                                        <div class="mt-auto">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="product-price"><?= number_format($product['prix_uni'], 2) ?> DH</span>
                                                <small class="text-muted">Stock: <?= $product['qte_stock'] ?></small>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <small class="text-muted">
                                                    <i class="fas fa-tag"></i> <?= htmlspecialchars($product['lib_cat']) ?>
                                                </small>
                                                <small class="text-muted">
                                                    <i class="fas fa-industry"></i> <?= htmlspecialchars($product['nom_marque']) ?>
                                                </small>
                                            </div>
                                            <?php if ($product['qte_stock'] > 0): ?>
                                                <button class="btn btn-primary add-to-cart-btn" 
                                                        onclick="addToCart('<?= $product['num_pr'] ?>', '<?= htmlspecialchars($product['lib_pr']) ?>', <?= $product['prix_uni'] ?>, <?= $product['qte_stock'] ?>)">
                                                    <i class="fas fa-cart-plus"></i> Add to Cart
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-secondary add-to-cart-btn" disabled>
                                                    <i class="fas fa-times"></i> Out of Stock
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Products pagination" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <!-- Previous Page -->
                            <?php if ($current_page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $current_page - 1])) ?>">
                                        <i class="fas fa-chevron-left"></i> Previous
                                    </a>
                                </li>
                            <?php endif; ?>

                            <!-- Page Numbers -->
                            <?php
                            $start_page = max(1, $current_page - 2);
                            $end_page = min($total_pages, $current_page + 2);
                            
                            for ($i = $start_page; $i <= $end_page; $i++):
                            ?>
                                <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <!-- Next Page -->
                            <?php if ($current_page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $current_page + 1])) ?>">
                                        Next <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="../assets/js/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>

    <script>
        // Hide global loader when page is loaded
        $(window).on('load', function() {
            $('#global-loader').fadeOut('slow');
        });

        // View switching functionality
        function changeView(viewType) {
            const container = document.getElementById('productsContainer');
            const productItems = container.querySelectorAll('.product-item');
            
            if (viewType === 'list') {
                container.className = 'row';
                productItems.forEach(item => {
                    item.className = 'col-12 mb-3 product-item';
                });
                document.getElementById('gridView').classList.remove('active');
                document.getElementById('listView').classList.add('active');
            } else {
                container.className = 'row';
                productItems.forEach(item => {
                    item.className = 'col-lg-3 col-md-4 col-sm-6 mb-4 product-item';
                });
                document.getElementById('listView').classList.remove('active');
                document.getElementById('gridView').classList.add('active');
            }
        }

        // Auto-submit form on filter change
        document.addEventListener('DOMContentLoaded', function() {
            const categorySelect = document.getElementById('category');
            const brandSelect = document.getElementById('brand');
            
            if (categorySelect) {
                categorySelect.addEventListener('change', function() {
                    this.form.submit();
                });
            }
            
            if (brandSelect) {
                brandSelect.addEventListener('change', function() {
                    this.form.submit();
                });
            }
        });

        function addToCart(productId, productName, price, stock) {
            // Get current cart from localStorage
            let cart = JSON.parse(localStorage.getItem('cart') || '[]');
            
            // Check if product already exists in cart
            let existingItem = cart.find(item => item.id === productId);
            
            if (existingItem) {
                if (existingItem.quantity < stock) {
                    existingItem.quantity += 1;
                } else {
                    alert('Cannot add more items. Stock limit reached.');
                    return;
                }
            } else {
                cart.push({
                    id: productId,
                    name: productName,
                    price: price,
                    quantity: 1,
                    stock: stock
                });
                
            }
            
            // Save cart to localStorage
            localStorage.setItem('cart', JSON.stringify(cart));
            
            // Show success message
            showNotification('Product added to cart successfully!', 'success');
        }
        
        function showNotification(message, type) {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(notification);
            
            // Auto remove after 3 seconds
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 3000);
        }
    </script>
</body>
</html>
