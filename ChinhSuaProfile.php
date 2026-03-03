<?php
// ============================================================
//  KET NOI DATABASE SQL SERVER
// ============================================================

session_start();

$serverName     = "localhost\\SQLEXPRESS";
$connectionInfo = ["Database"=>"QLBanHang","TrustServerCertificate"=>true,"CharacterSet"=>"UTF-8"];
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) die(print_r(sqlsrv_errors(), true));

if (!isset($_SESSION['user_id'])) $_SESSION['user_id'] = 1;
$user_id = (int)$_SESSION['user_id'];

$uploadPath = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'avatars';
if (!file_exists($uploadPath)) {
    mkdir($uploadPath, 0777, true);
}
define('UPLOAD_DIR', $uploadPath . DIRECTORY_SEPARATOR);


$success = ""; $error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (($_POST['action'] ?? '') === 'update_info') {
        $hoten  = trim($_POST['HoTen']      ?? '');
        $email  = trim($_POST['Email']       ?? '');
        $sdt    = trim($_POST['SoDienThoai'] ?? '');
        $diachi = trim($_POST['DiaChi']      ?? '');

        if (empty($hoten)) {
            $error = "Ho ten khong duoc de trong.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Email khong hop le.";
        } else {
            $chk = sqlsrv_query($conn,
                "SELECT MaND FROM dbo.NguoiDung WHERE Email=? AND MaND!=?",
                [$email, $user_id]);
            if ($chk && sqlsrv_fetch($chk)) {
                $error = "Email nay da duoc dung boi tai khoan khac.";
            } else {
                $res = sqlsrv_query($conn,
                    "UPDATE dbo.NguoiDung SET HoTen=?,Email=?,SoDienThoai=?,DiaChi=? WHERE MaND=?",
                    [$hoten,$email,$sdt,$diachi,$user_id]);
                if ($res) $success = "info";
                else $error = "Loi cap nhat DB.";
            }
        }
    }

    if (($_POST['action'] ?? '') === 'update_avatar') {
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $f  = $_FILES['avatar'];
            $fi = finfo_open(FILEINFO_MIME_TYPE);
            $mt = finfo_file($fi, $f['tmp_name']); finfo_close($fi);
            if ($f['size'] > 2097152) {
                $error = "Anh qua lon (max 2MB).";
            } elseif (!in_array($mt, ['image/jpeg','image/png','image/gif','image/webp'])) {
                $error = "Chi nhan JPG/PNG/GIF/WEBP.";
            } else {
                $ext  = pathinfo($f['name'], PATHINFO_EXTENSION);
                $dest = UPLOAD_DIR.'av_'.$user_id.'_'.time().'.'.$ext;
                if (move_uploaded_file($f['tmp_name'], $dest)) {
                    $old = sqlsrv_query($conn,"SELECT Avatar FROM dbo.NguoiDung WHERE MaND=?",[$user_id]);
                    if ($old && $row = sqlsrv_fetch_array($old, SQLSRV_FETCH_ASSOC)) {
                        if (!empty($row['Avatar']) && file_exists($row['Avatar'])) unlink($row['Avatar']);
                    }
                    $up = sqlsrv_query($conn,"UPDATE dbo.NguoiDung SET Avatar=? WHERE MaND=?",[$dest,$user_id]);
                    if ($up) $success = "avatar";
                    else $error = "Loi luu avatar vao DB.";
                } else { $error = "Khong the luu file. Kiem tra quyen thu muc uploads/."; }
            }
        } else { $error = "Vui long chon file anh."; }
    }
}

$res  = sqlsrv_query($conn,"SELECT * FROM dbo.NguoiDung WHERE MaND=?",[$user_id]);
$user = $res ? sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC) : null;

if (!$user) {
    $user = ['MaND'=>1,'TenDangNhap'=>'demo','HoTen'=>'Nguyen Van A',
             'Email'=>'demo@email.com','SoDienThoai'=>'0901234567',
             'DiaChi'=>'123 Nguyen Hue, Q.1, TP.HCM','VaiTro'=>0,'Avatar'=>''];
}

$avSrc = (!empty($user['Avatar']) && file_exists($user['Avatar']))
    ? $user['Avatar']
    : 'https://ui-avatars.com/api/?name='.urlencode($user['HoTen']).'&background=6366f1&color=fff&size=200';

