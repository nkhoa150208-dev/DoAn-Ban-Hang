<?php
session_start();

// 1. KẾT NỐI DATABASE
$serverName     = "localhost\\SQLEXPRESS";
$connectionInfo = ["Database"=>"QLBanHang","TrustServerCertificate"=>true,"CharacterSet"=>"UTF-8"];
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) die(print_r(sqlsrv_errors(), true));

// 2. KIỂM TRA ĐĂNG NHẬP
if (!isset($_SESSION['MaND'])) { header('Location: DangNhap.php'); exit; }
$user_id = (int)$_SESSION['MaND'];

// 3. LẤY THÔNG TIN USER
$res  = sqlsrv_query($conn, "SELECT * FROM dbo.NguoiDung WHERE MaND=?", [$user_id]);
$user = $res ? sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC) : null;
if (!$user) { session_destroy(); header('Location: DangNhap.php'); exit; }

$success = isset($_GET['s']) ? $_GET['s'] : "";
$error = isset($_GET['e']) ? $_GET['e'] : "";
$sMsg = ""; 

if ($success === 'info') $sMsg = 'Cập nhật thông tin thành công!';
elseif ($success === 'avatar') $sMsg = 'Cập nhật ảnh đại diện thành công!';
elseif ($success === 'product') $sMsg = 'Thêm sản phẩm thành công!';

$vTxt = ((int)$user['VaiTro'] === 1) ? "Quản trị viên" : "Khách hàng";

$uploadPath = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'avatars';
if (!file_exists($uploadPath)) mkdir($uploadPath, 0777, true);

$uploadProdPath = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'products';
if (!file_exists($uploadProdPath)) mkdir($uploadProdPath, 0777, true);

