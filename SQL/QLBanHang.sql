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
    MatKhau3Lop VARCHAR(4)         NOT NULL,
    HoTen       NVARCHAR(100) NOT NULL,
    Email       VARCHAR(100)  UNIQUE,
    SoDienThoai VARCHAR(15),
    DiaChi      NVARCHAR(255),
    Avatar      VARCHAR(255),
    VaiTro      INT DEFAULT 0,     -- 0: Khách hàng, 1: Admin
    TrangThai   INT DEFAULT 1      -- 1: Hoạt động, 0: Bị khóa
);
GO
ALTER TABLE NguoiDung ALTER COLUMN MatKhau3Lop VARCHAR(4) NOT NULL;

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

CREATE TABLE SoDiaChi (
    MaDC INT PRIMARY KEY IDENTITY(1,1),
    MaND INT NOT NULL FOREIGN KEY REFERENCES NguoiDung(MaND),
    HoTenNguoiNhan NVARCHAR(100) NOT NULL,
    SoDienThoai VARCHAR(15) NOT NULL,
    DiaChiCuThe NVARCHAR(255) NOT NULL,
    ThanhPho NVARCHAR(100) NOT NULL,
    MacDinh INT DEFAULT 0 -- 1: Địa chỉ mặc định, 0: Địa chỉ phụ
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
CREATE TABLE MaGiamGia (
    MaMGG       INT PRIMARY KEY IDENTITY(1,1),
    Code        VARCHAR(50)   UNIQUE NOT NULL,
    LoaiGiam    INT           DEFAULT 0,  -- 0: Theo %, 1: Số tiền cố định
    GiaTri      DECIMAL(18,2) NOT NULL,   -- % hoặc số tiền
    GiamToiDa   DECIMAL(18,2) DEFAULT 0, -- Giảm tối đa (cho loại %)
    DonToiThieu DECIMAL(18,2) DEFAULT 0, -- Đơn tối thiểu để dùng
    SoLanDung   INT           DEFAULT 1, -- Số lần được dùng
    DaDung      INT           DEFAULT 0, -- Đã dùng bao nhiêu lần
    NgayHetHan  DATETIME      NULL,
    TrangThai   INT           DEFAULT 1  -- 1: Còn hiệu lực, 0: Vô hiệu
);
GO

-- Du lieu mau
INSERT INTO MaGiamGia (Code, LoaiGiam, GiaTri, GiamToiDa, DonToiThieu, SoLanDung) VALUES
('GIAM10', 0, 10, 500000, 1000000, 100),   -- Giam 10%, toi da 500k, don toi thieu 1tr
('SALE50K', 1, 50000, 0, 500000, 50),      -- Giam thang 50k, don toi thieu 500k
('NEWUSER', 0, 15, 200000, 0, 1);          -- Giam 15%, toi da 200k, khong gioi han don
GO
select * from MaGiamGia
ALTER TABLE DonHang ADD LyDoHuy NVARCHAR(500) NULL;

--suamk3lop
ALTER PROCEDURE sp_ThemNguoiDung
    @PTenDangNhap VARCHAR(50),
    @PMatKhau     VARCHAR(255),
    @PMatKhau3Lop VARCHAR(4),   -- ĐÃ SỬA THÀNH VARCHAR(4)
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
--cap nhat them

GO

-- Xóa dữ liệu cũ (nếu có) để tránh trùng lặp

-- Reset lại ID tự tăng về 1
DBCC CHECKIDENT ('DanhMuc', RESEED, 0);
select * from DanhMuc
-- Thêm các danh mục chuẩn
INSERT INTO DanhMuc (TenDM) VALUES    -- ID = 2
(N'PC Gaming'),    -- ID = 3
(N'Phụ Kiện'),     -- ID = 4
(N'Gaming Gear');  -- ID = 5
GO
--thémqsql
USE QLBanHang;
GO

-- Bổ sung thêm các thuộc tính thực tế cho từng loại sản phẩm
ALTER TABLE SanPham ADD VGA NVARCHAR(100) NULL;         -- Dành cho Laptop, PC Gaming
ALTER TABLE SanPham ADD Camera NVARCHAR(100) NULL;      -- Dành cho Điện thoại
ALTER TABLE SanPham ADD Pin NVARCHAR(50) NULL;          -- Dành cho Điện thoại
ALTER TABLE SanPham ADD KetNoi NVARCHAR(100) NULL;      -- Dành cho Phụ kiện, Gaming Gear
ALTER TABLE SanPham ADD TuongThich NVARCHAR(100) NULL;  -- Dành cho Phụ kiện, Gaming Gear
GO
--thêm nữa

USE QLBanHang;
GO

-- XÓA DỮ LIỆU CŨ VÀ RESET ID ĐỂ TRÁNH TRÙNG LẶP (Chạy nếu bạn muốn làm mới hoàn toàn bảng sản phẩm)
-- CHÚ Ý: Nếu bạn có đơn hàng đang dính khóa ngoại tới sản phẩm cũ, bạn cần xóa bảng ChiTietDonHang trước. 
-- Nếu web mới test chưa có đơn hàng thì cứ chạy bình thường nhé.
DELETE FROM YeuThich;
DELETE FROM ChiTietDonHang;
DELETE FROM SanPham;
DBCC CHECKIDENT ('SanPham', RESEED, 0);
GO

-- =================================================================================================
-- 1. DANH MỤC LAPTOP (MaDM = 1) - Có CPU, RAM, ROM, Màn hình, VGA
-- =================================================================================================
INSERT INTO SanPham (TenSP, MaDM, Gia, SoLuongTon, CPU, RAM, O_Cung, ManHinh, VGA, BaoHanh, MoTa) VALUES
(N'MacBook Pro 14 M3 2023', 1, 39990000, 15, 'Apple M3 8-core', '8GB', '512GB SSD', N'14.2 inch Liquid Retina XDR', 'Apple GPU 10-core', N'12 tháng', N'Laptop đồ họa cao cấp từ Apple.'),
(N'Laptop Dell XPS 15 9530', 1, 45500000, 8, 'Intel Core i7-13700H', '16GB', '1TB SSD NVMe', N'15.6 inch FHD+', 'RTX 4050 4GB', N'12 tháng', N'Thiết kế sang trọng, viền siêu mỏng.'),
(N'Laptop Asus ROG Strix G15', 1, 28990000, 20, 'AMD Ryzen 7 6800H', '16GB', '512GB SSD', N'15.6 inch 144Hz', 'RTX 3050 4GB', N'24 tháng', N'Laptop gaming quốc dân, tản nhiệt tốt.'),
(N'Lenovo Legion 5 Pro', 1, 35000000, 12, 'Intel Core i7-14700HX', '32GB', '1TB SSD', N'16 inch WQXGA 165Hz', 'RTX 4060 8GB', N'24 tháng', N'Hiệu năng mạnh mẽ cho game thủ.'),
(N'HP Victus 16', 1, 22500000, 25, 'Intel Core i5-13500H', '16GB', '512GB SSD', N'16.1 inch FHD 144Hz', 'RTX 4050 6GB', N'12 tháng', N'Thiết kế thanh lịch, chiến game tốt.'),
(N'Acer Nitro 5 Tiger', 1, 19990000, 30, 'Intel Core i5-12500H', '8GB', '512GB SSD', N'15.6 inch FHD 144Hz', 'RTX 3050 4GB', N'12 tháng', N'Laptop gaming giá rẻ cho sinh viên.'),
(N'MSI Cyborg 15', 1, 24000000, 10, 'Intel Core i7-12650H', '8GB', '512GB SSD', N'15.6 inch FHD 144Hz', 'RTX 4050 6GB', N'24 tháng', N'Thiết kế cyber trong suốt độc đáo.'),
(N'Gigabyte G5', 1, 18500000, 18, 'Intel Core i5-12450H', '8GB', '512GB SSD', N'15.6 inch FHD 144Hz', 'RTX 2050 4GB', N'24 tháng', N'Laptop cấu hình tốt trong tầm giá.'),
(N'MacBook Air M2 2022', 1, 26500000, 40, 'Apple M2 8-core', '8GB', '256GB SSD', N'13.6 inch Liquid Retina', 'Apple GPU 8-core', N'12 tháng', N'Mỏng nhẹ, pin siêu trâu.'),
(N'LG Gram 14 2023', 1, 27900000, 5, 'Intel Core i5-1340P', '16GB', '512GB SSD', N'14 inch WUXGA', 'Intel Iris Xe', N'12 tháng', N'Siêu mỏng nhẹ chỉ 999g.');

-- =================================================================================================
-- 2. DANH MỤC ĐIỆN THOẠI (MaDM = 2) - Có CPU, RAM, ROM, Camera, Pin
-- =================================================================================================
INSERT INTO SanPham (TenSP, MaDM, Gia, SoLuongTon, CPU, RAM, O_Cung, ManHinh, Camera, Pin, BaoHanh, MoTa) VALUES
(N'iPhone 15 Pro Max', 2, 29990000, 50, 'Apple A17 Pro', '8GB', '256GB', N'6.7 inch Super Retina', N'Chính 48MP & Phụ 12MP', '4422 mAh', N'12 tháng', N'Khung Titanium siêu nhẹ, camera viễn vọng.'),
(N'Samsung Galaxy S24 Ultra', 2, 31500000, 40, 'Snapdragon 8 Gen 3', '12GB', '256GB', N'6.8 inch Dynamic AMOLED', N'200MP + 50MP + 12MP', '5000 mAh', N'12 tháng', N'Tích hợp Galaxy AI cực thông minh.'),
(N'Xiaomi 14 Pro', 2, 22990000, 20, 'Snapdragon 8 Gen 3', '12GB', '256GB', N'6.73 inch AMOLED 120Hz', N'Leica 50MP + 50MP', '4880 mAh', N'18 tháng', N'Camera hợp tác cùng Leica cực nét.'),
(N'Oppo Find X7 Ultra', 2, 24500000, 15, 'Snapdragon 8 Gen 3', '16GB', '512GB', N'6.82 inch AMOLED', N'Hasselblad 50MP', '5000 mAh', N'12 tháng', N'Thiết kế mặt lưng da sang trọng.'),
(N'iPhone 14 Plus', 2, 20990000, 30, 'Apple A15 Bionic', '6GB', '128GB', N'6.7 inch Super Retina', N'Kép 12MP', '4325 mAh', N'12 tháng', N'Màn hình lớn, pin trâu giá tốt.'),
(N'Samsung Galaxy Z Fold 5', 2, 33000000, 10, 'Snapdragon 8 Gen 2', '12GB', '512GB', N'7.6 inch Foldable', N'50MP + 12MP + 10MP', '4400 mAh', N'12 tháng', N'Điện thoại gập đỉnh cao công nghệ.'),
(N'Xiaomi Redmi Note 13 Pro', 2, 7500000, 100, 'Snapdragon 7s Gen 2', '8GB', '256GB', N'6.67 inch AMOLED', N'200MP', '5100 mAh', N'18 tháng', N'Vua phân khúc tầm trung mới.'),
(N'Vivo X100 Pro', 2, 23000000, 12, 'Dimensity 9300', '16GB', '512GB', N'6.78 inch LTPO AMOLED', N'Zeiss 50MP', '5400 mAh', N'12 tháng', N'Camera chân dung Zeiss xuất sắc.'),
(N'Asus ROG Phone 8', 2, 21990000, 25, 'Snapdragon 8 Gen 3', '16GB', '256GB', N'6.78 inch 165Hz AMOLED', N'50MP', '5500 mAh', N'12 tháng', N'Gaming phone thiết kế mỏng gọn hơn.'),
(N'Realme 12 Pro Plus', 2, 10500000, 45, 'Snapdragon 7s Gen 2', '12GB', '256GB', N'6.7 inch AMOLED', N'64MP (Periscope)', '5000 mAh', N'12 tháng', N'Camera tele trong tầm giá rẻ.');

-- =================================================================================================
-- 3. DANH MỤC PC GAMING (MaDM = 3) - Có CPU, RAM, ROM, VGA
-- =================================================================================================
INSERT INTO SanPham (TenSP, MaDM, Gia, SoLuongTon, CPU, RAM, O_Cung, VGA, BaoHanh, MoTa) VALUES
(N'PC Gaming ASUS ROG Strix G10', 3, 25000000, 10, 'Intel Core i5-11400F', '16GB', '512GB SSD NVMe', 'RTX 3060 12GB', N'24 tháng', N'Case đồng bộ ASUS chuẩn gaming.'),
(N'PC Dell Alienware Aurora R15', 3, 75000000, 3, 'Intel Core i9-13900KF', '32GB', '2TB SSD', 'RTX 4080 16GB', N'12 tháng', N'Thiết kế tàu vũ trụ, tản nhiệt nước.'),
(N'PC Gaming MSI MAG Codex 5', 3, 32000000, 8, 'Intel Core i7-12700', '16GB', '1TB SSD', 'RTX 3060 Ti 12GB', N'24 tháng', N'PC đồng bộ MSI mạnh mẽ.'),
(N'PC HP Omen 25L', 3, 40000000, 5, 'AMD Ryzen 7 5800X', '16GB', '1TB SSD + 2TB HDD', 'RX 6700 XT 12GB', N'12 tháng', N'Case kính cường lực sang trọng.'),
(N'PC Custom I9-14900K Master', 3, 85000000, 2, 'Intel Core i9-14900K', '64GB DDR5', '2TB SSD Gen 4', 'RTX 4090 24GB', N'36 tháng', N'PC tự build siêu khủng cho dân đồ họa.'),
(N'PC Custom i5-12400F Esport', 3, 15500000, 20, 'Intel Core i5-12400F', '16GB', '500GB SSD', 'GTX 1660 Super', N'36 tháng', N'Chiến tốt LoL, CS2, Valorant.'),
(N'PC Custom R5-5600X Streamer', 3, 21000000, 12, 'AMD Ryzen 5 5600X', '16GB', '500GB SSD', 'RTX 3060 12GB', N'36 tháng', N'Cấu hình tiêu chuẩn cho Streamer.'),
(N'PC Corsair One i300', 3, 95000000, 1, 'Intel Core i9-12900K', '64GB', '2TB SSD', 'RTX 3080 Ti', N'24 tháng', N'PC mini nhỏ gọn nhưng siêu mạnh.'),
(N'PC Lenovo Legion Tower 5i', 3, 34000000, 7, 'Intel Core i7-13700', '16GB', '1TB SSD', 'RTX 4060 Ti 8GB', N'12 tháng', N'Hệ thống LED RGB tích hợp.'),
(N'PC Custom i7-13700K Render', 3, 42000000, 6, 'Intel Core i7-13700K', '32GB DDR5', '1TB SSD Gen 4', 'RTX 4070 12GB', N'36 tháng', N'Chuyên xử lý Premiere, After Effects.');

-- =================================================================================================
-- 4. DANH MỤC PHỤ KIỆN (MaDM = 4) - Có Kết nối, Tương thích
-- =================================================================================================
INSERT INTO SanPham (TenSP, MaDM, Gia, SoLuongTon, KetNoi, TuongThich, BaoHanh, MoTa) VALUES
(N'Sạc dự phòng Baseus 20000mAh', 4, 650000, 100, N'USB-C, USB-A', N'Mọi thiết bị', N'12 tháng', N'Sạc nhanh 22.5W, màn hình LED hiển thị pin.'),
(N'Củ sạc Anker Nano 30W', 4, 350000, 150, N'USB-C', N'iPhone, Samsung', N'18 tháng', N'Kích thước siêu nhỏ, công nghệ GaN.'),
(N'Cáp sạc Ugreen USB-C to Lightning', 4, 250000, 200, N'Type-C to Lightning', N'iPhone, iPad', N'12 tháng', N'Chứng nhận MFi từ Apple, bọc dù chống đứt.'),
(N'Giá đỡ điện thoại/Tablet Baseus', 4, 150000, 80, N'Không', N'Điện thoại, Tablet', N'Không', N'Hợp kim nhôm nguyên khối, gấp gọn dễ dàng.'),
(N'Hub chuyển đổi Ugreen 6-in-1', 4, 850000, 45, N'USB-C', N'MacBook, Laptop Win', N'12 tháng', N'Mở rộng HDMI 4K, USB 3.0, khe đọc thẻ nhớ.'),
(N'Balo chống sốc Tomtoc', 4, 1200000, 30, N'Không', N'Laptop 15.6 inch', N'12 tháng', N'Vải Cordura chống nước, đệm chống sốc góc 360.'),
(N'Ổ cứng di động Samsung T7 1TB', 4, 2400000, 25, N'Type-C', N'Windows, macOS, Android', N'36 tháng', N'Tốc độ đọc ghi cực nhanh 1050MB/s.'),
(N'Tai nghe Apple AirPods Pro 2', 4, 5990000, 40, N'Bluetooth 5.3', N'iOS, macOS', N'12 tháng', N'Chống ồn chủ động ANC thế hệ mới.'),
(N'Cáp HDMI 2.1 Baseus 8K', 4, 200000, 60, N'HDMI', N'TV, PC, PS5', N'12 tháng', N'Hỗ trợ xuất hình 8K@60Hz, 4K@120Hz.'),
(N'Kính cường lực Spigen iPhone 15', 4, 300000, 120, N'Không', N'iPhone 15 Pro Max', N'Không', N'Độ cứng 9H, có khay tự dán dễ dàng.');

-- =================================================================================================
-- 5. DANH MỤC GAMING GEAR (MaDM = 5) - Có Kết nối, Tương thích
-- =================================================================================================
INSERT INTO SanPham (TenSP, MaDM, Gia, SoLuongTon, KetNoi, TuongThich, BaoHanh, MoTa) VALUES
(N'Chuột Logitech G Pro X Superlight 2', 5, 3490000, 30, N'Wireless Lightspeed', N'Windows, macOS', N'24 tháng', N'Chuột gaming không dây siêu nhẹ 60g.'),
(N'Bàn phím cơ Razer BlackWidow V4', 5, 3800000, 20, N'Cáp USB rời', N'Windows', N'24 tháng', N'Switch Razer Green, LED Chroma RGB.'),
(N'Tai nghe HyperX Cloud III', 5, 2500000, 35, N'USB / 3.5mm', N'PC, PS5, Xbox', N'24 tháng', N'Đệm tai memory foam siêu êm, mic lọc ồn.'),
(N'Lót chuột SteelSeries QcK Heavy', 5, 650000, 100, N'Không', N'Mọi loại chuột', N'Không', N'Vải dệt siêu mịn, đáy cao su chống trượt.'),
(N'Tay cầm Xbox Series X/S Controller', 5, 1450000, 50, N'Bluetooth / Xbox Wireless', N'PC, Xbox, Android', N'6 tháng', N'Cảm giác bấm cực tốt, kết nối ổn định.'),
(N'Bàn phím cơ Akko 3098B Plus', 5, 1800000, 40, N'3 Mode (Type-C, 2.4G, Bluetooth)', N'Windows, macOS', N'12 tháng', N'Profile ASA, switch Akko CS bấm cực mượt.'),
(N'Chuột Razer DeathAdder V3 Pro', 5, 3500000, 15, N'Wireless', N'Windows', N'24 tháng', N'Form công thái học, mắt đọc Focus Pro 30K.'),
(N'Micro thu âm Elgato Wave 3', 5, 3990000, 10, N'Type-C', N'Windows, macOS', N'24 tháng', N'Mic chuẩn Studio cho Streamer, tích hợp phần mềm mix âm.'),
(N'Ghế Gaming DXRacer Master', 5, 8900000, 8, N'Không', N'Không', N'24 tháng', N'Chất liệu da Microfiber, ngả lưng 155 độ.'),
(N'Giá treo tai nghe Corsair ST100', 5, 1200000, 20, N'USB 3.1', N'PC', N'24 tháng', N'Vỏ nhôm cao cấp, tích hợp DAC giả lập 7.1 và LED RGB.');
GO
--thêm ví giãm giá
USE QLBanHang;
GO
CREATE TABLE ViGiamGia (
    MaVi INT IDENTITY(1,1) PRIMARY KEY,
    MaND INT NOT NULL FOREIGN KEY REFERENCES NguoiDung(MaND),
    MaMGG INT NOT NULL FOREIGN KEY REFERENCES MaGiamGia(MaMGG),
    NgayLuu DATETIME DEFAULT GETDATE(),
    TrangThaiSuDung INT DEFAULT 0 -- 0: Chưa dùng, 1: Đã dùng
);
GO
--bình luận
USE QLBanHang;
GO

CREATE TABLE BinhLuan (
    MaBL INT IDENTITY(1,1) PRIMARY KEY,
    MaSP INT NOT NULL FOREIGN KEY REFERENCES SanPham(MaSP),
    MaND INT NOT NULL FOREIGN KEY REFERENCES NguoiDung(MaND),
    NoiDung NVARCHAR(MAX) NOT NULL,
    NgayBL DATETIME DEFAULT GETDATE()
);
GO

select * from NguoiDung
--bình luận 5 sao
USE QLBanHang;
GO
ALTER TABLE BinhLuan ADD SoSao INT NOT NULL DEFAULT 5;
GO
--trạng thaishoatj đông
USE QLBanHang;
GO
ALTER TABLE NguoiDung ADD NgayHoatDong DATETIME NULL;
GO
--đăng nhập face(test)
USE QLBanHang;
GO
ALTER TABLE NguoiDung ADD FacebookID VARCHAR(100) NULL;
GO
--update mã hìn
ALTER TABLE NguoiDung ALTER COLUMN Avatar VARCHAR(MAX);
GO
--đổi trả
USE QLBanHang;
GO
ALTER TABLE DonHang ADD LyDoTraHang NVARCHAR(MAX) NULL;
ALTER TABLE DonHang ADD LinkVideoProof VARCHAR(MAX) NULL;
GO