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
                    <form action="" method="POST"> 
                        <div class="trentk">
                           <input class="TenDN" minlength="4" maxlength="4" name="txtmk3lop" type="password" inputmode="numeric" placeholder="Nhập mật khẩu 3 lớp" oninput="this.value = this.value.replace(/[^0-9]/g, '');" required>
                        </div>
                        <div class="Giua"></div>          
                </div>
                <div class="Giua">         
                 <button class="dnxduong" type="submit" name="login">Xác Nhận</button>
                </div>
            </form> 

<?php
// Bắt buộc phải có session_start() ở đầu file (bạn đã có rồi)

// 1. KIỂM TRA XEM ĐÃ QUA BƯỚC ĐĂNG NHẬP LỚP 1 CHƯA
if (!isset($_SESSION['MaND'])) {
    header("Location: DangNhap.php"); 
    exit;
}

if (isset($_POST['login'])) {
    include "config.php";

    $MatKhau3Lop = trim($_POST['txtmk3lop']);
    $MaND = $_SESSION['MaND']; // Lấy ID của người dùng từ bước đăng nhập trước

    // VỊ TRÍ SỐ 2: Kiểm tra độ dài có đúng 4 số không bằng PHP
    if (strlen($MatKhau3Lop) !== 4) {
        echo '<p class="saithontinh" style="color:red; text-align:center;">Mật khẩu 3 lớp phải nhập đủ 4 số!</p>';
    } else {
        // NẾU NHẬP ĐỦ 4 SỐ THÌ MỚI GỌI SQL
        // 2. TRUY VẤN: Chỉ kiểm tra mật khẩu của ĐÚNG người dùng đó
        $sql = "SELECT MatKhau3Lop 
                FROM NguoiDung 
                WHERE MaND = ? AND MatKhau3Lop = ?";

        $params = [$MaND, $MatKhau3Lop];
        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        // 3. KIỂM TRA KẾT QUẢ
        if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            // ✅ Đúng mật khẩu của user này
            $_SESSION['MatKhau3Lop'] = $row['MatKhau3Lop'];
            $_SESSION['DaXacNhanMK3'] = true; 
            
            // ✅ Chuyển trang
            header("Location: TrangChuDaDangNhap.php");
            exit;
        } else {
            echo '<p class="saithontinh" style="color:red; text-align:center;">Sai Mật khẩu 3 lớp</p>';
        }
    } // Đóng ngoặc của else (kiểm tra 4 số)
}
?>

            </div>
        </div>
    </body>
</html>