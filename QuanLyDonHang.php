<?php
session_start();

// 1. KẾT NỐI DATABASE
$serverName = "localhost\\SQLEXPRESS";
$connectionInfo = ["Database"=>"QLBanHang","TrustServerCertificate"=>true,"CharacterSet"=>"UTF-8"];
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) die(print_r(sqlsrv_errors(), true));

// Kiểm tra đăng nhập
if (!isset($_SESSION['MaND'])) { header('Location: DangNhap.php'); exit; }
$user_id = (int)$_SESSION['MaND'];

// Lấy thông tin user hiện tại để kiểm tra VaiTro
$res_u  = sqlsrv_query($conn,"SELECT * FROM dbo.NguoiDung WHERE MaND=?",[$user_id]);
$user = sqlsrv_fetch_array($res_u, SQLSRV_FETCH_ASSOC);

// NẾU KHÔNG PHẢI ADMIN (VaiTro = 0) THÌ ĐÁ VĂNG VỀ TRANG CHỦ
if ($user['VaiTro'] == 0) {
    header('Location: TrangChuDaDangNhap.php');
    exit;
}

$success = ""; $error = "";

// 2. XỬ LÝ CẬP NHẬT TRẠNG THÁI ĐƠN HÀNG
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $maDH = (int)$_POST['MaDH'];
    $trangThaiMoi = $_POST['TrangThai'];

    $sql_update = "UPDATE DonHang SET TrangThai = ? WHERE MaDH = ?";
    $stmt_update = sqlsrv_query($conn, $sql_update, [$trangThaiMoi, $maDH]);

    if ($stmt_update) {
        $success = "Đã cập nhật trạng thái đơn hàng #$maDH thành: $trangThaiMoi";
    } else {
        $error = "Lỗi khi cập nhật trạng thái!";
    }
}

// 3. LẤY TOÀN BỘ DANH SÁCH ĐƠN HÀNG
$sql_dh = "SELECT * FROM DonHang ORDER BY NgayDat DESC";
$stmt_dh = sqlsrv_query($conn, $sql_dh);

// Màu sắc cho từng trạng thái
$ttColor = [
    'Chờ xử lý' => '#f59e0b', // Vàng cam
    'Đang giao' => '#3b82f6', // Xanh dương
    'Đã giao'   => '#22c55e', // Xanh lá
    'Đã hủy'    => '#ef4444'  // Đỏ
];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@300;400;600;700;900&family=Orbitron:wght@400;700;900&display=swap" rel="stylesheet">
<title>Quản Lý Đơn Hàng (Admin) - TechVN</title>
<style>
:root {
  --navy:    #050d1a; --navy2:   #071223; --panel:   #0d1f38; --panel2:  #0f2444;
  --cyan:    #00e5ff; --purple2: #a855f7; --tx:      #e2eaf5; --muted:   #7a92b0;
  --border:  rgba(0,229,255,0.12);
  --r: 14px;
}
*,*::before,*::after { box-sizing:border-box; margin:0; padding:0; }
body { font-family: 'Exo 2', sans-serif; background: var(--navy); color: var(--tx); padding: 24px 16px 60px; }

.topbar { max-width: 1200px; margin: 0 auto 28px; display: flex; align-items: center; justify-content: space-between; background: rgba(5,13,26,0.92); border: 1px solid var(--border); border-radius: var(--r); padding: 15px 20px; }
.logo { font-family: 'Orbitron', monospace; font-size: 20px; font-weight: 900; color: var(--cyan); }
.btn-back { color: var(--cyan); text-decoration: none; font-weight: bold; border: 1px solid var(--cyan); padding: 8px 15px; border-radius: 8px; transition: 0.2s; }
.btn-back:hover { background: var(--cyan); color: var(--navy); }

.container { max-width: 1200px; margin: 0 auto; background: var(--panel); border: 1px solid var(--border); border-radius: var(--r); padding: 25px; box-shadow: 0 8px 32px rgba(0,0,0,.5); }
.st { font-family: 'Orbitron', monospace; font-size: 18px; font-weight: 700; margin-bottom: 20px; color: var(--cyan); display: flex; align-items: center; gap: 8px; }
.st::after { content: ''; flex: 1; height: 1px; background: linear-gradient(90deg, rgba(0,229,255,0.4), transparent); }

