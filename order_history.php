<?php
require 'config.php';
if (!is_logged_in() || is_admin()) {
    header("Location: index.php");
    exit;
}
$userId = $_SESSION['user_id'];

// Fetch user's full order history
$orderStmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
$orderStmt->execute([$userId]);
$orderList = $orderStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <title>Order History | Cafeteria System</title>
</head>
<body>
    <div class="container">
        <h2>Your Order History</h2>
        <div class="nav-links">
            <a href="user_dashboard.php">Back to Menu</a>
            <a href="cart.php">Shopping Cart</a>
            <a href="index.php?logout=1">Logout</a>
        </div>

        <?php if (count($orderList) === 0): ?>
            <p>You have no previous orders yet.</p>
        <?php else: ?>
            <table border="1" width="100%">
                <tr>
                    <th>Order ID</th>
                    <th>Order Date</th>
                    <th>Total Amount</th>
                    <th>Payment Method</th>
                    <th>Status</th>
                </tr>
                <?php foreach ($orderList as $order): ?>
                <tr>
                    <td><?php echo $order['id']; ?></td>
                    <td><?php echo $order['order_date']; ?></td>
                    <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                    <td><?php echo $order['payment_method']; ?></td>
                    <td><?php echo $order['order_status']; ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>