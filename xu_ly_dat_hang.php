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

// LẤY SỐ XU HIỆN CÓ TỪ DATABASE
$stmt_xu = sqlsrv_query($conn, "SELECT XuTichLuy FROM NguoiDung WHERE MaND=?", [$user_id]);
$u_info = sqlsrv_fetch_array($stmt_xu, SQLSRV_FETCH_ASSOC);
$xuHienCo = (int)($u_info['XuTichLuy'] ?? 0);

// Tinh tong tien gio hang$tongTienGoc = 0;
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
        $result['msg'] = 'Vui lòng nhập mã giảm giá!';
    } else {
        $now = date('Y-m-d H:i:s');
        $stmt = sqlsrv_query($conn,
            "SELECT * FROM MaGiamGia WHERE Code=? AND TrangThai=1 AND (NgayHetHan IS NULL OR NgayHetHan > ?) AND DaDung < SoLanDung",
            [$code, $now]);
        $mg = $stmt ? sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC) : null;

        if (!$mg) {
            $result['msg'] = 'Mã giảm giá không hợp lệ, hết hạn hoặc đã hết lượt dùng!';
        } elseif ($tongTienGoc < $mg['DonToiThieu']) {
            $result['msg'] = 'Đơn tối thiểu ' . number_format($mg['DonToiThieu'],0,',','.') . 'đ để dùng mã này!';
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
                ? 'Áp dụng thành công! Giảm ' . $mg['GiaTri'] . '%' . ($mg['GiamToiDa']>0 ? ' (tối đa '.number_format($mg['GiamToiDa'],0,',','.').'đ)' : '')
                : 'Áp dụng thành công! Giảm ' . number_format($giam,0,',','.') . 'đ';
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
        $error = "Vui lòng chọn địa chỉ giao hàng!";
    } else {
        $stmt_dc = sqlsrv_query($conn, "SELECT * FROM SoDiaChi WHERE MaDC=? AND MaND=?", [$maDC, $user_id]);
        $dc      = sqlsrv_fetch_array($stmt_dc, SQLSRV_FETCH_ASSOC);
        $stmt_u  = sqlsrv_query($conn, "SELECT Email FROM NguoiDung WHERE MaND=?", [$user_id]);
        $u       = sqlsrv_fetch_array($stmt_u, SQLSRV_FETCH_ASSOC);
        $email   = $u['Email'] ?? '';

      if ($dc) {
            // XỬ LÝ KHÁCH CÓ TICK CHỌN DÙNG XU HAY KHÔNG
            $dungXu = isset($_POST['DungXu']) ? 1 : 0;
            $tienTruXu = 0;
            if ($dungXu == 1) {
                $tienTruXu = $xuHienCo; // 1 Xu = 1đ
                if ($tienTruXu > $tongTienSau) $tienTruXu = $tongTienSau; // Không trừ âm
            }
            $tongTienThanhToan = $tongTienSau - $tienTruXu;
            
            // THƯỞNG XU MỚI BẰNG 1% GIÁ TRỊ THANH TOÁN
            $xuThuThuong = floor($tongTienThanhToan * 0.01);

            $sql_dh = "INSERT INTO DonHang (MaND, TongTien, HoTen, SoDienThoai, Email, DiaChi, ThanhPho, ThanhToan, GhiChu)
                       OUTPUT INSERTED.MaDH
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_dh = sqlsrv_query($conn, $sql_dh, [
                $user_id, $tongTienThanhToan, // Sửa lại lưu tổng tiền cuối cùng
                $dc['HoTenNguoiNhan'], $dc['SoDienThoai'], $email,
                $dc['DiaChiCuThe'], $dc['ThanhPho'], $thanhToan, $ghiChu
            ]);

            if ($stmt_dh) {
                sqlsrv_fetch($stmt_dh);
                $maDH_Moi = sqlsrv_get_field($stmt_dh, 0);

                foreach($_SESSION['giohang'] as $maSP => $sp) {
                    $soLuongMua = $sp['SoLuong'];
                    $giaBan = $sp['Gia'];

                    $sql_insert_ct = "INSERT INTO ChiTietDonHang (MaDH, MaSP, SoLuong, DonGia) VALUES (?, ?, ?, ?)";
                    sqlsrv_query($conn, $sql_insert_ct, [$maDH_Moi, $maSP, $soLuongMua, $giaBan]);

                    $sql_tru_kho = "UPDATE SanPham SET SoLuongTon = SoLuongTon - ? WHERE MaSP = ?";
                    sqlsrv_query($conn, $sql_tru_kho, [$soLuongMua, $maSP]);
                }

               if (isset($_SESSION['maGiamGia'])) {
                    $maMGG_DaDung = $_SESSION['maGiamGia']['MaMGG'];
                    sqlsrv_query($conn, "UPDATE MaGiamGia SET DaDung = DaDung + 1 WHERE MaMGG = ?", [$maMGG_DaDung]);
                    sqlsrv_query($conn, "UPDATE ViGiamGia SET TrangThaiSuDung = 1 WHERE MaND = ? AND MaMGG = ?", [$user_id, $maMGG_DaDung]);
                    
                    unset($_SESSION['maGiamGia']);
                }

// CHỈ TRỪ XU NẾU KHÁCH CÓ TICK DÙNG XU (TUYỆT ĐỐI KHÔNG CỘNG XU MỚI Ở ĐÂY NỮA)
                if ($tienTruXu > 0) {
                    $sql_update_xu = "UPDATE NguoiDung SET XuTichLuy = XuTichLuy - ? WHERE MaND = ?";
                    sqlsrv_query($conn, $sql_update_xu, [$tienTruXu, $user_id]);
                }
                unset($_SESSION['giohang']);
                unset($_SESSION['giohang']);
                
                // --- PHÂN LUỒNG THANH TOÁN ---
                if ($thanhToan === 'COD') {
                    // Nếu là COD -> Báo thành công và về trang quản lý đơn
                    echo "<script>
                        alert('Đặt hàng thành công! Mã đơn của bạn là #".$maDH_Moi."');
                        window.location.href='ChinhSuaProfile.php?s=donhang_khach';
                    </script>";
                } else {
                    // Nếu là Chuyển khoản / MoMo -> Đẩy sang trang Quét QR
                    echo "<script>
                        window.location.href='ThanhToanQR.php?madh=".$maDH_Moi."';
                    </script>";
                }
                exit;
            } else {
                $error = "Có lỗi xảy ra khi tạo đơn hàng. Vui lòng thử lại!";
            }
        } else {
            $error = "Địa chỉ giao hàng không hợp lệ!";
        }
    }
}

