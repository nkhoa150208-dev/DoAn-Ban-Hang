<?php
// Kết nối Database
$serverName = "localhost\\SQLEXPRESS";
$database   = "QLBanHang";
$connectionInfo = [
    "Database" => $database,
    "TrustServerCertificate" => true,
    "CharacterSet" => "UTF-8"
];
$conn = sqlsrv_connect($serverName, $connectionInfo);

if (isset($_POST['tukhoa'])) {
    $tuKhoa = trim($_POST['tukhoa']);
    
    // Lấy ra 5 sản phẩm khớp nhất với từ khóa
    $sql = "SELECT TOP 5 MaSP, TenSP, Gia, MaDM FROM SanPham WHERE TenSP LIKE ?";
    $params = array("%" . $tuKhoa . "%"); 
    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt && sqlsrv_has_rows($stmt)) {
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $gia = number_format($row['Gia'], 0, ',', '.') . 'đ';
            $icon = ($row['MaDM'] == 1) ? '💻' : (($row['MaDM'] == 2) ? '📱' : '📦');

            // ĐIỂM THAY ĐỔI: Gắn link chuyển thẳng sang trang Chi Tiết Sản Phẩm kèm theo ID
            echo '<a href="ChiTietSanPham.php?id=' . $row['MaSP'] . '" class="search-item">';
            echo '  <div class="search-item-icon">'.$icon.'</div>';
            echo '  <div class="search-item-info">';
            echo '      <span class="search-item-name">'.htmlspecialchars($row['TenSP']).'</span>';
            echo '      <span class="search-item-price">'.$gia.'</span>';
            echo '  </div>';
            echo '</a>';
        }
    } else {
        echo '<div class="search-empty">🚫 Không tìm thấy sản phẩm nào phù hợp!</div>';
    }
}
?>