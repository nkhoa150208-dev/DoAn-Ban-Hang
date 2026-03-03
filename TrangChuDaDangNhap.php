<?php
session_start();


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

        <div class="KhungMenu">
           
            <div class="NoiDung">
                    <img class="Logo" src="Image/Logo.png" alt="">
                </div>
            <div class="ChuaNoiDungMenu">
                
                <div class="NoiDung">
                    <div class="ThanhTimKiem">
                        <input type="text" class="TimKiem" placeholder="Bạn tìm điện thoại, laptop gì..." required>
                    </div>
                </div>
                <div class="NoiDung">Danh Mục</div>
                <div class="NoiDung">Tin Tức</div>
                <div class="NoiDung1">
                

<div class="nonn">Tên đăng nhập: <p class="sinon"><?php echo $_SESSION['TenDangNhap']; ?></p></div>

<div class="nonn">Vai trò: <p class="sinon">
    <?php
echo ($_SESSION['VaiTro'] == 1) ? "Admin" : "Khách hàng";
?>
</p>

</div>
                </div>
                
              
            </div>
        </div>
        <div class="OGiua">

        </div>
       
    </body>