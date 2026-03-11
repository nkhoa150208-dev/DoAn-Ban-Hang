<?php
session_start();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quên Mật Khẩu</title>
<link rel="stylesheet" href="css/TrangChu.css">
<style>
    .thongbao-loi {
        color: #e74c3c;
        font-size: 13px;
        text-align: center;
        margin-top: 8px;
    }
    .thongbao-ok {
        color: #27ae60;
        font-size: 13px;
        text-align: center;
        margin-top: 8px;
    }
</style>
</head>
<body>

<div class="KhungTong">
<div class="KhungDN">
<div class="Tren1">

    <h3 style="text-align:center; margin-bottom: 16px;">Quên Mật Khẩu</h3>

    <form action="" method="POST">

        <!-- TÊN ĐĂNG NHẬP -->
        <div class="trentk">
            <input class="TenDN" name="txtTaiKhoan" type="text" placeholder="Tên đăng nhập" required>
        </div>

        <!-- HỌ TÊN (xác minh danh tính) -->
        <div class="trentk" style="margin-top: 12px;">
            <input class="TenDN" name="txtHoTen" type="text" placeholder="Họ và tên đầy đủ" required>
        </div>

        <div class="Giua" style="margin-top: 16px;">
            <button class="dnxduong" type="submit" name="xacminh">Xác Minh</button>
        </div>

    </form>

    <?php
    if (isset($_POST['xacminh'])) {

        include "config.php";

        $TaiKhoan = trim($_POST['txtTaiKhoan']);
        $HoTen    = trim($_POST['txtHoTen']);

        $sql = "SELECT MaND FROM NguoiDung WHERE TenDangNhap = ? AND HoTen = ?";
        $params = [$TaiKhoan, $HoTen];
        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            // Lưu vào session để dùng ở bước đổi mật khẩu
            $_SESSION['reset_MaND']     = $row['MaND'];
            $_SESSION['reset_TaiKhoan'] = $TaiKhoan;

            header("Location: DoiMatKhau.php");
            exit;
        } else {
            echo '<p class="thongbao-loi">Tên đăng nhập hoặc họ tên không đúng!</p>';
        }
    }
    ?>

</div>

<div class="Duoi" style="margin-top: 12px; text-align:center;">
    <a class="hoac1" href="DangNhap.php">← Quay lại đăng nhập</a>
</div>

</div>
</div>

</body>
</html>
