<!DOCTYPE html>
<html lang="vi">
<head>
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
    background: var(--cyan); color: var(--navy);
    width: 18px; height: 18px; border-radius: 50%;
    font-size: 10px; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
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
  .products { background: var(--navy); }

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
    padding: 20px; position: relative; overflow: hidden;
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
</style>
<body>

<!-- NAV -->
<nav>
  <a class="logo" href="#"><span>KhoaOngNghiem</span><span> TechVN </span></a>
  <div class="nav-links">
    <a href="#" class="active">Trang Chủ</a>
    <a href="#categories">Sản Phẩm</a>
    <a href="#promo">Khuyến Mãi</a>
    <a href="#footer">Liên Hệ</a>
  </div>
  <div class="nav-search">
    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
    <input type="text" placeholder="Tìm kiếm sản phẩm...">
  </div>
  <div class="nav-actions">
    <button class="btn-cart">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
      Giỏ Hàng
      <span class="cart-badge">3</span>
    </button>
     <form action="DangNhap.php" method="POST">
    <button class="btn-login">Đăng Nhập</button>
   
    </form>
    
  </div>
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
      <button class="btn-outline" onclick="document.getElementById('categories').scrollIntoView({behavior:'smooth'})">
        → Xem Danh Mục
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
      <!-- Main laptop -->
      <div class="device-card" style="width:220px;height:160px;left:22%;top:15%">
        <div class="dc-inner">
          <div class="dc-icon">💻</div>
          <div class="dc-name">LAPTOP ROG</div>
          <div class="dc-price">25,000,000đ</div>
        </div>
      </div>
      <!-- Phone -->
      <div class="device-card" style="width:130px;height:110px;right:0;top:10px">
        <div class="dc-inner">
          <div class="dc-icon">📱</div>
          <div class="dc-name">iPhone 15 Pro</div>
          <div class="dc-price">30M đ</div>
        </div>
      </div>
      <!-- Headphone -->
      <div class="device-card" style="width:130px;height:110px;left:0;bottom:20px">
        <div class="dc-inner">
          <div class="dc-icon">🎧</div>
          <div class="dc-name">Gaming Headset</div>
          <div class="dc-price">1,500,000đ</div>
        </div>
      </div>
      <!-- Mouse -->
      <div class="device-card" style="width:120px;height:100px;right:10px;bottom:10px">
        <div class="dc-inner">
          <div class="dc-icon">🖱️</div>
          <div class="dc-name">Logitech G Pro</div>
          <div class="dc-price">1,400,000đ</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CATEGORIES -->
<section class="categories" id="categories">
  <div class="section-header fade-in">
    <div class="section-label">// Khám Phá</div>
    <h2 class="section-title">Danh Mục <em>Nổi Bật</em></h2>
    <div class="section-line"></div>
  </div>
  <div class="cat-grid">
    <div class="cat-card fade-in d1">
      <div class="cat-icon">💻</div>
      <div class="cat-name">Laptop</div>
      <div class="cat-count">245 sản phẩm</div>
    </div>
    <div class="cat-card fade-in d2">
      <div class="cat-icon">🖥️</div>
      <div class="cat-name">PC Gaming</div>
      <div class="cat-count">128 sản phẩm</div>
    </div>
    <div class="cat-card fade-in d3">
      <div class="cat-icon">📱</div>
      <div class="cat-name">Điện Thoại</div>
      <div class="cat-count">310 sản phẩm</div>
    </div>
    <div class="cat-card fade-in d4">
      <div class="cat-icon">🎧</div>
      <div class="cat-name">Phụ Kiện</div>
      <div class="cat-count">520 sản phẩm</div>
    </div>
    <div class="cat-card fade-in d5">
      <div class="cat-icon">🖱️</div>
      <div class="cat-name">Gaming Gear</div>
      <div class="cat-count">180 sản phẩm</div>
    </div>
  </div>
</section>

