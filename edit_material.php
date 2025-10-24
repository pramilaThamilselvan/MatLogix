<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include '../db_connect.php';

if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if(!isset($_GET['id'])) {
    header("Location: manage_materials.php");
    exit();
}

$material_id = $_GET['id'];
$sql = "SELECT * FROM materials WHERE material_id = $material_id";
$result = mysqli_query($conn, $sql);
$material = mysqli_fetch_assoc($result);

if(!$material) {
    header("Location: manage_materials.php");
    exit();
}

$error = '';

if(isset($_POST['update_material'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $unit_type = mysqli_real_escape_string($conn, $_POST['unit_type']);
    $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    
    $image_url = $material['image_url'];
    
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
                if($material['image_url'] != 'images/materials/default.jpg') {
                    $old_image_path = '../' . $material['image_url'];
                    if(file_exists($old_image_path)) {
                        unlink($old_image_path);
                    }
                }
                $image_url = 'images/materials/' . $image_filename;
            } else {
                $error = "Failed to upload image.";
            }
        } else {
            $error = "Invalid file type. Only JPG, PNG, GIF allowed.";
        }
    }
    
    $sql = "UPDATE materials SET 
            name = '$name', 
            category = '$category', 
            price = '$price', 
            unit_type = '$unit_type', 
            quantity = '$quantity', 
            description = '$description',
            image_url = '$image_url'
            WHERE material_id = $material_id";
    
    if(mysqli_query($conn, $sql)) {
        $_SESSION['success'] = "Material updated successfully!";
        header("Location: manage_materials.php");
        exit();
    } else {
        $error = "Error updating material: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Material - MatLogix</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header style="background: #2c3e50; color: white; padding: 1rem 0;">
        <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
            <nav style="display: flex; justify-content: space-between; align-items: center;">
                <h1>MatLogix Admin</h1>
                <div style="display: flex; gap: 2rem;">
                    <a href="dashboard.php" style="color: white; text-decoration: none; padding: 0.5rem 1rem; border-radius: 5px; transition: background 0.3s;">Dashboard</a>
                    <a href="manage_materials.php" style="color: white; text-decoration: none; padding: 0.5rem 1rem; border-radius: 5px; background: #e67e22; font-weight: bold;">Materials</a>
                    <a href="manage_orders.php" style="color: white; text-decoration: none; padding: 0.5rem 1rem; border-radius: 5px; transition: background 0.3s;">Orders</a>
                    <a href="../logout.php" style="color: white; text-decoration: none; padding: 0.5rem 1rem; border-radius: 5px; background: #e74c3c; font-weight: bold;">Logout</a>
                </div>
            </nav>
        </div>
    </header>

    <div style="max-width: 600px; margin: 2rem auto; padding: 0 20px;">
        <div style="background: white; padding: 2rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <h1 style="text-align: center; color: #2c3e50; margin-bottom: 2rem;">Edit Material</h1>
            
            <?php if(!empty($error)): ?>
                <div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 5px; margin-bottom: 1rem; border: 1px solid #f5c6cb;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <div style="text-align: center; margin-bottom: 1rem; padding: 1rem; background: #f8f9fa; border-radius: 8px;">
                <p style="font-weight: bold; margin-bottom: 0.5rem;">Current Image:</p>
                <img src="../<?php echo htmlspecialchars($material['image_url']); ?>" 
                     alt="<?php echo htmlspecialchars($material['name']); ?>"
                     style="max-width: 200px; max-height: 200px; border-radius: 8px; border: 2px solid #e67e22;"
                     onerror="this.src='../images/materials/default.jpg'">
            </div>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Material Name:</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($material['name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="category">Category:</label>
                    <select id="category" name="category" required>
                        <option value="Cement" <?php echo $material['category'] == 'Cement' ? 'selected' : ''; ?>>Cement</option>
                        <option value="Bricks" <?php echo $material['category'] == 'Bricks' ? 'selected' : ''; ?>>Bricks</option>
                        <option value="Sand" <?php echo $material['category'] == 'Sand' ? 'selected' : ''; ?>>Sand</option>
                        <option value="Stones" <?php echo $material['category'] == 'Stones' ? 'selected' : ''; ?>>Stones</option>
                        <option value="Steel" <?php echo $material['category'] == 'Steel' ? 'selected' : ''; ?>>Steel</option>
                        <option value="Wood" <?php echo $material['category'] == 'Wood' ? 'selected' : ''; ?>>Wood</option>
                        <option value="Pipes" <?php echo $material['category'] == 'Pipes' ? 'selected' : ''; ?>>Pipes</option>
                        <option value="Electrical" <?php echo $material['category'] == 'Electrical' ? 'selected' : ''; ?>>Electrical</option>
                        <option value="Plumbing" <?php echo $material['category'] == 'Plumbing' ? 'selected' : ''; ?>>Plumbing</option>
                        <option value="Other" <?php echo $material['category'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="price">Price (Rs.):</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo $material['price']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="unit_type">Unit Type:</label>
                    <select id="unit_type" name="unit_type" required>
                        <option value="Per Bag" <?php echo $material['unit_type'] == 'Per Bag' ? 'selected' : ''; ?>>Per Bag</option>
                        <option value="Per Unit" <?php echo $material['unit_type'] == 'Per Unit' ? 'selected' : ''; ?>>Per Unit</option>
                        <option value="Per Tractor" <?php echo $material['unit_type'] == 'Per Tractor' ? 'selected' : ''; ?>>Per Tractor</option>
                        <option value="Per Kg" <?php echo $material['unit_type'] == 'Per Kg' ? 'selected' : ''; ?>>Per Kg</option>
                        <option value="Per Meter" <?php echo $material['unit_type'] == 'Per Meter' ? 'selected' : ''; ?>>Per Meter</option>
                        <option value="Per Piece" <?php echo $material['unit_type'] == 'Per Piece' ? 'selected' : ''; ?>>Per Piece</option>
                        <option value="Per Set" <?php echo $material['unit_type'] == 'Per Set' ? 'selected' : ''; ?>>Per Set</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="quantity">Stock Quantity:</label>
                    <input type="number" id="quantity" name="quantity" min="0" value="<?php echo $material['quantity']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" rows="4" required><?php echo htmlspecialchars($material['description']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="image">Change Image:</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    <small style="color: #666;">Leave empty to keep current image. Supported formats: JPG, PNG, GIF (Max 2MB)</small>
                </div>
                
                <div style="display: flex; gap: 1rem; justify-content: center; margin-top: 2rem;">
                    <button type="submit" name="update_material" style="background: #e67e22; color: white; padding: 0.75rem 1.5rem; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">Update Material</button>
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