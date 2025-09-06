<?php
session_start();

// Check if client is logged in
if (!isset($_SESSION['client']) || $_SESSION['user_type'] !== 'client') {
    http_response_code(401);
    exit('Unauthorized');
}

$order_id = $_GET['order_id'] ?? '';

if (empty($order_id)) {
    exit('Order ID required');
}

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
    exit('Database error');
}

if (!$order_details) {
    exit('Order not found');
}
?>

<div class="order-details">
    <!-- Order Information -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h6><i class="fas fa-info-circle"></i> Order Information</h6>
            <p><strong>Order Number:</strong> <?= htmlspecialchars($order_details['num_com']) ?></p>
            <p><strong>Order Date:</strong> <?= date('F j, Y g:i A', strtotime($order_details['date_com'])) ?></p>
        </div>
        <div class="col-md-6">
            <h6><i class="fas fa-user"></i> Customer Information</h6>
            <p><strong>Name:</strong> <?= htmlspecialchars($order_details['prenom'] . ' ' . $order_details['nom']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($order_details['email']) ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($order_details['tele']) ?></p>
        </div>
    </div>
    
    <!-- Order Items -->
    <h6><i class="fas fa-shopping-bag"></i> Order Items</h6>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $subtotal = 0;
                foreach ($order_items as $item): 
                    $item_total = $item['prix_vente'] * $item['qte_pr'];
                    $subtotal += $item_total;
                ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="../<?= htmlspecialchars($item['pr_image']) ?>" 
                                     class="me-3" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;" 
                                     alt="<?= htmlspecialchars($item['lib_pr']) ?>">
                                <div>
                                    <div class="fw-bold"><?= htmlspecialchars($item['lib_pr']) ?></div>
                                    <small class="text-muted">ID: <?= htmlspecialchars($item['num_pr']) ?></small>
                                </div>
                            </div>
                        </td>
                        <td><?= $item['qte_pr'] ?></td>
                        <td>$<?= number_format($item['prix_vente'], 2) ?></td>
                        <td>$<?= number_format($item_total, 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Order Summary -->
    <div class="row">
        <div class="col-md-6">
            <h6><i class="fas fa-map-marker-alt"></i> Delivery Address</h6>
            <p><?= htmlspecialchars($order_details['adr']) ?></p>
        </div>
        <div class="col-md-6">
            <h6><i class="fas fa-calculator"></i> Order Summary</h6>
            <div class="d-flex justify-content-between">
                <span>Subtotal:</span>
                <span>$<?= number_format($subtotal, 2) ?></span>
            </div>
            <div class="d-flex justify-content-between">
                <span>Tax (10%):</span>
                <span>$<?= number_format($subtotal * 0.1, 2) ?></span>
            </div>
            <hr>
            <div class="d-flex justify-content-between fw-bold">
                <span>Total:</span>
                <span>$<?= number_format($subtotal * 1.1, 2) ?></span>
            </div>
        </div>
    </div>
</div>
