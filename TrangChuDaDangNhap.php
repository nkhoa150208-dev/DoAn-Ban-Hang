

<?php
session_start();

if (!isset($_SESSION['MaND'])) { header('Location: DangNhap.php'); exit; }
$user_id = (int)$_SESSION['MaND'];

$uploadPath = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'avatars';
if (!file_exists($uploadPath)) {
    mkdir($uploadPath, 0777, true);
}
define('UPLOAD_DIR', $uploadPath . DIRECTORY_SEPARATOR);

// Đếm tổng giỏ hàng để hiển thị ban đầu
// Đếm tổng giỏ hàng để hiển thị ban đầu
$tongGioHang = 0;
if (isset($_SESSION['giohang'])) {
    foreach ($_SESSION['giohang'] as $item) {
        $tongGioHang += $item['SoLuong'];
    }
}

// ==== THÊM ĐOẠN NÀY VÀO NGAY BÊN DƯỚI ====
$serverName = "localhost\\SQLEXPRESS";
$database   = "QLBanHang";
$connectionInfo = ["Database" => $database, "TrustServerCertificate" => true, "CharacterSet" => "UTF-8"];
$conn = sqlsrv_connect($serverName, $connectionInfo);

// Lấy ngẫu nhiên 12 sản phẩm để làm hiệu ứng xoay vòng
$sql_hero = "SELECT TOP 40 MaSP, TenSP, Gia, HinhAnh, MaDM FROM SanPham ORDER BY NEWID()"; 
$stmt_hero = sqlsrv_query($conn, $sql_hero);
$hero_products = [];
if ($stmt_hero) {
    while ($row = sqlsrv_fetch_array($stmt_hero, SQLSRV_FETCH_ASSOC)) {
        $hero_products[] = $row;
    }
}
// ==========================================

?>
<!DOCTYPE html>
<html lang="vi">

<head>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>KhoaOngNghiem TechVN</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700;900&family=Exo+2:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="css/trangchu.css" rel="stylesheet">
</head>
<style>
  :root {
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
    --border: rgba(0,229,255,0.12);
    --glow-cyan: 0 0 20px rgba(0,229,255,0.4);
    --glow-purple: 0 0 20px rgba(168,85,247,0.4);
  }

  * { margin: 0; padding: 0; box-sizing: border-box; }

  html { scroll-behavior: smooth; }

  body {
    background: var(--navy);
    color: var(--text);
    font-family: 'Exo 2', sans-serif;
    overflow-x: hidden;
  }












