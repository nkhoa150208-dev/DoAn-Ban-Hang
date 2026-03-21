<?php
$serverName = "localhost\\SQLEXPRESS";
$database   = "QLBanHang";
$connectionInfo = [
    "Database" => $database,
    "TrustServerCertificate" => true,
    "CharacterSet" => "UTF-8"
];
$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn) {
    $sql = "TRUNCATE TABLE TinNhan";
    if (sqlsrv_query($conn, $sql)) {
        echo "<h1>✅ ĐÃ DỌN SẠCH TOÀN BỘ TIN NHẮN! KHUNG CHAT ĐÃ TRẮNG TINH!</h1>";
    } else {
        echo "❌ Lỗi: " . print_r(sqlsrv_errors(), true);
    }
}
?>