/* Table Styles */
table { width: 100%; border-collapse: collapse; margin-top: 10px; }
th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid var(--border); font-size: 14px; }
th { color: var(--muted); text-transform: uppercase; font-size: 12px; background: var(--panel2); }
tr:hover { background: rgba(0,229,255,0.03); }

.price { color: var(--purple2); font-weight: bold; }
.al { padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; }
.ok { background: rgba(34,197,94,.1); border: 1px solid rgba(34,197,94,.3); color: #4ade80; }
.er { background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.3); color: #f87171; }

select { background: var(--navy); color: var(--tx); border: 1px solid var(--border); padding: 6px; border-radius: 5px; outline: none; font-family: 'Exo 2', sans-serif; cursor: pointer; }
.btn-update { background: var(--cyan); color: var(--navy); border: none; padding: 7px 12px; border-radius: 5px; font-weight: bold; cursor: pointer; transition: 0.2s; margin-left: 5px; }
.btn-update:hover { background: #00b8d4; }
</style>
</head>
<body>

<div class="topbar">
    <div class="logo">💻 ADMIN PANEL - QUẢN LÝ ĐƠN HÀNG</div>
    <a href="ChinhSuaProfile.php" class="btn-back">← Trở về Hồ Sơ</a>
</div>

<div class="container">
    <div class="st">DANH SÁCH TẤT CẢ ĐƠN HÀNG</div>

    <?php if ($success): ?><div class="al ok">✅ <?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="al er">❌ <?= htmlspecialchars($error) ?></div><?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Mã Đơn</th>
                <th>Ngày Đặt</th>
                <th>Khách Hàng / SĐT</th>
                <th>Địa Chỉ Giao</th>
                <th>Tổng Tiền</th>
                <th>Trạng Thái Hiện Tại</th>
                <th>Thao Tác Cập Nhật</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($dh = sqlsrv_fetch_array($stmt_dh, SQLSRV_FETCH_ASSOC)): 
                $ngay = ($dh['NgayDat'] instanceof DateTime) ? $dh['NgayDat']->format('d/m/Y H:i') : '';
                $c = $ttColor[$dh['TrangThai']] ?? '#888899';
            ?>
            <tr>
                <td style="font-weight: bold; color: var(--cyan);">#<?= $dh['MaDH'] ?></td>
                <td><?= $ngay ?></td>
                <td>
                    <b><?= htmlspecialchars($dh['HoTen'] ?? '') ?></b><br>
                    <span style="color: var(--muted); font-size: 12px;">SĐT: <?= htmlspecialchars($dh['SoDienThoai'] ?? '') ?></span>
                </td>
                <td style="max-width: 250px; line-height: 1.4;">
                    <?= htmlspecialchars($dh['DiaChi'] ?? '') ?>, <?= htmlspecialchars($dh['ThanhPho'] ?? '') ?>
                </td>
                <td class="price"><?= number_format($dh['TongTien'] ?? 0, 0, ',', '.') ?>đ</td>
                <td>
                    <span style="background: <?= $c ?>22; color: <?= $c ?>; border: 1px solid <?= $c ?>; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; white-space: nowrap;">
                        <?= htmlspecialchars($dh['TrangThai']) ?>
                    </span>
                </td>
                <td>
                    <form method="POST" action="" style="display: flex; align-items: center;">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="MaDH" value="<?= $dh['MaDH'] ?>">
                        <select name="TrangThai">
                            <option value="Chờ xử lý" <?= ($dh['TrangThai'] == 'Chờ xử lý') ? 'selected' : '' ?>>Chờ xử lý</option>
                            <option value="Đang giao" <?= ($dh['TrangThai'] == 'Đang giao') ? 'selected' : '' ?>>Đang giao</option>
                            <option value="Đã giao" <?= ($dh['TrangThai'] == 'Đã giao') ? 'selected' : '' ?>>Đã giao</option>
                            <option value="Đã hủy" <?= ($dh['TrangThai'] == 'Đã hủy') ? 'selected' : '' ?>>Đã hủy</option>
                        </select>
                        <button type="submit" class="btn-update">Lưu</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
<?php sqlsrv_close($conn); ?>