<?php
session_start();
// print_r($_SESSION);
?>
<?php if (isset($_SESSION['admin'])):
    require_once("../stock2025/php/Client.php");
    require_once("../stock2025/php/Supplier.php");
    require_once("../stock2025/php/Purchase.php");
    require_once("../stock2025/php/Sale.php");
    require_once("../stock2025/php/Product.php");
    $active = array("active", 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
    $clients = Client::nbrDesTuples("client");
    $suppliers = Supplier::nbrDesTuples("fournisseur");
    $purchases = Purchase::TotalLigne("approvisionnement");
    $sales = Sale::TotalLigne("commande");
    $products = Product::afficher("produit");
    $almost_expired_products = Product::afficherExepiredPr();
    $all_sales = Sale::topSales();
    $all_purchases = Purchase::displayAllPur();
    $total_profit = Sale::totalProfit();
    $recent_orders = Sale::displayRecentSales();
    // Get only the 5 most recent orders
    $recent_orders = array_slice($recent_orders, 0, 5);
    $total_all_sales = 0;
    foreach ($all_sales as $item) {
        $total_all_sales += $item['total'];
    }
    $total_all_pur = 0;
    foreach ($all_purchases as $value) {
        $total_all_pur += $value['total'];
    }
    $total_all_pr = 0;
    foreach ($products as $value) {
        $total_all_pr += $value['qte_stock'];
    }
    // print_r($clients); 
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="description" content="POS - Bootstrap Admin Template">
    <meta name="keywords"
        content="admin, estimates, bootstrap, business, corporate, creative, management, minimal, modern,  html5, responsive">
    <meta name="author" content="Dreamguys - Bootstrap Admin Template">
    <meta name="robots" content="noindex, nofollow">
    <title>Haila Stock</title>

    <link rel="shortcut icon" type="image/x-icon" href="assets/img/fav1.jpg">

    <link rel="stylesheet" href="assets/css/bootstrap.min.css">

    <link rel="stylesheet" href="assets/css/animate.css">

    <link rel="stylesheet" href="assets/css/dataTables.bootstrap4.min.css">

    <link rel="stylesheet" href="assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="assets/plugins/fontawesome/css/all.min.css">

    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        .recent-order {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            background: #fff;
            transition: all 0.3s ease;
        }
        .recent-order:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        .order-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }
        .status-processing {
            background-color: #cce5ff;
            color: #004085;
        }
        .customer-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        .order-card-header {
            display: flex;
            justify-content: between;
            align-items: center;
            margin-bottom: 10px;
        }
        .order-amount {
            font-size: 1.1rem;
            font-weight: 600;
            color: #28a745;
        }
        .order-date {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .customer-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .customer-name {
            font-weight: 500;
            margin: 0;
        }
        .order-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
        }
        .order-items {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .view-all-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            transition: all 0.3s ease;
        }
        .view-all-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            color: white;
        }
        .recent-orders-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .recent-orders-title {
            color: #333;
            font-weight: 600;
            margin: 0;
        }
        .empty-orders {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }
        .empty-orders i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }
    </style>

</head>

