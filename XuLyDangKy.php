<?php
session_start();
include "config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $TenDangNhap = trim($_POST['TenDangNhap'] ?? '');
    $MatKhau     = trim($_POST['MatKhau'] ?? '');
    $MatKhau3Lop = trim($_POST['MatKhau3Lop'] ?? '');
    $HoTen       = trim($_POST['HoTen'] ?? '');
    $Email       = trim($_POST['Email'] ?? '');
    $SoDienThoai = trim($_POST['SoDienThoai'] ?? '');
    $DiaChi      = trim($_POST['DiaChi'] ?? '');

    // ❌ Rỗng
    if ($TenDangNhap === '' || $MatKhau === '' || $MatKhau3Lop === '' || $HoTen === '' ||
        $Email === '' || $SoDienThoai === '' || $DiaChi === '') {

        $_SESSION['error'] = "Vui lòng nhập đầy đủ thông tin";
        header("Location: DangKy.php");
        exit;
    }

    // ❌ Email gmail
    if (!preg_match('/@gmail\.com$/', $Email)) {
        $_SESSION['error'] = "Email phải có dạng @gmail.com";
        header("Location: DangKy.php");
        exit;
    }
 if (!preg_match('/^[0-9]{4}$/', $MatKhau3Lop)) {
        $_SESSION['error'] = "Mật khẩu 3 lớp phải đúng 4 chữ số";
        header("Location: DangKy.php");
        exit;
    }
    // ❌ SĐT 10 số
    if (!preg_match('/^[0-9]{10}$/', $SoDienThoai)) {
        $_SESSION['error'] = "Số điện thoại phải đúng 10 chữ số";
        header("Location: DangKy.php");
        exit;
    }

    // ❌ Trùng tài khoản
    $check = sqlsrv_query($conn,
        "SELECT 1 FROM NguoiDung WHERE TenDangNhap = ?",
        [$TenDangNhap]
    );

    if (sqlsrv_has_rows($check)) {
        $_SESSION['error'] = "Tên đăng nhập đã tồn tại";
        header("Location: DangKy.php");
        exit;
    }

    // ✅ Thêm người dùng
    $sql = "{CALL sp_ThemNguoiDung(?, ?, ?, ?, ?, ?, ?, ?)}";
    $params = [$TenDangNhap, $MatKhau, $MatKhau3Lop, $HoTen, $Email, $SoDienThoai, $DiaChi, 0];
    sqlsrv_query($conn, $sql, $params);


    header("Location: DangNhap.php");
    exit;
}