// 6. XỬ LÝ CÁC YÊU CẦU POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // --- A. CẬP NHẬT ẢNH ĐẠI DIỆN ---
    if ($action === 'update_avatar') {
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
            $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $fileName = 'av_' . $user_id . '_' . time() . '.' . $ext;
            $destPath = $uploadPath . DIRECTORY_SEPARATOR . $fileName;
            $dbPath = 'uploads/avatars/' . $fileName;

            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $destPath)) {
                sqlsrv_query($conn, "UPDATE dbo.NguoiDung SET Avatar = ? WHERE MaND = ?", [$dbPath, $user_id]);
                header('Location: ChinhSuaProfile.php?s=avatar');
                exit;
            }
        }
        $error = "Không thể tải ảnh lên.";
    }

    // --- B. AJAX: CẬP NHẬT ẢNH SẢN PHẨM ---
    if ($action === 'update_product_img_ajax') {
        if ((int)$user['VaiTro'] !== 1) { echo "Unauthorized"; exit; }
        $idSP = (int)$_POST['id'];
        if (isset($_FILES['HinhAnh']) && $_FILES['HinhAnh']['error'] === 0) {
            $ext = pathinfo($_FILES['HinhAnh']['name'], PATHINFO_EXTENSION);
            $fileName = 'prod_' . $idSP . '_' . time() . '.' . $ext;
            $destPath = $uploadProdPath . DIRECTORY_SEPARATOR . $fileName;
            $dbPath = 'uploads/products/' . $fileName;

            if (move_uploaded_file($_FILES['HinhAnh']['tmp_name'], $destPath)) {
                sqlsrv_query($conn, "UPDATE SanPham SET HinhAnh = ? WHERE MaSP = ?", [$dbPath, $idSP]);
                echo $dbPath; 
            } else { echo "Error moving file"; }
        }
        exit;
    }

    // --- C. AJAX: CẬP NHẬT KHO ---
    if ($action === 'quick_update_stock') {
        if ((int)$user['VaiTro'] !== 1) { echo "Unauthorized"; exit; }
        $idSP = (int)$_POST['id'];
        $qty = (int)$_POST['qty'];
        $stmt = sqlsrv_query($conn, "UPDATE SanPham SET SoLuongTon = ? WHERE MaSP = ?", [$qty, $idSP]);
        echo ($stmt) ? "OK" : "Error";
        exit;
    }

    // --- D. THÊM SẢN PHẨM MỚI ---
    if ($action === 'add_product' && $user['VaiTro'] == 1) {
        $ten = trim($_POST['TenSP'] ?? '');
        $madm = (int)($_POST['MaDM'] ?? 1);
        $gia = (float)($_POST['Gia'] ?? 0);
        $kho = (int)($_POST['SoLuongTon'] ?? 0);
        $mota = trim($_POST['MoTa'] ?? '');
        
        // Nhận dữ liệu cấu hình thực tế
        $cpu = trim($_POST['CPU'] ?? '');
        $ram = trim($_POST['RAM'] ?? '');
        $ocung = trim($_POST['O_Cung'] ?? '');
        $manhinh = trim($_POST['ManHinh'] ?? '');
        $vga = trim($_POST['VGA'] ?? '');
        $camera = trim($_POST['Camera'] ?? '');
        $pin = trim($_POST['Pin'] ?? '');
        $ketnoi = trim($_POST['KetNoi'] ?? '');
        $tuongthich = trim($_POST['TuongThich'] ?? '');
        $baohanh = trim($_POST['BaoHanh'] ?? '');

        $hinhAnh = "";
        if (isset($_FILES['HinhAnh']) && $_FILES['HinhAnh']['error'] === 0) {
            $fileName = 'prod_new_' . time() . '.' . pathinfo($_FILES['HinhAnh']['name'], PATHINFO_EXTENSION);
            if (move_uploaded_file($_FILES['HinhAnh']['tmp_name'], $uploadProdPath . DIRECTORY_SEPARATOR . $fileName)) {
                $hinhAnh = 'uploads/products/' . $fileName;
            }
        }

        $sql = "INSERT INTO SanPham (TenSP, MaDM, Gia, SoLuongTon, MoTa, CPU, RAM, O_Cung, ManHinh, VGA, Camera, Pin, KetNoi, TuongThich, BaoHanh, HinhAnh) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $params = [$ten, $madm, $gia, $kho, $mota, $cpu, $ram, $ocung, $manhinh, $vga, $camera, $pin, $ketnoi, $tuongthich, $baohanh, $hinhAnh];
        
        sqlsrv_query($conn, $sql, $params);
        header('Location: ChinhSuaProfile.php?s=product');
        exit;
    }
    
    // --- E. CẬP NHẬT THÔNG TIN CÁ NHÂN ---
    if ($action === 'update_info') {
        $hoTen = $_POST['HoTen'];
        $email = $_POST['Email'];
        $sdt = $_POST['SoDienThoai'];
        $diaChi = $_POST['DiaChi'];
        
        sqlsrv_query($conn, "UPDATE NguoiDung SET HoTen=?, Email=?, SoDienThoai=?, DiaChi=? WHERE MaND=?", [$hoTen, $email, $sdt, $diaChi, $user_id]);
        header('Location: ChinhSuaProfile.php?s=info');
        exit;
    }

    // --- F. XÓA SẢN PHẨM ---
    if ($action === 'delete_product' && $user['VaiTro']==1) {
        $idDel = (int)($_POST['MaSP']??0);
        sqlsrv_query($conn, "DELETE FROM ChiTietDonHang WHERE MaSP=?", [$idDel]);
        sqlsrv_query($conn, "DELETE FROM YeuThich WHERE MaSP=?", [$idDel]);
        sqlsrv_query($conn, "DELETE FROM SanPham WHERE MaSP=?", [$idDel]);
        $sMsg = "Đã xóa sản phẩm #$idDel";
    }

    // --- G. SỬA SẢN PHẨM ---
    if ($action === 'edit_product' && $user['VaiTro']==1) {
        $idEdit  = (int)($_POST['MaSP']??0);
        $tenSP   = trim($_POST['TenSP']   ?? '');
        $gia     = (float)($_POST['Gia']  ?? 0);
        $slTon   = (int)($_POST['SoLuongTon'] ?? 0);
        $maDM    = (int)($_POST['MaDM']   ?? 1);
        $moTa    = trim($_POST['MoTa']    ?? '');
        
        $cpu     = trim($_POST['CPU']     ?? '');
        $ram     = trim($_POST['RAM']     ?? '');
        $oCung   = trim($_POST['O_Cung']  ?? '');
        $manHinh = trim($_POST['ManHinh'] ?? '');
        $vga     = trim($_POST['VGA']     ?? '');
        $camera  = trim($_POST['Camera']  ?? '');
        $pin     = trim($_POST['Pin']     ?? '');
        $ketnoi  = trim($_POST['KetNoi']  ?? '');
        $tuongthich = trim($_POST['TuongThich'] ?? '');
        $baoHanh = trim($_POST['BaoHanh'] ?? '');
        
        $sql_update = "UPDATE SanPham SET TenSP=?, Gia=?, SoLuongTon=?, MaDM=?, CPU=?, RAM=?, O_Cung=?, ManHinh=?, VGA=?, Camera=?, Pin=?, KetNoi=?, TuongThich=?, BaoHanh=?, MoTa=? WHERE MaSP=?";
        sqlsrv_query($conn, $sql_update, [$tenSP, $gia, $slTon, $maDM, $cpu, $ram, $oCung, $manHinh, $vga, $camera, $pin, $ketnoi, $tuongthich, $baoHanh, $moTa, $idEdit]);
        $sMsg = "Đã cập nhật sản phẩm #$idEdit";
    }
}

