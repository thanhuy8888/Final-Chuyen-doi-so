<?php
session_start();
require_once 'config.php';
require_once 'db.php';

// Handle Add Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    try {
        $product_name = $_POST['product_name'];
        $category = $_POST['category'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];
        $image_url = !empty($_POST['image_url']) ? $_POST['image_url'] : 'https://via.placeholder.com/150'; // Default image
        $status = 'active'; // Default status
        $description = $_POST['description'] ?? '';
        
        $sql = "INSERT INTO products (product_name, category, price, stock, image_url, description, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$product_name, $category, $price, $stock, $image_url, $description, $status]);
        
        // Refresh to show new data
        header("Location: products.php");
        exit();
    } catch (PDOException $e) {
        $error = "Lỗi thêm sản phẩm: " . $e->getMessage();
    }
}

// Get all products
try {
    $sql = "SELECT * FROM products ORDER BY created_at DESC LIMIT 50";
    $stmt = $conn->query($sql);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get statistics
    $stats_sql = "SELECT 
        COUNT(*) as total_products,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_products,
        SUM(stock) as total_stock,
        AVG(price) as avg_price
        FROM products";
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
    <title>Quản lý Sản phẩm - MSDB</title>
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
            margin: 5% auto;
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
            margin-bottom: 1.25rem;
        }
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-secondary);
            font-size: 0.875rem;
            font-weight: 500;
        }
        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            background-color: var(--bg-secondary);
            color: var(--text-primary);
            font-size: 0.95rem;
            transition: all 0.2s;
            font-family: inherit;
        }
        .form-input:focus, .form-select:focus, .form-textarea:focus {
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
                    <a href="customers.php" class="nav-item">
                        <i class="fas fa-users"></i>
                        <span>Khách hàng</span>
                    </a>
                    <a href="products.php" class="nav-item active">
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
                        <input type="text" placeholder="Tìm kiếm sản phẩm...">
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
                    <h1 class="page-title">Quản lý Sản phẩm</h1>
                    <p class="page-subtitle">Quản lý kho hàng và danh mục sản phẩm</p>
                </div>

                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card fade-in">
                        <div class="stat-header">
                            <div class="stat-title">Tổng sản phẩm</div>
                            <div class="stat-icon primary">
                                <i class="fas fa-box"></i>
                            </div>
                        </div>
                        <div class="stat-value"><?php echo number_format($stats['total_products'] ?? 0); ?></div>
                        <div class="stat-change positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>+12% sản phẩm mới</span>
                        </div>
                    </div>

                    <div class="stat-card fade-in-delay-1">
                        <div class="stat-header">
                            <div class="stat-title">Đang kinh doanh</div>
                            <div class="stat-icon success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                        <div class="stat-value"><?php echo number_format($stats['active_products'] ?? 0); ?></div>
                        <div class="stat-change positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>98% hàng có sẵn</span>
                        </div>
                    </div>

                    <div class="stat-card fade-in-delay-2">
                        <div class="stat-header">
                            <div class="stat-title">Tổng tồn kho</div>
                            <div class="stat-icon warning">
                                <i class="fas fa-warehouse"></i>
                            </div>
                        </div>
                        <div class="stat-value"><?php echo number_format($stats['total_stock'] ?? 0); ?></div>
                        <div class="stat-change negative">
                            <i class="fas fa-arrow-down"></i>
                            <span>Cần nhập thêm 5 mã</span>
                        </div>
                    </div>

                    <div class="stat-card fade-in-delay-3">
                        <div class="stat-header">
                            <div class="stat-title">Giá trung bình</div>
                            <div class="stat-icon info">
                                <i class="fas fa-tag"></i>
                            </div>
                        </div>
                        <div class="stat-value">₫<?php echo number_format($stats['avg_price'] ?? 0); ?></div>
                        <div class="stat-change">
                            <span>Mức giá tối ưu</span>
                        </div>
                    </div>
                </div>

                <!-- Products Table -->
                <div class="table-card">
                    <div class="table-header">
                        <h3 class="table-title">Danh sách Sản phẩm</h3>
                        <div class="table-actions">
                            <button class="btn btn-secondary">
                                <i class="fas fa-filter"></i>
                                Lọc
                            </button>
                            <button class="btn btn-primary" onclick="openModal('addProductModal')">
                                <i class="fas fa-plus"></i>
                                Thêm sản phẩm
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
                                        <th>Sản phẩm</th>
                                        <th>Danh mục</th>
                                        <th>Giá</th>
                                        <th>Tồn kho</th>
                                        <th>Trạng thái</th>
                                        <th>Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $product): ?>
                                        <tr>
                                            <td>#<?php echo $product['id']; ?></td>
                                            <td>
                                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                                    <div style="width: 40px; height: 40px; border-radius: 8px; background: #f3f4f6; overflow: hidden;">
                                                        <img src="<?php echo htmlspecialchars($product['image_url'] ?? 'https://via.placeholder.com/40'); ?>" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                                                    </div>
                                                    <div style="font-weight: 500;"><?php echo htmlspecialchars($product['product_name']); ?></div>
                                                </div>
                                            </td>
                                            <td>
                                                <span style="padding: 0.25rem 0.75rem; background: var(--bg-secondary); border-radius: 999px; font-size: 0.85rem; color: var(--text-secondary);">
                                                    <?php echo htmlspecialchars($product['category']); ?>
                                                </span>
                                            </td>
                                            <td>₫<?php echo number_format($product['price']); ?></td>
                                            <td><?php echo number_format($product['stock']); ?></td>
                                            <td>
                                                <span class="status-badge <?php echo $product['status'] == 'active' ? 'completed' : 'cancelled'; ?>">
                                                    <?php echo $product['status'] == 'active' ? 'Đang bán' : 'Ngừng bán'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-secondary" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-secondary" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">
                                                    <i class="fas fa-trash"></i>
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

    <!-- Add Product Modal -->
    <div id="addProductModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('addProductModal')">&times;</span>
            <h2 class="modal-title">Thêm Sản Phẩm Mới</h2>
            <form action="" method="POST">
                <input type="hidden" name="add_product" value="1">
                <div class="form-group">
                    <label class="form-label">Tên sản phẩm</label>
                    <input type="text" name="product_name" class="form-input" required placeholder="Nhập tên sản phẩm...">
                </div>
                <div class="form-group">
                    <label class="form-label">Danh mục</label>
                    <select name="category" class="form-select" required>
                        <option value="Electronics">Electronics</option>
                        <option value="Clothing">Clothing</option>
                        <option value="Home & Garden">Home & Garden</option>
                        <option value="Books">Books</option>
                        <option value="Beauty">Beauty</option>
                        <option value="Sports">Sports</option>
                    </select>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Giá (VNĐ)</label>
                        <input type="number" name="price" class="form-input" required placeholder="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Tồn kho</label>
                        <input type="number" name="stock" class="form-input" required placeholder="0">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Link hình ảnh (URL)</label>
                    <input type="url" name="image_url" class="form-input" placeholder="https://example.com/image.jpg">
                </div>
                <div class="form-group">
                    <label class="form-label">Mô tả</label>
                    <textarea name="description" class="form-textarea" rows="3" placeholder="Mô tả sản phẩm..."></textarea>
                </div>
                <div style="text-align: right; margin-top: 2rem;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('addProductModal')" style="margin-right: 1rem;">Hủy</button>
                    <button type="submit" class="btn btn-primary">Thêm Sản Phẩm</button>
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
