<?php
session_start();
require_once 'config.php';
require_once 'db.php';

// === CSRF Token ===
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// === Xử lý tạo đơn hàng mới ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_order'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $error = "Token không hợp lệ.";
    } else {
        $customer_id = intval($_POST['customer_id']);
        $product_id  = intval($_POST['product_id']);
        $quantity    = intval($_POST['quantity']);
        $status      = in_array($_POST['status'], ['pending', 'processing', 'shipped']) ? $_POST['status'] : 'pending';

        if ($customer_id <= 0 || $product_id <= 0 || $quantity <= 0) {
            $error = "Vui lòng chọn đầy đủ thông tin hợp lệ.";
        } else {
            try {
                // Lấy giá sản phẩm
                $stmt = $conn->prepare("SELECT price, stock FROM products WHERE id = ? AND status = 'active'");
                $stmt->execute([$product_id]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$product) {
                    $error = "Sản phẩm không tồn tại hoặc đã ngừng bán.";
                } elseif ($product['stock'] < $quantity) {
                    $error = "Số lượng tồn kho không đủ (chỉ còn {$product['stock']} sản phẩm).";
                } else {
                    $total_amount = $product['price'] * $quantity;

                    // Thêm đơn hàng
                    $sql = "INSERT INTO orders (customer_id, product_id, quantity, total_amount, status, order_date)
                            VALUES (?, ?, ?, ?, ?, NOW())";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$customer_id, $product_id, $quantity, $total_amount, $status]);

                    // Giảm tồn kho (tuỳ chọn)
                    $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?")
                         ->execute([$quantity, $product_id]);

                    $_SESSION['success'] = "Tạo đơn hàng thành công!";
                    header("Location: orders.php");
                    exit();
                }
            } catch (PDOException $e) {
                $error = "Lỗi khi tạo đơn hàng: " . $e->getMessage();
            }
        }
    }
}

