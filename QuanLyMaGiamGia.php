<?php
$serverName     = "localhost\\SQLEXPRESS";
$connectionInfo = ["Database"=>"QLBanHang","TrustServerCertificate"=>true,"CharacterSet"=>"UTF-8"];
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) die(print_r(sqlsrv_errors(), true));
session_start();
if (!isset($_SESSION['MaND'])) { header('Location: DangNhap.php'); exit; }
if (!isset($_SESSION['VaiTro']) || $_SESSION['VaiTro'] != 1) {
    die('<p style="color:#ef4444;font-family:sans-serif;padding:40px">Khong co quyen truy cap.</p>');
}

$success = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action']??'') === 'them') {
    $code        = strtoupper(trim($_POST['Code']        ?? ''));
    $loai        = (int)($_POST['LoaiGiam']     ?? 0);
    $giaTri      = (float)($_POST['GiaTri']     ?? 0);
    $giamToiDa   = (float)($_POST['GiamToiDa']  ?? 0);
    $donToiThieu = (float)($_POST['DonToiThieu'] ?? 0);
    $soLan       = (int)($_POST['SoLanDung']    ?? 1);
    $hetHan      = trim($_POST['NgayHetHan']    ?? '');

    if (empty($code))     { $error = 'Vui long nhap ma giam gia!'; }
    elseif ($giaTri <= 0) { $error = 'Gia tri giam phai lon hon 0!'; }
    elseif ($loai == 0 && $giaTri > 100) { $error = 'Phan tram giam khong qua 100%!'; }
    else {
        $chk = sqlsrv_query($conn, "SELECT MaMGG FROM MaGiamGia WHERE Code=?", [$code]);
        if ($chk && sqlsrv_fetch($chk)) {
            $error = 'Ma nay da ton tai!';
        } else {
            $ngayHH = empty($hetHan) ? null : $hetHan;
            $stmt = sqlsrv_query($conn,
                "INSERT INTO MaGiamGia (Code,LoaiGiam,GiaTri,GiamToiDa,DonToiThieu,SoLanDung,NgayHetHan) VALUES (?,?,?,?,?,?,?)",
                [$code,$loai,$giaTri,$giamToiDa,$donToiThieu,$soLan,$ngayHH]);
            if ($stmt) $success = 'Tao ma "'.$code.'" thanh cong!';
            else $error = 'Loi tao ma!';
        }
    }
}

if (isset($_GET['xoa'])) {
    sqlsrv_query($conn, "DELETE FROM MaGiamGia WHERE MaMGG=?", [(int)$_GET['xoa']]);
    header('Location: QuanLyMaGiamGia.php'); exit;
}

if (isset($_GET['toggle'])) {
    $tid = (int)$_GET['toggle'];
    $cur = sqlsrv_query($conn, "SELECT TrangThai FROM MaGiamGia WHERE MaMGG=?", [$tid]);
    $row = sqlsrv_fetch_array($cur, SQLSRV_FETCH_ASSOC);
    $new = ($row['TrangThai'] == 1) ? 0 : 1;
    sqlsrv_query($conn, "UPDATE MaGiamGia SET TrangThai=? WHERE MaMGG=?", [$new,$tid]);
    header('Location: QuanLyMaGiamGia.php'); exit;
}

$search = trim($_GET['q'] ?? '');
$sql = "SELECT * FROM MaGiamGia WHERE 1=1";
$params = [];
if ($search !== '') { $sql .= " AND Code LIKE ?"; $params[] = "%$search%"; }
$sql .= " ORDER BY MaMGG DESC";
$dsMa = sqlsrv_query($conn, $sql, $params ?: []);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Quan Ly Ma Giam Gia</title>
<link href="https://fonts.googleapis.com/css2?family=Exo+2:ital,wght@0,300;0,400;0,600;0,700;0,900;1,400&family=Orbitron:wght@400;700;900&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#040c18;
  --panel:#081527;
  --panel2:#0a1c32;
  --panel3:#0d2240;
  --cyan:#00e5ff;
  --cyan-dim:rgba(0,229,255,0.15);
  --cyan-glow:rgba(0,229,255,0.08);
  --purple:#a855f7;
  --green:#22c55e;
  --red:#ef4444;
  --amber:#f59e0b;
  --tx:#dde8f5;
  --muted:#5a7a99;
  --border:rgba(0,229,255,0.1);
  --border-hover:rgba(0,229,255,0.3);
  --r:12px
}
body{font-family:'Exo 2',sans-serif;background:var(--bg);color:var(--tx);min-height:100vh;padding:28px 20px 80px}

