<?php
session_start();
?>
<!DOCTYPE html>
<html lang="vi">
    <head>
        <meta charset="UTF-8">
        <title>đồ án</title>
        <link rel="stylesheet" href="css/TrangChu.css">
        <script src="TrangChu.js"></script>
       
    </head>
    <body>
        <div class="KhungTong">
            <div class="KhungDN">
                <div class="Tren1">
                    <p class="Nhapmk3">Nhập mật khẩu 3 Lớp</p>
                    <form action="" method="POST"> <div class="trentk">
           <input class="TenDN"  maxlength="4" name="txtmk3lop" type="text" placeholder="Nhập mật khẩu 3 lớp" required>
        </div>
    <div class="Giua">
                    </div>          
                </div>
                <div  class="Giua">         
                 <button class="dnxduong" type="submit" name="login">Xác Nhận</button>
                </div>
            </form> 
           <?php
if (isset($_POST['login'])) {
    include "config.php";

    $MatKhau3Lop = $_POST['txtmk3lop'];


    $sql = "SELECT  MatKhau3Lop
            FROM NguoiDung
            WHERE MatKhau3Lop = ?";

    $params = [$MatKhau3Lop];
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {

        // ✅ Lưu session

        $_SESSION['MatKhau3Lop']        = $row['MatKhau3Lop'];
      

        // ✅ Chuyển trang
        header("Location: TrangChuDaDangNhap.php");
        exit;

    } else {
        echo '<p class="saithontinh"> Sai Mật khẩu 3 lớp</p>';
    }
}
?>


               

                
               
            </div>
        </div>
    </body>