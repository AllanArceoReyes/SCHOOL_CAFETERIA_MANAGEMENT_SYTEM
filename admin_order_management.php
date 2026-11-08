<?php
require 'config.php';
if (!is_logged_in() || !is_admin()) {
    header("Location: index.php");
    exit;
}

// Update order status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $stmt = $pdo->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
    $stmt->execute([$_POST['order_status'], $_POST['order_id']]);
    header("Location: admin_order_management.php");
    exit;
}

// Fetch all orders with customer name
$orderList = $pdo->query("SELECT orders.*, users.full_name FROM orders JOIN users ON orders.user_id = users.id ORDER BY order_date DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <title>Order Management | Admin</title>
</head>
<body>
    <div class="container">
        <h2>Admin — Order Management</h2>
        <div class="nav-links">
            <a href="admin_dashboard.php">Food Menu</a>
            <a href="admin_order_management.php">Order Management</a>
            <a href="sales_report.php">Sales Report</a>
            <a href="index.php?logout=1">Logout</a>
        </div>

        <?php if (count($orderList) === 0): ?>
            <p>No orders received yet.</p>
        <?php else: ?>
            <table border="1" width="100%">
                <tr>
                    <th>Order ID</th>
                    <th>Customer Name</th>
                    <th>Total Amount</th>
                    <th>Payment</th>
                    <th>Order Date</th>
                    <th>Current Status</th>
                    <th>Update Status</th>
                </tr>
                <?php foreach ($orderList as $order): ?>
                <tr>
                    <td><?php echo $order['id']; ?></td>
                    <td><?php echo $order['full_name']; ?></td>
                    <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                    <td><?php echo $order['payment_method']; ?></td>
                    <td><?php echo $order['order_date']; ?></td>
                    <td><?php echo $order['order_status']; ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <select name="order_status">
                                <option value="pending" <?php echo $order['order_status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="preparing" <?php echo $order['order_status'] == 'preparing' ? 'selected' : ''; ?>>Preparing</option>
                                <option value="ready" <?php echo $order['order_status'] == 'ready' ? 'selected' : ''; ?>>Ready</option>
                                <option value="completed" <?php echo $order['order_status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?php echo $order['order_status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                            <button type="submit" name="update_status">Save</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>