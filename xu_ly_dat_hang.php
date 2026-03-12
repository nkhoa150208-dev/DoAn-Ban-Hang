<?php
session_start();

// 1. KẾT NỐI DATABASE
$serverName = "localhost\\SQLEXPRESS";
$database   = "QLBanHang";
$connectionInfo = [
    "Database" => $database,
    "TrustServerCertificate" => true,
    "CharacterSet" => "UTF-8"
];
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) die("Lỗi kết nối CSDL: " . print_r(sqlsrv_errors(), true));

// Kiểm tra đăng nhập và giỏ hàng
if (!isset($_SESSION['MaND'])) { header('Location: DangNhap.php'); exit; }
if (!isset($_SESSION['giohang']) || count($_SESSION['giohang']) == 0) {
    header('Location: TrangChuDaDangNhap.php'); exit;
}

$user_id = (int)$_SESSION['MaND'];
$error = "";

// 2. XỬ LÝ KHI BẤM NÚT "CHỐT ĐƠN HÀNG"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'place_order') {
    $maDC = $_POST['MaDC'] ?? '';
    $thanhToan = $_POST['ThanhToan'] ?? 'COD';
    $ghiChu = $_POST['GhiChu'] ?? '';

    if (empty($maDC)) {
        $error = "Vui lòng chọn địa chỉ giao hàng!";
    } else {
        // Lấy thông tin địa chỉ khách đã chọn
        $sql_dc = "SELECT * FROM SoDiaChi WHERE MaDC = ? AND MaND = ?";
        $stmt_dc = sqlsrv_query($conn, $sql_dc, [$maDC, $user_id]);
        $dc = sqlsrv_fetch_array($stmt_dc, SQLSRV_FETCH_ASSOC);

        // Lấy Email từ bảng NguoiDung
        $sql_u = "SELECT Email FROM NguoiDung WHERE MaND = ?";
        $stmt_u = sqlsrv_query($conn, $sql_u, [$user_id]);
        $u = sqlsrv_fetch_array($stmt_u, SQLSRV_FETCH_ASSOC);
        $email = $u['Email'] ?? '';

        if ($dc) {
            // Tính tổng tiền
            $tongTien = 0;
            foreach($_SESSION['giohang'] as $sp) {
                $tongTien += $sp['Gia'] * $sp['SoLuong'];
            }

            // A. THÊM VÀO BẢNG DonHang VÀ LẤY RA MÃ ĐƠN HÀNG VỪA TẠO (OUTPUT INSERTED.MaDH)
            $sql_insert_dh = "INSERT INTO DonHang (MaND, TongTien, HoTen, SoDienThoai, Email, DiaChi, ThanhPho, ThanhToan, GhiChu) 
                              OUTPUT INSERTED.MaDH 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $params_dh = [
                $user_id, $tongTien, $dc['HoTenNguoiNhan'], $dc['SoDienThoai'], 
                $email, $dc['DiaChiCuThe'], $dc['ThanhPho'], $thanhToan, $ghiChu
            ];
            
            $stmt_insert_dh = sqlsrv_query($conn, $sql_insert_dh, $params_dh);
            
            if ($stmt_insert_dh) {
                sqlsrv_fetch($stmt_insert_dh);
                $maDH_Moi = sqlsrv_get_field($stmt_insert_dh, 0); // Lấy MaDH

                // B. THÊM TỪNG MÓN VÀO BẢNG ChiTietDonHang
                foreach($_SESSION['giohang'] as $maSP => $sp) {
                    $sql_insert_ct = "INSERT INTO ChiTietDonHang (MaDH, MaSP, SoLuong, DonGia) VALUES (?, ?, ?, ?)";
                    sqlsrv_query($conn, $sql_insert_ct, [$maDH_Moi, $maSP, $sp['SoLuong'], $sp['Gia']]);
                }

                // C. XÓA GIỎ HÀNG VÀ CHUYỂN HƯỚNG SANG TRANG LỊCH SỬ ĐƠN HÀNG
                unset($_SESSION['giohang']);
                echo "<script>
                        alert('🎉 Đặt hàng thành công! Mã đơn của bạn là #".$maDH_Moi."');
                        window.location.href='DonHang.php';
                      </script>";
                exit;
            } else {
                $error = "Có lỗi xảy ra khi tạo đơn hàng. Vui lòng thử lại!";
            }
        } else {
            $error = "Địa chỉ giao hàng không hợp lệ!";
        }
    }
}

