<?php
session_start();
require_once 'config.php';
require_once 'db.php';

// Handle Add Customer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_customer'])) {
    try {
        $full_name = $_POST['full_name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $city = $_POST['city'];
        $status = 'active'; // Default status
        
        $sql = "INSERT INTO customers (full_name, email, phone, city, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$full_name, $email, $phone, $city, $status]);
        
        // Refresh to show new data
        header("Location: customers.php");
        exit();
    } catch (PDOException $e) {
        $error = "Lỗi thêm khách hàng: " . $e->getMessage();
    }
}

// Get all customers
try {
    $sql = "SELECT * FROM customers ORDER BY created_at DESC LIMIT 50";
    $stmt = $conn->query($sql);
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get statistics
    $stats_sql = "SELECT 
        COUNT(*) as total_customers,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_customers,
        SUM(total_orders) as total_orders,
        SUM(total_spent) as total_revenue
        FROM customers";
    $stats = $conn->query($stats_sql)->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    if (!isset($error)) {
        $error = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Khách hàng - MSDB</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Modal Styles */
        .modal {
            display: none; 
            position: fixed; 
            z-index: 1000; 
            left: 0; 
            top: 0; 
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgba(0,0,0,0.5); 
            backdrop-filter: blur(4px);
        }
        .modal-content {
            background-color: var(--bg-primary);
            margin: 10% auto; 
            padding: 2rem; 
            border: 1px solid var(--border);
            width: 100%;
            max-width: 500px;
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            animation: slideIn 0.3s ease-out;
        }
        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .close {
            color: var(--text-secondary);
            float: right;
            font-size: 1.5rem;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.2s;
        }
        .close:hover {
            color: var(--text-primary);
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-secondary);
            font-size: 0.875rem;
            font-weight: 500;
        }
        .form-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            background-color: var(--bg-secondary);
            color: var(--text-primary);
            font-size: 0.95rem;
            transition: all 0.2s;
        }
        .form-input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        .modal-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: var(--text-primary);
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="logo-text">MSDB</div>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">MENU CHÍNH</div>
                    <a href="index.php" class="nav-item">
                        <i class="fas fa-home"></i>
                        <span>Tổng quan</span>
                    </a>
                    <a href="analytics.php" class="nav-item">
                        <i class="fas fa-chart-bar"></i>
                        <span>Phân tích</span>
                    </a>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">QUẢN LÝ</div>
                    <a href="orders.php" class="nav-item">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Đơn hàng</span>
                    </a>
                    <a href="customers.php" class="nav-item active">
                        <i class="fas fa-users"></i>
                        <span>Khách hàng</span>
                    </a>
                    <a href="products.php" class="nav-item">
                        <i class="fas fa-box"></i>
                        <span>Sản phẩm</span>
                    </a>
                    <a href="profile.php" class="nav-item">
                        <i class="fas fa-user"></i>
                        <span>Hồ sơ</span>
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="header">
                <div class="header-left">
                    <button class="menu-toggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Tìm kiếm khách hàng...">
                    </div>
                </div>
                <div class="header-right">
                    <div class="header-icon">
                        <i class="fas fa-bell"></i>
                        <span class="badge">3</span>
                    </div>
                    <div class="user-profile">
                        <div class="user-avatar">A</div>
                        <div class="user-info">
                            <div class="user-name">Admin</div>
                            <div class="user-role">Administrator</div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <div class="content">
                <div class="page-header">
                    <h1 class="page-title">Quản lý Khách hàng</h1>
                    <p class="page-subtitle">Theo dõi và quản lý thông tin khách hàng</p>
                </div>

                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card fade-in">
                        <div class="stat-header">
                            <div class="stat-title">Tổng khách hàng</div>
                            <div class="stat-icon primary">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <div class="stat-value"><?php echo number_format($stats['total_customers'] ?? 0); ?></div>
                        <div class="stat-change positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>+18% so với tháng trước</span>
                        </div>
                    </div>

                    <div class="stat-card fade-in-delay-1">
                        <div class="stat-header">
                            <div class="stat-title">Đang hoạt động</div>
                            <div class="stat-icon success">
                                <i class="fas fa-user-check"></i>
                            </div>
                        </div>
                        <div class="stat-value"><?php echo number_format($stats['active_customers'] ?? 0); ?></div>
                        <div class="stat-change positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>+5.2%</span>
                        </div>
                    </div>

                    <div class="stat-card fade-in-delay-2">
                        <div class="stat-header">
                            <div class="stat-title">Tổng đơn hàng</div>
                            <div class="stat-icon warning">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                        </div>
                        <div class="stat-value"><?php echo number_format($stats['total_orders'] ?? 0); ?></div>
                        <div class="stat-change">
                            <span>Tất cả khách hàng</span>
                        </div>
                    </div>

                    <div class="stat-card fade-in-delay-3">
                        <div class="stat-header">
                            <div class="stat-title">Tổng chi tiêu</div>
                            <div class="stat-icon danger">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                        <div class="stat-value">₫<?php echo number_format($stats['total_revenue'] ?? 0); ?></div>
                        <div class="stat-change positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>+22.8%</span>
                        </div>
                    </div>
                </div>

                <!-- Customers Table -->
                <div class="table-card">
                    <div class="table-header">
                        <h3 class="table-title">Danh sách Khách hàng</h3>
                        <div class="table-actions">
                            <button class="btn btn-secondary">
                                <i class="fas fa-filter"></i>
                                Lọc
                            </button>
                            <button class="btn btn-primary" onclick="openModal('addCustomerModal')">
                                <i class="fas fa-plus"></i>
                                Thêm khách hàng
                            </button>
                        </div>
                    </div>
                    <div class="table-container">
                        <?php if (isset($error)): ?>
                            <div style="padding: 2rem; text-align: center; color: var(--danger);">
                                <i class="fas fa-exclamation-triangle" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                                <p><?php echo htmlspecialchars($error); ?></p>
                                <p style="margin-top: 1rem; color: var(--text-secondary);">
                                    Hãy import database bằng cách chạy file <code>database_extended.sql</code>
                                </p>
                            </div>
                        <?php else: ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Họ tên</th>
                                        <th>Email</th>
                                        <th>Điện thoại</th>
                                        <th>Thành phố</th>
                                        <th>Tổng đơn</th>
                                        <th>Tổng chi tiêu</th>
                                        <th>Trạng thái</th>
                                        <th>Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($customers as $customer): ?>
                                        <tr>
                                            <td>#<?php echo $customer['id']; ?></td>
                                            <td>
                                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                                    <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--primary-gradient); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 0.85rem;">
                                                        <?php echo strtoupper(substr($customer['full_name'], 0, 1)); ?>
                                                    </div>
                                                    <span><?php echo htmlspecialchars($customer['full_name']); ?></span>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                            <td><?php echo htmlspecialchars($customer['phone'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($customer['city'] ?? 'N/A'); ?></td>
                                            <td><?php echo number_format($customer['total_orders']); ?></td>
                                            <td>₫<?php echo number_format($customer['total_spent']); ?></td>
                                            <td>
                                                <span class="status-badge <?php echo $customer['status'] == 'active' ? 'completed' : 'cancelled'; ?>">
                                                    <?php echo $customer['status'] == 'active' ? 'Hoạt động' : 'Không hoạt động'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-secondary" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-secondary" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add Customer Modal -->
    <div id="addCustomerModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('addCustomerModal')">&times;</span>
            <h2 class="modal-title">Thêm Khách Hàng Mới</h2>
            <form action="" method="POST">
                <input type="hidden" name="add_customer" value="1">
                <div class="form-group">
                    <label class="form-label">Họ tên</label>
                    <input type="text" name="full_name" class="form-input" required placeholder="Nhập họ tên...">
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" required placeholder="example@email.com">
                </div>
                <div class="form-group">
                    <label class="form-label">Số điện thoại</label>
                    <input type="tel" name="phone" class="form-input" placeholder="0123...">
                </div>
                <div class="form-group">
                    <label class="form-label">Thành phố</label>
                    <input type="text" name="city" class="form-input" placeholder="Hà Nội...">
                </div>
                <div style="text-align: right; margin-top: 2rem;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addCustomerModal')" style="margin-right: 1rem;">Hủy</button>
                    <button type="submit" class="btn btn-primary">Thêm Khách Hàng</button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
    <script>
        // Modal functions
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = "none";
            }
        }
    </script>
</body>
</html>
