<?php
session_start();

// Check if client is logged in
if (!isset($_SESSION['client']) || $_SESSION['user_type'] !== 'client') {
    header("Location: ../signin.php");
    exit();
}

$active = array(0, 0, "active", 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Shopping Cart - Haila Stock</title>
    
    <link rel="shortcut icon" type="image/x-icon" href="../assets/img/fav1.jpg">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/animate.css">
    <link rel="stylesheet" href="../assets/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        .cart-item {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            background: #fff;
        }
        .cart-item:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .quantity-btn {
            width: 35px;
            height: 35px;
            border: 1px solid #ddd;
            background: #f8f9fa;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        .quantity-btn:hover {
            background: #e9ecef;
        }
        .quantity-input {
            width: 60px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 5px;
        }
        .summary-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            position: sticky;
            top: 20px;
        }
        .empty-cart {
            text-align: center;
            padding: 60px 20px;
        }
        .empty-cart i {
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
                            <h4>Shopping Cart</h4>
                        </div>
                    </div>
                    <ul class="table-top-head">
                        <li>
                            <a href="products.php" class="btn btn-primary">
                                <i class="fas fa-arrow-left"></i> Continue Shopping
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="row">
                    <div class="col-lg-8">
                        <div id="cart-items">
                            <!-- Cart items will be loaded here -->
                        </div>
                        
                        <div id="empty-cart" class="empty-cart" style="display: none;">
                            <i class="fas fa-shopping-cart"></i>
                            <h3>Your cart is empty</h3>
                            <p>Add some products to get started!</p>
                            <a href="products.php" class="btn btn-primary">
                                <i class="fas fa-shopping-bag"></i> Start Shopping
                            </a>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="summary-card">
                            <h5 class="mb-3">Order Summary</h5>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span id="subtotal">$0.00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tax (10%):</span>
                                <span id="tax">$0.00</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <strong>Total:</strong>
                                <strong id="total">$0.00</strong>
                            </div>
                            <button id="checkout-btn" class="btn btn-success w-100" onclick="proceedToCheckout()" disabled>
                                <i class="fas fa-credit-card"></i> Proceed to Checkout
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

        let cart = [];

        // Load cart from localStorage
        function loadCart() {
            cart = JSON.parse(localStorage.getItem('cart') || '[]');
            displayCart();
        }

        // Display cart items
        function displayCart() {
            const cartContainer = document.getElementById('cart-items');
            const emptyCart = document.getElementById('empty-cart');
            
            if (cart.length === 0) {
                cartContainer.style.display = 'none';
                emptyCart.style.display = 'block';
                updateSummary();
                return;
            }
            
            cartContainer.style.display = 'block';
            emptyCart.style.display = 'none';
            
            cartContainer.innerHTML = '';
            
            cart.forEach((item, index) => {
                const cartItem = document.createElement('div');
                cartItem.className = 'cart-item';
                cartItem.innerHTML = `
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <img src="../assets/img/product-placeholder.jpg" class="product-image" alt="${item.name}">
                        </div>
                        <div class="col-md-4">
                            <h6 class="mb-1">${item.name}</h6>
                            <small class="text-muted">Product ID: ${item.id}</small>
                        </div>
                        <div class="col-md-2">
                            <span class="fw-bold">$${item.price.toFixed(2)}</span>
                        </div>
                        <div class="col-md-3">
                            <div class="quantity-controls">
                                <button class="quantity-btn" onclick="updateQuantity(${index}, -1)">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" class="quantity-input" value="${item.quantity}" 
                                       min="1" max="${item.stock}" onchange="setQuantity(${index}, this.value)">
                                <button class="quantity-btn" onclick="updateQuantity(${index}, 1)">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            <small class="text-muted">Max: ${item.stock}</small>
                        </div>
                        <div class="col-md-1">
                            <button class="btn btn-outline-danger btn-sm" onclick="removeItem(${index})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `;
                cartContainer.appendChild(cartItem);
            });
            
            updateSummary();
        }

        // Update quantity
        function updateQuantity(index, change) {
            const newQuantity = cart[index].quantity + change;
            if (newQuantity >= 1 && newQuantity <= cart[index].stock) {
                cart[index].quantity = newQuantity;
                saveCart();
                displayCart();
            }
        }

        // Set quantity directly
        function setQuantity(index, value) {
            const quantity = parseInt(value);
            if (quantity >= 1 && quantity <= cart[index].stock) {
                cart[index].quantity = quantity;
                saveCart();
                displayCart();
            } else {
                // Reset to current value if invalid
                displayCart();
            }
        }

        // Remove item from cart
        function removeItem(index) {
            if (confirm('Are you sure you want to remove this item from your cart?')) {
                cart.splice(index, 1);
                saveCart();
                displayCart();
                showNotification('Item removed from cart', 'info');
            }
        }

        // Update order summary
        function updateSummary() {
            const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const tax = subtotal * 0.1; // 10% tax
            const total = subtotal + tax;
            
            document.getElementById('subtotal').textContent = `$${subtotal.toFixed(2)}`;
            document.getElementById('tax').textContent = `$${tax.toFixed(2)}`;
            document.getElementById('total').textContent = `$${total.toFixed(2)}`;
            
            const checkoutBtn = document.getElementById('checkout-btn');
            checkoutBtn.disabled = cart.length === 0;
        }

        // Save cart to localStorage
        function saveCart() {
            localStorage.setItem('cart', JSON.stringify(cart));
        }

        // Proceed to checkout
        function proceedToCheckout() {
            if (cart.length === 0) {
                alert('Your cart is empty!');
                return;
            }
            
            // Save cart data to session for checkout
            fetch('save_cart_session.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({cart: cart})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'checkout.php';
                } else {
                    alert('Error saving cart data. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error saving cart data. Please try again.');
            });
        }

        // Show notification
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
            notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 3000);
        }

        // Load cart when page loads
        document.addEventListener('DOMContentLoaded', loadCart);
    </script>
</body>
</html>