$vMap  = [0=>'Khach hang',1=>'Nhan vien',2=>'Quan tri vien'];
$vTxt  = $vMap[$user['VaiTro']] ?? 'Khach hang';
$sMsg  = $success==='info' ? 'Cap nhat thong tin thanh cong!' : ($success==='avatar' ? 'Cap nhat anh dai dien thanh cong!' : '');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Tai Khoan Cua Toi</title>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--bg:#0f0f13;--s1:#1a1a24;--s2:#22222f;--bd:#2e2e40;--p:#6366f1;--ph:#4f46e5;--ac:#ec4899;--tx:#e2e2f0;--mu:#888899;--r:14px;--sh:0 8px 32px rgba(0,0,0,.45)}
body{font-family:'Segoe UI',system-ui,sans-serif;background:var(--bg);color:var(--tx);min-height:100vh;padding:24px 16px 60px}
.topbar{max-width:980px;margin:0 auto 28px;display:flex;align-items:center;gap:12px}
.logo{font-size:22px;font-weight:800;background:linear-gradient(135deg,var(--p),var(--ac));-webkit-background-clip:text;-webkit-text-fill-color:transparent}
.tr{margin-left:auto;display:flex;align-items:center;gap:10px;font-size:13px;color:var(--mu)}
.tav{width:34px;height:34px;border-radius:50%;object-fit:cover;border:2px solid var(--p)}
.lay{max-width:980px;margin:0 auto;display:grid;grid-template-columns:260px 1fr;gap:20px}
@media(max-width:700px){.lay{grid-template-columns:1fr}}
.card{background:var(--s1);border:1px solid var(--bd);border-radius:var(--r);padding:24px;box-shadow:var(--sh)}
.sb{display:flex;flex-direction:column;gap:20px}
.aw{display:flex;flex-direction:column;align-items:center;gap:14px}
.ar{position:relative;width:110px;height:110px}
.ar img{width:100%;height:100%;border-radius:50%;object-fit:cover;border:3px solid var(--p);box-shadow:0 0 20px rgba(99,102,241,.4)}
.ab{position:absolute;bottom:4px;right:4px;width:26px;height:26px;background:var(--p);border-radius:50%;border:2px solid var(--s1);display:flex;align-items:center;justify-content:center;font-size:12px;cursor:pointer;transition:.2s}
.ab:hover{background:var(--ph)}
.un{font-size:17px;font-weight:700}
.us{font-size:12px;color:var(--mu);margin-top:-10px}
.rbadge{padding:4px 12px;border-radius:20px;font-size:11px;font-weight:700;background:rgba(99,102,241,.2);color:var(--p);border:1px solid rgba(99,102,241,.3)}
.auf{width:100%}
.auf input[type=file]{display:none}
.ul{display:flex;align-items:center;justify-content:center;gap:8px;padding:9px;border:1.5px dashed var(--bd);border-radius:10px;cursor:pointer;font-size:13px;color:var(--mu);transition:.2s}
.ul:hover{border-color:var(--p);color:var(--p)}
#pw{display:none;flex-direction:column;align-items:center;gap:10px;margin-top:8px}
#pi{width:80px;height:80px;border-radius:50%;object-fit:cover;border:2px solid var(--p)}
.snav{display:flex;flex-direction:column;gap:4px}
.ni{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;font-size:14px;color:var(--mu);text-decoration:none;transition:.15s}
.ni:hover{background:var(--s2);color:var(--tx)}
.ni.act{background:rgba(99,102,241,.15);color:var(--p);font-weight:600}
.st{font-size:18px;font-weight:700;margin-bottom:20px;display:flex;align-items:center;gap:8px}
.st::after{content:'';flex:1;height:1px;background:var(--bd)}
.ig{display:grid;grid-template-columns:1fr 1fr;gap:14px}
@media(max-width:500px){.ig{grid-template-columns:1fr}}
.ii label{font-size:11px;text-transform:uppercase;letter-spacing:.6px;color:var(--mu);display:block;margin-bottom:5px}
.iv{background:var(--s2);border:1px solid var(--bd);border-radius:8px;padding:10px 14px;font-size:14px}
.fg{display:grid;grid-template-columns:1fr 1fr;gap:16px}
@media(max-width:500px){.fg{grid-template-columns:1fr}}
.fi{display:flex;flex-direction:column;gap:6px}
.fi.full{grid-column:1/-1}
.fi label{font-size:12px;color:var(--mu);text-transform:uppercase;letter-spacing:.5px}
.fi input,.fi textarea{background:var(--s2);border:1.5px solid var(--bd);border-radius:10px;color:var(--tx);font-size:14px;padding:10px 14px;outline:none;transition:.2s;font-family:inherit}
.fi input:focus,.fi textarea:focus{border-color:var(--p)}
.fi textarea{resize:vertical;min-height:72px}
.fi input:disabled{opacity:.5;cursor:not-allowed}
.tabs{display:flex;gap:4px;margin-bottom:24px;flex-wrap:wrap}
.tb{padding:8px 18px;border-radius:8px;font-size:13px;font-weight:600;border:1.5px solid var(--bd);background:transparent;color:var(--mu);cursor:pointer;transition:.2s}
.tb:hover{border-color:var(--p);color:var(--p)}
.tb.act{background:var(--p);border-color:var(--p);color:#fff}
.tp{display:none}
.tp.act{display:block}
.btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:11px 26px;border-radius:10px;font-size:14px;font-weight:600;border:none;cursor:pointer;transition:.2s}
.bp{background:linear-gradient(135deg,var(--p),var(--ph));color:#fff;box-shadow:0 4px 14px rgba(99,102,241,.4)}
.bp:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(99,102,241,.5)}
.bg2{background:var(--s2);color:var(--mu);border:1.5px solid var(--bd)}
.bg2:hover{color:var(--tx);border-color:var(--mu)}
.fa{display:flex;gap:10px;margin-top:24px;justify-content:flex-end}
.al{display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:10px;margin-bottom:20px;font-size:14px;animation:fIn .3s ease}
.ok{background:rgba(34,197,94,.12);border:1px solid rgba(34,197,94,.3);color:#4ade80}
.er{background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.3);color:#f87171}
@keyframes fIn{from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:none}}
::-webkit-scrollbar{width:6px}::-webkit-scrollbar-track{background:var(--bg)}::-webkit-scrollbar-thumb{background:var(--bd);border-radius:4px}
</style>
</head>
<body>