/* Bổ sung CSS cho thanh tìm kiếm */
  .nav-search { position: relative; } /* Giữ khung tìm kiếm làm gốc */
  .search-dropdown {
    position: absolute; top: 100%; left: 0; width: 100%;
    background: var(--panel); border: 1px solid var(--border); border-radius: 8px;
    margin-top: 8px; box-shadow: 0 5px 20px rgba(0,0,0,0.6);
    display: none; /* Ẩn đi khi chưa gõ chữ */
    max-height: 350px; overflow-y: auto; z-index: 9999;
  }
  .search-item {
    padding: 10px 15px; border-bottom: 1px solid rgba(0,229,255,0.1);
    display: flex; align-items: center; gap: 12px;
    text-decoration: none; color: var(--text); cursor: pointer; transition: 0.2s;
  }
  .search-item:last-child { border-bottom: none; }
  .search-item:hover { background: rgba(0,229,255,0.1); color: var(--cyan); }
  .search-item-icon { font-size: 24px; }
  .search-item-info { display: flex; flex-direction: column; }
  .search-item-name { font-size: 13px; font-weight: 600; }
  .search-item-price { font-size: 12px; color: var(--purple2); font-family: 'Orbitron', monospace; font-weight: bold; margin-top: 3px; }
  .search-empty { padding: 15px; text-align: center; color: var(--muted); font-size: 13px; }




  /* ── SCROLLBAR ── */
  ::-webkit-scrollbar { width: 6px; }
  ::-webkit-scrollbar-track { background: var(--navy2); }
  ::-webkit-scrollbar-thumb { background: var(--cyan2); border-radius: 3px; }

  /* ── NAV ── */
  nav {
    position: sticky; top: 0; z-index: 100;
    background: rgba(5,13,26,0.92);
    backdrop-filter: blur(20px);
    border-bottom: 1px solid var(--border);
    padding: 0 40px;
    display: flex; align-items: center; gap: 32px;
    height: 64px;
  }

  .logo {
    font-family: 'REVERT';
    font-weight: 900; font-size: 20px;
    letter-spacing: 0.05em;
    text-decoration: none;
    margin-right: 16px;
  }
  .logo span:first-child { color: var(--cyan); }
  .logo span:last-child { color: var(--text); }

  .nav-links { display: flex; gap: 4px; flex: 1; }
  .nav-links a {
    color: var(--muted); text-decoration: none;
    padding: 8px 14px; border-radius: 6px;
    font-size: 14px; font-weight: 500;
    transition: all 0.2s;
    position: relative;
  }
  .nav-links a:hover { color: var(--cyan); background: rgba(0,229,255,0.07); }
  .nav-links a.active { color: var(--cyan); }
  .nav-links a.active::after {
    content: ''; position: absolute; bottom: 4px; left: 14px; right: 14px;
    height: 2px; background: var(--cyan); border-radius: 1px;
    box-shadow: var(--glow-cyan);
  }

  .nav-search {
    display: flex; align-items: center; gap: 8px;
    background: var(--panel); border: 1px solid var(--border);
    border-radius: 8px; padding: 8px 14px; flex: 1; max-width: 320px;
    transition: border-color 0.2s;
  }
  .nav-search:focus-within { border-color: var(--cyan); box-shadow: 0 0 0 3px rgba(0,229,255,0.1); }
  .nav-search svg { color: var(--muted); flex-shrink: 0; }
  .nav-search input {
    background: none; border: none; outline: none;
    color: var(--text); font-family: 'Exo 2', sans-serif;
    font-size: 13px; width: 100%;
  }
  .nav-search input::placeholder { color: var(--muted); }

  .nav-actions { display: flex; align-items: center; gap: 10px; }

  .btn-cart {
    display: flex; align-items: center; gap: 8px;
    background: var(--panel); border: 1px solid var(--border);
    color: var(--text); padding: 8px 16px; border-radius: 8px;
    font-family: 'Exo 2', sans-serif; font-size: 13px; cursor: pointer;
    transition: all 0.2s; position: relative;
  }
  .btn-cart:hover { border-color: var(--cyan); color: var(--cyan); }
  .cart-badge {
    background: var(--cyan); 
    color: var(--navy);
    width: 18px; 
    height: 18px; 
    border-radius: 50%;
    font-size: 10px; 
    font-weight: 700;
    /* display: flex;  <-- Bỏ dòng này hoặc thay bằng đoạn dưới */
    display: none;    /* Mặc định ẩn đi */
    align-items: center; 
    justify-content: center;
}

  .btn-login {
    background: var(--purple); color: #fff;
    padding: 8px 20px; border-radius: 8px;
    border: none; font-family: 'Exo 2', sans-serif;
    font-size: 13px; font-weight: 600; cursor: pointer;
    transition: all 0.2s; white-space: nowrap;
  }
  .btn-login:hover { background: var(--purple2); box-shadow: var(--glow-purple); }

  /* ── HERO ── */
  .hero {
    position: relative; overflow: hidden;
    min-height: 520px;
    background: linear-gradient(135deg, #050d1a 0%, #0a1533 40%, #0d1a40 70%, #08102e 100%);
    display: flex; align-items: center;
  }

  /* Star field */
  .hero-bg {
    position: absolute; inset: 0; overflow: hidden;
  }
  .star {
    position: absolute; border-radius: 50%;
    background: white; animation: twinkle var(--dur) ease-in-out infinite;
  }

  @keyframes twinkle {
    0%,100% { opacity: var(--minO); transform: scale(1); }
    50% { opacity: var(--maxO); transform: scale(1.3); }
  }

  /* Glowing orbs */
  .orb {
    position: absolute; border-radius: 50%;
    filter: blur(80px); pointer-events: none;
  }
  .orb1 { width: 500px; height: 500px; background: rgba(124,58,237,0.25); top: -100px; right: 100px; }
  .orb2 { width: 300px; height: 300px; background: rgba(0,229,255,0.15); bottom: -50px; right: 300px; }
  .orb3 { width: 200px; height: 200px; background: rgba(34,197,94,0.1); top: 50px; left: 200px; }

  /* Grid lines */
  .hero-grid {
    position: absolute; inset: 0;
    background-image:
      linear-gradient(rgba(0,229,255,0.04) 1px, transparent 1px),
      linear-gradient(90deg, rgba(0,229,255,0.04) 1px, transparent 1px);
    background-size: 60px 60px;
    mask-image: linear-gradient(180deg, transparent, rgba(0,0,0,0.6) 30%, rgba(0,0,0,0.6) 70%, transparent);
  }

  .hero-content {
    position: relative; z-index: 2;
    padding: 80px 60px; flex: 1;
    animation: heroIn 0.8s ease both;
  }

  @keyframes heroIn {
    from { opacity: 0; transform: translateX(-40px); }
    to { opacity: 1; transform: translateX(0); }
  }

  .hero-tag {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(0,229,255,0.1); border: 1px solid rgba(0,229,255,0.3);
    color: var(--cyan); padding: 5px 12px; border-radius: 20px;
    font-size: 11px; font-weight: 600; letter-spacing: 0.1em;
    text-transform: uppercase; margin-bottom: 20px;
  }
  .hero-tag::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: var(--cyan); animation: blink 1.5s ease infinite; }
  @keyframes blink { 0%,100% { opacity: 1; } 50% { opacity: 0.3; } }

  .hero h1 {
    font-family: 'REVERT';
    font-size: clamp(36px, 5vw, 58px);
    font-weight: 900; line-height: 1.1;
    text-transform: uppercase; letter-spacing: 0.02em;
    margin-bottom: 16px;
  }
  .hero h1 .line1 { color: var(--text); display: block; }
  .hero h1 .line2 {
    display: block;
    background: linear-gradient(90deg, var(--cyan), var(--purple2));
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .hero-sub {
    color: var(--muted); font-size: 16px; font-weight: 300;
    margin-bottom: 36px; letter-spacing: 0.05em;
  }
  .hero-sub span { color: var(--cyan); font-weight: 500; }

  .hero-btns { display: flex; gap: 14px; flex-wrap: wrap; }

  .btn-primary {
    background: linear-gradient(135deg, var(--green), var(--green2));
    color: #fff; padding: 14px 32px; border-radius: 10px;
    border: none; font-family: 'Exo 2', sans-serif;
    font-size: 15px; font-weight: 700; cursor: pointer;
    letter-spacing: 0.05em; text-transform: uppercase;
    transition: all 0.25s; box-shadow: 0 4px 20px rgba(34,197,94,0.35);
    display: flex; align-items: center; gap: 8px;
  }
  .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(34,197,94,0.5); }

  .btn-outline {
    background: transparent;
    color: var(--cyan); padding: 14px 28px; border-radius: 10px;
    border: 1.5px solid rgba(0,229,255,0.4);
    font-family: 'Exo 2', sans-serif;
    font-size: 15px; font-weight: 600; cursor: pointer;
    transition: all 0.25s; display: flex; align-items: center; gap: 8px;
  }
  .btn-outline:hover { background: rgba(0,229,255,0.1); border-color: var(--cyan); box-shadow: var(--glow-cyan); }

  .hero-stats {
    display: flex; gap: 32px; margin-top: 44px;
  }
  .stat { text-align: left; }
  .stat-num {
    font-family: 'Orbitron', monospace;
    font-size: 24px; font-weight: 700; color: var(--cyan);
  }
  .stat-label { font-size: 11px; color: var(--muted); letter-spacing: 0.08em; text-transform: uppercase; }

  /* Hero right visual */
  .hero-visual {
    position: relative; z-index: 2;
    padding: 40px 60px 40px 0;
    display: flex; align-items: center; justify-content: center;
    animation: heroVisual 1s ease both 0.3s;
  }
  @keyframes heroVisual {
    from { opacity: 0; transform: translateX(40px) scale(0.95); }
    to { opacity: 1; transform: translateX(0) scale(1); }
  }

  .hero-devices {
    position: relative; width: 480px; height: 360px;
  }

  .device-glow {
    position: absolute; inset: -40px;
    background: radial-gradient(ellipse, rgba(124,58,237,0.3) 0%, transparent 70%);
    pointer-events: none;
  }

  .device-card {
    position: absolute; border-radius: 16px;
    border: 1px solid rgba(0,229,255,0.2);
    overflow: hidden; backdrop-filter: blur(10px);
    transition: transform 0.4s ease;
    cursor: default;
  }
  .device-card:hover { transform: translateY(-8px) scale(1.02) !important; }

  .device-card .dc-inner {
    background: linear-gradient(135deg, rgba(13,31,56,0.95), rgba(15,36,68,0.9));
    padding: 20px; width: 100%; height: 100%;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: 8px;
  }

  .dc-icon { font-size: 40px; filter: drop-shadow(0 0 10px rgba(0,229,255,0.5)); }
  .dc-name { font-family: 'Orbitron', monospace; font-size: 11px; color: var(--cyan); letter-spacing: 0.1em; }
  .dc-price { font-size: 13px; font-weight: 600; color: var(--text); }

  /* Floating scan line */
  .scan-line {
    position: absolute; left: 0; right: 0; height: 2px;
    background: linear-gradient(90deg, transparent, var(--cyan), transparent);
    animation: scan 3s linear infinite;
    pointer-events: none; z-index: 5;
    opacity: 0.6;
  }
  @keyframes scan { from { top: 0%; } to { top: 100%; } }

  /* ── SECTION COMMON ── */
  section { padding: 70px 40px; }

  .section-header {
    text-align: center; margin-bottom: 48px;
  }
  .section-label {
    display: inline-block;
    font-size: 11px; font-weight: 600; letter-spacing: 0.15em;
    text-transform: uppercase; color: var(--cyan);
    margin-bottom: 10px;
  }
  .section-title {
    font-family: 'Orbitron', monospace;
    font-size: 28px; font-weight: 700; color: var(--text);
  }
  .section-title em { color: var(--cyan); font-style: normal; }
  .section-line {
    width: 60px; height: 3px;
    background: linear-gradient(90deg, var(--cyan), var(--purple2));
    margin: 12px auto 0; border-radius: 2px;
  }

  /* ── CATEGORIES ── */
  .categories { background: var(--navy2); }

  .cat-grid {
    display: grid; grid-template-columns: repeat(5, 1fr); gap: 16px;
  }

  .cat-card {
    background: var(--panel);
    border: 1px solid var(--border); border-radius: 14px;
    padding: 24px 16px; text-align: center; cursor: pointer;
    transition: all 0.3s; position: relative; overflow: hidden;
  }
  .cat-card::before {
    content: ''; position: absolute; inset: 0;
    background: linear-gradient(135deg, rgba(0,229,255,0.06), transparent);
    opacity: 0; transition: opacity 0.3s;
  }
  .cat-card:hover { border-color: var(--cyan); transform: translateY(-4px); box-shadow: 0 12px 30px rgba(0,229,255,0.15); }
  .cat-card:hover::before { opacity: 1; }

  .cat-icon {
    font-size: 36px; margin-bottom: 10px;
    filter: drop-shadow(0 0 8px rgba(0,229,255,0.3));
    transition: transform 0.3s;
  }
  .cat-card:hover .cat-icon { transform: scale(1.15); }

  .cat-name { font-size: 13px; font-weight: 600; color: var(--text); margin-bottom: 4px; }
  .cat-count { font-size: 11px; color: var(--muted); }

  /* ── PRODUCTS ── */
  .products { background: #0b1a32; }

  .product-filters {
    display: flex; gap: 8px; margin-bottom: 32px; flex-wrap: wrap;
    justify-content: center;
  }
  .filter-btn {
    padding: 7px 18px; border-radius: 20px;
    border: 1px solid var(--border); background: var(--panel);
    color: var(--muted); font-family: 'Exo 2', sans-serif;
    font-size: 12px; font-weight: 500; cursor: pointer;
    transition: all 0.2s;
  }
  .filter-btn.active, .filter-btn:hover {
    border-color: var(--cyan); color: var(--cyan);
    background: rgba(0,229,255,0.08);
  }

  .products-grid {
    display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px;
  }

  .product-card {
    background: var(--panel); border: 1px solid var(--border);
    border-radius: 16px; overflow: hidden;
    transition: all 0.35s; cursor: pointer; position: relative;
  }
  .product-card::after {
    content: ''; position: absolute; inset: 0; border-radius: 16px;
    box-shadow: inset 0 0 0 1px var(--cyan);
    opacity: 0; transition: opacity 0.3s;
    pointer-events: none; /* THÊM DÒNG NÀY ĐỂ CHO PHÉP BẤM XUYÊN QUA */
  }
  .product-card:hover { transform: translateY(-6px); box-shadow: 0 20px 40px rgba(0,0,0,0.4), 0 0 30px rgba(0,229,255,0.1); }
  .product-card:hover::after { opacity: 1; }

  .product-badge {
    position: absolute; top: 12px; left: 12px; z-index: 2;
    padding: 3px 8px; border-radius: 4px; font-size: 10px; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.05em;
  }
  .badge-hot { background: #ef4444; color: #fff; }
  .badge-new { background: var(--cyan); color: var(--navy); }
  .badge-sale { background: var(--green); color: #fff; }

 .product-img-wrap {
    height: 180px; display: flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg, var(--panel2), var(--navy3));
    padding: 0; /* SỬA TỪ 20px THÀNH 0 - ĐỂ XÓA KHOẢNG TRỐNG */
    position: relative; overflow: hidden;
  }
  /* ÉP ẢNH SẢN PHẨM PHẢI PHÓNG TO KHÍT KHUNG */
  .product-img-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover; /* 'cover' giúp ảnh tràn đầy khung. Nếu không muốn bị cắt lẹm viền ảnh, bạn đổi thành 'contain' nhé */
    transition: transform 0.3s; /* Giữ lại hiệu ứng phóng to khi đưa chuột vào */
  }
  
  .product-card:hover .product-img-wrap img {
    transform: scale(1.08); /* Hiệu ứng zoom ảnh khi trỏ chuột */
  }
  .product-img-wrap::after {
    content: ''; position: absolute; bottom: 0; left: 0; right: 0;
    height: 60px;
    background: linear-gradient(transparent, var(--panel));
  }
  .product-img { font-size: 72px; filter: drop-shadow(0 0 20px rgba(0,229,255,0.3)); transition: transform 0.3s; }
  .product-card:hover .product-img { transform: scale(1.08); }

  .product-info { padding: 16px; }

  .product-cat {
    font-size: 10px; font-weight: 600; color: var(--cyan);
    text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 6px;
  }
  .product-name {
    font-size: 14px; font-weight: 600; color: var(--text);
    margin-bottom: 4px; line-height: 1.3;
  }
  .product-specs { font-size: 11px; color: var(--muted); margin-bottom: 12px; }

  .product-price-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
  .product-price { font-family: 'Orbitron', monospace; font-size: 16px; font-weight: 700; color: var(--cyan); }
  .product-price-old { font-size: 11px; color: var(--muted); text-decoration: line-through; }

  .product-rating { display: flex; align-items: center; gap: 4px; font-size: 11px; color: #fbbf24; }

  .product-actions { display: flex; gap: 8px; }

  .btn-add {
    flex: 1; background: var(--green); color: #fff;
    border: none; border-radius: 8px; padding: 8px;
    font-family: 'Exo 2', sans-serif; font-size: 12px; font-weight: 600;
    cursor: pointer; transition: all 0.2s;
  }
  .btn-add:hover { background: var(--green2); }

  .btn-detail {
    background: var(--panel2); color: var(--muted);
    border: 1px solid var(--border); border-radius: 8px; padding: 8px 12px;
    font-family: 'Exo 2', sans-serif; font-size: 12px; font-weight: 500;
    cursor: pointer; transition: all 0.2s;
  }
  .btn-detail:hover { border-color: var(--cyan); color: var(--cyan); }

  /* ── FEATURES ── */
  .features { background: var(--navy2); }
  .features-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }

  .feature-card {
    background: var(--panel); border: 1px solid var(--border);
    border-radius: 16px; padding: 32px;
    display: flex; align-items: center; gap: 20px;
    transition: all 0.3s;
  }
  .feature-card:hover { border-color: var(--cyan); box-shadow: 0 0 30px rgba(0,229,255,0.1); }

  .feature-icon {
    width: 56px; height: 56px; border-radius: 14px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 26px;
  }
  .fi1 { background: rgba(0,229,255,0.1); }
  .fi2 { background: rgba(124,58,237,0.15); }
  .fi3 { background: rgba(34,197,94,0.1); }

  .feature-text h4 { font-size: 16px; font-weight: 700; color: var(--text); margin-bottom: 6px; }
  .feature-text p { font-size: 13px; color: var(--muted); line-height: 1.5; }

  /* ── BANNER ── */
  .promo-banner {
    background: linear-gradient(135deg, #0d0f2e, #1a0633, #0d1a40);
    position: relative; overflow: hidden; padding: 60px 40px;
  }
  .promo-banner::before {
    content: ''; position: absolute; inset: 0;
    background-image: linear-gradient(rgba(0,229,255,0.03) 1px, transparent 1px),
      linear-gradient(90deg, rgba(0,229,255,0.03) 1px, transparent 1px);
    background-size: 40px 40px;
  }
  .promo-inner {
    position: relative; z-index: 2;
    display: flex; align-items: center; justify-content: space-between; gap: 40px;
  }
  .promo-text .promo-tag {
    font-size: 11px; letter-spacing: 0.15em; color: var(--purple2);
    text-transform: uppercase; font-weight: 600; margin-bottom: 12px;
  }
  .promo-text h2 {
    font-family: 'Orbitron', monospace; font-size: 36px; font-weight: 900;
    line-height: 1.1; margin-bottom: 12px;
  }
  .promo-text h2 .hl { color: var(--purple2); }
  .promo-text p { color: var(--muted); font-size: 15px; margin-bottom: 28px; }
  .promo-countdown {
    display: flex; gap: 16px; margin-bottom: 32px;
  }
  .countdown-unit { text-align: center; }
  .countdown-num {
    font-family: 'Orbitron', monospace; font-size: 32px; font-weight: 700;
    color: var(--purple2);
    background: rgba(124,58,237,0.15); border: 1px solid rgba(124,58,237,0.3);
    border-radius: 10px; width: 68px; height: 68px;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 6px;
  }
  .countdown-label { font-size: 10px; color: var(--muted); text-transform: uppercase; letter-spacing: 0.1em; }

  .promo-visual { font-size: 120px; animation: float 4s ease-in-out infinite; }
  @keyframes float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-16px); } }

  /* ── FOOTER ── */
  footer {
    background: var(--navy2); border-top: 1px solid var(--border);
    padding: 60px 40px 30px;
  }
  .footer-grid { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 48px; margin-bottom: 48px; }

  .footer-brand .logo { display: inline-block; margin-bottom: 14px; }
  .footer-brand p { font-size: 13px; color: var(--muted); line-height: 1.7; max-width: 260px; margin-bottom: 20px; }

  .footer-socials { display: flex; gap: 10px; }
  .social-btn {
    width: 36px; height: 36px; border-radius: 8px;
    background: var(--panel); border: 1px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    color: var(--muted); text-decoration: none; font-size: 14px;
    transition: all 0.2s;
  }
  .social-btn:hover { border-color: var(--cyan); color: var(--cyan); }

  .footer-col h5 { font-size: 13px; font-weight: 700; color: var(--text); margin-bottom: 16px; letter-spacing: 0.05em; }
  .footer-col ul { list-style: none; display: flex; flex-direction: column; gap: 10px; }
  .footer-col ul a { color: var(--muted); text-decoration: none; font-size: 13px; transition: color 0.2s; }
  .footer-col ul a:hover { color: var(--cyan); }

  .footer-bottom {
    border-top: 1px solid var(--border); padding-top: 24px;
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 12px;
  }
  .footer-bottom p { font-size: 12px; color: var(--muted); }
  .footer-payment { display: flex; gap: 8px; align-items: center; }
  .payment-badge {
    background: var(--panel); border: 1px solid var(--border);
    border-radius: 6px; padding: 4px 10px; font-size: 11px; font-weight: 600; color: var(--muted);
  }

  /* ── ANIMATIONS ── */
  /* ── PHÂN TRANG SẢN PHẨM ── */
  .product-pagination { display: flex; gap: 10px; justify-content: center; margin-top: 40px; width: 100%; }
  .product-pagination button {
      background: transparent; border: 1px solid var(--panel2); color: var(--muted);
      width: 42px; height: 42px; border-radius: 8px; font-family: 'Orbitron', monospace;
      font-size: 16px; font-weight: 700; cursor: pointer; transition: all 0.3s ease;
      display: flex; align-items: center; justify-content: center;
  }
  .product-pagination button:hover { border-color: var(--cyan); color: var(--cyan); }
  .product-pagination button.active {
      border: 2px solid var(--cyan); color: var(--cyan); background: rgba(0, 229, 255, 0.1);
      box-shadow: 0 0 15px rgba(0,229,255,0.3);
  }
  .fade-in { opacity: 0; transform: translateY(24px); transition: opacity 0.6s ease, transform 0.6s ease; }
  .fade-in.visible { opacity: 1; transform: translateY(0); }

  /* Delay helpers */
  .d1 { transition-delay: 0.1s; }
  .d2 { transition-delay: 0.2s; }
  .d3 { transition-delay: 0.3s; }
  .d4 { transition-delay: 0.4s; }
  .d5 { transition-delay: 0.5s; }

  @media (max-width: 1100px) {
    .products-grid { grid-template-columns: repeat(3, 1fr); }
    .cat-grid { grid-template-columns: repeat(4, 1fr); }
  }
  @media (max-width: 900px) {
    nav { padding: 0 20px; gap: 16px; }
    .hero-visual { display: none; }
    .hero-content { padding: 60px 24px; }
    .products-grid { grid-template-columns: repeat(2, 1fr); }
    .cat-grid { grid-template-columns: repeat(3, 1fr); }
    .features-grid { grid-template-columns: 1fr; }
    .footer-grid { grid-template-columns: 1fr 1fr; }
  }
  .tav{
  width:28px;
  height:28px;
  border-radius:50%;
  object-fit:cover;
  border:none;
  transition:0.3s;
  border: 2px solid var(--cyan);
}
.tr {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: auto;
}
button.NoiDung1 {
    display: flex;
    align-items: center;
    gap: 8px;
    background: var(--panel);
    border: 1px solid var(--border);
    color: var(--text);
    padding: 8px 16px;
    border-radius: 8px;
    font-family: 'Exo 2', sans-serif;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s;
    padding-block: 1px;
    padding-inline: 6px;
    border-width: 2px;
    position: relative;
}

