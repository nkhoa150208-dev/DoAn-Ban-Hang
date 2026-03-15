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
if (!isset($_GET['id'])) { header('Location: TrangChu.php'); exit; }
$maSP = (int)$_GET['id'];

// --- XỬ LÝ GỬI BÌNH LUẬN & ĐÁNH GIÁ SAO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_comment') {
    if (isset($_SESSION['MaND'])) {
        $noidung = trim($_POST['noidung'] ?? '');
        $sosao = (int)($_POST['sosao'] ?? 5); // Nhận số sao, mặc định là 5
        if (!empty($noidung)) {
            $sql_bl = "INSERT INTO BinhLuan (MaSP, MaND, NoiDung, SoSao) VALUES (?, ?, ?, ?)";
            sqlsrv_query($conn, $sql_bl, [$maSP, $_SESSION['MaND'], $noidung, $sosao]);
            header("Location: ChiTietSanPham.php?id=" . $maSP); exit;
        }
    }
}

// 3. TRUY VẤN THÔNG TIN SẢN PHẨM
$sql = "SELECT sp.*, dm.TenDM FROM SanPham sp LEFT JOIN DanhMuc dm ON sp.MaDM = dm.MaDM WHERE sp.MaSP = ?";
$stmt = sqlsrv_query($conn, $sql, [$maSP]);
$sp = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

if (!$sp) die("<h2 style='color:white; text-align:center; padding:50px;'>Sản phẩm không tồn tại!</h2>");

$isFav = false;
if (isset($_SESSION['MaND'])) {
    $checkFav = sqlsrv_query($conn, "SELECT 1 FROM YeuThich WHERE MaND=? AND MaSP=?", [$_SESSION['MaND'], $maSP]);
    if ($checkFav && sqlsrv_has_rows($checkFav)) $isFav = true;
}

$linkTrangChu = isset($_SESSION['MaND']) ? "TrangChuDaDangNhap.php" : "TrangChu.php";
$tongGioHang = 0;
if (isset($_SESSION['giohang'])) foreach ($_SESSION['giohang'] as $item) $tongGioHang += $item['SoLuong'];

// 4. LẤY DANH SÁCH BÌNH LUẬN VÀ TÍNH TRUNG BÌNH SAO
$sql_get_bl = "SELECT bl.*, nd.HoTen, nd.Avatar FROM BinhLuan bl JOIN NguoiDung nd ON bl.MaND = nd.MaND WHERE bl.MaSP = ? ORDER BY bl.NgayBL DESC";
$stmt_bl = sqlsrv_query($conn, $sql_get_bl, [$maSP]);

