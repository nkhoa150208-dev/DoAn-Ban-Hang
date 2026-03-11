<?php
session_start();

// 1. KẾT NỐI DATABASE (COPY Y HỆT TRANG CHỦ CỦA BẠN)
$serverName = "localhost\\SQLEXPRESS";
$database   = "QLBanHang";

$connectionInfo = [
    "Database" => $database,
    "TrustServerCertificate" => true
];

$conn = sqlsrv_connect($serverName, $connectionInfo);

// Bắt lỗi kết nối ngay tại cửa
if ($conn === false) {
    die("Lỗi kết nối CSDL: " . print_r(sqlsrv_errors(), true));
}

// 2. XỬ LÝ GIỎ HÀNG
if (isset($_POST['id_sanpham'])) {
    $maSP = $_POST['id_sanpham'];

    if (!isset($_SESSION['giohang'])) {
        $_SESSION['giohang'] = array();
    }

    if (isset($_SESSION['giohang'][$maSP])) {
        $_SESSION['giohang'][$maSP]['SoLuong'] += 1;
    } else {
        $sql = "SELECT TenSP, Gia FROM SanPham WHERE MaSP = ?";
        $params = array($maSP);
        
        $stmt = sqlsrv_query($conn, $sql, $params);
        
        if ($stmt === false) {
            die("Lỗi truy vấn SQL: " . print_r(sqlsrv_errors(), true));
        }

        if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $_SESSION['giohang'][$maSP] = array(
                'TenSP' => $row['TenSP'],
                'Gia' => $row['Gia'],
                'SoLuong' => 1
            );
        }
    }

    $tongSoLuong = 0;
    foreach ($_SESSION['giohang'] as $sp) {
        $tongSoLuong += $sp['SoLuong'];
    }
    
    echo $tongSoLuong;
} else {
    echo "Không nhận được mã sản phẩm";
}
?>