button.NoiDung1:hover {
    border-color: var(--cyan);
    color: var(--cyan);
}

.tav:hover{
  transform:scale(1.08);
}

.sinon{
  text-align: center;
}
</style>
<body>

<!-- NAV -->
<nav>
  <a class="logo" href="#"><span>KON</span><span> TechVN </span></a>
  <div class="nav-links">
    <a href="#" class="active" id="nav-home">Trang Chủ</a>
    <a href="#products" id="nav-products">Sản Phẩm</a>
    <a href="#promo" id="nav-promo">Khuyến Mãi</a>
    <a href="#footer" id="nav-footer">Liên Hệ</a>
  </div>
<div class="nav-search">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
    <input type="text" id="search-input" placeholder="Tìm kiếm sản phẩm (vd: iPhone, Laptop)..." autocomplete="off">
    
    <div id="search-results" class="search-dropdown"></div>
  </div>
  
  <div class="nav-actions">
    
   <button class="btn-cart" onclick="window.location.href='ChiTietGioHang.php'">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
      Giỏ Hàng
      <span class="cart-badge" id="so-luong-gio-hang" 
      style="<?= ($tongGioHang > 0) ? 'display: flex;' : 'display: none;' ?>">
    <?= $tongGioHang ?>
</span>
    </button>
    <form action="ChinhSuaProfile.php" method="POST">
        <button class="NoiDung1">
