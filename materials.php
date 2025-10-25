<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'db_connect.php';

if(isset($_POST['add_to_cart']) && isset($_SESSION['customer_id'])) {
    $material_id = $_POST['material_id'];
    $quantity = $_POST['quantity'] ?? 1;
    
    if(!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }
    
    if(isset($_SESSION['cart'][$material_id])) {
        $_SESSION['cart'][$material_id] += $quantity;
    } else {
        $_SESSION['cart'][$material_id] = $quantity;
    }
    
    $_SESSION['success'] = "Item added to cart successfully!";
    header("Location: materials.php");
    exit();
}

$sql = "SELECT * FROM materials WHERE quantity > 0 ORDER BY category, name";
$result = mysqli_query($conn, $sql);
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
    
    <div class="main-container">
        <div class="materials">
            <h2>Building Materials</h2>
            
            <?php if(isset($_SESSION['success'])): ?>
                <div class="success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            
            <div class="materials-grid">
                <?php
                if(mysqli_num_rows($result) > 0) {
                    while($row = mysqli_fetch_assoc($result)) {
                        ?>
                        <div class="material-card">
                            <div class="material-image">
                                <img src="<?php echo $row['image_url']; ?>" 
                                     alt="<?php echo htmlspecialchars($row['name']); ?>"
                                     onerror="this.src='images/materials/default.jpg'">
                            </div>
                            <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                            <p><strong><?php echo htmlspecialchars($row['category']); ?></strong></p>
                            <p class="material-description"><?php echo htmlspecialchars($row['description']); ?></p>
                            <!-- FIXED: Added unit type display -->
                            <p class="material-price">Rs. <?php echo number_format($row['price'], 2); ?> <?php echo htmlspecialchars($row['unit_type']); ?></p>
                            <p>Stock: <?php echo $row['quantity']; ?></p>
                            
                            <?php if(isset($_SESSION['customer_id'])): ?>
                            <form method="POST">
                                <input type="hidden" name="material_id" value="<?php echo $row['material_id']; ?>">
                                <div class="form-group">
                                    <label>Quantity (<?php echo htmlspecialchars($row['unit_type']); ?>):</label>
                                    <input type="number" name="quantity" value="1" min="1" max="<?php echo $row['quantity']; ?>" style="width: 80px;">
                                </div>
                                <button type="submit" name="add_to_cart" class="submit-btn">Add to Cart</button>
                            </form>
                            <?php else: ?>
                            <a href="login.php" class="view-btn">Login to Purchase</a>
                            <?php endif; ?>
                        </div>
                        <?php
                    }
                } else {
                    echo '<p>No materials available.</p>';
                }
                ?>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var currentPage = window.location.pathname.split('/').pop();
        var navLinks = document.querySelectorAll('.nav-link');
        
        navLinks.forEach(function(link) {
            if (link.getAttribute('href') === currentPage) {
                link.classList.add('active');
            }
        });
    });
    </script>
</body>
</html>
<?php 
mysqli_close($conn);
?>