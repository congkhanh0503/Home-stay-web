# 🏠 Hệ thống Đặt phòng Homestay

## 📖 1. Giới thiệu
Hệ thống Đặt phòng Homestay được xây dựng nhằm hỗ trợ quản lý, đặt phòng và theo dõi tình trạng homestay một cách hiệu quả. Thay vì quản lý thủ công bằng giấy tờ hay các tệp Excel rời rạc, hệ thống mang đến một giải pháp tập trung, hiện đại và dễ sử dụng.

## 🔧 2. Các công nghệ được sử dụng
<div align="center">

### Hệ điều hành
![macOS](https://img.shields.io/badge/macOS-000000?style=for-the-badge&logo=macos&logoColor=F0F0F0)
![Windows](https://img.shields.io/badge/Windows-0078D6?style=for-the-badge&logo=windows&logoColor=white)
![Ubuntu](https://img.shields.io/badge/Ubuntu-E95420?style=for-the-badge&logo=ubuntu&logoColor=white)

### Công nghệ chính
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![Bootstrap](https://img.shields.io/badge/Bootstrap-563D7C?style=for-the-badge&logo=bootstrap&logoColor=white)

### Web Server & Database
![Apache](https://img.shields.io/badge/Apache-D22128?style=for-the-badge&logo=apache&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![XAMPP](https://img.shields.io/badge/XAMPP-FB7A24?style=for-the-badge&logo=xampp&logoColor=white)

### Database Management Tools
![MySQL Workbench](https://img.shields.io/badge/MySQL_Workbench-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
</div>

## 🚀 3. Hình ảnh các chức năng

### Trang đăng nhập
<img width="800" alt="Trang đăng nhập" src="https://github.com/user-attachments/assets/b6ea078d-24f0-4f2b-92ac-fc4cef3713f3" />

### Trang đăng ký
<img width="800" alt="Trang đăng ký" src="https://github.com/user-attachments/assets/586e09de-55af-4bad-9233-5743b130981e" />

### Trang quên mật khẩu
<img width="800" alt="Trang quên mật khẩu" src="https://github.com/user-attachments/assets/2c3a1464-35f2-43b1-9f06-ab2807a948b7" />

### Trang dashboard admin
<img width="800" alt="Trang dashboard admin" src="https://github.com/user-attachments/assets/8857983b-a7c7-4cd7-9f13-e8972c5d9b3d" />

### Trang quản lý người dùng
<img width="800" alt="Trang quản lý người dùng" src="https://github.com/user-attachments/assets/402752ee-643b-477a-9fdc-87d51f6b56f3" />

### Trang dashboard host
<img width="800" alt="Trang dashboard host" src="https://github.com/user-attachments/assets/8f117d6b-db32-4c1e-8b9d-7bf4035dbea1" />

### Trang quản lý homestay
<img width="800" alt="Trang quản lý homestay" src="https://github.com/user-attachments/assets/d6e5883d-1c2a-4433-85db-3ad296523295" />

### Trang quản lý đơn đặt
<img width="800" alt="Trang quản lý đơn đặt" src="https://github.com/user-attachments/assets/bdf3b3cf-4c60-49fa-aa13-b1312271b520" />

### Trang thêm homestay
<img width="800" alt="Trang thêm homestay" src="https://github.com/user-attachments/assets/b38860be-078d-44d9-b0c9-973c18bc4c34" />

### Trang quản lý doanh thu
<img width="800" alt="Trang quản lý doanh thu" src="https://github.com/user-attachments/assets/cd7c6d17-5be5-4ed8-9c9e-03144f46d06e" />

### Trang đơn đặt của tôi
<img width="800" alt="Trang đơn đặt của tôi" src="https://github.com/user-attachments/assets/296374d2-28f0-401f-86d2-60c199bb2738" />

### Trang tìm kiếm homestay
<img width="800" alt="Trang tìm kiếm homestay" src="https://github.com/user-attachments/assets/71604670-afb6-4c31-b943-6d00687174e9" />

### Trang chi tiết homestay
<img width="800" alt="Trang chi tiết homestay" src="https://github.com/user-attachments/assets/000e8e61-291e-4db9-bdfa-c398d2afafd4" />

### Trang thông tin cá nhân
<img width="800" alt="Trang thông tin cá nhân" src="https://github.com/user-attachments/assets/28edd17a-76f7-457f-9e1d-fc7312601747" />

## ⚙️ 4. Cài đặt

### 4.1. Cài đặt công cụ, môi trường và các thư viện cần thiết

- Tải và cài đặt **XAMPP**  
  👉 https://www.apachefriends.org/download.html  
  (Khuyến nghị bản XAMPP với PHP 8.x)

- Cài đặt **Visual Studio Code** và các extension:
  - PHP Intelephense  
  - MySQL  
  - Prettier - Code Formatter  

### 4.2. Tải project
Clone project về thư mục `htdocs` của XAMPP (ví dụ ổ C):

```bash
cd C:\xampp\htdocs
git clone https://github.com/congkhanh0503/Home-stay-web.git
```
### 4.3. Setup database
Mở XAMPP Control Panel, Start Apache và MySQL

Truy cập MySQL WorkBench
Tạo database:
```bash
CREATE DATABASE IF NOT EXISTS homestay_management
   CHARACTER SET utf8mb4
   COLLATE utf8mb4_unicode_ci;
```

### 4.4. Setup tham số kết nối
Mở file config.php (hoặc .env) trong project, chỉnh thông tin DB:
```bash

<?php
// Cấu hình cơ bản
define('SITE_NAME', 'Homestay Relax');
define('SITE_URL', 'http://localhost/HomestayManagementSystem');

// Cấu hình database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'khanh2005');
define('DB_NAME', 'homestay_management');
define('DB_PORT', 3306);

// Cấu hình upload
define('UPLOAD_PATH', dirname(__DIR__) . '/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Bắt đầu session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
```
### 4.5. Chạy hệ thống
Mở XAMPP Control Panel → Start Apache và MySQL

Truy cập hệ thống:
👉 http://localhost/Home-stay-web

### 4.6. Đăng nhập lần đầu
Hệ thống có thể được cấp tài khoản admin mặc định.

Sau khi đăng nhập, Admin có thể:

Quản lý thông tin homestay

Thêm/sửa/xóa phòng

Quản lý đặt phòng

Phân quyền người dùng
    