// Lay danh sach dia chi
$stmt_ds_dc = sqlsrv_query($conn, "SELECT * FROM SoDiaChi WHERE MaND=? ORDER BY MacDinh DESC", [$user_id]);
$hasAddress = false;

// ============================================================
// LẤY DANH SÁCH MÃ GIẢM GIÁ ĐÃ LƯU TRONG VÍ ĐỂ HIỆN LÊN MODAL
// ============================================================
$sql_vi = "SELECT m.*, v.NgayLuu FROM ViGiamGia v 
           JOIN MaGiamGia m ON v.MaMGG = m.MaMGG 
           WHERE v.MaND = ? AND v.TrangThaiSuDung = 0
           ORDER BY v.NgayLuu DESC";
$stmt_vi = sqlsrv_query($conn, $sql_vi, [$user_id]);
$savedCoupons = [];
if ($stmt_vi) {
    while($v = sqlsrv_fetch_array($stmt_vi, SQLSRV_FETCH_ASSOC)) {
        $savedCoupons[] = $v;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Thanh Toán - QLBanHang</title>
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
.btn-apply{padding:10px 14px;border-radius:8px;background:rgba(0,229,255,0.15);color:#00e5ff;border:1.5px solid rgba(0,229,255,0.3);font-family:'Exo 2',sans-serif;font-size:12px;font-weight:700;cursor:pointer;transition:.2s;white-space:nowrap}
.btn-apply:hover{background:rgba(0,229,255,0.25)}
.code-msg{font-size:13px;padding:10px 14px;border-radius:8px;margin-bottom:12px;display:none}
.code-ok{background:rgba(34,197,94,0.1);color:#4ade80;border:1px solid rgba(34,197,94,0.3)}
.code-er{background:rgba(239,68,68,0.1);color:#f87171;border:1px solid rgba(239,68,68,0.3)}
.code-applied{display:flex;align-items:center;justify-content:space-between;background:rgba(34,197,94,0.08);border:1px solid rgba(34,197,94,0.25);border-radius:8px;padding:10px 14px;margin-bottom:12px}
.code-applied span{color:#4ade80;font-size:13px;font-weight:600}
.btn-remove-code{background:none;border:none;color:#f87171;cursor:pointer;font-size:18px;line-height:1;padding:0 4px}

/* MODAL CHON MA DA LUU */
.modal-overlay { position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.7); display:none; align-items:center; justify-content:center; z-index:999; backdrop-filter:blur(5px); }
.modal-content { background:#0d1f38; border:1px solid rgba(0,229,255,0.2); border-radius:12px; width:90%; max-width:450px; padding:25px; position:relative; max-height:80vh; overflow-y:auto; }
.modal-close { position:absolute; top:15px; right:15px; background:none; border:none; color:#7a92b0; font-size:20px; cursor:pointer; }
.modal-close:hover { color:#ef4444; }
.voucher-item { background:#0f2444; border:1px solid rgba(0,229,255,0.1); border-radius:8px; padding:15px; margin-bottom:12px; display:flex; justify-content:space-between; align-items:center; transition:0.2s;}
.voucher-item:hover { border-color:rgba(0,229,255,0.4); }
/* Class dành cho mã bị mờ do không đủ điều kiện */
.voucher-item.disabled { opacity:0.5; filter:grayscale(1); pointer-events:none; border-color:rgba(255,255,255,0.05); }

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

  <div>
    <div class="card">
      <div class="card-title">&#x1F4CD; CHỌN ĐỊA CHỈ GIAO HÀNG</div>
      <?php while ($dc = sqlsrv_fetch_array($stmt_ds_dc, SQLSRV_FETCH_ASSOC)): $hasAddress = true; ?>
        <label class="addr-box">
          <input type="radio" name="MaDC" value="<?= $dc['MaDC'] ?>" <?= $dc['MacDinh']==1?'checked':'' ?> required>
          <div class="addr-info">
            <h4><?= htmlspecialchars($dc['HoTenNguoiNhan']) ?> - <?= htmlspecialchars($dc['SoDienThoai']) ?>
              <?php if ($dc['MacDinh']==1): ?><span class="badge-default">Mặc định</span><?php endif; ?>
            </h4>
            <p><?= htmlspecialchars($dc['DiaChiCuThe']) ?></p>
            <p><?= htmlspecialchars($dc['ThanhPho']) ?></p>
          </div>
        </label>
      <?php endwhile; ?>
      <?php if (!$hasAddress): ?>
        <div style="text-align:center;padding:20px;color:#7a92b0">
          Bạn chưa có địa chỉ nào!<br><br>
          <a href="diachigiaohang.php" class="btn-submit" style="display:inline-block;width:auto;text-decoration:none;padding:10px 20px;font-size:14px">+ Thêm địa chỉ mới</a>
        </div>
      <?php else: ?>
        <div style="text-align:right;margin-top:8px">
          <a href="diachigiaohang.php" class="btn-link">+ Thêm địa chỉ khác</a>
        </div>
      <?php endif; ?>
    </div>

    <div class="card">
      <div class="card-title">&#x1F4B3; PHƯƠNG THỨC THANH TOÁN</div>
      <select name="ThanhToan">
        <option value="COD">Tiền mặt khi nhận hàng (COD)</option>
        <option value="ChuyenKhoan">Chuyển khoản ngân hàng</option>
        <option value="Momo">Ví MoMo</option>
      </select>
      <div class="card-title">&#x1F4DD; GHI CHÚ</div>
      <textarea name="GhiChu" rows="3" placeholder="Ví dụ: Giao giờ hành chính..."></textarea>
    </div>
  </div>

  <div>
    <div class="card">
      <div class="card-title">&#x1F6D2; TÓM TẮT ĐƠN HÀNG</div>

      <?php foreach ($_SESSION['giohang'] as $sp): ?>
        <div class="sp-item">
          <div>
            <div class="sp-name"><?= htmlspecialchars($sp['TenSP']) ?></div>
            <div class="sp-qty">x<?= $sp['SoLuong'] ?></div>
          </div>
          <div class="sp-price"><?= number_format($sp['Gia']*$sp['SoLuong'],0,',','.') ?>đ</div>
        </div>
      <?php endforeach; ?>

      <div style="margin-top:20px">
        <div class="card-title" style="font-size:13px;margin-bottom:12px">&#x1F3AB; MÃ GIẢM GIÁ</div>

        <?php if ($maGGInfo): ?>
          <div class="code-applied">
            <span>&#x2705; <?= htmlspecialchars($maGGInfo['Code']) ?> — Giảm <?= number_format($giamGia,0,',','.') ?>đ</span>
            <a href="?remove_code=1"><button type="button" class="btn-remove-code" title="Xóa mã">&#x2715;</button></a>
          </div>
        <?php else: ?>
          <div class="code-wrap">
            <input type="text" id="codeInput" class="code-input" placeholder="NHẬP MÃ GIẢM GIÁ">
            <button type="button" class="btn-apply" onclick="applyCode()">ÁP DỤNG</button>
            <button type="button" class="btn-apply" style="background:rgba(168,85,247,0.15); color:#a855f7; border-color:rgba(168,85,247,0.3);" onclick="openVoucherModal()">💳 MÃ ĐÃ LƯU</button>
          </div>
          <div id="codeMsg" class="code-msg"></div>
        <?php endif; ?>
      </div>

      <div style="margin-top:16px">
        <?php if ($xuHienCo > 0): ?>
        <label style="display:flex; align-items:center; gap:8px; background:rgba(245,158,11,0.1); padding:12px; border-radius:8px; border:1px solid rgba(245,158,11,0.3); color:#fbbf24; cursor:pointer; font-size:14px; margin-bottom:12px;">
            <input type="checkbox" name="DungXu" id="chkDungXu" value="1" onchange="tinhLaiTienDeHienThi()" style="accent-color:#f59e0b; width:18px; height:18px;">
            Dùng <strong style="font-family:'Orbitron'; font-size:16px;"><?= number_format($xuHienCo,0,',','.') ?> Xu</strong> 
            <span style="color:var(--muted); font-size:12px;">(Giảm <?= number_format($xuHienCo,0,',','.') ?>đ)</span>
        </label>
        <?php endif; ?>

        <div class="price-row">
          <span>Tạm tính</span>
          <span id="tongGoc"><?= number_format($tongTienGoc,0,',','.') ?>đ</span>
        </div>
        <div class="price-row discount" id="rowXu" style="display: none; color: #fbbf24;">
          <span>Dùng Xu</span>
          <span id="soTruXu">-0đ</span>
        </div>
        <div class="price-row discount">
          <span>Giảm giá</span>
          <span id="soGiam">-<?= number_format($giamGia,0,',','.') ?>đ</span>
        </div>
        <div class="total-row">
          <span>TỔNG CỘNG</span>
          <span id="tongSau"><?= number_format($tongTienSau,0,',','.') ?>đ</span>
        </div>
      </div>

      <button type="submit" class="btn-submit" <?= !$hasAddress?'disabled':'' ?>>
        &#x26A1; XÁC NHẬN CHỐT ĐƠN
      </button>
      <div style="text-align:center;margin-top:14px">
        <a href="ChiTietGioHang.php" class="btn-link">&#x2190; Quay lại giỏ hàng</a>
      </div>
    </div>
  </div>

</div>
</form>

<div class="modal-overlay" id="voucherModal">
    <div class="modal-content">
        <button class="modal-close" onclick="closeVoucherModal()">✕</button>
        <div class="card-title" style="margin-top:0;">🎫 VÍ VOUCHER CỦA BẠN</div>
        
        <?php if(count($savedCoupons) > 0): ?>
            <div style="max-height:400px; overflow-y:auto; padding-right:5px;">
            <?php foreach($savedCoupons as $v): 
                $loai = $v['LoaiGiam'] == 0 ? "Giảm ".$v['GiaTri']."%" : "Giảm ".number_format($v['GiaTri'],0,',','.')."đ";
                $hsd = $v['NgayHetHan'] ? $v['NgayHetHan']->format('H:i d/m/Y') : "Không giới hạn";
                
                // KIỂM TRA MÃ CÒN SỐNG VÀ ĐỦ ĐIỀU KIỆN KHÔNG
                $now = new DateTime();
                $isExpired = ($v['NgayHetHan'] && $v['NgayHetHan'] <= $now); // Đã hết hạn chưa?
                $isEnough = ($tongTienGoc >= $v['DonToiThieu']);             // Đơn đủ tiền chưa?
                $isActive = ($v['TrangThai'] == 1);                          // Còn hoạt động không?
                
                // Mã chỉ sáng lên và cho bấm DÙNG nếu thỏa mãn toàn bộ
                $isEligible = $isEnough && !$isExpired && $isActive;
            ?>
                <div class="voucher-item <?= $isEligible ? '' : 'disabled' ?>">
                    <div>
                        <h4 style="color:var(--cyan); margin:0 0 5px; font-family:'Orbitron'; font-size:16px;"><?= htmlspecialchars($v['Code']) ?></h4>
                        <p style="font-size:12px; color:var(--text); margin:0 0 3px;"><?= $loai ?></p>
                        
                        <p style="font-size:11px; color: <?= $isEnough ? 'var(--muted)' : '#ef4444' ?>; margin:0 0 3px;">
                            Đơn tối thiểu: <?= number_format($v['DonToiThieu'],0,',','.') ?>đ
                        </p>
                        
                        <p style="font-size:11px; color: <?= !$isExpired ? 'var(--muted)' : '#ef4444' ?>; margin:0;">
                            ⏳ Hạn dùng: <?= $hsd ?>
                        </p>
                    </div>
                    
                    <?php if($isEligible): ?>
                        <button type="button" class="btn-apply" style="padding:6px 12px;" onclick="applySavedCode('<?= htmlspecialchars($v['Code']) ?>')">Dùng</button>
                    <?php else: ?>
                        <span style="font-size:11px; color:#ef4444; font-weight:bold;">
                            <?= $isExpired ? 'Đã hết hạn' : (!$isActive ? 'Vô hiệu hóa' : 'Chưa đạt ĐK') ?>
                        </span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align:center; padding:30px 10px; color:var(--muted);">
                <div style="font-size:40px; margin-bottom:10px;">🛒</div>
                Bạn chưa lưu mã giảm giá nào.<br>Hãy săn mã ở Trang Chủ nhé!
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
const tongGoc = <?= $tongTienGoc ?>;

function formatMoney(n) {
  return n.toLocaleString('vi-VN') + 'đ';
}

function openVoucherModal() {
    document.getElementById('voucherModal').style.display = 'flex';
}

function closeVoucherModal() {
    document.getElementById('voucherModal').style.display = 'none';
}

// Khi người dùng bấm "Dùng" trong Modal
function applySavedCode(code) {
    closeVoucherModal();
    document.getElementById('codeInput').value = code; // Tự động điền mã vào ô
    applyCode(); // Gọi hàm áp dụng mã
}

function applyCode() {
  const code = document.getElementById('codeInput').value.trim();
  const msg  = document.getElementById('codeMsg');
  if (!code) { showMsg('Vui lòng nhập mã giảm giá!', false); return; }

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
      
      // GỌI HÀM TÍNH LẠI TIỀN ĐỂ TỔNG KẾT CẢ GIẢM GIÁ LẪN XU
      tinhLaiTienDeHienThi();
      
      setTimeout(() => location.reload(), 1500); // Đợi xíu cho khách thấy chữ thành công rồi reload
    } else {
      showMsg(data.msg, false);
    }
  })
  .catch(() => showMsg('Lỗi kết nối, thử lại!', false));
}

function showMsg(text, ok) {
  const el = document.getElementById('codeMsg');
  el.textContent = (ok ? '✅ ' : '❌ ') + text;
  el.className = 'code-msg ' + (ok ? 'code-ok' : 'code-er');
  el.style.display = 'block';
}

// --- HÀM TỰ ĐỘNG TÍNH LẠI TIỀN KHI BẤM DÙNG XU HOẶC NHẬP MÃ ---
const soXuHienCo = <?= $xuHienCo ?>;

function tinhLaiTienDeHienThi() {
    let chk = document.getElementById('chkDungXu');
    
    // Lấy tổng gốc và giảm giá hiện tại (cắt bỏ ký tự đ và chấm)
    let tongGocSo = parseInt(document.getElementById('tongGoc').textContent.replace(/\D/g,'')) || 0;
    let giamGiaSo = parseInt(document.getElementById('soGiam').textContent.replace(/\D/g,'')) || 0;
    let tongHienTai = tongGocSo - giamGiaSo;
    
    let tienTruXu = 0;
    let rowXu = document.getElementById('rowXu');
    let soTruXu = document.getElementById('soTruXu');

    // Nếu khách có tick vào nút dùng Xu
    if (chk && chk.checked) {
        tienTruXu = soXuHienCo;
        if (tienTruXu > tongHienTai) tienTruXu = tongHienTai; // Không cho trừ âm
        
        rowXu.style.display = 'flex';
        soTruXu.textContent = '-' + formatMoney(tienTruXu);
    } else {
        if(rowXu) rowXu.style.display = 'none';
        if(soTruXu) soTruXu.textContent = '-0đ';
    }
    
    // Cập nhật lại tổng tiền thanh toán cuối cùng
    let thanhToanCuoiCung = tongHienTai - tienTruXu;
    document.getElementById('tongSau').textContent = formatMoney(thanhToanCuoiCung);
}

// Nhan Enter de ap dung ma
document.addEventListener('DOMContentLoaded', () => {
  const inp = document.getElementById('codeInput');
  if (inp) inp.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); applyCode(); } });
  
  // Khởi chạy tính tiền lần đầu tiên để đảm bảo tổng số chính xác
  tinhLaiTienDeHienThi();
});
</script>
</body>
</html> 