$avSrc = (!empty($user['Avatar']) && file_exists(__DIR__ . '/' . $user['Avatar'])) 
         ? $user['Avatar'] 
         : 'https://ui-avatars.com/api/?name='.urlencode($user['HoTen']).'&background=6366f1&color=fff&size=200';
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
  --navy:    #050d1a; --navy2:   #071223; --panel:   #0d1f38; --panel2:  #0f2444;
  --cyan:    #00e5ff; --cyan2:   #00b8d4; --purple:  #7c3aed; --purple2: #a855f7;
  --green:   #22c55e; --tx:      #e2eaf5; --muted:   #7a92b0; --border:  rgba(0,229,255,0.12);
  --glow-cyan:   0 0 20px rgba(0,229,255,0.4);
  --glow-purple: 0 0 20px rgba(168,85,247,0.4); --r: 14px;
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

.stock-table { width:100%; border-collapse: separate; border-spacing: 0 8px; font-size: 13px; }
.stock-table tr { background: rgba(13, 31, 56, 0.4); transition: 0.3s; }
.stock-table tr:hover { background: rgba(0, 229, 255, 0.05); }
.stock-table td { padding: 12px 10px; border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); }
.stock-table td:first-child { border-left: 1px solid var(--border); border-radius: 10px 0 0 10px; padding-left: 15px; }
.stock-table td:last-child { border-right: 1px solid var(--border); border-radius: 0 10px 10px 0; }