<body>
    <div id=" global-loader">
        <div class="whirly-loader"> </div>
    </div>

    <div class="main-wrapper">

        <?php require_once("header.php"); ?>
        <?php require_once("sidebar.php"); ?>

        <div class="page-wrapper">
            <div class="content">
                <div class="row">
                    <div class="col-lg-3 col-sm-6 col-12">
                        <div class="dash-widget">
                            <div class="dash-widgetimg">
                                <span><img src="assets/img/icons/dash1.svg" alt="img"></span>
                            </div>
                            <?php ?>
                            <?php ?>
                            <div class="dash-widgetcontent">
                                <h5><span class="counters" data-count="<?= $total_all_pur ?>"><?= $total_all_pur ?>DH</span></h5>
                                <h6>Total Purchases (DH)</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-12">
                        <div class="dash-widget dash1">
                            <div class="dash-widgetimg">
                                <span><img src="assets/img/icons/dash2.svg" alt="img"></span>
                            </div>
                            <div class="dash-widgetcontent">
                                <h5><span class="counters" data-count="<?= $total_all_sales ?>"><?= $total_all_sales ?>DH</span></h5>
                                <h6>Total Sales (DH)</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-12">
                        <div class="dash-widget dash2">
                            <div class="dash-widgetimg">
                                <span><img src="assets/img/icons/dash3.svg" alt="img"></span>
                            </div>
                            <div class="dash-widgetcontent">
                                <h5><span class="counters" data-count="<?= $total_profit ?>"><?= $total_profit ?>DH</span></h5>
                                <h6>Total Profit (DH)</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-12">
                        <div class="dash-widget dash3">
                            <div class="dash-widgetimg">
                                <span><img src="assets/img/icons/dash4.svg" alt="img"></span>
                            </div>
                            <div class="dash-widgetcontent">
                                <h5><span class="counters" data-count="<?= $total_all_pr ?>"><?= $total_all_pr ?>
                                        DH</span>
                                </h5>
                                <h6>Total Products</h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-12 d-flex">
                        <div class="dash-count">
                            <div class="dash-counts">
                                <h4><?= $clients ?></h4>
                                <h5>Customers</h5>
                            </div>
                            <div class="dash-imgs">
                                <i data-feather="user"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-12 d-flex">
                        <div class="dash-count das1">
                            <div class="dash-counts">
                                <h4><?= $suppliers ?></h4>
                                <h5>Suppliers</h5>
                            </div>
                            <div class="dash-imgs">
                                <i data-feather="user-check"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-12 d-flex">
                        <div class="dash-count das2">
                            <div class="dash-counts">
                                <h4><?= $purchases ?></h4>
                                <h5>Purchase Invoice</h5>
                            </div>
                            <div class="dash-imgs">
                                <i data-feather="file-text"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6 col-12 d-flex">
                        <div class="dash-count das3">
                            <div class="dash-counts">
                                <h4><?= $sales ?></h4>
                                <h5>Sales Invoice</h5>
                            </div>
                            <div class="dash-imgs">
                                <i data-feather="file"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Recent Orders Section -->
                    <div class="col-lg-8 col-sm-12 col-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="recent-orders-header">
                                    <h5 class="recent-orders-title">
                                        <i class="fas fa-history"></i> Recent Orders
                                    </h5>
                                    <a href="salesreturnlists.php" class="btn btn-sm view-all-btn">
                                        View All Orders
                                    </a>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php if (empty($recent_orders)): ?>
                                    <div class="empty-orders">
                                        <i class="fas fa-shopping-cart"></i>
                                        <h5>No orders yet</h5>
                                        <p>Orders will appear here once customers start making purchases.</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($recent_orders as $order): ?>
                                        <div class="recent-order">
                                            <div class="order-card-header">
                                                <div class="customer-info">
                                                    <img src="<?= htmlspecialchars($order['image']) ?>" 
                                                         alt="Customer" class="customer-avatar">
                                                    <div>
                                                        <h6 class="customer-name">
                                                            <?= htmlspecialchars($order['nom'] . ' ' . $order['prenom']) ?>
                                                        </h6>
                                                        <small class="order-date">
                                                            <i class="fas fa-calendar"></i> 
                                                            <?= date('M j, Y g:i A', strtotime($order['date_com'])) ?>
                                                        </small>
                                                    </div>
                                                </div>
                                                <span class="order-status status-pending">Pending</span>
                                            </div>
                                            
                                            <div class="order-details">
                                                <div>
                                                    <strong>Order #<?= htmlspecialchars($order['num_com']) ?></strong>
                                                    <div class="order-items">
                                                        <i class="fas fa-box"></i> Multiple items
                                                    </div>
                                                </div>
                                                <div class="order-amount">
                                                    <?= number_format($order['total'], 2) ?> DH
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Top Sales Section -->
                    <div class="col-lg-4 col-sm-12 col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-trophy"></i> Top Sales
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($all_sales)): ?>
                                    <?php for ($i = 0; $i < min(3, count($all_sales)); $i++): ?>
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="flex-shrink-0">
                                                <img src="<?= htmlspecialchars($all_sales[$i]['image']) ?>" 
                                                     alt="Customer" class="customer-avatar">
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h6 class="mb-1"><?= htmlspecialchars($all_sales[$i]['nom'] . ' ' . $all_sales[$i]['prenom']) ?></h6>
                                                <small class="text-muted">Order #<?= htmlspecialchars($all_sales[$i]['num_com']) ?></small>
                                            </div>
                                            <div class="text-end">
                                                <strong class="order-amount"><?= number_format($all_sales[$i]['total'], 2) ?> DH</strong>
                                            </div>
                                        </div>
                                    <?php endfor; ?>
                                <?php else: ?>
                                    <div class="text-center py-3">
                                        <i class="fas fa-chart-line fa-2x text-muted mb-2"></i>
                                        <p class="text-muted">No sales data available</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- <div class="card mb-0">
                    <div class="card-body">
                        <h4 class="card-title">Least Quantity in Stock</h4>
                        <div class="table-responsive dataview">
                            <table class="table datatable ">
                                <thead>
                                    <tr>
                                        <th>SNo</th>
                                        <th>Product Name</th>
                                        <th>Brand Name</th>
                                        <th>Category Name</th>
                                        <th>Purchase price</th>
                                        <th>Remaining quantity</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php for ($i = 0; $i < 4; $i++): ?>
                                    <tr>
                                        <td><?= $i + 1; ?></td>
                                        <td class="productimgname">
                                            <a class="product-img" href="productlist.php">
                                                <img src="<?= $almost_expired_products[$i]['pr_image'] ?>"
                                                    alt="product">
                                            </a>
                                            <a href="productlist.php"><?= $almost_expired_products[$i]['lib_pr'] ?></a>
                                        </td>
                                        <td><?= $almost_expired_products[$i]['nom_marque'] ?></td>
                                        <td><?= $almost_expired_products[$i]['lib_cat'] ?></td>
                                        <td><?= $almost_expired_products[$i]['prix_achat'] ?></td>
                                        <td><?= $almost_expired_products[$i]['qte_stock'] ?></td>
                                    </tr>
                                    <?php endfor ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div> -->
            </div>
        </div>
    </div>


    <script src="assets/js/jquery-3.6.0.min.js"></script>

    <script src="assets/js/feather.min.js"></script>

    <script src="assets/js/jquery.slimscroll.min.js"></script>

    <script src="assets/js/jquery.dataTables.min.js"></script>
    <script src="assets/js/dataTables.bootstrap4.min.js"></script>

    <script src="assets/js/bootstrap.bundle.min.js"></script>

    <script src="assets/plugins/apexchart/apexcharts.min.js"></script>
    <script src="assets/plugins/apexchart/chart-data.js"></script>

    <script src="assets/js/script.js"></script>
</body>

</html>
<?php else: ?>
<?php header("Location: signin.php"); ?>
<?php endif ?>