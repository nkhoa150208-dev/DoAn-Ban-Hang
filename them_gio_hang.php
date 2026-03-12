<?php
session_start();

$serverName = "localhost\\SQLEXPRESS";
$database   = "QLBanHang";

$connectionInfo = [
    "Database" => $database,
    "TrustServerCertificate" => true,
    "CharacterSet" => "UTF-8"
];
$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
    die("Lỗi kết nối CSDL");
}

if (isset($_POST['id_sanpham'])) {
    $maSP = $_POST['id_sanpham'];

    // 1. Kéo thông tin sản phẩm từ DB ra, đặc biệt là cột SoLuongTon
    $sql = "SELECT TenSP, Gia, SoLuongTon FROM SanPham WHERE MaSP = ?";
    $stmt = sqlsrv_query($conn, $sql, array($maSP));
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

    if ($row) {
        $tonKho = $row['SoLuongTon'];

        // 2. Tính xem trong giỏ hàng hiện tại khách đã có mấy cái rồi
        $sl_trong_gio = 0;
        if (isset($_SESSION['giohang'][$maSP])) {
            $sl_trong_gio = $_SESSION['giohang'][$maSP]['SoLuong'];
        }

        // 3. KIỂM TRA GIỚI HẠN: Nếu thêm 1 cái nữa mà vượt quá tồn kho -> Báo lỗi!
        if (($sl_trong_gio + 1) > $tonKho) {
            echo "VUOT_QUY_DINH"; 
            exit; // Dừng ngang tại đây, không cho thêm vào giỏ nữa
        }

        // 4. Nếu qua được bài test trên thì cho phép thêm vào giỏ
        if (!isset($_SESSION['giohang'])) {
            $_SESSION['giohang'] = array();
        }

        if (isset($_SESSION['giohang'][$maSP])) {
            $_SESSION['giohang'][$maSP]['SoLuong'] += 1;
        } else {
            $_SESSION['giohang'][$maSP] = array(
                'TenSP' => $row['TenSP'],
                'Gia' => $row['Gia'],
                'SoLuong' => 1
            );
        }

        // Đếm lại tổng số hàng báo về cho nút giỏ hàng góc trên
        $tongSoLuong = 0;
        foreach ($_SESSION['giohang'] as $sp) {
            $tongSoLuong += $sp['SoLuong'];
        }
        echo $tongSoLuong;
    }
}
?>