.prod-img-wrapper { position: relative; width: 45px; height: 45px; cursor: pointer; overflow: hidden; border-radius: 6px; border: 1.5px solid var(--border); }
.prod-img-wrapper img { width: 100%; height: 100%; object-fit: cover; transition: 0.3s; }
.prod-img-wrapper:hover img { transform: scale(1.1); filter: brightness(0.7); }
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
        <a href="ChinhSuaProfile.php" class="ni act">👤 Hồ sơ cá nhân</a>
        <?php if ($user['VaiTro'] == 0): ?>
            <a href="DonHang.php" class="ni">📦 Đơn hàng của tôi</a>
            <a href="YeuThich.php" class="ni">❤️ Sản phẩm yêu thích</a>
            <a href="diachigiaohang.php" class="ni">🏠 Địa chỉ giao hàng</a>
        <?php endif; ?> 
        <?php if ($user['VaiTro'] == 1): ?>
        <a href="QuanLyMaGiamGia.php" class="ni">🎫 Quản lý mã giảm giá</a>
        <a href="QuanLyDonHang.php" class="ni">📦 Quản lý đơn hàng</a>
        <a href="QuanLyNguoiDung.php" class="ni">&#x1F6E1; Quản lý người dùng</a>
        <a href="QuanLyTinNhan.php" class="ni">&#x1F4AC; Quản lý tin nhắn</a>
        <?php endif; ?>
        <a href="TrangChu.php" class="ni" style="color:#ef4444">🚪 Đăng xuất</a>
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
              <button class="tb" onclick="sw('update_stock',this)">⚙️ Cập Nhật Kho</button>
              <button class="tb" onclick="sw('sua_sp',this)">✏️ Sửa / Xóa SP</button>
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
                <a href="diachigiaohang.php" class="btn bg2" style="text-decoration: none; border-color: var(--cyan); color: var(--cyan);">📍 QUẢN LÝ SỔ ĐỊA CHỈ GIAO HÀNG</a>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <?php if ($user['VaiTro'] == 0): ?>
      <div id="te" class="tp">
        <div class="st">Chỉnh sửa thông tin</div>
        <form method="post">
          <input type="hidden" name="action" value="update_info">
          <div class="fg">
            <div class="fi"><label>Họ và tên <span style="color:#ef4444">*</span></label><input type="text" name="HoTen" value="<?= htmlspecialchars($user['HoTen']) ?>" required></div>
            <div class="fi"><label>Tên đăng nhập</label><input type="text" value="<?= htmlspecialchars($user['TenDangNhap']) ?>" disabled></div>
            <div class="fi"><label>Email <span style="color:#ef4444">*</span></label><input type="email" name="Email" value="<?= htmlspecialchars($user['Email'] ?? '') ?>" required></div>
            <div class="fi"><label>Số điện thoại</label><input type="tel" name="SoDienThoai" value="<?= htmlspecialchars($user['SoDienThoai'] ?? '') ?>"></div>
            <div class="fi full"><label>Địa chỉ</label><textarea name="DiaChi"><?= htmlspecialchars($user['DiaChi'] ?? '') ?></textarea></div>
          </div>
          <div class="fa"><button type="submit" class="btn bp">&#x1F4BE; Lưu thay đổi</button></div>
        </form>
      </div>
      <?php endif; ?>

      <?php if ($user['VaiTro'] == 1): ?>
      <div id="ts" class="tp">
        <div class="st">Thêm sản phẩm bán hàng</div>
        <form method="post" enctype="multipart/form-data">
          <input type="hidden" name="action" value="add_product">
          <div class="fg">
            <div class="fi full"><label>Tên sản phẩm <span style="color:#ef4444">*</span></label><input type="text" name="TenSP" required></div>
            
            <div class="fi">
              <label>Danh mục</label>
              <select name="MaDM" onchange="toggleSpecs(this.value, '')">
                <option value="1">💻 Laptop</option>
                <option value="2">📱 Điện thoại</option>
                <option value="3">🖥️ PC Gaming</option>
                <option value="4">🎧 Phụ Kiện</option>
                <option value="5">🖱️ Gaming Gear</option>
              </select>
            </div>
            
            <div class="fi"><label>Giá bán (VNĐ) <span style="color:#ef4444">*</span></label><input type="number" name="Gia" required></div>
            <div class="fi"><label>Số lượng trong kho <span style="color:#ef4444">*</span></label><input type="number" name="SoLuongTon" value="10" required></div>
            <div class="fi"><label>Ảnh sản phẩm</label><input type="file" name="HinhAnh" accept="image/*" style="padding: 7px;"></div>
            
            <div class="fi full"><div class="st" style="font-size:12px; margin: 15px 0 0 0;">Cấu hình chi tiết</div></div>

            <div id="grp-dientu" style="display:contents;">
              <div class="fi"><label>CPU (Chip xử lý)</label><input type="text" name="CPU"></div>
              <div class="fi"><label>RAM</label><input type="text" name="RAM"></div>
              <div class="fi"><label>Ổ Cứng (ROM)</label><input type="text" name="O_Cung"></div>
              <div class="fi"><label>Màn hình</label><input type="text" name="ManHinh"></div>
            </div>

            <div id="grp-laptop" style="display:contents;">
              <div class="fi"><label>VGA (Card đồ họa)</label><input type="text" name="VGA"></div>
            </div>

            <div id="grp-phone" style="display:none;">
              <div class="fi"><label>Camera</label><input type="text" name="Camera"></div>
              <div class="fi"><label>Dung lượng Pin</label><input type="text" name="Pin"></div>
            </div>

            <div id="grp-gear" style="display:none;">
              <div class="fi"><label>Chuẩn Kết Nối</label><input type="text" name="KetNoi" placeholder="VD: Bluetooth 5.0, Cáp USB..."></div>
              <div class="fi"><label>Hệ điều hành tương thích</label><input type="text" name="TuongThich" placeholder="VD: Windows, macOS, iOS..."></div>
            </div>

            <div class="fi full"><label>Thời gian bảo hành</label><input type="text" name="BaoHanh" placeholder="VD: 24 tháng chính hãng"></div>
            <div class="fi full"><label>Mô tả sản phẩm</label><textarea name="MoTa"></textarea></div>
          </div>
          <div class="fa"><button type="submit" class="btn bp">&#x1F4E6; Đăng bán sản phẩm</button></div>
        </form>
      </div>
      <?php endif; ?>
      
      <?php if ($user['VaiTro'] == 1): ?>
      <div id="tuk" class="tp">
        <div class="st">Quản lý tồn kho & Hình ảnh</div>
        <div style="overflow-x: auto;">
          <table class="stock-table">
            <thead>
              <tr style="color: var(--cyan); text-transform: uppercase;">
                <th style="text-align: left; padding: 10px;">ID</th>
                <th style="text-align: left; padding: 10px;">Ảnh</th>
                <th style="text-align: left; padding: 10px;">Sản phẩm</th>
                <th style="text-align: left; padding: 10px;">Số lượng</th>
                <th style="text-align: right; padding: 10px;">Lưu</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $q_list = sqlsrv_query($conn, "SELECT MaSP, TenSP, SoLuongTon, HinhAnh FROM SanPham ORDER BY MaSP DESC");
              while($row = sqlsrv_fetch_array($q_list, SQLSRV_FETCH_ASSOC)):
              ?>
              <tr>
                <td style="color: var(--muted);">#<?= $row['MaSP'] ?></td>
                <td>
                  <div class="prod-img-wrapper" onclick="document.getElementById('file-<?= $row['MaSP'] ?>').click()">
                    <input type="file" id="file-<?= $row['MaSP'] ?>" onchange="updateProductImage(<?= $row['MaSP'] ?>)" style="display:none;" accept="image/*">
                    <img src="<?= $row['HinhAnh'] ?>" id="img-<?= $row['MaSP'] ?>" onerror="this.src='https://via.placeholder.com/50x50?text=Img'">
                  </div>
                </td>
                <td style="font-weight: 600;"><?= htmlspecialchars($row['TenSP']) ?></td>
                <td><input type="number" id="stock-<?= $row['MaSP'] ?>" value="<?= $row['SoLuongTon'] ?>" style="width:70px; background:var(--panel2); border:1.5px solid var(--border); color:var(--cyan); padding:5px; border-radius:4px;"></td>
                <td style="text-align: right;"><button onclick="saveStock(<?= $row['MaSP'] ?>)" class="btn bp" style="padding: 6px 12px;">LƯU</button></td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($user['VaiTro'] == 1): ?>
      <div id="tsp" class="tp">
        <div class="st">Chỉnh sửa / Xóa sản phẩm</div>
        <div style="overflow-x:auto">
          <table class="stock-table">
            <thead>
              <tr style="color:var(--cyan);text-transform:uppercase;">
                <th style="padding:10px;text-align:left">ID</th>
                <th style="padding:10px;text-align:left">Tên SP</th>
                <th style="padding:10px;text-align:left">Giá</th>
                <th style="padding:10px;text-align:left">Tồn</th>
                <th style="padding:10px;text-align:right">Thao tác</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $q2 = sqlsrv_query($conn, "SELECT MaSP,TenSP,Gia,SoLuongTon FROM SanPham ORDER BY MaSP DESC");
              while($r2 = sqlsrv_fetch_array($q2, SQLSRV_FETCH_ASSOC)):
              ?>
              <tr>
                <td style="color:var(--muted);padding:10px">#<?= $r2['MaSP'] ?></td>
                <td style="font-weight:600;padding:10px"><?= htmlspecialchars($r2['TenSP']) ?></td>
                <td style="color:var(--cyan);padding:10px"><?= number_format($r2['Gia'],0,',','.') ?>đ</td>
                <td style="padding:10px"><?= $r2['SoLuongTon'] ?></td>
                <td style="text-align:right;padding:10px">
                  <button onclick="openEdit(<?= $r2['MaSP'] ?>)" class="btn" style="padding:6px 14px;background:rgba(99,102,241,.15);color:#818cf8;border:1px solid rgba(99,102,241,.3)">✏️ Sửa</button>
                  <form method="post" style="display:inline" onsubmit="return confirm('Xóa sản phẩm này?')">
                    <input type="hidden" name="action" value="delete_product">
                    <input type="hidden" name="MaSP" value="<?= $r2['MaSP'] ?>">
                    <button type="submit" class="btn" style="padding:6px 14px;background:rgba(239,68,68,.1);color:#f87171;border:1px solid rgba(239,68,68,.25)">🗑 Xóa</button>
                  </form>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endif; ?>

