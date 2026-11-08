<?php
require 'config.php';
if (!is_logged_in() || !is_admin()) {
    header("Location: index.php");
    exit;
}

// Add new food item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_food'])) {
    $imageName = "";
    if (!empty($_FILES['food_image']['name'])) {
        $targetDir = "uploads/";
        $imageName = basename($_FILES["food_image"]["name"]);
        $imagePath = $targetDir . $imageName;
        move_uploaded_file($_FILES["food_image"]["tmp_name"], $imagePath);
    }
    $stmt = $pdo->prepare("INSERT INTO food_items(food_name, description, price, category_id, food_image, status) VALUES (?,?,?,?,?,?)");
    $stmt->execute([$_POST['food_name'], $_POST['description'], $_POST['price'], $_POST['category_id'], $imageName, $_POST['status']]);
    header("Location: admin_dashboard.php");
    exit;
}

// Edit food item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_food'])) {
    $imageName = $_POST['old_image'];
    if (!empty($_FILES['food_image']['name'])) {
        $targetDir = "uploads/";
        $imageName = basename($_FILES["food_image"]["name"]);
        $imagePath = $targetDir . $imageName;
        move_uploaded_file($_FILES["food_image"]["tmp_name"], $imagePath);
    }
    $stmt = $pdo->prepare("UPDATE food_items SET food_name=?, description=?, price=?, category_id=?, food_image=?, status=? WHERE id=?");
    $stmt->execute([$_POST['food_name'], $_POST['description'], $_POST['price'], $_POST['category_id'], $imageName, $_POST['status'], $_POST['food_id']]);
    header("Location: admin_dashboard.php");
    exit;
}

// Delete food item
if (isset($_GET['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM food_items WHERE id = ?");
    $stmt->execute([$_GET['delete_id']]);
    header("Location: admin_dashboard.php");
    exit;
}

// Add category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $stmt = $pdo->prepare("INSERT INTO food_categories(category_name) VALUES (?)");
    $stmt->execute([$_POST['category_name']]);
    header("Location: admin_dashboard.php");
    exit;
}

// Fetch all food items and categories
$foodList = $pdo->query("SELECT food_items.*, food_categories.category_name FROM food_items LEFT JOIN food_categories ON food_items.category_id = food_categories.id")->fetchAll(PDO::FETCH_ASSOC);
$categoryList = $pdo->query("SELECT * FROM food_categories")->fetchAll(PDO::FETCH_ASSOC);
$editItem = null;
if (isset($_GET['edit_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM food_items WHERE id = ?");
    $stmt->execute([$_GET['edit_id']]);
    $editItem = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <title>Admin Dashboard | Cafeteria System</title>
</head>
<body>
    <div class="container">
        <h2>Admin Dashboard — Cafeteria Management System</h2>
        <div class="nav-links">
            <a href="admin_dashboard.php">Food Menu</a>
            <a href="admin_order_management.php">Order Management</a>
            <a href="sales_report.php">Sales Report</a>
            <a href="index.php?logout=1">Logout</a>
        </div>

        <h3>Add Food Category</h3>
        <form method="POST">
            <input type="text" name="category_name" placeholder="Category Name (e.g. Main Dish)" required>
            <button type="submit" name="add_category">Add Category</button>
        </form>

        <h3><?php echo $editItem ? "Edit Food Item" : "Add New Food Item"; ?></h3>
        <form method="POST" enctype="multipart/form-data">
            <?php if ($editItem): ?>
                <input type="hidden" name="food_id" value="<?php echo $editItem['id']; ?>">
                <input type="hidden" name="old_image" value="<?php echo $editItem['food_image']; ?>">
            <?php endif; ?>
            <input type="text" name="food_name" placeholder="Food Name" value="<?php echo $editItem ? $editItem['food_name'] : ''; ?>" required>
            <textarea name="description" placeholder="Food Description"><?php echo $editItem ? $editItem['description'] : ''; ?></textarea>
            <input type="number" step="0.01" name="price" placeholder="Price" value="<?php echo $editItem ? $editItem['price'] : ''; ?>" required>
            <select name="category_id" required>
                <?php foreach ($categoryList as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo $editItem && $editItem['category_id'] == $cat['id'] ? 'selected' : ''; ?>><?php echo $cat['category_name']; ?></option>
                <?php endforeach; ?>
            </select>
            <input type="file" name="food_image" accept="image/*">
            <select name="status" required>
                <option value="available" <?php echo $editItem && $editItem['status'] == 'available' ? 'selected' : ''; ?>>Available</option>
                <option value="unavailable" <?php echo $editItem && $editItem['status'] == 'unavailable' ? 'selected' : ''; ?>>Unavailable</option>
            </select>
            <?php if ($editItem): ?>
                <button type="submit" name="edit_food">Update Food Item</button>
                <a href="admin_dashboard.php">Cancel</a>
            <?php else: ?>
                <button type="submit" name="add_food">Add Food Item</button>
            <?php endif; ?>
        </form>

        <h3>Existing Food Items</h3>
        <table border="1" width="100%">
            <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Category</th>
                <th>Price</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($foodList as $food): ?>
            <tr>
                <td><?php if (!empty($food['food_image'])): ?><img src="uploads/<?php echo $food['food_image']; ?>" width="50"><?php endif; ?></td>
                <td><?php echo $food['food_name']; ?></td>
                <td><?php echo $food['category_name']; ?></td>
                <td>₱<?php echo number_format($food['price'], 2); ?></td>
                <td><?php echo ucfirst($food['status']); ?></td>
                <td>
                    <a href="admin_dashboard.php?edit_id=<?php echo $food['id']; ?>">Edit</a> | 
                    <a href="admin_dashboard.php?delete_id=<?php echo $food['id']; ?>" onclick="return confirm('Delete this item?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>