<div class="tr">
    <img class="tav" src="https://ui-avatars.com/api/?name=Qu%E1%BA%A3n+tr%E1%BB%8B+vi%C3%AAn&amp;background=6366f1&amp;color=fff&amp;size=200" alt="">
  <p class="sinon">
      <?php echo $_SESSION['TenDangNhap']; ?>
    </p>
  
  </div>
  
  </button>
    
    
  </form>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-bg" id="starfield"></div>
  <div class="orb orb1"></div>
  <div class="orb orb2"></div>
  <div class="orb orb3"></div>
  <div class="hero-grid"></div>
  <div class="scan-line"></div>

  <div class="hero-content">
    <div class="hero-tag">🔥 Flash Sale — Giảm đến 40%</div>
    <h1>
      <span class="line1">ĐỒ CÔNG NGHỆ</span>
      <span class="line2">CHÍNH HÃNG</span>
    </h1>
    <p class="hero-sub">
      <span>Laptop · PC · Gaming Gear</span> — Giá tốt nhất thị trường
    </p>
    <div class="hero-btns">
      <button class="btn-primary" onclick="document.getElementById('products').scrollIntoView({behavior:'smooth'})">
        ⚡ Mua Ngay
      </button>
     
    </div>
    <div class="hero-stats">
      <div class="stat"><div class="stat-num">10K+</div><div class="stat-label">Sản phẩm</div></div>
      <div class="stat"><div class="stat-num">50K+</div><div class="stat-label">Khách hàng</div></div>
      <div class="stat"><div class="stat-num">99%</div><div class="stat-label">Hài lòng</div></div>
    </div>
  </div>

  <div class="hero-visual">
    <div class="hero-devices">
      <div class="device-glow"></div>
      
      <div class="device-card" id="hc-0" style="width:220px;height:160px;left:22%;top:10%; cursor:pointer; transition: opacity 0.5s, transform 0.4s ease;">
        <div class="dc-inner">
          <div class="dc-icon" id="hi-0">💻</div>
          <div class="dc-name" id="hn-0">Đang tải...</div>
          <div class="dc-price" id="hp-0">0đ</div>
        </div>
      </div>
      <div class="device-card" id="hc-1" style="width:130px;height:130px;right:0;top:10px; cursor:pointer; transition: opacity 0.5s, transform 0.4s ease;">
        <div class="dc-inner">
          <div class="dc-icon" id="hi-1">📱</div>
          <div class="dc-name" id="hn-1">Đang tải...</div>
          <div class="dc-price" id="hp-1">0đ</div>
        </div>
      </div>
      <div class="device-card" id="hc-2" style="width:130px;height:130px;left:0;bottom:20px; cursor:pointer; transition: opacity 0.5s, transform 0.4s ease;">
        <div class="dc-inner">
          <div class="dc-icon" id="hi-2">🎧</div>
          <div class="dc-name" id="hn-2">Đang tải...</div>
          <div class="dc-price" id="hp-2">0đ</div>
        </div>
      </div>
      <div class="device-card" id="hc-3" style="width:120px;height:130px;right:10px;bottom:10px; cursor:pointer; transition: opacity 0.5s, transform 0.4s ease;">
        <div class="dc-inner">
          <div class="dc-icon" id="hi-3">🖱️</div>
          <div class="dc-name" id="hn-3">Đang tải...</div>
          <div class="dc-price" id="hp-3">0đ</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CATEGORIES -->

