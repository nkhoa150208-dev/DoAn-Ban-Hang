<?php
session_start();

$serverName     = "localhost\\SQLEXPRESS";
$connectionInfo = ["Database"=>"QLBanHang","TrustServerCertificate"=>true,"CharacterSet"=>"UTF-8"];
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) die(print_r(sqlsrv_errors(), true));

if (!isset($_SESSION['MaND'])) { header('Location: DangNhap.php'); exit; }
$user_id = (int)$_SESSION['MaND'];

$success = ""; $error = "";

// XỬ LÝ FORM THÊM ĐỊA CHỈ & XÓA & ĐẶT MẶC ĐỊNH
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. Thêm địa chỉ mới
    if ($action === 'add_address') {
        $hoten = trim($_POST['HoTen'] ?? '');
        $sdt = trim($_POST['SoDienThoai'] ?? '');
        $thanhpho = trim($_POST['ThanhPho'] ?? '');
        $diachi = trim($_POST['DiaChi'] ?? '');
        $macdinh = isset($_POST['MacDinh']) ? 1 : 0;

        if (empty($hoten) || empty($sdt) || empty($thanhpho) || empty($diachi)) {
            $error = "Vui lòng điền đầy đủ thông tin.";
        } else {
            // Nếu chọn làm mặc định, reset các địa chỉ cũ về 0
            if ($macdinh == 1) {
                sqlsrv_query($conn, "UPDATE SoDiaChi SET MacDinh = 0 WHERE MaND = ?", [$user_id]);
            } else {
                // Nếu chưa có địa chỉ nào, tự động ép cái đầu tiên làm mặc định
                $chk = sqlsrv_query($conn, "SELECT COUNT(*) as Cnt FROM SoDiaChi WHERE MaND = ?", [$user_id]);
                $rowChk = sqlsrv_fetch_array($chk, SQLSRV_FETCH_ASSOC);
                if ($rowChk['Cnt'] == 0) $macdinh = 1;
            }

            $sql_insert = "INSERT INTO SoDiaChi (MaND, HoTenNguoiNhan, SoDienThoai, ThanhPho, DiaChiCuThe, MacDinh) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = sqlsrv_query($conn, $sql_insert, [$user_id, $hoten, $sdt, $thanhpho, $diachi, $macdinh]);
            if ($stmt) $success = "Đã thêm địa chỉ giao hàng thành công!";
            else $error = "Lỗi khi thêm địa chỉ vào CSDL.";
        }
    }

    // 2. Xóa địa chỉ
    if ($action === 'delete_address') {
        $madc = (int)$_POST['MaDC'];
        sqlsrv_query($conn, "DELETE FROM SoDiaChi WHERE MaDC = ? AND MaND = ?", [$madc, $user_id]);
        $success = "Đã xóa địa chỉ thành công!";
    }

    // 3. Đặt làm mặc định
    if ($action === 'set_default') {
        $madc = (int)$_POST['MaDC'];
        sqlsrv_query($conn, "UPDATE SoDiaChi SET MacDinh = 0 WHERE MaND = ?", [$user_id]);
        sqlsrv_query($conn, "UPDATE SoDiaChi SET MacDinh = 1 WHERE MaDC = ? AND MaND = ?", [$madc, $user_id]);
        $success = "Đã cập nhật địa chỉ mặc định!";
    }
}

// Lấy thông tin user (để hiển thị sidebar)
$res  = sqlsrv_query($conn,"SELECT * FROM dbo.NguoiDung WHERE MaND=?",[$user_id]);
$user = sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC);
$avSrc = (!empty($user['Avatar']) && file_exists($user['Avatar'])) ? $user['Avatar'] : 'https://ui-avatars.com/api/?name='.urlencode($user['HoTen']).'&background=6366f1&color=fff&size=200';

// Lấy danh sách địa chỉ của user
$sql_diachi = "SELECT * FROM SoDiaChi WHERE MaND = ? ORDER BY MacDinh DESC, MaDC DESC";
$stmt_diachi = sqlsrv_query($conn, $sql_diachi, [$user_id]);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@300;400;600;700;900&family=Orbitron:wght@400;700;900&display=swap" rel="stylesheet">
<title>Sổ Địa Chỉ - TechVN</title>
<style>
/* GIỮ NGUYÊN CSS CỦA BẠN CHO ĐỒNG BỘ */
:root {
  --navy:    #050d1a; --navy2:   #071223; --panel:   #0d1f38; --panel2:  #0f2444;
  --cyan:    #00e5ff; --cyan2:   #00b8d4; --purple:  #7c3aed; --purple2: #a855f7;
  --green:   #22c55e; --tx:      #e2eaf5; --muted:   #7a92b0; --border:  rgba(0,229,255,0.12);
  --glow-cyan:   0 0 20px rgba(0,229,255,0.4); --glow-purple: 0 0 20px rgba(168,85,247,0.4);
  --r: 14px;
}
*,*::before,*::after { box-sizing:border-box; margin:0; padding:0; }
body { font-family: 'Exo 2', sans-serif; background: var(--navy); color: var(--tx); min-height: 100vh; padding: 24px 16px 60px; }

