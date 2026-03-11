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

// Bắt ID khách gửi từ AJAX và đảm bảo Admin đang thao tác
if(isset($_POST['id_khach']) && isset($_POST['noidung']) && isset($_SESSION['MaND']) && $_SESSION['MaND'] == 1) {
    $maNguoiNhan = $_POST['id_khach']; // Gửi lại cho khách
    $maAdmin = 1; // Admin là người gửi
    $noiDung = trim($_POST['noidung']);

    if($noiDung != "") {
        $sql = "INSERT INTO TinNhan (MaNguoiGui, MaNguoiNhan, NoiDung, ThoiGian, DaDoc) VALUES (?, ?, ?, GETDATE(), 0)";
        $params = array($maAdmin, $maNguoiNhan, $noiDung);
        sqlsrv_query($conn, $sql, $params);
    }
}
?>