<!-- PRODUCTS -->
<section class="products" id="products">
  <div class="section-header fade-in">
    <div class="section-label">// Sản phẩm</div>
    <h2 class="section-title">Sản Phẩm <em>Hot</em></h2>
    <div class="section-line"></div>
  </div>
  <div class="product-filters fade-in">
    <button class="filter-btn active">Tất Cả</button>
    <button class="filter-btn">Laptop</button>
    <button class="filter-btn">PC Gaming</button>
    <button class="filter-btn">Điện Thoại</button>
    <button class="filter-btn">Phụ Kiện</button>
    <button class="filter-btn">Gaming Gear</button>
  </div>
  <div class="products-grid">
    <div class="product-card fade-in d1">
      <span class="product-badge badge-hot">HOT</span>
      <div class="product-img-wrap"><div class="product-img">💻</div></div>
      <div class="product-info">
        <div class="product-cat">Laptop</div>
        <div class="product-name">ASUS ROG Strix G16</div>
        <div class="product-specs">i9-14900H · RTX 4070 · 16GB · 1TB</div>
        <div class="product-price-row">
          <div>
            <div class="product-price">25,000,000đ</div>
            <div class="product-price-old">29,000,000đ</div>
          </div>
          <div class="product-rating">★★★★★ <span style="color:var(--muted)">(128)</span></div>
        </div>
        <div class="product-actions">
          <button class="btn-add">🛒 Thêm vào giỏ</button>
          <button class="btn-detail">Chi tiết</button>
        </div>
      </div>
    </div>
    <div class="product-card fade-in d2">
      <span class="product-badge badge-new">MỚI</span>
      <div class="product-img-wrap"><div class="product-img">📱</div></div>
      <div class="product-info">
        <div class="product-cat">Điện Thoại</div>
        <div class="product-name">iPhone 15 Pro Max</div>
        <div class="product-specs">A17 Pro · 256GB · Titanium · 5G</div>
        <div class="product-price-row">
          <div>
            <div class="product-price">30,000,000đ</div>
            <div class="product-price-old">34,000,000đ</div>
          </div>
          <div class="product-rating">★★★★★ <span style="color:var(--muted)">(256)</span></div>
        </div>
        <div class="product-actions">
          <button class="btn-add">🛒 Thêm vào giỏ</button>
          <button class="btn-detail">Chi tiết</button>
        </div>
      </div>
    </div>
    <div class="product-card fade-in d3">
      <span class="product-badge badge-sale">SALE</span>
      <div class="product-img-wrap"><div class="product-img">🎧</div></div>
      <div class="product-info">
        <div class="product-cat">Phụ Kiện</div>
        <div class="product-name">Tai Nghe Gaming 7.1</div>
        <div class="product-specs">7.1 Surround · RGB · USB · Noise Cancel</div>
        <div class="product-price-row">
          <div>
            <div class="product-price">1,500,000đ</div>
            <div class="product-price-old">2,200,000đ</div>
          </div>
          <div class="product-rating">★★★★☆ <span style="color:var(--muted)">(89)</span></div>
        </div>
        <div class="product-actions">
          <button class="btn-add">🛒 Thêm vào giỏ</button>
          <button class="btn-detail">Chi tiết</button>
        </div>
      </div>
    </div>
    <div class="product-card fade-in d4">
      <div class="product-img-wrap"><div class="product-img">🖱️</div></div>
      <div class="product-info">
        <div class="product-cat">Gaming Gear</div>
        <div class="product-name">Chuột Logitech G Pro X</div>
        <div class="product-specs">25,600 DPI · Wireless · 60h · HERO Sensor</div>
        <div class="product-price-row">
          <div>
            <div class="product-price">1,400,000đ</div>
            <div class="product-price-old">1,800,000đ</div>
          </div>
          <div class="product-rating">★★★★★ <span style="color:var(--muted)">(312)</span></div>
        </div>
        <div class="product-actions">
          <button class="btn-add">🛒 Thêm vào giỏ</button>
          <button class="btn-detail">Chi tiết</button>
        </div>
      </div>
    </div>
    <div class="product-card fade-in d1">
      <span class="product-badge badge-hot">HOT</span>
      <div class="product-img-wrap"><div class="product-img">🖥️</div></div>
      <div class="product-info">
        <div class="product-cat">PC Gaming</div>
        <div class="product-name">PC Gaming RTX 4090</div>
        <div class="product-specs">i9-14900K · RTX 4090 · 64GB · 4TB NVMe</div>
        <div class="product-price-row">
          <div>
            <div class="product-price">85,000,000đ</div>
            <div class="product-price-old">95,000,000đ</div>
          </div>
          <div class="product-rating">★★★★★ <span style="color:var(--muted)">(45)</span></div>
        </div>
        <div class="product-actions">
          <button class="btn-add">🛒 Thêm vào giỏ</button>
          <button class="btn-detail">Chi tiết</button>
        </div>
      </div>
    </div>
    <div class="product-card fade-in d2">
      <span class="product-badge badge-new">MỚI</span>
      <div class="product-img-wrap"><div class="product-img">⌨️</div></div>
      <div class="product-info">
        <div class="product-cat">Gaming Gear</div>
        <div class="product-name">Bàn Phím Mechanical RGB</div>
        <div class="product-specs">Cherry MX Red · TKL · RGB · PBT Keycaps</div>
        <div class="product-price-row">
          <div>
            <div class="product-price">2,200,000đ</div>
            <div class="product-price-old">2,800,000đ</div>
          </div>
          <div class="product-rating">★★★★☆ <span style="color:var(--muted)">(167)</span></div>
        </div>
        <div class="product-actions">
          <button class="btn-add">🛒 Thêm vào giỏ</button>
          <button class="btn-detail">Chi tiết</button>
        </div>
      </div>
    </div>
    <div class="product-card fade-in d3">
      <div class="product-img-wrap"><div class="product-img">📺</div></div>
      <div class="product-info">
        <div class="product-cat">Phụ Kiện</div>
        <div class="product-name">Màn Hình 4K 144Hz</div>
        <div class="product-specs">27" IPS · 4K · 144Hz · HDR600 · 1ms</div>
        <div class="product-price-row">
          <div>
            <div class="product-price">12,000,000đ</div>
            <div class="product-price-old">15,000,000đ</div>
          </div>
          <div class="product-rating">★★★★★ <span style="color:var(--muted)">(78)</span></div>
        </div>
        <div class="product-actions">
          <button class="btn-add">🛒 Thêm vào giỏ</button>
          <button class="btn-detail">Chi tiết</button>
        </div>
      </div>
    </div>
    <div class="product-card fade-in d4">
      <span class="product-badge badge-sale">SALE</span>
      <div class="product-img-wrap"><div class="product-img">🎮</div></div>
      <div class="product-info">
        <div class="product-cat">Gaming Gear</div>
        <div class="product-name">Tay Cầm Xbox Series X</div>
        <div class="product-specs">Bluetooth 5.0 · USB-C · AA battery · 40h</div>
        <div class="product-price-row">
          <div>
            <div class="product-price">1,800,000đ</div>
            <div class="product-price-old">2,400,000đ</div>
          </div>
          <div class="product-rating">★★★★☆ <span style="color:var(--muted)">(203)</span></div>
        </div>
        <div class="product-actions">
          <button class="btn-add">🛒 Thêm vào giỏ</button>
          <button class="btn-detail">Chi tiết</button>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- PROMO BANNER -->