<div id="editModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:9999;align-items:center;justify-content:center;backdrop-filter:blur(6px)">
  <div style="background:#0d1f38;border:1px solid rgba(0,229,255,.2);border-radius:14px;padding:28px;width:90%;max-width:620px;max-height:85vh;overflow-y:auto">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
      <div style="font-family:'Orbitron',monospace;font-size:14px;color:var(--cyan)">✏️ CHỈNH SỬA SẢN PHẨM</div>
      <button onclick="closeEdit()" style="background:none;border:none;color:var(--muted);font-size:22px;cursor:pointer">✕</button>
    </div>
    <form method="post">
      <input type="hidden" name="action" value="edit_product">
      <input type="hidden" name="MaSP" id="eMaSP">
      <div class="fg">
        <div class="fi full"><label>Tên sản phẩm <span style="color:#ef4444">*</span></label><input type="text" name="TenSP" id="eTenSP" required></div>
        <div class="fi">
          <label>Danh mục</label>
          <select name="MaDM" id="eMaDM" onchange="toggleSpecs(this.value, 'e-')">
            <option value="1">💻 Laptop</option>
            <option value="2">📱 Điện thoại</option>
            <option value="3">🖥️ PC Gaming</option>
            <option value="4">🎧 Phụ Kiện</option>
            <option value="5">🖱️ Gaming Gear</option>
          </select>
        </div>
        <div class="fi"><label>Giá bán</label><input type="number" name="Gia" id="eGia" required></div>
        <div class="fi"><label>Tồn kho</label><input type="number" name="SoLuongTon" id="eSoLuong"></div>

        <div id="e-grp-dientu" style="display:contents;">
          <div class="fi"><label>CPU</label><input type="text" name="CPU" id="eCPU"></div>
          <div class="fi"><label>RAM</label><input type="text" name="RAM" id="eRAM"></div>
          <div class="fi"><label>Ổ cứng</label><input type="text" name="O_Cung" id="eOCung"></div>
          <div class="fi"><label>Màn hình</label><input type="text" name="ManHinh" id="eManHinh"></div>
        </div>

        <div id="e-grp-laptop" style="display:contents;">
          <div class="fi"><label>VGA</label><input type="text" name="VGA" id="eVGA"></div>
        </div>

        <div id="e-grp-phone" style="display:none;">
          <div class="fi"><label>Camera</label><input type="text" name="Camera" id="eCamera"></div>
          <div class="fi"><label>Pin</label><input type="text" name="Pin" id="ePin"></div>
        </div>

        <div id="e-grp-gear" style="display:none;">
          <div class="fi"><label>Kết nối</label><input type="text" name="KetNoi" id="eKetNoi"></div>
          <div class="fi"><label>Tương thích</label><input type="text" name="TuongThich" id="eTuongThich"></div>
        </div>

        <div class="fi full"><label>Bảo hành</label><input type="text" name="BaoHanh" id="eBaoHanh"></div>
        <div class="fi full"><label>Mô tả</label><textarea name="MoTa" id="eMoTa"></textarea></div>
      </div>
      <div class="fa" style="margin-top:16px">
        <button type="button" onclick="closeEdit()" class="btn bg2">Hủy</button>
        <button type="submit" class="btn bp">💾 Lưu thay đổi</button>
      </div>
    </form>
  </div>
