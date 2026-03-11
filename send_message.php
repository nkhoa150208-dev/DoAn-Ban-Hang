<?php
session_start();

// GỌI FILE KẾT NỐI DATABASE CỦA BẠN VÀO ĐÂY (Sửa lại tên file nếu cần)
// require_once 'config.php'; 
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

// Đây là code kết nối mẫu (nếu bạn dùng SQL Server)
if(isset($_SESSION['MaND']) && isset($_POST['noidung'])) {
    $maNguoiGui = $_SESSION['MaND'];
    $maNguoiNhan = 1; // Mặc định gửi cho Admin (ID = 1)
    $noiDung = trim($_POST['noidung']);

    if($noiDung != "") {
        // Lưu tin nhắn vào CSDL
        $sql = "INSERT INTO TinNhan (MaNguoiGui, MaNguoiNhan, NoiDung, ThoiGian, DaDoc) VALUES (?, ?, ?, GETDATE(), 0)";
        $params = array($maNguoiGui, $maNguoiNhan, $noiDung);
        sqlsrv_query($conn, $sql, $params);
    }
}
?>