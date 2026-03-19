<?php
$serverName = "localhost\\SQLEXPRESS";
$database   = "QLBanHang";
$connectionInfo = [
    "Database" => $database,
    "TrustServerCertificate" => true,
    "CharacterSet" => "UTF-8"
];
$conn = sqlsrv_connect($serverName, $connectionInfo);

if(isset($_POST['tukhoa'])) {
    $tukhoa = trim($_POST['tukhoa']);
    
    // Lấy tối đa 5 sản phẩm khớp từ khóa để cái khung xổ xuống không bị quá dài
    $sql = "SELECT TOP 5 * FROM SanPham WHERE TenSP LIKE ?"; 
    $params = array("%".$tukhoa."%");
    $stmt = sqlsrv_query($conn, $sql, $params);

    if($stmt && sqlsrv_has_rows($stmt)) {
        while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            
            // XỬ LÝ HÌNH ẢNH
            $hinhAnh = $row['HinhAnh'];
            $hienThiAnh = "";
            
            if (!empty($hinhAnh)) {
                // Nếu có ảnh thật trong Database -> Hiện ảnh thật bo góc
                $hienThiAnh = '<img src="' . htmlspecialchars($hinhAnh) . '" style="width: 40px; height: 40px; object-fit: contain; border-radius: 6px; background: var(--panel2); padding: 2px;">';
            } else {
                // Nếu chưa có ảnh -> Hiện Icon cứu cánh
                $icon = '📦';
                if ($row['MaDM'] == 1 || $row['MaDM'] == 3) $icon = '💻';
                elseif ($row['MaDM'] == 2) $icon = '📱';
                elseif ($row['MaDM'] == 4) $icon = '🎧';
                elseif ($row['MaDM'] == 5) $icon = '🖱️';
                $hienThiAnh = '<div style="font-size: 24px;">' . $icon . '</div>';
            }

            // IN RA GIAO DIỆN TỪNG DÒNG TÌM KIẾM
            echo '
            <a href="ChiTietSanPham.php?id='.$row['MaSP'].'" class="search-item">
                <div class="search-item-icon" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    '.$hienThiAnh.'
                </div>
                <div class="search-item-info">
                    <div class="search-item-name">'.htmlspecialchars($row['TenSP']).'</div>
                    <div class="search-item-price">'.number_format($row['Gia'], 0, ',', '.').'đ</div>
                </div>
            </a>';
        }
    } else {
        // Trả về nếu gõ sai tên không có trong DB
        echo '<div class="search-empty">🔍 Không tìm thấy sản phẩm nào!</div>';
    }
}
?>