</div>

    </div>
  </main>
</div>

<script>
<?php if ($user['VaiTro'] == 1): ?>
    const panels = { view: 'tv', add_sp: 'ts', update_stock: 'tuk', sua_sp: 'tsp' };
<?php else: ?>
    const panels = { view: 'tv', edit: 'te' };
<?php endif; ?>

function sw(name, btn) {
    Object.values(panels).forEach(id => { const el = document.getElementById(id); if(el) el.classList.remove('act'); });
    document.querySelectorAll('.tb').forEach(b => b.classList.remove('act'));
    const target = document.getElementById(panels[name]);
    if(target) { target.classList.add('act'); btn.classList.add('act'); }
}

function swn(name){ const btn = document.querySelector('.tb[onclick*="'+name+'"]'); if(btn) sw(name, btn); }

// Hàm ẩn/hiện cấu hình theo Danh Mục cực xịn
function toggleSpecs(maDM, prefix) {
    const grpDienTu = document.getElementById(prefix + 'grp-dientu');
    const grpLaptop = document.getElementById(prefix + 'grp-laptop');
    const grpPhone = document.getElementById(prefix + 'grp-phone');
    const grpGear = document.getElementById(prefix + 'grp-gear');

    // Ẩn tất cả trước khi hiển thị
    if(grpDienTu) grpDienTu.style.display = 'none';
    if(grpLaptop) grpLaptop.style.display = 'none';
    if(grpPhone) grpPhone.style.display = 'none';
    if(grpGear) grpGear.style.display = 'none';

    maDM = parseInt(maDM);
    if (maDM === 1 || maDM === 3) { // Laptop & PC
        if(grpDienTu) grpDienTu.style.display = 'contents';
        if(grpLaptop) grpLaptop.style.display = 'contents';
    } 
    else if (maDM === 2) { // Điện thoại
        if(grpDienTu) grpDienTu.style.display = 'contents';
        if(grpPhone) grpPhone.style.display = 'contents';
    } 
    else if (maDM === 4 || maDM === 5) { // Phụ Kiện & Gear
        if(grpGear) grpGear.style.display = 'contents';
    }
}

