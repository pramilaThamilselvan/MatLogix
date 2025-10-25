<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if(!isset($_SESSION['order_success'])) {
    header("Location: index.php");
    exit();
}

$order_id = $_SESSION['order_id'];
unset($_SESSION['order_success']);
unset($_SESSION['order_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Successful - MatLogix</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="main-container">
        <div style="text-align: center; padding: 4rem 2rem; max-width: 600px; margin: 0 auto; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <div style="font-size: 4rem; margin-bottom: 2rem; color: #27ae60;">✓</div>
            <h1 style="color: #2c3e50; margin-bottom: 1rem;">Order Placed Successfully!</h1>
            <p style="font-size: 1.2rem; margin-bottom: 1rem; color: #2c3e50;">
                Thank you for your order. Your order ID is <strong>#<?php echo $order_id; ?></strong>
            </p>
            <p style="color: #7f8c8d; margin-bottom: 2rem;">
                We will process your order and contact you soon for delivery details.
            </p>
            
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="my_orders.php" style="background: #e67e22; color: white; padding: 0.75rem 1.5rem; text-decoration: none; border-radius: 4px; font-weight: bold;">View My Orders</a>
                <a href="materials.php" style="background: #2c3e50; color: white; padding: 0.75rem 1.5rem; text-decoration: none; border-radius: 4px; font-weight: bold;">Continue Shopping</a>
                <a href="index.php" style="background: transparent; color: #e67e22; padding: 0.75rem 1.5rem; text-decoration: none; border-radius: 4px; font-weight: bold; border: 2px solid #e67e22;">Back to Home</a>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>