<?php
require_once 'config.php';
require_once 'db.php';

// Fetch detailed analytics
try {
    // Sales by month for the year
    $stmt = $conn->query("
        SELECT DATE_FORMAT(sale_date, '%Y-%m') as month, SUM(amount) as revenue, COUNT(*) as orders
        FROM sales 
        WHERE sale_date >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY month
        ORDER BY month ASC
    ");
    $monthly_data = $stmt->fetchAll();
    
    // Sales by region
    $stmt = $conn->query("
        SELECT region, SUM(amount) as revenue, COUNT(*) as orders
        FROM sales 
        WHERE status = 'completed'
        GROUP BY region
        ORDER BY revenue DESC
    ");
    $regional_data = $stmt->fetchAll();
    
    // Sales by category
    $stmt = $conn->query("
        SELECT category, SUM(amount) as revenue, COUNT(*) as orders
        FROM sales 
        WHERE status = 'completed'
        GROUP BY category
        ORDER BY revenue DESC
    ");
    $category_data = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $monthly_data = [];
    $regional_data = [];
    $category_data = [];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phân tích - <?php echo SITE_NAME; ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
    <div class="dashboard-container">
        <!-- Same Sidebar as index.php -->
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
                    <a href="index.php" class="nav-item">
                        <i class="fas fa-home"></i>
                        <span>Tổng quan</span>
                    </a>
                    <a href="analytics.php" class="nav-item active">
                        <i class="fas fa-chart-bar"></i>
                        <span>Phân tích</span>
                    </a>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Quản lý</div>
                    <a href="orders.php" class="nav-item">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Đơn hàng</span>
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
        
        <main class="main-content">
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
            
            <div class="content">
                <div class="page-header">
                    <h1 class="page-title">Phân tích chi tiết</h1>
                    <p class="page-subtitle">Xem chi tiết phân tích dữ liệu kinh doanh</p>
                </div>
                
                <!-- Regional Analysis -->
                <div class="table-card" style="margin-bottom: 2rem;">
                    <div class="table-header">
                        <h3 class="table-title">Phân tích theo khu vực</h3>
                        <div class="table-actions">
                            <button class="btn btn-primary">
                                <i class="fas fa-download"></i>
                                Xuất Excel
                            </button>
                        </div>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Khu vực</th>
                                    <th>Doanh thu</th>
                                    <th>Đơn hàng</th>
                                    <th>Trung bình/đơn</th>
                                    <th>Tỷ trọng</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $total_revenue = array_sum(array_column($regional_data, 'revenue'));
                                foreach ($regional_data as $region): 
                                    $avg_order = $region['orders'] > 0 ? $region['revenue'] / $region['orders'] : 0;
                                    $percentage = $total_revenue > 0 ? ($region['revenue'] / $total_revenue) * 100 : 0;
                                ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($region['region']); ?></strong></td>
                                    <td><?php echo number_format($region['revenue']); ?> ₫</td>
                                    <td><?php echo number_format($region['orders']); ?></td>
                                    <td><?php echo number_format($avg_order); ?> ₫</td>
                                    <td><?php echo number_format($percentage, 1); ?>%</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Category Analysis -->
                <div class="table-card" style="margin-bottom: 2rem;">
                    <div class="table-header">
                        <h3 class="table-title">Phân tích theo danh mục</h3>
                        <div class="table-actions">
                            <button class="btn btn-secondary">
                                <i class="fas fa-filter"></i>
                                Lọc
                            </button>
                        </div>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Danh mục</th>
                                    <th>Doanh thu</th>
                                    <th>Đơn hàng</th>
                                    <th>Trung bình/đơn</th>
                                    <th>Tỷ trọng</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $total_revenue = array_sum(array_column($category_data, 'revenue'));
                                foreach ($category_data as $category): 
                                    $avg_order = $category['orders'] > 0 ? $category['revenue'] / $category['orders'] : 0;
                                    $percentage = $total_revenue > 0 ? ($category['revenue'] / $total_revenue) * 100 : 0;
                                ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($category['category']); ?></strong></td>
                                    <td><?php echo number_format($category['revenue']); ?> ₫</td>
                                    <td><?php echo number_format($category['orders']); ?></td>
                                    <td><?php echo number_format($avg_order); ?> ₫</td>
                                    <td><?php echo number_format($percentage, 1); ?>%</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="js/main.js"></script>
</body>
</html>
