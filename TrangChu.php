<?php
session_start();

// KẾT NỐI DATABASE
$serverName = "localhost\\SQLEXPRESS";
$database   = "QLBanHang";
$connectionInfo = ["Database" => $database, "TrustServerCertificate" => true, "CharacterSet" => "UTF-8"];
$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Lấy ngẫu nhiên 12 sản phẩm để làm hiệu ứng xoay vòng trên Banner
$sql_hero = "SELECT TOP 12 MaSP, TenSP, Gia, HinhAnh, MaDM FROM SanPham ORDER BY NEWID()"; 
$stmt_hero = sqlsrv_query($conn, $sql_hero);
$hero_products = [];
if ($stmt_hero) {
    while ($row = sqlsrv_fetch_array($stmt_hero, SQLSRV_FETCH_ASSOC)) {
        $hero_products[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">

<head>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KhoaOngNghiem TechVN - Trang Chủ</title>
  <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700;900&family=Exo+2:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
  :root {
    --navy: #050d1a; --navy2: #071223; --navy3: #0a1a30;
    --panel: #0d1f38; --panel2: #0f2444;
    --cyan: #00e5ff; --cyan2: #00b8d4;
    --purple: #7c3aed; --purple2: #a855f7;
    --green: #22c55e; --green2: #16a34a;
    --text: #e2eaf5; --muted: #7a92b0;
    --border: rgba(0,229,255,0.12);
    --glow-cyan: 0 0 20px rgba(0,229,255,0.4);
    --glow-purple: 0 0 20px rgba(168,85,247,0.4);
  }

  * { margin: 0; padding: 0; box-sizing: border-box; }
  html { scroll-behavior: smooth; }
  body { background: var(--navy); color: var(--text); font-family: 'Exo 2', sans-serif; overflow-x: hidden; }

  /* Bổ sung CSS cho thanh tìm kiếm */
  .nav-search { position: relative; display: flex; align-items: center; gap: 8px; background: var(--panel); border: 1px solid var(--border); border-radius: 8px; padding: 8px 14px; flex: 1; max-width: 320px; transition: border-color 0.2s; }
  .nav-search:focus-within { border-color: var(--cyan); box-shadow: 0 0 0 3px rgba(0,229,255,0.1); }
  .nav-search svg { color: var(--muted); flex-shrink: 0; }
  .nav-search input { background: none; border: none; outline: none; color: var(--text); font-family: 'Exo 2', sans-serif; font-size: 13px; width: 100%; }
  .nav-search input::placeholder { color: var(--muted); }
  
  .search-dropdown { position: absolute; top: 100%; left: 0; width: 100%; background: var(--panel); border: 1px solid var(--border); border-radius: 8px; margin-top: 8px; box-shadow: 0 5px 20px rgba(0,0,0,0.6); display: none; max-height: 350px; overflow-y: auto; z-index: 9999; }
  .search-item { padding: 10px 15px; border-bottom: 1px solid rgba(0,229,255,0.1); display: flex; align-items: center; gap: 12px; text-decoration: none; color: var(--text); cursor: pointer; transition: 0.2s; }
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
  nav { position: sticky; top: 0; z-index: 100; background: rgba(5,13,26,0.92); backdrop-filter: blur(20px); border-bottom: 1px solid var(--border); padding: 0 40px; display: flex; align-items: center; gap: 32px; height: 64px; }
  .logo { font-family: 'REVERT'; font-weight: 900; font-size: 20px; letter-spacing: 0.05em; text-decoration: none; margin-right: 16px; }
  .logo span:first-child { color: var(--cyan); }
  .logo span:last-child { color: var(--text); }

  .nav-links { display: flex; gap: 4px; flex: 1; }
  .nav-links a { color: var(--muted); text-decoration: none; padding: 8px 14px; border-radius: 6px; font-size: 14px; font-weight: 500; transition: all 0.2s; position: relative; }
  .nav-links a:hover { color: var(--cyan); background: rgba(0,229,255,0.07); }
  .nav-links a.active { color: var(--cyan); }
  .nav-links a.active::after { content: ''; position: absolute; bottom: 4px; left: 14px; right: 14px; height: 2px; background: var(--cyan); border-radius: 1px; box-shadow: var(--glow-cyan); }

  .nav-actions { display: flex; align-items: center; gap: 10px; }
  .btn-cart { display: flex; align-items: center; gap: 8px; background: var(--panel); border: 1px solid var(--border); color: var(--text); padding: 8px 16px; border-radius: 8px; font-family: 'Exo 2', sans-serif; font-size: 13px; cursor: pointer; transition: all 0.2s; position: relative; }
  .btn-cart:hover { border-color: var(--cyan); color: var(--cyan); }
  
  .btn-login { background: var(--purple); color: #fff; padding: 8px 20px; border-radius: 8px; border: none; font-family: 'Exo 2', sans-serif; font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s; white-space: nowrap; text-decoration: none; }
  .btn-login:hover { background: var(--purple2); box-shadow: var(--glow-purple); }

  /* ── HERO ── */
  .hero { position: relative; overflow: hidden; min-height: 520px; background: linear-gradient(135deg, #050d1a 0%, #0a1533 40%, #0d1a40 70%, #08102e 100%); display: flex; align-items: center; }
  .hero-bg { position: absolute; inset: 0; overflow: hidden; }
  .star { position: absolute; border-radius: 50%; background: white; animation: twinkle var(--dur) ease-in-out infinite; }
  @keyframes twinkle { 0%,100% { opacity: var(--minO); transform: scale(1); } 50% { opacity: var(--maxO); transform: scale(1.3); } }
  .orb { position: absolute; border-radius: 50%; filter: blur(80px); pointer-events: none; }
  .orb1 { width: 500px; height: 500px; background: rgba(124,58,237,0.25); top: -100px; right: 100px; }
  .orb2 { width: 300px; height: 300px; background: rgba(0,229,255,0.15); bottom: -50px; right: 300px; }
  .orb3 { width: 200px; height: 200px; background: rgba(34,197,94,0.1); top: 50px; left: 200px; }
  .hero-grid { position: absolute; inset: 0; background-image: linear-gradient(rgba(0,229,255,0.04) 1px, transparent 1px), linear-gradient(90deg, rgba(0,229,255,0.04) 1px, transparent 1px); background-size: 60px 60px; mask-image: linear-gradient(180deg, transparent, rgba(0,0,0,0.6) 30%, rgba(0,0,0,0.6) 70%, transparent); }
  .hero-content { position: relative; z-index: 2; padding: 80px 60px; flex: 1; animation: heroIn 0.8s ease both; }
  @keyframes heroIn { from { opacity: 0; transform: translateX(-40px); } to { opacity: 1; transform: translateX(0); } }
  .hero-tag { display: inline-flex; align-items: center; gap: 6px; background: rgba(0,229,255,0.1); border: 1px solid rgba(0,229,255,0.3); color: var(--cyan); padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; margin-bottom: 20px; }
  .hero-tag::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: var(--cyan); animation: blink 1.5s ease infinite; }
  @keyframes blink { 0%,100% { opacity: 1; } 50% { opacity: 0.3; } }
  .hero h1 { font-family: 'REVERT'; font-size: clamp(36px, 5vw, 58px); font-weight: 900; line-height: 1.1; text-transform: uppercase; letter-spacing: 0.02em; margin-bottom: 16px; }
  .hero h1 .line1 { color: var(--text); display: block; }
  .hero h1 .line2 { display: block; background: linear-gradient(90deg, var(--cyan), var(--purple2)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
  .hero-sub { color: var(--muted); font-size: 16px; font-weight: 300; margin-bottom: 36px; letter-spacing: 0.05em; }
  .hero-sub span { color: var(--cyan); font-weight: 500; }
  .hero-btns { display: flex; gap: 14px; flex-wrap: wrap; }
  .btn-primary { background: linear-gradient(135deg, var(--green), var(--green2)); color: #fff; padding: 14px 32px; border-radius: 10px; border: none; font-family: 'Exo 2', sans-serif; font-size: 15px; font-weight: 700; cursor: pointer; letter-spacing: 0.05em; text-transform: uppercase; transition: all 0.25s; box-shadow: 0 4px 20px rgba(34,197,94,0.35); display: flex; align-items: center; gap: 8px; }
  .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(34,197,94,0.5); }
  .btn-outline { background: transparent; color: var(--cyan); padding: 14px 28px; border-radius: 10px; border: 1.5px solid rgba(0,229,255,0.4); font-family: 'Exo 2', sans-serif; font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.25s; display: flex; align-items: center; gap: 8px; }
  .btn-outline:hover { background: rgba(0,229,255,0.1); border-color: var(--cyan); box-shadow: var(--glow-cyan); }
  .hero-stats { display: flex; gap: 32px; margin-top: 44px; }
  .stat { text-align: left; }
  .stat-num { font-family: 'Orbitron', monospace; font-size: 24px; font-weight: 700; color: var(--cyan); }
  .stat-label { font-size: 11px; color: var(--muted); letter-spacing: 0.08em; text-transform: uppercase; }
  .hero-visual { position: relative; z-index: 2; padding: 40px 60px 40px 0; display: flex; align-items: center; justify-content: center; animation: heroVisual 1s ease both 0.3s; }
  @keyframes heroVisual { from { opacity: 0; transform: translateX(40px) scale(0.95); } to { opacity: 1; transform: translateX(0) scale(1); } }
  .hero-devices { position: relative; width: 480px; height: 360px; }
  .device-glow { position: absolute; inset: -40px; background: radial-gradient(ellipse, rgba(124,58,237,0.3) 0%, transparent 70%); pointer-events: none; }
  
  .device-card { position: absolute; border-radius: 16px; border: 1px solid rgba(0,229,255,0.2); overflow: hidden; backdrop-filter: blur(10px); transition: transform 0.4s ease; cursor: pointer; }
  .device-card:hover { transform: translateY(-8px) scale(1.02) !important; }
  .device-card .dc-inner { background: linear-gradient(135deg, rgba(13,31,56,0.95), rgba(15,36,68,0.9)); padding: 20px; width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; }
  .dc-icon { font-size: 40px; filter: drop-shadow(0 0 10px rgba(0,229,255,0.5)); }
  .dc-name { font-family: 'Orbitron', monospace; font-size: 11px; color: var(--cyan); letter-spacing: 0.1em; text-align: center;}
  .dc-price { font-size: 13px; font-weight: 600; color: var(--text); }
  .scan-line { position: absolute; left: 0; right: 0; height: 2px; background: linear-gradient(90deg, transparent, var(--cyan), transparent); animation: scan 3s linear infinite; pointer-events: none; z-index: 5; opacity: 0.6; }
  @keyframes scan { from { top: 0%; } to { top: 100%; } }

  /* ── SECTION COMMON ── */
  section { padding: 70px 40px; }
  .section-header { text-align: center; margin-bottom: 48px; }
  .section-label { display: inline-block; font-size: 11px; font-weight: 600; letter-spacing: 0.15em; text-transform: uppercase; color: var(--cyan); margin-bottom: 10px; }
  .section-title { font-family: 'Orbitron', monospace; font-size: 28px; font-weight: 700; color: var(--text); }
  .section-title em { color: var(--cyan); font-style: normal; }
  .section-line { width: 60px; height: 3px; background: linear-gradient(90deg, var(--cyan), var(--purple2)); margin: 12px auto 0; border-radius: 2px; }

  /* ── PRODUCTS ── */
  .products { background: #0b1a32; }
  .product-filters { display: flex; gap: 8px; margin-bottom: 32px; flex-wrap: wrap; justify-content: center; }
  .filter-btn { padding: 7px 18px; border-radius: 20px; border: 1px solid var(--border); background: var(--panel); color: var(--muted); font-family: 'Exo 2', sans-serif; font-size: 12px; font-weight: 500; cursor: pointer; transition: all 0.2s; }
  .filter-btn.active, .filter-btn:hover { border-color: var(--cyan); color: var(--cyan); background: rgba(0,229,255,0.08); }
  .products-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
  .product-card { background: var(--panel); border: 1px solid var(--border); border-radius: 16px; overflow: hidden; transition: all 0.35s; cursor: pointer; position: relative; }
  .product-card::after { content: ''; position: absolute; inset: 0; border-radius: 16px; box-shadow: inset 0 0 0 1px var(--cyan); opacity: 0; transition: opacity 0.3s; pointer-events: none; }
  .product-card:hover { transform: translateY(-6px); box-shadow: 0 20px 40px rgba(0,0,0,0.4), 0 0 30px rgba(0,229,255,0.1); }
  .product-card:hover::after { opacity: 1; }

  .product-img-wrap { height: 180px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, var(--panel2), var(--navy3)); padding: 0; position: relative; overflow: hidden; }
  .product-img-wrap img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s; }
  .product-card:hover .product-img-wrap img { transform: scale(1.08); }
  .product-img-wrap::after { content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 60px; background: linear-gradient(transparent, var(--panel)); }
  .product-img { font-size: 72px; filter: drop-shadow(0 0 20px rgba(0,229,255,0.3)); transition: transform 0.3s; }
  .product-card:hover .product-img { transform: scale(1.08); }

  .product-info { padding: 16px; }
  .product-cat { font-size: 10px; font-weight: 600; color: var(--cyan); text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 6px; }
  .product-name { font-size: 14px; font-weight: 600; color: var(--text); margin-bottom: 4px; line-height: 1.3; }
  .product-specs { font-size: 11px; color: var(--muted); margin-bottom: 12px; }
  .product-price-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
  .product-price { font-family: 'Orbitron', monospace; font-size: 16px; font-weight: 700; color: var(--cyan); }
  .product-price-old { font-size: 11px; color: var(--muted); text-decoration: line-through; }
  .product-rating { display: flex; align-items: center; gap: 4px; font-size: 11px; color: #fbbf24; }
  .product-actions { display: flex; gap: 8px; position: relative; z-index: 10;}
  .btn-add { flex: 1; background: var(--green); color: #fff; border: none; border-radius: 8px; padding: 8px; font-family: 'Exo 2', sans-serif; font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
  .btn-add:hover { background: var(--green2); }
  .btn-detail { background: var(--panel2); color: var(--muted); border: 1px solid var(--border); border-radius: 8px; padding: 8px 12px; font-family: 'Exo 2', sans-serif; font-size: 12px; font-weight: 500; cursor: pointer; transition: all 0.2s; }
  .btn-detail:hover { border-color: var(--cyan); color: var(--cyan); }

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

  /* BANNERS, FEATURES, FOOTER */
  .promo-banner { background: linear-gradient(135deg, #0d0f2e, #1a0633, #0d1a40); position: relative; overflow: hidden; padding: 60px 40px; }
  .promo-banner::before { content: ''; position: absolute; inset: 0; background-image: linear-gradient(rgba(0,229,255,0.03) 1px, transparent 1px), linear-gradient(90deg, rgba(0,229,255,0.03) 1px, transparent 1px); background-size: 40px 40px; }
  .promo-inner { position: relative; z-index: 2; display: flex; align-items: center; justify-content: space-between; gap: 40px; }
  .promo-text .promo-tag { font-size: 11px; letter-spacing: 0.15em; color: var(--purple2); text-transform: uppercase; font-weight: 600; margin-bottom: 12px; }
  .promo-text h2 { font-family: 'Orbitron', monospace; font-size: 36px; font-weight: 900; line-height: 1.1; margin-bottom: 12px; }
  .promo-text h2 .hl { color: var(--purple2); }
  .promo-text p { color: var(--muted); font-size: 15px; margin-bottom: 28px; }
  .promo-countdown { display: flex; gap: 16px; margin-bottom: 32px; }
  .countdown-unit { text-align: center; }
  .countdown-num { font-family: 'Orbitron', monospace; font-size: 32px; font-weight: 700; color: var(--purple2); background: rgba(124,58,237,0.15); border: 1px solid rgba(124,58,237,0.3); border-radius: 10px; width: 68px; height: 68px; display: flex; align-items: center; justify-content: center; margin-bottom: 6px; }
  .countdown-label { font-size: 10px; color: var(--muted); text-transform: uppercase; letter-spacing: 0.1em; }
  .promo-visual { font-size: 120px; animation: float 4s ease-in-out infinite; }
  @keyframes float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-16px); } }

  .features { background: var(--navy2); }
  .features-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
  .feature-card { background: var(--panel); border: 1px solid var(--border); border-radius: 16px; padding: 32px; display: flex; align-items: center; gap: 20px; transition: all 0.3s; }
  .feature-card:hover { border-color: var(--cyan); box-shadow: 0 0 30px rgba(0,229,255,0.1); }
  .feature-icon { width: 56px; height: 56px; border-radius: 14px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 26px; }
  .fi1 { background: rgba(0,229,255,0.1); } .fi2 { background: rgba(124,58,237,0.15); } .fi3 { background: rgba(34,197,94,0.1); }
  .feature-text h4 { font-size: 16px; font-weight: 700; color: var(--text); margin-bottom: 6px; }
  .feature-text p { font-size: 13px; color: var(--muted); line-height: 1.5; }

  footer { background: var(--navy2); border-top: 1px solid var(--border); padding: 60px 40px 30px; }
  .footer-grid { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 48px; margin-bottom: 48px; }
  .footer-brand .logo { display: inline-block; margin-bottom: 14px; }
  .footer-brand p { font-size: 13px; color: var(--muted); line-height: 1.7; max-width: 260px; margin-bottom: 20px; }
  .footer-socials { display: flex; gap: 10px; }
  .social-btn { width: 36px; height: 36px; border-radius: 8px; background: var(--panel); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; color: var(--muted); text-decoration: none; font-size: 14px; transition: all 0.2s; }
  .social-btn:hover { border-color: var(--cyan); color: var(--cyan); }
  .footer-col h5 { font-size: 13px; font-weight: 700; color: var(--text); margin-bottom: 16px; letter-spacing: 0.05em; }
  .footer-col ul { list-style: none; display: flex; flex-direction: column; gap: 10px; }
  .footer-col ul a { color: var(--muted); text-decoration: none; font-size: 13px; transition: color 0.2s; }
  .footer-col ul a:hover { color: var(--cyan); }
  .footer-bottom { border-top: 1px solid var(--border); padding-top: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; }
  .footer-bottom p { font-size: 12px; color: var(--muted); }
  .footer-payment { display: flex; gap: 8px; align-items: center; }
  .payment-badge { background: var(--panel); border: 1px solid var(--border); border-radius: 6px; padding: 4px 10px; font-size: 11px; font-weight: 600; color: var(--muted); }

  .fade-in { opacity: 0; transform: translateY(24px); transition: opacity 0.6s ease, transform 0.6s ease; }
  .fade-in.visible { opacity: 1; transform: translateY(0); }

  @media (max-width: 1100px) { .products-grid { grid-template-columns: repeat(3, 1fr); } }
  @media (max-width: 900px) { nav { padding: 0 20px; gap: 16px; } .hero-visual { display: none; } .hero-content { padding: 60px 24px; } .products-grid { grid-template-columns: repeat(2, 1fr); } .features-grid { grid-template-columns: 1fr; } .footer-grid { grid-template-columns: 1fr 1fr; } }
  </style>
</head>
<body>

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
    <button class="btn-cart" onclick="yeuCauDangNhap()">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
      Giỏ Hàng
    </button>
    <form action="DangNhap.php" method="POST">
        <button class="btn-login">Đăng Nhập</button>
    </form>
  </div>
</nav>

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

<section class="products" id="products">
  <div class="section-header fade-in">
    <div class="section-label">// Sản phẩm</div>
    <h2 class="section-title">Sản Phẩm <em>Hot</em></h2>
    <div class="section-line"></div>
  </div>
  
  <?php 
      $madm_filter = isset($_GET['danhmuc']) ? (int)$_GET['danhmuc'] : 0; 
  ?>
  
  <div class="product-filters fade-in" id="bo-loc">
    <a href="TrangChu.php#bo-loc" class="filter-btn <?= ($madm_filter == 0) ? 'active' : '' ?>" style="text-decoration:none; display:inline-block;">Tất Cả</a>
    <a href="TrangChu.php?danhmuc=1#bo-loc" class="filter-btn <?= ($madm_filter == 1) ? 'active' : '' ?>" style="text-decoration:none; display:inline-block;">Laptop</a>
    <a href="TrangChu.php?danhmuc=3#bo-loc" class="filter-btn <?= ($madm_filter == 3) ? 'active' : '' ?>" style="text-decoration:none; display:inline-block;">PC Gaming</a>
    <a href="TrangChu.php?danhmuc=2#bo-loc" class="filter-btn <?= ($madm_filter == 2) ? 'active' : '' ?>" style="text-decoration:none; display:inline-block;">Điện Thoại</a>
    <a href="TrangChu.php?danhmuc=4#bo-loc" class="filter-btn <?= ($madm_filter == 4) ? 'active' : '' ?>" style="text-decoration:none; display:inline-block;">Phụ Kiện</a>
    <a href="TrangChu.php?danhmuc=5#bo-loc" class="filter-btn <?= ($madm_filter == 5) ? 'active' : '' ?>" style="text-decoration:none; display:inline-block;">Gaming Gear</a>
  </div>
  
  <div class="products-grid" id="product-grid">
    <?php
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
                        <span style="color: var(--green); font-weight: bold; font-size: 12px;">✅ Còn lại: <?php echo $row['SoLuongTon']; ?> sản phẩm</span>
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
                        <button class="btn-add" onclick="yeuCauDangNhap()">🛒 Thêm vào giỏ</button>
                    <?php else: ?>
                        <button class="btn-add" style="background: #475569; opacity: 0.6; cursor: not-allowed;" onclick="alert('Rất tiếc! Sản phẩm này hiện đã hết hàng.');">🚫 Hết hàng</button>
                    <?php endif; ?>
                    
                    <button class="btn-detail" onclick="window.location.href='ChiTietSanPham.php?id=<?php echo $row['MaSP']; ?>'">Chi tiết</button>
                </div>
            </div>
        </div>

    <?php 
        } 
    } 
    ?>
  </div> <div id="product-pagination" class="product-pagination"></div>

</section>

<?php
  $now = date('Y-m-d H:i:s');

  $sql_disable = "UPDATE MaGiamGia SET TrangThai = 0 WHERE (NgayHetHan IS NOT NULL AND NgayHetHan <= ?) OR DaDung >= SoLanDung";
  sqlsrv_query($conn, $sql_disable, [$now]);

  $sql_check = "SELECT TOP 1 * FROM MaGiamGia WHERE Code LIKE 'FLASH%' AND TrangThai = 1 AND NgayHetHan > ? ORDER BY MaMGG DESC";
  $stmt_check = sqlsrv_query($conn, $sql_check, [$now]);
  $promo = $stmt_check ? sqlsrv_fetch_array($stmt_check, SQLSRV_FETCH_ASSOC) : null;

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
      
      <button class="btn-primary" style="font-size: 14px; padding: 12px 24px;" onclick="yeuCauDangNhap()">
        💾 Đăng nhập để lưu mã
      </button>
    </div>
    <div class="promo-visual">🎫</div>
  </div>
</section>
<?php endif; ?>

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
// --- CHẶN KHI CHƯA ĐĂNG NHẬP ---
function yeuCauDangNhap() {
    alert("Vui lòng đăng nhập để sử dụng chức năng này!");
    window.location.href = "DangNhap.php";
}

// --- XỬ LÝ CLICK ĐỔI MÀU MENU TRÊN ---
document.querySelectorAll('.nav-links a').forEach(link => {
    link.addEventListener('click', function() {
        document.querySelectorAll('.nav-links a').forEach(a => a.classList.remove('active'));
        this.classList.add('active');
    });
});

// --- HIỆU ỨNG NGÔI SAO ---
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

// --- HIỆU ỨNG CUỘN FADE-IN ---
const observer = new IntersectionObserver(entries => {
  entries.forEach(e => { if(e.isIntersecting) e.target.classList.add('visible'); });
}, { threshold: 0.1 });
document.querySelectorAll('.fade-in').forEach(el => observer.observe(el));

// --- LIVE SEARCH (TÌM KIẾM NGAY) ---
$(document).ready(function(){
    $('#search-input').on('keyup', function() {
        var keyword = $(this).val(); 
        if (keyword.length > 0) {
            $.ajax({
                url: 'tim_kiem_san_pham.php',
                type: 'POST',
                data: { tukhoa: keyword },
                success: function(data) {
                    $('#search-results').html(data).show(); 
                }
            });
        } else {
            $('#search-results').hide(); 
        }
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('.nav-search').length) {
            $('#search-results').hide();
        }
    });
});

// --- HIỆU ỨNG XOAY VÒNG SẢN PHẨM TRÊN BANNER ---
const heroProducts = <?php echo json_encode($hero_products); ?>;
let currentHeroIndex = 0;

function rotateHeroCards() {
    if (!heroProducts || heroProducts.length === 0) return;
    
    for (let i = 0; i < 4; i++) {
        let p = heroProducts[(currentHeroIndex + i) % heroProducts.length];
        if (!p) continue;
        
        let card = document.getElementById('hc-' + i);
        let iconWrap = document.getElementById('hi-' + i);
        let nameDiv = document.getElementById('hn-' + i);
        let priceDiv = document.getElementById('hp-' + i);
        
        card.style.opacity = '0';
        
        setTimeout(() => {
            card.onclick = function() {
                window.location.href = 'ChiTietSanPham.php?id=' + p.MaSP;
            };
            
            let shortName = p.TenSP.length > 16 ? p.TenSP.substring(0, 16) + '...' : p.TenSP;
            nameDiv.innerText = shortName;
            priceDiv.innerText = new Intl.NumberFormat('vi-VN').format(p.Gia) + 'đ';
            
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
            
            card.style.opacity = '1';
        }, 500); 
    }
    
    currentHeroIndex = (currentHeroIndex + 4) % heroProducts.length;
}
rotateHeroCards();
setInterval(rotateHeroCards, 4000);

// --- HÀM ĐẾM NGƯỢC THỜI GIAN THỰC (FLASH SALE) ---
let total = <?= max(0, $time_left) ?>;
function updateCountdown(){
  if(total <= 0) {
      window.location.reload();
      return;
  }
  total--;
  const h = Math.floor(total/3600);
  const m = Math.floor((total%3600)/60);
  const s = total%60;
  
  let cdH = document.getElementById('cd-h');
  let cdM = document.getElementById('cd-m');
  let cdS = document.getElementById('cd-s');
  
  if (cdH) cdH.textContent = String(h).padStart(2,'0');
  if (cdM) cdM.textContent = String(m).padStart(2,'0');
  if (cdS) cdS.textContent = String(s).padStart(2,'0');
}
setInterval(updateCountdown, 1000);
updateCountdown();

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
document.addEventListener('DOMContentLoaded', initProductPagination);
</script>
</body>
</html>
<?php sqlsrv_close($conn); ?>