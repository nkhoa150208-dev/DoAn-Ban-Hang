<?php
session_start();
if (!isset($_SESSION['MaND'])) { header('Location: DangNhap.php'); exit; }

// Nếu không có dữ liệu đơn hàng thì về trang chủ
if (!isset($_SESSION['order_success'])) {
    header('Location: TrangChuDaDangNhap.php');
    exit;
}

$order = $_SESSION['order_success'];
unset($_SESSION['order_success']); // Xóa sau khi đọc

$labelThanhToan = [
    'COD'  => '💵 Thanh toán khi nhận hàng (COD)',
    'CK'   => '🏦 Chuyển khoản ngân hàng',
    'MOMO' => '💜 Ví điện tử MoMo',
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đặt Hàng Thành Công</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Exo+2:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Exo 2', sans-serif; background: #050d1a; color: #e2eaf5; padding: 60px 20px; text-align: center; }
        .card { max-width: 520px; margin: 0 auto; background: #0d1f38; border: 1px solid rgba(0,229,255,0.2); border-radius: 16px; padding: 40px 35px; }
        .check-icon { font-size: 64px; margin-bottom: 20px; animation: pop .4s ease; }
        @keyframes pop { from { transform: scale(0); } to { transform: scale(1); } }
        h2 { font-family: 'Orbitron', sans-serif; color: #22c55e; font-size: 22px; margin-bottom: 8px; }
        .sub { color: #7a92b0; margin-bottom: 30px; }
        .info-box { background: #0a1628; border-radius: 10px; padding: 18px 20px; text-align: left; margin-bottom: 25px; }
        .info-row { display: flex; justify-content: space-between; padding: 7px 0; border-bottom: 1px solid rgba(0,229,255,0.07); font-size: 14px; }
        .info-row:last-child { border-bottom: none; }
        .info-row .lbl { color: #7a92b0; }
        .info-row .val { color: #e2eaf5; font-weight: 600; text-align: right; max-width: 60%; }
        .total-final { font-size: 20px; font-weight: bold; color: #22c55e; margin: 18px 0 25px; }
        .btn { display: inline-block; padding: 12px 28px; border-radius: 8px; text-decoration: none; font-weight: bold; color: white; background: linear-gradient(135deg, #0ea5e9, #0284c7); }
        .btn:hover { opacity: 0.9; }
    </style>
</head>
<body>
    <div class="card">
        <div class="check-icon">✅</div>
        <h2>ĐẶT HÀNG THÀNH CÔNG!</h2>
        <p class="sub">Cảm ơn bạn đã mua hàng. Chúng tôi sẽ liên hệ sớm để xác nhận đơn hàng.</p>

        <div class="info-box">
            <div class="info-row">
                <span class="lbl">Mã đơn hàng</span>
                <span class="val" style="color:#00e5ff;">#<?php echo str_pad($order['maDonHang'], 6, '0', STR_PAD_LEFT); ?></span>
            </div>
            <div class="info-row">
                <span class="lbl">Người nhận</span>
                <span class="val"><?php echo htmlspecialchars($order['hoTen']); ?></span>
            </div>
            <div class="info-row">
                <span class="lbl">Số điện thoại</span>
                <span class="val"><?php echo htmlspecialchars($order['soDienThoai']); ?></span>
            </div>
            <div class="info-row">
                <span class="lbl">Địa chỉ giao hàng</span>
                <span class="val"><?php echo htmlspecialchars($order['diaChi']); ?></span>
            </div>
            <div class="info-row">
                <span class="lbl">Thanh toán</span>
                <span class="val"><?php echo $labelThanhToan[$order['thanhToan']] ?? $order['thanhToan']; ?></span>
            </div>
        </div>

        <div class="total-final">
            Tổng thanh toán: <?php echo number_format($order['tongTien'], 0, ',', '.'); ?>đ
        </div>

        <a href="TrangChuDaDangNhap.php" class="btn">🏠 Về trang chủ</a>
    </div>
</body>
</html>
