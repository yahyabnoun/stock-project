<?php
session_start();

// Set timezone to Casablanca, Morocco
date_default_timezone_set('Africa/Casablanca');

// Check if client is logged in
if (!isset($_SESSION['client']) || $_SESSION['user_type'] !== 'client') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['cart_data']) || !isset($input['total'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid order data']);
    exit();
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=stock2025", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get client ID
    $client_email = $_SESSION['username'];
    $stmt = $pdo->prepare("SELECT id FROM client WHERE email = :email");
    $stmt->bindParam(':email', $client_email);
    $stmt->execute();
    $client = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$client) {
        echo json_encode(['success' => false, 'message' => 'Client not found']);
        exit();
    }
    
    $client_id = $client['id'];
    
    // Generate order number
    $order_number = 'ORD' . date('Ymd') . rand(1000, 9999);
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Insert order
    $order_date = date('Y-m-d H:i:s');
    $stmt = $pdo->prepare("INSERT INTO commande (num_com, date_com, id_cli) VALUES (:num_com, :date_com, :id_cli)");
    $stmt->bindParam(':num_com', $order_number);
    $stmt->bindParam(':date_com', $order_date);
    $stmt->bindParam(':id_cli', $client_id);
    $stmt->execute();
    
    // Insert order items
    foreach ($input['cart_data'] as $item) {
        $stmt = $pdo->prepare("INSERT INTO contient_pr (num_pr, num_com, qte_pr, prix_vente) VALUES (:num_pr, :num_com, :qte_pr, :prix_vente)");
        $stmt->bindParam(':num_pr', $item['id']);
        $stmt->bindParam(':num_com', $order_number);
        $stmt->bindParam(':qte_pr', $item['quantity']);
        $stmt->bindParam(':prix_vente', $item['price']);
        $stmt->execute();
        
        // Update product stock
        $stmt = $pdo->prepare("UPDATE produit SET qte_stock = qte_stock - :qte WHERE num_pr = :num_pr");
        $stmt->bindParam(':qte', $item['quantity']);
        $stmt->bindParam(':num_pr', $item['id']);
        $stmt->execute();
    }
    
    // Commit transaction
    $pdo->commit();
    
    // Clear cart from session
    unset($_SESSION['cart_data']);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Order placed successfully',
        'order_id' => $order_number
    ]);
    
} catch (PDOException $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollback();
    }
    
    error_log("Order placement error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
} catch (Exception $e) {
    error_log("Order placement error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while placing the order']);
}
?>
