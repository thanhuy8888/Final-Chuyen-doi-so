-- Additional tables for management pages

-- Products Table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(100) NOT NULL,
    category VARCHAR(50),
    price DECIMAL(10, 2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    description TEXT,
    image_url VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Customers Table (extending users)
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(50),
    country VARCHAR(50) DEFAULT 'Vietnam',
    total_orders INT DEFAULT 0,
    total_spent DECIMAL(12, 2) DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_purchase_date TIMESTAMP NULL
);

-- Orders Table (enhanced from sales)
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT,
    product_id INT,
    quantity INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    shipping_address TEXT,
    notes TEXT,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
);

-- Insert Sample Products
INSERT INTO products (product_name, category, price, stock, description) VALUES
('Laptop Dell XPS 13', 'Electronics', 25000000, 15, 'High-performance ultrabook with 13-inch display'),
('iPhone 15 Pro', 'Electronics', 30000000, 25, 'Latest iPhone with A17 Pro chip'),
('Samsung Galaxy S24', 'Electronics', 22000000, 30, 'Flagship Android smartphone'),
('MacBook Pro M3', 'Electronics', 45000000, 10, 'Professional laptop with M3 chip'),
('iPad Air', 'Electronics', 15000000, 20, 'Versatile tablet for work and play'),
('Sony WH-1000XM5', 'Audio', 8000000, 50, 'Premium noise-cancelling headphones'),
('AirPods Pro', 'Audio', 6000000, 40, 'Wireless earbuds with active noise cancellation'),
('Dell Monitor 27"', 'Electronics', 7000000, 18, 'High-resolution 4K monitor'),
('Logitech MX Master 3', 'Accessories', 2500000, 60, 'Ergonomic wireless mouse'),
('Mechanical Keyboard', 'Accessories', 3000000, 35, 'RGB mechanical gaming keyboard');

-- Insert Sample Customers
INSERT INTO customers (full_name, email, phone, address, city, total_orders, total_spent) VALUES
('Nguyễn Văn An', 'nguyenvanan@email.com', '0901234567', '123 Láng Hạ', 'Hà Nội', 5, 50000000),
('Trần Thị Bình', 'tranthib@email.com', '0912345678', '456 Nguyễn Huệ', 'TP.HCM', 8, 75000000),
('Lê Hoàng Cường', 'lehc@email.com', '0923456789', '789 Trần Phú', 'Đà Nẵng', 3, 35000000),
('Phạm Thu Duyên', 'phamtd@email.com', '0934567890', '321 Hoàng Diệu', 'Hà Nội', 12, 120000000),
('Vũ Minh Đức', 'vumd@email.com', '0945678901', '654 Lê Lợi', 'TP.HCM', 6, 60000000),
('Đỗ Thị Em', 'dothie@email.com', '0956789012', '987 Hùng Vương', 'Hải Phòng', 4, 40000000),
('Hoàng Văn Phong', 'hoangvp@email.com', '0967890123', '159 Điện Biên Phủ', 'Cần Thơ', 7, 85000000),
('Bùi Thanh Giang', 'buitg@email.com', '0978901234', '753 Phan Châu Trinh', 'Huế', 2, 25000000);

-- Insert Sample Orders
INSERT INTO orders (customer_id, product_id, quantity, total_amount, status, shipping_address) VALUES
(1, 1, 1, 25000000, 'delivered', '123 Láng Hạ, Hà Nội'),
(2, 2, 1, 30000000, 'delivered', '456 Nguyễn Huệ, TP.HCM'),
(3, 3, 2, 44000000, 'shipped', '789 Trần Phú, Đà Nẵng'),
(4, 4, 1, 45000000, 'processing', '321 Hoàng Diệu, Hà Nội'),
(5, 5, 2, 30000000, 'delivered', '654 Lê Lợi, TP.HCM'),
(1, 6, 3, 24000000, 'delivered', '123 Láng Hạ, Hà Nội'),
(2, 7, 2, 12000000, 'pending', '456 Nguyễn Huệ, TP.HCM'),
(6, 8, 1, 7000000, 'delivered', '987 Hùng Vương, Hải Phòng'),
(7, 9, 4, 10000000, 'delivered', '159 Điện Biên Phủ, Cần Thơ'),
(8, 10, 2, 6000000, 'cancelled', '753 Phan Châu Trinh, Huế');
