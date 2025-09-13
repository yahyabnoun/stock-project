<?php
session_start();

// Set timezone to Casablanca, Morocco
date_default_timezone_set('Africa/Casablanca');

// Check if client is logged in
if (!isset($_SESSION['client']) || $_SESSION['user_type'] !== 'client') {
    header("Location: ../signin.php");
    exit();
}

// Check if cart data exists
if (!isset($_SESSION['cart_data']) || empty($_SESSION['cart_data'])) {
    header("Location: cart.php");
    exit();
}

require_once("../php/Client.php");
require_once("../php/Product.php");

// Get client information
$client_email = $_SESSION['username'];
$client_info = null;

try {
    $pdo = new PDO("mysql:host=localhost;dbname=stock2025", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("SELECT * FROM client WHERE email = :email");
    $stmt->bindParam(':email', $client_email);
    $stmt->execute();
    $client_info = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
}

$cart_data = $_SESSION['cart_data'];
$subtotal = 0;
$tax_rate = 0.1; // 10% tax

// Calculate totals
foreach ($cart_data as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$tax = $subtotal * $tax_rate;
$total = $subtotal + $tax;

$active = array(0, 0, 0, 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Checkout - Haila Stock</title>
    
    <link rel="shortcut icon" type="image/x-icon" href="../assets/img/fav1.jpg">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/animate.css">
    <link rel="stylesheet" href="../assets/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        .checkout-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
        }
        .order-summary {
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 20px;
            position: sticky;
            top: 20px;
        }
        .order-item {
            border-bottom: 1px solid #f0f0f0;
            padding: 10px 0;
        }
        .order-item:last-child {
            border-bottom: none;
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
                            <h4>Checkout</h4>
                        </div>
                    </div>
                    <ul class="table-top-head">
                        <li>
                            <a href="cart.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Cart
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="row">
                    <div class="col-lg-8">
                        <!-- Customer Information -->
                        <div class="checkout-section">
                            <h5 class="mb-3"><i class="fas fa-user"></i> Customer Information</h5>
                            <?php if ($client_info): ?>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Name:</strong> <?= htmlspecialchars($client_info['prenom'] . ' ' . $client_info['nom']) ?></p>
                                        <p><strong>Email:</strong> <?= htmlspecialchars($client_info['email']) ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Phone:</strong> <?= htmlspecialchars($client_info['tele']) ?></p>
                                        <p><strong>Address:</strong> <?= htmlspecialchars($client_info['adr']) ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Payment Information -->
                        <div class="checkout-section">
                            <h5 class="mb-3"><i class="fas fa-credit-card"></i> Payment Information</h5>
                            <form id="checkout-form">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group mb-3">
                                            <label for="payment_method">Payment Method</label>
                                            <select class="form-control" id="payment_method" name="payment_method" required>
                                                <option value="">Select Payment Method</option>
                                                <option value="cash">Cash on Delivery</option>
                                                <option value="card">Credit/Debit Card</option>
                                                <option value="bank_transfer">Bank Transfer</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group mb-3">
                                    <label for="notes">Order Notes (Optional)</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="3" 
                                              placeholder="Any special instructions for your order..."></textarea>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="order-summary">
                            <h5 class="mb-3">Order Summary</h5>
                            
                            <!-- Order Items -->
                            <div class="mb-3">
                                <?php foreach ($cart_data as $item): ?>
                                    <div class="order-item">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <h6 class="mb-1"><?= htmlspecialchars($item['name']) ?></h6>
                                                <small class="text-muted">Qty: <?= $item['quantity'] ?></small>
                                            </div>
                                            <span class="fw-bold"><?= number_format($item['price'] * $item['quantity'], 2) ?> DH</span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <hr>
                            
                            <!-- Totals -->
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span><?= number_format($subtotal, 2) ?> DH</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tax (10%):</span>
                                <span><?= number_format($tax, 2) ?> DH</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <strong>Total:</strong>
                                <strong><?= number_format($total, 2) ?> DH</strong>
                            </div>
                            
                            <button type="button" class="btn btn-success w-100" onclick="placeOrder()">
                                <i class="fas fa-check"></i> Place Order
                            </button>
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

        function placeOrder() {
            const form = document.getElementById('checkout-form');
            const formData = new FormData(form);
            
            // Validate form
            if (!formData.get('payment_method')) {
                alert('Please select a payment method.');
                return;
            }
            
            // Confirm order
            if (!confirm('Are you sure you want to place this order?')) {
                return;
            }
            
            // Prepare order data
            const orderData = {
                payment_method: formData.get('payment_method'),
                notes: formData.get('notes'),
                cart_data: <?= json_encode($cart_data) ?>,
                subtotal: <?= $subtotal ?>,
                tax: <?= $tax ?>,
                total: <?= $total ?>
            };
            
            // Submit order
            fetch('place_order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(orderData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Clear cart from localStorage
                    localStorage.removeItem('cart');
                    
                    // Redirect to order confirmation
                    window.location.href = `order_confirmation.php?order_id=${data.order_id}`;
                } else {
                    alert('Error placing order: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error placing order. Please try again.');
            });
        }
    </script>
</body>
</html>