// Tính trung bình
$sql_avg = "SELECT ISNULL(AVG(CAST(SoSao AS FLOAT)), 0) as AvgSao, COUNT(MaBL) as TotalBL FROM BinhLuan WHERE MaSP = ?";
$stmt_avg = sqlsrv_query($conn, $sql_avg, [$maSP]);
$avgData = sqlsrv_fetch_array($stmt_avg, SQLSRV_FETCH_ASSOC);
$avgSao = round($avgData['AvgSao'], 1);
$totalBL = $avgData['TotalBL'];
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
        :root { --navy: #050d1a; --panel: #0d1f38; --panel2: #0f2444; --cyan: #00e5ff; --purple2: #a855f7; --green: #22c55e; --tx: #e2eaf5; --muted: #7a92b0; --border: rgba(0,229,255,0.15); --star: #fbbf24; }
        body { font-family: 'Exo 2', sans-serif; background: var(--navy); color: var(--tx); margin: 0; padding: 20px; padding-bottom: 60px; }
        .topbar { max-width: 1000px; margin: 0 auto 30px; display: flex; align-items: center; justify-content: space-between; background: rgba(5,13,26,0.9); padding: 15px 20px; border-bottom: 1px solid var(--border); border-radius: 12px; }
        .logo { font-family: 'Orbitron', sans-serif; font-size: 20px; font-weight: 900; color: var(--cyan); text-decoration: none; }
        .nav-actions { display: flex; gap: 15px; }
        .btn-outline { padding: 8px 15px; border: 1px solid var(--cyan); color: var(--cyan); border-radius: 8px; text-decoration: none; font-weight: bold; transition: 0.2s; display: flex; align-items: center;}
        .btn-outline:hover { background: rgba(0,229,255,0.1); }
        .cart-badge { background: var(--cyan); color: var(--navy); padding: 2px 6px; border-radius: 50%; font-size: 11px; margin-left: 5px; font-weight: bold;}
        .sp-container { max-width: 1000px; margin: 0 auto; display: grid; grid-template-columns: 1fr 1fr; gap: 40px; background: var(--panel); border: 1px solid var(--border); border-radius: 16px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        @media (max-width: 768px) { .sp-container { grid-template-columns: 1fr; } }
        .btn-fav { width: 60px; font-size: 26px; background: var(--panel2); border: 1px solid var(--border); border-radius: 10px; cursor: pointer; transition: 0.3s; display: flex; align-items: center; justify-content: center; color: #ef4444; }
        .btn-fav:hover { border-color: #ef4444; background: rgba(239,68,68,0.1); }
        .sp-img-box { background: linear-gradient(135deg, var(--panel2), var(--navy)); border-radius: 16px; display: flex; align-items: center; justify-content: center; height: 400px; font-size: 120px; border: 1px solid var(--border); filter: drop-shadow(0 0 20px rgba(0,229,255,0.1)); overflow: hidden; box-shadow: 0 0 40px rgb(4 0 61); }
        .sp-img-box img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease; }
        .sp-img-box:hover img { transform: scale(1.05); }        
        .sp-cat { color: var(--cyan); font-size: 12px; text-transform: uppercase; font-weight: bold; letter-spacing: 2px; margin-bottom: 10px; }
        .sp-name { font-family: 'Orbitron', sans-serif; font-size: 28px; line-height: 1.2; margin: 0 0 5px 0; color: #fff; }
        .sp-price { font-family: 'Orbitron', sans-serif; font-size: 32px; font-weight: bold; color: var(--green); margin-bottom: 25px; margin-top: 10px;}
        .sp-specs { background: var(--panel2); border-radius: 12px; padding: 20px; margin-bottom: 25px; border: 1px solid var(--border); }
        .spec-item { display: flex; border-bottom: 1px dashed rgba(255,255,255,0.1); padding: 10px 0; font-size: 14px; }
        .spec-item:last-child { border-bottom: none; }
        .spec-title { width: 120px; color: var(--muted); font-weight: bold; }
        .spec-value { flex: 1; color: var(--tx); font-weight: 500; }
        .btn-buy { width: 100%; padding: 16px; font-size: 16px; font-weight: bold; text-transform: uppercase; background: linear-gradient(135deg, var(--purple2), #7c3aed); color: white; border: none; border-radius: 10px; cursor: pointer; transition: 0.3s; font-family: 'Exo 2', sans-serif; box-shadow: 0 4px 15px rgba(168,85,247,0.4); }
        .btn-buy:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(168,85,247,0.6); }
        .sp-desc { margin-top: 30px; font-size: 15px; color: var(--muted); line-height: 1.6; }
        
        /* Đánh giá & Bình luận CSS */
        .rating-summary { font-size: 14px; color: var(--muted); display: flex; align-items: center; gap: 8px; margin-bottom: 15px;}
        .star-box { color: var(--star); font-size: 18px; }
        .comments-section { max-width: 1000px; margin: 30px auto 0; background: var(--panel); border: 1px solid var(--border); border-radius: 16px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .comments-section h3 { color: var(--cyan); margin-top: 0; margin-bottom: 20px; font-family: 'Orbitron', sans-serif; font-size: 18px; border-bottom: 1px solid var(--border); padding-bottom: 15px;}
        
        /* Star Rating UI */
        .rating-input { display: flex; flex-direction: row-reverse; justify-content: flex-end; gap: 5px; margin-bottom: 10px; }
        .rating-input input { display: none; }
        .rating-input label { font-size: 26px; color: var(--muted); cursor: pointer; transition: 0.2s; }
        .rating-input input:checked ~ label, .rating-input label:hover, .rating-input label:hover ~ label { color: var(--star); }
        
        .comment-form { display: flex; flex-direction: column; align-items: flex-end; margin-bottom: 30px; background: var(--panel2); padding: 15px; border-radius: 12px; border: 1px solid var(--border);}
        .comment-form textarea { width: 97%; background: var(--navy); border: 1px solid var(--border); border-radius: 8px; color: var(--tx); padding: 15px; font-family: 'Exo 2', sans-serif; min-height: 80px; resize: vertical; margin-bottom: 15px; outline: none; transition: 0.2s;}
        .comment-form textarea:focus { border-color: var(--cyan); box-shadow: 0 0 0 3px rgba(0,229,255,0.1);}
        .comment-list { display: flex; flex-direction: column; gap: 15px; }
        .comment-item { display: flex; gap: 15px; background: rgba(0,229,255,0.03); padding: 15px; border-radius: 12px; border: 1px solid rgba(0,229,255,0.1); transition: 0.2s;}
        .cmt-avatar { width: 45px; height: 45px; border-radius: 50%; object-fit: cover; border: 2px solid var(--cyan); }
        .cmt-content { flex: 1; }
        .cmt-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
        .cmt-name { font-weight: bold; color: var(--tx); font-size: 14px;}
        .cmt-date { font-size: 11px; color: var(--muted); }
        .cmt-text { font-size: 14px; color: var(--muted); line-height: 1.5; margin-top: 5px; }
        .logo{
      --navy: #050d1a;
    --navy2: #071223;
    --navy3: #0a1a30;
    --panel: #0d1f38;
    --panel2: #0f2444;
    --cyan: #00e5ff;
    --cyan2: #00b8d4;
    --purple: #7c3aed;
    --purple2: #a855f7;
    --green: #22c55e;
    --green2: #16a34a;
    --text: #e2eaf5;
    --muted: #7a92b0;
    --border: rgba(0, 229, 255, 0.12);
    --glow-cyan: 0 0 20px rgba(0, 229, 255, 0.4);
    --glow-purple: 0 0 20px rgba(168, 85, 247, 0.4);
        font-family: 'REVERT';
    font-weight: 900;
    font-size: 20px;
    letter-spacing: 0.05em;
    text-decoration: none;
    margin-right: 16px;

}.logo span:first-child { color: var(--cyan); }
  .logo span:last-child { color: var(--text); }
    </style>
</head>
<body>

<div class="topbar">
  <a class="logo" href="#"><span>KON</span><span> TechVN </span></a>
      <div class="nav-actions">
        <?php if(isset($_SESSION['MaND'])): ?>
            <a href="ChiTietGioHang.php" class="btn-outline">🛒 Giỏ hàng <span class="cart-badge" id="so-luong-gio-hang"><?= $tongGioHang ?></span></a>
        <?php else: ?>
            <a href="javascript:void(0)" onclick="yeuCauDangNhap()" class="btn-outline">🛒 Giỏ hàng <span class="cart-badge" id="so-luong-gio-hang">0</span></a>
        <?php endif; ?>
        <a href="<?= $linkTrangChu ?>" class="btn-outline">← Trở về</a>
    </div>
</div>

<div class="sp-container">
    <div class="sp-img-box">
        <?php 
        if(!empty($sp['HinhAnh'])) echo "<img src='".htmlspecialchars($sp['HinhAnh'])."' alt='Ảnh'>";
        else echo ($sp['MaDM'] == 1 || $sp['MaDM'] == 3) ? '💻' : (($sp['MaDM'] == 2) ? '📱' : '📦'); 
        ?>
    </div>

    <div>
        <div class="sp-cat">Danh mục: <?= htmlspecialchars($sp['TenDM'] ?? 'Chưa phân loại') ?></div>
        <h1 class="sp-name"><?= htmlspecialchars($sp['TenSP']) ?></h1>
        
        <div class="rating-summary">
            <?php if($totalBL > 0): ?>
                <span class="star-box">★</span>
                <strong style="color: var(--tx);"><?= $avgSao ?></strong> 
                <span>(<?= $totalBL ?> đánh giá)</span>
            <?php else: ?>
                <span>Chưa có đánh giá</span>
            <?php endif; ?>
        </div>

        <div class="sp-price"><?= number_format($sp['Gia'], 0, ',', '.') ?> đ</div>
        
        <div class="sp-specs">
            <h3 style="margin-top:0; color: var(--cyan); font-size: 16px; margin-bottom: 15px;">Thông số kỹ thuật</h3>
            <?php if(!empty($sp['CPU'])): ?><div class="spec-item"><div class="spec-title">CPU</div><div class="spec-value"><?= htmlspecialchars($sp['CPU']) ?></div></div><?php endif; ?>
            <?php if(!empty($sp['RAM'])): ?><div class="spec-item"><div class="spec-title">RAM</div><div class="spec-value"><?= htmlspecialchars($sp['RAM']) ?></div></div><?php endif; ?>
            <?php if(!empty($sp['O_Cung'])): ?><div class="spec-item"><div class="spec-title">Ổ cứng</div><div class="spec-value"><?= htmlspecialchars($sp['O_Cung']) ?></div></div><?php endif; ?>
            <?php if(!empty($sp['ManHinh'])): ?><div class="spec-item"><div class="spec-title">Màn hình</div><div class="spec-value"><?= htmlspecialchars($sp['ManHinh']) ?></div></div><?php endif; ?>
            <?php if(!empty($sp['BaoHanh'])): ?><div class="spec-item"><div class="spec-title">Bảo hành</div><div class="spec-value"><?= htmlspecialchars($sp['BaoHanh']) ?></div></div><?php endif; ?>
            <div class="spec-item"><div class="spec-title">Tình trạng</div><div class="spec-value" style="color:var(--green); font-weight:bold;">Còn <?= $sp['SoLuongTon'] ?> sản phẩm</div></div>
        </div>

        <div style="display: flex; gap: 15px;">
            <?php if(isset($_SESSION['MaND'])): ?>
                <?php if($sp['SoLuongTon'] > 0): ?>
                    <button class="btn-buy" style="flex: 1;" onclick="themVaoGio(<?= $sp['MaSP'] ?>, this)">🛒 THÊM VÀO GIỎ NGAY</button>
                <?php else: ?>
                    <button class="btn-buy" style="flex: 1; background: #475569; box-shadow: none; cursor: not-allowed;" onclick="alert('Hết hàng!');">🚫 ĐÃ HẾT HÀNG</button>
                <?php endif; ?>
                <button class="btn-fav" id="btn-heart" onclick="toggleYeuThich(<?= $sp['MaSP'] ?>)"><?= $isFav ? '❤️' : '🤍' ?></button>
            <?php else: ?>
                <button class="btn-buy" style="flex: 1;" onclick="yeuCauDangNhap()">🛒 THÊM VÀO GIỎ NGAY</button>
                <button class="btn-fav" onclick="yeuCauDangNhap()">🤍</button>
            <?php endif; ?>
        </div>

        <?php if(!empty($sp['MoTa'])): ?>
        <div class="sp-desc">
            <h4 style="color: white; margin-bottom: 5px;">Mô tả sản phẩm:</h4>
            <?= nl2br(htmlspecialchars($sp['MoTa'])) ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="comments-section">
    <h3>💬 Đánh giá & Bình luận (<?= $totalBL ?>)</h3>
    
    <?php if(isset($_SESSION['MaND'])): ?>
    <form method="post" class="comment-form">
        <input type="hidden" name="action" value="add_comment">
        
        <div style="width: 100%; display: flex; align-items: center; gap: 10px;">
            <span style="font-weight: bold; color: var(--tx); font-size: 13px;">Chất lượng sản phẩm:</span>
            <div class="rating-input">
                <input type="radio" name="sosao" id="star5" value="5" checked><label for="star5" title="Tuyệt vời">★</label>
                <input type="radio" name="sosao" id="star4" value="4"><label for="star4" title="Tốt">★</label>
                <input type="radio" name="sosao" id="star3" value="3"><label for="star3" title="Bình thường">★</label>
                <input type="radio" name="sosao" id="star2" value="2"><label for="star2" title="Tệ">★</label>
                <input type="radio" name="sosao" id="star1" value="1"><label for="star1" title="Rất tệ">★</label>
            </div>
        </div>

        <textarea name="noidung" placeholder="Chia sẻ cảm nhận, đánh giá của bạn về sản phẩm này..." required></textarea>
        <button type="submit" class="btn-buy" style="width: auto; padding: 10px 25px; font-size: 13px; margin: 0; display: inline-block;">Gửi đánh giá ✈</button>
    </form>
    <?php else: ?>
    <div style="background: rgba(0,229,255,0.05); padding: 20px; border-radius: 12px; text-align: center; margin-bottom: 30px; border: 1px dashed var(--border);">
        <p style="color: var(--muted); margin: 0 0 10px 0;">Bạn cần đăng nhập để có thể tham gia đánh giá sản phẩm.</p>
        <a href="DangNhap.php" class="btn-buy" style="display: inline-block; width: auto; padding: 8px 20px; text-decoration: none; font-size: 13px;">Đăng Nhập Ngay</a>
    </div>
    <?php endif; ?>

    <div class="comment-list">
        <?php 
        $hasComment = false;
        if ($stmt_bl) {
            while($bl = sqlsrv_fetch_array($stmt_bl, SQLSRV_FETCH_ASSOC)):
                $hasComment = true;
                $avt = (!empty($bl['Avatar']) && file_exists($bl['Avatar'])) ? $bl['Avatar'] : 'https://ui-avatars.com/api/?name='.urlencode($bl['HoTen']).'&background=00e5ff&color=050d1a&bold=true';
        ?>
        <div class="comment-item">
            <img src="<?= $avt ?>" alt="Avatar" class="cmt-avatar">
            <div class="cmt-content">
                <div class="cmt-header">
                    <span class="cmt-name"><?= htmlspecialchars($bl['HoTen']) ?></span>
                    <span class="cmt-date"><?= $bl['NgayBL']->format('H:i - d/m/Y') ?></span>
                </div>
                <div style="color: var(--star); font-size: 14px; margin-bottom: 5px;">
                    <?= str_repeat('★', $bl['SoSao']) ?><span style="color: var(--muted);"><?= str_repeat('★', 5 - $bl['SoSao']) ?></span>
                </div>
                <div class="cmt-text"><?= nl2br(htmlspecialchars($bl['NoiDung'])) ?></div>
            </div>
        </div>
        <?php endwhile; } ?>
        
        <?php if(!$hasComment): ?>
            <div style="text-align: center; color: var(--muted); padding: 40px 20px; background: var(--panel2); border-radius: 12px;">
                <div style="font-size: 40px; margin-bottom: 10px;">🍃</div>
                Chưa có đánh giá nào.<br>Hãy là người đầu tiên đánh giá sản phẩm này nhé!
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function yeuCauDangNhap() { alert("Vui lòng đăng nhập để sử dụng tính năng này!"); window.location.href = "DangNhap.php"; }
function themVaoGio(maSP, buttonElement) {
    if(!buttonElement) return;
    $.ajax({
        url: "them_gio_hang.php", type: "POST", data: { id_sanpham: maSP },
        success: function(response) {
            if (response.trim() === "VUOT_QUY_DINH") { alert("⚠️ KHO KHÔNG ĐỦ SẢN PHẨM!"); return; }
            $("#so-luong-gio-hang").text(response);
            let oldText = buttonElement.innerHTML;
            buttonElement.innerHTML = '✅ ĐÃ THÊM VÀO GIỎ';
            buttonElement.style.background = 'linear-gradient(135deg, #22c55e, #16a34a)'; 
            setTimeout(() => { buttonElement.innerHTML = oldText; buttonElement.style.background = ''; }, 1500);
        }
    });
}
function toggleYeuThich(maSP) {
    $.ajax({ url: 'xu_ly_yeu_thich.php', type: 'POST', data: { id_sanpham: maSP }, success: function(res) {
        let heartBtn = document.getElementById('btn-heart');
        if(res === 'added') heartBtn.innerHTML = '❤️'; else if(res === 'removed') heartBtn.innerHTML = '🤍';
    }});
}
</script>
</body>
</html>