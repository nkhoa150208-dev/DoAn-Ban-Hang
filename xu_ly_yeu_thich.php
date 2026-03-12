<?php
session_start();
if (!isset($_SESSION['MaND']) || !isset($_POST['id_sanpham'])) {
    echo "error"; 
    exit;
}

$serverName = "localhost\\SQLEXPRESS";
$database   = "QLBanHang";
$connectionInfo = ["Database" => $database, "TrustServerCertificate" => true, "CharacterSet" => "UTF-8"];
$conn = sqlsrv_connect($serverName, $connectionInfo);

$maND = (int)$_SESSION['MaND'];
$maSP = (int)$_POST['id_sanpham'];

// Kiểm tra xem sản phẩm đã được thả tim chưa
$sql_check = "SELECT MaYT FROM YeuThich WHERE MaND = ? AND MaSP = ?";
$stmt_check = sqlsrv_query($conn, $sql_check, [$maND, $maSP]);

if (sqlsrv_has_rows($stmt_check)) {
    // Nếu có rồi -> Khách muốn bỏ tim -> Xóa khỏi DB
    sqlsrv_query($conn, "DELETE FROM YeuThich WHERE MaND = ? AND MaSP = ?", [$maND, $maSP]);
    echo "removed";
} else {
    // Nếu chưa có -> Thêm vào DB
    sqlsrv_query($conn, "INSERT INTO YeuThich (MaND, MaSP) VALUES (?, ?)", [$maND, $maSP]);
    echo "added";
}
?>