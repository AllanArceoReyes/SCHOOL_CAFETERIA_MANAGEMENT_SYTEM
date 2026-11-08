<?php
require 'config.php';
if (!is_logged_in() || is_admin()) {
    header("Location: index.php");
    exit;
}

$searchKeyword = "";
if (isset($_GET['search'])) {
    $searchKeyword = $_GET['search'];
    $stmt = $pdo->prepare("SELECT food_items.*, food_categories.category_name FROM food_items LEFT JOIN food_categories ON food_items.category_id = food_categories.id WHERE food_items.status = 'available' AND food_items.food_name LIKE ?");
    $stmt->execute(["%$searchKeyword%"]);
    $foodList = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $foodList = $pdo->query("SELECT food_items.*, food_categories.category_name FROM food_items LEFT JOIN food_categories ON food_items.category_id = food_categories.id WHERE food_items.status = 'available'")->fetchAll(PDO::FETCH_ASSOC);
}

// Add item to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $userId = $_SESSION['user_id'];
    $foodId = $_POST['food_id'];
    // Check if item already exists in user's cart
    $checkCart = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND food_id = ?");
    checkCart->execute([$userId, $foodId]);
    if ($checkCart->rowCount() > 0) {
        $updateCart = $pdo->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND food_id = ?");
        $updateCart->execute([$userId, $foodId]);
    } else {
        $insertCart = $pdo->prepare("INSERT INTO cart(user_id, food_id, quantity) VALUES (?, ?, 1)");
        $insertCart->execute([$userId, $foodId]);
    }
    header("Location: user_dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <title>User Dashboard | Cafeteria System</title>
</head>
<body>
    <div class="container">
        <h2>Welcome <?php echo $_SESSION['full_name']; ?>!</h2>
        <div class="nav-links">
            <a href="user_dashboard.php">Menu</a>
            <a href="cart.php">Shopping Cart</a>
            <a href="order_history.php">Order History</a>
            <a href="index.php?logout=1">Logout</a>
        </div>

        <form method="GET">
            <input type="text" name="search" placeholder="Search food name..." value="<?php echo $searchKeyword; ?>">
            <button type="submit">Search</button>
        </form>

        <h3>Available Food Menu</h3>
        <div class="food-grid">
            <?php if (count($foodList) === 0): ?>
                <p>No matching food items found.</p>
            <?php else: ?>
                <?php foreach ($foodList as $food): ?>
                    <div class="food-card">
                        <?php if (!empty($food['food_image'])): ?>
                            <img src="uploads/<?php echo $food['food_image']; ?>" width="120">
                        <?php endif; ?>
                        <h4><?php echo $food['food_name']; ?></h4>
                        <p>Category: <?php echo $food['category_name']; ?></p>
                        <p><?php echo $food['description']; ?></p>
                        <p>Price: ₱<?php echo $food['price']; ?></p>
                        <form method="POST">
                            <input type="hidden" name="food_id" value="<?php echo $food['id']; ?>">
                            <button type="submit" name="add_to_cart">Add to Cart</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>