// Lấy danh sách địa chỉ để in ra màn hình
$sql_ds_dc = "SELECT * FROM SoDiaChi WHERE MaND = ? ORDER BY MacDinh DESC";
$stmt_ds_dc = sqlsrv_query($conn, $sql_ds_dc, [$user_id]);
$hasAddress = false;
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thanh Toán - TechVN</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Exo+2:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Exo 2', sans-serif; background: #050d1a; color: #e2eaf5; margin: 0; padding: 40px 20px; }
        .container { max-width: 1000px; margin: 0 auto; display: grid; grid-template-columns: 1.5fr 1fr; gap: 30px; }
        @media (max-width: 800px) { .container { grid-template-columns: 1fr; } }
        
        .card { background: #0d1f38; border: 1px solid rgba(0,229,255,0.2); border-radius: 12px; padding: 25px; box-shadow: 0 5px 20px rgba(0,0,0,0.3); }
        .card-title { font-family: 'Orbitron', monospace; font-size: 18px; color: #00e5ff; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .card-title::after { content: ''; flex: 1; height: 1px; background: linear-gradient(90deg, rgba(0,229,255,0.4), transparent); }
        
        /* Box Chọn Địa Chỉ */
        .addr-box { background: #0f2444; border: 1.5px solid rgba(0,229,255,0.1); border-radius: 8px; padding: 15px; margin-bottom: 12px; cursor: pointer; display: flex; gap: 12px; transition: 0.2s; }
        .addr-box:hover { border-color: rgba(0,229,255,0.5); }
        .addr-box input[type="radio"] { margin-top: 5px; accent-color: #00e5ff; transform: scale(1.2); }
        .addr-info h4 { margin: 0 0 5px 0; color: #fff; font-size: 15px; }
        .addr-info p { margin: 0; font-size: 13px; color: #7a92b0; }
        .badge-default { background: rgba(34,197,94,0.15); color: #4ade80; border: 1px solid rgba(34,197,94,0.3); padding: 2px 8px; border-radius: 12px; font-size: 10px; font-weight: bold; text-transform: uppercase; margin-left: 10px; }
        
        /* Box Sản Phẩm */
        .sp-item { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid rgba(0,229,255,0.1); }
        .sp-name { font-size: 14px; font-weight: 600; }
        .sp-price { color: #a855f7; font-weight: bold; font-family: 'Orbitron', sans-serif; }
        
        .total-row { display: flex; justify-content: space-between; font-size: 20px; font-weight: bold; color: #22c55e; margin-top: 20px; padding-top: 20px; border-top: 2px dashed rgba(34,197,94,0.3); }
        
        /* Form Inputs */
        textarea, select { width: 100%; background: #0f2444; border: 1px solid rgba(0,229,255,0.2); border-radius: 8px; color: #fff; padding: 12px; font-family: 'Exo 2', sans-serif; outline: none; margin-bottom: 20px; box-sizing: border-box;}
        textarea:focus, select:focus { border-color: #00e5ff; }
        
        .btn-submit { width: 100%; background: linear-gradient(135deg, #22c55e, #16a34a); color: #fff; border: none; border-radius: 10px; padding: 15px; font-size: 16px; font-weight: 700; cursor: pointer; transition: 0.3s; text-transform: uppercase; letter-spacing: 1px; }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(34,197,94,0.4); }
        
        .btn-link { color: #00e5ff; text-decoration: none; font-size: 13px; font-weight: bold; }
        .btn-link:hover { text-decoration: underline; }
        .error-msg { background: rgba(239,68,68,0.1); color: #ef4444; border: 1px solid rgba(239,68,68,0.3); padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; }
    </style>
</head>
<body>

    <?php if($error): ?><div class="error-msg">⚠️ <?php echo $error; ?></div><?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="action" value="place_order">
        
        <div class="container">
            <div>
                <div class="card" style="margin-bottom: 20px;">
                    <div class="card-title">📍 CHỌN ĐỊA CHỈ GIAO HÀNG</div>
                    
                    <?php 
                    while ($dc = sqlsrv_fetch_array($stmt_ds_dc, SQLSRV_FETCH_ASSOC)): 
                        $hasAddress = true;
                    ?>
                        <label class="addr-box">
                            <input type="radio" name="MaDC" value="<?php echo $dc['MaDC']; ?>" <?php echo ($dc['MacDinh']==1) ? 'checked' : ''; ?> required>
                            <div class="addr-info">
                                <h4>
                                    <?php echo $dc['HoTenNguoiNhan']; ?> - <?php echo $dc['SoDienThoai']; ?>
                                    <?php if($dc['MacDinh'] == 1): ?><span class="badge-default">Mặc định</span><?php endif; ?>
                                </h4>
                                <p><?php echo $dc['DiaChiCuThe']; ?></p>
                                <p><?php echo $dc['ThanhPho']; ?></p>
                            </div>
                        </label>
                    <?php endwhile; ?>

                    <?php if(!$hasAddress): ?>
                        <div style="text-align: center; padding: 20px; color: #7a92b0;">
                            Bạn chưa có địa chỉ nào! <br><br>
                            <a href="diachigiaohang.php" class="btn-submit" style="display:inline-block; width:auto; text-decoration:none; padding:10px 20px; font-size:14px;">+ Thêm địa chỉ mới</a>
                        </div>
                    <?php else: ?>
                        <div style="text-align: right; margin-top: 10px;">
                            <a href="diachigiaohang.php" class="btn-link">+ Thêm địa chỉ khác</a>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <div class="card-title">💳 PHƯƠNG THỨC THANH TOÁN</div>
                    <select name="ThanhToan">
                        <option value="COD">Thanh toán tiền mặt khi nhận hàng (COD)</option>
                        <option value="ChuyenKhoan">Chuyển khoản ngân hàng</option>
                        <option value="Momo">Thanh toán qua Ví MoMo</option>
                    </select>

                    <div class="card-title">📝 GHI CHÚ ĐƠN HÀNG</div>
                    <textarea name="GhiChu" rows="3" placeholder="Ví dụ: Giao hàng vào giờ hành chính..."></textarea>
                </div>
            </div>

            <div>
                <div class="card">
                    <div class="card-title">🛒 TÓM TẮT ĐƠN HÀNG</div>
                    
                    <?php 
                    $tongTien = 0;
                    foreach($_SESSION['giohang'] as $sp): 
                        $thanhTien = $sp['Gia'] * $sp['SoLuong'];
                        $tongTien += $thanhTien;
                    ?>
                        <div class="sp-item">
                            <div>
                                <div class="sp-name"><?php echo $sp['TenSP']; ?></div>
                                <div style="font-size: 12px; color: #7a92b0;">Số lượng: x<?php echo $sp['SoLuong']; ?></div>
                            </div>
                            <div class="sp-price"><?php echo number_format($thanhTien, 0, ',', '.'); ?>đ</div>
                        </div>
                    <?php endforeach; ?>

                    <div class="total-row">
                        <span>TỔNG CỘNG:</span>
                        <span><?php echo number_format($tongTien, 0, ',', '.'); ?>đ</span>
                    </div>

                    <button type="submit" class="btn-submit" style="margin-top: 25px;" <?php echo (!$hasAddress) ? 'disabled style="opacity:0.5; cursor:not-allowed;"' : ''; ?>>
                        ⚡ XÁC NHẬN CHỐT ĐƠN
                    </button>
                    
                    <div style="text-align: center; margin-top: 15px;">
                        <a href="ChiTietGioHang.php" class="btn-link">← Quay lại giỏ hàng</a>
                    </div>
                </div>
            </div>
        </div>
    </form>

</body>
</html>