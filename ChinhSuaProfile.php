<?php
// ============================================================
//  KET NOI DATABASE SQL SERVER
// ============================================================

session_start();

$serverName     = "localhost\\SQLEXPRESS";
$connectionInfo = ["Database"=>"QLBanHang","TrustServerCertificate"=>true,"CharacterSet"=>"UTF-8"];
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) die(print_r(sqlsrv_errors(), true));

if (!isset($_SESSION['MaND'])) { header('Location: DangNhap.php'); exit; }
$user_id = (int)$_SESSION['MaND'];

// Tạo thư mục lưu ảnh Avatar và Ảnh Sản Phẩm nếu chưa có
$uploadPath = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'avatars';
if (!file_exists($uploadPath)) mkdir($uploadPath, 0777, true);
define('UPLOAD_DIR', $uploadPath . DIRECTORY_SEPARATOR);

$uploadProdPath = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'products';
if (!file_exists($uploadProdPath)) mkdir($uploadProdPath, 0777, true);

$success = ""; $error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. XỬ LÝ CẬP NHẬT THÔNG TIN CÁ NHÂN (Dành cho Khách hàng)
    if (($_POST['action'] ?? '') === 'update_info') {
        $hoten  = trim($_POST['HoTen']      ?? '');
        $email  = trim($_POST['Email']       ?? '');
        $sdt    = trim($_POST['SoDienThoai'] ?? '');
        $diachi = trim($_POST['DiaChi']      ?? '');

        if (empty($hoten)) {
            $error = "Họ tên không được để trống.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Email không hợp lệ.";
        } else {
            $chk = sqlsrv_query($conn, "SELECT MaND FROM dbo.NguoiDung WHERE Email=? AND MaND!=?", [$email, $user_id]);
            if ($chk && sqlsrv_fetch($chk)) {
                $error = "Email này đã được dùng bởi tài khoản khác.";
            } else {
                $res = sqlsrv_query($conn, "UPDATE dbo.NguoiDung SET HoTen=?,Email=?,SoDienThoai=?,DiaChi=? WHERE MaND=?", [$hoten,$email,$sdt,$diachi,$user_id]);
                if ($res) $success = "info"; else $error = "Lỗi cập nhật DB.";
            }
        }
    }

    // 2. XỬ LÝ THÊM SẢN PHẨM MỚI (Dành riêng cho Admin)
    if (($_POST['action'] ?? '') === 'add_product') {
        $tensp      = trim($_POST['TenSP'] ?? '');
        $madm       = (int)($_POST['MaDM'] ?? 1);
        $gia        = (float)($_POST['Gia'] ?? 0);
        $soluong    = (int)($_POST['SoLuongTon'] ?? 0);
        $mota       = trim($_POST['MoTa'] ?? '');
        $cpu        = trim($_POST['CPU'] ?? '');
        $ram        = trim($_POST['RAM'] ?? '');
        $ocung      = trim($_POST['O_Cung'] ?? '');
        $manhinh    = trim($_POST['ManHinh'] ?? '');
        $baohanh    = trim($_POST['BaoHanh'] ?? '');
        
        $hinhanh = ''; // Đường dẫn ảnh mặc định

        // Xử lý upload ảnh sản phẩm
        if (isset($_FILES['HinhAnh']) && $_FILES['HinhAnh']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['HinhAnh']['name'], PATHINFO_EXTENSION);
            $fileName = 'sp_' . time() . '.' . $ext;
            $dest = $uploadProdPath . DIRECTORY_SEPARATOR . $fileName;
            
            if (move_uploaded_file($_FILES['HinhAnh']['tmp_name'], $dest)) {
                $hinhanh = 'uploads/products/' . $fileName; // Lưu đường dẫn tương đối vào SQL
            }
        }

        if (empty($tensp) || $gia <= 0) {
            $error = "Vui lòng nhập Tên sản phẩm và Giá bán lớn hơn 0!";
        } else {
            $sql_add = "INSERT INTO SanPham (TenSP, MaDM, Gia, SoLuongTon, MoTa, CPU, RAM, O_Cung, ManHinh, BaoHanh, HinhAnh) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $params_add = [$tensp, $madm, $gia, $soluong, $mota, $cpu, $ram, $ocung, $manhinh, $baohanh, $hinhanh];
            
            $res_add = sqlsrv_query($conn, $sql_add, $params_add);
            if ($res_add) $success = "product";
            else $error = "Lỗi thêm sản phẩm: " . print_r(sqlsrv_errors(), true);
        }
    }

    // 3. XỬ LÝ CẬP NHẬT AVATAR
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
                $dest     = UPLOAD_DIR . 'av_' . $user_id . '_' . time() . '.' . $ext;
                $destWeb  = 'uploads/avatars/' . 'av_' . $user_id . '_' . time() . '.' . $ext;
                if (move_uploaded_file($f['tmp_name'], $dest)) {
                    $old = sqlsrv_query($conn,"SELECT Avatar FROM dbo.NguoiDung WHERE MaND=?",[$user_id]);
                    if ($old && $row = sqlsrv_fetch_array($old, SQLSRV_FETCH_ASSOC)) {
                        if (!empty($row['Avatar']) && file_exists($row['Avatar'])) unlink($row['Avatar']);
                    }
                    $up = sqlsrv_query($conn,"UPDATE NguoiDung SET Avatar=? WHERE MaND=?",[$destWeb,$user_id]);
                    if ($up) $success = "avatar";
                    else $error = "Lỗi lưu ảnh vào DB.";
                } else { $error = "Không thể lưu file. Kiểm tra quyền thư mục."; }
            }
        } else { $error = "Vui lòng chọn file ảnh."; }
    }
}

