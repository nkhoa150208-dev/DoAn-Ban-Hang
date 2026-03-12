<?php
session_start();

// Chưa đăng nhập bước 1 thì quay lại
if (!isset($_SESSION['MaND'])) {
    header('Location: DangNhap.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Xác nhận mật khẩu 3 lớp</title>
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
                    <input class="TenDN" maxlength="4" name="txtmk3lop" type="text"
                           placeholder="Nhập mật khẩu 3 lớp" required>
                </div>
                <div class="Giua">
                    <button class="dnxduong" type="submit" name="login">Xác Nhận</button>
                </div>
            </form>

            <?php
            if (isset($_POST['login'])) {
                include "config.php";

                $MatKhau3Lop = $_POST['txtmk3lop'];
                $MaND        = $_SESSION['MaND']; // ← lấy từ session bước 1

                // ✅ Kiểm tra đúng người + đúng mật khẩu 3 lớp
                $sql = "SELECT MaND, MatKhau3Lop
                        FROM NguoiDung
                        WHERE MaND = ? AND MatKhau3Lop = ?";

                $stmt = sqlsrv_query($conn, $sql, [$MaND, $MatKhau3Lop]);

                if ($stmt === false) {
                    die(print_r(sqlsrv_errors(), true));
                }

                if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                    // ✅ Xác nhận thành công - session MaND đã có sẵn từ bước 1
                    $_SESSION['MatKhau3Lop'] = $row['MatKhau3Lop'];
                    header("Location: TrangChuDaDangNhap.php");
                    exit;
                } else {
                    echo '<p class="saithontinh">Sai mật khẩu 3 lớp</p>';
                }
            }
            ?>
        </div>
    </div>
</div>
</body>
</html>