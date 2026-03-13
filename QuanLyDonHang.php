<?php
session_start();
$serverName = "localhost\\SQLEXPRESS";
$connectionInfo = ["Database"=>"QLBanHang","TrustServerCertificate"=>true,"CharacterSet"=>"UTF-8"];
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) die(print_r(sqlsrv_errors(), true));
if (!isset($_SESSION['MaND'])) { header('Location: DangNhap.php'); exit; }
$user_id = (int)$_SESSION['MaND'];
$res_u = sqlsrv_query($conn, "SELECT * FROM dbo.NguoiDung WHERE MaND=?", [$user_id]);
$user  = sqlsrv_fetch_array($res_u, SQLSRV_FETCH_ASSOC);
if ($user['VaiTro'] == 0) { header('Location: TrangChuDaDangNhap.php'); exit; }

$success = ''; $error = '';

// CAP NHAT TRANG THAI THUONG
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action']??'') === 'update_status') {
    $maDH = (int)$_POST['MaDH'];
    $tt   = $_POST['TrangThai'];
    $stmt = sqlsrv_query($conn, "UPDATE DonHang SET TrangThai=? WHERE MaDH=?", [$tt, $maDH]);
    if ($stmt) $success = "Da cap nhat don hang #$maDH thanh: $tt";
    else $error = "Loi cap nhat!";
}

// DONG Y HUY
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action']??'') === 'duyet_huy') {
    $maDH = (int)$_POST['MaDH'];
    $stmt = sqlsrv_query($conn, "UPDATE DonHang SET TrangThai=N'Đã hủy' WHERE MaDH=?", [$maDH]);
    if ($stmt) $success = "Da dong y huy don hang #$maDH!";
    else $error = "Loi!";
}

// TU CHOI HUY
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action']??'') === 'tu_choi_huy') {
    $maDH = (int)$_POST['MaDH'];
    $stmt = sqlsrv_query($conn, "UPDATE DonHang SET TrangThai=N'Chờ xử lý', LyDoHuy=NULL WHERE MaDH=?", [$maDH]);
    if ($stmt) $success = "Da tu choi yeu cau huy don #$maDH, don hang khoi phuc ve 'Cho xu ly'!";
    else $error = "Loi!";
}

// LOC
$filterTT = $_GET['tt'] ?? '';
$search   = trim($_GET['q'] ?? '');
$sql = "SELECT dh.*, nd.TenDangNhap FROM DonHang dh LEFT JOIN NguoiDung nd ON dh.MaND=nd.MaND WHERE 1=1";
$params = [];
if ($filterTT !== '') { $sql .= " AND dh.TrangThai=?"; $params[] = $filterTT; }
if ($search !== '')   { $sql .= " AND (dh.HoTen LIKE ? OR CAST(dh.MaDH AS VARCHAR)=?)"; $params[] = "%$search%"; $params[] = $search; }
$sql .= " ORDER BY CASE WHEN dh.TrangThai=N'Chờ xác nhận hủy' THEN 0 ELSE 1 END, dh.NgayDat DESC";
$stmt_dh = sqlsrv_query($conn, $sql, $params ?: []);

// Dem don cho xac nhan huy
$cntHuy = sqlsrv_query($conn, "SELECT COUNT(*) as cnt FROM DonHang WHERE TrangThai=N'Chờ xác nhận hủy'");
$rowHuy = sqlsrv_fetch_array($cntHuy, SQLSRV_FETCH_ASSOC);
$soChoHuy = (int)($rowHuy['cnt'] ?? 0);

