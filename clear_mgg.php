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
    // 1. Xóa dữ liệu trong ví giảm giá trước (Tránh lỗi Foreign Key)
    $xoaVi = sqlsrv_query($conn, "DELETE FROM ViGiamGia");
    
    // 2. Xóa toàn bộ mã giảm giá
    $xoaMa = sqlsrv_query($conn, "DELETE FROM MaGiamGia");
    
    if ($xoaVi !== false && $xoaMa !== false) {
        // 3. Reset lại cột ID (MaMGG) bắt đầu lại từ 1
        sqlsrv_query($conn, "DBCC CHECKIDENT ('MaGiamGia', RESEED, 0)");
        
        echo "<h1 style='color: green; text-align: center; margin-top: 50px;'>✅ ĐÃ DỌN SẠCH TOÀN BỘ MÃ GIẢM GIÁ!</h1>";
        echo "<p style='text-align: center;'>Bảng mã giảm giá đã trống trơn và ID đã được reset về 1.</p>";
    } else {
        echo "<h1 style='color: red;'>❌ Lỗi khi xóa:</h1>";
        echo "<pre>" . print_r(sqlsrv_errors(), true) . "</pre>";
    }
}
?>