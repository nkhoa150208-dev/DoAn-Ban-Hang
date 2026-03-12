<?php
session_start();
if (!isset($_SESSION['MaND'])) { header('Location: DangNhap.php'); exit; }
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi Tiết Giỏ Hàng</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Exo+2:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Exo 2', sans-serif; background: #050d1a; color: #e2eaf5; padding: 40px; }
        .cart-container { max-width: 900px; margin: 0 auto; background: #0d1f38; border: 1px solid rgba(0,229,255,0.2); border-radius: 12px; padding: 30px; }
        h2 { font-family: 'Orbitron', sans-serif; color: #00e5ff; text-align: center; margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th, td { padding: 15px; border-bottom: 1px solid rgba(0,229,255,0.1); text-align: left; }
        th { color: #7a92b0; text-transform: uppercase; font-size: 13px; }
        .total-row { font-size: 20px; font-weight: bold; color: #22c55e; text-align: right; }
        .btn-group { display: flex; justify-content: space-between; margin-top: 20px; }
        .btn { padding: 12px 25px; border-radius: 8px; border: none; font-weight: bold; cursor: pointer; text-decoration: none; color: white; }
        .btn-back { background: #334155; }
        .btn-checkout { background: linear-gradient(135deg, #22c55e, #16a34a); font-size: 16px; }
        .empty-cart { text-align: center; padding: 50px; color: #7a92b0; }
    </style>
</head>
<body>

<div class="cart-container">
    <h2>🛒 GIỎ HÀNG CỦA BẠN</h2>

    <?php if(isset($_SESSION['giohang']) && count($_SESSION['giohang']) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Tên Sản Phẩm</th>
                    <th>Đơn Giá</th>
                    <th>Số Lượng</th>
                    <th>Thành Tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $tongTien = 0;
                foreach($_SESSION['giohang'] as $maSP => $sp): 
                    $thanhTien = $sp['Gia'] * $sp['SoLuong'];
                    $tongTien += $thanhTien;
                ?>
                <tr>
                    <td><strong><?php echo $sp['TenSP']; ?></strong></td>
                    <td style="color: #00e5ff;"><?php echo number_format($sp['Gia'], 0, ',', '.'); ?>đ</td>
                    <td><?php echo $sp['SoLuong']; ?></td>
                    <td style="color: #a855f7; font-weight: bold;"><?php echo number_format($thanhTien, 0, ',', '.'); ?>đ</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="total-row">
            Tổng cộng: <?php echo number_format($tongTien, 0, ',', '.'); ?>đ
        </div>

        <div class="btn-group">
            <a href="TrangChuDaDangNhap.php" class="btn btn-back">← Tiếp tục mua sắm</a>
            <form action="xu_ly_dat_hang.php" method="POST">
                <button type="submit" class="btn btn-checkout">⚡ TIẾN HÀNH ĐẶT HÀNG</button>
            </form>
        </div>

    <?php else: ?>
        <div class="empty-cart">
            <h3>Giỏ hàng của bạn đang trống!</h3>
            <p>Hãy quay lại trang chủ và chọn cho mình những món đồ công nghệ yêu thích nhé.</p>
            <br>
            <a href="TrangChuDaDangNhap.php" class="btn btn-checkout">Quay lại Cửa Hàng</a>
        </div>
    <?php endif; ?>

</div>

</body>
</html>