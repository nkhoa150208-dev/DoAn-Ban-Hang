<?php
session_start();

// KẾT NỐI DATABASE
$serverName = "localhost\\SQLEXPRESS";
$database   = "QLBanHang";
$connectionInfo = ["Database" => $database, "TrustServerCertificate" => true, "CharacterSet" => "UTF-8"];
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) die(print_r(sqlsrv_errors(), true));

if (!isset($_SESSION['MaND'])) { header('Location: DangNhap.php'); exit; }
$user_id = (int)$_SESSION['MaND'];

// Đếm giỏ hàng để hiện lên icon
$tongGioHang = 0;
if (isset($_SESSION['giohang'])) {
    foreach ($_SESSION['giohang'] as $item) {
        $tongGioHang += $item['SoLuong'];
    }
}

// BẮT CÁC THAM SỐ LỌC TỪ URL (Lấy từ Form)
$tu_khoa = isset($_GET['tukhoa']) ? trim($_GET['tukhoa']) : '';
$danh_muc = isset($_GET['danhmuc']) ? (int)$_GET['danhmuc'] : 0;
$gia_min = isset($_GET['gia_min']) ? (int)$_GET['gia_min'] : '';
$gia_max = isset($_GET['gia_max']) ? (int)$_GET['gia_max'] : '';
$sap_xep = isset($_GET['sap_xep']) ? $_GET['sap_xep'] : 'newest';

// XÂY DỰNG CÂU LỆNH SQL ĐỘNG DỰA THEO BỘ LỌC
$conditions = [];
$params = [];

if ($tu_khoa !== '') {
    $conditions[] = "sp.TenSP LIKE ?";
    $params[] = "%" . $tu_khoa . "%";
}
if ($danh_muc > 0) {
    $conditions[] = "sp.MaDM = ?";
    $params[] = $danh_muc;
}
if ($gia_min !== '') {
    $conditions[] = "sp.Gia >= ?";
    $params[] = $gia_min;
}
if ($gia_max !== '') {
    $conditions[] = "sp.Gia <= ?";
    $params[] = $gia_max;
}

$whereClause = count($conditions) > 0 ? "WHERE " . implode(" AND ", $conditions) : "";

$orderClause = "ORDER BY sp.MaSP DESC"; // Mặc định mới nhất
if ($sap_xep === 'price_asc') $orderClause = "ORDER BY sp.Gia ASC";
if ($sap_xep === 'price_desc') $orderClause = "ORDER BY sp.Gia DESC";

$sql_sp = "SELECT sp.*, 
            ISNULL((SELECT AVG(CAST(SoSao AS FLOAT)) FROM BinhLuan WHERE MaSP = sp.MaSP), 0) AS AvgSao,
            (SELECT COUNT(*) FROM BinhLuan WHERE MaSP = sp.MaSP) AS TotalBL
           FROM SanPham sp 
           $whereClause $orderClause";

