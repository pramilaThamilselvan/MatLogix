<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'db_connect.php';

if(!isset($_SESSION['customer_id'])) {
    header("Location: login.php");
    exit();
}

if(!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header("Location: materials.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$sql = "SELECT * FROM customers WHERE customer_id = $customer_id";
$result = mysqli_query($conn, $sql);
$customer = mysqli_fetch_assoc($result);

$total = 0;
$cart_items = array();

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

$error = '';

if(isset($_POST['place_order'])) {
    $payment_method = mysqli_real_escape_string($conn, $_POST['payment_method']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    
    mysqli_begin_transaction($conn);
    
    try {
        $sql = "INSERT INTO orders (customer_id, total_amount, payment_method) VALUES ($customer_id, $total, '$payment_method')";
        mysqli_query($conn, $sql);
        $order_id = mysqli_insert_id($conn);
        
        foreach($cart_items as $item) {
            $material_id = $item['material']['material_id'];
            $quantity = $item['quantity'];
            $price = $item['material']['price'];
            
            $sql = "INSERT INTO order_details (order_id, material_id, quantity, price) 
                    VALUES ($order_id, $material_id, $quantity, $price)";
            mysqli_query($conn, $sql);
            
            $sql = "UPDATE materials SET quantity = quantity - $quantity WHERE material_id = $material_id";
            mysqli_query($conn, $sql);
        }
        
        if($payment_method == 'Card Payment') {
            $card_number = mysqli_real_escape_string($conn, $_POST['card_number']);
            $card_holder = mysqli_real_escape_string($conn, $_POST['card_holder']);
            
            $sql = "INSERT INTO payments (order_id, payment_method, amount, card_number, card_holder_name, status) 
                    VALUES ($order_id, '$payment_method', $total, '$card_number', '$card_holder', 'Completed')";
        } else {
            $sql = "INSERT INTO payments (order_id, payment_method, amount) 
                    VALUES ($order_id, '$payment_method', $total)";
        }
        mysqli_query($conn, $sql);
        
        mysqli_commit($conn);
        
        unset($_SESSION['cart']);
        $_SESSION['order_success'] = true;
        $_SESSION['order_id'] = $order_id;
        
        header("Location: order_success.php");
        exit();
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error = "Order failed: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - MatLogix</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="main-container">
        <h1 style="text-align: center; color: #2c3e50; margin-bottom: 2rem;">Checkout</h1>
        
        <?php if(isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem; margin: 2rem 0;">
            <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <h2 style="color: #2c3e50; margin-bottom: 1.5rem;">Order Summary</h2>
                <?php foreach($cart_items as $item): ?>
                <div style="padding: 1rem 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h4 style="color: #2c3e50; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($item['material']['name']); ?></h4>
                        <p style="color: #666;">Quantity: <?php echo $item['quantity']; ?></p>
                    </div>
                    <p style="font-weight: bold; color: #27ae60;">Rs. <?php echo number_format($item['subtotal'], 2); ?></p>
                </div>
                <?php endforeach; ?>
                <div style="margin-top: 1rem; padding-top: 1rem; border-top: 2px solid #e67e22; text-align: right; font-size: 1.2rem; font-weight: bold;">
                    <h3 style="color: #2c3e50;">Total: Rs. <?php echo number_format($total, 2); ?></h3>
                </div>
            </div>
            
            <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <form method="POST" id="checkoutForm">
                    <h2 style="color: #2c3e50; margin-bottom: 1.5rem;">Delivery Information</h2>
                    
                    <div class="form-group">
                        <label for="name">Full Name:</label>
                        <input type="text" id="name" value="<?php echo htmlspecialchars($customer['name']); ?>" readonly style="background: #f8f9fa;">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" value="<?php echo htmlspecialchars($customer['email']); ?>" readonly style="background: #f8f9fa;">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone:</label>
                        <input type="text" id="phone" value="<?php echo htmlspecialchars($customer['phone']); ?>" readonly style="background: #f8f9fa;">
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Delivery Address:</label>
                        <textarea id="address" name="address" required rows="4"><?php echo htmlspecialchars($customer['address']); ?></textarea>
                    </div>
                    
                    <h2 style="color: #2c3e50; margin-bottom: 1.5rem;">Payment Method</h2>
                    
                    <div style="margin: 1.5rem 0;">
                        <div style="display: flex; align-items: center; padding: 1rem; margin-bottom: 1rem; border: 2px solid #e1e8ed; border-radius: 8px; cursor: pointer;" onclick="selectPayment('cod')">
                            <input type="radio" id="cod" name="payment_method" value="Cash on Delivery" checked style="margin-right: 1rem;">
                            <label for="cod" style="font-weight: bold; color: #2c3e50;">Cash on Delivery</label>
                        </div>
                        
                        <div style="display: flex; align-items: center; padding: 1rem; margin-bottom: 1rem; border: 2px solid #e1e8ed; border-radius: 8px; cursor: pointer;" onclick="selectPayment('card')">
                            <input type="radio" id="card" name="payment_method" value="Card Payment" style="margin-right: 1rem;">
                            <label for="card" style="font-weight: bold; color: #2c3e50;">Card Payment</label>
                        </div>
                    </div>
                    
                    <div id="card_details" style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; margin-top: 1rem; border-left: 4px solid #e67e22; display: none;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label for="card_holder">Card Holder Name:</label>
                                <input type="text" id="card_holder" name="card_holder" placeholder="John Doe">
                            </div>
                            <div class="form-group">
                                <label for="card_number">Card Number:</label>
                                <input type="text" id="card_number" name="card_number" placeholder="1234 5678 9012 3456" maxlength="19">
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" name="place_order" class="submit-btn" style="width: 100%; padding: 1rem; font-size: 1.1rem;">
                        Place Order - Rs. <?php echo number_format($total, 2); ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        function selectPayment(type) {
            document.getElementById(type).checked = true;
            
            var cardDetails = document.getElementById('card_details');
            if (type === 'card') {
                cardDetails.style.display = 'block';
            } else {
                cardDetails.style.display = 'none';
            }
        }

        document.getElementById('card_number').addEventListener('input', function(e) {
            var value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            var matches = value.match(/\d{4,16}/g);
            var match = matches && matches[0] || '';
            var parts = [];
            
            for (var i = 0; i < match.length; i += 4) {
                parts.push(match.substring(i, i + 4));
            }
            
            if (parts.length) {
                e.target.value = parts.join(' ');
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            selectPayment('cod');
        });
    </script>
</body>
</html>
<?php mysqli_close($conn); ?>