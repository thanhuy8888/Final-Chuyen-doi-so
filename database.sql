-- Create Database
CREATE DATABASE IF NOT EXISTS dashboard_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dashboard_db;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    avatar VARCHAR(255),
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- Sales Data Table
CREATE TABLE IF NOT EXISTS sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(100) NOT NULL,
    category VARCHAR(50),
    amount DECIMAL(10, 2) NOT NULL,
    quantity INT NOT NULL,
    sale_date DATE NOT NULL,
    region VARCHAR(50),
    status ENUM('completed', 'pending', 'cancelled') DEFAULT 'completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Analytics Data Table
CREATE TABLE IF NOT EXISTS analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    metric_name VARCHAR(50) NOT NULL,
    metric_value DECIMAL(12, 2) NOT NULL,
    date DATE NOT NULL,
    category VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert Sample Admin User (password: admin123)
INSERT INTO users (username, email, password, full_name, role) VALUES
('admin', 'admin@dashboard.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin'),
('user1', 'user1@dashboard.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nguyễn Văn A', 'user');

-- Insert Sample Sales Data
INSERT INTO sales (product_name, category, amount, quantity, sale_date, region, status) VALUES
('Laptop Dell XPS 13', 'Electronics', 25000000, 2, '2024-12-01', 'Hà Nội', 'completed'),
('iPhone 15 Pro', 'Electronics', 30000000, 1, '2024-12-02', 'TP.HCM', 'completed'),
('Samsung Galaxy S24', 'Electronics', 22000000, 3, '2024-12-03', 'Đà Nẵng', 'completed'),
('MacBook Pro M3', 'Electronics', 45000000, 1, '2024-12-04', 'Hà Nội', 'pending'),
('iPad Air', 'Electronics', 15000000, 2, '2024-12-05', 'TP.HCM', 'completed'),
('Sony WH-1000XM5', 'Audio', 8000000, 5, '2024-12-06', 'Hà Nội', 'completed'),
('AirPods Pro', 'Audio', 6000000, 4, '2024-12-07', 'Đà Nẵng', 'completed'),
('Dell Monitor 27"', 'Electronics', 7000000, 3, '2024-11-28', 'TP.HCM', 'completed'),
('Logitech MX Master 3', 'Accessories', 2500000, 10, '2024-11-29', 'Hà Nội', 'completed'),
('Mechanical Keyboard', 'Accessories', 3000000, 6, '2024-11-30', 'Đà Nẵng', 'completed');

-- Insert Sample Analytics Data
INSERT INTO analytics (metric_name, metric_value, date, category) VALUES
('revenue', 168500000, '2024-12-07', 'sales'),
('orders', 36, '2024-12-07', 'sales'),
('customers', 24, '2024-12-07', 'users'),
('conversion_rate', 3.5, '2024-12-07', 'marketing'),
('revenue', 155000000, '2024-12-06', 'sales'),
('orders', 32, '2024-12-06', 'sales'),
('customers', 22, '2024-12-06', 'users'),
('conversion_rate', 3.2, '2024-12-06', 'marketing');
