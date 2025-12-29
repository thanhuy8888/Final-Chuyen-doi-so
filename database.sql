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

CREATE TABLE surveys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    order_id INT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (order_id) REFERENCES orders(id)
);

CREATE TABLE churn_reasons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    reason TEXT,
    churn_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id)
);
-- Nếu chưa có khách hàng nào, thêm vài khách hàng
INSERT IGNORE INTO customers (full_name, email, phone, address) VALUES
('Nguyễn Văn An', 'an.nguyen@email.com', '0901234567', 'Hà Nội'),
('Trần Thị Bình', 'binh.tran@email.com', '0912345678', 'TP.HCM'),
('Lê Văn Cường', 'cuong.le@email.com', '0923456789', 'Đà Nẵng'),
('Phạm Minh Đạt', 'dat.pham@email.com', '0934567890', 'Cần Thơ'),
('Hoàng Thị Lan', 'lan.hoang@email.com', '0945678901', 'Hải Phòng'),
('Vũ Văn Hùng', 'hung.vu@email.com', '0956789012', 'Nha Trang'),
('Đỗ Thị Mai', 'mai.do@email.com', '0967890123', 'Huế');

-- Nếu chưa có đơn hàng, thêm vài đơn hàng
INSERT IGNORE INTO orders (customer_id, product_id, quantity, total_amount, status, order_date) VALUES
(1, 1, 2, 500000, 'delivered', '2025-11-15 10:30:00'),
(2, 2, 1, 1200000, 'delivered', '2025-11-20 14:20:00'),
(3, 3, 3, 750000, 'delivered', '2025-12-01 09:15:00'),
(4, 1, 1, 250000, 'delivered', '2025-12-10 16:45:00'),
(5, 2, 2, 2400000, 'delivered', '2025-12-15 11:00:00');

-- Thêm dữ liệu khảo sát
INSERT INTO surveys (customer_id, order_id, rating, comment, created_at) VALUES
(1, 1, 5, 'Sản phẩm chất lượng tốt, giao hàng nhanh, sẽ tiếp tục ủng hộ!', '2025-11-18 08:00:00'),
(2, 2, 4, 'Hàng đẹp, đóng gói cẩn thận. Chỉ mong giá rẻ hơn chút nữa.', '2025-11-22 15:30:00'),
(3, 3, 3, 'Sản phẩm tạm ổn, nhưng giao hàng chậm hơn dự kiến 2 ngày.', '2025-12-03 10:20:00'),
(4, 4, 5, 'Dịch vụ tuyệt vời! Nhân viên hỗ trợ nhiệt tình.', '2025-12-12 09:00:00'),
(5, 5, 2, 'Sản phẩm không đúng như mô tả, kích thước nhỏ hơn quảng cáo.', '2025-12-17 14:00:00'),
(1, NULL, 5, 'Tôi rất hài lòng với trải nghiệm mua sắm tại cửa hàng.', '2025-12-20 11:11:00'),
(6, NULL, 4, 'Giao diện website dễ dùng, tìm kiếm nhanh.', '2025-12-22 16:45:00'),
(7, NULL, 1, 'Hỗ trợ khách hàng phản hồi chậm, không giải quyết được vấn đề.', '2025-12-25 09:30:00');

-- Thêm dữ liệu churn (khách hàng rời bỏ)
INSERT INTO churn_reasons (customer_id, reason, churn_date) VALUES
(3, 'Giá sản phẩm tăng cao hơn so với đối thủ', '2025-12-05'),
(5, 'Chất lượng sản phẩm không đồng đều, lần sau mua bị lỗi', '2025-12-18'),
(7, 'Thời gian giao hàng quá chậm, không đúng cam kết', '2025-12-26'),
(2, 'Tìm được nhà cung cấp khác có chính sách ưu đãi tốt hơn', '2025-11-25'),
(6, 'Không còn nhu cầu sử dụng sản phẩm này nữa', '2025-12-10');