$ttColor = [
    'Chờ xử lý'         => '#f59e0b',
    'Đang giao'          => '#3b82f6',
    'Đã giao'            => '#22c55e',
    'Đã hủy'             => '#ef4444',
    'Chờ xác nhận hủy'  => '#f97316',
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@300;400;600;700;900&family=Orbitron:wght@400;700;900&display=swap" rel="stylesheet">
<title>Quan Ly Don Hang (Admin)</title>
<style>
:root{--navy:#050d1a;--navy2:#071223;--panel:#0d1f38;--panel2:#0f2444;--cyan:#00e5ff;--purple2:#a855f7;--tx:#e2eaf5;--muted:#7a92b0;--border:rgba(0,229,255,0.12);--orange:#f97316;--r:14px}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Exo 2',sans-serif;background:var(--navy);color:var(--tx);padding:24px 16px 60px}
::-webkit-scrollbar{width:6px}::-webkit-scrollbar-track{background:var(--navy2)}::-webkit-scrollbar-thumb{background:#00b8d4;border-radius:3px}

.topbar{max-width:1200px;margin:0 auto 24px;display:flex;align-items:center;gap:12px;background:rgba(5,13,26,.92);border:1px solid var(--border);border-radius:var(--r);padding:15px 20px}
.logo{font-family:'Orbitron',monospace;font-size:18px;font-weight:900;color:var(--cyan)}
.btn-back{margin-left:auto;color:var(--cyan);text-decoration:none;font-weight:700;border:1px solid var(--cyan);padding:8px 15px;border-radius:8px;transition:.2s;font-size:13px}
.btn-back:hover{background:var(--cyan);color:var(--navy)}

.container{max-width:1200px;margin:0 auto;background:var(--panel);border:1px solid var(--border);border-radius:var(--r);padding:25px}
.st{font-family:'Orbitron',monospace;font-size:16px;font-weight:700;margin-bottom:20px;color:var(--cyan);display:flex;align-items:center;gap:8px}
.st::after{content:'';flex:1;height:1px;background:linear-gradient(90deg,rgba(0,229,255,.4),transparent)}

/* ALERT HUY */
.alert-huy{background:rgba(249,115,22,.1);border:1px solid rgba(249,115,22,.4);border-radius:10px;padding:14px 18px;margin-bottom:20px;display:flex;align-items:center;gap:10px;font-size:13px;font-weight:600;color:#fb923c}
.alert-huy .cnt{background:#f97316;color:#fff;border-radius:50%;width:22px;height:22px;display:inline-flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;flex-shrink:0}

/* FILTER */
.filter-bar{display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap}
.search-box{display:flex;align-items:center;gap:8px;background:var(--panel2);border:1px solid var(--border);border-radius:8px;padding:8px 14px;flex:1;min-width:180px;transition:.2s}
.search-box:focus-within{border-color:var(--cyan)}
.search-box input{background:none;border:none;outline:none;color:var(--tx);font-family:'Exo 2',sans-serif;font-size:13px;width:100%}
.search-box input::placeholder{color:var(--muted)}
.filter-sel{background:var(--panel2);border:1px solid var(--border);border-radius:8px;padding:8px 12px;color:var(--tx);font-family:'Exo 2',sans-serif;font-size:13px;outline:none;cursor:pointer}
.btn-search{padding:9px 18px;border-radius:8px;background:var(--cyan);color:var(--navy);font-family:'Exo 2',sans-serif;font-size:13px;font-weight:700;border:none;cursor:pointer}
.btn-search:hover{background:#00b8d4}

/* TABLE */
.tbl-wrap{overflow-x:auto}
table{width:100%;border-collapse:collapse;min-width:900px}
th,td{padding:12px 14px;text-align:left;border-bottom:1px solid var(--border);font-size:13px}
th{color:var(--muted);text-transform:uppercase;font-size:11px;letter-spacing:.08em;background:var(--panel2)}
tr:hover{background:rgba(0,229,255,.03)}
tr.row-huy{background:rgba(249,115,22,.05);border-left:3px solid var(--orange)}
tr.row-huy:hover{background:rgba(249,115,22,.09)}

.price{color:var(--purple2);font-weight:700}
.al{padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600}
.ok{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.3);color:#4ade80}
.er{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#f87171}

.badge-tt{padding:4px 10px;border-radius:20px;font-size:11px;font-weight:700;white-space:nowrap;display:inline-block}
.ly-do-box{background:rgba(249,115,22,.1);border:1px solid rgba(249,115,22,.25);border-radius:6px;padding:6px 10px;font-size:12px;color:#fb923c;margin-top:6px;max-width:200px}

.actions{display:flex;gap:6px;flex-wrap:wrap;align-items:center}
select.sel-tt{background:var(--navy);color:var(--tx);border:1px solid var(--border);padding:6px 8px;border-radius:6px;outline:none;font-family:'Exo 2',sans-serif;font-size:12px;cursor:pointer}
.btn-luu{background:var(--cyan);color:var(--navy);border:none;padding:6px 12px;border-radius:6px;font-weight:700;cursor:pointer;font-size:12px;font-family:'Exo 2',sans-serif;transition:.2s}
.btn-luu:hover{background:#00b8d4}
.btn-ok-huy{background:rgba(239,68,68,.15);color:#f87171;border:1px solid rgba(239,68,68,.35);padding:6px 12px;border-radius:6px;font-weight:700;cursor:pointer;font-size:12px;font-family:'Exo 2',sans-serif;transition:.2s}
.btn-ok-huy:hover{background:rgba(239,68,68,.3)}
.btn-no-huy{background:rgba(34,197,94,.12);color:#4ade80;border:1px solid rgba(34,197,94,.3);padding:6px 12px;border-radius:6px;font-weight:700;cursor:pointer;font-size:12px;font-family:'Exo 2',sans-serif;transition:.2s}
.btn-no-huy:hover{background:rgba(34,197,94,.25)}
</style>
</head>
<body>

<div class="topbar">
  <div class="logo">&#x1F4BB; ADMIN - QUAN LY DON HANG</div>
  <a href="ChinhSuaProfile.php" class="btn-back">&#x2190; Ho so</a>
</div>

<div class="container">
  <div class="st">DANH SACH TAT CA DON HANG</div>

  <?php if ($success): ?><div class="al ok">&#x2705; <?= htmlspecialchars($success) ?></div><?php endif; ?>
  <?php if ($error):   ?><div class="al er">&#x274C; <?= htmlspecialchars($error) ?></div><?php endif; ?>

  <?php if ($soChoHuy > 0): ?>
  <div class="alert-huy">
    <span class="cnt"><?= $soChoHuy ?></span>
    Co <strong><?= $soChoHuy ?> don hang</strong> dang cho ban xac nhan yeu cau huy! Xem phia duoi.
  </div>
  <?php endif; ?>

  <!-- FILTER -->
  <form method="get" class="filter-bar">
    <div class="search-box">
      <span style="color:var(--muted)">&#x1F50D;</span>
      <input type="text" name="q" placeholder="Tim ten khach, ma don..." value="<?= htmlspecialchars($search) ?>">
    </div>
    <select name="tt" class="filter-sel">
      <option value="">Tat ca trang thai</option>
      <option value="Chờ xác nhận hủy" <?= $filterTT==='Chờ xác nhận hủy'?'selected':'' ?>>&#x1F525; Cho xac nhan huy</option>
      <option value="Chờ xử lý" <?= $filterTT==='Chờ xử lý'?'selected':'' ?>>Cho xu ly</option>
      <option value="Đang giao" <?= $filterTT==='Đang giao'?'selected':'' ?>>Dang giao</option>
      <option value="Đã giao" <?= $filterTT==='Đã giao'?'selected':'' ?>>Da giao</option>
      <option value="Đã hủy" <?= $filterTT==='Đã hủy'?'selected':'' ?>>Da huy</option>
    </select>
    <button type="submit" class="btn-search">Loc</button>
    <?php if ($search || $filterTT !== ''): ?>
      <a href="QuanLyDonHang.php" class="btn-search" style="background:var(--panel2);color:var(--muted);border:1px solid var(--border);text-decoration:none">&#x2715; Reset</a>
    <?php endif; ?>
  </form>

  <div class="tbl-wrap">
    <table>
      <thead>
        <tr>
          <th>Ma Don</th>
          <th>Ngay Dat</th>
          <th>Khach Hang</th>
          <th>Dia Chi Giao</th>
          <th>Tong Tien</th>
          <th>Trang Thai</th>
          <th>Thao Tac</th>
        </tr>
      </thead>
      <tbody>
      <?php while ($dh = sqlsrv_fetch_array($stmt_dh, SQLSRV_FETCH_ASSOC)):
        $ngay = ($dh['NgayDat'] instanceof DateTime) ? $dh['NgayDat']->format('d/m/Y H:i') : '';
        $c    = $ttColor[$dh['TrangThai']] ?? '#888899';
        $isChoHuy = ($dh['TrangThai'] === 'Chờ xác nhận hủy');
      ?>
      <tr class="<?= $isChoHuy ? 'row-huy' : '' ?>">
        <td style="font-weight:700;color:var(--cyan)">#<?= $dh['MaDH'] ?></td>
        <td style="font-size:12px;color:var(--muted)"><?= $ngay ?></td>
        <td>
          <div style="font-weight:600"><?= htmlspecialchars($dh['HoTen'] ?? '') ?></div>
          <div style="font-size:11px;color:var(--muted)">@<?= htmlspecialchars($dh['TenDangNhap'] ?? '') ?> | <?= htmlspecialchars($dh['SoDienThoai'] ?? '') ?></div>
        </td>
        <td style="max-width:200px;font-size:12px;line-height:1.4">
          <?= htmlspecialchars($dh['DiaChi'] ?? '') ?><?= !empty($dh['ThanhPho']) ? ', '.$dh['ThanhPho'] : '' ?>
        </td>
        <td class="price"><?= number_format($dh['TongTien'] ?? 0, 0, ',', '.') ?>d</td>
        <td>
          <span class="badge-tt" style="background:<?= $c ?>22;color:<?= $c ?>;border:1px solid <?= $c ?>55">
            <?= htmlspecialchars($dh['TrangThai']) ?>
          </span>
          <?php if ($isChoHuy && !empty($dh['LyDoHuy'])): ?>
            <div class="ly-do-box">&#x1F4AC; "<?= htmlspecialchars($dh['LyDoHuy']) ?>"</div>
          <?php endif; ?>
        </td>
        <td>
          <?php if ($isChoHuy): ?>
            <!-- NUT DUYET / TU CHOI HUY -->
            <div class="actions">
              <form method="post" style="display:inline" onsubmit="return confirm('Dong y huy don #<?= $dh['MaDH'] ?>?')">
                <input type="hidden" name="action" value="duyet_huy">
                <input type="hidden" name="MaDH" value="<?= $dh['MaDH'] ?>">
                <button type="submit" class="btn-no-huy">&#x2714; Dong y huy</button>
              </form>
              <form method="post" style="display:inline">
                <input type="hidden" name="action" value="tu_choi_huy">
                <input type="hidden" name="MaDH" value="<?= $dh['MaDH'] ?>">
                <button type="submit" class="btn-ok-huy">&#x2715; Tu choi</button>
              </form>
            </div>
          <?php else: ?>
            <!-- CAP NHAT TRANG THAI THUONG -->
            <form method="post" class="actions">
              <input type="hidden" name="action" value="update_status">
              <input type="hidden" name="MaDH" value="<?= $dh['MaDH'] ?>">
              <select name="TrangThai" class="sel-tt">
                <option value="Chờ xử lý"  <?= $dh['TrangThai']==='Chờ xử lý' ?'selected':'' ?>>Cho xu ly</option>
                <option value="Đang giao"   <?= $dh['TrangThai']==='Đang giao'  ?'selected':'' ?>>Dang giao</option>
                <option value="Đã giao"     <?= $dh['TrangThai']==='Đã giao'    ?'selected':'' ?>>Da giao</option>
                <option value="Đã hủy"      <?= $dh['TrangThai']==='Đã hủy'     ?'selected':'' ?>>Da huy</option>
              </select>
              <button type="submit" class="btn-luu">Luu</button>
            </form>
          <?php endif; ?>
        </td>
      </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
</body></html>
<?php sqlsrv_close($conn); ?>