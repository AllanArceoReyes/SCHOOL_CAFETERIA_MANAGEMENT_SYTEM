<?php
require 'config.php';
if (!is_logged_in() || is_admin()) {
    header("Location: index.php");
    exit;
}
$userId = $_SESSION['user_id'];

// Fetch cart items for checkout
$cartStmt = $pdo->prepare("SELECT cart.*, food_items.food_name, food_items.price FROM cart JOIN food_items ON cart.food_id = food_items.id WHERE cart.user_id = ?");
$cartStmt->execute([$userId]);
$cartItems = $cartStmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total order amount
$totalAmount = 0;
foreach ($cartItems as $item) {
    $totalAmount += $item['price'] * $item['quantity'];
}

// Redirect back to cart if cart is empty
if ($totalAmount == 0) {
    header("Location: cart.php");
    exit;
}

// Process order submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $paymentMethod = $_POST['payment_method'];
    try {
        $pdo->beginTransaction();
        // Insert main order record
        $orderStmt = $pdo->prepare("INSERT INTO orders(user_id, total_amount, payment_method) VALUES (?, ?, ?)");
        $orderStmt->execute([$userId, $totalAmount, $paymentMethod]);
        $newOrderId = $pdo->lastInsertId();

        // Insert individual order items
        foreach ($cartItems as $item) {
            $orderItemStmt = $pdo->prepare("INSERT INTO order_items(order_id, food_id, quantity, item_price) VALUES (?, ?, ?, ?)");
            $orderItemStmt->execute([$newOrderId, $item['food_id'], $item['quantity'], $item['price']]);
        }

        // Clear user's shopping cart after successful checkout
        $clearCartStmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $clearCartStmt->execute([$userId]);
        $pdo->commit();
        header("Location: order_history.php");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Order failed: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <title>Checkout | Cafeteria System</title>
</head>
<body>
    <div class="container">
        <h2>Checkout</h2>
        <div class="nav-links">
            <a href="cart.php">Back to Cart</a>
            <a href="user_dashboard.php">Menu</a>
            <a href="index.php?logout=1">Logout</a>
        </div>
        <h3>Total Order Amount: ₱<?php echo number_format($totalAmount, 2); ?></h3>
        <form method="POST">
            <label>Select Payment Method:</label>
            <select name="payment_method" required>
                <option value="Cash">Cash</option>
                <option value="GCash">GCash</option>
                <option value="Bank Transfer">Bank Transfer</option>
            </select>
            <button type="submit" name="place_order">Place Order</button>
        </form>
    </div>
</body>
</html>