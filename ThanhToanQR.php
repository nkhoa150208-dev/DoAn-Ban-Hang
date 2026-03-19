<?php
session_start();
include "config.php";

if (!isset($_SESSION['MaND']) || !isset($_GET['madh'])) {
    header('Location: TrangChuDaDangNhap.php');
    exit;
}

$maDH = (int)$_GET['madh'];
$user_id = (int)$_SESSION['MaND'];

// Lấy thông tin đơn hàng vừa đặt
$sql = "SELECT TongTien, TrangThai FROM DonHang WHERE MaDH = ? AND MaND = ?";
$stmt = sqlsrv_query($conn, $sql, [$maDH, $user_id]);
$donHang = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

if (!$donHang) {
    die("Không tìm thấy đơn hàng!");
}

// ========================================================
// 1. CẤU HÌNH THÔNG TIN NHẬN TIỀN CỦA BẠN (SỬA Ở ĐÂY)
// ========================================================
$ngan_hang = "MB"; // Tên viết tắt ngân hàng (VD: MB, VCB, TCB, ACB, Viettinbank...)
$stk = "0585246973"; // Số tài khoản của bạn
$ten_chu_tk = "VU QUOC THANH"; // Tên chủ tài khoản (Viết hoa không dấu)
// ========================================================

$so_tien = $donHang['TongTien'];
$noi_dung = "Thanh toan don hang " . $maDH;

// 2. LINK API TẠO MÃ VIETQR TỰ ĐỘNG
$link_qr = "https://img.vietqr.io/image/{$ngan_hang}-{$stk}-compact2.png?amount={$so_tien}&addInfo=" . urlencode($noi_dung) . "&accountName=" . urlencode($ten_chu_tk);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Thanh Toán QR</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Exo+2:wght@400;600;700&display=swap" rel="stylesheet">
<style>
    body { font-family: 'Exo 2', sans-serif; background: #050d1a; color: #e2eaf5; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
    .qr-container { background: #0d1f38; border: 1px solid rgba(0,229,255,0.3); border-radius: 16px; padding: 40px 30px; text-align: center; max-width: 450px; box-shadow: 0 0 30px rgba(0,229,255,0.1); }
    .title { font-family: 'Orbitron', sans-serif; color: #00e5ff; font-size: 22px; font-weight: bold; margin-bottom: 10px; }
    .amount { font-family: 'Orbitron', sans-serif; color: #a855f7; font-size: 28px; font-weight: bold; margin: 20px 0; }
    .qr-img { width: 100%; max-width: 300px; border-radius: 12px; border: 2px solid #00e5ff; padding: 10px; background: #fff; margin-bottom: 20px; }
    .btn-home { background: linear-gradient(135deg, #22c55e, #16a34a); color: #fff; text-decoration: none; padding: 12px 24px; border-radius: 8px; font-weight: bold; display: inline-block; transition: 0.3s; text-transform: uppercase; }
    .btn-home:hover { transform: translateY(-3px); box-shadow: 0 8px 15px rgba(34,197,94,0.4); }
    .instructions { font-size: 14px; color: #7a92b0; margin-bottom: 20px; line-height: 1.6; }
</style>
</head>
<body>

<div class="qr-container">
    <div class="title">QUÉT MÃ ĐỂ THANH TOÁN</div>
    <p class="instructions">Sử dụng App Ngân hàng hoặc MoMo để quét mã này.<br>Số tiền và nội dung sẽ được nhập tự động.</p>
    
    <div class="amount"><?= number_format($so_tien, 0, ',', '.') ?> VNĐ</div>
    
    <img src="<?= $link_qr ?>" alt="Mã QR Thanh Toán" class="qr-img">
    
    <div style="font-size: 14px; margin-bottom: 20px;">
        Nội dung CK: <strong style="color:#00e5ff;"><?= $noi_dung ?></strong>
    </div>

    <a href="ChinhSuaProfile.php?s=donhang_khach" class="btn-home">Đã chuyển khoản xong</a>
</div>

</body>
</html>