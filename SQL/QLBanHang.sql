-- =====================================================
-- DATABASE: QLBanHang
-- =====================================================
CREATE DATABASE QLBanHang;
GO
USE QLBanHang;
GO

-- 1. Người dùng
CREATE TABLE NguoiDung (
    MaND        INT PRIMARY KEY IDENTITY(1,1),
    TenDangNhap VARCHAR(50)   UNIQUE NOT NULL,
    MatKhau     VARCHAR(255)  NOT NULL,
    MatKhau3Lop INT           NOT NULL,
    HoTen       NVARCHAR(100) NOT NULL,
    Email       VARCHAR(100)  UNIQUE,
    SoDienThoai VARCHAR(15),
    DiaChi      NVARCHAR(255),
    Avatar      VARCHAR(255),
    VaiTro      INT DEFAULT 0,     -- 0: Khách hàng, 1: Admin
    TrangThai   INT DEFAULT 1      -- 1: Hoạt động, 0: Bị khóa
);
GO

-- 2. Danh mục sản phẩm
CREATE TABLE DanhMuc (
    MaDM  INT PRIMARY KEY IDENTITY(1,1),
    TenDM NVARCHAR(100) NOT NULL
);
GO

-- 3. Sản phẩm
CREATE TABLE SanPham (
    MaSP       INT PRIMARY KEY IDENTITY(1,1),
    TenSP      NVARCHAR(255) NOT NULL,
    MaDM       INT FOREIGN KEY REFERENCES DanhMuc(MaDM),
    Gia        DECIMAL(18,2) NOT NULL,
    SoLuongTon INT DEFAULT 0,
    MoTa       NVARCHAR(MAX),
    CPU        NVARCHAR(100),
    RAM        NVARCHAR(50),
    O_Cung     NVARCHAR(50),
    ManHinh    NVARCHAR(100),
    BaoHanh    NVARCHAR(50),
    HinhAnh    VARCHAR(MAX)
);
GO

-- 4. Đơn hàng
CREATE TABLE DonHang (
    MaDH        INT PRIMARY KEY IDENTITY(1,1),
    MaND        INT FOREIGN KEY REFERENCES NguoiDung(MaND),
    NgayDat     DATETIME      DEFAULT GETDATE(),
    TongTien    DECIMAL(18,2),
    TrangThai   NVARCHAR(50)  DEFAULT N'Chờ xử lý',
    HoTen       NVARCHAR(150),
    SoDienThoai VARCHAR(15),
    Email       NVARCHAR(150),
    DiaChi      NVARCHAR(255),
    ThanhPho    NVARCHAR(100),
    ThanhToan   NVARCHAR(20)  DEFAULT 'COD',
    GhiChu      NVARCHAR(500)
);
GO

-- 5. Chi tiết đơn hàng
CREATE TABLE ChiTietDonHang (
    MaCT    INT PRIMARY KEY IDENTITY(1,1),
    MaDH    INT FOREIGN KEY REFERENCES DonHang(MaDH),
    MaSP    INT FOREIGN KEY REFERENCES SanPham(MaSP),
    SoLuong INT           NOT NULL,
    DonGia  DECIMAL(18,2) NOT NULL
);
GO

-- 6. Yêu thích
CREATE TABLE YeuThich (
    MaYT     INT IDENTITY(1,1) PRIMARY KEY,
    MaND     INT NOT NULL FOREIGN KEY REFERENCES NguoiDung(MaND),
    MaSP     INT NOT NULL FOREIGN KEY REFERENCES SanPham(MaSP),
    NgayThem DATETIME DEFAULT GETDATE()
);
GO
CREATE TABLE dbo.YeuThich (
    MaYT   INT IDENTITY(1,1) PRIMARY KEY,
    MaND   INT NOT NULL FOREIGN KEY REFERENCES NguoiDung(MaND),
    MaSP   INT NOT NULL FOREIGN KEY REFERENCES SanPham(MaSP),
    NgayThem DATETIME DEFAULT GETDATE()
);
USE QLBanHang;
ALTER TABLE NguoiDung ADD TrangThai int NOT NULL DEFAULT 1;
-- 1 = hoat dong, 0 = bi khoa
-- 1 = hoat dong, 0 = bi khoa
-- 7. Stored procedure thêm người dùng
CREATE PROCEDURE sp_ThemNguoiDung
    @PTenDangNhap VARCHAR(50),
    @PMatKhau     VARCHAR(255),
    @PMatKhau3Lop INT,
    @PHoTen       NVARCHAR(100),
    @PEmail       VARCHAR(100),
    @PSoDienThoai VARCHAR(15),
    @PDiaChi      NVARCHAR(255),
    @PVaiTro      INT = 0
AS
BEGIN
    INSERT INTO NguoiDung
        (TenDangNhap, MatKhau, MatKhau3Lop, HoTen, Email, SoDienThoai, DiaChi, VaiTro)
    VALUES
        (@PTenDangNhap, @PMatKhau, @PMatKhau3Lop, @PHoTen, @PEmail, @PSoDienThoai, @PDiaChi, @PVaiTro)
END
GO



-- =====================================================
-- DỮ LIỆU MẪU
-- =====================================================

-- Tài khoản admin (MaND = 1)
INSERT INTO NguoiDung (TenDangNhap, MatKhau, MatKhau3Lop, HoTen, VaiTro)
VALUES ('admin', '123456', 1111, N'Quản trị viên', 1);
GO

-- Danh mục
INSERT INTO DanhMuc (TenDM) VALUES (N'Laptop'), (N'Điện thoại');
GO

-- Sản phẩm mẫu
INSERT INTO SanPham (TenSP, MaDM, Gia, SoLuongTon, CPU, RAM, O_Cung, BaoHanh)
VALUES
    (N'Laptop Dell XPS 13', 1, 35000000, 10, 'Core i7 12th', '16GB', '512GB SSD', N'24 tháng'),
    (N'iPhone 15 Pro Max',  2, 32000000, 20, 'A17 Pro',       '8GB',  '256GB',     N'12 tháng');
GO
ALTER TABLE dbo.NguoiDung 
ADD Avatar varchar(255) NULL;
USE QLBanHang;
ALTER TABLE NguoiDung ADD TrangThai int NOT NULL DEFAULT 1;
-- 1 = hoat dong, 0 = bi khoa
-- Kiểm tra
SELECT MaND, TenDangNhap, HoTen, VaiTro FROM NguoiDung;
SELECT MaSP, TenSP, Gia FROM SanPham;
GO
CREATE TABLE TinNhan (
    MaTN INT PRIMARY KEY IDENTITY(1,1),           -- ID tự tăng của tin nhắn
    MaNguoiGui INT NOT NULL,                      -- ID người gửi (Khách hoặc Admin)
    MaNguoiNhan INT NOT NULL,                     -- ID người nhận (Admin hoặc Khách)
    NoiDung NVARCHAR(MAX) NOT NULL,               -- Nội dung tin nhắn (NVARCHAR để gõ Tiếng Việt)
    ThoiGian DATETIME DEFAULT GETDATE(),          -- Thời gian gửi (Tự động lấy giờ hiện tại)
    DaDoc INT DEFAULT 0,                          -- Trạng thái: 0 = Chưa đọc, 1 = Đã đọc

    -- Tạo khóa ngoại liên kết với bảng NguoiDung
    FOREIGN KEY (MaNguoiGui) REFERENCES NguoiDung(MaND),
    FOREIGN KEY (MaNguoiNhan) REFERENCES NguoiDung(MaND)
);