/* TOPBAR & SIDEBAR CSS TƯƠNG TỰ PROFILE */
.topbar { max-width: 980px; margin: 0 auto 28px; display: flex; align-items: center; gap: 12px; background: rgba(5,13,26,0.92); border: 1px solid var(--border); border-radius: var(--r); padding: 12px 20px; }
.logo { font-family: 'Orbitron', monospace; font-size: 18px; font-weight: 900; color: var(--cyan); }
.lay { max-width: 980px; margin: 0 auto; display: grid; grid-template-columns: 260px 1fr; gap: 20px; }
.card { background: var(--panel); border: 1px solid var(--border); border-radius: var(--r); padding: 24px; box-shadow: 0 8px 32px rgba(0,0,0,.5); }
.sb { display: flex; flex-direction: column; gap: 20px; }
.snav { display: flex; flex-direction: column; gap: 4px; }
.ni { padding: 10px 12px; border-radius: 10px; font-size: 14px; color: var(--muted); text-decoration: none; border: 1px solid transparent; }
.ni:hover { background: rgba(0,229,255,0.06); color: var(--cyan); }
.ni.act { background: rgba(0,229,255,0.1); color: var(--cyan); border-color: rgba(0,229,255,0.3); font-weight: 600; box-shadow: 0 0 15px rgba(0,229,255,0.08); }

