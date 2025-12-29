<?php
session_start();
require_once 'config.php';
require_once 'db.php';

// Fake login để test (xóa khi có login thật)
// $_SESSION['user_id'] = 1; $_SESSION['role'] = 'admin';

// Lấy danh sách khảo sát
try {
    $sql = "SELECT s.*, c.full_name AS customer_name, c.email, o.id AS order_id
            FROM surveys s
            LEFT JOIN customers c ON s.customer_id = c.id
            LEFT JOIN orders o ON s.order_id = o.id
            ORDER BY s.created_at DESC
            LIMIT 100";
    $surveys = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    // Thống kê
    $stats_sql = "SELECT 
        COUNT(*) AS total_surveys,
        AVG(rating) AS avg_rating,
        SUM(CASE WHEN rating >= 4 THEN 1 ELSE 0 END) / COUNT(*) * 100 AS satisfaction_rate,
        COUNT(DISTINCT customer_id) AS unique_customers
        FROM surveys";
    $stats = $conn->query($stats_sql)->fetch(PDO::FETCH_ASSOC);

    $avg_rating = round($stats['avg_rating'] ?? 0, 1);
    $satisfaction_rate = round($stats['satisfaction_rate'] ?? 0, 1);
} catch (PDOException $e) {
    $error = "Lỗi kết nối database: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khảo sát Khách hàng - MSDB</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(4px); }
        .modal-content { background-color: var(--bg-primary); margin: 5% auto; padding: 2rem; border: 1px solid var(--border); width: 100%; max-width: 600px; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); animation: slideIn 0.3s ease-out; }
        @keyframes slideIn { from { transform: translateY(-20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .close { color: var(--text-secondary); float: right; font-size: 1.5rem; font-weight: bold; cursor: pointer; }
        .close:hover { color: var(--text-primary); }
        .rating-stars { color: #fbbf24; font-size: 1.1rem; }
        .comment-box { background: var(--bg-secondary); padding: 1rem; border-radius: 8px; margin-top: 0.5rem; font-style: italic; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo"><i class="fas fa-chart-line"></i></div>
                <div class="logo-text">MSDB</div>
            </div>
            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-section-title">Menu chính</div>
                    <a href="index.php" class="nav-item"><i class="fas fa-home"></i><span>Tổng quan</span></a>
                    <a href="analytics.php" class="nav-item"><i class="fas fa-chart-bar"></i><span>Phân tích</span></a>
                </div>
                <div class="nav-section">
                    <div class="nav-section-title">Quản lý</div>
                    <a href="orders.php" class="nav-item"><i class="fas fa-shopping-cart"></i><span>Đơn hàng</span></a>
                    <a href="customers.php" class="nav-item"><i class="fas fa-users"></i><span>Khách hàng</span></a>
                    <a href="products.php" class="nav-item"><i class="fas fa-box"></i><span>Sản phẩm</span></a>
                </div>
                <div class="nav-section">
                    <div class="nav-section-title">Khảo sát & Phân tích</div>
                    <a href="surveys.php" class="nav-item active"><i class="fas fa-poll"></i><span>Khảo sát khách hàng</span></a>
                    <a href="attrition.php" class="nav-item"><i class="fas fa-chart-pie"></i><span>Phân tích rủi ro</span></a>
                </div>
                <div class="nav-section">
                <div class="nav-section-title">Trung tâm thông tin</div>
                    <a href="profile.php" class="nav-item"><i class="fas fa-user-circle"></i><span>Hồ sơ</span></a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="header">
                <div class="header-left">
                    <button class="menu-toggle"><i class="fas fa-bars"></i></button>
                    <div class="search-box"><i class="fas fa-search"></i><input type="text" placeholder="Tìm kiếm khảo sát..."></div>
                </div>
                <div class="header-right">
                    <div class="header-icon"><i class="fas fa-bell"></i><span class="badge">3</span></div>
                    <div class="user-profile"><div class="user-avatar">A</div><div class="user-info"><div class="user-name">Admin</div><div class="user-role">Administrator</div></div></div>
                </div>
            </header>

            <div class="content">
                <div class="page-header">
                    <h1 class="page-title">Khảo sát Khách hàng</h1>
                    <p class="page-subtitle">Theo dõi phản hồi và mức độ hài lòng của khách hàng</p>
                </div>

                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card fade-in">
                        <div class="stat-header"><div class="stat-title">Tổng khảo sát</div><div class="stat-icon primary"><i class="fas fa-poll"></i></div></div>
                        <div class="stat-value"><?php echo number_format($stats['total_surveys'] ?? 0); ?></div>
                    </div>
                    <div class="stat-card fade-in-delay-1">
                        <div class="stat-header"><div class="stat-title">Điểm trung bình</div><div class="stat-icon success"><i class="fas fa-star"></i></div></div>
                        <div class="stat-value"><?php echo $avg_rating; ?>/5.0</div>
                    </div>
                    <div class="stat-card fade-in-delay-2">
                        <div class="stat-header"><div class="stat-title">% Hài lòng (≥4★)</div><div class="stat-icon info"><i class="fas fa-smile"></i></div></div>
                        <div class="stat-value"><?php echo $satisfaction_rate; ?>%</div>
                    </div>
                    <div class="stat-card fade-in-delay-3">
                        <div class="stat-header"><div class="stat-title">Khách hàng tham gia</div><div class="stat-icon warning"><i class="fas fa-users"></i></div></div>
                        <div class="stat-value"><?php echo number_format($stats['unique_customers'] ?? 0); ?></div>
                    </div>
                </div>

                <!-- Surveys Table -->
                <div class="table-card">
                    <div class="table-header">
                        <h3 class="table-title">Danh sách Phản hồi</h3>
                    </div>
                    <div class="table-container">
                        <?php if (isset($error)): ?>
                            <div style="padding: 2rem; text-align: center; color: var(--danger);">
                                <p><?php echo htmlspecialchars($error); ?></p>
                                <p style="margin-top:1rem;color:var(--text-secondary);">Hãy tạo bảng <code>surveys</code> trong database.</p>
                            </div>
                        <?php else: ?>
                            <table>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Khách hàng</th>
                                        <th>Đơn hàng</th>
                                        <th>Đánh giá</th>
                                        <th>Nhận xét</th>
                                        <th>Ngày gửi</th>
                                        <th>Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($surveys as $s): ?>
                                    <tr>
                                        <td>#<?php echo $s['id']; ?></td>
                                        <td>
                                            <div><?php echo htmlspecialchars($s['customer_name'] ?? 'N/A'); ?></div>
                                            <div style="font-size:0.85rem;color:var(--text-muted);"><?php echo htmlspecialchars($s['email'] ?? ''); ?></div>
                                        </td>
                                        <td><?php echo $s['order_id'] ? '#' . $s['order_id'] : '—'; ?></td>
                                        <td>
                                            <span class="rating-stars">
                                                <?php for($i=1;$i<=5;$i++): ?>
                                                    <i class="fa<?php echo $i <= $s['rating'] ? 's' : 'r'; ?> fa-star"></i>
                                                <?php endfor; ?>
                                            </span>
                                            <strong><?php echo $s['rating']; ?>/5</strong>
                                        </td>
                                        <td>
                                            <?php if ($s['comment']): ?>
                                                <button class="btn btn-secondary" style="padding:0.4rem 0.8rem;font-size:0.85rem;" onclick="openModal('survey<?php echo $s['id']; ?>')">
                                                    Xem nhận xét
                                                </button>
                                            <?php else: echo '—'; endif; ?>
                                        </td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($s['created_at'])); ?></td>
                                        <td>
                                            <button class="btn btn-secondary" style="padding:0.4rem 0.8rem;font-size:0.85rem;" onclick="openModal('survey<?php echo $s['id']; ?>')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Modal chi tiết nhận xét -->
                                    <div id="survey<?php echo $s['id']; ?>" class="modal">
                                        <div class="modal-content">
                                            <span class="close" onclick="closeModal('survey<?php echo $s['id']; ?>')">&times;</span>
                                            <h2 class="modal-title">Phản hồi từ khách hàng</h2>
                                            <p><strong>Khách hàng:</strong> <?php echo htmlspecialchars($s['customer_name'] ?? 'N/A'); ?></p>
                                            <p><strong>Đánh giá:</strong> <?php echo $s['rating']; ?>/5 <span class="rating-stars"><?php for($i=1;$i<=5;$i++) echo '<i class="fa'.($i<=$s['rating']?'s':'r').' fa-star"></i>'; ?></span></p>
                                            <p><strong>Nhận xét:</strong></p>
                                            <div class="comment-box"><?php echo nl2br(htmlspecialchars($s['comment'] ?? 'Không có nhận xét.')); ?></div>
                                            <div style="text-align:right;margin-top:2rem;">
                                                <button type="button" class="btn btn-secondary" onclick="closeModal('survey<?php echo $s['id']; ?>')">Đóng</button>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function openModal(id) { document.getElementById(id).style.display = 'block'; }
        function closeModal(id) { document.getElementById(id).style.display = 'none'; }
        window.onclick = function(e) { if (e.target.classList.contains('modal')) e.target.style.display = 'none'; }
    </script>
</body>
</html>