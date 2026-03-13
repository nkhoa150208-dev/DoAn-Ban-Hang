<?php
session_start();
// Kiểm tra đăng nhập
if (!isset($_SESSION['MaND'])) { 
    header('Location: DangNhap.php'); 
    exit; 
}

// Logic xóa sản phẩm nếu cần (tùy chọn thêm)
if (isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['id'])) {
    unset($_SESSION['giohang'][$_GET['id']]);
    header('Location: ChiTietGioHang.php');
    exit;
}
// --- PHẦN 1: XỬ LÝ CẬP NHẬT (Dành cho AJAX) ---
if (isset($_POST['id_sanpham']) && isset($_POST['thay_doi'])) {
    $maSP = $_POST['id_sanpham'];
    $thayDoi = (int)$_POST['thay_doi'];

    // 1. Kết nối database để kiểm tra tồn kho
    $serverName = "localhost\\SQLEXPRESS";
    $connectionInfo = array("Database" => "QLBanHang", "CharacterSet" => "UTF-8", "TrustServerCertificate" => true);
    $conn = sqlsrv_connect($serverName, $connectionInfo);

    if (isset($_SESSION['giohang'][$maSP])) {
        $soLuongHienTai = $_SESSION['giohang'][$maSP]['SoLuong'];
        $soLuongMoi = $soLuongHienTai + $thayDoi;

        if ($soLuongMoi > $soLuongHienTai) { 
            // Nếu là hành động TĂNG (+), phải check kho
            $sql = "SELECT SoLuongTon FROM SanPham WHERE MaSP = ?";
            $stmt = sqlsrv_query($conn, $sql, array($maSP));
            $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            $tonKho = $row['SoLuongTon'];

            if ($soLuongMoi > $tonKho) {
                // Trả về thông báo lỗi cho AJAX
                echo "VUOT_KHO"; 
                exit;
            }
        }

        // Nếu ok hoặc là hành động giảm
        if ($soLuongMoi > 0) {
            $_SESSION['giohang'][$maSP]['SoLuong'] = $soLuongMoi;
            echo "SUCCESS";
        } else {
            unset($_SESSION['giohang'][$maSP]);
            echo "REMOVED";
        }
    }
    exit; 
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi Tiết Giỏ Hàng - KhoaOngNghiem Tech</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Exo+2:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <style>
        :root {
            --navy: #050d1a;
            --panel: #0d1f38;
            --cyan: #00e5ff;
            --purple: #7c3aed;
            --green: #22c55e;
            --border: rgba(0,229,255,0.2);
        }

        body { 
            font-family: 'Exo 2', sans-serif; 
            background: var(--navy); 
            color: #e2eaf5; 
            padding: 40px; 
            margin: 0;
        }

        .cart-container { 
            max-width: 900px; 
            margin: 0 auto; 
            background: var(--panel); 
            border: 1px solid var(--border); 
            border-radius: 12px; 
            padding: 30px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }

        h2 { 
            font-family: 'Orbitron', sans-serif; 
            color: var(--cyan); 
            text-align: center; 
            margin-bottom: 30px; 
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th, td { padding: 15px; border-bottom: 1px solid rgba(0,229,255,0.1); text-align: left; }
        th { color: #7a92b0; text-transform: uppercase; font-size: 13px; letter-spacing: 1px; }

        /* CSS MỚI: BỘ ĐIỀU KHIỂN SỐ LƯỢNG */
        .quantity-control {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-qty {
            width: 32px;
            height: 32px;
            border: 1px solid var(--cyan);
            background: transparent;
            color: var(--cyan);
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            font-size: 18px;
            transition: 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 0;
        }

        .btn-qty:hover {
            background: var(--cyan);
            color: var(--navy);
            box-shadow: 0 0 15px rgba(0,229,255,0.4);
        }

        .qty-number {
            font-family: 'Orbitron', sans-serif;
            font-weight: bold;
            min-width: 25px;
            text-align: center;
            font-size: 16px;
        }

        .total-row { 
            font-size: 22px; 
            font-weight: bold; 
            color: var(--green); 
            text-align: right; 
            font-family: 'Orbitron', sans-serif;
        }

        .btn-group { display: flex; justify-content: space-between; margin-top: 30px; align-items: center; }
        
        .btn { 
            padding: 12px 25px; 
            border-radius: 8px; 
            border: none; 
            font-weight: bold; 
            cursor: pointer; 
            text-decoration: none; 
            color: white; 
            transition: 0.3s;
            display: inline-block;
        }

        .btn-back { background: #334155; }
        .btn-back:hover { background: #475569; }

        .btn-checkout { 
            background: linear-gradient(135deg, #22c55e, #16a34a); 
            font-size: 16px;
            font-family: 'Orbitron', sans-serif;
            box-shadow: 0 4px 15px rgba(34,197,94,0.3);
        }
        .btn-checkout:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(34,197,94,0.4); }

        .empty-cart { text-align: center; padding: 60px; color: #7a92b0; }
        
        /* Nút xóa sản phẩm */
        .btn-remove { color: #ef4444; text-decoration: none; font-size: 12px; margin-left: 10px; }
        .btn-remove:hover { text-decoration: underline; }
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
                    <td>
                        <strong><?php echo $sp['TenSP']; ?></strong>
                        <br>
                        <a href="ChiTietGioHang.php?action=remove&id=<?php echo $maSP; ?>" class="btn-remove">Xóa món này</a>
                    </td>
                    <td style="color: var(--cyan); font-family: 'Orbitron';">
                        <?php echo number_format($sp['Gia'], 0, ',', '.'); ?>đ
                    </td>
                    <td>
                        <div class="quantity-control">
                            <button class="btn-qty" onclick="updateQty(<?php echo $maSP; ?>, -1)">−</button>
                            <span class="qty-number" id="qty-<?php echo $maSP; ?>"><?php echo $sp['SoLuong']; ?></span>
                            <button class="btn-qty" onclick="updateQty(<?php echo $maSP; ?>, 1)">+</button>
                        </div>
                    </td>
                    <td style="color: #a855f7; font-weight: bold; font-family: 'Orbitron';">
                        <?php echo number_format($thanhTien, 0, ',', '.'); ?>đ
                    </td>
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
            <h3 style="color: var(--cyan)">Giỏ hàng của bạn đang trống!</h3>
            <p>Hãy quay lại trang chủ và chọn cho mình những món đồ công nghệ yêu thích nhé.</p>
            <br>
            <a href="TrangChuDaDangNhap.php" class="btn btn-checkout" style="text-decoration:none">Quay lại Cửa Hàng</a>
        </div>
    <?php endif; ?>

</div>

<script>
function updateQty(maSP, change) {
    $.ajax({
        url: 'ChiTietGioHang.php', // Hoặc file xử lý của bạn
        type: 'POST',
        data: { id_sanpham: maSP, thay_doi: change },
        success: function(response) {
            if (response.trim() === "VUOT_KHO") {
                alert("⚠️ Xin lỗi: Số lượng trong kho không đủ để thêm nữa!");
            } else {
                // Chỉ reload khi cập nhật thành công
                location.reload(); 
            }
        },
        error: function() {
            alert("Lỗi kết nối!");
        }
    });
}
</script>

</body>
</html>