// === Lấy danh sách đơn hàng ===
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

    // === Thống kê ===
    $stats_sql = "SELECT 
        COUNT(*) as total_orders,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
        SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
        SUM(total_amount) as total_revenue
        FROM orders";
    $stats = $conn->query($stats_sql)->fetch(PDO::FETCH_ASSOC);

    // Biến dùng cho badge ở sidebar
    $pending_orders = $stats['pending_orders'] ?? 0;

    // === Load danh sách khách hàng và sản phẩm cho modal ===
    $customers = $conn->query("SELECT id, full_name, email FROM customers ORDER BY full_name")->fetchAll(PDO::FETCH_ASSOC);
    $products_list = $conn->query("SELECT id, product_name, price, stock FROM products WHERE status = 'active' ORDER BY product_name")->fetchAll(PDO::FETCH_ASSOC);

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
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(4px); }
        .modal-content { background-color: var(--bg-primary); margin: 5% auto; padding: 2rem; border: 1px solid var(--border); width: 100%; max-width: 500px; border-radius: 16px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); animation: slideIn 0.3s ease-out; }
        @keyframes slideIn { from { transform: translateY(-20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .close { color: var(--text-secondary); float: right; font-size: 1.5rem; font-weight: bold; cursor: pointer; }
        .close:hover { color: var(--text-primary); }
        .form-group { margin-bottom: 1.25rem; }
        .form-label { display: block; margin-bottom: 0.5rem; color: var(--text-secondary); font-size: 0.875rem; font-weight: 500; }
        .form-input, .form-select { width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border); border-radius: 8px; background-color: var(--bg-secondary); color: var(--text-primary); font-size: 0.95rem; }
        .form-input:focus, .form-select:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(99,102,241,0.1); }
        .modal-title { font-size: 1.5rem; font-weight: 700; margin-bottom: 1.5rem; color: var(--text-primary); }
        .alert { padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; }
        .alert.success { background: rgba(34,197,94,0.1); color: #16a34a; border: 1px solid rgba(34,197,94,0.3); }
        .alert.danger { background: rgba(239,68,68,0.1); color: #dc2626; border: 1px solid rgba(239,68,68,0.3); }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar (giữ nguyên, chỉ sửa badge) -->
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
                    <a href="orders.php" class="nav-item active">
                        <i class="fas fa-shopping-cart"></i><span>Đơn hàng</span>
                        <span class="nav-badge"><?php echo $pending_orders; ?></span>
                    </a>
                    <a href="customers.php" class="nav-item"><i class="fas fa-users"></i><span>Khách hàng</span></a>
                    <a href="products.php" class="nav-item"><i class="fas fa-box"></i><span>Sản phẩm</span></a>                
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
            <!-- Header giữ nguyên -->
            <header class="header">
                <div class="header-left">
                    <button class="menu-toggle"><i class="fas fa-bars"></i></button>
                    <div class="search-box"><i class="fas fa-search"></i><input type="text" placeholder="Tìm kiếm đơn hàng..."></div>
                </div>
                <div class="header-right">
                    <div class="header-icon"><i class="fas fa-bell"></i><span class="badge">3</span></div>
                    <div class="user-profile">
                        <div class="user-avatar">A</div>
                        <div class="user-info">
                            <div class="user-name">Admin</div>
                            <div class="user-role">Administrator</div>
                        </div>
                    </div>
                </div>
            </header>

            <div class="content">
                <div class="page-header">
                    <h1 class="page-title">Quản lý Đơn hàng</h1>
                    <p class="page-subtitle">Theo dõi và quản lý tất cả đơn hàng</p>
                </div>

                <!-- Thông báo thành công / lỗi -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="alert danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <!-- Stats Grid (giữ nguyên) -->
                <div class="stats-grid">
                    <div class="stat-card fade-in">
                        <div class="stat-header"><div class="stat-title">Tổng đơn hàng</div><div class="stat-icon primary"><i class="fas fa-shopping-cart"></i></div></div>
                        <div class="stat-value"><?php echo number_format($stats['total_orders'] ?? 0); ?></div>
                        <div class="stat-change positive"><i class="fas fa-arrow-up"></i><span>+12% so với tháng trước</span></div>
                    </div>
                    <div class="stat-card fade-in-delay-1">
                        <div class="stat-header"><div class="stat-title">Chờ xử lý</div><div class="stat-icon warning"><i class="fas fa-clock"></i></div></div>
                        <div class="stat-value"><?php echo number_format($stats['pending_orders'] ?? 0); ?></div>
                        <div class="stat-change"><span>Cần xử lý</span></div>
                    </div>
                    <div class="stat-card fade-in-delay-2">
                        <div class="stat-header"><div class="stat-title">Đã giao</div><div class="stat-icon success"><i class="fas fa-check-circle"></i></div></div>
                        <div class="stat-value"><?php echo number_format($stats['delivered_orders'] ?? 0); ?></div>
                        <div class="stat-change positive"><i class="fas fa-arrow-up"></i><span>+8.5%</span></div>
                    </div>
                    <div class="stat-card fade-in-delay-3">
                        <div class="stat-header"><div class="stat-title">Tổng doanh thu</div><div class="stat-icon danger"><i class="fas fa-dollar-sign"></i></div></div>
                        <div class="stat-value">₫<?php echo number_format($stats['total_revenue'] ?? 0); ?></div>
                        <div class="stat-change positive"><i class="fas fa-arrow-up"></i><span>+15.3%</span></div>
                    </div>
                </div>

                <!-- Orders Table -->
                <div class="table-card">
                    <div class="table-header">
                        <h3 class="table-title">Danh sách Đơn hàng</h3>
                        <div class="table-actions">
                            <button class="btn btn-secondary"><i class="fas fa-filter"></i> Lọc</button>
                            <button class="btn btn-primary" onclick="openModal('addOrderModal')">
                                <i class="fas fa-plus"></i> Tạo đơn mới
                            </button>
                        </div>
                    </div>
                    <div class="table-container">
                        <?php if (isset($error) && !isset($_SESSION['success'])): ?>
                            <div style="padding: 2rem; text-align: center; color: var(--danger);">
                                <i class="fas fa-exclamation-triangle" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                                <p><?php echo htmlspecialchars($error); ?></p>
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
                                                $statusMap = [
                                                    'delivered' => ['completed', 'Đã giao'],
                                                    'pending'   => ['pending', 'Chờ xử lý'],
                                                    'processing'=> ['pending', 'Đang xử lý'],
                                                    'shipped'   => ['completed', 'Đang giao'],
                                                    'cancelled' => ['cancelled', 'Đã hủy']
                                                ];
                                                $s = $statusMap[$order['status']] ?? ['pending', 'Không xác định'];
                                                ?>
                                                <span class="status-badge <?php echo $s[0]; ?>"><?php echo $s[1]; ?></span>
                                            </td>
                                            <td>
                                                <button class="btn btn-secondary" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;"><i class="fas fa-eye"></i></button>
                                                <button class="btn btn-secondary" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;"><i class="fas fa-edit"></i></button>
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

    <!-- Modal Tạo Đơn Hàng -->
    <div id="addOrderModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('addOrderModal')">&times;</span>
            <h2 class="modal-title">Tạo Đơn Hàng Mới</h2>
            <form action="" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="add_order" value="1">

                <div class="form-group">
                    <label class="form-label">Khách hàng</label>
                    <select name="customer_id" class="form-select" required>
                        <option value="">-- Chọn khách hàng --</option>
                        <?php foreach ($customers as $cust): ?>
                            <option value="<?php echo $cust['id']; ?>">
                                <?php echo htmlspecialchars($cust['full_name'] . ' (' . $cust['email'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Sản phẩm</label>
                    <select name="product_id" class="form-select" required>
                        <option value="">-- Chọn sản phẩm --</option>
                        <?php foreach ($products_list as $prod): ?>
                            <option value="<?php echo $prod['id']; ?>">
                                <?php echo htmlspecialchars($prod['product_name'] . ' - ₫' . number_format($prod['price']) . ' (Tồn: ' . $prod['stock'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Số lượng</label>
                    <input type="number" name="quantity" class="form-input" min="1" value="1" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Trạng thái ban đầu</label>
                    <select name="status" class="form-select">
                        <option value="pending">Chờ xử lý</option>
                        <option value="processing">Đang xử lý</option>
                        <option value="shipped">Đang giao</option>
                    </select>
                </div>

                <div style="text-align: right; margin-top: 2rem;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addOrderModal')" style="margin-right: 1rem;">Hủy</button>
                    <button type="submit" class="btn btn-primary">Tạo Đơn Hàng</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/main.js"></script>
    <script>
        function openModal(id) { document.getElementById(id).style.display = 'block'; }
        function closeModal(id) { document.getElementById(id).style.display = 'none'; }
        window.onclick = function(e) {
            if (e.target.classList.contains('modal')) e.target.style.display = 'none';
        }
    </script>
</body>
</html>