<script>
function themVaoGio(maSP, buttonElement) {
    // 1. Chuông báo: Bấm phát phải hiện thông báo này ngay!
    alert("Đã bấm nút! Đang chuẩn bị thêm sản phẩm mã số: " + maSP);

    // 2. Kiểm tra nếu quên chữ 'this'
    if(!buttonElement) {
        alert("Lỗi: Nút HTML đang viết thiếu chữ 'this'!");
        return;
    }

    // 3. Tiến hành gửi AJAX ngầm
    $.ajax({
        url: "them_gio_hang.php",
        type: "POST",
        data: { id_sanpham: maSP },
        success: function(response) {
            // NẾU BACKEND PHÁT HIỆN ĐÃ ĐẶT QUÁ SỐ LƯỢNG KHO
            if (response.trim() === "VUOT_QUY_DINH") {
                alert("⚠️ SỐ LƯỢNG ĐẠT GIỚI HẠN!\nKho không đủ sản phẩm để bạn thêm tiếp vào giỏ hàng.");
                return; // Dừng lại, không đổi màu nút, không cộng giỏ hàng
            }

            // Nếu thành công bình thường
            $("#so-luong-gio-hang").text(response);
            
            let oldText = buttonElement.innerHTML;
            buttonElement.innerHTML = '✓ Đã thêm!';
            buttonElement.style.background = '#059669'; 
            
            setTimeout(() => { 
                buttonElement.innerHTML = oldText; 
                buttonElement.style.background = ''; 
            }, 1500);
        },
        error: function(xhr, status, error) {
            alert("Lỗi AJAX: Không gọi được file PHP!");
        }
    });
}






</script>





<script>
$(document).ready(function(){
    // Khi gõ phím vào ô tìm kiếm
    $('#search-input').on('keyup', function() {
        var keyword = $(this).val(); // Lấy chữ khách vừa gõ
        
        if (keyword.length > 0) {
            // Gửi chữ đó qua file PHP bằng AJAX
            $.ajax({
                url: 'tim_kiem_san_pham.php',
                type: 'POST',
                data: { tukhoa: keyword },
                success: function(data) {
                    $('#search-results').html(data).show(); // Hiển thị kết quả
                }
            });
        } else {
            $('#search-results').hide(); // Xóa chữ thì ẩn hộp kết quả đi
        }
    });

    // Bấm ra ngoài khoảng trống thì tự động ẩn hộp tìm kiếm đi cho gọn
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.nav-search').length) {
            $('#search-results').hide();
        }
    });
});
</script>
<!-- PRODUCTS -->
<section class="products" id="products">
  <div class="section-header fade-in">
    <div class="section-label">// Sản phẩm</div>
    <h2 class="section-title">Sản Phẩm <em>Hot</em></h2>
    <div class="section-line"></div>
  </div>
  
  <?php $madm_filter = isset($_GET['danhmuc']) ? (int)$_GET['danhmuc'] : 0; ?>
  
  <div class="product-filters fade-in" id="bo-loc">
    <a href="TrangChuDaDangNhap.php#bo-loc" class="filter-btn <?= ($madm_filter == 0) ? 'active' : '' ?>" style="text-decoration:none; display:inline-block;">Tất Cả</a>
    <a href="TrangChuDaDangNhap.php?danhmuc=1#bo-loc" class="filter-btn <?= ($madm_filter == 1) ? 'active' : '' ?>" style="text-decoration:none; display:inline-block;">Laptop</a>
    <a href="TrangChuDaDangNhap.php?danhmuc=3#bo-loc" class="filter-btn <?= ($madm_filter == 3) ? 'active' : '' ?>" style="text-decoration:none; display:inline-block;">PC Gaming</a>
    <a href="TrangChuDaDangNhap.php?danhmuc=2#bo-loc" class="filter-btn <?= ($madm_filter == 2) ? 'active' : '' ?>" style="text-decoration:none; display:inline-block;">Điện Thoại</a>
    <a href="TrangChuDaDangNhap.php?danhmuc=4#bo-loc" class="filter-btn <?= ($madm_filter == 4) ? 'active' : '' ?>" style="text-decoration:none; display:inline-block;">Phụ Kiện</a>
    <a href="TrangChuDaDangNhap.php?danhmuc=5#bo-loc" class="filter-btn <?= ($madm_filter == 5) ? 'active' : '' ?>" style="text-decoration:none; display:inline-block;">Gaming Gear</a>
  </div>
<div class="products-grid" id="product-grid">
  
    <?php
    
    // TRUY VẤN LẤY SẢN PHẨM KÈM THEO TÍNH TRUNG BÌNH SAO
    if ($madm_filter > 0) {
        $sql_sp = "SELECT sp.*, 
                   ISNULL((SELECT AVG(CAST(SoSao AS FLOAT)) FROM BinhLuan WHERE MaSP = sp.MaSP), 0) AS AvgSao,
                   (SELECT COUNT(*) FROM BinhLuan WHERE MaSP = sp.MaSP) AS TotalBL
                   FROM SanPham sp WHERE sp.MaDM = ? ORDER BY sp.MaSP DESC";
        $stmt_sp = sqlsrv_query($conn, $sql_sp, [$madm_filter]);
    } else {
        $sql_sp = "SELECT sp.*, 
                   ISNULL((SELECT AVG(CAST(SoSao AS FLOAT)) FROM BinhLuan WHERE MaSP = sp.MaSP), 0) AS AvgSao,
                   (SELECT COUNT(*) FROM BinhLuan WHERE MaSP = sp.MaSP) AS TotalBL
                   FROM SanPham sp ORDER BY sp.MaSP DESC"; 
        $stmt_sp = sqlsrv_query($conn, $sql_sp);
    }

    if ($stmt_sp === false) {
        echo "Lỗi truy vấn sản phẩm: <pre>" . print_r(sqlsrv_errors(), true) . "</pre>";
    } else {
        while($row = sqlsrv_fetch_array($stmt_sp, SQLSRV_FETCH_ASSOC)) { 
            $specs = implode(' · ', array_filter([$row['CPU'], $row['RAM'], $row['O_Cung']]));
    ?>
            
        <div class="product-card fade-in">
            <div class="product-img-wrap">
            <?php if (!empty($row['HinhAnh'])): ?>
                <img src="<?php echo htmlspecialchars($row['HinhAnh']); ?>" alt="Ảnh">
            <?php else: ?>
                <div class="product-img">
                    <?php echo ($row['MaDM'] == 1 || $row['MaDM'] == 3) ? '💻' : (($row['MaDM'] == 2) ? '📱' : '📦'); ?>
                </div>
            <?php endif; ?>
            </div>
            
            <div class="product-info">
                <div class="product-cat">Danh mục ID: <?php echo $row['MaDM']; ?></div>
                <div class="product-name"><?php echo $row['TenSP']; ?></div>
                
                <div style="font-size: 12px; margin-bottom: 8px;">
                    <?php if($row['TotalBL'] > 0): ?>
                        <span style="color: #fbbf24;">★ <?= number_format($row['AvgSao'], 1) ?></span>
                        <span style="color: var(--muted); margin-left: 4px;">(<?= $row['TotalBL'] ?> đánh giá)</span>
                    <?php else: ?>
                        <span style="color: var(--muted);">Chưa có đánh giá</span>
                    <?php endif; ?>
                </div>
                
                <div class="product-specs">
                    <?php echo $specs; ?><br>
                    <?php if($row['SoLuongTon'] > 0): ?>
                        <span style="color: var(--green); font-weight: bold; font-size: 12px;">✅ Còn lại: <?php echo $row['SoLuongTon']; ?></span>
                    <?php else: ?>
                        <span style="color: #ef4444; font-weight: bold; font-size: 12px;">❌ Đã hết hàng</span>
                    <?php endif; ?>
                </div>
                
                <div class="product-price-row">
                    <div>
                        <div class="product-price"><?php echo number_format($row['Gia'], 0, ',', '.'); ?>đ</div>
                    </div>
                </div>
                
                <div class="product-actions" style="position: relative; z-index: 10;">
                    <?php if($row['SoLuongTon'] > 0): ?>
                        <button class="btn-add" onclick="themVaoGio(<?php echo $row['MaSP']; ?>, this)">🛒 Thêm vào giỏ</button>
                    <?php else: ?>
                        <button class="btn-add" style="background: #475569; opacity: 0.6; cursor: not-allowed;" onclick="alert('Rất tiếc! Sản phẩm này hiện đã hết hàng.');">🚫 Hết hàng</button>
                    <?php endif; ?>
                    <button class="btn-detail" onclick="window.location.href='ChiTietSanPham.php?id=<?= $row['MaSP']; ?>'">Chi tiết</button>
                </div>
            </div>
        </div>
    <?php 
        } 
    } 
    ?>
    </div> <div id="product-pagination" class="product-pagination"></div>
  </div>