/* TOPBAR */
.topbar{max-width:1160px;margin:0 auto 28px;display:flex;align-items:center;gap:14px;padding:14px 22px;background:var(--panel);border:1px solid var(--border);border-radius:var(--r);backdrop-filter:blur(20px)}
.logo-icon{width:34px;height:34px;border-radius:8px;background:linear-gradient(135deg,rgba(0,229,255,.2),rgba(168,85,247,.2));border:1px solid rgba(0,229,255,.25);display:grid;place-items:center;font-size:16px}
.logo-text{font-family:'Orbitron',monospace;font-size:15px;font-weight:900;background:linear-gradient(90deg,var(--cyan),var(--purple));-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.topbar-sub{font-size:12px;color:var(--muted);margin-left:2px}
.back-btn{margin-left:auto;text-decoration:none;color:var(--muted);font-size:12px;font-weight:600;padding:8px 16px;border:1px solid var(--border);border-radius:8px;transition:.2s;display:flex;align-items:center;gap:6px;letter-spacing:.03em}
.back-btn:hover{color:var(--cyan);border-color:var(--cyan);background:var(--cyan-glow)}

/* LAYOUT */
.wrap{max-width:1160px;margin:0 auto;display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start}
@media(max-width:860px){.wrap{grid-template-columns:1fr}}

/* CARDS */
.card{background:var(--panel);border:1px solid var(--border);border-radius:var(--r);overflow:hidden}
.card-head{padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px;background:var(--panel2)}
.card-head-icon{width:30px;height:30px;border-radius:7px;display:grid;place-items:center;font-size:14px}
.icon-list{background:rgba(0,229,255,.1);border:1px solid rgba(0,229,255,.2)}
.icon-add{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.2)}
.card-title{font-family:'Orbitron',monospace;font-size:12px;font-weight:700;letter-spacing:.08em;color:var(--cyan);text-transform:uppercase}
.card-body{padding:20px 22px}

/* ALERTS */
.al{padding:12px 16px;border-radius:9px;margin-bottom:18px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:8px;animation:slideIn .3s}
.ok{background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.25);color:#4ade80}
.er{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);color:#f87171}
@keyframes slideIn{from{opacity:0;transform:translateY(-8px)}to{opacity:1;transform:none}}

