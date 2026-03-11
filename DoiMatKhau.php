<?php
session_start();

// Nếu chưa qua bước xác minh thì chuyển về quên mật khẩu
if (!isset($_SESSION['reset_MaND'])) {
    header("Location: QuenMatKhau.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Đổi Mật Khẩu</title>
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
    .input-wrap {
        position: relative;
        margin-top: 12px;
    }
</style>
</head>
<body>

<div class="KhungTong">
<div class="KhungDN">
<div class="Tren1">

    <h3 style="text-align:center; margin-bottom: 4px;">Đổi Mật Khẩu</h3>
    <p style="text-align:center; font-size:13px; color:#888; margin-bottom:16px;">
        Tài khoản: <strong><?= htmlspecialchars($_SESSION['reset_TaiKhoan']) ?></strong>
    </p>

    <form action="" method="POST">

        <!-- MẬT KHẨU MỚI -->
        <div class="trenmk">
            <input class="input-star" id="newpass" name="txtMatKhauMoi"
                   placeholder="Mật khẩu mới" type="password" required minlength="6">
            <div class="conmat">
                <img class="mat1" id="showPass1" src="Image/mathien.png">
                <img class="mat2" id="hidePass1" src="Image/Mat.png">
            </div>
        </div>

        <!-- XÁC NHẬN MẬT KHẨU -->
        <div class="trenmk" style="margin-top: 12px;">
            <input class="input-star" id="confirmpass" name="txtXacNhan"
                   placeholder="Xác nhận mật khẩu mới" type="password" required minlength="6">
            <div class="conmat">
                <img class="mat1" id="showPass2" src="Image/mathien.png">
                <img class="mat2" id="hidePass2" src="Image/Mat.png">
            </div>
        </div>

        <div class="Giua" style="margin-top: 16px;">
            <button class="dnxduong" type="submit" name="doi">Đổi Mật Khẩu</button>
        </div>

    </form>

    <?php
    if (isset($_POST['doi'])) {

        $MatKhauMoi = $_POST['txtMatKhauMoi'];
        $XacNhan    = $_POST['txtXacNhan'];

        if (strlen($MatKhauMoi) < 6) {
            echo '<p class="thongbao-loi">Mật khẩu phải có ít nhất 6 ký tự!</p>';
        } elseif ($MatKhauMoi !== $XacNhan) {
            echo '<p class="thongbao-loi">Mật khẩu xác nhận không khớp!</p>';
        } else {
            include "config.php";

            $MaND = $_SESSION['reset_MaND'];

            // Nếu hệ thống bạn có dùng hash mật khẩu, hãy hash ở đây
            // Ví dụ: $MatKhauMoi = password_hash($MatKhauMoi, PASSWORD_DEFAULT);

            $sql = "UPDATE NguoiDung SET MatKhau = ? WHERE MaND = ?";
            $params = [$MatKhauMoi, $MaND];
            $stmt = sqlsrv_query($conn, $sql, $params);

            if ($stmt === false) {
                die(print_r(sqlsrv_errors(), true));
            }

            // Xóa session reset sau khi đổi thành công
            unset($_SESSION['reset_MaND']);
            unset($_SESSION['reset_TaiKhoan']);

            echo '<p class="thongbao-ok">Đổi mật khẩu thành công! 
                  <a href="DangNhap.php">Đăng nhập ngay</a></p>';
        }
    }
    ?>

</div>

<div class="Duoi" style="margin-top: 12px; text-align:center;">
    <a class="hoac1" href="DangNhap.php">← Quay lại đăng nhập</a>
</div>

</div>
</div>

<script>
// Toggle show/hide password - mật khẩu mới
const showPass1 = document.getElementById('showPass1');
const hidePass1 = document.getElementById('hidePass1');
const newpass   = document.getElementById('newpass');

hidePass1.style.display = 'none';
showPass1.addEventListener('click', () => {
    newpass.type = 'text';
    showPass1.style.display = 'none';
    hidePass1.style.display = 'inline';
});
hidePass1.addEventListener('click', () => {
    newpass.type = 'password';
    hidePass1.style.display = 'none';
    showPass1.style.display = 'inline';
});

// Toggle show/hide password - xác nhận
const showPass2   = document.getElementById('showPass2');
const hidePass2   = document.getElementById('hidePass2');
const confirmpass = document.getElementById('confirmpass');

hidePass2.style.display = 'none';
showPass2.addEventListener('click', () => {
    confirmpass.type = 'text';
    showPass2.style.display = 'none';
    hidePass2.style.display = 'inline';
});
hidePass2.addEventListener('click', () => {
    confirmpass.type = 'password';
    hidePass2.style.display = 'none';
    showPass2.style.display = 'inline';
});
</script>

</body>
</html>