</section>
<!-- PROMO BANNER -->
 <script>
// --- HÀM PHÂN TRANG SẢN PHẨM TRANG CHỦ ---
// --- HÀM PHÂN TRANG SẢN PHẨM TRANG CHỦ (TỰ ĐỘNG TRƯỢT 5 TRANG) ---
function initProductPagination() {
    const grid = document.getElementById('product-grid');
    if (!grid) return;
    
    const cards = Array.from(grid.querySelectorAll('.product-card'));
    const itemsPerPage = 8; // Số sản phẩm hiển thị trên 1 trang (có thể đổi thành 12, 16...)
    const totalPages = Math.ceil(cards.length / itemsPerPage);
    const paginationContainer = document.getElementById('product-pagination');
    
    if (totalPages <= 1) {
        paginationContainer.style.display = 'none';
        return;
    }
    
    let currentPage = 1;
    const maxVisibleButtons = 5; // Số lượng nút trang hiển thị tối đa (1 2 3 4 5)

    // Hàm render lại các nút bấm
    function renderPagination() {
        paginationContainer.innerHTML = '';

        // Tính toán trang bắt đầu và trang kết thúc
        let startPage = Math.max(1, currentPage - Math.floor(maxVisibleButtons / 2));
        let endPage = Math.min(totalPages, startPage + maxVisibleButtons - 1);

        // Điều chỉnh lại nếu các trang ở cuối bị hụt (ví dụ: tổng 7 trang, đang ở trang 7)
        if (endPage - startPage + 1 < maxVisibleButtons) {
            startPage = Math.max(1, endPage - maxVisibleButtons + 1);
        }

        // Nút Prev (Mũi tên lùi ❮) - Chỉ hiện khi không phải trang 1
        if (currentPage > 1) {
            const prevBtn = document.createElement('button');
            prevBtn.innerText = '❮';
            prevBtn.onclick = () => { currentPage--; showPage(currentPage); };
            paginationContainer.appendChild(prevBtn);
        }

        // Các nút số (1, 2, 3...)
        for (let i = startPage; i <= endPage; i++) {
            const btn = document.createElement('button');
            btn.innerText = i;
            btn.dataset.page = i;
            if (i === currentPage) btn.classList.add('active'); // Đánh dấu trang hiện tại
            btn.onclick = () => { currentPage = i; showPage(currentPage); };
            paginationContainer.appendChild(btn);
        }

        // Nút Next (Mũi tên tiến ❯) - Chỉ hiện khi chưa đến trang cuối
        if (currentPage < totalPages) {
            const nextBtn = document.createElement('button');
            nextBtn.innerText = '❯';
            nextBtn.onclick = () => { currentPage++; showPage(currentPage); };
            paginationContainer.appendChild(nextBtn);
        }
    }

    // Hàm hiển thị sản phẩm của trang tương ứng
    function showPage(page) {
        cards.forEach((card, index) => {
            if (index >= (page - 1) * itemsPerPage && index < page * itemsPerPage) {
                card.style.display = 'block';
                card.classList.remove('visible');
                setTimeout(() => card.classList.add('visible'), 50);
            } else {
                card.style.display = 'none';
            }
        });
        
        // Cập nhật lại thanh phân trang
        renderPagination();
        
        // Cuộn mượt lên đầu khu vực sản phẩm
        document.getElementById('products').scrollIntoView({ behavior: 'smooth' });
    }
    
    // Mặc định gọi trang 1 khi mới vào
    showPage(1);
}
document.addEventListener('DOMContentLoaded', initProductPagination);

// Chạy hàm phân trang ngay khi web load xong
document.addEventListener('DOMContentLoaded', initProductPagination);
</script>
<?php
  // KHẮC PHỤC LỆCH MÚI GIỜ: Dùng giờ của PHP thay vì GETDATE() của SQL
  $now = date('Y-m-d H:i:s');

  // 1. THAY VÌ XÓA MẤT TÍCH, CHÚNG TA CHỈ "VÔ HIỆU HÓA" MÃ (Để mã còn hiện trong ví khách)
  $sql_disable = "UPDATE MaGiamGia SET TrangThai = 0 WHERE (NgayHetHan IS NOT NULL AND NgayHetHan <= ?) OR DaDung >= SoLanDung";
  sqlsrv_query($conn, $sql_disable, [$now]);

  // 2. Kiểm tra xem có mã FLASH nào đang sống không
  $sql_check = "SELECT TOP 1 * FROM MaGiamGia WHERE Code LIKE 'FLASH%' AND TrangThai = 1 AND NgayHetHan > ? ORDER BY MaMGG DESC";
  $stmt_check = sqlsrv_query($conn, $sql_check, [$now]);
  $promo = $stmt_check ? sqlsrv_fetch_array($stmt_check, SQLSRV_FETCH_ASSOC) : null;

  // 3. Nếu chưa có mã FLASH (chưa từng tạo hoặc mã cũ vừa hết hạn), TẠO MÃ MỚI!
  if (!$promo) {
      $newCode = 'FLASH' . rand(1000, 9999);
      $loaiGiam = rand(0, 1);
      $giaTri = ($loaiGiam == 0) ? rand(10, 30) : rand(50000, 200000);
      $giamToiDa = ($loaiGiam == 0) ? rand(100000, 500000) : 0;
      $donToiThieu = rand(300000, 1500000);
      $soLanDung = rand(3, 10);
      $ngayHetHan = date('Y-m-d H:i:s', strtotime('+5 minutes'));

      $sql_insert = "INSERT INTO MaGiamGia (Code, LoaiGiam, GiaTri, GiamToiDa, DonToiThieu, SoLanDung, NgayHetHan, TrangThai) VALUES (?, ?, ?, ?, ?, ?, ?, 1)";
      sqlsrv_query($conn, $sql_insert, [$newCode, $loaiGiam, $giaTri, $giamToiDa, $donToiThieu, $soLanDung, $ngayHetHan]);

      $stmt_check = sqlsrv_query($conn, $sql_check, [$now]);
      $promo = sqlsrv_fetch_array($stmt_check, SQLSRV_FETCH_ASSOC);
  }

  // 4. Tính số giây còn lại cho Javascript
  $time_left = 0;
  if ($promo && $promo['NgayHetHan']) {
      $time_left = $promo['NgayHetHan']->getTimestamp() - time();
  }
