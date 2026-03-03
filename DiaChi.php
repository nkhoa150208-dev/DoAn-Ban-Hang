<?php
$serverName = "localhost\\SQLEXPRESS";
$connectionInfo = ["Database"=>"QLBanHang","TrustServerCertificate"=>true,"CharacterSet"=>"UTF-8"];
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) die(print_r(sqlsrv_errors(), true));
session_start();
if (!isset($_SESSION['MaND'])) { header('Location: DangNhap.php'); exit; }
$user_id = (int)$_SESSION['MaND'];

$success = ''; $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $diaChi = trim($_POST['DiaChi'] ?? '');
    if (empty($diaChi)) {
        $error = 'Dia chi khong duoc de trong.';
    } else {
        $stmt = sqlsrv_query($conn, "UPDATE NguoiDung SET DiaChi=? WHERE MaND=?", [$diaChi, $user_id]);
        if ($stmt) $success = 'Cap nhat dia chi thanh cong!'; else $error = 'Loi cap nhat.';
    }
}

$res  = sqlsrv_query($conn, "SELECT HoTen, DiaChi FROM NguoiDung WHERE MaND=?", [$user_id]);
$user = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC);
?>
<!DOCTYPE html><html lang="vi"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Dia Chi Giao Hang</title>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--bg:#0f0f13;--s1:#1a1a24;--s2:#22222f;--bd:#2e2e40;--p:#6366f1;--ac:#ec4899;--tx:#e2e2f0;--mu:#888899;--r:14px}
body{font-family:'Segoe UI',sans-serif;background:var(--bg);color:var(--tx);padding:24px 16px 60px}
.topbar{max-width:600px;margin:0 auto 24px;display:flex;align-items:center;gap:12px}
.logo{font-size:22px;font-weight:800;background:linear-gradient(135deg,var(--p),var(--ac));-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.back{margin-left:auto;text-decoration:none;color:var(--mu);font-size:13px;padding:8px 14px;border:1px solid var(--bd);border-radius:8px;transition:.2s}
.back:hover{color:var(--tx);border-color:var(--p)}
.card{max-width:600px;margin:0 auto;background:var(--s1);border:1px solid var(--bd);border-radius:var(--r);padding:28px}
.sec-title{font-size:17px;font-weight:700;margin-bottom:20px;display:flex;align-items:center;gap:8px}
.sec-title::after{content:'';flex:1;height:1px;background:var(--bd)}
.fi{display:flex;flex-direction:column;gap:6px;margin-bottom:16px}
.fi label{font-size:12px;color:var(--mu);text-transform:uppercase;letter-spacing:.5px}
.fi textarea,.fi input{background:var(--s2);border:1.5px solid var(--bd);border-radius:10px;color:var(--tx);font-size:14px;padding:12px 14px;outline:none;transition:.2s;font-family:inherit;width:100%}
.fi textarea:focus,.fi input:focus{border-color:var(--p)}
.fi textarea{resize:vertical;min-height:100px}
.btn{display:inline-flex;align-items:center;gap:8px;padding:11px 26px;border-radius:10px;font-size:14px;font-weight:600;border:none;cursor:pointer;transition:.2s}
.bp{background:linear-gradient(135deg,var(--p),#4f46e5);color:#fff;box-shadow:0 4px 14px rgba(99,102,241,.4)}
.bp:hover{transform:translateY(-1px)}
.al{display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:10px;margin-bottom:20px;font-size:14px}
.ok{background:rgba(34,197,94,.12);border:1px solid rgba(34,197,94,.3);color:#4ade80}
.er{background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.3);color:#f87171}
.cur-addr{background:var(--s2);border:1px solid var(--bd);border-radius:10px;padding:14px;margin-bottom:20px;font-size:14px;line-height:1.6}
.cur-label{font-size:11px;color:var(--mu);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px}
</style></head><body>
<div class="topbar">
  <div class="logo">&#x1F6CD; QLBanHang</div>
  <a href="ChinhSuaProfile.php" class="back">&#x2190; Ho so</a>
</div>
<div class="card">
  <?php if ($success): ?><div class="al ok">&#x2705; <?= htmlspecialchars($success) ?></div><?php endif; ?>
  <?php if ($error):   ?><div class="al er">&#x274C; <?= htmlspecialchars($error) ?></div><?php endif; ?>
  <div class="sec-title">&#x1F3E0; Dia chi giao hang</div>
  <?php if (!empty($user['DiaChi'])): ?>
  <div class="cur-label">Dia chi hien tai</div>
  <div class="cur-addr">&#x1F4CD; <?= htmlspecialchars($user['DiaChi']) ?></div>
  <?php endif; ?>
  <form method="post">
    <div class="fi">
      <label>Cap nhat dia chi moi</label>
      <textarea name="DiaChi" placeholder="So nha, duong, phuong/xa, quan/huyen, tinh/thanh pho..."><?= htmlspecialchars($user['DiaChi'] ?? '') ?></textarea>
    </div>
    <button type="submit" class="btn bp">&#x1F4BE; Luu dia chi</button>
  </form>
</div>
</body></html>
<?php sqlsrv_close($conn); ?>