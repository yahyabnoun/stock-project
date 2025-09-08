<?php
session_start();

// Check if client is logged in
if (!isset($_SESSION['client']) || $_SESSION['user_type'] !== 'client') {
    header("Location: ../signin.php");
    exit();
}

require_once("../php/Product.php");

// Get client information and stats
$client_email = $_SESSION['username'];
$client_info = null;
$recent_orders = [];
$total_orders = 0;
$total_spent = 0;

try {
    $pdo = new PDO("mysql:host=localhost;dbname=stock2025", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get client information
    $stmt = $pdo->prepare("SELECT * FROM client WHERE email = :email");
    $stmt->bindParam(':email', $client_email);
    $stmt->execute();
    $client_info = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($client_info) {
        // Get recent orders
        $stmt = $pdo->prepare("
            SELECT c.*, 
                   COUNT(cp.num_pr) as item_count,
                   SUM(cp.qte_pr * cp.prix_vente) as total_amount
            FROM commande c 
            LEFT JOIN contient_pr cp ON c.num_com = cp.num_com
            WHERE c.id_cli = :client_id
            GROUP BY c.num_com
            ORDER BY c.date_com DESC
            LIMIT 5
        ");
        $stmt->bindParam(':client_id', $client_info['id']);
        $stmt->execute();
        $recent_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get total orders count
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM commande WHERE id_cli = :client_id");
        $stmt->bindParam(':client_id', $client_info['id']);
        $stmt->execute();
        $total_orders = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Get total spent
        $stmt = $pdo->prepare("
            SELECT SUM(cp.qte_pr * cp.prix_vente) as total_spent
            FROM commande c 
            JOIN contient_pr cp ON c.num_com = cp.num_com
            WHERE c.id_cli = :client_id
        ");
        $stmt->bindParam(':client_id', $client_info['id']);
        $stmt->execute();
        $total_spent = $stmt->fetch(PDO::FETCH_ASSOC)['total_spent'] ?? 0;
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
}

$active = array("active", 0, 0, 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Dashboard - Haila Stock</title>
    
    <link rel="shortcut icon" type="image/x-icon" href="../assets/img/fav1.jpg">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/animate.css">
    <link rel="stylesheet" href="../assets/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
        }
        .stats-card.success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }
        .stats-card.warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        .stats-card.info {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        .stats-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }
        .recent-order {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            background: #fff;
        }
        .welcome-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
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
                            <h4>Dashboard</h4>
                        </div>
                    </div>
                </div>

                <!-- Welcome Section -->
                <div class="welcome-section">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2>Welcome back, <?= htmlspecialchars($client_info['prenom'] ?? 'Customer') ?>!</h2>
                            <p class="mb-0">Manage your orders and discover new products in our store.</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="products.php" class="btn btn-light btn-lg">
                                <i class="fas fa-shopping-bag"></i> Start Shopping
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <div class="stats-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="mb-1"><?= $total_orders ?></h3>
                                    <p class="mb-0">Total Orders</p>
                                </div>
                                <i class="fas fa-shopping-cart stats-icon"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="stats-card success">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="mb-1"><?= number_format($total_spent * 1.1, 2) ?> DH</h3>
                                    <p class="mb-0">Total Spent</p>
                                </div>
                                <i class="fas fa-dollar-sign stats-icon"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="stats-card warning">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="mb-1"><?= count($recent_orders) ?></h3>
                                    <p class="mb-0">Recent Orders</p>
                                </div>
                                <i class="fas fa-clock stats-icon"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="stats-card info">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h3 class="mb-1">Active</h3>
                                    <p class="mb-0">Account Status</p>
                                </div>
                                <i class="fas fa-user-check stats-icon"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Recent Orders -->
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-history"></i> Recent Orders
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (empty($recent_orders)): ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                        <h5>No orders yet</h5>
                                        <p class="text-muted">Start shopping to see your orders here.</p>
                                        <a href="products.php" class="btn btn-primary">
                                            <i class="fas fa-shopping-bag"></i> Start Shopping
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($recent_orders as $order): ?>
                                        <div class="recent-order">
                                            <div class="row align-items-center">
                                                <div class="col-md-4">
                                                    <h6 class="mb-1">Order #<?= htmlspecialchars($order['num_com']) ?></h6>
                                                    <small class="text-muted">
                                                        <?= date('M j, Y', strtotime($order['date_com'])) ?>
                                                    </small>
                                                </div>
                                                <div class="col-md-3">
                                                    <span class="badge bg-warning">Pending</span>
                                                </div>
                                                <div class="col-md-3">
                                                    <small class="text-muted"><?= $order['item_count'] ?> item(s)</small>
                                                </div>
                                                <div class="col-md-2 text-end">
                                                    <strong><?= number_format($order['total_amount'] * 1.1, 2) ?> DH</strong>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    <div class="text-center mt-3">
                                        <a href="orders.php" class="btn btn-outline-primary">
                                            View All Orders
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-bolt"></i> Quick Actions
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="products.php" class="btn btn-primary">
                                        <i class="fas fa-shopping-bag"></i> Browse Products
                                    </a>
                                    <a href="cart.php" class="btn btn-outline-primary">
                                        <i class="fas fa-shopping-cart"></i> View Cart
                                    </a>
                                    <a href="orders.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-list"></i> My Orders
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Account Info -->
                        <?php if ($client_info): ?>
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-user"></i> Account Information
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <p><strong>Name:</strong> <?= htmlspecialchars($client_info['prenom'] . ' ' . $client_info['nom']) ?></p>
                                    <p><strong>Email:</strong> <?= htmlspecialchars($client_info['email']) ?></p>
                                    <p><strong>Phone:</strong> <?= htmlspecialchars($client_info['tele']) ?></p>
                                    <a href="profile.php" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-edit"></i> Edit Profile
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
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
    </script>
</body>
</html>
