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

    $sql = "SELECT TenSP, Gia, SoLuongTon FROM SanPham WHERE MaSP = ?";
    $stmt = sqlsrv_query($conn, $sql, array($maSP));
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

    if ($row) {
        $tonKho = $row['SoLuongTon'];
        $sl_trong_gio = 0;
        
        if (isset($_SESSION['giohang'][$maSP])) {
            $sl_trong_gio = $_SESSION['giohang'][$maSP]['SoLuong'];
        }

        if (($sl_trong_gio + 1) > $tonKho) {
            echo "VUOT_QUY_DINH"; 
            exit;
        }

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

        // TÍNH TỔNG SỐ LƯỢNG
        $tongSoLuong = 0;
        if (isset($_SESSION['giohang'])) {
            foreach ($_SESSION['giohang'] as $sp) {
                $tongSoLuong += $sp['SoLuong'];
            }
        }
        
        // Trả về con số tổng để JavaScript nhận diện
        echo $tongSoLuong; 
    }
}
?>