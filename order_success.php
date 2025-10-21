<?php
session_start();
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
    <style>
        .success-page {
            text-align: center;
            padding: 4rem 2rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .success-icon {
            font-size: 4rem;
            margin-bottom: 2rem;
            color: #27ae60;
        }

        .success-message {
            font-size: 1.2rem;
            margin-bottom: 1rem;
            color: #2c3e50;
        }

        .success-details {
            color: #7f8c8d;
            margin-bottom: 2rem;
        }

        .success-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        @media (max-width: 768px) {
            .success-actions {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main class="container">
        <div class="success-page">
            <div class="success-icon">✅</div>
            <h1>Order Placed Successfully!</h1>
            <p class="success-message">Thank you for your order. Your order ID is <strong>#<?php echo $order_id; ?></strong></p>
            <p class="success-details">We will process your order and contact you soon for delivery details.</p>
            
            <div class="success-actions">
                <a href="my_orders.php" class="btn btn-primary">View My Orders</a>
                <a href="materials.php" class="btn btn-secondary">Continue Shopping</a>
                <a href="index.php" class="btn btn-outline">Back to Home</a>
            </div>
        </div>
    </main>

    <footer>
        <div class="container">
            <p>&copy; 2024 MatLogix. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>