<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'db_connect.php';

if(!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];

$sql = "SELECT o.*, COUNT(od.order_detail_id) as item_count 
        FROM orders o 
        LEFT JOIN order_details od ON o.order_id = od.order_id 
        WHERE o.customer_id = $customer_id 
        GROUP BY o.order_id 
        ORDER BY o.order_date DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - MatLogix</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="main-container">
        <h1 style="text-align: center; color: #2c3e50; margin-bottom: 2rem;">My Orders</h1>
        
        <?php if(mysqli_num_rows($result) > 0): ?>
            <div class="orders-list">
                <?php while($order = mysqli_fetch_assoc($result)): ?>
                <div class="order-card" style="background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <div>
                            <h3 style="color: #2c3e50; margin-bottom: 0.5rem;">Order #<?php echo $order['order_id']; ?></h3>
                            <p style="color: #666; margin-bottom: 0.5rem;">
                                <strong>Date:</strong> <?php echo date('F j, Y', strtotime($order['order_date'])); ?>
                            </p>
                            <p style="color: #666; margin-bottom: 0.5rem;">
                                <strong>Items:</strong> <?php echo $order['item_count']; ?> items
                            </p>
                            <p style="color: #666;">
                                <strong>Payment:</strong> <?php echo htmlspecialchars($order['payment_method']); ?>
                            </p>
                        </div>
                        <div style="text-align: right;">
                            <p style="font-size: 1.3rem; font-weight: bold; color: #27ae60; margin-bottom: 0.5rem;">
                                Rs. <?php echo number_format($order['total_amount'], 2); ?>
                            </p>
                            <span style="background: #e67e22; color: white; padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.9rem; font-weight: bold;">
                                <?php echo htmlspecialchars($order['status']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div style="border-top: 1px solid #eee; padding-top: 1rem;">
                        <h4 style="color: #2c3e50; margin-bottom: 1rem;">Order Items:</h4>
                        <?php
                        $order_id = $order['order_id'];
                        $detail_sql = "SELECT od.*, m.name, m.unit_type 
                                      FROM order_details od 
                                      JOIN materials m ON od.material_id = m.material_id 
                                      WHERE od.order_id = $order_id";
                        $detail_result = mysqli_query($conn, $detail_sql);
                        
                        while($item = mysqli_fetch_assoc($detail_result)):
                        ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; border-bottom: 1px solid #f8f9fa;">
                            <div>
                                <p style="font-weight: bold; color: #2c3e50; margin-bottom: 0.2rem;"><?php echo htmlspecialchars($item['name']); ?></p>
                                <p style="color: #666; font-size: 0.9rem;">Quantity: <?php echo $item['quantity']; ?> </p>
                            </div>
                            <p style="font-weight: bold; color: #27ae60;">
                                Rs. <?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                            </p>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div style="text-align: center; padding: 4rem 2rem; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <h2 style="color: #2c3e50; margin-bottom: 1rem;">No Orders Yet</h2>
                <p style="color: #666; margin-bottom: 2rem;">You haven't placed any orders yet.</p>
                <a href="materials.php" class="view-all-btn">Browse Materials</a>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
<?php mysqli_close($conn); ?>