<?php
session_start();

$serverName = "localhost\\SQLEXPRESS";
$database   = "QLBanHang";

$connectionInfo = [
    "Database" => $database,
    "TrustServerCertificate" => true
];

$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Kiểm tra có ID khách truyền lên và người đang gọi là Admin (ID = 1)
if(isset($_GET['id_khach']) && isset($_SESSION['MaND']) && $_SESSION['MaND'] == 1) {
    $maKhach = $_GET['id_khach'];
    $maAdmin = 1;

    $sql = "SELECT * FROM TinNhan 
            WHERE (MaNguoiGui = ? AND MaNguoiNhan = ?) 
               OR (MaNguoiGui = ? AND MaNguoiNhan = ?) 
            ORDER BY ThoiGian ASC";
    $params = array($maKhach, $maAdmin, $maAdmin, $maKhach);
    $stmt = sqlsrv_query($conn, $sql, $params);

    while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        if($row['MaNguoiGui'] == $maAdmin) {
            // Admin gửi (Bên phải, màu xanh)
            echo '<div style="text-align: right; margin: 5px 0; clear: both;"><span style="display: inline-block; padding: 8px 12px; background: #007bff; color: white; border-radius: 15px;">'.$row['NoiDung'].'</span></div>';
        } else {
            // Khách gửi (Bên trái, màu xám)
            echo '<div style="text-align: left; margin: 5px 0; clear: both;"><span style="display: inline-block; padding: 8px 12px; background: #e9ecef; color: black; border-radius: 15px;">'.$row['NoiDung'].'</span></div>';
        }
    }
}
?>