<section class="promo-banner" id="promo">
  <div class="promo-inner">
    <div class="promo-text">
      <div class="promo-tag">⚡ Flash Sale · Giới hạn thời gian</div>
      <h2>GIẢM GIÁ <span class="hl">40%</span><br>CHO MỌI LAPTOP</h2>
      <p>Chương trình khuyến mãi đặc biệt, số lượng có hạn — Đừng bỏ lỡ!</p>
      <div class="promo-countdown">
        <div class="countdown-unit"><div class="countdown-num" id="cd-h">08</div><div class="countdown-label">Giờ</div></div>
        <div class="countdown-unit"><div class="countdown-num" id="cd-m">34</div><div class="countdown-label">Phút</div></div>
        <div class="countdown-unit"><div class="countdown-num" id="cd-s">22</div><div class="countdown-label">Giây</div></div>
      </div>
      <button class="btn-primary">⚡ Xem Ngay</button>
    </div>
    <div class="promo-visual">💻</div>
  </div>
</section>

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
      <a class="logo"><span>TECH</span><span> STORE</span></a>
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
// ── Star field
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
document.querySelectorAll('.filter-btn').forEach(btn => {
  btn.addEventListener('click', function(){
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    this.classList.add('active');
  });
});

// ── Countdown timer
let total = 8*3600 + 34*60 + 22;
function updateCountdown(){
  total = Math.max(0, total-1);
  const h = Math.floor(total/3600);
  const m = Math.floor((total%3600)/60);
  const s = total%60;
  document.getElementById('cd-h').textContent = String(h).padStart(2,'0');
  document.getElementById('cd-m').textContent = String(m).padStart(2,'0');
  document.getElementById('cd-s').textContent = String(s).padStart(2,'0');
}
setInterval(updateCountdown, 1000);

// ── Add to cart
document.querySelectorAll('.btn-add').forEach(btn => {
  btn.addEventListener('click', function(){
    const badge = document.querySelector('.cart-badge');
    badge.textContent = parseInt(badge.textContent) + 1;
    this.textContent = '✓ Đã thêm!';
    this.style.background = '#059669';
    setTimeout(() => { this.textContent = '🛒 Thêm vào giỏ'; this.style.background = ''; }, 1500);
  });
});
</script>

<script src="js/trangchu.js"></script>
</body>
</html>