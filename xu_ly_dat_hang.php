<?php
session_start();

// KET NOI DATABASE
$serverName = "localhost\\SQLEXPRESS";
$database   = "QLBanHang";
$connectionInfo = [
    "Database" => $database,
    "TrustServerCertificate" => true,
    "CharacterSet" => "UTF-8"
];
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) die("Loi ket noi CSDL: " . print_r(sqlsrv_errors(), true));

if (!isset($_SESSION['MaND'])) { header('Location: DangNhap.php'); exit; }
if (!isset($_SESSION['giohang']) || count($_SESSION['giohang']) == 0) {
    header('Location: TrangChuDaDangNhap.php'); exit;
}

$user_id   = (int)$_SESSION['MaND'];
$error     = "";
$mgMsg     = "";
$mgError   = "";
$giamGia   = 0;
$maGGInfo  = null;

// Tinh tong tien gio hang
$tongTienGoc = 0;
foreach ($_SESSION['giohang'] as $sp) {
    $tongTienGoc += $sp['Gia'] * $sp['SoLuong'];
}

// ============================================================
// KIEM TRA MA GIAM GIA (AJAX hoac POST)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'check_code') {
    $code = strtoupper(trim($_POST['code'] ?? ''));
    $result = ['success' => false, 'msg' => '', 'giam' => 0, 'tongSau' => $tongTienGoc];

    if (empty($code)) {
        $result['msg'] = 'Vui long nhap ma giam gia!';
    } else {
        $now = date('Y-m-d H:i:s');
        $stmt = sqlsrv_query($conn,
            "SELECT * FROM MaGiamGia WHERE Code=? AND TrangThai=1 AND (NgayHetHan IS NULL OR NgayHetHan > ?) AND DaDung < SoLanDung",
            [$code, $now]);
        $mg = $stmt ? sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC) : null;

        if (!$mg) {
            $result['msg'] = 'Ma giam gia khong hop le, het han hoac da het luot dung!';
        } elseif ($tongTienGoc < $mg['DonToiThieu']) {
            $result['msg'] = 'Don hang toi thieu ' . number_format($mg['DonToiThieu'],0,',','.') . 'D de dung ma nay!';
        } else {
            if ($mg['LoaiGiam'] == 0) {
                // Giam theo %
                $giam = $tongTienGoc * $mg['GiaTri'] / 100;
                if ($mg['GiamToiDa'] > 0 && $giam > $mg['GiamToiDa']) $giam = $mg['GiamToiDa'];
            } else {
                // Giam so tien co dinh
                $giam = $mg['GiaTri'];
            }
            $giam = min($giam, $tongTienGoc);
            $tongSau = $tongTienGoc - $giam;

            $result['success'] = true;
            $result['msg']     = ($mg['LoaiGiam']==0)
                ? 'Ap dung thanh cong! Giam ' . $mg['GiaTri'] . '%' . ($mg['GiamToiDa']>0 ? ' (toi da '.number_format($mg['GiamToiDa'],0,',','.').'D)' : '')
                : 'Ap dung thanh cong! Giam ' . number_format($giam,0,',','.') . 'D';
            $result['giam']    = $giam;
            $result['tongSau'] = $tongSau;
            $result['maMGG']   = $mg['MaMGG'];

            // Luu vao session
            $_SESSION['maGiamGia'] = [
                'MaMGG'  => $mg['MaMGG'],
                'Code'   => $code,
                'GiaTri' => $giam
            ];
        }
    }
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}

// Xoa ma giam gia
if (isset($_GET['remove_code'])) {
    unset($_SESSION['maGiamGia']);
    header('Location: xu_ly_dat_hang.php'); exit;
}

// Lay ma giam gia tu session (neu co)
if (isset($_SESSION['maGiamGia'])) {
    $giamGia  = (float)$_SESSION['maGiamGia']['GiaTri'];
    $maGGInfo = $_SESSION['maGiamGia'];
}
$tongTienSau = $tongTienGoc - $giamGia;

