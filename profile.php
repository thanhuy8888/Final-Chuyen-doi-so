<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hồ sơ - <?php echo SITE_NAME; ?></title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="dashboard-container">
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
                    <a href="analytics.php" class="nav-item">
                        <i class="fas fa-chart-bar"></i>
                        <span>Phân tích</span>
                    </a>
                    <a href="#" class="nav-item">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Đơn hàng</span>
                    </a>
                    <a href="#" class="nav-item">
                        <i class="fas fa-users"></i>
                        <span>Khách hàng</span>
                    </a>
                    <a href="#" class="nav-item">
                        <i class="fas fa-box"></i>
                        <span>Sản phẩm</span>
                    </a>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Quản lý</div>
                    <a href="#" class="nav-item">
                        <i class="fas fa-file-invoice"></i>
                        <span>Báo cáo</span>
                    </a>
                    <a href="#" class="nav-item">
                        <i class="fas fa-cog"></i>
                        <span>Cài đặt</span>
                    </a>
                    <a href="profile.php" class="nav-item active">
                        <i class="fas fa-user-circle"></i>
                        <span>Hồ sơ</span>
                    </a>
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
                    <h1 class="page-title">Hồ sơ người dùng</h1>
                    <p class="page-subtitle">Quản lý thông tin cá nhân của bạn</p>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <div style="text-align: center;">
                            <div style="width: 120px; height: 120px; margin: 0 auto 1rem; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; font-size: 3rem; box-shadow: 0 0 30px rgba(102, 126, 234, 0.4);">
                                <i class="fas fa-user"></i>
                            </div>
                            <h2 style="font-size: 1.5rem; margin-bottom: 0.5rem;">Administrator</h2>
                            <p style="color: var(--text-secondary);">admin@dashboard.com</p>
                            <button class="btn btn-primary" style="margin-top: 1rem;">
                                <i class="fas fa-edit"></i> Chỉnh sửa
                            </button>
                        </div>
                    </div>
                    
                    <div class="stat-card" style="grid-column: span 2;">
                        <h3 style="margin-bottom: 1.5rem; font-size: 1.2rem;">Thông tin cá nhân</h3>
                        <div style="display: grid; gap: 1rem;">
                            <div style="display: flex; justify-content: space-between; padding: 0.75rem 0; border-bottom: 1px solid var(--border);">
                                <span style="color: var(--text-secondary);">Họ và tên:</span>
                                <strong>Administrator</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 0.75rem 0; border-bottom: 1px solid var(--border);">
                                <span style="color: var(--text-secondary);">Email:</span>
                                <strong>admin@dashboard.com</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 0.75rem 0; border-bottom: 1px solid var(--border);">
                                <span style="color: var(--text-secondary);">Vai trò:</span>
                                <strong>Quản trị viên</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 0.75rem 0; border-bottom: 1px solid var(--border);">
                                <span style="color: var(--text-secondary);">Ngày tạo:</span>
                                <strong>01/12/2024</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 0.75rem 0;">
                                <span style="color: var(--text-secondary);">Đăng nhập lần cuối:</span>
                                <strong>Hôm nay, 21:30</strong>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="table-card" style="margin-top: 2rem;">
                    <div class="table-header">
                        <h3 class="table-title">Hoạt động gần đây</h3>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Hành động</th>
                                    <th>Mô tả</th>
                                    <th>Thời gian</th>
                                    <th>IP Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><i class="fas fa-sign-in-alt" style="color: var(--success);"></i> Đăng nhập</td>
                                    <td>Đăng nhập thành công</td>
                                    <td>Hôm nay, 21:30</td>
                                    <td>127.0.0.1</td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-file-export" style="color: var(--primary);"></i> Xuất báo cáo</td>
                                    <td>Xuất báo cáo doanh thu tháng 12</td>
                                    <td>Hôm qua, 15:45</td>
                                    <td>127.0.0.1</td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-edit" style="color: var(--warning);"></i> Cập nhật</td>
                                    <td>Cập nhật thông tin sản phẩm</td>
                                    <td>02/12/2024, 10:20</td>
                                    <td>127.0.0.1</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="assets/js/main.js"></script>
</body>
</html>
