<?php
require 'config.php';
if (!is_logged_in() || is_admin()) {
    header("Location: index.php");
    exit;
}
$userId = $_SESSION['user_id'];

// Update item quantity
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_qty'])) {
    $newQty = $_POST['quantity'];
    if ($newQty <= 0) {
        $delStmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $delStmt->execute([$_POST['cart_id'], $userId]);
    } else {
        $updStmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
        $updStmt->execute([$newQty, $_POST['cart_id'], $userId]);
    }
    header("Location: cart.php");
    exit;
}

// Remove cart item
if (isset($_GET['remove_id'])) {
    $delStmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $delStmt->execute([$_GET['remove_id'], $userId]);
    header("Location: cart.php");
    exit;
}

// Fetch cart items
$cartStmt = $pdo->prepare("SELECT cart.*, food_items.food_name, food_items.price, food_items.food_image FROM cart JOIN food_items ON cart.food_id = food_items.id WHERE cart.user_id = ?");
$cartStmt->execute([$userId]);
$cartItems = $cartStmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total
$total = 0;
foreach ($cartItems as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <title>Shopping Cart | Cafeteria System</title>
</head>
<body>
    <div class="container">
        <h2>Your Shopping Cart</h2>
        <div class="nav-links">
            <a href="user_dashboard.php">Back to Menu</a>
            <a href="order_history.php">Order History</a>
            <a href="index.php?logout=1">Logout</a>
        </div>

        <?php if (count($cartItems) === 0): ?>
            <p>Your cart is empty. Add food items from the menu page.</p>
        <?php else: ?>
            <table border="1" width="100%">
                <tr>
                    <th>Image</th>
                    <th>Food Name</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                    <th>Action</th>
                </tr>
                <?php foreach ($cartItems as $item): ?>
                <tr>
                    <td><?php if (!empty($item['food_image'])): ?><img src="uploads/<?php echo $item['food_image']; ?>" width="60"><?php endif; ?></td>
                    <td><?php echo $item['food_name']; ?></td>
                    <td>₱<?php echo $item['price']; ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                            <input type="number" min="1" name="quantity" value="<?php echo $item['quantity']; ?>">
                            <button type="submit" name="update_qty">Update</button>
                        </form>
                    </td>
                    <td>₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                    <td><a href="cart.php?remove_id=<?php echo $item['id']; ?>" onclick="return confirm('Remove this item?')">Remove</a></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <h3>Total Amount: ₱<?php echo number_format($total, 2); ?></h3>
            <a href="checkout.php"><button>Proceed to Checkout</button></a>
        <?php endif; ?>
    </div>
</body>
</html>