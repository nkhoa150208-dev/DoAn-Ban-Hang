<?php
$serverName = "localhost\\SQLEXPRESS";
$connectionInfo = ["Database"=>"QLBanHang","TrustServerCertificate"=>true,"CharacterSet"=>"UTF-8"];
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) die(print_r(sqlsrv_errors(), true));
session_start();
if (!isset($_SESSION['MaND'])) { header('Location: DangNhap.php'); exit; }
$user_id = (int)$_SESSION['MaND'];

$dsDH = sqlsrv_query($conn, "SELECT * FROM DonHang WHERE MaND=? ORDER BY NgayDat DESC", [$user_id]);
$chiTiet = []; $maDHChon = null;
if (isset($_GET['id'])) {
    $maDHChon = (int)$_GET['id'];
    $rsCT = sqlsrv_query($conn,
        "SELECT ct.*, sp.TenSP, sp.HinhAnh FROM ChiTietDonHang ct JOIN SanPham sp ON ct.MaSP=sp.MaSP WHERE ct.MaDH=?",
        [$maDHChon]);
    while ($row = sqlsrv_fetch_array($rsCT, SQLSRV_FETCH_ASSOC)) $chiTiet[] = $row;
}
$ttColor = ['Cho xu ly'=>'#f59e0b','Dang giao'=>'#6366f1','Da giao'=>'#22c55e','Da huy'=>'#ef4444'];
?>
<!DOCTYPE html><html lang="vi"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Don Hang</title>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--bg:#0f0f13;--s1:#1a1a24;--s2:#22222f;--bd:#2e2e40;--p:#6366f1;--ac:#ec4899;--tx:#e2e2f0;--mu:#888899;--r:14px}
body{font-family:'Segoe UI',sans-serif;background:var(--bg);color:var(--tx);padding:24px 16px 60px}
.topbar{max-width:1000px;margin:0 auto 24px;display:flex;align-items:center;gap:12px}
.logo{font-size:22px;font-weight:800;background:linear-gradient(135deg,var(--p),var(--ac));-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.back{margin-left:auto;text-decoration:none;color:var(--mu);font-size:13px;padding:8px 14px;border:1px solid var(--bd);border-radius:8px;transition:.2s}
.back:hover{color:var(--tx);border-color:var(--p)}
.wrap{max-width:1000px;margin:0 auto;display:grid;gap:20px}
.card{background:var(--s1);border:1px solid var(--bd);border-radius:var(--r);padding:24px}
.sec-title{font-size:17px;font-weight:700;margin-bottom:20px;display:flex;align-items:center;gap:8px}
.sec-title::after{content:'';flex:1;height:1px;background:var(--bd)}
.order-item{background:var(--s2);border:1px solid var(--bd);border-radius:10px;padding:16px;margin-bottom:12px;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap}
.order-id{font-weight:700;font-size:15px}.order-date{font-size:12px;color:var(--mu);margin-top:3px}
.order-total{font-weight:700;color:var(--p);font-size:16px}
.badge{padding:4px 12px;border-radius:20px;font-size:11px;font-weight:700}
.btn-det{padding:7px 16px;border-radius:8px;font-size:12px;font-weight:600;border:1.5px solid var(--p);color:var(--p);background:transparent;text-decoration:none;transition:.2s}
.btn-det:hover,.btn-det.act{background:var(--p);color:#fff}
.ct-item{display:flex;gap:14px;align-items:center;padding:12px 0;border-bottom:1px solid var(--bd)}
.ct-item:last-child{border-bottom:none}
.ct-img{width:60px;height:60px;border-radius:8px;object-fit:cover;background:var(--s2);border:1px solid var(--bd);flex-shrink:0}
.ct-name{font-size:14px;font-weight:600}.ct-info{font-size:12px;color:var(--mu);margin-top:4px}
.ct-price{margin-left:auto;font-weight:700;color:var(--p);white-space:nowrap}
.empty{text-align:center;padding:40px;color:var(--mu)}.empty-icon{font-size:48px;margin-bottom:12px}
</style></head><body>
<div class="topbar">
  <div class="logo">&#x1F6CD; QLBanHang</div>
  <a href="ChinhSuaProfile.php" class="back">&#x2190; Ho so</a>
</div>
<div class="wrap">
  <div class="card">
    <div class="sec-title">&#x1F4E6; Don hang cua toi</div>
    <?php if ($dsDH && sqlsrv_has_rows($dsDH)): ?>
      <?php while ($dh = sqlsrv_fetch_array($dsDH, SQLSRV_FETCH_ASSOC)): ?>
        <?php $c = $ttColor[$dh['TrangThai']] ?? '#888899';
              $ngay = ($dh['NgayDat'] instanceof DateTime) ? $dh['NgayDat']->format('d/m/Y') : ''; ?>
        <div class="order-item">
          <div><div class="order-id">Don #<?= $dh['MaDH'] ?></div><div class="order-date"><?= $ngay ?></div></div>
          <div class="badge" style="background:<?= $c ?>22;color:<?= $c ?>;border:1px solid <?= $c ?>55"><?= htmlspecialchars($dh['TrangThai']) ?></div>
          <div class="order-total"><?= number_format($dh['TongTien'],0,',','.') ?>d</div>
          <a href="?id=<?= $dh['MaDH'] ?>" class="btn-det <?= $maDHChon==$dh['MaDH']?'act':'' ?>">Chi tiet</a>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div class="empty"><div class="empty-icon">&#x1F4E6;</div>Ban chua co don hang nao</div>
    <?php endif; ?>
  </div>
  <?php if ($maDHChon && count($chiTiet)): ?>
  <div class="card">
    <div class="sec-title">&#x1F4CB; Chi tiet don #<?= $maDHChon ?></div>
    <?php foreach ($chiTiet as $ct): ?>
      <div class="ct-item">
        <img class="ct-img" src="<?= htmlspecialchars($ct['HinhAnh'] ?? '') ?>" alt="" onerror="this.style.display='none'">
        <div>
          <div class="ct-name"><?= htmlspecialchars($ct['TenSP']) ?></div>
          <div class="ct-info">SL: <?= $ct['SoLuong'] ?> | Don gia: <?= number_format($ct['DonGia'],0,',','.') ?>d</div>
        </div>
        <div class="ct-price"><?= number_format($ct['SoLuong']*$ct['DonGia'],0,',','.') ?>d</div>
      </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
</body></html>
<?php sqlsrv_close($conn); ?>