// ============================================================
// XU LY DAT HANG
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'place_order') {
    $maDC      = $_POST['MaDC']      ?? '';
    $thanhToan = $_POST['ThanhToan'] ?? 'COD';
    $ghiChu    = $_POST['GhiChu']    ?? '';

    if (empty($maDC)) {
        $error = "Vui long chon dia chi giao hang!";
    } else {
        $stmt_dc = sqlsrv_query($conn, "SELECT * FROM SoDiaChi WHERE MaDC=? AND MaND=?", [$maDC, $user_id]);
        $dc      = sqlsrv_fetch_array($stmt_dc, SQLSRV_FETCH_ASSOC);
        $stmt_u  = sqlsrv_query($conn, "SELECT Email FROM NguoiDung WHERE MaND=?", [$user_id]);
        $u       = sqlsrv_fetch_array($stmt_u, SQLSRV_FETCH_ASSOC);
        $email   = $u['Email'] ?? '';

        if ($dc) {
            $sql_dh = "INSERT INTO DonHang (MaND, TongTien, HoTen, SoDienThoai, Email, DiaChi, ThanhPho, ThanhToan, GhiChu)
                       OUTPUT INSERTED.MaDH
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_dh = sqlsrv_query($conn, $sql_dh, [
                $user_id, $tongTienSau,
                $dc['HoTenNguoiNhan'], $dc['SoDienThoai'], $email,
                $dc['DiaChiCuThe'], $dc['ThanhPho'], $thanhToan, $ghiChu
            ]);

            if ($stmt_dh) {
                sqlsrv_fetch($stmt_dh);
                $maDH_Moi = sqlsrv_get_field($stmt_dh, 0);

                foreach ($_SESSION['giohang'] as $maSP => $sp) {
                    sqlsrv_query($conn,
                        "INSERT INTO ChiTietDonHang (MaDH, MaSP, SoLuong, DonGia) VALUES (?, ?, ?, ?)",
                        [$maDH_Moi, $maSP, $sp['SoLuong'], $sp['Gia']]);
                }

                // Tang luot da dung ma giam gia
                if (isset($_SESSION['maGiamGia'])) {
                    sqlsrv_query($conn,
                        "UPDATE MaGiamGia SET DaDung = DaDung + 1 WHERE MaMGG = ?",
                        [$_SESSION['maGiamGia']['MaMGG']]);
                    unset($_SESSION['maGiamGia']);
                }

                unset($_SESSION['giohang']);
                echo "<script>
                    alert('Dat hang thanh cong! Ma don cua ban la #".$maDH_Moi."');
                    window.location.href='DonHang.php';
                </script>";
                exit;
            } else {
                $error = "Co loi xay ra khi tao don hang. Vui long thu lai!";
            }
        } else {
            $error = "Dia chi giao hang khong hop le!";
        }
    }
}

