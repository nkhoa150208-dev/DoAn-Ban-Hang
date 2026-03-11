<?php
session_start();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Đăng nhập</title>

<link rel="stylesheet" href="css/TrangChu.css">
<script src="TrangChu.js"></script>

</head>

<body>

<div class="KhungTong">

<div class="KhungDN">

<div class="Tren1">

<form action="" method="POST">

<!-- TÊN ĐĂNG NHẬP -->
<div class="trentk">
<input class="TenDN" name="txtTaiKhoan" type="text" placeholder="Nhập Tên" required>
</div>

<!-- MẬT KHẨU -->
<div class="trenmk">
<input class="input-star" id="password" name="txtMatKhau" placeholder="Mật Khẩu" type="password" required>
<div class="conmat">
<img class="mat1" id="showPass" src="Image/mathien.png">
<img class="mat2" id="hidePass" src="Image/Mat.png">
</div>
</div>

<!-- NÚT ĐĂNG NHẬP -->
<div class="Giua">
<button class="dnxduong" type="submit" name="login">Đăng nhập</button>
</div>

</form>

</div>

<?php
if (isset($_POST['login'])) {

    include "config.php";

    $TenDangNhap = $_POST['txtTaiKhoan'];
    $MatKhau     = $_POST['txtMatKhau'];

    $sql = "SELECT MaND, TenDangNhap, HoTen, VaiTro
            FROM NguoiDung
            WHERE TenDangNhap = ? AND MatKhau = ?";

    $params = [$TenDangNhap, $MatKhau];
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {

        $_SESSION['MaND']        = $row['MaND'];
        $_SESSION['TenDangNhap'] = $row['TenDangNhap'];
        $_SESSION['HoTen']       = $row['HoTen'];
        $_SESSION['VaiTro']      = $row['VaiTro'];

        header("Location: MK3LOP.php");
        exit;

    } else {
        echo '<p class="saithontinh">Sai tài khoản hoặc mật khẩu</p>';
    }
}
?>

<!-- NÚT QUÊN MẬT KHẨU → dẫn sang trang QuenMatKhau.php -->
<div class="Duoi">
<a class="QuenMk" href="QuenMatKhau.php">Quên Mật Khẩu?</a>
</div>

<div class="hoac">

<div class="gachngang"></div>

<p class="Hoac">Hoặc</p>

<div class="gachngang"></div>

</div>

<div class="duoinua">
<a class="hoac1" href="DangKy.php">Tạo Tài Khoản mới</a>
</div>

</div>

</div>

<script>
const password  = document.getElementById('password');
const showPass  = document.getElementById('showPass');
const hidePass  = document.getElementById('hidePass');

// Mặc định: hiện icon "mắt hiện" (showPass), ẩn icon "mắt ẩn" (hidePass)
hidePass.style.display = 'none';

showPass.addEventListener('click', function () {
    password.type = 'text';
    showPass.style.display = 'none';
    hidePass.style.display = 'inline';
});

hidePass.addEventListener('click', function () {
    password.type = 'password';
    hidePass.style.display = 'none';
    showPass.style.display = 'inline';
});
</script>

</body>
</html>