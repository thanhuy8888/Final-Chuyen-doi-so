# MSDB - Management System Dashboard

## 🚀 Hướng dẫn cài đặt

### Yêu cầu
- Laragon (hoặc XAMPP/WAMP)
- PHP 7.4 trở lên
- MySQL 5.7 trở lên

### Các bước cài đặt

#### 1. Import Database
Mở **HeidiSQL** hoặc **phpMyAdmin** và import file `database.sql`:

**Cách 1: Sử dụng HeidiSQL (Laragon)**
1. Mở HeidiSQL từ Laragon
2. Đăng nhập với user `root` (không cần password)
3. Click menu **File > Run SQL file...**
4. Chọn file `database.sql`
5. Click **Execute**

**Cách 2: Sử dụng phpMyAdmin**
1. Truy cập http://localhost/phpmyadmin
2. Click tab **SQL**
3. Click **Import**
4. Chọn file `database.sql`
5. Click **Go**

**Cách 3: Sử dụng Command Line (nếu có MySQL CLI)**
```bash
cd c:\laragon\bin\mysql\mysql-8.0.30-winx64\bin
.\mysql.exe -u root < "c:\laragon\www\Finalchuyendoiso\database.sql"
```

#### 2. Cấu hình Database
File `config.php` đã được cấu hình sẵn với:
- Host: `localhost`
- Database: `dashboard_db`
- User: `root`
- Password: `` (để trống)

Nếu cấu hình MySQL của bạn khác, vui lòng sửa file `config.php`.

#### 3. Truy cập Website

**Dashboard chính:**
- URL: http://localhost/Finalchuyendoiso
- Hoặc: http://localhost/Finalchuyendoiso/index.php

**Trang đăng nhập:**
- URL: http://localhost/Finalchuyendoiso/login.php
- Username: `admin`
- Password: `admin123`

**Các trang khác:**
- Analytics: http://localhost/Finalchuyendoiso/analytics.php
- Profile: http://localhost/Finalchuyendoiso/profile.php

## 📁 Cấu trúc thư mục

```
Finalchuyendoiso/
├── assets/
│   ├── css/
│   │   └── style.css          # CSS chính với dark theme
│   └── js/
│       └── main.js            # JavaScript và Chart.js
├── config.php                  # Cấu hình website
├── db.php                      # Kết nối database
├── database.sql                # Database schema và sample data
├── index.php                   # Dashboard chính
├── login.php                   # Trang đăng nhập
├── analytics.php               # Trang phân tích
├── profile.php                 # Trang hồ sơ
└── README.md                   # File này
```

## ✨ Tính năng

### Dashboard Chính
- 📊 **4 Thẻ thống kê**: Doanh thu, Đơn hàng, Khách hàng, Đơn chờ
- 📈 **Biểu đồ động**:
  - Biểu đồ đường: Doanh thu theo tháng
  - Biểu đồ tròn: Doanh số theo danh mục
  - Biểu đồ cột: Đơn hàng theo khu vực
- 📋 **Bảng dữ liệu**: Đơn hàng gần đây với lọc và sắp xếp

### Trang Phân Tích
- 🗺️ Phân tích theo khu vực (Hà Nội, TP.HCM, Đà Nẵng...)
- 📦 Phân tích theo danh mục sản phẩm
- 💰 Tính toán tỷ trọng và doanh thu trung bình

### Giao diện
- 🎨 **Dark theme** với gradient màu tím/xanh
- ✨ **Glassmorphism** và hiệu ứng mờ
- 🌊 **Smooth animations** mượt mà
- 📱 **Responsive design** cho mobile/tablet/desktop
- 🎭 **Interactive charts** với Chart.js

## 🔐 Tài khoản mặc định

**Admin Account:**
- Username: `admin`
- Password: `admin123`
- Email: `admin@dashboard.com`

**User Account:**
- Username: `user1`
- Password: `admin123`
- Email: `user1@dashboard.com`

## 🛠️ Technologies Used

- **Backend**: PHP 7.4+ with PDO
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Charts**: Chart.js 4.4.0
- **Icons**: Font Awesome 6.4.0
- **Fonts**: Google Fonts (Inter)

## 🎨 Design Features

- Modern glassmorphism UI
- Vibrant purple/blue gradient theme
- Smooth micro-animations
- Hover effects and transitions
- Responsive sidebar navigation
- Interactive data visualizations
- Beautiful login page with animated background

## 📝 Lưu ý

- Database đã có **sample data** sẵn để demo
- Tất cả mật khẩu mặc định đều là `admin123`
- Responsive design hoạt động tốt từ 320px đến 4K
- Charts tự động cập nhật với dữ liệu từ database

## 🚧 Development

Để tiếp tục phát triển:
1. Thêm authentication thực tế (hiện tại chỉ là demo)
2. Thêm các trang quản lý: Sản phẩm, Đơn hàng, Khách hàng
3. Thêm API endpoints cho real-time updates
4. Thêm export Excel/PDF cho báo cáo
5. Thêm search và filtering nâng cao

## 📞 Support

Nếu gặp vấn đề, vui lòng kiểm tra:
- Laragon đã chạy chưa
- Database đã import thành công chưa
- PHP version >= 7.4
- Error logs tại `c:\laragon\logs\`

---

**Created with ❤️ by BA Group 5**
