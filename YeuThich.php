<?php
$serverName = "localhost\\SQLEXPRESS";
$connectionInfo = ["Database"=>"QLBanHang","TrustServerCertificate"=>true,"CharacterSet"=>"UTF-8"];
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) die(print_r(sqlsrv_errors(), true));
session_start();
if (!isset($_SESSION['MaND'])) { header('Location: DangNhap.php'); exit; }
$user_id = (int)$_SESSION['MaND'];

// Xoa yeu thich
if (isset($_GET['xoa'])) {
    sqlsrv_query($conn, "DELETE FROM YeuThich WHERE MaND=? AND MaSP=?", [$user_id, (int)$_GET['xoa']]);
    header('Location: YeuThich.php'); exit;
}

$dsYT = sqlsrv_query($conn,
    "SELECT yt.MaSP, sp.TenSP, sp.Gia, sp.HinhAnh, sp.MoTa FROM YeuThich yt
     JOIN SanPham sp ON yt.MaSP=sp.MaSP WHERE yt.MaND=?", [$user_id]);
?>
<!DOCTYPE html><html lang="vi"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Yeu Thich</title>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--bg:#0f0f13;--s1:#1a1a24;--s2:#22222f;--bd:#2e2e40;--p:#6366f1;--ac:#ec4899;--tx:#e2e2f0;--mu:#888899;--r:14px}
body{font-family:'Segoe UI',sans-serif;background:var(--bg);color:var(--tx);padding:24px 16px 60px}
.topbar{max-width:1000px;margin:0 auto 24px;display:flex;align-items:center;gap:12px}
.logo{font-size:22px;font-weight:800;background:linear-gradient(135deg,var(--p),var(--ac));-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.back{margin-left:auto;text-decoration:none;color:var(--mu);font-size:13px;padding:8px 14px;border:1px solid var(--bd);border-radius:8px;transition:.2s}
.back:hover{color:var(--tx);border-color:var(--p)}
.wrap{max-width:1000px;margin:0 auto}
.card{background:var(--s1);border:1px solid var(--bd);border-radius:var(--r);padding:24px}
.sec-title{font-size:17px;font-weight:700;margin-bottom:20px;display:flex;align-items:center;gap:8px}
.sec-title::after{content:'';flex:1;height:1px;background:var(--bd)}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px}
.sp-card{background:var(--s2);border:1px solid var(--bd);border-radius:12px;overflow:hidden;transition:.2s}
.sp-card:hover{border-color:var(--p);transform:translateY(-2px)}
.sp-img{width:100%;height:160px;object-fit:cover;background:var(--bd)}
.sp-body{padding:12px}
.sp-name{font-size:14px;font-weight:600;margin-bottom:4px}
.sp-desc{font-size:12px;color:var(--mu);margin-bottom:8px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.sp-price{font-weight:700;color:var(--p);font-size:15px;margin-bottom:10px}
.btn-xoa{width:100%;padding:8px;border-radius:8px;border:1.5px solid var(--ac);color:var(--ac);background:transparent;font-size:12px;font-weight:600;cursor:pointer;text-decoration:none;display:block;text-align:center;transition:.2s}
.btn-xoa:hover{background:var(--ac);color:#fff}
.empty{text-align:center;padding:60px;color:var(--mu)}.empty-icon{font-size:48px;margin-bottom:12px}
</style></head><body>
<div class="topbar">
  <div class="logo">&#x1F6CD; QLBanHang</div>
  <a href="ChinhSuaProfile.php" class="back">&#x2190; Ho so</a>
</div>
<div class="wrap">
  <div class="card">
    <div class="sec-title">&#x2764; San pham yeu thich</div>
    <?php if ($dsYT && sqlsrv_has_rows($dsYT)): ?>
      <div class="grid">
      <?php while ($sp = sqlsrv_fetch_array($dsYT, SQLSRV_FETCH_ASSOC)): ?>
        <div class="sp-card">
          <img class="sp-img" src="<?= htmlspecialchars($sp['HinhAnh'] ?? '') ?>" alt="" onerror="this.style.display='none'">
          <div class="sp-body">
            <div class="sp-name"><?= htmlspecialchars($sp['TenSP']) ?></div>
            <div class="sp-desc"><?= htmlspecialchars($sp['MoTa'] ?? '') ?></div>
            <div class="sp-price"><?= number_format($sp['Gia'],0,',','.') ?>d</div>
            <a href="?xoa=<?= $sp['MaSP'] ?>" class="btn-xoa" onclick="return confirm('Xoa khoi yeu thich?')">&#x1F5D1; Xoa</a>
          </div>
        </div>
      <?php endwhile; ?>
      </div>
    <?php else: ?>
      <div class="empty"><div class="empty-icon">&#x2764;</div>Ban chua co san pham yeu thich nao</div>
    <?php endif; ?>
  </div>
</div>
</body></html>
<?php sqlsrv_close($conn); ?>