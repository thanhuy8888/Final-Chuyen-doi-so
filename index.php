<?php
require_once 'config.php';
require_once 'db.php';

// Fetch dashboard statistics
try {
    // Total Revenue
    $stmt = $conn->query("SELECT SUM(amount) as total_revenue FROM sales WHERE status = 'completed'");
    $total_revenue = $stmt->fetch()['total_revenue'] ?? 0;
    
    // Total Orders
    $stmt = $conn->query("SELECT COUNT(*) as total_orders FROM sales");
    $total_orders = $stmt->fetch()['total_orders'] ?? 0;
    
    // New Customers (simplified)
    $stmt = $conn->query("SELECT COUNT(*) as total_customers FROM users WHERE role = 'user'");
    $total_customers = $stmt->fetch()['total_customers'] ?? 0;
    
    // Pending Orders
    $stmt = $conn->query("SELECT COUNT(*) as pending_orders FROM sales WHERE status = 'pending'");
    $pending_orders = $stmt->fetch()['pending_orders'] ?? 0;
    
    // Recent Sales
    $stmt = $conn->query("SELECT * FROM sales ORDER BY sale_date DESC, created_at DESC LIMIT 10");
    $recent_sales = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $total_revenue = 0;
    $total_orders = 0;
    $total_customers = 0;
    $pending_orders = 0;
    $recent_sales = [];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo SITE_NAME; ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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
                    <div class="nav-section-title">Menu chính</div>
                    <a href="index.php" class="nav-item active">
                        <i class="fas fa-home"></i>
                        <span>Tổng quan</span>
                    </a>
                    <a href="analytics.php" class="nav-item">
                        <i class="fas fa-chart-bar"></i>
                        <span>Phân tích</span>
                    </a>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Quản lý</div>
                    <a href="orders.php" class="nav-item">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Đơn hàng</span>
                        <span class="nav-badge"><?php echo $pending_orders; ?></span>
                    </a>
                    <a href="customers.php" class="nav-item">
                        <i class="fas fa-users"></i>
                        <span>Khách hàng</span>
                    </a>
                    <a href="products.php" class="nav-item">
                        <i class="fas fa-box"></i>
                        <span>Sản phẩm</span>
                    </a>
                </div>
                <div class="nav-section">
                    <div class="nav-section-title">Khảo sát & Phân tích</div>
                    <a href="surveys.php" class="nav-item"><i class="fas fa-poll"></i><span>Khảo sát khách hàng</span></a>
                    <a href="attrition.php" class="nav-item"><i class="fas fa-chart-pie"></i><span>Phân tích rời bỏ</span></a>
                </div>
                <div class="nav-section">
                    <div class="nav-section-title">Trung tâm thông tin</div>
                    <a href="profile.php" class="nav-item"><i class="fas fa-user-circle"></i><span>Hồ sơ</span></a>
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
                        <input type="text" placeholder="Tìm kiếm...">
                    </div>
                </div>
                
                <div class="header-right">
                    <div class="header-icon">
                        <i class="fas fa-bell"></i>
                        <span class="badge">3</span>
                    </div>
                    <div class="header-icon">
                        <i class="fas fa-envelope"></i>
                        <span class="badge">5</span>
                    </div>
                    <div class="user-profile">
                        <div class="user-avatar">AD</div>
                        <div class="user-info">
                            <div class="user-name">Admin</div>
                            <div class="user-role">Quản trị viên</div>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Content -->
            <div class="content">
                <!-- Page Header -->
                <div class="page-header">
                    <h1 class="page-title">Tổng quan Dashboard</h1>
                    <p class="page-subtitle">Chào mừng trở lại! Đây là thống kê tổng quan của bạn.</p>
                </div>
                
                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-title">Tổng doanh thu</div>
                            <div class="stat-icon primary">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                        <div class="stat-value"><?php echo number_format($total_revenue / 1000000, 1); ?>M</div>
                        <div class="stat-change positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>12.5% so với tháng trước</span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-title">Tổng đơn hàng</div>
                            <div class="stat-icon success">
                                <i class="fas fa-shopping-bag"></i>
                            </div>
                        </div>
                        <div class="stat-value"><?php echo number_format($total_orders); ?></div>
                        <div class="stat-change positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>8.2% so với tháng trước</span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-title">Khách hàng</div>
                            <div class="stat-icon warning">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <div class="stat-value"><?php echo number_format($total_customers); ?></div>
                        <div class="stat-change positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>15.3% khách hàng mới</span>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-header">
                            <div class="stat-title">Đơn chờ xử lý</div>
                            <div class="stat-icon danger">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                        <div class="stat-value"><?php echo number_format($pending_orders); ?></div>
                        <div class="stat-change negative">
                            <i class="fas fa-arrow-down"></i>
                            <span>Cần xử lý ngay</span>
                        </div>
                    </div>
                </div>
                
                <!-- Charts Grid -->
                <div class="charts-grid">
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3 class="chart-title">Doanh thu theo tháng</h3>
                            <div class="chart-actions">
                                <button class="chart-btn">Tháng</button>
                                <button class="chart-btn">Năm</button>
                            </div>
                        </div>
                        <div class="chart-container">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                    
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3 class="chart-title">Sales by Countries</h3>
                            <div class="chart-actions">
                                <select class="chart-btn" style="padding: 0.5rem 1rem; border: 1px solid var(--border); border-radius: 8px; background: var(--bg-secondary); cursor: pointer;">
                                    <option>This Week</option>
                                    <option>This Month</option>
                                    <option>This Year</option>
                                </select>
                            </div>
                        </div>
                        <div class="chart-container" style="position: relative; padding: 2rem 1rem;">
                            <!-- World Map SVG -->
                            <div style="text-align: center; margin-bottom: 1.5rem;">
                                <svg viewBox="0 0 600 300" style="width: 100%; max-width: 500px; height: auto;">
                                    <style>
                                        @keyframes pulse {
                                            0%, 100% { opacity: 0.3; transform: scale(1); }
                                            50% { opacity: 0.7; transform: scale(1.1); }
                                        }
                                        .country-marker {
                                            cursor: pointer;
                                            transition: all 0.3s ease;
                                        }
                                        .country-marker:hover {
                                            opacity: 0.9 !important;
                                            filter: drop-shadow(0 0 8px #6366F1);
                                        }
                                        .marker-1 { animation: pulse 3s ease-in-out infinite; animation-delay: 0s; }
                                        .marker-2 { animation: pulse 3s ease-in-out infinite; animation-delay: 0.6s; }
                                        .marker-3 { animation: pulse 3s ease-in-out infinite; animation-delay: 1.2s; }
                                        .marker-4 { animation: pulse 3s ease-in-out infinite; animation-delay: 1.8s; }
                                        .marker-5 { animation: pulse 3s ease-in-out infinite; animation-delay: 2.4s; }
                                    </style>
                                    
                                    <!-- Simplified world map -->
                                    <path d="M 50 100 L 150 120 L 200 80 L 250 100 L 300 90 L 350 110 L 400 95 L 450 105 L 500 100 L 550 115" fill="none" stroke="#E5E7EB" stroke-width="2"/>
                                    <path d="M 100 150 L 180 160 L 220 140 L 280 145 L 320 135 L 380 150 L 420 140 L 480 155" fill="none" stroke="#E5E7EB" stroke-width="2"/>
                                    <path d="M 80 200 L 160 210 L 240 195 L 300 200 L 360 190 L 440 205 L 500 200" fill="none" stroke="#E5E7EB" stroke-width="2"/>
                                    
                                    <!-- Country markers with animations -->
                                    <circle class="country-marker marker-1" cx="150" cy="120" r="8" fill="#6366F1" opacity="0.3">
                                        <title>USA - 450 Sales</title>
                                    </circle>
                                    <circle class="country-marker marker-2" cx="300" cy="145" r="12" fill="#6366F1" opacity="0.5">
                                        <title>Africa - 890 Sales</title>
                                    </circle>
                                    <circle class="country-marker marker-3" cx="420" cy="140" r="10" fill="#6366F1" opacity="0.4">
                                        <title>Asia - 1200 Sales</title>
                                    </circle>
                                    <circle class="country-marker marker-4" cx="240" cy="195" r="14" fill="#6366F1" opacity="0.6">
                                        <title>Brazil - 615 Sales</title>
                                    </circle>
                                    <circle class="country-marker marker-5" cx="500" cy="155" r="9" fill="#6366F1" opacity="0.4">
                                        <title>Indonesia - 300 Sales</title>
                                    </circle>
                                    
                                    <!-- Labels -->
                                    <text x="150" y="115" text-anchor="middle" fill="#6B7280" font-size="11" font-weight="500">USA</text>
                                    <text x="300" y="140" text-anchor="middle" fill="#6B7280" font-size="11" font-weight="500">Africa</text>
                                    <text x="420" y="135" text-anchor="middle" fill="#6B7280" font-size="11" font-weight="500">Asia</text>
                                    <text x="240" y="190" text-anchor="middle" fill="#6B7280" font-size="11" font-weight="500">Brazil</text>
                                    <text x="500" y="150" text-anchor="middle" fill="#6B7280" font-size="11" font-weight="500">Indo</text>
                                </svg>
                            </div>
                            
                            <!-- Sales Data with adjusted typography -->
                            <div style="text-align: center; margin-top: 1.5rem;">
                                <div style="display: flex; align-items: baseline; justify-content: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                                    <span style="font-size: 2.25rem; font-weight: 700; color: var(--text-primary); line-height: 1;">3,455</span>
                                    <span style="font-size: 1rem; font-weight: 500; color: var(--text-secondary);">Sales</span>
                                </div>
                                <div style="display: flex; align-items: center; justify-content: center; gap: 0.4rem; color: #10B981; font-weight: 500; font-size: 0.9rem;">
                                    <i class="fas fa-arrow-up"></i>
                                    <span>48% increase compared to last week</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Orders Chart -->
                <div class="chart-card" style="margin-bottom: 2rem;">
                    <div class="chart-header">
                        <h3 class="chart-title">Đơn hàng theo khu vực</h3>
                        <div class="chart-actions">
                            <button class="chart-btn">Xuất báo cáo</button>
                        </div>
                    </div>
                    <div class="chart-container">
                        <canvas id="ordersChart"></canvas>
                    </div>
                </div>
                
                <!-- Recent Sales Table -->
                <div class="table-card">
                    <div class="table-header">
                        <h3 class="table-title">Đơn hàng gần đây</h3>
                        <div class="table-actions">
                            <button class="btn btn-secondary">
                                <i class="fas fa-filter"></i>
                                Lọc
                            </button>
                            <button class="btn btn-primary">
                                <i class="fas fa-plus"></i>
                                Thêm mới
                            </button>
                        </div>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Sản phẩm</th>
                                    <th>Danh mục</th>
                                    <th>Số lượng</th>
                                    <th>Giá trị</th>
                                    <th>Khu vực</th>
                                    <th>Ngày</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_sales as $sale): ?>
                                <tr>
                                    <td>#<?php echo str_pad($sale['id'], 4, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo htmlspecialchars($sale['product_name']); ?></td>
                                    <td><?php echo htmlspecialchars($sale['category']); ?></td>
                                    <td><?php echo number_format($sale['quantity']); ?></td>
                                    <td><?php echo number_format($sale['amount']); ?> ₫</td>
                                    <td><?php echo htmlspecialchars($sale['region']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($sale['sale_date'])); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $sale['status']; ?>">
                                            <?php 
                                            $status_map = [
                                                'completed' => 'Hoàn thành',
                                                'pending' => 'Chờ xử lý',
                                                'cancelled' => 'Đã hủy'
                                            ];
                                            echo $status_map[$sale['status']] ?? $sale['status'];
                                            ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- JavaScript -->
    <script src="js/main.js"></script>
</body>
</html>
