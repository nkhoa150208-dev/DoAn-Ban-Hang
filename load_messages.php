<?php
session_start();

// GỌI FILE KẾT NỐI DATABASE VÀO ĐÂY
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

if(isset($_SESSION['MaND'])) {
    $maNguoiGui = $_SESSION['MaND'];
    $maAdmin = 1; // ID của Admin

    // Lấy tin nhắn giữa khách đang đăng nhập và Admin, sắp xếp theo thời gian cũ -> mới
    $sql = "SELECT * FROM TinNhan 
            WHERE (MaNguoiGui = ? AND MaNguoiNhan = ?) 
               OR (MaNguoiGui = ? AND MaNguoiNhan = ?) 
            ORDER BY ThoiGian ASC";
    $params = array($maNguoiGui, $maAdmin, $maAdmin, $maNguoiGui);
    $stmt = sqlsrv_query($conn, $sql, $params);

    while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        if($row['MaNguoiGui'] == $maNguoiGui) {
            // Tin nhắn của Khách (Nằm bên phải, màu xanh)
            echo '<div style="text-align: right; margin: 5px 0; clear: both;">
                    <span style="display: inline-block; padding: 8px 12px; background: #007bff; color: white; border-radius: 15px;">'.$row['NoiDung'].'</span>
                  </div>';
        } else {
            // Tin nhắn của Admin (Nằm bên trái, màu xám)
            echo '<div style="text-align: left; margin: 5px 0; clear: both;">
                    <span style="display: inline-block; padding: 8px 12px; background: #e9ecef; color: black; border-radius: 15px;">'.$row['NoiDung'].'</span>
                  </div>';
        }
    }
}
?>