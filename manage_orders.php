<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include '../db_connect.php';

if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if(isset($_POST['update_status'])) {
    $order_id = mysqli_real_escape_string($conn, $_POST['order_id']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $sql = "UPDATE orders SET status = '$status' WHERE order_id = $order_id";
    
    if(mysqli_query($conn, $sql)) {
        $_SESSION['success'] = "Order #$order_id status updated to $status successfully!";
    } else {
        $_SESSION['error'] = "Error updating order status: " . mysqli_error($conn);
    }
    
    header("Location: manage_orders.php");
    exit();
}

$sql = "SELECT o.*, c.name as customer_name, c.email, c.phone 
        FROM orders o 
        JOIN customers c ON o.customer_id = c.customer_id 
        ORDER BY o.order_date DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - MatLogix</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header style="background: #2c3e50; color: white; padding: 1rem 0;">
        <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
            <nav style="display: flex; justify-content: space-between; align-items: center;">
                <h1>MatLogix Admin</h1>
                <div style="display: flex; gap: 2rem;">
                    <a href="dashboard.php" style="color: white; text-decoration: none; padding: 0.5rem 1rem; border-radius: 5px; transition: background 0.3s;">Dashboard</a>
                    <a href="manage_materials.php" style="color: white; text-decoration: none; padding: 0.5rem 1rem; border-radius: 5px; transition: background 0.3s;">Materials</a>
                    <a href="manage_orders.php" style="color: white; text-decoration: none; padding: 0.5rem 1rem; border-radius: 5px; background: #e67e22; font-weight: bold;">Orders</a>
                    <a href="../logout.php" style="color: white; text-decoration: none; padding: 0.5rem 1rem; border-radius: 5px; background: #e74c3c; font-weight: bold;">Logout</a>
                </div>
            </nav>
        </div>
    </header>

    <div style="max-width: 1200px; margin: 0 auto; padding: 2rem 20px;">
        <h1 style="color: #2c3e50; margin-bottom: 2rem;">Manage Orders</h1>
        
        <?php if(isset($_SESSION['success'])): ?>
            <div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 5px; margin-bottom: 1rem; border: 1px solid #c3e6cb;">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 5px; margin-bottom: 1rem; border: 1px solid #f5c6cb;">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <div style="background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="padding: 1rem; text-align: left; background: #f8f9fa; font-weight: bold; border-bottom: 1px solid #ddd;">Order ID</th>
                        <th style="padding: 1rem; text-align: left; background: #f8f9fa; font-weight: bold; border-bottom: 1px solid #ddd;">Customer</th>
                        <th style="padding: 1rem; text-align: left; background: #f8f9fa; font-weight: bold; border-bottom: 1px solid #ddd;">Amount</th>
                        <th style="padding: 1rem; text-align: left; background: #f8f9fa; font-weight: bold; border-bottom: 1px solid #ddd;">Payment Method</th>
                        <th style="padding: 1rem; text-align: left; background: #f8f9fa; font-weight: bold; border-bottom: 1px solid #ddd;">Status</th>
                        <th style="padding: 1rem; text-align: left; background: #f8f9fa; font-weight: bold; border-bottom: 1px solid #ddd;">Date</th>
                        <th style="padding: 1rem; text-align: left; background: #f8f9fa; font-weight: bold; border-bottom: 1px solid #ddd;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($result) > 0): ?>
                        <?php while($order = mysqli_fetch_assoc($result)): 
                            $order_id = $order['order_id'];
                            $items_sql = "SELECT od.quantity, od.price, m.name 
                                        FROM order_details od 
                                        JOIN materials m ON od.material_id = m.material_id 
                                        WHERE od.order_id = $order_id";
                            $items_result = mysqli_query($conn, $items_sql);
                            $items_count = mysqli_num_rows($items_result);
                        ?>
                        <tr>
                            <td style="padding: 1rem; border-bottom: 1px solid #ddd;">#<?php echo $order['order_id']; ?></td>
                            <td style="padding: 1rem; border-bottom: 1px solid #ddd;">
                                <strong><?php echo htmlspecialchars($order['customer_name']); ?></strong>
                                <div style="background: #f8f9fa; padding: 0.5rem; border-radius: 5px; margin-top: 0.5rem;">
                                    <small>Email: <?php echo htmlspecialchars($order['email']); ?></small><br>
                                    <small>Phone: <?php echo htmlspecialchars($order['phone']); ?></small>
                                </div>
                            </td>
                            <td style="padding: 1rem; border-bottom: 1px solid #ddd;">Rs. <?php echo number_format($order['total_amount'], 2); ?></td>
                            <td style="padding: 1rem; border-bottom: 1px solid #ddd;"><?php echo htmlspecialchars($order['payment_method']); ?></td>
                            <td style="padding: 1rem; border-bottom: 1px solid #ddd;">
                                <?php
                                $status_class = '';
                                if($order['status'] == 'Pending') $status_class = 'background: #fff3cd; color: #856404;';
                                elseif($order['status'] == 'Confirmed') $status_class = 'background: #d1ecf1; color: #0c5460;';
                                elseif($order['status'] == 'Delivered') $status_class = 'background: #d4edda; color: #155724;';
                                ?>
                                <span style="padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.8rem; font-weight: bold; display: inline-block; <?php echo $status_class; ?>">
                                    <?php echo htmlspecialchars($order['status']); ?>
                                </span>
                            </td>
                            <td style="padding: 1rem; border-bottom: 1px solid #ddd;"><?php echo date('M j, Y g:i A', strtotime($order['order_date'])); ?></td>
                            <td style="padding: 1rem; border-bottom: 1px solid #ddd;">
                                <button onclick="toggleItems(<?php echo $order['order_id']; ?>)" 
                                        style="background: #e67e22; color: white; border: none; padding: 0.5rem 1rem; border-radius: 5px; cursor: pointer; margin-bottom: 0.5rem;">
                                    View Items (<?php echo $items_count; ?>)
                                </button>
                                
                                <div id="items-<?php echo $order['order_id']; ?>" style="background: #f8f9fa; padding: 1rem; border-radius: 5px; margin-top: 0.5rem; display: none;">
                                    <?php while($item = mysqli_fetch_assoc($items_result)): ?>
                                        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #dee2e6;">
                                            <span><?php echo htmlspecialchars($item['name']); ?></span>
                                            <span>Qty: <?php echo $item['quantity']; ?></span>
                                            <span>Rs. <?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                                        </div>
                                    <?php endwhile; ?>
                                </div>

                                <form method="POST" style="display: flex; gap: 0.5rem; align-items: center; margin-top: 0.5rem;">
                                    <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                    <select name="status" style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 5px; background: white; cursor: pointer;">
                                        <option value="Pending" <?php echo $order['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Confirmed" <?php echo $order['status'] == 'Confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                        <option value="Delivered" <?php echo $order['status'] == 'Delivered' ? 'selected' : ''; ?>>Delivered</option>
                                    </select>
                                    <button type="submit" name="update_status" style="padding: 0.5rem 1rem; background: #e67e22; color: white; border: none; border-radius: 5px; cursor: pointer;">Update</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem;">
                                No orders found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="footer">
        <div class="footer-content">
            <p>&copy; 2025 MatLogix. Admin Dashboard</p>
        </div>
    </div>

    <script>
        function toggleItems(orderId) {
            var itemsDiv = document.getElementById('items-' + orderId);
            if (itemsDiv.style.display === 'block') {
                itemsDiv.style.display = 'none';
            } else {
                itemsDiv.style.display = 'block';
            }
        }
    </script>
</body>
</html>
<?php mysqli_close($conn); ?>