function updateProductImage(id) {
    const fileInput = document.getElementById('file-' + id);
    if (!fileInput.files[0]) return;
    const fd = new FormData();
    fd.append('action', 'update_product_img_ajax'); fd.append('id', id); fd.append('HinhAnh', fileInput.files[0]);
    document.getElementById('img-' + id).style.opacity = '0.5';
    fetch('ChinhSuaProfile.php', { method: 'POST', body: fd }).then(r => r.text()).then(res => {
        document.getElementById('img-' + id).style.opacity = '1';
        if (res.trim().startsWith('uploads/')) { document.getElementById('img-' + id).src = res.trim() + '?t=' + new Date().getTime(); } 
        else { alert("❌ Lỗi: " + res); }
    });
}

function saveStock(id) {
    const qty = document.getElementById('stock-' + id).value;
    const fd = new FormData();
    fd.append('action', 'quick_update_stock'); fd.append('id', id); fd.append('qty', qty);
    fetch('ChinhSuaProfile.php', { method: 'POST', body: fd }).then(r => r.text()).then(txt => {
        if(txt.trim() === "OK") alert("✅ Cập nhật kho thành công!"); else alert("❌ Lỗi: " + txt);
    });
}

function prevAv(input){
    if(!input.files[0]) return;
    const r = new FileReader();
    r.onload = e => { document.getElementById('pi').src = e.target.result; document.getElementById('pw').style.display = 'flex'; document.getElementById('mai').src = e.target.result; };
    r.readAsDataURL(input.files[0]);
}

function cancelPrev(){ document.getElementById('avi').value = ''; document.getElementById('pw').style.display = 'none'; document.getElementById('mai').src = <?= json_encode($avSrc) ?>; }

<?php if($success==='info'): ?> window.onload = () => swn('view'); <?php endif; ?>
<?php if($success==='product'): ?> window.onload = () => swn('add_sp'); <?php endif; ?>

const spData = <?php
    $allSP = sqlsrv_query($conn, "SELECT * FROM SanPham");
    $arr = [];
    while($r = sqlsrv_fetch_array($allSP, SQLSRV_FETCH_ASSOC)) $arr[$r['MaSP']] = $r;
    echo json_encode($arr);
?>;

function openEdit(id) {
    const sp = spData[id];
    if (!sp) return;
    document.getElementById('eMaSP').value    = sp.MaSP;
    document.getElementById('eTenSP').value   = sp.TenSP;
    
    // Đổi Select và Cập nhật Form tương ứng
    document.getElementById('eMaDM').value    = sp.MaDM;
    toggleSpecs(sp.MaDM, 'e-'); 

    document.getElementById('eGia').value     = sp.Gia;
    document.getElementById('eSoLuong').value = sp.SoLuongTon;
    document.getElementById('eCPU').value     = sp.CPU     || '';
    document.getElementById('eRAM').value     = sp.RAM     || '';
    document.getElementById('eOCung').value   = sp.O_Cung  || '';
    document.getElementById('eManHinh').value = sp.ManHinh || '';
    document.getElementById('eVGA').value     = sp.VGA     || '';
    document.getElementById('eCamera').value  = sp.Camera  || '';
    document.getElementById('ePin').value     = sp.Pin     || '';
    document.getElementById('eKetNoi').value  = sp.KetNoi  || '';
    document.getElementById('eTuongThich').value = sp.TuongThich || '';
    document.getElementById('eBaoHanh').value = sp.BaoHanh || '';
    document.getElementById('eMoTa').value    = sp.MoTa    || '';
    document.getElementById('editModal').style.display = 'flex';
}
function closeEdit() { document.getElementById('editModal').style.display = 'none'; }
</script>
</body>
</html>
<?php sqlsrv_close($conn); ?>