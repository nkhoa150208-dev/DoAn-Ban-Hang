<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tiền Ngu — Đồ Công Nghệ Fake</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700;900&family=Exo+2:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="css/trangchu.css" rel="stylesheet">
</head>
<body>

<!-- NAV -->
<nav>
  <a class="logo" href="#"><span>Tiền</span><span> Store </span></a>
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
    <button class="btn-login">Đăng Nhập</button>
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

<script src="js/trangchu.js"></script>
</body>
</html>