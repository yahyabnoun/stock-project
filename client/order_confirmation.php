<?php
session_start();

// Check if client is logged in
if (!isset($_SESSION['client']) || $_SESSION['user_type'] !== 'client') {
    header("Location: ../signin.php");
    exit();
}

$order_id = $_GET['order_id'] ?? '';

if (empty($order_id)) {
    header("Location: orders.php");
    exit();
}

require_once("../php/Product.php");

// Get order details
$order_details = null;
$order_items = [];

try {
    $pdo = new PDO("mysql:host=localhost;dbname=stock2025", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get order information
    $stmt = $pdo->prepare("
        SELECT c.*, cl.nom, cl.prenom, cl.email, cl.tele, cl.adr 
        FROM commande c 
        JOIN client cl ON c.id_cli = cl.id 
        WHERE c.num_com = :order_id
    ");
    $stmt->bindParam(':order_id', $order_id);
    $stmt->execute();
    $order_details = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($order_details) {
        // Get order items
        $stmt = $pdo->prepare("
            SELECT cp.*, p.lib_pr, p.desc_pr, p.pr_image 
            FROM contient_pr cp 
            JOIN produit p ON cp.num_pr = p.num_pr 
            WHERE cp.num_com = :order_id
        ");
        $stmt->bindParam(':order_id', $order_id);
        $stmt->execute();
        $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
}

if (!$order_details) {
    header("Location: orders.php");
    exit();
}

$active = array(0, 0, 0, 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Order Confirmation - Haila Stock</title>
    
    <link rel="shortcut icon" type="image/x-icon" href="../assets/img/fav1.jpg">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/animate.css">
    <link rel="stylesheet" href="../assets/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        .confirmation-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            text-align: center;
        }
        .success-icon {
            font-size: 4rem;
            color: #28a745;
            margin-bottom: 20px;
        }
        .order-details {
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
        }
        .order-item {
            border-bottom: 1px solid #f0f0f0;
            padding: 15px 0;
        }
        .order-item:last-child {
            border-bottom: none;
        }
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
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
                            <h4>Order Confirmation</h4>
                        </div>
                    </div>
                </div>

                <!-- Success Message -->
                <div class="confirmation-card">
                    <div class="success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h2 class="text-success mb-3">Order Placed Successfully!</h2>
                    <p class="lead">Thank you for your order. We'll process it and get back to you soon.</p>
                    <p><strong>Order Number:</strong> <?= htmlspecialchars($order_details['num_com']) ?></p>
                </div>

                <div class="row">
                    <div class="col-lg-8">
                        <!-- Order Details -->
                        <div class="order-details">
                            <h5 class="mb-3"><i class="fas fa-info-circle"></i> Order Information</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Order Number:</strong> <?= htmlspecialchars($order_details['num_com']) ?></p>
                                    <p><strong>Order Date:</strong> <?= date('F j, Y g:i A', strtotime($order_details['date_com'])) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Customer:</strong> <?= htmlspecialchars($order_details['prenom'] . ' ' . $order_details['nom']) ?></p>
                                    <p><strong>Email:</strong> <?= htmlspecialchars($order_details['email']) ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Order Items -->
                        <div class="order-details">
                            <h5 class="mb-3"><i class="fas fa-shopping-bag"></i> Order Items</h5>
                            <?php foreach ($order_items as $item): ?>
                                <div class="order-item">
                                    <div class="row align-items-center">
                                        <div class="col-md-2">
                                            <img src="../<?= htmlspecialchars($item['pr_image']) ?>" 
                                                 class="product-image" alt="<?= htmlspecialchars($item['lib_pr']) ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="mb-1"><?= htmlspecialchars($item['lib_pr']) ?></h6>
                                            <small class="text-muted">Product ID: <?= htmlspecialchars($item['num_pr']) ?></small>
                                        </div>
                                        <div class="col-md-2">
                                            <span class="text-muted">Qty: <?= $item['qte_pr'] ?></span>
                                        </div>
                                        <div class="col-md-2">
                                            <span class="fw-bold">$<?= number_format($item['prix_vente'] * $item['qte_pr'], 2) ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <!-- Order Summary -->
                        <div class="order-details">
                            <h5 class="mb-3">Order Summary</h5>
                            
                            <?php 
                            $subtotal = 0;
                            foreach ($order_items as $item) {
                                $subtotal += $item['prix_vente'] * $item['qte_pr'];
                            }
                            $tax = $subtotal * 0.1;
                            $total = $subtotal + $tax;
                            ?>
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span>$<?= number_format($subtotal, 2) ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tax (10%):</span>
                                <span>$<?= number_format($tax, 2) ?></span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <strong>Total:</strong>
                                <strong>$<?= number_format($total, 2) ?></strong>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <a href="orders.php" class="btn btn-primary">
                                    <i class="fas fa-list"></i> View All Orders
                                </a>
                                <a href="products.php" class="btn btn-outline-primary">
                                    <i class="fas fa-shopping-bag"></i> Continue Shopping
                                </a>
                            </div>
                        </div>
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
