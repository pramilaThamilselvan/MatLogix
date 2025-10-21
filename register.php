<?php
session_start();
include 'db_connect.php';

if(isset($_SESSION['customer_id'])) {
    header("Location: index.php");
    exit();
}

if(isset($_POST['register'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate passwords match
    if($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        // Check if email already exists
        $check_sql = "SELECT * FROM customers WHERE email = '$email'";
        $check_result = mysqli_query($conn, $check_sql);
        
        if(mysqli_num_rows($check_result) > 0) {
            $error = "Email already registered!";
        } else {
            // Hash password and insert customer
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql = "INSERT INTO customers (name, email, phone, address, password) 
                    VALUES ('$name', '$email', '$phone', '$address', '$hashed_password')";
            
            if(mysqli_query($conn, $sql)) {
                $_SESSION['success'] = "Registration successful! Please login.";
                header("Location: login.php");
                exit();
            } else {
                $error = "Registration failed: " . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - MatLogix</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .auth-container {
            max-width: 500px;
            margin: 2rem auto;
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .auth-header h1 {
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2c3e50;
            font-weight: 600;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #e67e22;
        }

        .auth-btn {
            width: 100%;
            padding: 1rem;
            background: #e67e22;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1.1rem;
            cursor: pointer;
            transition: background 0.3s;
        }

        .auth-btn:hover {
            background: #d35400;
        }

        .auth-links {
            text-align: center;
            margin-top: 1.5rem;
        }

        .auth-links a {
            color: #e67e22;
            text-decoration: none;
        }

        .auth-links a:hover {
            text-decoration: underline;
        }

        .error {
            background: #ffebee;
            color: #c62828;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            text-align: center;
            border: 1px solid #ffcdd2;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main class="container">
        <div class="auth-container">
            <div class="auth-header">
                <h1>Create Account</h1>
                <p>Join MatLogix today</p>
            </div>
            
            <?php if(isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="name">Full Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number:</label>
                    <input type="text" id="phone" name="phone" required>
                </div>
                
                <div class="form-group">
                    <label for="address">Delivery Address:</label>
                    <textarea id="address" name="address" rows="3" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" name="register" class="auth-btn">Create Account</button>
            </form>
            
            <div class="auth-links">
                <p>Already have an account? <a href="login.php">Login here</a></p>
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
<?php mysqli_close($conn); ?>