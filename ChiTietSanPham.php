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

// 2. LẤY ID SẢN PHẨM TỪ TRÊN THANH ĐỊA CHỈ (URL)
if (!isset($_GET['id'])) {
    header('Location: TrangChuDaDangNhap.php');
    exit;
}
$maSP = (int)$_GET['id'];

// 3. TRUY VẤN THÔNG TIN SẢN PHẨM & TÊN DANH MỤC
$sql = "SELECT sp.*, dm.TenDM 
        FROM SanPham sp 
        LEFT JOIN DanhMuc dm ON sp.MaDM = dm.MaDM 
        WHERE sp.MaSP = ?";
$stmt = sqlsrv_query($conn, $sql, [$maSP]);
$sp = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
$isFav = false;
if (isset($_SESSION['MaND'])) {
    $checkFav = sqlsrv_query($conn, "SELECT 1 FROM YeuThich WHERE MaND=? AND MaSP=?", [$_SESSION['MaND'], $maSP]);
    if ($checkFav && sqlsrv_has_rows($checkFav)) $isFav = true;
}
// Nếu gõ ID bậy bạ không có trong SQL
if (!$sp) {
    die("<h2 style='color:white; text-align:center; padding:50px;'>Sản phẩm không tồn tại hoặc đã bị xóa!</h2>");
}

