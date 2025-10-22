<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if(isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    include '../db_connect.php';
    
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    
    $sql = "SELECT * FROM admin WHERE username = '$username' AND password = '$password'";
    $result = mysqli_query($conn, $sql);
    
    if(mysqli_num_rows($result) == 1) {
        $admin = mysqli_fetch_assoc($result);
        
        $_SESSION['admin_id'] = $admin['admin_id'];
        $_SESSION['admin_username'] = $admin['username'];
        
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password!";
    }
    
    mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - MatLogix</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .back-link {
            color: #3498db;
            text-decoration: none;
            font-weight: bold;
            margin-top: 2rem;
            padding: 0.5rem 0;
            transition: color 0.3s ease;
            display: inline-block;
        }
        
        .back-link:hover {
            color: #2980b9;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="form-container">
            <div class="form-header">
                <h1>Admin Login</h1>
                <p>MatLogix Building Materials System</p>
            </div>
            
            <?php if(!empty($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" name="login" class="submit-btn">Login to Dashboard</button>
            </form>
            
            <a href="../index.php" class="back-link">← Back to Main Website</a>
        </div>
    </div>
</body>
</html>