$res  = sqlsrv_query($conn,"SELECT * FROM dbo.NguoiDung WHERE MaND=?",[$user_id]);
$user = $res ? sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC) : null;

$avSrc = (!empty($user['Avatar']) && file_exists($user['Avatar']))
    ? $user['Avatar']
    : 'https://ui-avatars.com/api/?name='.urlencode($user['HoTen']).'&background=6366f1&color=fff&size=200';

$vMap = [0=>'Khách hàng', 1=>'Quản trị viên'];
$vTxt  = $vMap[$user['VaiTro']] ?? 'Khách hàng';

// Thông báo thành công
if ($success === 'info') $sMsg = 'Cập nhật thông tin thành công!';
elseif ($success === 'avatar') $sMsg = 'Cập nhật ảnh đại diện thành công!';
elseif ($success === 'product') $sMsg = 'Đã thêm SẢN PHẨM MỚI vào cửa hàng thành công!';
else $sMsg = '';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@300;400;600;700;900&family=Orbitron:wght@400;700;900&display=swap" rel="stylesheet">
<title>Tài Khoản / Quản Trị</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Exo+2:wght@300;400;500;600;700;800;900&family=Orbitron:wght@400;700;900&display=swap');

:root {
  --navy:    #050d1a;
  --navy2:   #071223;
  --panel:   #0d1f38;
  --panel2:  #0f2444;
  --cyan:    #00e5ff;
  --cyan2:   #00b8d4;
  --purple:  #7c3aed;
  --purple2: #a855f7;
  --green:   #22c55e;
  --tx:      #e2eaf5;
  --muted:   #7a92b0;
  --border:  rgba(0,229,255,0.12);
  --glow-cyan:   0 0 20px rgba(0,229,255,0.4);
  --glow-purple: 0 0 20px rgba(168,85,247,0.4);
  --r: 14px;
}

*,*::before,*::after { box-sizing:border-box; margin:0; padding:0; }

