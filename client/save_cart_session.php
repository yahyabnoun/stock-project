<?php
session_start();

// Check if client is logged in
if (!isset($_SESSION['client']) || $_SESSION['user_type'] !== 'client') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['cart']) && is_array($input['cart'])) {
    // Save cart data to session
    $_SESSION['cart_data'] = $input['cart'];
    
    echo json_encode(['success' => true, 'message' => 'Cart saved successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid cart data']);
}
?>