<div class="topbar">
  <div class="logo">&#x1F6CD; KhoaOngNghiem Tech</div>
  <div class="tr">
    <img class="tav" src="<?= htmlspecialchars($avSrc) ?>" alt="">
    <span><?= htmlspecialchars($user['TenDangNhap']) ?></span>
  </div>
</div>

<div class="lay">
  <aside class="sb">

    <div class="card aw">
      <div class="ar">
        <img id="mai" src="<?= htmlspecialchars($avSrc) ?>" alt="avatar">
        <div class="ab" onclick="document.getElementById('avi').click()">&#x270F;</div>
      </div>
      <div class="un"><?= htmlspecialchars($user['HoTen']) ?></div>
      <div class="us">@<?= htmlspecialchars($user['TenDangNhap']) ?></div>
      <div class="rbadge"><?= $vTxt ?></div>
      <form class="auf" method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="update_avatar">
        <input type="file" name="avatar" id="avi" accept="image/*" onchange="prevAv(this)">
        <label for="avi" class="ul">&#x1F4F7; Chon anh dai dien</label>
        <div id="pw">
          <img id="pi" src="#" alt="">
          <button type="submit" class="btn bp" style="width:100%;padding:9px">&#x2705; Luu anh</button>
          <button type="button" class="btn bg2" style="width:100%;padding:9px" onclick="cancelPrev()">Huy</button>
        </div>
      </form>
    </div>

    <div class="card">
      <nav class="snav">
        <a href="ChinhSuaProfile.php" class="ni act">👤 Ho so ca nhan</a>
        <a href="DonHang.php" class="ni">📦 Don hang cua toi</a>
        <a href="YeuThich.php" class="ni">❤️ San pham yeu thich</a>
        <a href="DiaChi.php" class="ni">🏠 Dia chi giao hang</a>
        <a href="DangXuat.php" class="ni" style="color:#ef4444">🚪 Dang xuat</a>
      </nav>
    </div>

  </aside>

  <main>
    <div class="card">

      <?php if ($sMsg): ?><div class="al ok">&#x2705; <?= htmlspecialchars($sMsg) ?></div><?php endif; ?>
      <?php if ($error): ?><div class="al er">&#x274C; <?= htmlspecialchars($error) ?></div><?php endif; ?>

      <div class="tabs">
        <button class="tb act" onclick="sw('view',this)">&#x1F441; Xem thong tin</button>
        <button class="tb" onclick="sw('edit',this)">&#x270F; Chinh sua</button>
      </div>

      <div id="tv" class="tp act">
        <div class="st">Thong tin ca nhan</div>
        <div class="ig">
          <div class="ii"><label>Ho va ten</label><div class="iv"><?= htmlspecialchars($user['HoTen']) ?></div></div>
          <div class="ii"><label>Ten dang nhap</label><div class="iv"><?= htmlspecialchars($user['TenDangNhap']) ?></div></div>
          <div class="ii"><label>Email</label><div class="iv"><?= htmlspecialchars($user['Email'] ?? '—') ?></div></div>
          <div class="ii"><label>So dien thoai</label><div class="iv"><?= htmlspecialchars($user['SoDienThoai'] ?? '—') ?></div></div>
          <div class="ii" style="grid-column:1/-1"><label>Dia chi</label><div class="iv"><?= htmlspecialchars($user['DiaChi'] ?? '—') ?></div></div>
          <div class="ii"><label>Vai tro</label><div class="iv"><?= $vTxt ?></div></div>
        </div>
        <div class="fa"><button class="btn bp" onclick="swn('edit')">&#x270F; Chinh sua ngay</button></div>
      </div>

      <div id="te" class="tp">
        <div class="st">Chinh sua thong tin</div>
        <form method="post">
          <input type="hidden" name="action" value="update_info">
          <div class="fg">
            <div class="fi">
              <label>Ho va ten <span style="color:#ef4444">*</span></label>
              <input type="text" name="HoTen" value="<?= htmlspecialchars($user['HoTen']) ?>" required>
            </div>
            <div class="fi">
              <label>Ten dang nhap</label>
              <input type="text" value="<?= htmlspecialchars($user['TenDangNhap']) ?>" disabled>
            </div>
            <div class="fi">
              <label>Email <span style="color:#ef4444">*</span></label>
              <input type="email" name="Email" value="<?= htmlspecialchars($user['Email'] ?? '') ?>" required>
            </div>
            <div class="fi">
              <label>So dien thoai</label>
              <input type="tel" name="SoDienThoai" value="<?= htmlspecialchars($user['SoDienThoai'] ?? '') ?>" placeholder="0901234567">
            </div>
            <div class="fi full">
              <label>Dia chi</label>
              <textarea name="DiaChi"><?= htmlspecialchars($user['DiaChi'] ?? '') ?></textarea>
            </div>
          </div>
          <div class="fa">
            <button type="button" class="btn bg2" onclick="swn('view')">Huy</button>
            <button type="submit" class="btn bp">&#x1F4BE; Luu thay doi</button>
          </div>
        </form>
      </div>

    </div>
  </main>
</div>

<script>
const panels={view:'tv',edit:'te'};
function sw(name,btn){
  Object.values(panels).forEach(id=>document.getElementById(id).classList.remove('act'));
  document.querySelectorAll('.tb').forEach(b=>b.classList.remove('act'));
  document.getElementById(panels[name]).classList.add('act');
  btn.classList.add('act');
}
function swn(name){const btn=document.querySelector('.tb[onclick*="'+name+'"]');if(btn)sw(name,btn);}
function prevAv(input){
  if(!input.files[0])return;
  const r=new FileReader();
  r.onload=e=>{
    document.getElementById('pi').src=e.target.result;
    document.getElementById('pw').style.display='flex';
    document.getElementById('mai').src=e.target.result;
  };
  r.readAsDataURL(input.files[0]);
}
function cancelPrev(){
  document.getElementById('avi').value='';
  document.getElementById('pw').style.display='none';
  document.getElementById('mai').src=<?= json_encode($avSrc) ?>;
}
<?php if($success==='info'):?>window.onload=()=>swn('view');<?php endif;?>
</script>
</body>
</html>
<?php sqlsrv_close($conn); ?>