// Lay danh sach dia chi
$stmt_ds_dc = sqlsrv_query($conn, "SELECT * FROM SoDiaChi WHERE MaND=? ORDER BY MacDinh DESC", [$user_id]);
$hasAddress = false;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Thanh Toan - QLBanHang</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Exo+2:wght@400;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Exo 2',sans-serif;background:#050d1a;color:#e2eaf5;min-height:100vh;padding:40px 20px}
.container{max-width:1000px;margin:0 auto;display:grid;grid-template-columns:1.5fr 1fr;gap:30px}
@media(max-width:800px){.container{grid-template-columns:1fr}}
.card{background:#0d1f38;border:1px solid rgba(0,229,255,0.2);border-radius:12px;padding:25px;margin-bottom:20px}
.card-title{font-family:'Orbitron',monospace;font-size:16px;color:#00e5ff;margin-bottom:20px;display:flex;align-items:center;gap:10px}
.card-title::after{content:'';flex:1;height:1px;background:linear-gradient(90deg,rgba(0,229,255,0.4),transparent)}
.addr-box{background:#0f2444;border:1.5px solid rgba(0,229,255,0.1);border-radius:8px;padding:15px;margin-bottom:12px;cursor:pointer;display:flex;gap:12px;transition:.2s}
.addr-box:hover{border-color:rgba(0,229,255,0.5)}
.addr-box input[type=radio]{margin-top:5px;accent-color:#00e5ff;transform:scale(1.2)}
.addr-info h4{margin:0 0 5px;color:#fff;font-size:15px}
.addr-info p{margin:0;font-size:13px;color:#7a92b0}
.badge-default{background:rgba(34,197,94,0.15);color:#4ade80;border:1px solid rgba(34,197,94,0.3);padding:2px 8px;border-radius:12px;font-size:10px;font-weight:700;text-transform:uppercase;margin-left:8px}
.sp-item{display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid rgba(0,229,255,0.08)}
.sp-name{font-size:14px;font-weight:600}
.sp-qty{font-size:12px;color:#7a92b0;margin-top:3px}
.sp-price{color:#a855f7;font-weight:700;font-family:'Orbitron',sans-serif;font-size:13px;white-space:nowrap}

/* MA GIAM GIA */
.code-wrap{display:flex;gap:8px;margin-bottom:12px}
.code-input{flex:1;background:#0f2444;border:1.5px solid rgba(0,229,255,0.2);border-radius:8px;color:#fff;padding:10px 14px;font-family:'Exo 2',sans-serif;font-size:14px;outline:none;transition:.2s;text-transform:uppercase}
.code-input:focus{border-color:#00e5ff;box-shadow:0 0 0 3px rgba(0,229,255,0.1)}
.btn-apply{padding:10px 18px;border-radius:8px;background:rgba(0,229,255,0.15);color:#00e5ff;border:1.5px solid rgba(0,229,255,0.3);font-family:'Exo 2',sans-serif;font-size:13px;font-weight:700;cursor:pointer;transition:.2s;white-space:nowrap}
.btn-apply:hover{background:rgba(0,229,255,0.25)}
.code-msg{font-size:13px;padding:10px 14px;border-radius:8px;margin-bottom:12px;display:none}
.code-ok{background:rgba(34,197,94,0.1);color:#4ade80;border:1px solid rgba(34,197,94,0.3)}
.code-er{background:rgba(239,68,68,0.1);color:#f87171;border:1px solid rgba(239,68,68,0.3)}
.code-applied{display:flex;align-items:center;justify-content:space-between;background:rgba(34,197,94,0.08);border:1px solid rgba(34,197,94,0.25);border-radius:8px;padding:10px 14px;margin-bottom:12px}
.code-applied span{color:#4ade80;font-size:13px;font-weight:600}
.btn-remove-code{background:none;border:none;color:#f87171;cursor:pointer;font-size:18px;line-height:1;padding:0 4px}

.price-row{display:flex;justify-content:space-between;font-size:14px;padding:8px 0;color:#7a92b0}
.price-row.discount{color:#4ade80}
.total-row{display:flex;justify-content:space-between;font-size:20px;font-weight:700;color:#22c55e;margin-top:12px;padding-top:12px;border-top:2px dashed rgba(34,197,94,0.3)}
textarea,select{width:100%;background:#0f2444;border:1px solid rgba(0,229,255,0.2);border-radius:8px;color:#fff;padding:12px;font-family:'Exo 2',sans-serif;outline:none;margin-bottom:20px}
textarea:focus,select:focus{border-color:#00e5ff}
.btn-submit{width:100%;background:linear-gradient(135deg,#22c55e,#16a34a);color:#fff;border:none;border-radius:10px;padding:15px;font-size:16px;font-weight:700;cursor:pointer;transition:.3s;text-transform:uppercase;letter-spacing:1px;margin-top:16px}
.btn-submit:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(34,197,94,0.4)}
.btn-submit:disabled{opacity:.5;cursor:not-allowed;transform:none}
.btn-link{color:#00e5ff;text-decoration:none;font-size:13px;font-weight:600}
.btn-link:hover{text-decoration:underline}
.error-msg{background:rgba(239,68,68,0.1);color:#ef4444;border:1px solid rgba(239,68,68,0.3);padding:15px;border-radius:8px;margin-bottom:20px;text-align:center;max-width:1000px;margin-left:auto;margin-right:auto}
</style>
</head>
<body>

<?php if ($error): ?><div class="error-msg">&#x26A0; <?= htmlspecialchars($error) ?></div><?php endif; ?>

<form method="POST">
<input type="hidden" name="action" value="place_order">
<div class="container">

  <!-- COT TRAI -->
  <div>
    <!-- DIA CHI -->
    <div class="card">
      <div class="card-title">&#x1F4CD; CHON DIA CHI GIAO HANG</div>
      <?php while ($dc = sqlsrv_fetch_array($stmt_ds_dc, SQLSRV_FETCH_ASSOC)): $hasAddress = true; ?>
        <label class="addr-box">
          <input type="radio" name="MaDC" value="<?= $dc['MaDC'] ?>" <?= $dc['MacDinh']==1?'checked':'' ?> required>
          <div class="addr-info">
            <h4><?= htmlspecialchars($dc['HoTenNguoiNhan']) ?> - <?= htmlspecialchars($dc['SoDienThoai']) ?>
              <?php if ($dc['MacDinh']==1): ?><span class="badge-default">Mac dinh</span><?php endif; ?>
            </h4>
            <p><?= htmlspecialchars($dc['DiaChiCuThe']) ?></p>
            <p><?= htmlspecialchars($dc['ThanhPho']) ?></p>
          </div>
        </label>
      <?php endwhile; ?>
      <?php if (!$hasAddress): ?>
        <div style="text-align:center;padding:20px;color:#7a92b0">
          Ban chua co dia chi nao!<br><br>
          <a href="diachigiaohang.php" class="btn-submit" style="display:inline-block;width:auto;text-decoration:none;padding:10px 20px;font-size:14px">+ Them dia chi moi</a>
        </div>
      <?php else: ?>
        <div style="text-align:right;margin-top:8px">
          <a href="diachigiaohang.php" class="btn-link">+ Them dia chi khac</a>
        </div>
      <?php endif; ?>
    </div>

    <!-- THANH TOAN & GHI CHU -->
    <div class="card">
      <div class="card-title">&#x1F4B3; PHUONG THUC THANH TOAN</div>
      <select name="ThanhToan">
        <option value="COD">Tien mat khi nhan hang (COD)</option>
        <option value="ChuyenKhoan">Chuyen khoan ngan hang</option>
        <option value="Momo">Vi MoMo</option>
      </select>
      <div class="card-title">&#x1F4DD; GHI CHU</div>
      <textarea name="GhiChu" rows="3" placeholder="Vi du: Giao gio hanh chinh..."></textarea>
    </div>
  </div>

  <!-- COT PHAI -->
  <div>
    <div class="card">
      <div class="card-title">&#x1F6D2; TOM TAT DON HANG</div>

      <!-- Danh sach san pham -->
      <?php foreach ($_SESSION['giohang'] as $sp): ?>
        <div class="sp-item">
          <div>
            <div class="sp-name"><?= htmlspecialchars($sp['TenSP']) ?></div>
            <div class="sp-qty">x<?= $sp['SoLuong'] ?></div>
          </div>
          <div class="sp-price"><?= number_format($sp['Gia']*$sp['SoLuong'],0,',','.') ?>d</div>
        </div>
      <?php endforeach; ?>

      <!-- MA GIAM GIA -->
      <div style="margin-top:20px">
        <div class="card-title" style="font-size:13px;margin-bottom:12px">&#x1F3AB; MA GIAM GIA</div>

        <?php if ($maGGInfo): ?>
          <!-- Da ap dung -->
          <div class="code-applied">
            <span>&#x2705; <?= htmlspecialchars($maGGInfo['Code']) ?> — Giam <?= number_format($giamGia,0,',','.') ?>d</span>
            <a href="?remove_code=1"><button type="button" class="btn-remove-code" title="Xoa ma">&#x2715;</button></a>
          </div>
        <?php else: ?>
          <!-- Chua ap dung -->
          <div class="code-wrap">
            <input type="text" id="codeInput" class="code-input" placeholder="NHAP MA GIAM GIA">
            <button type="button" class="btn-apply" onclick="applyCode()">AP DUNG</button>
          </div>
          <div id="codeMsg" class="code-msg"></div>
        <?php endif; ?>
      </div>

      <!-- TONG TIEN -->
      <div style="margin-top:16px">
        <div class="price-row">
          <span>Tam tinh</span>
          <span id="tongGoc"><?= number_format($tongTienGoc,0,',','.') ?>d</span>
        </div>
        <div class="price-row discount">
          <span>Giam gia</span>
          <span id="soGiam">-<?= number_format($giamGia,0,',','.') ?>d</span>
        </div>
        <div class="total-row">
          <span>TONG CONG</span>
          <span id="tongSau"><?= number_format($tongTienSau,0,',','.') ?>d</span>
        </div>
      </div>

      <button type="submit" class="btn-submit" <?= !$hasAddress?'disabled':'' ?>>
        &#x26A1; XAC NHAN CHOT DON
      </button>
      <div style="text-align:center;margin-top:14px">
        <a href="ChiTietGioHang.php" class="btn-link">&#x2190; Quay lai gio hang</a>
      </div>
    </div>
  </div>

</div>
</form>

<script>
const tongGoc = <?= $tongTienGoc ?>;

function formatMoney(n) {
  return n.toLocaleString('vi-VN') + 'd';
}

function applyCode() {
  const code = document.getElementById('codeInput').value.trim();
  const msg  = document.getElementById('codeMsg');
  if (!code) { showMsg('Vui long nhap ma giam gia!', false); return; }

  fetch('xu_ly_dat_hang.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'action=check_code&code=' + encodeURIComponent(code)
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showMsg(data.msg, true);
      document.getElementById('soGiam').textContent = '-' + formatMoney(data.giam);
      document.getElementById('tongSau').textContent = formatMoney(data.tongSau);
      // Reload de hien thi trang thai da ap dung
      setTimeout(() => location.reload(), 1000);
    } else {
      showMsg(data.msg, false);
    }
  })
  .catch(() => showMsg('Loi ket noi, thu lai!', false));
}

function showMsg(text, ok) {
  const el = document.getElementById('codeMsg');
  el.textContent = (ok ? '✅ ' : '❌ ') + text;
  el.className = 'code-msg ' + (ok ? 'code-ok' : 'code-er');
  el.style.display = 'block';
}

// Nhan Enter de ap dung ma
document.addEventListener('DOMContentLoaded', () => {
  const inp = document.getElementById('codeInput');
  if (inp) inp.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); applyCode(); } });
});
</script>
</body>
</html>
<?php sqlsrv_close($conn); ?>