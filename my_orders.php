<?php
session_start();
include 'db_connect.php';

// Handle add to cart
if(isset($_POST['add_to_cart']) && isset($_SESSION['customer_id'])) {
    $material_id = $_POST['material_id'];
    $quantity = $_POST['quantity'] ?? 1;
    
    // Initialize cart if not exists
    if(!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Add item to cart
    if(isset($_SESSION['cart'][$material_id])) {
        $_SESSION['cart'][$material_id] += $quantity;
    } else {
        $_SESSION['cart'][$material_id] = $quantity;
    }
    
    $_SESSION['success'] = "Item added to cart successfully!";
    header("Location: materials.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Materials - MatLogix</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main class="container">
        <h1>Building Materials</h1>
        
        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 5px; margin-bottom: 1rem; border: 1px solid #c3e6cb;">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <div class="materials-grid">
            <?php
            $sql = "SELECT * FROM materials WHERE stock > 0 ORDER BY category, name";
            $result = mysqli_query($conn, $sql);
            
            if(mysqli_num_rows($result) > 0) {
                while($row = mysqli_fetch_assoc($result)) {
                    // Image handling with cache busting
                    $image_file = $row['image'];
                    $image_path = "images/materials/" . $image_file;
                    $default_path = "images/materials/default.jpg";
                    $cache_buster = "?v=1.0";
                    
                    // Check if image exists, otherwise use default
                    if (!file_exists($image_path) || $image_file == 'default.jpg') {
                        $display_image = $default_path . $cache_buster;
                    } else {
                        $display_image = $image_path . $cache_buster;
                    }
                    ?>
                    <div class="material-card">
                        <div class="material-image">
                            <img src="<?php echo $display_image; ?>" 
                                 alt="<?php echo $row['name']; ?>"
                                 onerror="this.src='<?php echo $default_path . $cache_buster; ?>'">
                        </div>
                        <div class="material-info">
                            <h3><?php echo $row['name']; ?></h3>
                            <p class="category"><?php echo $row['category']; ?></p>
                            <p class="description"><?php echo $row['description']; ?></p>
                            <p class="price">Rs. <?php echo number_format($row['price'], 2); ?></p>
                            <p class="unit"><?php echo $row['unit_type']; ?></p>
                            <p class="stock <?php echo $row['stock'] < 10 ? 'low-stock' : ''; ?>">
                                Stock: <?php echo $row['stock']; ?>
                            </p>
                            
                            <?php if(isset($_SESSION['customer_id'])): ?>
                            <form method="POST" class="add-to-cart-form">
                                <input type="hidden" name="material_id" value="<?php echo $row['material_id']; ?>">
                                <div class="quantity-selector">
                                    <label for="quantity_<?php echo $row['material_id']; ?>">Quantity:</label>
                                    <input type="number" name="quantity" id="quantity_<?php echo $row['material_id']; ?>" 
                                           value="1" min="1" max="<?php echo $row['stock']; ?>">
                                </div>
                                <button type="submit" name="add_to_cart" class="btn btn-primary btn-full">
                                    Add to Cart
                                </button>
                            </form>
                            <?php else: ?>
                            <a href="login.php" class="btn btn-secondary btn-full">Login to Purchase</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "<p class='no-materials' style='text-align: center; padding: 2rem; background: white; border-radius: 10px;'>No materials available at the moment.</p>";
            }
            ?>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2024 MatLogix. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
<?php mysqli_close($conn); ?>