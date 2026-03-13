<?php
$serverName = "localhost\\SQLEXPRESS";
$connectionInfo = ["Database"=>"QLBanHang","TrustServerCertificate"=>true,"CharacterSet"=>"UTF-8"];
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) die(print_r(sqlsrv_errors(), true));
session_start();
if (!isset($_SESSION['MaND'])) { header('Location: DangNhap.php'); exit; }
$user_id = (int)$_SESSION['MaND'];

$success = ''; $error = '';

// XU LY YEU CAU HUY DON
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action']??'') === 'yeu_cau_huy') {
    $maDH  = (int)($_POST['MaDH'] ?? 0);
    $lyDo  = trim($_POST['LyDoHuy'] ?? '');
    if (empty($lyDo)) {
        $error = 'Vui long nhap ly do huy don!';
    } else {
        // Kiem tra don nay co phai cua user nay khong va dang o trang thai cho xu ly
        $chk = sqlsrv_query($conn,
            "SELECT TrangThai FROM DonHang WHERE MaDH=? AND MaND=?",
            [$maDH, $user_id]);
        $row = $chk ? sqlsrv_fetch_array($chk, SQLSRV_FETCH_ASSOC) : null;
        if (!$row) {
            $error = 'Don hang khong hop le!';
        } elseif ($row['TrangThai'] !== 'Chờ xử lý') {
            $error = 'Chi co the yeu cau huy don dang o trang thai "Cho xu ly"!';
        } else {
            $stmt = sqlsrv_query($conn,
                "UPDATE DonHang SET TrangThai=N'Chờ xác nhận hủy', LyDoHuy=? WHERE MaDH=? AND MaND=?",
                [$lyDo, $maDH, $user_id]);
            if ($stmt) $success = "Da gui yeu cau huy don #$maDH. Vui long cho admin xac nhan!";
            else $error = 'Loi gui yeu cau!';
        }
    }
}

$dsDH = sqlsrv_query($conn, "SELECT * FROM DonHang WHERE MaND=? ORDER BY NgayDat DESC", [$user_id]);
$chiTiet = []; $maDHChon = null; $donChon = null;
if (isset($_GET['id'])) {
    $maDHChon = (int)$_GET['id'];
    $rsCT = sqlsrv_query($conn,
        "SELECT ct.*, sp.TenSP, sp.HinhAnh FROM ChiTietDonHang ct JOIN SanPham sp ON ct.MaSP=sp.MaSP WHERE ct.MaDH=?",
        [$maDHChon]);
    while ($row = sqlsrv_fetch_array($rsCT, SQLSRV_FETCH_ASSOC)) $chiTiet[] = $row;
    $rsDon = sqlsrv_query($conn, "SELECT * FROM DonHang WHERE MaDH=? AND MaND=?", [$maDHChon, $user_id]);
    $donChon = $rsDon ? sqlsrv_fetch_array($rsDon, SQLSRV_FETCH_ASSOC) : null;
}

