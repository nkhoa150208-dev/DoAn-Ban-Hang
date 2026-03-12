<?php
session_start();

if (!isset($_SESSION['MaND'])) { header('Location: DangNhap.php'); exit; }
if (empty($_SESSION['giohang']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ChiTietGioHang.php'); exit;
}

// Dùng lại config kết nối chung của dự án
require_once 'config.php';

// =========================================================
// LẤY VÀ VỆ SINH DỮ LIỆU FORM
// =========================================================
$maND        = $_SESSION['MaND'];
$hoTen       = trim(htmlspecialchars($_POST['hoTen']       ?? ''));
$soDienThoai = trim(htmlspecialchars($_POST['soDienThoai'] ?? ''));
$email       = trim(htmlspecialchars($_POST['email']       ?? ''));
$diaChi      = trim(htmlspecialchars($_POST['diaChi']      ?? ''));
$thanhPho    = trim(htmlspecialchars($_POST['thanhPho']    ?? ''));
$thanhToan   = trim(htmlspecialchars($_POST['thanhToan']   ?? 'COD'));
$ghiChu      = trim(htmlspecialchars($_POST['ghiChu']      ?? ''));
$tongTien    = (float)($_POST['tongTien'] ?? 0);

// Validate phía server
$errors = [];
if (!$hoTen)    $errors[] = "Thiếu họ tên.";
if (!preg_match('/^(0|\+84)[0-9]{9}$/', $soDienThoai)) $errors[] = "Số điện thoại không hợp lệ.";
if (!$diaChi)   $errors[] = "Thiếu địa chỉ giao hàng.";
if (!$thanhPho) $errors[] = "Thiếu tỉnh/thành phố.";

if (!empty($errors)) {
    $_SESSION['order_error'] = implode(' ', $errors);
    header('Location: ChiTietGioHang.php'); exit;
}

// =========================================================
// LƯU ĐƠN HÀNG VÀO DATABASE
// =========================================================
sqlsrv_begin_transaction($conn);

try {
    // 1. Insert vào DonHang
    $sqlDH = "INSERT INTO DonHang
                (MaND, HoTen, SoDienThoai, Email, DiaChi, ThanhPho,
                 ThanhToan, GhiChu, TongTien, TrangThai, NgayDat)
              OUTPUT INSERTED.MaDH
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, N'Chờ xử lý', GETDATE())";

    // Ép kiểu tường minh cho sqlsrv - tránh lỗi FOREIGN KEY / type mismatch
    $params_dh = [
        [(int)$maND,        SQLSRV_PARAM_IN, null, SQLSRV_SQLTYPE_INT],
        [$hoTen,            SQLSRV_PARAM_IN, null, SQLSRV_SQLTYPE_NVARCHAR(150)],
        [$soDienThoai,      SQLSRV_PARAM_IN, null, SQLSRV_SQLTYPE_VARCHAR(15)],
        [$email,            SQLSRV_PARAM_IN, null, SQLSRV_SQLTYPE_NVARCHAR(150)],
        [$diaChi,           SQLSRV_PARAM_IN, null, SQLSRV_SQLTYPE_NVARCHAR(255)],
        [$thanhPho,         SQLSRV_PARAM_IN, null, SQLSRV_SQLTYPE_NVARCHAR(100)],
        [$thanhToan,        SQLSRV_PARAM_IN, null, SQLSRV_SQLTYPE_NVARCHAR(20)],
        [$ghiChu,           SQLSRV_PARAM_IN, null, SQLSRV_SQLTYPE_NVARCHAR(500)],
        [(float)$tongTien,  SQLSRV_PARAM_IN, null, SQLSRV_SQLTYPE_DECIMAL(18,2)],
    ];
    $stmtDH = sqlsrv_query($conn, $sqlDH, $params_dh);
    if (!$stmtDH) throw new Exception(sqlsrv_errors()[0]['message']);

    // Lấy MaDH từ OUTPUT clause
    $rowID     = sqlsrv_fetch_array($stmtDH, SQLSRV_FETCH_ASSOC);
    $maDonHang = (int)$rowID['MaDH'];
    if (!$maDonHang) throw new Exception("Không lấy được MaDH sau INSERT DonHang!");

    // 2. Insert từng sản phẩm vào ChiTietDonHang
    $sqlCT = "INSERT INTO ChiTietDonHang (MaDH, MaSP, SoLuong, DonGia)
              VALUES (?, ?, ?, ?)";

    foreach ($_SESSION['giohang'] as $maSP => $sp) {
        $params_ct = [
            [(int)$maDonHang,        SQLSRV_PARAM_IN, null, SQLSRV_SQLTYPE_INT],
            [(int)$maSP,             SQLSRV_PARAM_IN, null, SQLSRV_SQLTYPE_INT],
            [(int)$sp['SoLuong'],    SQLSRV_PARAM_IN, null, SQLSRV_SQLTYPE_INT],
            [(float)$sp['Gia'],      SQLSRV_PARAM_IN, null, SQLSRV_SQLTYPE_DECIMAL(18,2)],
        ];
        $stmtCT = sqlsrv_query($conn, $sqlCT, $params_ct);
        if (!$stmtCT) throw new Exception(sqlsrv_errors()[0]['message']);
    }

    sqlsrv_commit($conn);
    sqlsrv_close($conn);

    // 3. Xóa giỏ hàng
    unset($_SESSION['giohang']);

    // 4. Lưu thông tin để hiển thị trang thành công
    $_SESSION['order_success'] = [
        'maDonHang'   => $maDonHang,
        'hoTen'       => $hoTen,
        'soDienThoai' => $soDienThoai,
        'diaChi'      => $diaChi . ', ' . $thanhPho,
        'thanhToan'   => $thanhToan,
        'tongTien'    => $tongTien,
    ];

    header('Location: dat_hang_thanh_cong.php');
    exit;

} catch (Exception $e) {
    sqlsrv_rollback($conn);
    sqlsrv_close($conn);
    $_SESSION['order_error'] = "Đặt hàng thất bại: " . $e->getMessage();
    header('Location: ChiTietGioHang.php');
    exit;
}