?>

<?php if($promo): ?>
<section class="promo-banner" id="promo">
  <div class="promo-inner">
    <div class="promo-text">
      <div class="promo-tag">⚡ FLASH SALE 5 PHÚT · Chỉ còn <?= $promo['SoLanDung'] - $promo['DaDung'] ?> lượt</div>
      <h2>MÃ: <span class="hl"><?= htmlspecialchars($promo['Code']) ?></span></h2>
      <p>
        Giảm <?= $promo['LoaiGiam'] == 0 ? $promo['GiaTri'].'%' : number_format($promo['GiaTri'], 0, ',', '.').'đ' ?> 
        cho đơn hàng từ <?= number_format($promo['DonToiThieu'], 0, ',', '.') ?>đ!
      </p>
      <div class="promo-countdown">
        <div class="countdown-unit"><div class="countdown-num" id="cd-h">00</div><div class="countdown-label">Giờ</div></div>
        <div class="countdown-unit"><div class="countdown-num" id="cd-m">00</div><div class="countdown-label">Phút</div></div>
        <div class="countdown-unit"><div class="countdown-num" id="cd-s">00</div><div class="countdown-label">Giây</div></div>
      </div>
      
      <form action="ChinhSuaProfile.php" method="POST" style="display:inline-block;">
         <input type="hidden" name="action" value="save_coupon">
         <input type="hidden" name="MaMGG" value="<?= $promo['MaMGG'] ?>">
         <button type="submit" class="btn-primary" style="font-size: 14px; padding: 12px 24px;">
            💾 Lưu vào thẻ giảm giá của bạn
         </button>
      </form>
    </div>
    <div class="promo-visual">🎫</div>
  </div>
</section>
<?php endif; ?>

<!-- FEATURES -->
<section class="features">
  <div class="features-grid">
    <div class="feature-card fade-in d1">
      <div class="feature-icon fi1">🚚</div>
      <div class="feature-text">
        <h4>Giao Hàng Nhanh</h4>
        <p>Giao hàng trong 24h tại TP.HCM và 48h toàn quốc. Miễn phí với đơn trên 500k.</p>
      </div>
    </div>
    <div class="feature-card fade-in d2">
      <div class="feature-icon fi2">🛡️</div>
      <div class="feature-text">
        <h4>Bảo Hành Chính Hãng</h4>
        <p>Cam kết 100% hàng chính hãng. Bảo hành toàn quốc tại các trung tâm ủy quyền.</p>
      </div>
    </div>
    <div class="feature-card fade-in d3">
      <div class="feature-icon fi3">💳</div>
      <div class="feature-text">
        <h4>Thanh Toán An Toàn</h4>
        <p>Hỗ trợ 15+ phương thức thanh toán. Mã hóa SSL 256-bit bảo vệ dữ liệu của bạn.</p>
      </div>
    </div>
  </div>
</section>

<!-- FOOTER -->
<footer id="footer">
  <div class="footer-grid">
    <div class="footer-brand">
  <a class="logo" href="#"><span>KON</span><span> TechVN </span></a>
      <p>Chuyên cung cấp thiết bị công nghệ chính hãng, giá tốt nhất thị trường. Hơn 10 năm kinh nghiệm phục vụ hàng triệu khách hàng.</p>
      <div class="footer-socials">
        <a class="social-btn" href="#">f</a>
        <a class="social-btn" href="#">𝕏</a>
        <a class="social-btn" href="#">in</a>
        <a class="social-btn" href="#">▶</a>
      </div>
    </div>
    <div class="footer-col">
      <h5>Sản Phẩm</h5>
      <ul>
        <li><a href="#">Laptop</a></li>
        <li><a href="#">PC Gaming</a></li>
        <li><a href="#">Điện Thoại</a></li>
        <li><a href="#">Màn Hình</a></li>
        <li><a href="#">Phụ Kiện</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h5>Hỗ Trợ</h5>
      <ul>
        <li><a href="#">Chính Sách Bảo Hành</a></li>
        <li><a href="#">Đổi Trả Hàng</a></li>
        <li><a href="#">Hướng Dẫn Mua</a></li>
        <li><a href="#">FAQ</a></li>
        <li><a href="#">Liên Hệ</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h5>Liên Hệ</h5>
      <ul>
        <li><a href="#">📍 123 Nguyễn Huệ, Q1, HCM</a></li>
        <li><a href="#">📞 1800 9999</a></li>
        <li><a href="#">✉️ support@techstore.vn</a></li>
        <li><a href="#">🕐 8:00 – 22:00 (T2–CN)</a></li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom">
    <p>© 2024 Tech Store. All rights reserved.</p>
    <div class="footer-payment">
      <span class="payment-badge">VISA</span>
      <span class="payment-badge">MC</span>
      <span class="payment-badge">MOMO</span>
      <span class="payment-badge">VNPAY</span>
      <span class="payment-badge">ZaloPay</span>
    </div>
  </div>
</footer>
<script>

(function(){
  const sf = document.getElementById('starfield');
  for(let i=0;i<120;i++){
    const s = document.createElement('div');
    s.className = 'star';
    const size = Math.random()*2.5+0.5;
    s.style.cssText = `
      width:${size}px;height:${size}px;
      left:${Math.random()*100}%;top:${Math.random()*100}%;
      --dur:${2+Math.random()*4}s;
      --minO:${0.1+Math.random()*0.2};
      --maxO:${0.5+Math.random()*0.5};
      animation-delay:${Math.random()*4}s;
    `;
    sf.appendChild(s);
  }
})();

// ── Intersection Observer for fade-in
const observer = new IntersectionObserver(entries => {
  entries.forEach(e => { if(e.isIntersecting) e.target.classList.add('visible'); });
}, { threshold: 0.1 });
document.querySelectorAll('.fade-in').forEach(el => observer.observe(el));

// ── Filter buttons


// ── Countdown timer
// ── Countdown timer (ĐỒNG BỘ 100% VỚI THỜI GIAN SỐNG CỦA MÃ TRONG SQL)
// ── Countdown timer (ĐỒNG BỘ 100% VỚI THỜI GIAN SỐNG CỦA MÃ TRONG SQL)
let total = <?= max(0, $time_left) ?>;

function updateCountdown(){
  if(total <= 0) {
      // Khi hết 5 phút (total về 0), tự động F5 lại trang để PHP tạo mã mới!
      window.location.reload();
      return;
  }
  total--;
  const h = Math.floor(total/3600);
  const m = Math.floor((total%3600)/60);
  const s = total%60;
  document.getElementById('cd-h').textContent = String(h).padStart(2,'0');
  document.getElementById('cd-m').textContent = String(m).padStart(2,'0');
  document.getElementById('cd-s').textContent = String(s).padStart(2,'0');
}
setInterval(updateCountdown, 1000);
// Gọi ngay lần đầu để khỏi bị delay 1s
updateCountdown();

</script>