/* ADDRESS LIST STYLES */
.st { font-family: 'Orbitron', monospace; font-size: 14px; font-weight: 700; margin-bottom: 20px; color: var(--cyan); display: flex; align-items: center; gap: 8px; }
.st::after { content: ''; flex: 1; height: 1px; background: linear-gradient(90deg, rgba(0,229,255,0.4), transparent); }
.addr-card { background: var(--panel2); border: 1px solid var(--border); border-radius: 10px; padding: 16px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: flex-start; }
.addr-info h4 { margin-bottom: 5px; color: var(--tx); display: flex; align-items: center; gap: 10px; }
.addr-info p { font-size: 13px; color: var(--muted); margin-bottom: 4px; }
.badge-default { background: rgba(34,197,94,0.15); color: #4ade80; border: 1px solid rgba(34,197,94,0.3); padding: 2px 8px; border-radius: 12px; font-size: 10px; font-weight: bold; text-transform: uppercase; }
.addr-actions { display: flex; gap: 8px; flex-direction: column; align-items: flex-end; }

/* FORM & BUTTONS */
.btn { padding: 8px 16px; border-radius: 8px; font-size: 12px; font-weight: 700; text-transform: uppercase; border: none; cursor: pointer; font-family: 'Exo 2', sans-serif; transition: 0.2s; }
.bp { background: linear-gradient(135deg, var(--green), #16a34a); color: #fff; }
.bg2 { background: transparent; color: var(--muted); border: 1px solid var(--muted); }
.bg2:hover { color: #ef4444; border-color: #ef4444; }
.b-add { background: rgba(0,229,255,0.1); color: var(--cyan); border: 1px solid var(--cyan); width: 100%; padding: 12px; margin-bottom: 20px;}
.b-add:hover { background: var(--cyan); color: var(--navy); }

.fg { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 15px; }
.fi { display: flex; flex-direction: column; gap: 6px; }
.fi.full { grid-column: 1/-1; }
.fi label { font-size: 10px; color: var(--cyan); text-transform: uppercase; font-weight: 600; }
.fi input, .fi textarea { background: var(--panel2); border: 1.5px solid var(--border); border-radius: 8px; color: var(--tx); padding: 10px; outline: none; }
.fi input:focus, .fi textarea:focus { border-color: var(--cyan); }
.al { padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; }
.ok { background: rgba(34,197,94,.1); border: 1px solid rgba(34,197,94,.3); color: #4ade80; }
.er { background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.3); color: #f87171; }
#form-add { display: none; background: var(--panel2); padding: 20px; border-radius: 10px; border: 1px dashed var(--cyan); }
</style>
</head>
<body>

<div class="topbar">
  <div class="logo">&#x1F6CD; KhoaOngNghiem Tech</div>
  <a href="TrangChuDaDangNhap.php" style="margin-left:auto; color:var(--cyan); text-decoration:none; font-weight:bold;">&#x2190; Về Trang Chủ</a>
</div>

<div class="lay">
  <aside class="sb">
    <div class="card">
      <nav class="snav">
        <a href="ChinhSuaProfile.php" class="ni">👤 Hồ sơ cá nhân</a>
<?php if ($user['VaiTro'] == 0): ?>
            <a href="DonHang.php" class="ni">📦 Don hang cua toi</a>
            <a href="YeuThich.php" class="ni">❤️ San pham yeu thich</a>
            <a href="diachigiaohang.php" class="ni">🏠 Dia chi giao hang</a>
        <?php endif; ?>          <?php if ($user['VaiTro'] == 1): ?>
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
      <div class="st">Sổ Địa Chỉ Giao Hàng</div>
      
      <?php if ($success): ?><div class="al ok">&#x2705; <?= htmlspecialchars($success) ?></div><?php endif; ?>
      <?php if ($error): ?><div class="al er">&#x274C; <?= htmlspecialchars($error) ?></div><?php endif; ?>

      <button class="btn b-add" onclick="document.getElementById('form-add').style.display='block'">+ Thêm Địa Chỉ Mới</button>

      <div id="form-add">
        <h4 style="color:var(--cyan); margin-bottom:10px;">Thêm địa chỉ giao hàng</h4>
        <form method="post">
          <input type="hidden" name="action" value="add_address">
          <div class="fg">
            <div class="fi">
              <label>Họ tên người nhận *</label>
              <input type="text" name="HoTen" required>
            </div>
            <div class="fi">
              <label>Số điện thoại *</label>
              <input type="tel" name="SoDienThoai" required>
            </div>
            <div class="fi full">
              <label>Thành phố / Tỉnh *</label>
              <input type="text" name="ThanhPho" placeholder="VD: TP. Hồ Chí Minh" required>
            </div>
            <div class="fi full">
              <label>Địa chỉ cụ thể (Số nhà, đường, phường/xã) *</label>
              <textarea name="DiaChi" required></textarea>
            </div>
            <div class="fi full" style="flex-direction:row; align-items:center;">
              <input type="checkbox" name="MacDinh" id="md" value="1" style="width:auto;">
              <label for="md" style="margin-top:2px; cursor:pointer; color:var(--tx)">Đặt làm địa chỉ mặc định</label>
            </div>
          </div>
          <div style="display:flex; gap:10px; margin-top:15px; justify-content:flex-end;">
            <button type="button" class="btn bg2" onclick="document.getElementById('form-add').style.display='none'">Hủy</button>
            <button type="submit" class="btn bp">Lưu Địa Chỉ</button>
          </div>
        </form>
      </div>

      <div style="margin-top: 20px;">
        <?php 
        $hasAddress = false;
        while ($dc = sqlsrv_fetch_array($stmt_diachi, SQLSRV_FETCH_ASSOC)): 
            $hasAddress = true;
        ?>
        <div class="addr-card">
          <div class="addr-info">
            <h4>
              <?= htmlspecialchars($dc['HoTenNguoiNhan']) ?> 
              <span style="color:var(--muted); font-weight:normal;">| <?= htmlspecialchars($dc['SoDienThoai']) ?></span>
              <?php if($dc['MacDinh'] == 1): ?><span class="badge-default">Mặc định</span><?php endif; ?>
            </h4>
            <p><?= htmlspecialchars($dc['DiaChiCuThe']) ?></p>
            <p><?= htmlspecialchars($dc['ThanhPho']) ?></p>
          </div>
          <div class="addr-actions">
            <?php if($dc['MacDinh'] == 0): ?>
            <form method="post" style="margin:0;">
                <input type="hidden" name="action" value="set_default">
                <input type="hidden" name="MaDC" value="<?= $dc['MaDC'] ?>">
                <button type="submit" class="btn" style="background:transparent; color:var(--cyan); border:1px solid var(--cyan);">Thiết lập mặc định</button>
            </form>
            <?php endif; ?>
            <form method="post" style="margin:0;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa địa chỉ này?');">
                <input type="hidden" name="action" value="delete_address">
                <input type="hidden" name="MaDC" value="<?= $dc['MaDC'] ?>">
                <button type="submit" class="btn bg2">Xóa</button>
            </form>
          </div>
        </div>
        <?php endwhile; ?>

        <?php if(!$hasAddress): ?>
            <p style="text-align:center; color:var(--muted); padding:30px;">Bạn chưa có địa chỉ giao hàng nào.</p>
        <?php endif; ?>
      </div>

    </div>
  </main>
</div>

</body>
</html>
<?php sqlsrv_close($conn); ?>