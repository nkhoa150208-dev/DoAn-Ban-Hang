<?php
// ============================================================
//  KIEM TRA QUYEN ADMIN
// ============================================================
$serverName     = "localhost\\SQLEXPRESS";
$connectionInfo = ["Database"=>"QLBanHang","TrustServerCertificate"=>true,"CharacterSet"=>"UTF-8"];
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) die(print_r(sqlsrv_errors(), true));

session_start();
if (!isset($_SESSION['MaND'])) { header('Location: DangNhap.php'); exit; }
$me_id = (int)$_SESSION['MaND'];

// Chi admin (VaiTro=1) moi vao duoc
if (!isset($_SESSION['VaiTro']) || $_SESSION['VaiTro'] != 1) {
    die('<p style="color:red;font-family:sans-serif;padding:40px">Khong co quyen truy cap trang nay.</p>');
}

$success = ''; $error = '';

// ============================================================
//  XU LY HANH DONG
// ============================================================
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// -- Sua thong tin + doi vai tro --
if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $tid      = (int)$_POST['MaND'];
    $hoTen    = trim($_POST['HoTen']    ?? '');
    $email    = trim($_POST['Email']    ?? '');
    $sdt      = trim($_POST['SoDienThoai'] ?? '');
    $vaiTro   = (int)($_POST['VaiTro'] ?? 0);

    if ($tid === $me_id && $vaiTro != 1) {
        $error = 'Khong the tu ha quyen cua chinh minh!';
    } elseif (empty($hoTen)) {
        $error = 'Ho ten khong duoc de trong.';
    } else {
        $stmt = sqlsrv_query($conn,
            "UPDATE NguoiDung SET HoTen=?,Email=?,SoDienThoai=?,VaiTro=? WHERE MaND=?",
            [$hoTen,$email,$sdt,$vaiTro,$tid]);
        if ($stmt) $success = 'Cap nhat thanh cong!';
        else $error = 'Loi cap nhat.';
    }
}

// -- Khoa / mo khoa --
if ($action === 'toggle' && isset($_GET['id'])) {
    $tid = (int)$_GET['id'];
    if ($tid === $me_id) {
        $error = 'Khong the khoa chinh minh!';
    } else {
        $cur = sqlsrv_query($conn, "SELECT TrangThai FROM NguoiDung WHERE MaND=?", [$tid]);
        $row = sqlsrv_fetch_array($cur, SQLSRV_FETCH_ASSOC);
        $newStatus = ($row['TrangThai'] == 1) ? 0 : 1;
        sqlsrv_query($conn, "UPDATE NguoiDung SET TrangThai=? WHERE MaND=?", [$newStatus,$tid]);
        header('Location: QuanLyNguoiDung.php'); exit;
    }
}

// -- Xoa tai khoan --
if ($action === 'delete' && isset($_GET['id'])) {
    $tid = (int)$_GET['id'];
    if ($tid === $me_id) {
        $error = 'Khong the xoa chinh minh!';
    } else {
        sqlsrv_query($conn, "DELETE FROM NguoiDung WHERE MaND=?", [$tid]);
        header('Location: QuanLyNguoiDung.php'); exit;
    }
}

// ============================================================
//  LAY DANH SACH USER
// ============================================================
$search   = trim($_GET['q']    ?? '');
$filterVT = $_GET['vt'] ?? '';
$editId   = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;

$sql = "SELECT * FROM NguoiDung WHERE 1=1";
$params = [];
if ($search !== '') {
    $sql .= " AND (HoTen LIKE ? OR TenDangNhap LIKE ? OR Email LIKE ?)";
    $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%";
}
if ($filterVT !== '') {
    $sql .= " AND VaiTro = ?";
    $params[] = (int)$filterVT;
}
$sql .= " ORDER BY MaND ASC";
$dsUser = sqlsrv_query($conn, $sql, $params ?: []);

// Lay user dang edit
$editUser = null;
if ($editId > 0) {
    $er = sqlsrv_query($conn, "SELECT * FROM NguoiDung WHERE MaND=?", [$editId]);
    $editUser = sqlsrv_fetch_array($er, SQLSRV_FETCH_ASSOC);
}