a.back { border: 2px solid #242342; border-radius: 8px; width: 100px; height: 30px; display: flex; justify-content: center; text-decoration: none; color: #bbbbbb; align-items: center; }
body { font-family: 'Exo 2', system-ui, sans-serif; background: var(--navy); color: var(--tx); min-height: 100vh; padding: 24px 16px 60px; }

::-webkit-scrollbar { width: 6px; }
::-webkit-scrollbar-track { background: var(--navy2); }
::-webkit-scrollbar-thumb { background: var(--cyan2); border-radius: 3px; }

.topbar { max-width: 980px; margin: 0 auto 28px; display: flex; align-items: center; gap: 12px; background: rgba(5,13,26,0.92); backdrop-filter: blur(20px); border: 1px solid var(--border); border-radius: var(--r); padding: 12px 20px; }
.logo { font-family: 'Orbitron', monospace; font-size: 18px; font-weight: 900; letter-spacing: 0.05em; background: linear-gradient(90deg, var(--cyan), var(--purple2)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
.tr { margin-left: auto; display: flex; align-items: center; gap: 10px; font-size: 13px; color: var(--muted); }
.tav { width: 34px; height: 34px; border-radius: 50%; object-fit: cover; border: 2px solid var(--cyan); box-shadow: var(--glow-cyan); }

.lay { max-width: 980px; margin: 0 auto; display: grid; grid-template-columns: 260px 1fr; gap: 20px; }
@media (max-width: 700px) { .lay { grid-template-columns: 1fr; } }

.card { background: var(--panel); border: 1px solid var(--border); border-radius: var(--r); padding: 24px; box-shadow: 0 8px 32px rgba(0,0,0,.5); transition: border-color 0.3s; }
.card:hover { border-color: rgba(0,229,255,0.25); }

.sb { display: flex; flex-direction: column; gap: 20px; }
.aw { display: flex; flex-direction: column; align-items: center; gap: 14px; }
.ar { position: relative; width: 110px; height: 110px; }
.ar img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; border: 3px solid var(--cyan); box-shadow: var(--glow-cyan); }
.ab { position: absolute; bottom: 4px; right: 4px; width: 28px; height: 28px; background: var(--purple); border-radius: 50%; border: 2px solid var(--panel); display: flex; align-items: center; justify-content: center; font-size: 12px; cursor: pointer; transition: .2s; box-shadow: var(--glow-purple); }
.ab:hover { background: var(--purple2); }
.un { font-family: 'Orbitron', monospace; font-size: 15px; font-weight: 700; color: var(--tx); }
.us { font-size: 12px; color: var(--muted); margin-top: -10px; }
.rb { padding: 4px 14px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; background: rgba(0,229,255,0.1); color: var(--cyan); border: 1px solid rgba(0,229,255,0.3); }

.auf { width: 100%; }
.auf input[type=file] { display: none; }
.ul { display: flex; align-items: center; justify-content: center; gap: 8px; padding: 9px; border: 1.5px dashed rgba(0,229,255,0.3); border-radius: 10px; cursor: pointer; font-size: 13px; color: var(--muted); transition: .2s; }
.ul:hover { border-color: var(--cyan); color: var(--cyan); box-shadow: 0 0 10px rgba(0,229,255,0.1); }
#pw { display: none; flex-direction: column; align-items: center; gap: 10px; margin-top: 8px; }
#pi { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 2px solid var(--cyan); }

.snav { display: flex; flex-direction: column; gap: 4px; }
.ni { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 10px; font-size: 14px; color: var(--muted); text-decoration: none; transition: .15s; border: 1px solid transparent; }
.ni:hover { background: rgba(0,229,255,0.06); color: var(--cyan); border-color: var(--border); }
.ni.act { background: rgba(0,229,255,0.1); color: var(--cyan); border-color: rgba(0,229,255,0.3); font-weight: 600; box-shadow: 0 0 15px rgba(0,229,255,0.08); }

.st { font-family: 'Orbitron', monospace; font-size: 14px; font-weight: 700; letter-spacing: 0.05em; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; color: var(--cyan); }
.st::after { content: ''; flex: 1; height: 1px; background: linear-gradient(90deg, rgba(0,229,255,0.4), transparent); }

.ig { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
@media (max-width: 500px) { .ig { grid-template-columns: 1fr; } }
.ii label { font-size: 10px; text-transform: uppercase; color: var(--cyan); display: block; margin-bottom: 5px; font-weight: 600; }
.iv { background: var(--panel2); border: 1px solid var(--border); border-radius: 8px; padding: 10px 14px; font-size: 14px; color: var(--tx); }

.fg { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
@media (max-width: 500px) { .fg { grid-template-columns: 1fr; } }
.fi { display: flex; flex-direction: column; gap: 6px; }
.fi.full { grid-column: 1/-1; }
.fi label { font-size: 10px; color: var(--cyan); text-transform: uppercase; font-weight: 600; }
.fi input, .fi textarea, .fi select { background: var(--panel2); border: 1.5px solid var(--border); border-radius: 10px; color: var(--tx); font-size: 14px; font-family: 'Exo 2', sans-serif; padding: 10px 14px; outline: none; transition: .2s; }
.fi input:focus, .fi textarea:focus, .fi select:focus { border-color: var(--cyan); box-shadow: 0 0 0 3px rgba(0,229,255,0.1); }
.fi textarea { resize: vertical; min-height: 72px; }
.fi input:disabled { opacity: .4; cursor: not-allowed; }

.tabs { display: flex; gap: 6px; margin-bottom: 24px; }
.tb { padding: 8px 20px; border-radius: 8px; font-size: 12px; font-weight: 700; text-transform: uppercase; border: 1.5px solid var(--border); background: transparent; color: var(--muted); cursor: pointer; transition: .2s; }
.tb:hover { border-color: var(--cyan); color: var(--cyan); }
.tb.act { background: rgba(0,229,255,0.12); border-color: var(--cyan); color: var(--cyan); box-shadow: var(--glow-cyan); }
.tp { display: none; }
.tp.act { display: block; }

.btn { display: inline-flex; align-items: center; gap: 8px; padding: 11px 26px; border-radius: 10px; font-size: 13px; font-weight: 700; text-transform: uppercase; border: none; cursor: pointer; transition: .2s; }
.bp { background: linear-gradient(135deg, var(--green), #16a34a); color: #fff; box-shadow: 0 4px 14px rgba(34,197,94,0.35); }
.bp:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(34,197,94,0.5); }
.bg2 { background: var(--panel2); color: var(--muted); border: 1.5px solid var(--border); }
.bg2:hover { color: var(--tx); border-color: var(--muted); }
.fa { display: flex; gap: 10px; margin-top: 24px; justify-content: flex-end; }

.al { display: flex; align-items: center; gap: 10px; padding: 12px 16px; border-radius: 10px; margin-bottom: 20px; font-size: 14px; animation: fadeIn .3s ease; }
.ok { background: rgba(34,197,94,.1); border: 1px solid rgba(34,197,94,.3); color: #4ade80; }
.er { background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.3); color: #f87171; }
@keyframes fadeIn { from { opacity:0; transform:translateY(-6px); } to { opacity:1; transform:none; } }
</style>
</head>
<body>

<div class="topbar">
  <div class="logo">&#x1F6CD; KhoaOngNghiem Tech</div>
  <div class="tr">
    <img class="tav" src="<?= htmlspecialchars($avSrc) ?>" alt="">
    <span><?= htmlspecialchars($user['TenDangNhap']) ?></span>
  </div>
  <a href="TrangChuDaDangNhap.php" class="back">&#x2190; Trang Chủ</a>
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
        <label for="avi" class="ul">&#x1F4F7; Chọn ảnh đại diện</label>
        <div id="pw">
          <img id="pi" src="#" alt="">
          <button type="submit" class="btn bp" style="width:100%;padding:9px">&#x2705; Lưu ảnh</button>
          <button type="button" class="btn bg2" style="width:100%;padding:9px" onclick="cancelPrev()">Hủy</button>
        </div>
      </form>
    </div>
    
    <div class="card">
      <nav class="snav">
        <a href="ChinhSuaProfile.php" class="ni act">👤 Ho so ca nhan</a>
<?php if ($user['VaiTro'] == 0): ?>
            <a href="DonHang.php" class="ni">📦 Don hang cua toi</a>
            <a href="YeuThich.php" class="ni">❤️ San pham yeu thich</a>
            <a href="diachigiaohang.php" class="ni">🏠 Dia chi giao hang</a>
        <?php endif; ?>        <!-- THEM DOAN NAY -->
        <?php if ($user['VaiTro'] == 1): ?>
        <a href="QuanLyMaGiamGia.php" class="ni">🎫 Quan ly ma giam gia</a>
        <a href="QuanLyDonHang.php" class="ni">📦 Quản lý đơn hàng</a>
        <a href="QuanLyNguoiDung.php" class="ni">&#x1F6E1; Quản lý người dùng</a>
        <a href="QuanLyTinNhan.php" class="ni">&#x1F4AC; Quản lý tin nhắn</a>
        <?php endif; ?>
       
        <a href="DangXuat.php" class="ni" style="color:#ef4444">🚪 Đăng xuất</a>
      </nav>
    </div>
  </aside>

  <main>
    <div class="card">

      <?php if ($sMsg): ?><div class="al ok">&#x2705; <?= htmlspecialchars($sMsg) ?></div><?php endif; ?>
      <?php if ($error): ?><div class="al er">&#x274C; <?= htmlspecialchars($error) ?></div><?php endif; ?>

      <div class="tabs">
        <button class="tb act" onclick="sw('view',this)">&#x1F441; Xem thông tin</button>
        
        <?php if ($user['VaiTro'] == 1): ?>
            <button class="tb" onclick="sw('add_sp',this)">&#x1F4E6; Thêm Sản Phẩm</button>
        <?php else: ?>
            <button class="tb" onclick="sw('edit',this)">&#x270F; Chỉnh sửa</button>
        <?php endif; ?>
      </div>

      <div id="tv" class="tp act">
        <div class="st">Thông tin cá nhân</div>
        <div class="ig">
          <div class="ii"><label>Họ và tên</label><div class="iv"><?= htmlspecialchars($user['HoTen']) ?></div></div>
          <div class="ii"><label>Tên đăng nhập</label><div class="iv"><?= htmlspecialchars($user['TenDangNhap']) ?></div></div>
          <div class="ii"><label>Email</label><div class="iv"><?= htmlspecialchars($user['Email'] ?? '—') ?></div></div>
          <div class="ii"><label>Số điện thoại</label><div class="iv"><?= htmlspecialchars($user['SoDienThoai'] ?? '—') ?></div></div>
          <div class="ii" style="grid-column:1/-1">
            <label>Địa chỉ</label>
            <div class="iv"><?= htmlspecialchars($user['DiaChi'] ?? '—') ?></div>
            <?php if ($user['VaiTro'] == 0): ?>
            <div style="margin-top: 10px;">
                <a href="diachigiaohang.php" class="btn bg2" style="text-decoration: none; border-color: var(--cyan); color: var(--cyan);">
                    📍 QUẢN LÝ SỔ ĐỊA CHỈ GIAO HÀNG
                </a>
            </div>
            <?php endif; ?>
          </div>
          <div class="ii"><label>Vai trò</label><div class="iv"><?= $vTxt ?></div></div>
        </div>
        
        <div class="fa">
            <?php if ($user['VaiTro'] == 1): ?>
                <button class="btn bp" onclick="swn('add_sp')">&#x1F4E6; Thêm Sản Phẩm Ngay</button>
            <?php else: ?>
                <button class="btn bp" onclick="swn('edit')">&#x270F; Chỉnh sửa ngay</button>
            <?php endif; ?>
        </div>
      </div>

      <?php if ($user['VaiTro'] == 0): ?>
      <div id="te" class="tp">
        <div class="st">Chỉnh sửa thông tin</div>
        <form method="post">
          <input type="hidden" name="action" value="update_info">
          <div class="fg">
            <div class="fi">
              <label>Họ và tên <span style="color:#ef4444">*</span></label>
              <input type="text" name="HoTen" value="<?= htmlspecialchars($user['HoTen']) ?>" required>
            </div>
            <div class="fi">
              <label>Tên đăng nhập</label>
              <input type="text" value="<?= htmlspecialchars($user['TenDangNhap']) ?>" disabled>
            </div>
            <div class="fi">
              <label>Email <span style="color:#ef4444">*</span></label>
              <input type="email" name="Email" value="<?= htmlspecialchars($user['Email'] ?? '') ?>" required>
            </div>
            <div class="fi">
              <label>Số điện thoại</label>
              <input type="tel" name="SoDienThoai" value="<?= htmlspecialchars($user['SoDienThoai'] ?? '') ?>" placeholder="0901234567">
            </div>
            <div class="fi full">
              <label>Địa chỉ</label>
              <textarea name="DiaChi"><?= htmlspecialchars($user['DiaChi'] ?? '') ?></textarea>
            </div>
          </div>
          <div class="fa">
            <button type="button" class="btn bg2" onclick="swn('view')">Hủy</button>
            <button type="submit" class="btn bp">&#x1F4BE; Lưu thay đổi</button>
          </div>
        </form>
      </div>
      <?php endif; ?>

      <?php if ($user['VaiTro'] == 1): ?>
      <div id="ts" class="tp">
        <div class="st">Thêm sản phẩm bán hàng (Dành cho Admin)</div>
        <form method="post" enctype="multipart/form-data">
          <input type="hidden" name="action" value="add_product">
          <div class="fg">
            <div class="fi full">
              <label>Tên sản phẩm (Laptop, iPhone...) <span style="color:#ef4444">*</span></label>
              <input type="text" name="TenSP" placeholder="VD: Laptop Dell XPS 15..." required>
            </div>
            <div class="fi">
              <label>Danh mục</label>
              <select name="MaDM">
                <option value="1">💻 Laptop</option>
                <option value="2">📱 Điện thoại</option>
              </select>
            </div>
            <div class="fi">
              <label>Giá bán (VNĐ) <span style="color:#ef4444">*</span></label>
              <input type="number" name="Gia" placeholder="VD: 35000000" required>
            </div>
            <div class="fi">
              <label>Số lượng trong kho <span style="color:#ef4444">*</span></label>
              <input type="number" name="SoLuongTon" value="10" required>
            </div>
            <div class="fi">
              <label>Ảnh sản phẩm (Tùy chọn)</label>
              <input type="file" name="HinhAnh" accept="image/*" style="padding: 7px;">
            </div>
            
            <div class="fi full"><div class="st" style="font-size:12px; margin: 15px 0 0 0;">Cấu hình chi tiết (Tùy chọn)</div></div>

            <div class="fi">
              <label>CPU</label>
              <input type="text" name="CPU" placeholder="VD: Core i9 13900H / A17 Pro">
            </div>
            <div class="fi">
              <label>RAM</label>
              <input type="text" name="RAM" placeholder="VD: 16GB / 8GB">
            </div>
            <div class="fi">
              <label>Ổ Cứng</label>
              <input type="text" name="O_Cung" placeholder="VD: 1TB SSD NVMe">
            </div>
            <div class="fi">
              <label>Màn hình</label>
              <input type="text" name="ManHinh" placeholder="VD: 16 inch 4K OLED">
            </div>
            <div class="fi">
              <label>Thời gian bảo hành</label>
              <input type="text" name="BaoHanh" placeholder="VD: 24 tháng chính hãng">
            </div>
            
            <div class="fi full">
              <label>Mô tả sản phẩm</label>
              <textarea name="MoTa" placeholder="Nhập đoạn văn quảng cáo hoặc mô tả ngắn..."></textarea>
            </div>
          </div>
          <div class="fa">
            <button type="button" class="btn bg2" onclick="swn('view')">Hủy bỏ</button>
            <button type="submit" class="btn bp">&#x1F4E6; Đăng bán sản phẩm</button>
          </div>
        </form>
      </div>
      <?php endif; ?>

    </div>
  </main>
</div>

<script>
// JS phân luồng tab theo Role
<?php if ($user['VaiTro'] == 1): ?>
    const panels = {view:'tv', add_sp:'ts'};
<?php else: ?>
    const panels = {view:'tv', edit:'te'};
<?php endif; ?>

function sw(name,btn){
  Object.values(panels).forEach(id => document.getElementById(id).classList.remove('act'));
  document.querySelectorAll('.tb').forEach(b => b.classList.remove('act'));
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
<?php if($success==='product'):?>window.onload=()=>swn('add_sp');<?php endif;?>
</script>
</body>
</html>
<?php sqlsrv_close($conn); ?>