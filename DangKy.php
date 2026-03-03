<?php
session_start();

if (isset($_SESSION['error'])) {
    echo "<p style='color:red; text-align:center;'>
            ❌ {$_SESSION['error']}
          </p>";
    unset($_SESSION['error']);
}

if (isset($_SESSION['success'])) {
    echo "<p style='color:green; text-align:center;'>
            ✅ {$_SESSION['success']}
          </p>";
    unset($_SESSION['success']);
}
?>

<!DOCTYPE html>
<html lang="vi">
    <head>
        <meta charset="UTF-8">
        <title>đồ án</title>
        <link rel="stylesheet" href="css/TrangChu.css">
        <link rel="stylesheet" href="Image">
    </head>
    <body>
        <div class="KhungTong">
            <div class="KhungDN"> 
                <form class="formdky" action="XuLyDangKy.php" method="post">
                <div class="Tren">    
                    <div class="cumtren">
                <input class="TenDY khung"name="TenDangNhap" type="text" placeholder="Nhập Tên đăng nhập">
                <input class="MatKhau khung"name="MatKhau"type="password"  placeholder="Mật Khẩu">
                    </div>              
                <input class="MatKhau3Lop khung"  maxlength="4" name="MatKhau3Lop" type="text" placeholder="Mật Khẩu 3 Lớp">
                <input class="HoTen khung" name="HoTen" type="text" placeholder="Họ Tên">
                <input class="Email khung" name="Email" type="text" placeholder="Email">
                <input class="SoDienThoai khung" name="SoDienThoai" type="text" placeholder="Số Điện Thoại">
                <input class="DiaChi khung" name="DiaChi" type="text" placeholder="Địa Chỉ">
                </div>

            


    <button class="hoac1" type="submit">Đăng Ký tiền ngu</button>
</form>

               
              
                <div class="Duoi">
                    <a class="hoac2" href="DangNhap.php">đã có tài khoản?</a>
                </div>
                
               
            </div>
        </div>
    </body>