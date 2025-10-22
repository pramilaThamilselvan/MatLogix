<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include '../db_connect.php';

if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$customers_sql = "SELECT COUNT(*) as total FROM customers";
$customers_result = mysqli_query($conn, $customers_sql);
$customers = mysqli_fetch_assoc($customers_result)['total'];

$materials_sql = "SELECT COUNT(*) as total FROM materials";
$materials_result = mysqli_query($conn, $materials_sql);
$materials_count = mysqli_fetch_assoc($materials_result)['total'];

$orders_sql = "SELECT COUNT(*) as total FROM orders";
$orders_result = mysqli_query($conn, $orders_sql);
$orders = mysqli_fetch_assoc($orders_result)['total'];

$revenue_sql = "SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE status = 'Delivered'";
$revenue_result = mysqli_query($conn, $revenue_sql);
$revenue = mysqli_fetch_assoc($revenue_result)['total'];

$recent_orders_sql = "SELECT o.*, c.name as customer_name 
                      FROM orders o 
                      JOIN customers c ON o.customer_id = c.customer_id 
                      ORDER BY o.order_date DESC 
                      LIMIT 5";
$recent_orders_result = mysqli_query($conn, $recent_orders_sql);

$low_stock_sql = "SELECT * FROM materials WHERE quantity < 10 ORDER BY quantity ASC LIMIT 5";
$low_stock_result = mysqli_query($conn, $low_stock_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - MatLogix</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header style="background: #2c3e50; color: white; padding: 1rem 0;">
        <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
            <nav style="display: flex; justify-content: space-between; align-items: center;">
                <h1>MatLogix Admin Dashboard</h1>
                <div style="display: flex; gap: 2rem; align-items: center;">
                    <a href="dashboard.php" style="color: white; text-decoration: none; padding: 0.5rem 1rem; border-radius: 5px; background: #e67e22; font-weight: bold;">Dashboard</a>
                    <a href="manage_materials.php" style="color: white; text-decoration: none; padding: 0.5rem 1rem; border-radius: 5px; transition: background 0.3s;">Materials</a>
                    <a href="manage_orders.php" style="color: white; text-decoration: none; padding: 0.5rem 1rem; border-radius: 5px; transition: background 0.3s;">Orders</a>
                    <span style="color: #ecf0f1;">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    <a href="../logout.php" style="background: #e74c3c; color: white; padding: 0.5rem 1rem; text-decoration: none; border-radius: 5px; font-weight: bold;">Logout</a>
                </div>
            </nav>
        </div>
    </header>

    <div style="max-width: 1200px; margin: 0 auto; padding: 2rem 20px;">
        <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 2rem;">
            <h1 style="color: #2c3e50; margin-bottom: 0.5rem;">Welcome back, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</h1>
            <p style="color: #666;">Here's what's happening with your store today.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin: 2rem 0;">
            <div style="background: white; padding: 2rem; border-radius: 10px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-left: 4px solid #e67e22;">
                <div style="font-size: 2.5rem; font-weight: bold; color: #e67e22; margin-bottom: 0.5rem;"><?php echo $customers; ?></div>
                <div style="color: #666; font-size: 1.1rem;">Total Customers</div>
            </div>
            <div style="background: white; padding: 2rem; border-radius: 10px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-left: 4px solid #27ae60;">
                <div style="font-size: 2.5rem; font-weight: bold; color: #27ae60; margin-bottom: 0.5rem;"><?php echo $materials_count; ?></div>
                <div style="color: #666; font-size: 1.1rem;">Materials</div>
            </div>
            <div style="background: white; padding: 2rem; border-radius: 10px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-left: 4px solid #3498db;">
                <div style="font-size: 2.5rem; font-weight: bold; color: #3498db; margin-bottom: 0.5rem;"><?php echo $orders; ?></div>
                <div style="color: #666; font-size: 1.1rem;">Total Orders</div>
            </div>
            <div style="background: white; padding: 2rem; border-radius: 10px; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-left: 4px solid #9b59b6;">
                <div style="font-size: 2.5rem; font-weight: bold; color: #9b59b6; margin-bottom: 0.5rem;">Rs. <?php echo number_format($revenue, 2); ?></div>
                <div style="color: #666; font-size: 1.1rem;">Total Revenue</div>
            </div>
        </div>

        <div style="background: white; padding: 2rem; border-radius: 10px; margin-bottom: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid #f4f4f4;">
                <h2 style="color: #2c3e50; margin: 0;">Recent Orders</h2>
                <a href="manage_orders.php" style="background: #e67e22; color: white; padding: 0.5rem 1rem; text-decoration: none; border-radius: 5px; font-weight: bold;">View All Orders</a>
            </div>
            
            <?php if(mysqli_num_rows($recent_orders_result) > 0): ?>
                <div>
                    <?php while($order = mysqli_fetch_assoc($recent_orders_result)): 
                        $order_id = $order['order_id'];
                        $items_sql = "SELECT od.quantity, od.price, m.name 
                                    FROM order_details od 
                                    JOIN materials m ON od.material_id = m.material_id 
                                    WHERE od.order_id = $order_id";
                        $items_result = mysqli_query($conn, $items_sql);
                        $items_count = mysqli_num_rows($items_result);
                    ?>
                    <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; margin-bottom: 1rem; border-left: 4px solid #e67e22;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                            <div style="flex: 1;">
                                <h3 style="color: #2c3e50; margin-bottom: 0.5rem;">Order #<?php echo $order['order_id']; ?></h3>
                                <p style="margin-bottom: 0.5rem;"><strong>Customer:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                                <p style="margin-bottom: 0.5rem;"><strong>Amount:</strong> Rs. <?php echo number_format($order['total_amount'], 2); ?></p>
                                <p style="margin-bottom: 0.5rem;"><strong>Items:</strong> <?php echo $items_count; ?> item(s)</p>
                                <p style="margin-bottom: 1rem;"><strong>Payment:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
                                
                                <div style="background: white; padding: 1rem; border-radius: 5px; border: 1px solid #dee2e6;">
                                    <strong>Ordered Items:</strong>
                                    <?php while($item = mysqli_fetch_assoc($items_result)): ?>
                                        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #dee2e6;">
                                            <span><?php echo htmlspecialchars($item['name']); ?></span>
                                            <span>Qty: <?php echo $item['quantity']; ?></span>
                                            <span>Rs. <?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <?php
                                $status_class = '';
                                if($order['status'] == 'Pending') $status_class = 'background: #fff3cd; color: #856404;';
                                elseif($order['status'] == 'Confirmed') $status_class = 'background: #d1ecf1; color: #0c5460;';
                                elseif($order['status'] == 'Delivered') $status_class = 'background: #d4edda; color: #155724;';
                                else $status_class = 'background: #f8d7da; color: #721c24;';
                                ?>
                                <span style="padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.8rem; font-weight: bold; <?php echo $status_class; ?>">
                                    <?php echo htmlspecialchars($order['status']); ?>
                                </span>
                                <p style="margin-top: 0.5rem; color: #666;">
                                    <?php echo date('M j, Y g:i A', strtotime($order['order_date'])); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p>No recent orders found.</p>
            <?php endif; ?>
        </div>

        <div style="background: white; padding: 2rem; border-radius: 10px; margin-bottom: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid #f4f4f4;">
                <h2 style="color: #2c3e50; margin: 0;">Low Stock Alert</h2>
                <a href="manage_materials.php" style="background: #e67e22; color: white; padding: 0.5rem 1rem; text-decoration: none; border-radius: 5px; font-weight: bold;">Manage Materials</a>
            </div>
            
            <?php if(mysqli_num_rows($low_stock_result) > 0): ?>
                <div>
                    <?php while($material = mysqli_fetch_assoc($low_stock_result)): ?>
                    <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h3 style="color: #2c3e50; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($material['name']); ?></h3>
                            <p style="color: #666; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($material['category']); ?></p>
                            <p style="color: #e74c3c; font-weight: bold;">Only <?php echo $material['quantity']; ?> left in stock</p>
                        </div>
                        <a href="edit_material.php?id=<?php echo $material['material_id']; ?>" style="background: #e67e22; color: white; padding: 0.5rem 1rem; text-decoration: none; border-radius: 5px; font-weight: bold;">
                            Update Stock
                        </a>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p>All materials have sufficient stock.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="footer">
        <div class="footer-content">
            <p>&copy; 2025 MatLogix. Admin Dashboard</p>
        </div>
    </div>
</body>
</html>
<?php mysqli_close($conn); ?>