$vMap = [0 => 'Khach hang', 1 => 'Quan tri vien'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Quan Ly Nguoi Dung</title>
<link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@300;400;600;700;900&family=Orbitron:wght@400;700;900&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--navy:#050d1a;--navy2:#071223;--panel:#0d1f38;--panel2:#0f2444;--cyan:#00e5ff;--cyan2:#00b8d4;--purple:#7c3aed;--purple2:#a855f7;--green:#22c55e;--red:#ef4444;--tx:#e2eaf5;--muted:#7a92b0;--border:rgba(0,229,255,0.12);--r:14px}
body{font-family:'Exo 2',sans-serif;background:var(--navy);color:var(--tx);min-height:100vh;padding:24px 16px 60px}
::-webkit-scrollbar{width:6px}::-webkit-scrollbar-track{background:var(--navy2)}::-webkit-scrollbar-thumb{background:var(--cyan2);border-radius:3px}

.topbar{max-width:1100px;margin:0 auto 24px;display:flex;align-items:center;gap:12px;background:rgba(5,13,26,.92);backdrop-filter:blur(20px);border:1px solid var(--border);border-radius:var(--r);padding:12px 20px}
.logo{font-family:'Orbitron',monospace;font-size:18px;font-weight:900;background:linear-gradient(90deg,var(--cyan),var(--purple2));-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.back{margin-left:auto;text-decoration:none;color:var(--muted);font-size:13px;padding:8px 14px;border:1px solid var(--border);border-radius:8px;transition:.2s}
.back:hover{color:var(--cyan);border-color:var(--cyan)}

.wrap{max-width:1100px;margin:0 auto;display:grid;grid-template-columns:1fr<?php echo $editUser ? ' 380px' : ''; ?>;gap:20px;align-items:start}

.card{background:var(--panel);border:1px solid var(--border);border-radius:var(--r);padding:24px}
.card:hover{border-color:rgba(0,229,255,.2)}

.st{font-family:'Orbitron',monospace;font-size:14px;font-weight:700;letter-spacing:.05em;color:var(--cyan);margin-bottom:20px;display:flex;align-items:center;gap:8px}
.st::after{content:'';flex:1;height:1px;background:linear-gradient(90deg,rgba(0,229,255,.4),transparent)}

/* FILTER BAR */
.filter-bar{display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap}
.search-box{display:flex;align-items:center;gap:8px;background:var(--panel2);border:1px solid var(--border);border-radius:8px;padding:8px 14px;flex:1;min-width:200px;transition:.2s}
.search-box:focus-within{border-color:var(--cyan);box-shadow:0 0 0 3px rgba(0,229,255,.1)}
.search-box input{background:none;border:none;outline:none;color:var(--tx);font-family:'Exo 2',sans-serif;font-size:13px;width:100%}
.search-box input::placeholder{color:var(--muted)}
.filter-sel{background:var(--panel2);border:1px solid var(--border);border-radius:8px;padding:8px 14px;color:var(--tx);font-family:'Exo 2',sans-serif;font-size:13px;outline:none;cursor:pointer;transition:.2s}
.filter-sel:focus{border-color:var(--cyan)}
.btn-search{padding:9px 18px;border-radius:8px;background:var(--cyan);color:var(--navy);font-family:'Exo 2',sans-serif;font-size:13px;font-weight:700;border:none;cursor:pointer;transition:.2s}
.btn-search:hover{background:var(--cyan2)}

/* TABLE */
.tbl-wrap{overflow-x:auto}
table{width:100%;border-collapse:collapse;font-size:13px}
thead th{background:var(--panel2);color:var(--cyan);font-size:10px;text-transform:uppercase;letter-spacing:.1em;padding:10px 14px;text-align:left;white-space:nowrap}
tbody tr{border-bottom:1px solid var(--border);transition:.15s}
tbody tr:hover{background:rgba(0,229,255,.04)}
td{padding:12px 14px;vertical-align:middle}
.td-avatar{width:38px;height:38px;border-radius:50%;object-fit:cover;border:2px solid var(--cyan)}
.td-name{font-weight:600;color:var(--tx)}
.td-sub{font-size:11px;color:var(--muted);margin-top:2px}
.badge-vt{padding:3px 10px;border-radius:20px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;white-space:nowrap}
.vt1{background:rgba(0,229,255,.15);color:var(--cyan);border:1px solid rgba(0,229,255,.3)}
.vt0{background:rgba(122,146,176,.1);color:var(--muted);border:1px solid rgba(122,146,176,.2)}
.badge-tt{padding:3px 10px;border-radius:20px;font-size:10px;font-weight:700}
.tt1{background:rgba(34,197,94,.12);color:#4ade80;border:1px solid rgba(34,197,94,.3)}
.tt0{background:rgba(239,68,68,.12);color:#f87171;border:1px solid rgba(239,68,68,.3)}
.actions{display:flex;gap:6px;flex-wrap:wrap}
.btn-sm{padding:5px 12px;border-radius:6px;font-size:11px;font-weight:700;border:none;cursor:pointer;transition:.2s;text-decoration:none;display:inline-block;font-family:'Exo 2',sans-serif;letter-spacing:.03em;white-space:nowrap}
.btn-edit{background:rgba(99,102,241,.15);color:#818cf8;border:1px solid rgba(99,102,241,.3)}
.btn-edit:hover{background:rgba(99,102,241,.3)}
.btn-lock{background:rgba(245,158,11,.12);color:#fbbf24;border:1px solid rgba(245,158,11,.3)}
.btn-lock:hover{background:rgba(245,158,11,.25)}
.btn-unlock{background:rgba(34,197,94,.12);color:#4ade80;border:1px solid rgba(34,197,94,.3)}
.btn-unlock:hover{background:rgba(34,197,94,.25)}
.btn-del{background:rgba(239,68,68,.12);color:#f87171;border:1px solid rgba(239,68,68,.3)}
.btn-del:hover{background:rgba(239,68,68,.25)}
.btn-me{opacity:.4;cursor:not-allowed}

/* EDIT PANEL */
.fi{display:flex;flex-direction:column;gap:6px;margin-bottom:14px}
.fi label{font-size:10px;color:var(--cyan);text-transform:uppercase;letter-spacing:.8px;font-weight:600}
.fi input,.fi select{background:var(--panel2);border:1.5px solid var(--border);border-radius:10px;color:var(--tx);font-size:14px;font-family:'Exo 2',sans-serif;padding:10px 14px;outline:none;transition:.2s;width:100%}
.fi input:focus,.fi select:focus{border-color:var(--cyan);box-shadow:0 0 0 3px rgba(0,229,255,.1)}
.fi input:disabled{opacity:.4;cursor:not-allowed}
select option{background:var(--panel2)}
.btn-save{width:100%;padding:12px;border-radius:10px;background:linear-gradient(135deg,var(--green),#16a34a);color:#fff;font-family:'Exo 2',sans-serif;font-size:13px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;border:none;cursor:pointer;transition:.2s;margin-top:4px}
.btn-save:hover{transform:translateY(-1px);box-shadow:0 4px 14px rgba(34,197,94,.4)}
.btn-cancel{display:block;text-align:center;margin-top:10px;color:var(--muted);font-size:12px;text-decoration:none;transition:.2s}
.btn-cancel:hover{color:var(--tx)}

.al{display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:10px;margin-bottom:16px;font-size:13px;animation:fi .3s}
.ok{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.3);color:#4ade80}
.er{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#f87171}
@keyframes fi{from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:none}}

.empty{text-align:center;padding:40px;color:var(--muted)}
.me-tag{font-size:10px;background:rgba(0,229,255,.1);color:var(--cyan);padding:2px 6px;border-radius:4px;margin-left:4px;vertical-align:middle}
</style>
</head>
<body>

<div class="topbar">
  <div class="logo">&#x1F6E1; Admin Panel</div>
  <a href="ChinhSuaProfile.php" class="back">&#x2190; Ho so</a>
</div>

<div class="wrap">
  <!-- DANH SACH USER -->
  <div class="card">
    <?php if ($success): ?><div class="al ok">&#x2705; <?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error):   ?><div class="al er">&#x274C; <?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="st">&#x1F464; Quan ly nguoi dung</div>

    <!-- Filter -->
    <form method="get" class="filter-bar">
      <div class="search-box">
        <span>&#x1F50D;</span>
        <input type="text" name="q" placeholder="Tim ten, username, email..." value="<?= htmlspecialchars($search) ?>">
      </div>
      <select name="vt" class="filter-sel">
        <option value="">Tat ca vai tro</option>
        <option value="1" <?= $filterVT==='1'?'selected':'' ?>>Quan tri vien</option>
        <option value="0" <?= $filterVT==='0'?'selected':'' ?>>Khach hang</option>
      </select>
      <button type="submit" class="btn-search">Loc</button>
      <?php if ($search||$filterVT!==''): ?>
        <a href="QuanLyNguoiDung.php" class="btn-search" style="background:var(--panel2);color:var(--muted);border:1px solid var(--border);text-decoration:none">&#x2715; Reset</a>
      <?php endif; ?>
    </form>

    <div class="tbl-wrap">
      <table>
        <thead>
          <tr>
            <th>Avatar</th>
            <th>Ho ten</th>
            <th>Email</th>
            <th>SĐT</th>
            <th>Vai tro</th>
            <th>Trang thai</th>
            <th>Hanh dong</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($dsUser && sqlsrv_has_rows($dsUser)): ?>
          <?php while ($u = sqlsrv_fetch_array($dsUser, SQLSRV_FETCH_ASSOC)): ?>
            <?php
              $avSrc = (!empty($u['Avatar']) && file_exists($u['Avatar']))
                ? $u['Avatar']
                : 'https://ui-avatars.com/api/?name='.urlencode($u['HoTen']).'&background=0d1f38&color=00e5ff&size=80';
              $isMe = ($u['MaND'] == $me_id);
            ?>
            <tr>
              <td><img class="td-avatar" src="<?= htmlspecialchars($avSrc) ?>" alt=""></td>
              <td>
                <div class="td-name">
                  <?= htmlspecialchars($u['HoTen']) ?>
                  <?php if ($isMe): ?><span class="me-tag">Ban</span><?php endif; ?>
                </div>
                <div class="td-sub">@<?= htmlspecialchars($u['TenDangNhap']) ?></div>
              </td>
              <td><?= htmlspecialchars($u['Email'] ?? '—') ?></td>
              <td><?= htmlspecialchars($u['SoDienThoai'] ?? '—') ?></td>
              <td>
                <span class="badge-vt <?= $u['VaiTro']==1?'vt1':'vt0' ?>">
                  <?= $vMap[$u['VaiTro']] ?? 'Khach hang' ?>
                </span>
              </td>
              <td>
                <span class="badge-tt <?= $u['TrangThai']==1?'tt1':'tt0' ?>">
                  <?= $u['TrangThai']==1 ? 'Hoat dong' : 'Bi khoa' ?>
                </span>
              </td>
              <td>
                <div class="actions">
                  <a href="?edit=<?= $u['MaND'] ?><?= $search?"&q=$search":'' ?><?= $filterVT!==''?"&vt=$filterVT":'' ?>" class="btn-sm btn-edit">&#x270F; Sua</a>
                  <?php if (!$isMe): ?>
                    <a href="?action=toggle&id=<?= $u['MaND'] ?>" class="btn-sm <?= $u['TrangThai']==1?'btn-lock':'btn-unlock' ?>">
                      <?= $u['TrangThai']==1 ? '&#x1F512; Khoa' : '&#x1F513; Mo' ?>
                    </a>
                    <a href="?action=delete&id=<?= $u['MaND'] ?>" class="btn-sm btn-del" onclick="return confirm('Xoa tai khoan <?= htmlspecialchars($u['TenDangNhap']) ?>?')">&#x1F5D1; Xoa</a>
                  <?php else: ?>
                    <span class="btn-sm btn-me">&#x1F512; Khoa</span>
                    <span class="btn-sm btn-me">&#x1F5D1; Xoa</span>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="7"><div class="empty">Khong tim thay nguoi dung nao</div></td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- EDIT PANEL -->
  <?php if ($editUser): ?>
  <div class="card">
    <div class="st">&#x270F; Sua thong tin</div>
    <form method="post">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="MaND" value="<?= $editUser['MaND'] ?>">
      <div class="fi">
        <label>Ma ND</label>
        <input type="text" value="#<?= $editUser['MaND'] ?>" disabled>
      </div>
      <div class="fi">
        <label>Ten dang nhap</label>
        <input type="text" value="<?= htmlspecialchars($editUser['TenDangNhap']) ?>" disabled>
      </div>
      <div class="fi">
        <label>Ho va ten <span style="color:#ef4444">*</span></label>
        <input type="text" name="HoTen" value="<?= htmlspecialchars($editUser['HoTen']) ?>" required>
      </div>
      <div class="fi">
        <label>Email</label>
        <input type="email" name="Email" value="<?= htmlspecialchars($editUser['Email'] ?? '') ?>">
      </div>
      <div class="fi">
        <label>So dien thoai</label>
        <input type="tel" name="SoDienThoai" value="<?= htmlspecialchars($editUser['SoDienThoai'] ?? '') ?>">
      </div>
      <div class="fi">
        <label>Vai tro</label>
        <select name="VaiTro" <?= ($editUser['MaND']==$me_id)?'disabled':'' ?>>
          <option value="0" <?= $editUser['VaiTro']==0?'selected':'' ?>>Khach hang</option>
          <option value="1" <?= $editUser['VaiTro']==1?'selected':'' ?>>Quan tri vien</option>
        </select>
        <?php if ($editUser['MaND']==$me_id): ?>
          <input type="hidden" name="VaiTro" value="1">
          <small style="color:var(--muted);font-size:11px">Khong the thay doi vai tro cua chinh minh</small>
        <?php endif; ?>
      </div>
      <button type="submit" class="btn-save">&#x1F4BE; Luu thay doi</button>
      <a href="QuanLyNguoiDung.php" class="btn-cancel">Huy</a>
    </form>
  </div>
  <?php endif; ?>
</div>

</body>
</html>
<?php sqlsrv_close($conn); ?>