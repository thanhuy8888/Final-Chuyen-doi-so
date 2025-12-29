<?php
session_start();
require_once 'config.php';
require_once 'db.php';

// Fake login để test (xóa khi có login thật)
// $_SESSION['user_id'] = 1; $_SESSION['role'] = 'admin';

try {
    // Lấy dữ liệu churn theo tháng (chỉ các tháng có dữ liệu)
    $churn_sql = "SELECT 
        DATE_FORMAT(churn_date, '%Y-%m') AS month,
        COUNT(*) AS churn_count
        FROM churn_reasons
        WHERE churn_date IS NOT NULL
          AND churn_date >= DATE_SUB(CURDATE(), INTERVAL 18 MONTH)
        GROUP BY month
        ORDER BY month";
    $churn_data = $conn->query($churn_sql)->fetchAll(PDO::FETCH_ASSOC);

    $has_churn_data = !empty($churn_data) && count($churn_data) > 1;

    // Top nguyên nhân
    $reason_sql = "SELECT reason, COUNT(*) AS count
                   FROM churn_reasons
                   WHERE reason IS NOT NULL AND TRIM(reason) != ''
                   GROUP BY reason
                   ORDER BY count DESC
                   LIMIT 8";
    $top_reasons = $conn->query($reason_sql)->fetchAll(PDO::FETCH_ASSOC);

    // Tổng churn
    $total_churn = $conn->query("SELECT COUNT(*) FROM churn_reasons")->fetchColumn();
    $total_customers = $conn->query("SELECT COUNT(*) FROM customers")->fetchColumn();
    $churn_rate = $total_customers > 0 ? round(($total_churn / $total_customers) * 100, 1) : 0;
} catch (PDOException $e) {
    $error = "Lỗi kết nối database: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phân tích Nguyên nhân Rời bỏ - MSDB</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .charts-section {
            margin-top: 2.5rem;
            display: grid;
            grid-template-columns: 1fr;
            gap: 2.5rem;
        }

        @media (min-width: 992px) {
            .charts-section {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .chart-container {
            background: var(--bg-primary);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            height: 520px;
        }

        .chart-container:hover {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            transform: translateY(-4px);
            transition: all 0.3s ease;
        }

        .chart-title {
            font-size: 1.35rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .chart-title i {
            color: var(--primary);
        }

        .chart-canvas-wrapper {
            position: relative;
            flex: 1;
            min-height: 0;
        }

        .chart-canvas-wrapper canvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100% !important;
            height: 100% !important;
        }

        .no-data {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: var(--text-secondary);
            font-style: italic;
            font-size: 1.1rem;
            text-align: center;
        }

        @media (max-width: 768px) {
            .chart-container {
                padding: 1.5rem;
                height: 460px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar (giữ nguyên) -->
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
                    <a href="surveys.php" class="nav-item"><i class="fas fa-poll"></i><span>Khảo sát khách hàng</span></a>
                    <a href="attrition.php" class="nav-item active"><i class="fas fa-chart-pie"></i><span>Phân tích rời bỏ</span></a>
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
                    <button class="menu-toggle"><i class="fas fa-bars"></i></button>
                    <div class="search-box"><i class="fas fa-search"></i><input type="text" placeholder="Tìm kiếm..."></div>
                </div>
                <div class="header-right">
                    <div class="header-icon"><i class="fas fa-bell"></i><span class="badge">3</span></div>
                    <div class="user-profile"><div class="user-avatar">A</div><div class="user-info"><div class="user-name">Admin</div><div class="user-role">Administrator</div></div></div>
                </div>
            </header>

            <div class="content">
                <div class="page-header">
                    <h1 class="page-title">Phân tích Nguyên nhân Rời bỏ (Churn Analysis)</h1>
                    <p class="page-subtitle">Theo dõi xu hướng và lý do khách hàng ngừng sử dụng dịch vụ Microsoft</p>
                </div>

                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card fade-in">
                        <div class="stat-header"><div class="stat-title">Số khách hàng rời bỏ</div><div class="stat-icon danger"><i class="fas fa-user-times"></i></div></div>
                        <div class="stat-value"><?php echo number_format($total_churn); ?></div>
                        <div class="stat-change negative"><i class="fas fa-arrow-down"></i><span>Thấp hơn ngành 12%</span></div>
                    </div>
                    <div class="stat-card fade-in-delay-1">
                        <div class="stat-header"><div class="stat-title">Tỷ lệ Churn</div><div class="stat-icon warning"><i class="fas fa-percentage"></i></div></div>
                        <div class="stat-value"><?php echo $churn_rate; ?>%</div>
                        <div class="stat-change positive"><i class="fas fa-arrow-up"></i><span>Tốt hơn trung bình ngành</span></div>
                    </div>
                    <div class="stat-card fade-in-delay-2">
                        <div class="stat-header"><div class="stat-title">Tổng khách hàng</div><div class="stat-icon primary"><i class="fas fa-users"></i></div></div>
                        <div class="stat-value"><?php echo number_format($total_customers); ?></div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="charts-section">
                    <!-- Biểu đồ Xu hướng -->
                    <div class="chart-container">
                        <h3 class="chart-title"><i class="fas fa-chart-line"></i> Xu hướng khách hàng rời bỏ</h3>
                        <div class="chart-canvas-wrapper">
                            <?php if ($has_churn_data): ?>
                                <canvas id="churnTrendChart"></canvas>
                            <?php else: ?>
                                <div class="no-data">
                                    Chưa có đủ dữ liệu để hiển thị xu hướng<br>
                                    <small>(Cần ít nhất 2 tháng có khách hàng rời bỏ)</small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Biểu đồ Top nguyên nhân -->
                    <div class="chart-container">
                        <h3 class="chart-title"><i class="fas fa-exclamation-triangle"></i> Top nguyên nhân rời bỏ</h3>
                        <div class="chart-canvas-wrapper">
                            <?php if (!empty($top_reasons)): ?>
                                <canvas id="reasonChart"></canvas>
                            <?php else: ?>
                                <div class="no-data">
                                    Chưa có dữ liệu về nguyên nhân rời bỏ
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <?php if (isset($error)): ?>
                    <div style="padding: 2rem; text-align: center; color: var(--danger); margin-top: 2rem;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        <?php if ($has_churn_data): ?>
        const months = <?php echo json_encode(array_column($churn_data, 'month')); ?>;
        const churnCounts = <?php echo json_encode(array_column($churn_data, 'churn_count')); ?>;

        new Chart(document.getElementById('churnTrendChart'), {
            type: 'line',
            data: {
                labels: months,
                datasets: [{
                    label: 'Số khách hàng rời bỏ',
                    data: churnCounts,
                    borderColor: '#60a5fa',        // Pastel blue
                    backgroundColor: 'rgba(96, 165, 250, 0.15)', // Fill nhẹ
                    tension: 0.4,
                    fill: true,
                    pointRadius: 6,
                    pointHoverRadius: 10,
                    pointBackgroundColor: '#60a5fa'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', labels: { font: { size: 14 } } }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } },
                    x: { grid: { display: false } }
                }
            }
        });
        <?php endif; ?>

        <?php if (!empty($top_reasons)): ?>
        const reasons = <?php echo json_encode(array_column($top_reasons, 'reason')); ?>;
        const counts = <?php echo json_encode(array_column($top_reasons, 'count')); ?>;

        new Chart(document.getElementById('reasonChart'), {
            type: 'doughnut',
            data: {
                labels: reasons,
                datasets: [{
                    data: counts,
                    backgroundColor: [
                        '#a7f3d0', // emerald pastel
                        '#cbd5e1', // slate pastel
                        '#fdd6e3', // pink pastel
                        '#c7d2fe', // indigo pastel
                        '#fed7aa', // orange pastel
                        '#d9f99d', // lime pastel
                        '#c084fc', // purple pastel
                        '#a5f3fc'  // cyan pastel
                    ],
                    borderWidth: 4,
                    borderColor: '#fff',
                    hoverOffset: 15,
                    cutout: '68%'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 25,
                            font: { size: 13 },
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    }
                }
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>