$stmt_sp = sqlsrv_query($conn, $sql_sp, $params);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tìm Kiếm Sản Phẩm - KON TechVN</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700;900&family=Exo+2:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="css/trangchu.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
  :root { --navy:#050d1a; --navy2:#071223; --panel:#0d1f38; --panel2:#0f2444; --cyan:#00e5ff; --purple:#7c3aed; --green:#22c55e; --text:#e2eaf5; --muted:#7a92b0; --border:rgba(0,229,255,0.12); }
  body { background: var(--navy); color: var(--text); font-family: 'Exo 2', sans-serif; margin:0; padding-bottom: 50px;}
  
  /* Topbar basic */
  .topbar { display: flex; align-items: center; justify-content: space-between; background: rgba(5,13,26,0.92); border-bottom: 1px solid var(--border); padding: 15px 40px; position: sticky; top: 0; z-index: 100; backdrop-filter: blur(10px); }
  .logo { font-family: 'REVERT', 'Orbitron', sans-serif; font-weight: 900; font-size: 22px; text-decoration: none; }
  .logo span:first-child { color: var(--cyan); } .logo span:last-child { color: var(--text); }
  .btn-back { border: 1.5px solid var(--border); background: var(--panel2); color: var(--muted); padding: 8px 20px; border-radius: 8px; text-decoration: none; font-size: 14px; font-weight: 600; transition: 0.2s; display: flex; align-items: center; gap: 8px; }
  .btn-back:hover { border-color: var(--cyan); color: var(--cyan); box-shadow: 0 0 10px rgba(0,229,255,0.2); }

  /* Layout Tìm Kiếm */
  .search-container { max-width: 1200px; margin: 40px auto; display: grid; grid-template-columns: 280px 1fr; gap: 30px; padding: 0 20px; }
  @media (max-width: 800px) { .search-container { grid-template-columns: 1fr; } }
  
  /* Cột Lọc Bến Trái */
  .filter-box { background: var(--panel); border: 1px solid var(--border); border-radius: 12px; padding: 20px; height: fit-content; position: sticky; top: 100px; }
  .fb-title { font-family: 'Orbitron', sans-serif; font-size: 16px; color: var(--cyan); margin-bottom: 20px; border-bottom: 1px dashed var(--border); padding-bottom: 10px; }
  .fi-group { margin-bottom: 18px; }
  .fi-group label { display: block; font-size: 12px; font-weight: bold; color: var(--text); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 1px; }
  .fi-group input, .fi-group select { width: 100%; background: var(--panel2); border: 1px solid var(--border); color: var(--text); padding: 10px; border-radius: 8px; font-family: 'Exo 2', sans-serif; outline: none; transition: 0.2s; }
  .fi-group input:focus, .fi-group select:focus { border-color: var(--cyan); }
  .btn-loc { background: linear-gradient(135deg, var(--cyan), #0088cc); color: #000; font-weight: bold; border: none; width: 100%; padding: 12px; border-radius: 8px; font-size: 15px; cursor: pointer; text-transform: uppercase; transition: 0.3s; margin-top: 10px; }
  .btn-loc:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,229,255,0.3); }

  /* Kết quả bên phải */
  .result-title { font-size: 20px; font-weight: 400; margin-bottom: 20px; }
  .result-title strong { color: var(--cyan); }
  .products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; }
  
  /* Thẻ Sản Phẩm (Dùng lại CSS Trang Chủ) */
  .product-card { background: var(--panel); border: 1px solid var(--border); border-radius: 16px; overflow: hidden; transition: all 0.35s; position: relative; display: flex; flex-direction: column; }
  .product-card:hover { transform: translateY(-6px); box-shadow: 0 10px 30px rgba(0,0,0,0.4), 0 0 20px rgba(0,229,255,0.15); border-color: var(--cyan); }
  .product-img-wrap { height: 180px; display: flex; align-items: center; justify-content: center; background: var(--panel2); position: relative; overflow: hidden; }
  .product-img-wrap img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s; }
  .product-card:hover .product-img-wrap img { transform: scale(1.08); }
  .product-img { font-size: 60px; }
  .product-info { padding: 15px; display: flex; flex-direction: column; flex: 1; }
  .product-cat { font-size: 10px; font-weight: 600; color: var(--cyan); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; }
  .product-name { font-size: 14px; font-weight: 600; color: var(--text); margin-bottom: 5px; line-height: 1.3; }
  .product-specs { font-size: 11px; color: var(--muted); margin-bottom: auto; }
  .product-price-row { margin-top: 10px; margin-bottom: 10px; }
  .product-price { font-family: 'Orbitron', monospace; font-size: 16px; font-weight: 700; color: var(--cyan); }
  .product-actions { display: flex; gap: 8px; margin-top: 10px; }
  .btn-add { flex: 1; background: var(--green); color: #fff; border: none; border-radius: 8px; padding: 8px; font-family: 'Exo 2', sans-serif; font-size: 12px; font-weight: bold; cursor: pointer; transition: 0.2s; }
  .btn-add:hover { background: var(--green2); }
  .btn-detail { background: var(--panel2); color: var(--muted); border: 1px solid var(--border); border-radius: 8px; padding: 8px 12px; font-family: 'Exo 2', sans-serif; font-size: 12px; font-weight: 500; cursor: pointer; text-decoration: none; text-align: center; transition: 0.2s; }
  .btn-detail:hover { border-color: var(--cyan); color: var(--cyan); }
</style>
</head>
<body>

<div class="topbar">
    <a class="logo" href="TrangChuDaDangNhap.php"><span>KON</span><span> TechVN </span></a>
    
    <div style="display: flex; gap: 15px;">
        <a href="ChiTietGioHang.php" class="btn-back" style="border-color: var(--green); color: var(--green);">
            🛒 Giỏ hàng (<span id="so-luong-gio-hang"><?= $tongGioHang ?></span>)
        </a>
        <a href="TrangChuDaDangNhap.php" class="btn-back">← Quay lại Trang Chủ</a>
    </div>
</div>

<div class="search-container">
    <div class="filter-box">
        <div class="fb-title">⚙️ BỘ LỌC TÌM KIẾM</div>
        <form method="GET" action="TimKiem.php">
            <div class="fi-group">
                <label>Từ khóa</label>
                <input type="text" name="tukhoa" value="<?= htmlspecialchars($tu_khoa) ?>" placeholder="Nhập tên sản phẩm...">
            </div>
            <div class="fi-group">
                <label>Danh mục</label>
                <select name="danhmuc">
                    <option value="0">-- Tất cả danh mục --</option>
                    <option value="1" <?= $danh_muc==1 ? 'selected' : '' ?>>Laptop</option>
                    <option value="2" <?= $danh_muc==2 ? 'selected' : '' ?>>Điện thoại</option>
                    <option value="3" <?= $danh_muc==3 ? 'selected' : '' ?>>PC Gaming</option>
                    <option value="4" <?= $danh_muc==4 ? 'selected' : '' ?>>Phụ kiện</option>
                    <option value="5" <?= $danh_muc==5 ? 'selected' : '' ?>>Gaming Gear</option>
                </select>
            </div>
            <div class="fi-group">
                <label>Mức giá (VNĐ)</label>
                <div style="display:flex; gap:10px;">
                    <input type="number" name="gia_min" value="<?= $gia_min ?>" placeholder="Từ..." min="0">
                    <input type="number" name="gia_max" value="<?= $gia_max ?>" placeholder="Đến..." min="0">
                </div>
            </div>
            <div class="fi-group">
                <label>Sắp xếp theo</label>
                <select name="sap_xep">
                    <option value="newest" <?= $sap_xep=='newest' ? 'selected' : '' ?>>Mới nhất</option>
                    <option value="price_asc" <?= $sap_xep=='price_asc' ? 'selected' : '' ?>>Giá: Thấp đến Cao</option>
                    <option value="price_desc" <?= $sap_xep=='price_desc' ? 'selected' : '' ?>>Giá: Cao xuống Thấp</option>
                </select>
            </div>
            <button type="submit" class="btn-loc">🔍 Lọc Kết Quả</button>
            <a href="TimKiem.php" style="display:block; text-align:center; color:var(--muted); font-size:12px; margin-top:15px; text-decoration:none;">Xóa bộ lọc</a>
        </form>
    </div>

    <div>
        <div class="result-title">
            KẾT QUẢ TÌM KIẾM 
            <?php if($tu_khoa !== '') echo 'CHO: <strong>"'.htmlspecialchars($tu_khoa).'"</strong>'; ?>
        </div>

        <div class="products-grid">
            <?php
            $hasProducts = false;
            if ($stmt_sp) {
                while($row = sqlsrv_fetch_array($stmt_sp, SQLSRV_FETCH_ASSOC)) {
                    $hasProducts = true;
                    $specs = implode(' · ', array_filter([$row['CPU'], $row['RAM'], $row['O_Cung']]));
            ?>
            <div class="product-card">
                <div class="product-img-wrap">
                    <?php if (!empty($row['HinhAnh'])): ?>
                        <img src="<?= htmlspecialchars($row['HinhAnh']) ?>" alt="Ảnh">
                    <?php else: ?>
                        <div class="product-img">
                            <?= ($row['MaDM']==1||$row['MaDM']==3) ? '💻' : (($row['MaDM']==2)?'📱':'📦') ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="product-info">
                    <div class="product-cat">Danh mục ID: <?= $row['MaDM'] ?></div>
                    <div class="product-name"><?= htmlspecialchars($row['TenSP']) ?></div>
                    
                    <div style="font-size: 11px; margin-bottom: 8px;">
                        <?php if($row['TotalBL'] > 0): ?>
                            <span style="color: #fbbf24;">★ <?= number_format($row['AvgSao'], 1) ?></span>
                            <span style="color: var(--muted); margin-left: 4px;">(<?= $row['TotalBL'] ?>)</span>
                        <?php else: ?>
                            <span style="color: var(--muted);">Chưa có đánh giá</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-specs">
                        <?= htmlspecialchars($specs) ?><br>
                        <?php if($row['SoLuongTon'] > 0): ?>
                            <span style="color: var(--green); font-weight: bold; font-size: 11px;">✅ Còn: <?= $row['SoLuongTon'] ?></span>
                        <?php else: ?>
                            <span style="color: #ef4444; font-weight: bold; font-size: 11px;">❌ Hết hàng</span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-price-row">
                        <div class="product-price"><?= number_format($row['Gia'], 0, ',', '.') ?>đ</div>
                    </div>
                    
                    <div class="product-actions">
                        <?php if($row['SoLuongTon'] > 0): ?>
                            <button class="btn-add" onclick="themVaoGio(<?= $row['MaSP'] ?>, this)">🛒 Thêm</button>
                        <?php else: ?>
                            <button class="btn-add" style="background: #475569; opacity:0.6; cursor:not-allowed;" onclick="alert('Đã hết hàng!');">🚫 Hết</button>
                        <?php endif; ?>
                        <a href="ChiTietSanPham.php?id=<?= $row['MaSP'] ?>" class="btn-detail">Chi tiết</a>
                    </div>
                </div>
            </div>
            <?php 
                } 
            }
            if (!$hasProducts) {
                echo '<div style="grid-column: 1/-1; text-align: center; padding: 50px; background: var(--panel); border: 1px dashed var(--border); border-radius: 12px; color: var(--muted);">
                        <div style="font-size: 50px; margin-bottom: 15px;">🔍</div>
                        <h3>Không tìm thấy sản phẩm nào!</h3>
                        <p>Vui lòng thử lại với từ khóa khác hoặc bỏ bớt các bộ lọc.</p>
                      </div>';
            }
            ?>
        </div>
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
            if (response.trim() === "VUOT_QUY_DINH") {
                alert("⚠️ SỐ LƯỢNG ĐẠT GIỚI HẠN!\nKho không đủ sản phẩm.");
                return;
            }

            let soLuongMoi = parseInt(response);
            let badge = $("#so-luong-gio-hang");
            
            if (soLuongMoi > 0) {
                badge.text(soLuongMoi);
            }

            let oldText = buttonElement.innerHTML;
            buttonElement.innerHTML = '✓ Đã thêm!';
            let originalBg = buttonElement.style.background;
            buttonElement.style.background = '#059669'; 
            
            setTimeout(() => { 
                buttonElement.innerHTML = oldText; 
                buttonElement.style.background = originalBg; 
            }, 1500);
        },
        error: function() {
            alert("Lỗi kết nối máy chủ!");
        }
    });
}
</script>
</body>
</html>