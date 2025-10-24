-- Create database
CREATE DATABASE IF NOT EXISTS matlogix_db;
USE matlogix_db;

-- Admin table
CREATE TABLE admin (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- Insert new admin with simple password
INSERT INTO admin (username, password) VALUES ('admin', 'admin123');

-- Materials table
CREATE TABLE materials (
    material_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(100),
    price DECIMAL(10,2) NOT NULL,
    unit_type VARCHAR(50) NOT NULL,
    quantity INT NOT NULL,
    description TEXT,
    image_url VARCHAR(255) DEFAULT 'images/materials/default.jpg'
);

-- Insert sample materials
INSERT INTO materials (name, category, price, unit_type, quantity, description, image_url) VALUES
('Cement Bag', 'Cement', 1450.00, 'Per Bag', 200, '50kg high-quality cement bag', 'images/materials/cement.jpg'),
('Crushed Stones', 'Stones', 25000.00, 'Per Tractor', 50, 'High-quality crushed stones for construction', 'images/materials/crushed_stones.jpg'),
('Bricks', 'Bricks', 35.00, 'Per Unit', 5000, 'Standard red clay bricks', 'images/materials/bricks.jpg'),
('Cement Bricks', 'Bricks', 70.00, 'Per Unit', 2500, 'Strong cement bricks for durability', 'images/materials/cement_bricks.jpg'),
('Plastering Sand', 'Sand', 25000.00, 'Per Tractor', 30, 'Fine plastering sand for smooth finish', 'images/materials/plastering_sand.jpg'),
('Masonry Sand', 'Sand', 25000.00, 'Per Tractor', 30, 'Medium-grain masonry sand', 'images/materials/masonry_sand.jpg'),
('Fill Sand', 'Sand', 25000.00, 'Per Tractor', 30, 'Coarse fill sand for foundation', 'images/materials/fill_sand.jpg'),
('Gravel', 'Sand', 25000.00, 'Per Tractor', 30, 'Rounded gravel sand for drainage', 'images/materials/gravel.jpg'),
('Rubble Stone', 'Stone', 25000.00, 'Per Tractor', 25, 'Large rubble stone for foundation work', 'images/materials/rubble_stone.jpg');


-- Orders table
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('Cash on Delivery', 'Card Payment') NOT NULL,
    status ENUM('Pending', 'Confirmed', 'Delivered') DEFAULT 'Pending',
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id) ON DELETE CASCADE
);

-- Order details table
CREATE TABLE order_details (
    order_detail_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    material_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (material_id) REFERENCES materials(material_id) ON DELETE CASCADE
);

