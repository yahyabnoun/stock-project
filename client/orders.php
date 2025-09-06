<?php
session_start();

// Check if client is logged in
if (!isset($_SESSION['client']) || $_SESSION['user_type'] !== 'client') {
    header("Location: ../signin.php");
    exit();
}

require_once("../php/Product.php");

// Get client orders
$orders = [];
$client_email = $_SESSION['username'];

try {
    $pdo = new PDO("mysql:host=localhost;dbname=stock2025", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get all orders for this client
    $stmt = $pdo->prepare("
        SELECT c.*, 
               COUNT(cp.num_pr) as item_count,
               SUM(cp.qte_pr * cp.prix_vente) as total_amount
        FROM commande c 
        LEFT JOIN contient_pr cp ON c.num_com = cp.num_com
        JOIN client cl ON c.id_cli = cl.id 
        WHERE cl.email = :email
        GROUP BY c.num_com
        ORDER BY c.date_com DESC
    ");
    $stmt->bindParam(':email', $client_email);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
}

$active = array(0, 0, 0, "active");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>My Orders - Haila Stock</title>
    
    <link rel="shortcut icon" type="image/x-icon" href="../assets/img/fav1.jpg">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/animate.css">
    <link rel="stylesheet" href="../assets/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        .order-card {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            background: #fff;
            transition: box-shadow 0.3s ease;
        }
        .order-card:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .order-status {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .status-processing {
            background: #d1ecf1;
            color: #0c5460;
        }
        .status-completed {
            background: #d4edda;
            color: #155724;
        }
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        .empty-orders {
            text-align: center;
            padding: 60px 20px;
        }
        .empty-orders i {
            font-size: 4rem;
            color: #ccc;
            margin-bottom: 20px;
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
                            <h4>My Orders</h4>
                        </div>
                    </div>
                    <ul class="table-top-head">
                        <li>
                            <a href="products.php" class="btn btn-primary">
                                <i class="fas fa-shopping-bag"></i> Continue Shopping
                            </a>
                        </li>
                    </ul>
                </div>

                <?php if (empty($orders)): ?>
                    <div class="empty-orders">
                        <i class="fas fa-shopping-cart"></i>
                        <h3>No orders found</h3>
                        <p>You haven't placed any orders yet.</p>
                        <a href="products.php" class="btn btn-primary">
                            <i class="fas fa-shopping-bag"></i> Start Shopping
                        </a>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($orders as $order): ?>
                            <div class="col-lg-6 mb-4">
                                <div class="order-card">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h5 class="mb-1">Order #<?= htmlspecialchars($order['num_com']) ?></h5>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar"></i> 
                                                <?= date('F j, Y g:i A', strtotime($order['date_com'])) ?>
                                            </small>
                                        </div>
                                        <span class="order-status status-pending">Pending</span>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <small class="text-muted">Items:</small>
                                            <div class="fw-bold"><?= $order['item_count'] ?> item(s)</div>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted">Total:</small>
                                            <div class="fw-bold text-success">$<?= number_format($order['total_amount'] * 1.1, 2) ?></div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-outline-primary btn-sm" 
                                                onclick="viewOrderDetails('<?= $order['num_com'] ?>')">
                                            <i class="fas fa-eye"></i> View Details
                                        </button>
                                        <button class="btn btn-outline-secondary btn-sm" 
                                                onclick="printOrder('<?= $order['num_com'] ?>')">
                                            <i class="fas fa-print"></i> Print
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Order Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="orderDetailsContent">
                    <!-- Order details will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="printOrder()">
                        <i class="fas fa-print"></i> Print Order
                    </button>
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

        function viewOrderDetails(orderId) {
            // Fetch order details
            fetch(`order_details.php?order_id=${orderId}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('orderDetailsContent').innerHTML = html;
                    const modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
                    modal.show();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading order details');
                });
        }
        
        function printOrder(orderId) {
            if (orderId) {
                window.open(`print_order.php?order_id=${orderId}`, '_blank');
            } else {
                // Print current modal content
                const printContent = document.getElementById('orderDetailsContent').innerHTML;
                const printWindow = window.open('', '_blank');
                printWindow.document.write(`
                    <html>
                        <head>
                            <title>Order Details</title>
                            <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
                        </head>
                        <body>
                            ${printContent}
                        </body>
                    </html>
                `);
                printWindow.document.close();
                printWindow.print();
            }
        }
    </script>
</body>
</html>