$ttColor = [
    'Chờ xử lý'        => '#f59e0b',
    'Đang giao'         => '#6366f1',
    'Đã giao'           => '#22c55e',
    'Đã hủy'            => '#ef4444',
    'Chờ xác nhận hủy'  => '#f97316',
];
?>
<!DOCTYPE html><html lang="vi"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Don Hang Cua Toi</title>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--bg:#0f0f13;--s1:#1a1a24;--s2:#22222f;--bd:#2e2e40;--p:#6366f1;--ac:#ec4899;--tx:#e2e2f0;--mu:#888899;--r:14px;--orange:#f97316}
body{font-family:'Segoe UI',sans-serif;background:var(--bg);color:var(--tx);padding:24px 16px 60px}
.topbar{max-width:1000px;margin:0 auto 24px;display:flex;align-items:center;gap:12px}
.logo{font-size:22px;font-weight:800;background:linear-gradient(135deg,var(--p),var(--ac));-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.back{margin-left:auto;text-decoration:none;color:var(--mu);font-size:13px;padding:8px 14px;border:1px solid var(--bd);border-radius:8px;transition:.2s}
.back:hover{color:var(--tx);border-color:var(--p)}
.wrap{max-width:1000px;margin:0 auto;display:grid;gap:20px}
.card{background:var(--s1);border:1px solid var(--bd);border-radius:var(--r);padding:24px}
.sec-title{font-size:17px;font-weight:700;margin-bottom:20px;display:flex;align-items:center;gap:8px}
.sec-title::after{content:'';flex:1;height:1px;background:var(--bd)}
.order-item{background:var(--s2);border:1px solid var(--bd);border-radius:10px;padding:16px;margin-bottom:12px;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;transition:.2s}
.order-item:hover{border-color:rgba(99,102,241,.4)}
.order-item.pending-cancel{border-color:rgba(249,115,22,.35);background:rgba(249,115,22,.04)}
.order-id{font-weight:700;font-size:15px}.order-date{font-size:12px;color:var(--mu);margin-top:3px}
.order-total{font-weight:700;color:var(--p);font-size:16px}
.badge{padding:4px 12px;border-radius:20px;font-size:11px;font-weight:700;white-space:nowrap}
.btn-det{padding:7px 16px;border-radius:8px;font-size:12px;font-weight:600;border:1.5px solid var(--p);color:var(--p);background:transparent;text-decoration:none;transition:.2s;white-space:nowrap}
.btn-det:hover,.btn-det.act{background:var(--p);color:#fff}
.btn-huy{padding:7px 14px;border-radius:8px;font-size:12px;font-weight:600;border:1.5px solid var(--orange);color:var(--orange);background:transparent;cursor:pointer;transition:.2s;white-space:nowrap;font-family:'Segoe UI',sans-serif}
.btn-huy:hover{background:var(--orange);color:#fff}
.ct-item{display:flex;gap:14px;align-items:center;padding:12px 0;border-bottom:1px solid var(--bd)}
.ct-item:last-child{border-bottom:none}
.ct-img{width:60px;height:60px;border-radius:8px;object-fit:cover;background:var(--s2);border:1px solid var(--bd);flex-shrink:0}
.ct-name{font-size:14px;font-weight:600}.ct-info{font-size:12px;color:var(--mu);margin-top:4px}
.ct-price{margin-left:auto;font-weight:700;color:var(--p);white-space:nowrap}
.empty{text-align:center;padding:40px;color:var(--mu)}.empty-icon{font-size:48px;margin-bottom:12px}
.al{padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600}
.ok{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.3);color:#4ade80}
.er{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#f87171}
.cancel-note{font-size:12px;color:var(--orange);margin-top:6px;padding:6px 10px;background:rgba(249,115,22,.08);border-radius:6px;border:1px solid rgba(249,115,22,.2)}

/* MODAL */
.modal-bg{display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:9999;align-items:center;justify-content:center;backdrop-filter:blur(6px)}
.modal-box{background:#1a1a24;border:1px solid var(--bd);border-radius:14px;padding:28px;width:90%;max-width:460px}
.modal-title{font-size:16px;font-weight:700;margin-bottom:6px;color:#f97316}
.modal-sub{font-size:13px;color:var(--mu);margin-bottom:18px}
.modal-box textarea{width:100%;background:#22222f;border:1.5px solid var(--bd);border-radius:8px;color:var(--tx);font-family:'Segoe UI',sans-serif;font-size:13px;padding:10px 14px;outline:none;resize:vertical;min-height:90px;transition:.2s}
.modal-box textarea:focus{border-color:#f97316}
.modal-box textarea::placeholder{color:var(--mu)}
.modal-actions{display:flex;gap:10px;margin-top:14px;justify-content:flex-end}
.btn-cancel-modal{padding:9px 20px;border-radius:8px;background:transparent;border:1px solid var(--bd);color:var(--mu);font-size:13px;font-weight:600;cursor:pointer;transition:.2s;font-family:'Segoe UI',sans-serif}
.btn-cancel-modal:hover{color:var(--tx);border-color:var(--tx)}
.btn-confirm-huy{padding:9px 20px;border-radius:8px;background:#f97316;border:none;color:#fff;font-size:13px;font-weight:700;cursor:pointer;transition:.2s;font-family:'Segoe UI',sans-serif}
.btn-confirm-huy:hover{background:#ea6c0a;box-shadow:0 4px 12px rgba(249,115,22,.4)}
</style></head><body>

<div class="topbar">
  <div class="logo">&#x1F6CD; QLBanHang</div>
  <a href="ChinhSuaProfile.php" class="back">&#x2190; Ho so</a>
</div>

<div class="wrap">
  <div class="card">
    <div class="sec-title">&#x1F4E6; Don hang cua toi</div>
    <?php if ($success): ?><div class="al ok">&#x2705; <?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error):   ?><div class="al er">&#x274C; <?= htmlspecialchars($error) ?></div><?php endif; ?>

    <?php if ($dsDH && sqlsrv_has_rows($dsDH)): ?>
      <?php while ($dh = sqlsrv_fetch_array($dsDH, SQLSRV_FETCH_ASSOC)): ?>
        <?php
          $c = $ttColor[$dh['TrangThai']] ?? '#888899';
          $ngay = ($dh['NgayDat'] instanceof DateTime) ? $dh['NgayDat']->format('d/m/Y') : '';
          $isPendingCancel = ($dh['TrangThai'] === 'Chờ xác nhận hủy');
          $canCancel = ($dh['TrangThai'] === 'Chờ xử lý');
        ?>
        <div class="order-item <?= $isPendingCancel ? 'pending-cancel' : '' ?>">
          <div>
            <div class="order-id">Don #<?= $dh['MaDH'] ?></div>
            <div class="order-date"><?= $ngay ?></div>
            <?php if ($isPendingCancel && !empty($dh['LyDoHuy'])): ?>
              <div class="cancel-note">&#x23F3; Dang cho admin duyet: "<?= htmlspecialchars($dh['LyDoHuy']) ?>"</div>
            <?php endif; ?>
          </div>
          <div class="badge" style="background:<?= $c ?>22;color:<?= $c ?>;border:1px solid <?= $c ?>55">
            <?= htmlspecialchars($dh['TrangThai']) ?>
          </div>
          <div class="order-total"><?= number_format($dh['TongTien'],0,',','.') ?>d</div>
          <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
            <?php if ($canCancel): ?>
              <button class="btn-huy" onclick="openHuy(<?= $dh['MaDH'] ?>)">&#x274C; Yeu cau huy</button>
            <?php endif; ?>
            <a href="?id=<?= $dh['MaDH'] ?>" class="btn-det <?= $maDHChon==$dh['MaDH']?'act':'' ?>">Chi tiet</a>
          </div>
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

<!-- MODAL YEU CAU HUY -->
<div id="modalHuy" class="modal-bg">
  <div class="modal-box">
    <div class="modal-title">&#x274C; Yeu cau huy don hang</div>
    <div class="modal-sub" id="modalSub">Don #<span id="modalMaDH"></span></div>
    <form method="post">
      <input type="hidden" name="action" value="yeu_cau_huy">
      <input type="hidden" name="MaDH" id="inputMaDH">
      <textarea name="LyDoHuy" placeholder="Vui long neu ro ly do ban muon huy don hang nay..." required></textarea>
      <div class="modal-actions">
        <button type="button" class="btn-cancel-modal" onclick="closeHuy()">Huy bo</button>
        <button type="submit" class="btn-confirm-huy">Gui yeu cau huy</button>
      </div>
    </form>
  </div>
</div>

<script>
function openHuy(id) {
  document.getElementById('modalMaDH').textContent = id;
  document.getElementById('inputMaDH').value = id;
  document.getElementById('modalHuy').style.display = 'flex';
}
function closeHuy() {
  document.getElementById('modalHuy').style.display = 'none';
}
document.getElementById('modalHuy').addEventListener('click', function(e) {
  if (e.target === this) closeHuy();
});
</script>
</body></html>
<?php sqlsrv_close($conn); ?>