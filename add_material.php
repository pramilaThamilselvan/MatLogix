<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include '../db_connect.php';

if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$error = '';

if(isset($_POST['add_material'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $unit_type = mysqli_real_escape_string($conn, $_POST['unit_type']);
    $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    
    $image_url = 'images/materials/default.jpg';
    if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['image']['type'];
        
        if(in_array($file_type, $allowed_types)) {
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image_filename = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $name) . '.' . $file_extension;
            $upload_path = '../images/materials/' . $image_filename;
            
            if(!is_dir('../images/materials/')) {
                mkdir('../images/materials/', 0777, true);
            }
            
            if(move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_url = 'images/materials/' . $image_filename;
            } else {
                $error = "Failed to upload image.";
            }
        } else {
            $error = "Invalid file type. Only JPG, PNG, GIF allowed.";
        }
    }
    
    $sql = "INSERT INTO materials (name, category, price, unit_type, quantity, description, image_url) 
            VALUES ('$name', '$category', '$price', '$unit_type', '$quantity', '$description', '$image_url')";
    
    if(mysqli_query($conn, $sql)) {
        $_SESSION['success'] = "Material added successfully!";
        header("Location: manage_materials.php");
        exit();
    } else {
        $error = "Error adding material: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Material - MatLogix</title>
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
                    <a href="manage_orders.php" style="color: white; text-decoration: none; padding: 0.5rem 1rem; border-radius: 5px; transition: background 0.3s;">Orders</a>
                    <a href="../logout.php" style="color: white; text-decoration: none; padding: 0.5rem 1rem; border-radius: 5px; background: #e74c3c; font-weight: bold;">Logout</a>
                </div>
            </nav>
        </div>
    </header>

    <div style="max-width: 600px; margin: 2rem auto; padding: 0 20px;">
        <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <h1 style="text-align: center; color: #2c3e50; margin-bottom: 2rem;">Add New Material</h1>
            
            <?php if(!empty($error)): ?>
                <div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 5px; margin-bottom: 1rem; border: 1px solid #f5c6cb;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Material Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="category">Category:</label>
                    <select id="category" name="category" required>
                        <option value="">Select Category</option>
                        <option value="Cement">Cement</option>
                        <option value="Bricks">Bricks</option>
                        <option value="Sand">Sand</option>
                        <option value="Stones">Stones</option>
                        <option value="Steel">Steel</option>
                        <option value="Wood">Wood</option>
                        <option value="Pipes">Pipes</option>
                        <option value="Electrical">Electrical</option>
                        <option value="Plumbing">Plumbing</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="price">Price (Rs.):</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="unit_type">Unit Type:</label>
                    <select id="unit_type" name="unit_type" required>
                        <option value="">Select Unit</option>
                        <option value="Per Bag">Per Bag</option>
                        <option value="Per Unit">Per Unit</option>
                        <option value="Per Tractor">Per Tractor</option>
                        <option value="Per Kg">Per Kg</option>
                        <option value="Per Meter">Per Meter</option>
                        <option value="Per Piece">Per Piece</option>
                        <option value="Per Set">Per Set</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="quantity">Stock Quantity:</label>
                    <input type="number" id="quantity" name="quantity" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" rows="4" required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="image">Material Image:</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    <small style="color: #666;">Supported formats: JPG, PNG, GIF (Max 2MB)</small>
                </div>
                
                <div style="display: flex; gap: 1rem; justify-content: center; margin-top: 1.5rem;">
                    <button type="submit" name="add_material" style="background: #e67e22; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">Add Material</button>
                    <a href="manage_materials.php" style="background: #2c3e50; color: white; padding: 0.75rem 1.5rem; text-decoration: none; border-radius: 5px; font-weight: bold;">Cancel</a>
                </div>
            </form>
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