create database QLBanHang
use QLBanHang
-- 2. Bảng Người dùng (Tài khoản & Mật khẩu)
CREATE TABLE NguoiDung (
    MaND INT PRIMARY KEY IDENTITY(1,1),
    TenDangNhap VARCHAR(50) UNIQUE NOT NULL,
    MatKhau VARCHAR(255) NOT NULL,
    MatKhau3Lop int NOT NULL, -- Dùng để lưu mật khẩu
    HoTen NVARCHAR(100) NOT NULL,
    Email VARCHAR(100) UNIQUE,
    SoDienThoai VARCHAR(15),
    DiaChi NVARCHAR(255),
    VaiTro INT DEFAULT 0
     -- 0: Khách hàng, 1: Admin
);

-- 3. Bảng Danh mục sản phẩm (Laptop, Điện thoại, Phụ kiện...)
CREATE TABLE DanhMuc (
    MaDM INT PRIMARY KEY IDENTITY(1,1),
    TenDM NVARCHAR(100) NOT NULL
);

-- 4. Bảng Sản phẩm điện tử
CREATE TABLE SanPham (
    MaSP INT PRIMARY KEY IDENTITY(1,1),
    TenSP NVARCHAR(255) NOT NULL,
    MaDM INT FOREIGN KEY REFERENCES DanhMuc(MaDM),
    Gia DECIMAL(18, 2) NOT NULL,
    SoLuongTon INT DEFAULT 0,
    MoTa NVARCHAR(MAX),
    CPU NVARCHAR(100),
    RAM NVARCHAR(50),
    O_Cung NVARCHAR(50),
    ManHinh NVARCHAR(100),
    BaoHanh NVARCHAR(50),
    HinhAnh VARCHAR(MAX) -- Lưu đường dẫn ảnh sản phẩm
);

-- 5. Bảng Đơn hàng
CREATE TABLE DonHang (
    MaDH INT PRIMARY KEY IDENTITY(1,1),
    MaND INT FOREIGN KEY REFERENCES NguoiDung(MaND),
    NgayDat DATETIME DEFAULT GETDATE(),
    TongTien DECIMAL(18, 2),
    TrangThai NVARCHAR(50) DEFAULT N'Chờ xử lý' -- Chờ duyệt, Đang giao, Đã giao
);

-- 6. Bảng Chi tiết đơn hàng (Sản phẩm trong mỗi đơn)
CREATE TABLE ChiTietDonHang (
    MaCT INT PRIMARY KEY IDENTITY(1,1),
    MaDH INT FOREIGN KEY REFERENCES DonHang(MaDH),
    MaSP INT FOREIGN KEY REFERENCES SanPham(MaSP),
    SoLuong INT NOT NULL,
    DonGia DECIMAL(18, 2) NOT NULL
);

----------------------------------------------------
--- DỮ LIỆU MẪU (Dùng để kiểm tra kết nối PHP) ---
----------------------------------------------------






-- 3. Chèn dữ liệu mới (Mỗi tên đăng nhập chỉ xuất hiện DUY NHẤT 1 lần)
INSERT INTO NguoiDung (TenDangNhap, MatKhau,MatKhau3Lop, HoTen, VaiTro)
VALUES ('admin', '123456', N'Quản trị viên', 1);


DELETE FROM NguoiDung;
DBCC CHECKIDENT ('NguoiDung', RESEED, 0); -- Reset cột ID về 1

-- 4. Kiểm tra kết quả
SELECT * FROM NguoiDung;
-- Danh mục
INSERT INTO DanhMuc (TenDM) VALUES (N'Laptop'), (N'Điện thoại');

-- Sản phẩm
INSERT INTO SanPham (TenSP, MaDM, Gia, SoLuongTon, CPU, RAM, O_Cung, BaoHanh)
VALUES 
(N'Laptop Dell XPS 13', 1, 35000000, 10, 'Core i7 12th', '16GB', '512GB SSD', N'24 tháng'),
(N'iPhone 15 Pro Max', 2, 32000000, 20, 'A17 Pro', '8GB', '256GB', N'12 tháng');


--thêm
CREATE PROCEDURE sp_ThemNguoiDung
    @PTenDangNhap VARCHAR(50),
    @PMatKhau VARCHAR(255),
    @PMatKhau3Lop int,
    @PHoTen NVARCHAR(100),
    @PEmail VARCHAR(100),
    @PSoDienThoai VARCHAR(15),
    @PDiaChi NVARCHAR(255),
    @PVaiTro INT = 0
AS
BEGIN
    INSERT INTO NguoiDung
    (TenDangNhap, MatKhau, MatKhau3Lop, HoTen, Email, SoDienThoai, DiaChi, VaiTro)
    VALUES
    (@PTenDangNhap, @PMatKhau,@PMatKhau3Lop, @PHoTen, @PEmail, @PSoDienThoai, @PDiaChi, @PVaiTro)
END
