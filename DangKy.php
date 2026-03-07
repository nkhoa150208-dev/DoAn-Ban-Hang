<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Đăng ký</title>
<link rel="stylesheet" href="css/Dangky.css">
<script src="TrangChu.js"></script>
</head>

<body>

<div class="KhungTong">
<div class="KhungDN">

<?php
session_start();

if (isset($_SESSION['error'])) {
echo "<p class='thongbao loi'>❌ {$_SESSION['error']}</p>";
unset($_SESSION['error']);
}

if (isset($_SESSION['success'])) {
echo "<p class='thongbao dung'>✅ {$_SESSION['success']}</p>";
unset($_SESSION['success']);
}
?>

<form class="formdky" action="XuLyDangKy.php" method="post">

<div class="Tren">

<!-- tên đăng nhập + mật khẩu -->

<input class="khung"
name="TenDangNhap"
type="text"
placeholder="Nhập Tên đăng nhập"
required>

<div class="trenmk">

<input class="khung"id="password"name="MatKhau"type="password"placeholder="Mật Khẩu"required>

<div class="conmat">
<img class="mat1" id="showPass" src="Image/mathien.png">
<img class="mat2" id="hidePass" src="Image/Mat.png">
</div>

</div>



<input class="khung" maxlength="4" name="MatKhau3Lop" type="text" placeholder="Mật Khẩu 3 Lớp">
<input class="khung" name="HoTen" type="text" placeholder="Họ Tên">
<input class="khung" name="Email" type="text" placeholder="Email">
<input class="khung" name="SoDienThoai" type="text" placeholder="Số Điện Thoại">
<input class="khung" name="DiaChi" type="text" placeholder="Địa Chỉ">

</div>

<button class="hoac1" type="submit">Đăng Ký</button>

</form>

<div class="Duoi">
<a class="hoac2" href="DangNhap.php">Đã có tài khoản?</a>
</div>

</div>
</div>


</body>
</html>