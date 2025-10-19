<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'db_connect.php';

if(!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

if(isset($_POST['update_cart'])) {
    foreach($_POST['quantity'] as $material_id => $quantity) {
        if($quantity == 0) {
            unset($_SESSION['cart'][$material_id]);
        } else {
            $_SESSION['cart'][$material_id] = $quantity;
        }
    }
    $_SESSION['success'] = "Cart updated successfully!";
    header("Location: cart.php");
    exit();
}

if(isset($_GET['remove'])) {
    $material_id = $_GET['remove'];
    unset($_SESSION['cart'][$material_id]);
    $_SESSION['success'] = "Item removed from cart!";
    header("Location: cart.php");
    exit();
}

$total = 0;
$cart_items = array();

if(isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $material_ids = implode(",", array_keys($_SESSION['cart']));
    $sql = "SELECT * FROM materials WHERE material_id IN ($material_ids)";
    $result = mysqli_query($conn, $sql);
    
    while($row = mysqli_fetch_assoc($result)) {
        $quantity = $_SESSION['cart'][$row['material_id']];
        $subtotal = $row['price'] * $quantity;
        $total += $subtotal;
        
        $cart_items[] = array(
            'material' => $row,
            'quantity' => $quantity,
            'subtotal' => $subtotal
        );
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - MatLogix</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="main-container">
        <h2>Shopping Cart</h2>
        
        <?php if(isset($_SESSION['success'])): ?>
            <div class="success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <?php if(empty($cart_items)): ?>
            <div class="empty-cart">
                <h3>Your cart is empty</h3>
                <p>Browse our materials and add items to your cart.</p>
                <a href="materials.php" class="view-all-btn">Browse Materials</a>
            </div>
        <?php else: ?>
            <form method="POST">
                <div class="cart-items">
                    <?php foreach($cart_items as $item): ?>
                    <div class="cart-item">
                        <div class="item-info">
                            <h3><?php echo htmlspecialchars($item['material']['name']); ?></h3>
                            <p><?php echo htmlspecialchars($item['material']['category']); ?></p>
                            <p>Rs. <?php echo number_format($item['material']['price'], 2); ?> (<?php echo htmlspecialchars($item['material']['unit_type']); ?>)</p>
                        </div>
                        <div class="item-controls">
                            <div class="quantity-control">
                                <label>Quantity:</label>
                                <input type="number" name="quantity[<?php echo $item['material']['material_id']; ?>]" 
                                       value="<?php echo $item['quantity']; ?>" min="0" max="<?php echo $item['material']['quantity']; ?>">
                            </div>
                            <p class="subtotal">Rs. <?php echo number_format($item['subtotal'], 2); ?></p>
                            <a href="cart.php?remove=<?php echo $item['material']['material_id']; ?>" class="logout-btn">Remove</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="cart-summary">
                    <div class="total">
                        <h3>Total: Rs. <?php echo number_format($total, 2); ?></h3>
                    </div>
                    <div class="cart-actions">
                        <button type="submit" name="update_cart" class="view-btn">Update Cart</button>
                        <a href="checkout.php" class="cta-button">Proceed to Checkout</a>
                    </div>
                </div>
            </form>
        <?php endif; ?>
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