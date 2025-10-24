<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include '../db_connect.php';

if(!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if(isset($_GET['delete'])) {
    $material_id = $_GET['delete'];
    
    $material_sql = "SELECT image_url FROM materials WHERE material_id = $material_id";
    $material_result = mysqli_query($conn, $material_sql);
    $material = mysqli_fetch_assoc($material_result);
    
    $sql = "DELETE FROM materials WHERE material_id = $material_id";
    
    if(mysqli_query($conn, $sql)) {
        if($material['image_url'] != 'images/materials/default.jpg') {
            $image_path = '../' . $material['image_url'];
            if(file_exists($image_path)) {
                unlink($image_path);
            }
        }
        $_SESSION['success'] = "Material deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting material: " . mysqli_error($conn);
    }
    
    header("Location: manage_materials.php");
    exit();
}

$sql = "SELECT * FROM materials ORDER BY category, name";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Materials - MatLogix</title>
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

    <div style="max-width: 1200px; margin: 0 auto; padding: 2rem 20px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1 style="color: #2c3e50;">Manage Materials</h1>
            <a href="add_material.php" style="background: #e67e22; color: white; padding: 0.75rem 1.5rem; text-decoration: none; border-radius: 5px; font-weight: bold;">Add New Material</a>
        </div>
        
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
                        <th style="padding: 1rem; text-align: left; background: #f8f9fa; font-weight: bold; border-bottom: 1px solid #ddd;">Image</th>
                        <th style="padding: 1rem; text-align: left; background: #f8f9fa; font-weight: bold; border-bottom: 1px solid #ddd;">Name</th>
                        <th style="padding: 1rem; text-align: left; background: #f8f9fa; font-weight: bold; border-bottom: 1px solid #ddd;">Category</th>
                        <th style="padding: 1rem; text-align: left; background: #f8f9fa; font-weight: bold; border-bottom: 1px solid #ddd;">Price</th>
                        <th style="padding: 1rem; text-align: left; background: #f8f9fa; font-weight: bold; border-bottom: 1px solid #ddd;">Unit</th>
                        <th style="padding: 1rem; text-align: left; background: #f8f9fa; font-weight: bold; border-bottom: 1px solid #ddd;">Stock</th>
                        <th style="padding: 1rem; text-align: left; background: #f8f9fa; font-weight: bold; border-bottom: 1px solid #ddd;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($result) > 0): ?>
                        <?php while($material = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td style="padding: 1rem; border-bottom: 1px solid #ddd;">
                                <img src="../<?php echo htmlspecialchars($material['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($material['name']); ?>" 
                                     style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;"
                                     onerror="this.src='../images/materials/default.jpg'">
                            </td>
                            <td style="padding: 1rem; border-bottom: 1px solid #ddd;"><?php echo htmlspecialchars($material['name']); ?></td>
                            <td style="padding: 1rem; border-bottom: 1px solid #ddd;"><?php echo htmlspecialchars($material['category']); ?></td>
                            <td style="padding: 1rem; border-bottom: 1px solid #ddd;">Rs. <?php echo number_format($material['price'], 2); ?></td>
                            <td style="padding: 1rem; border-bottom: 1px solid #ddd;"><?php echo htmlspecialchars($material['unit_type']); ?></td>
                            <td style="padding: 1rem; border-bottom: 1px solid #ddd; <?php echo $material['quantity'] < 10 ? 'color: #e74c3c; font-weight: bold;' : ''; ?>">
                                <?php echo $material['quantity']; ?>
                            </td>
                            <td style="padding: 1rem; border-bottom: 1px solid #ddd;">
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="edit_material.php?id=<?php echo $material['material_id']; ?>" 
                                       style="background: #3498db; color: white; padding: 0.5rem 1rem; text-decoration: none; border-radius: 4px; font-size: 0.9rem;">Edit</a>
                                    <a href="manage_materials.php?delete=<?php echo $material['material_id']; ?>" 
                                       style="background: #e74c3c; color: white; padding: 0.5rem 1rem; text-decoration: none; border-radius: 4px; font-size: 0.9rem;" 
                                       onclick="return confirm('Are you sure you want to delete this material?')">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem;">
                                No materials found. <a href="add_material.php" style="color: #e67e22;">Add your first material</a>
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
</body>
</html>
<?php mysqli_close($conn); ?>