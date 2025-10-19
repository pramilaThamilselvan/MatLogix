-- Create database
CREATE DATABASE IF NOT EXISTS matlogix_db;
USE matlogix_db;

-- Admin table
CREATE TABLE admin (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);
- Insert new admin with simple password
INSERT INTO admin (username, password) VALUES ('admin', 'admin123');




