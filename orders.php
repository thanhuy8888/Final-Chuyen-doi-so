<?php
session_start();
require_once 'config.php';
require_once 'db.php';

// Get all orders with customer and product details
try {
    $sql = "SELECT o.*, c.full_name as customer_name, c.email as customer_email, 
            p.product_name, p.category
            FROM orders o
            LEFT JOIN customers c ON o.customer_id = c.id  
            LEFT JOIN products p ON o.product_id = p.id
            ORDER BY o.order_date DESC 
            LIMIT 50";
    $stmt = $conn->query($sql);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get statistics
    $stats_sql = "SELECT 
        COUNT(*) as total_orders,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
        SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
        SUM(total_amount) as total_revenue
        FROM orders";
    $stats = $conn->query($stats_sql)->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Đơn hàng - MSDB</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
                    <a href="orders.php" class="nav-item active">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Đơn hàng</span>
                        <span class="nav-badge"><?php echo $stats['pending_orders'] ?? 0; ?></span>
                    </a>
                    <a href="customers.php" class="nav-item">
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
                        <input type="text" placeholder="Tìm kiếm đơn hàng...">
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
                    <h1 class="page-title">Quản lý Đơn hàng</h1>
                    <p class="page-subtitle">Theo dõi và quản lý tất cả đơn hàng</p>
                </div>

                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card fade-in">
                        <div class="stat-header">
                            <div class="stat-title">Tổng đơn hàng</div>
                            <div class="stat-icon primary">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                        </div>
                        <div class="stat-value"><?php echo number_format($stats['total_orders'] ?? 0); ?></div>
                        <div class="stat-change positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>+12% so với tháng trước</span>
                        </div>
                    </div>

                    <div class="stat-card fade-in-delay-1">
                        <div class="stat-header">
                            <div class="stat-title">Chờ xử lý</div>
                            <div class="stat-icon warning">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                        <div class="stat-value"><?php echo number_format($stats['pending_orders'] ?? 0); ?></div>
                        <div class="stat-change">
                            <span>Cần xử lý</span>
                        </div>
                    </div>

                    <div class="stat-card fade-in-delay-2">
                        <div class="stat-header">
                            <div class="stat-title">Đã giao</div>
                            <div class="stat-icon success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                        <div class="stat-value"><?php echo number_format($stats['delivered_orders'] ?? 0); ?></div>
                        <div class="stat-change positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>+8.5%</span>
                        </div>
                    </div>

                    <div class="stat-card fade-in-delay-3">
                        <div class="stat-header">
                            <div class="stat-title">Tổng doanh thu</div>
                            <div class="stat-icon danger">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                        <div class="stat-value">₫<?php echo number_format($stats['total_revenue'] ?? 0); ?></div>
                        <div class="stat-change positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>+15.3%</span>
                        </div>
                    </div>
                </div>

                <!-- Orders Table -->
                <div class="table-card">
                    <div class="table-header">
                        <h3 class="table-title">Danh sách Đơn hàng</h3>
                        <div class="table-actions">
                            <button class="btn btn-secondary">
                                <i class="fas fa-filter"></i>
                                Lọc
                            </button>
                            <button class="btn btn-primary">
                                <i class="fas fa-plus"></i>
                                Tạo đơn mới
                            </button>
                        </div>
                    </div>
                    <div class="table-container">
                        <?php if (isset($error)): ?>
                            <div style="padding: 2rem; text-align: center; color: var(--danger);">
                                <i class="fas fa-exclamation-triangle" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                                <p><?php echo htmlspecialchars($error); ?></p>
                                <p style="margin-top: 1rem; color: var(--text-secondary);">
                                    Hãy import database bằng cách chạy file <code>database.sql</code> và <code>database_extended.sql</code>
                                </p>
                            </div>
                        <?php else: ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Khách hàng</th>
                                        <th>Sản phẩm</th>
                                        <th>Danh mục</th>
                                        <th>Số lượng</th>
                                        <th>Tổng tiền</th>
                                        <th>Ngày đặt</th>
                                        <th>Trạng thái</th>
                                        <th>Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td>#<?php echo $order['id']; ?></td>
                                            <td>
                                                <div><?php echo htmlspecialchars($order['customer_name'] ?? 'N/A'); ?></div>
                                                <div style="font-size: 0.85rem; color: var(--text-muted);">
                                                    <?php echo htmlspecialchars($order['customer_email'] ?? ''); ?>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($order['product_name'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($order['category'] ?? 'N/A'); ?></td>
                                            <td><?php echo number_format($order['quantity']); ?></td>
                                            <td>₫<?php echo number_format($order['total_amount']); ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
                                            <td>
                                                <?php
                                                $statusClass = '';
                                                $statusText = '';
                                                switch($order['status']) {
                                                    case 'delivered':
                                                        $statusClass = 'completed';
                                                        $statusText = 'Đã giao';
                                                        break;
                                                    case 'pending':
                                                        $statusClass = 'pending';
                                                        $statusText = 'Chờ xử lý';
                                                        break;
                                                    case 'processing':
                                                        $statusClass = 'pending';
                                                        $statusText = 'Đang xử lý';
                                                        break;
                                                    case 'shipped':
                                                        $statusClass = 'completed';
                                                        $statusText = 'Đang giao';
                                                        break;
                                                    case 'cancelled':
                                                        $statusClass = 'cancelled';
                                                        $statusText = 'Đã hủy';
                                                        break;
                                                }
                                                ?>
                                                <span class="status-badge <?php echo $statusClass; ?>">
                                                    <?php echo $statusText; ?>
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

    <script src="assets/js/main.js"></script>
</body>
</html>