<script src="js/trangchu.js"></script>
<?php 
// CHỈ HIỂN THỊ KHUNG CHAT NẾU TÀI KHOẢN KHÔNG PHẢI LÀ "admin"
if (isset($_SESSION['TenDangNhap']) && $_SESSION['TenDangNhap'] !== 'admin') { 
?>

   
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

      
<style>
    #chat-box {
        position: fixed; bottom: 20px; right: 20px; width: 320px;
        background: white; border-radius: 10px;
        box-shadow: 0px 4px 15px rgba(0,0,0,0.2); z-index: 9999;
        font-family: Arial, sans-serif;
    }
    #chat-header {
        background: #007bff; color: white; padding: 12px;
        border-top-left-radius: 10px; border-top-right-radius: 10px;
        font-weight: bold; text-align: center; cursor: pointer;
    }
    #chat-content {
        height: 300px; overflow-y: auto; padding: 10px;
        background: #fafafa; display: flex; flex-direction: column;
    }
    #chat-input-area {
        display: flex; padding: 10px; border-top: 1px solid #ddd;
    }
    #txt-message {
        flex: 1; padding: 8px; border: 1px solid #ccc; border-radius: 5px; outline: none;
    }
    #btn-send {
        background: #007bff; color: white; border: none; padding: 8px 15px;
        margin-left: 5px; border-radius: 5px; cursor: pointer; font-weight: bold;
    }
    #btn-send:hover { background: #0056b3; }
</style>

<div id="chat-box">
    <div id="chat-header" onclick="toggleChat()">💬 Trò chuyện với Admin</div>
    <div id="chat-body">
        <div id="chat-content">
            </div>
        <div id="chat-input-area">
            <input type="text" id="txt-message" placeholder="Nhập tin nhắn..." onkeypress="handleKeyPress(event)">
            <button id="btn-send" onclick="sendMessage()">Gửi</button>
        </div>
    </div>
</div>

<script>
    // Hàm Thu gọn / Mở rộng khung chat
    function toggleChat() {
        $("#chat-body").slideToggle();
    }

    // Hàm tải tin nhắn từ Database lên
    function loadMessages() {
        $.ajax({
            url: "load_messages.php",
            type: "GET",
            success: function(data) {
                var chatContent = $("#chat-content");
                // Kiểm tra xem thanh cuộn có đang ở dưới cùng không
                var isScrolledToBottom = chatContent[0].scrollHeight - chatContent[0].clientHeight <= chatContent[0].scrollTop + 20;
                
                chatContent.html(data); // Đổ dữ liệu vào khung
                
                // Nếu đang ở dưới cùng thì tự cuộn xuống khi có tin nhắn mới
                if(isScrolledToBottom) {
                    chatContent.scrollTop(chatContent[0].scrollHeight);
                }
            }
        });
    }

    // Hàm Gửi tin nhắn
    function sendMessage() {
        var message = $("#txt-message").val();
        if(message.trim() !== "") {
            $.ajax({
                url: "send_message.php",
                type: "POST",
                data: { noidung: message },
                success: function() {
                    $("#txt-message").val(""); // Xóa trắng ô nhập
                    loadMessages(); // Tải lại tin nhắn ngay lập tức
                    
                    // Bắt buộc cuộn xuống dưới cùng sau khi gửi
                    setTimeout(function(){
                        var chatContent = $("#chat-content");
                        chatContent.scrollTop(chatContent[0].scrollHeight);
                    }, 100);
                }
            });
        }
    }

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

            // 1. Lấy con số trả về
            let soLuongMoi = parseInt(response);
            
            // 2. Tìm thẻ badge
            let badge = $("#so-luong-gio-hang");

            // 3. Cập nhật số và HIỆN THỊ nếu > 0
            if (soLuongMoi > 0) {
                badge.text(soLuongMoi);
                badge.css("display", "flex"); // Ép nó hiện ra lại
            } else {
                badge.css("display", "none");
            }

            // Hiệu ứng đổi màu nút của bạn
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
            alert("Lỗi AJAX!");
        }
    });
}


    // Ấn Enter để gửi thay vì bấm nút
    function handleKeyPress(e) {
        if(e.keyCode === 13) {
            sendMessage();
        }
    }

    // Khi trang web vừa tải xong:
    $(document).ready(function(){
        loadMessages(); // Load tin nhắn lần đầu
        
        // Cứ mỗi 2 giây lại ngầm chạy hàm loadMessages() 1 lần để quét tin nhắn mới
        setInterval(loadMessages, 2000); 
        
        // Cuộn xuống dưới cùng lúc mới mở
        setTimeout(function(){
            var chatContent = $("#chat-content");
            chatContent.scrollTop(chatContent[0].scrollHeight);
        }, 500);
    });  
</script>

    <?php 
} // Dòng này khóa cái lệnh if lại
?>
<script>
// Nhận danh sách 12 sản phẩm từ PHP
const heroProducts = <?php echo json_encode($hero_products); ?>;
let currentHeroIndex = 0;

function rotateHeroCards() {
    if (!heroProducts || heroProducts.length === 0) return;
    
    for (let i = 0; i < 4; i++) {
        // Lấy sản phẩm tiếp theo, nếu hết vòng thì quay lại từ đầu
        let p = heroProducts[(currentHeroIndex + i) % heroProducts.length];
        if (!p) continue;
        
        let card = document.getElementById('hc-' + i);
        let iconWrap = document.getElementById('hi-' + i);
        let nameDiv = document.getElementById('hn-' + i);
        let priceDiv = document.getElementById('hp-' + i);
        
        // Làm mờ thẻ đi trước khi đổi nội dung
        card.style.opacity = '0';
        
        setTimeout(() => {
            // Thay đổi link khi click
            card.onclick = function() {
                window.location.href = 'ChiTietSanPham.php?id=' + p.MaSP;
            };
            
            // Thay đổi tên (Cắt ngắn nếu tên quá dài để không bị tràn khung)
            let shortName = p.TenSP.length > 16 ? p.TenSP.substring(0, 16) + '...' : p.TenSP;
            nameDiv.innerText = shortName;
            
            // Thay đổi giá tiền
            priceDiv.innerText = new Intl.NumberFormat('vi-VN').format(p.Gia) + 'đ';
            
            // Hiện ảnh sản phẩm (nếu có), nếu không có ảnh thì hiện Icon
            if (p.HinhAnh && p.HinhAnh.trim() !== '') {
                iconWrap.innerHTML = `<img src="${p.HinhAnh}" style="width:60px; height:60px; object-fit:contain; filter: drop-shadow(0 0 10px rgba(0,229,255,0.4));">`;
            } else {
                let icon = '📦';
                if (p.MaDM == 1 || p.MaDM == 3) icon = '💻';
                else if (p.MaDM == 2) icon = '📱';
                else if (p.MaDM == 4) icon = '🎧';
                else if (p.MaDM == 5) icon = '🖱️';
                iconWrap.innerHTML = icon;
            }
            
            // Cho thẻ hiện rõ lại
            card.style.opacity = '1';
        }, 500); // Chờ 0.5s mờ đi rồi đổi
    }
    
    // Cộng index lên 4 để lần lặp sau lấy 4 sản phẩm mới
    currentHeroIndex = (currentHeroIndex + 4) % heroProducts.length;
}

// Chạy lần đầu tiên ngay khi tải trang
rotateHeroCards();

// Thiết lập chu kỳ 4 giây (4000ms) đổi 1 lần
setInterval(rotateHeroCards, 4000);
</script>
<script>
// --- XỬ LÝ CLICK ĐỔI MÀU MENU ---
document.querySelectorAll('.nav-links a').forEach(link => {
    link.addEventListener('click', function() {
        // Xóa class 'active' khỏi tất cả các thẻ a
        document.querySelectorAll('.nav-links a').forEach(a => a.classList.remove('active'));
        
        // Thêm class 'active' vào chính thẻ vừa click
        this.classList.add('active');
    });
});
</script>
</body>
</html>