/* SEARCH */
.search-row{display:flex;gap:10px;margin-bottom:18px}
.search-wrap{flex:1;display:flex;align-items:center;gap:9px;background:var(--panel2);border:1.5px solid var(--border);border-radius:9px;padding:0 14px;transition:.2s}
.search-wrap:focus-within{border-color:var(--cyan);box-shadow:0 0 0 3px rgba(0,229,255,.07)}
.search-icon{color:var(--muted);font-size:14px;flex-shrink:0}
.search-wrap input{background:none;border:none;outline:none;color:var(--tx);font-family:'Exo 2',sans-serif;font-size:13px;width:100%;padding:10px 0}
.search-wrap input::placeholder{color:var(--muted)}
.btn-filter{padding:10px 18px;border-radius:9px;background:var(--cyan);color:#040c18;font-family:'Exo 2',sans-serif;font-size:12px;font-weight:800;border:none;cursor:pointer;letter-spacing:.05em;text-transform:uppercase;transition:.2s;white-space:nowrap}
.btn-filter:hover{background:#00c8e0;box-shadow:0 0 12px rgba(0,229,255,.3)}
.btn-reset{padding:10px 14px;border-radius:9px;background:var(--panel3);color:var(--muted);font-family:'Exo 2',sans-serif;font-size:12px;font-weight:700;border:1px solid var(--border);cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;transition:.2s}
.btn-reset:hover{color:var(--tx);border-color:var(--border-hover)}

/* TABLE */
.tbl-outer{overflow-x:auto;margin:0 -22px;padding:0 22px}
table{width:100%;border-collapse:collapse;font-size:13px;min-width:640px}
thead tr{border-bottom:1.5px solid var(--border)}
thead th{padding:10px 14px;text-align:left;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:var(--muted);white-space:nowrap}
tbody tr{border-bottom:1px solid rgba(0,229,255,.06);transition:.15s}
tbody tr:last-child{border-bottom:none}
tbody tr:hover{background:rgba(0,229,255,.03)}
td{padding:13px 14px;vertical-align:middle}

.code-chip{font-family:'Orbitron',monospace;font-size:11px;font-weight:700;letter-spacing:.08em;color:var(--cyan);background:rgba(0,229,255,.08);border:1px solid rgba(0,229,255,.2);padding:5px 12px;border-radius:6px;display:inline-block;white-space:nowrap}
.tag{padding:4px 10px;border-radius:20px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;display:inline-block;white-space:nowrap}
.tag-pct{background:rgba(168,85,247,.12);color:#c084fc;border:1px solid rgba(168,85,247,.25)}
.tag-fix{background:rgba(34,197,94,.1);color:#4ade80;border:1px solid rgba(34,197,94,.22)}
.tag-on{background:rgba(34,197,94,.1);color:#4ade80;border:1px solid rgba(34,197,94,.22)}
.tag-off{background:rgba(239,68,68,.1);color:#f87171;border:1px solid rgba(239,68,68,.22)}
.tag-exp{background:rgba(245,158,11,.1);color:#fbbf24;border:1px solid rgba(245,158,11,.22)}

.val-main{font-weight:700;color:var(--tx);font-size:14px}
.val-sub{font-size:11px;color:var(--muted);margin-top:2px}
.bar-wrap{width:72px;height:5px;background:var(--panel3);border-radius:3px;overflow:hidden;margin-top:5px}
.bar-fill{height:100%;border-radius:3px;transition:.3s}
.usage-txt{font-size:12px;font-weight:600;color:var(--tx)}
.usage-sub{font-size:11px;color:var(--muted);margin-top:2px}

.actions{display:flex;gap:7px;align-items:center}
.btn-act{padding:6px 13px;border-radius:7px;font-size:11px;font-weight:700;font-family:'Exo 2',sans-serif;letter-spacing:.04em;cursor:pointer;border:none;transition:.2s;text-decoration:none;display:inline-flex;align-items:center;gap:5px;white-space:nowrap}
.btn-on{background:rgba(245,158,11,.1);color:#fbbf24;border:1px solid rgba(245,158,11,.25)}
.btn-on:hover{background:rgba(245,158,11,.22);box-shadow:0 0 8px rgba(245,158,11,.2)}
.btn-off{background:rgba(34,197,94,.1);color:#4ade80;border:1px solid rgba(34,197,94,.22)}
.btn-off:hover{background:rgba(34,197,94,.2)}
.btn-del{background:rgba(239,68,68,.08);color:#f87171;border:1px solid rgba(239,68,68,.2)}
.btn-del:hover{background:rgba(239,68,68,.18);box-shadow:0 0 8px rgba(239,68,68,.15)}

.empty-state{text-align:center;padding:50px 20px;color:var(--muted)}
.empty-state .ei{font-size:36px;margin-bottom:12px;opacity:.4}
.empty-state p{font-size:13px}

/* FORM */
.fi{margin-bottom:16px}
.fi label{display:block;font-size:10px;font-weight:700;color:var(--cyan);text-transform:uppercase;letter-spacing:.1em;margin-bottom:7px}
.fi label .req{color:#ef4444}
.fi input,.fi select{width:100%;background:var(--panel3);border:1.5px solid var(--border);border-radius:9px;color:var(--tx);font-size:13px;font-family:'Exo 2',sans-serif;padding:10px 14px;outline:none;transition:.2s}
.fi input:focus,.fi select:focus{border-color:var(--cyan);box-shadow:0 0 0 3px rgba(0,229,255,.08)}
.fi input::placeholder{color:var(--muted)}
.fi select option{background:var(--panel2)}
.fi .hint{font-size:11px;color:var(--muted);margin-top:5px}
.grid2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.divider{height:1px;background:var(--border);margin:18px 0}
.btn-create{width:100%;padding:13px;border-radius:10px;background:linear-gradient(135deg,#22c55e,#16a34a);color:#fff;font-family:'Exo 2',sans-serif;font-size:13px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;border:none;cursor:pointer;transition:.25s;display:flex;align-items:center;justify-content:center;gap:8px;margin-top:4px}
.btn-create:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(34,197,94,.35)}
.btn-create:active{transform:none}
.loai-pill{display:flex;gap:8px;margin-top:4px}
.loai-opt{flex:1;position:relative}
.loai-opt input{position:absolute;opacity:0;width:0;height:0}
.loai-opt label{display:flex;flex-direction:column;align-items:center;gap:4px;padding:10px 8px;border:1.5px solid var(--border);border-radius:9px;cursor:pointer;transition:.2s;background:var(--panel3);font-size:11px;font-weight:700;color:var(--muted);text-align:center}
.loai-opt label .emoji{font-size:18px}
.loai-opt input:checked + label{border-color:var(--cyan);color:var(--cyan);background:rgba(0,229,255,.07)}
</style>
</head>
<body>

<div class="topbar">
  <div class="logo-icon">🎫</div>
  <div>
    <div class="logo-text">Ma Giam Gia</div>
    <div class="topbar-sub">Quan ly khuyen mai</div>
  </div>
  <a href="ChinhSuaProfile.php" class="back-btn">&#x2190; Ho so</a>
</div>

<div class="wrap">

  <!-- DANH SACH -->
  <div class="card">
    <div class="card-head">
      <div class="card-head-icon icon-list">📋</div>
      <div class="card-title">Danh sach ma giam gia</div>
    </div>
    <div class="card-body">
      <?php if ($success): ?><div class="al ok">✅ <?= htmlspecialchars($success) ?></div><?php endif; ?>
      <?php if ($error):   ?><div class="al er">❌ <?= htmlspecialchars($error) ?></div><?php endif; ?>

      <form method="get" class="search-row">
        <div class="search-wrap">
          <span class="search-icon">🔍</span>
          <input type="text" name="q" placeholder="Tim kiem ma giam gia..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <button type="submit" class="btn-filter">Loc</button>
        <?php if ($search): ?>
          <a href="QuanLyMaGiamGia.php" class="btn-reset">✕</a>
        <?php endif; ?>
      </form>

      <div class="tbl-outer">
        <table>
          <thead>
            <tr>
              <th>Ma code</th>
              <th>Loai</th>
              <th>Gia tri</th>
              <th>Don toi thieu</th>
              <th>Luot dung</th>
              <th>Het han</th>
              <th>Trang thai</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
          <?php if ($dsMa && sqlsrv_has_rows($dsMa)): ?>
            <?php while ($mg = sqlsrv_fetch_array($dsMa, SQLSRV_FETCH_ASSOC)): ?>
              <?php
                $pct = ($mg['SoLanDung'] > 0) ? min(100, round($mg['DaDung'] / $mg['SoLanDung'] * 100)) : 0;
                $barColor = $pct >= 100 ? '#ef4444' : ($pct >= 70 ? '#f59e0b' : '#00e5ff');
                if ($mg['NgayHetHan'] instanceof DateTime) {
                    $expired = $mg['NgayHetHan'] < new DateTime();
                    $hetHanStr = $mg['NgayHetHan']->format('d/m/Y');
                } elseif (!empty($mg['NgayHetHan'])) {
                    $expired = strtotime($mg['NgayHetHan']) < time();
                    $hetHanStr = date('d/m/Y', strtotime($mg['NgayHetHan']));
                } else {
                    $expired = false;
                    $hetHanStr = '—';
                }
              ?>
              <tr>
                <td><span class="code-chip"><?= htmlspecialchars($mg['Code']) ?></span></td>
                <td>
                  <?php if ($mg['LoaiGiam'] == 0): ?>
                    <span class="tag tag-pct">% Phan tram</span>
                  <?php else: ?>
                    <span class="tag tag-fix">So tien</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($mg['LoaiGiam'] == 0): ?>
                    <div class="val-main"><?= number_format($mg['GiaTri'],0) ?>%</div>
                    <?php if ($mg['GiamToiDa'] > 0): ?>
                      <div class="val-sub">Max: <?= number_format($mg['GiamToiDa'],0,',','.') ?>d</div>
                    <?php endif; ?>
                  <?php else: ?>
                    <div class="val-main"><?= number_format($mg['GiaTri'],0,',','.') ?>d</div>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($mg['DonToiThieu'] > 0): ?>
                    <div class="val-main"><?= number_format($mg['DonToiThieu'],0,',','.') ?>d</div>
                  <?php else: ?>
                    <div class="val-sub">Khong gioi han</div>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="usage-txt"><?= $mg['DaDung'] ?><span style="color:var(--muted);font-weight:400"> / <?= $mg['SoLanDung'] ?></span></div>
                  <div class="bar-wrap"><div class="bar-fill" style="width:<?= $pct ?>%;background:<?= $barColor ?>"></div></div>
                </td>
                <td>
                  <?php if ($expired): ?>
                    <span class="tag tag-exp"><?= $hetHanStr ?></span>
                  <?php else: ?>
                    <span style="font-size:12px;color:<?= $hetHanStr==='—'?'var(--muted)':'var(--tx)' ?>"><?= $hetHanStr ?></span>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="tag <?= $mg['TrangThai']==1?'tag-on':'tag-off' ?>">
                    <?= $mg['TrangThai']==1 ? 'Hoat dong' : 'Vo hieu' ?>
                  </span>
                </td>
                <td>
                  <div class="actions">
                    <a href="?toggle=<?= $mg['MaMGG'] ?>" class="btn-act <?= $mg['TrangThai']==1?'btn-on':'btn-off' ?>">
                      <?= $mg['TrangThai']==1 ? '⏸ Tat' : '▶ Bat' ?>
                    </a>
                    <a href="?xoa=<?= $mg['MaMGG'] ?>" class="btn-act btn-del" onclick="return confirm('Xoa ma <?= htmlspecialchars($mg['Code']) ?>?')">🗑</a>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="8">
              <div class="empty-state">
                <div class="ei">🎫</div>
                <p>Chua co ma giam gia nao<?= $search ? ' phu hop' : '' ?></p>
              </div>
            </td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- FORM TAO MA -->
  <div class="card">
    <div class="card-head">
      <div class="card-head-icon icon-add">✚</div>
      <div class="card-title">Tao ma moi</div>
    </div>
    <div class="card-body">
      <form method="post">
        <input type="hidden" name="action" value="them">

        <div class="fi">
          <label>Ma giam gia <span class="req">*</span></label>
          <input type="text" name="Code" id="codeInput" placeholder="VD: SALE20" autocomplete="off" required>
          <div class="hint">Chi nhap chu va so, khong dau cach</div>
        </div>

        <div class="fi">
          <label>Loai giam <span class="req">*</span></label>
          <div class="loai-pill">
            <div class="loai-opt">
              <input type="radio" name="LoaiGiam" id="loai0" value="0" checked onchange="toggleLoai()">
              <label for="loai0"><span class="emoji">%</span>Phan tram</label>
            </div>
            <div class="loai-opt">
              <input type="radio" name="LoaiGiam" id="loai1" value="1" onchange="toggleLoai()">
              <label for="loai1"><span class="emoji">₫</span>Co dinh</label>
            </div>
          </div>
        </div>

        <div class="grid2">
          <div class="fi">
            <label id="lblGiaTri">Phan tram (%) <span class="req">*</span></label>
            <input type="number" name="GiaTri" id="giaTri" placeholder="VD: 10" min="0.01" step="0.01" required>
          </div>
          <div class="fi" id="wrapToiDa">
            <label>Giam toi da (d)</label>
            <input type="number" name="GiamToiDa" placeholder="0 = vo han" min="0" step="1000">
          </div>
        </div>

        <div class="fi">
          <label>Don hang toi thieu (d)</label>
          <input type="number" name="DonToiThieu" placeholder="0 = khong yeu cau" min="0" step="1000">
        </div>

        <div class="divider"></div>

        <div class="grid2">
          <div class="fi">
            <label>So luot dung <span class="req">*</span></label>
            <input type="number" name="SoLanDung" value="100" min="1" required>
          </div>
          <div class="fi">
            <label>Ngay het han</label>
            <input type="date" name="NgayHetHan">
            <div class="hint">De trong = khong het han</div>
          </div>
        </div>

        <button type="submit" class="btn-create">
          <span>💾</span> Tao ma giam gia
        </button>
      </form>
    </div>
  </div>

</div>

<script>
function toggleLoai() {
  const loai = document.querySelector('input[name=LoaiGiam]:checked').value;
  const lbl  = document.getElementById('lblGiaTri');
  const inp  = document.getElementById('giaTri');
  const wrap = document.getElementById('wrapToiDa');
  if (loai === '0') {
    lbl.innerHTML = 'Phan tram (%) <span class="req">*</span>';
    inp.placeholder = 'VD: 10'; inp.max = '100';
    wrap.style.display = 'block';
  } else {
    lbl.innerHTML = 'So tien giam (d) <span class="req">*</span>';
    inp.placeholder = 'VD: 50000'; inp.removeAttribute('max');
    wrap.style.display = 'none';
  }
}
document.getElementById('codeInput').addEventListener('input', function() {
  this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g,'');
});
</script>
</body>
</html>
<?php sqlsrv_close($conn); ?>