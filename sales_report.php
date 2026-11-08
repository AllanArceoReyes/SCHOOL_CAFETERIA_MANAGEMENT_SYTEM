<?php
require 'config.php';
if (!is_logged_in() || !is_admin()) {
    header("Location: index.php");
    exit;
}

// Get total completed sales
$totalSalesStmt = $pdo->query("SELECT SUM(total_amount) AS total_sales, COUNT(id) AS completed_orders FROM orders WHERE order_status = 'completed'");
$salesData = $totalSalesStmt->fetch(PDO::FETCH_ASSOC);

// Sales grouped by payment method
$paymentSalesStmt = $pdo->query("SELECT payment_method, SUM(total_amount) AS amount, COUNT(id) AS order_count FROM orders WHERE order_status = 'completed' GROUP BY payment_method");
$paymentSalesList = $paymentSalesStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <title>Sales Report | Admin</title>
</head>
<body>
    <div class="container">
        <h2>Admin — Cafeteria Sales Report</h2>
        <div class="nav-links">
            <a href="admin_dashboard.php">Food Menu</a>
            <a href="admin_order_management.php">Order Management</a>
            <a href="sales_report.php">Sales Report</a>
            <a href="index.php?logout=1">Logout</a>
        </div>

        <div class="summary-card">
            <h3>Overall Summary</h3>
            <p><strong>Total Completed Sales:</strong> ₱<?php echo number_format($salesData['total_sales'] ?? 0, 2); ?></p>
            <p><strong>Total Completed Orders:</strong> <?php echo $salesData['completed_orders'] ?? 0; ?></p>
        </div>

        <h3>Sales Breakdown by Payment Method</h3>
        <table border="1" width="100%">
            <tr>
                <th>Payment Method</th>
                <th>Number of Orders</th>
                <th>Total Sales</th>
            </tr>
            <?php if (count($paymentSalesList) === 0): ?>
                <tr><td colspan="3">No completed sales data available yet.</td></tr>
            <?php else: ?>
                <?php foreach ($paymentSalesList as $row): ?>
                <tr>
                    <td><?php echo $row['payment_method']; ?></td>
                    <td><?php echo $row['order_count']; ?></td>
                    <td>₱<?php echo number_format($row['amount'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </table>
    </div>
</body>
</html>