// Đếm số lượng giỏ hàng để hiện lên góc phải
$tongGioHang = 0;
if (isset($_SESSION['giohang'])) {
    foreach ($_SESSION['giohang'] as $item) {
        $tongGioHang += $item['SoLuong'];
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($sp['TenSP']) ?> - TechVN</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700;900&family=Exo+2:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy: #050d1a; --panel: #0d1f38; --panel2: #0f2444;
            --cyan: #00e5ff; --purple2: #a855f7; --green: #22c55e;
            --tx: #e2eaf5; --muted: #7a92b0; --border: rgba(0,229,255,0.15);
        }
        body { font-family: 'Exo 2', sans-serif; background: var(--navy); color: var(--tx); margin: 0; padding: 20px; }
        
        /* Topbar */
        .topbar { max-width: 1000px; margin: 0 auto 30px; display: flex; align-items: center; justify-content: space-between; background: rgba(5,13,26,0.9); padding: 15px 20px; border-bottom: 1px solid var(--border); border-radius: 12px; }
        .logo { font-family: 'Orbitron', sans-serif; font-size: 20px; font-weight: 900; color: var(--cyan); text-decoration: none; }
        .nav-actions { display: flex; gap: 15px; }
        .btn-outline { padding: 8px 15px; border: 1px solid var(--cyan); color: var(--cyan); border-radius: 8px; text-decoration: none; font-weight: bold; transition: 0.2s; }
        .btn-outline:hover { background: rgba(0,229,255,0.1); }
        .cart-badge { background: var(--cyan); color: var(--navy); padding: 2px 6px; border-radius: 50%; font-size: 11px; margin-left: 5px; }

        /* Container */
        .sp-container { max-width: 1000px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 40px; background: var(--panel); border: 1px solid var(--border); border-radius: 16px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        @media (max-width: 768px) { .sp-container { grid-template-columns: 1fr; } }


        .btn-fav { width: 60px; font-size: 26px; background: var(--panel2); border: 1px solid var(--border); border-radius: 10px; cursor: pointer; transition: 0.3s; display: flex; align-items: center; justify-content: center; color: #ef4444; }
.btn-fav:hover { border-color: #ef4444; background: rgba(239,68,68,0.1); }
        /* Ảnh sản phẩm */
        .sp-img-box { background: linear-gradient(135deg, var(--panel2), var(--navy)); border-radius: 16px; display: flex; align-items: center; justify-content: center; height: 400px; font-size: 120px; border: 1px solid var(--border); filter: drop-shadow(0 0 20px rgba(0,229,255,0.1)); }
        
        /* Thông tin sản phẩm */
        .sp-cat { color: var(--cyan); font-size: 12px; text-transform: uppercase; font-weight: bold; letter-spacing: 2px; margin-bottom: 10px; }
        .sp-name { font-family: 'Orbitron', sans-serif; font-size: 28px; line-height: 1.2; margin: 0 0 15px 0; color: #fff; }
        .sp-price { font-family: 'Orbitron', sans-serif; font-size: 32px; font-weight: bold; color: var(--green); margin-bottom: 25px; }
        
        /* Cấu hình */
        .sp-specs { background: var(--panel2); border-radius: 12px; padding: 20px; margin-bottom: 25px; border: 1px solid var(--border); }
        .spec-item { display: flex; border-bottom: 1px dashed rgba(255,255,255,0.1); padding: 10px 0; font-size: 14px; }
        .spec-item:last-child { border-bottom: none; }
        .spec-title { width: 120px; color: var(--muted); font-weight: bold; }
        .spec-value { flex: 1; color: var(--tx); font-weight: 500; }

        /* Nút mua */
        .btn-buy { width: 100%; padding: 16px; font-size: 16px; font-weight: bold; text-transform: uppercase; background: linear-gradient(135deg, var(--purple2), #7c3aed); color: white; border: none; border-radius: 10px; cursor: pointer; transition: 0.3s; font-family: 'Exo 2', sans-serif; box-shadow: 0 4px 15px rgba(168,85,247,0.4); }
        .btn-buy:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(168,85,247,0.6); }

        /* Mô tả */
        .sp-desc { margin-top: 30px; font-size: 15px; color: var(--muted); line-height: 1.6; }
    </style>
</head>
<body>

<div class="topbar">
    <a href="TrangChuDaDangNhap.php" class="logo">&#x1F6CD; KhoaOngNghiem Tech</a>
    <div class="nav-actions">
        <a href="ChiTietGioHang.php" class="btn-outline">
            🛒 Giỏ hàng <span class="cart-badge" id="so-luong-gio-hang"><?= $tongGioHang ?></span>
        </a>
        <a href="TrangChuDaDangNhap.php" class="btn-outline">← Trở về</a>
    </div>
</div>

<div class="sp-container">
    <div class="sp-img-box">
        <?php 
        // Chế icon nếu chưa có ảnh
        if(!empty($sp['HinhAnh'])) {
            echo "<img src='".htmlspecialchars($sp['HinhAnh'])."' style='max-width:100%; max-height:100%; object-fit:contain;' alt=''>";
        } else {
            echo ($sp['MaDM'] == 1) ? '💻' : (($sp['MaDM'] == 2) ? '📱' : '📦'); 
        }
        ?>
    </div>

    <div>
        <div class="sp-cat">Danh mục: <?= htmlspecialchars($sp['TenDM'] ?? 'Chưa phân loại') ?></div>
        <h1 class="sp-name"><?= htmlspecialchars($sp['TenSP']) ?></h1>
        <div class="sp-price"><?= number_format($sp['Gia'], 0, ',', '.') ?> đ</div>
        
        <div class="sp-specs">
            <h3 style="margin-top:0; color: var(--cyan); font-size: 16px; margin-bottom: 15px;">Thông số kỹ thuật</h3>
            
            <?php if(!empty($sp['CPU'])): ?>
            <div class="spec-item"><div class="spec-title">CPU</div><div class="spec-value"><?= htmlspecialchars($sp['CPU']) ?></div></div>
            <?php endif; ?>
            
            <?php if(!empty($sp['RAM'])): ?>
            <div class="spec-item"><div class="spec-title">RAM</div><div class="spec-value"><?= htmlspecialchars($sp['RAM']) ?></div></div>
            <?php endif; ?>
            
            <?php if(!empty($sp['O_Cung'])): ?>
            <div class="spec-item"><div class="spec-title">Ổ cứng</div><div class="spec-value"><?= htmlspecialchars($sp['O_Cung']) ?></div></div>
            <?php endif; ?>
            
            <?php if(!empty($sp['ManHinh'])): ?>
            <div class="spec-item"><div class="spec-title">Màn hình</div><div class="spec-value"><?= htmlspecialchars($sp['ManHinh']) ?></div></div>
            <?php endif; ?>
            
            <?php if(!empty($sp['BaoHanh'])): ?>
            <div class="spec-item"><div class="spec-title">Bảo hành</div><div class="spec-value"><?= htmlspecialchars($sp['BaoHanh']) ?></div></div>
            <?php endif; ?>
            
            <div class="spec-item"><div class="spec-title">Tình trạng</div><div class="spec-value" style="color:var(--green);">Còn <?= $sp['SoLuongTon'] ?> sản phẩm</div></div>
        </div>

        <div style="display: flex; gap: 15px;">
            <button class="btn-buy" style="flex: 1;" onclick="themVaoGio(<?= $sp['MaSP'] ?>, this)">
                🛒 THÊM VÀO GIỎ HÀNG NGAY
            </button>
            <button class="btn-fav" id="btn-heart" onclick="toggleYeuThich(<?= $sp['MaSP'] ?>)">
                <?= $isFav ? '❤️' : '🤍' ?>
            </button>
        </div>

        <?php if(!empty($sp['MoTa'])): ?>
        <div class="sp-desc">
            <h4 style="color: white; margin-bottom: 5px;">Mô tả sản phẩm:</h4>
            <?= nl2br(htmlspecialchars($sp['MoTa'])) ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function themVaoGio(maSP, buttonElement) {
    if(!buttonElement) return;

    $.ajax({
        url: "them_gio_hang.php",
        type: "POST",
        data: { id_sanpham: maSP },
        success: function(response) {
            $("#so-luong-gio-hang").text(response);
            
            let oldText = buttonElement.innerHTML;
            buttonElement.innerHTML = '✅ ĐÃ THÊM VÀO GIỎ';
            buttonElement.style.background = 'linear-gradient(135deg, #22c55e, #16a34a)'; 
            buttonElement.style.boxShadow = '0 8px 25px rgba(34,197,94,0.6)';
            
            setTimeout(() => { 
                buttonElement.innerHTML = oldText; 
                buttonElement.style.background = ''; 
                buttonElement.style.boxShadow = '';
            }, 1500);
        },
        error: function() {
            alert("Lỗi gửi dữ liệu!");
        }
    });
}
function toggleYeuThich(maSP) {
    $.ajax({
        url: 'xu_ly_yeu_thich.php',
        type: 'POST',
        data: { id_sanpham: maSP },
        success: function(response) {
            let heartBtn = document.getElementById('btn-heart');
            if(response === 'added') {
                heartBtn.innerHTML = '❤️'; // Đổi sang tim đỏ
            } else if(response === 'removed') {
                heartBtn.innerHTML = '🤍'; // Đổi về tim rỗng
            } else {
                alert("Bạn cần đăng nhập để sử dụng tính năng này!");
                window.location.href = "DangNhap.php";
            }
        }
    });
}
</script>

</body>
</html>