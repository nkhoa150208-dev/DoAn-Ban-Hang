<?php
session_start();

// Chỉ admin mới được vào
if (!isset($_SESSION['MaND']) || $_SESSION['VaiTro'] != 1) {
    header('Location: DangNhap.php');
    exit;
}

include 'config.php';

// Xử lý cập nhật trạng thái
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $maDH      = (int)$_POST['maDH'];
    $trangThai = htmlspecialchars($_POST['trangThai']);

    $sql  = "UPDATE DonHang SET TrangThai = ? WHERE MaDH = ?";
    $stmt = sqlsrv_query($conn, $sql, [$trangThai, $maDH]);

    header('Location: quan_ly_don_hang.php?msg=ok');
    exit;
}

// Lọc theo trạng thái
$filter = $_GET['filter'] ?? 'all';
$msg    = $_GET['msg']    ?? '';

$where = $filter !== 'all' ? "WHERE TrangThai = N'$filter'" : "";

$sqlDH = "SELECT MaDH, MaND, HoTen, SoDienThoai, Email, DiaChi, ThanhPho,
                 ThanhToan, GhiChu, TongTien, TrangThai, NgayDat
          FROM DonHang
          $where
          ORDER BY NgayDat DESC";

$stmtDH = sqlsrv_query($conn, $sqlDH);
$donHangs = [];
while ($row = sqlsrv_fetch_array($stmtDH, SQLSRV_FETCH_ASSOC)) {
    $row['NgayDat'] = $row['NgayDat'] instanceof DateTime
        ? $row['NgayDat']->format('d/m/Y H:i')
        : (is_string($row['NgayDat']) ? $row['NgayDat'] : '');
    $donHangs[] = $row;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Đơn Hàng</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;600;700;900&family=Exo+2:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:       #030810;
            --surface:  #0a1628;
            --surface2: #0f2040;
            --border:   rgba(0,229,255,0.15);
            --cyan:     #00e5ff;
            --green:    #22c55e;
            --yellow:   #f59e0b;
            --red:      #ef4444;
            --purple:   #a855f7;
            --text:     #e2eaf5;
            --muted:    #4a6080;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Exo 2', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }

        /* Scan line effect */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: repeating-linear-gradient(
                0deg,
                transparent,
                transparent 2px,
                rgba(0,229,255,0.015) 2px,
                rgba(0,229,255,0.015) 4px
            );
            pointer-events: none;
            z-index: 9999;
        }

        /* HEADER */
        .header {
            background: linear-gradient(135deg, #060f20 0%, #0a1628 100%);
            border-bottom: 1px solid var(--border);
            padding: 18px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .header-left { display: flex; align-items: center; gap: 14px; }
        .header-icon {
            width: 42px; height: 42px;
            background: linear-gradient(135deg, var(--cyan), #0077ff);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
        }
        .header h1 {
            font-family: 'Orbitron', sans-serif;
            font-size: 18px;
            color: var(--cyan);
            letter-spacing: 2px;
        }
        .header-sub { font-size: 12px; color: var(--muted); margin-top: 2px; }
        .admin-badge {
            background: rgba(168,85,247,0.15);
            border: 1px solid rgba(168,85,247,0.4);
            color: var(--purple);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 1px;
        }

        /* MAIN */
        .main { padding: 30px 40px; max-width: 1400px; margin: 0 auto; }

        /* STATS */
        .stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 30px; }
        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
        }
        .stat-card.cyan::before  { background: var(--cyan); }
        .stat-card.yellow::before { background: var(--yellow); }
        .stat-card.green::before  { background: var(--green); }
        .stat-card.red::before    { background: var(--red); }

        .stat-label { font-size: 11px; color: var(--muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
        .stat-value { font-family: 'Orbitron', sans-serif; font-size: 28px; font-weight: 700; }
        .stat-card.cyan  .stat-value { color: var(--cyan); }
        .stat-card.yellow .stat-value { color: var(--yellow); }
        .stat-card.green  .stat-value { color: var(--green); }
        .stat-card.red    .stat-value { color: var(--red); }

        /* FILTERS */
        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 12px;
        }
        .filter-tabs { display: flex; gap: 8px; flex-wrap: wrap; }
        .filter-tab {
            padding: 8px 18px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: transparent;
            color: var(--muted);
            font-family: 'Exo 2', sans-serif;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }
        .filter-tab:hover, .filter-tab.active {
            border-color: var(--cyan);
            color: var(--cyan);
            background: rgba(0,229,255,0.07);
        }

        /* SUCCESS MSG */
        .msg-success {
            background: rgba(34,197,94,0.1);
            border: 1px solid rgba(34,197,94,0.3);
            color: var(--green);
            padding: 12px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* TABLE */
        .table-wrap {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            overflow: hidden;
        }
        table { width: 100%; border-collapse: collapse; }
        thead { background: var(--surface2); }
        th {
            padding: 14px 16px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--muted);
            font-weight: 600;
            white-space: nowrap;
        }
        td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border);
            font-size: 14px;
            vertical-align: middle;
        }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: rgba(0,229,255,0.03); }

        .order-id {
            font-family: 'Orbitron', sans-serif;
            font-size: 13px;
            color: var(--cyan);
        }
        .customer-name { font-weight: 600; color: var(--text); }
        .customer-phone { font-size: 12px; color: var(--muted); margin-top: 3px; }
        .amount {
            font-family: 'Orbitron', sans-serif;
            font-size: 14px;
            color: var(--green);
            white-space: nowrap;
        }
        .date { font-size: 12px; color: var(--muted); white-space: nowrap; }

        /* BADGE TRẠNG THÁI */
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }
        .badge-cho    { background: rgba(245,158,11,0.15); color: var(--yellow); border: 1px solid rgba(245,158,11,0.3); }
        .badge-dang   { background: rgba(0,229,255,0.1);   color: var(--cyan);   border: 1px solid rgba(0,229,255,0.3); }
        .badge-da     { background: rgba(34,197,94,0.1);   color: var(--green);  border: 1px solid rgba(34,197,94,0.3); }
        .badge-huy    { background: rgba(239,68,68,0.1);   color: var(--red);    border: 1px solid rgba(239,68,68,0.3); }

        /* ACTION BUTTONS */
        .btn-detail {
            background: rgba(0,229,255,0.1);
            border: 1px solid rgba(0,229,255,0.3);
            color: var(--cyan);
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-detail:hover { background: rgba(0,229,255,0.2); }

        /* EMPTY */
        .empty { text-align: center; padding: 60px; color: var(--muted); }
        .empty-icon { font-size: 48px; margin-bottom: 16px; }

        /* ===== MODAL CHI TIẾT ===== */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.8);
            backdrop-filter: blur(6px);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .modal-overlay.active { display: flex !important; }

        .modal {
            background: #0a1628;
            border: 1px solid rgba(0,229,255,0.25);
            border-radius: 18px;
            width: 100%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            animation: modalIn 0.25s ease;
        }
        @keyframes modalIn {
            from { transform: translateY(-20px) scale(0.97); opacity: 0; }
            to   { transform: translateY(0)     scale(1);    opacity: 1; }
        }

        .modal-header {
            padding: 24px 28px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .modal-title {
            font-family: 'Orbitron', sans-serif;
            font-size: 16px;
            color: var(--cyan);
            letter-spacing: 1px;
        }
        .modal-order-id { font-size: 12px; color: var(--muted); margin-top: 4px; }
        .modal-close {
            background: none; border: none;
            color: var(--muted); font-size: 20px;
            cursor: pointer; line-height: 1;
            padding: 4px;
        }
        .modal-close:hover { color: var(--text); }

        .modal-body { padding: 24px 28px; }

        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px; }
        .info-item { }
        .info-item.full { grid-column: 1 / -1; }
        .info-lbl {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--muted);
            margin-bottom: 5px;
        }
        .info-val {
            font-size: 14px;
            font-weight: 500;
            color: var(--text);
        }
        .info-val.highlight { color: var(--cyan); font-family: 'Orbitron', sans-serif; font-size: 13px; }

        /* Sản phẩm trong đơn */
        .products-title {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--muted);
            margin-bottom: 12px;
        }
        .product-list {
            background: #060f20;
            border: 1px solid var(--border);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 24px;
        }
        .product-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            border-bottom: 1px solid var(--border);
        }
        .product-item:last-child { border-bottom: none; }
        .product-name { font-size: 14px; font-weight: 500; }
        .product-qty  { font-size: 12px; color: var(--muted); margin-top: 2px; }
        .product-price { color: var(--purple); font-weight: 600; font-size: 14px; white-space: nowrap; }

        .modal-total {
            background: rgba(34,197,94,0.08);
            border: 1px solid rgba(34,197,94,0.2);
            border-radius: 10px;
            padding: 14px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        .modal-total-lbl { font-size: 13px; color: var(--muted); }
        .modal-total-val { font-family: 'Orbitron', sans-serif; font-size: 18px; color: var(--green); font-weight: 700; }

        /* Cập nhật trạng thái */
        .update-section {
            border-top: 1px solid var(--border);
            padding-top: 20px;
        }
        .update-title {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--muted);
            margin-bottom: 12px;
        }
        .status-options { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 16px; }
        .status-opt {
            flex: 1; min-width: 120px;
            background: #060f20;
            border: 2px solid var(--border);
            border-radius: 10px;
            padding: 10px 14px;
            cursor: pointer;
            text-align: center;
            transition: all 0.2s;
        }
        .status-opt input[type="radio"] { display: none; }
        .status-opt .s-icon { font-size: 20px; margin-bottom: 4px; }
        .status-opt .s-lbl { font-size: 12px; font-weight: 600; color: var(--muted); }
        .status-opt.selected-cho    { border-color: var(--yellow); background: rgba(245,158,11,0.08); }
        .status-opt.selected-dang   { border-color: var(--cyan);   background: rgba(0,229,255,0.08); }
        .status-opt.selected-da     { border-color: var(--green);  background: rgba(34,197,94,0.08); }
        .status-opt.selected-huy    { border-color: var(--red);    background: rgba(239,68,68,0.08); }
        .status-opt.selected-cho  .s-lbl  { color: var(--yellow); }
        .status-opt.selected-dang .s-lbl  { color: var(--cyan); }
        .status-opt.selected-da   .s-lbl  { color: var(--green); }
        .status-opt.selected-huy  .s-lbl  { color: var(--red); }

        .btn-update {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #00b4d8, #0077ff);
            border: none;
            border-radius: 10px;
            color: white;
            font-family: 'Orbitron', sans-serif;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 1px;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        .btn-update:hover { opacity: 0.85; }

        /* Payment badge */
        .pay-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        .pay-cod  { background: rgba(245,158,11,0.15); color: var(--yellow); }
        .pay-ck   { background: rgba(0,229,255,0.1);   color: var(--cyan); }
        .pay-momo { background: rgba(168,85,247,0.15); color: var(--purple); }
    </style>
</head>
<body>

<!-- HEADER -->
<div class="header">
    <div class="header-left">
        <div class="header-icon">📦</div>
        <div>
            <h1>QUẢN LÝ ĐƠN HÀNG</h1>
            <div class="header-sub">QLBanHang · Xin chào, <?php echo htmlspecialchars($_SESSION['HoTen']); ?></div>
        </div>
    </div>
    <div style="display:flex; gap:12px; align-items:center;">
        <span class="admin-badge">⚡ ADMIN</span>
        <a href="TrangChuDaDangNhap.php" style="color:var(--muted); font-size:13px; text-decoration:none;">← Trang chủ</a>
    </div>
</div>

<div class="main">

    <?php if($msg === 'ok'): ?>
    <div class="msg-success">✅ Cập nhật trạng thái đơn hàng thành công!</div>
    <?php endif; ?>

    <!-- STATS -->
    <?php
    $total   = count($donHangs);
    $cho     = count(array_filter($donHangs, fn($d) => str_contains($d['TrangThai'] ?? '', 'Chờ')));
    $dang    = count(array_filter($donHangs, fn($d) => str_contains($d['TrangThai'] ?? '', 'giao')));
    $da      = count(array_filter($donHangs, fn($d) => str_contains($d['TrangThai'] ?? '', 'Đã giao')));
    $tongDoanhThu = array_sum(array_column($donHangs, 'TongTien'));
    ?>
    <div class="stats">
        <div class="stat-card cyan">
            <div class="stat-label">Tổng đơn hàng</div>
            <div class="stat-value"><?php echo $total; ?></div>
        </div>
        <div class="stat-card yellow">
            <div class="stat-label">Chờ xác nhận</div>
            <div class="stat-value"><?php echo $cho; ?></div>
        </div>
        <div class="stat-card cyan" style="--cyan:#0ea5e9">
            <div class="stat-label">Đang giao</div>
            <div class="stat-value"><?php echo $dang; ?></div>
        </div>
        <div class="stat-card green">
            <div class="stat-label">Doanh thu</div>
            <div class="stat-value" style="font-size:18px;"><?php echo number_format($tongDoanhThu, 0, ',', '.'); ?>đ</div>
        </div>
    </div>

    <!-- TOOLBAR -->
    <div class="toolbar">
        <div class="filter-tabs">
            <a href="?filter=all"           class="filter-tab <?= $filter==='all'         ?'active':'' ?>">Tất cả (<?= $total ?>)</a>
            <a href="?filter=Chờ xử lý"    class="filter-tab <?= $filter==='Chờ xử lý'  ?'active':'' ?>">⏳ Chờ xử lý</a>
            <a href="?filter=Đang giao"     class="filter-tab <?= $filter==='Đang giao'   ?'active':'' ?>">🚚 Đang giao</a>
            <a href="?filter=Đã giao"       class="filter-tab <?= $filter==='Đã giao'     ?'active':'' ?>">✅ Đã giao</a>
            <a href="?filter=Đã hủy"        class="filter-tab <?= $filter==='Đã hủy'      ?'active':'' ?>">❌ Đã hủy</a>
        </div>
    </div>

    <!-- TABLE -->
    <div class="table-wrap">
        <?php if(empty($donHangs)): ?>
            <div class="empty">
                <div class="empty-icon">📭</div>
                <div>Không có đơn hàng nào</div>
            </div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Mã ĐH</th>
                    <th>Khách hàng</th>
                    <th>Địa chỉ</th>
                    <th>Thanh toán</th>
                    <th>Tổng tiền</th>
                    <th>Ngày đặt</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($donHangs as $dh): ?>
                <?php
                    $tt = $dh['TrangThai'] ?? '';
                    $badgeClass = 'badge-cho';
                    if (str_contains($tt, 'Đang giao')) $badgeClass = 'badge-dang';
                    elseif (str_contains($tt, 'Đã giao')) $badgeClass = 'badge-da';
                    elseif (str_contains($tt, 'hủy'))     $badgeClass = 'badge-huy';

                    $payClass = 'pay-cod';
                    if ($dh['ThanhToan'] === 'CK')   $payClass = 'pay-ck';
                    if ($dh['ThanhToan'] === 'MOMO')  $payClass = 'pay-momo';

                    $payLabel = ['COD'=>'💵 COD','CK'=>'🏦 CK','MOMO'=>'💜 MoMo'];
                    $payText  = $payLabel[$dh['ThanhToan']] ?? $dh['ThanhToan'];
                ?>
                <tr>
                    <td><span class="order-id">#<?php echo str_pad($dh['MaDH'],6,'0',STR_PAD_LEFT); ?></span></td>
                    <td>
                        <div class="customer-name"><?php echo htmlspecialchars($dh['HoTen'] ?? 'N/A'); ?></div>
                        <div class="customer-phone"><?php echo htmlspecialchars($dh['SoDienThoai'] ?? ''); ?></div>
                    </td>
                    <td style="font-size:13px; color:var(--muted); max-width:180px;">
                        <?php echo htmlspecialchars(($dh['DiaChi'] ?? '') . (($dh['ThanhPho'] ?? '') ? ', '.$dh['ThanhPho'] : '')); ?>
                    </td>
                    <td><span class="pay-badge <?= $payClass ?>"><?= $payText ?></span></td>
                    <td><span class="amount"><?php echo number_format($dh['TongTien'], 0, ',', '.'); ?>đ</span></td>
                    <td><span class="date"><?php echo $dh['NgayDat']; ?></span></td>
                    <td><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($tt) ?></span></td>
                    <td>
                        <button class="btn-detail" onclick="moModal(<?php echo htmlspecialchars(json_encode($dh)); ?>)">
                            Chi tiết
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>


<!-- ===== MODAL CHI TIẾT ===== -->
<div class="modal-overlay" id="modalChiTiet">
    <div class="modal">
        <div class="modal-header">
            <div>
                <div class="modal-title">📋 CHI TIẾT ĐƠN HÀNG</div>
                <div class="modal-order-id" id="m-order-id"></div>
            </div>
            <button class="modal-close" onclick="dongModal()">✕</button>
        </div>
        <div class="modal-body">

            <!-- Thông tin khách -->
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-lbl">Người nhận</div>
                    <div class="info-val" id="m-hoten"></div>
                </div>
                <div class="info-item">
                    <div class="info-lbl">Số điện thoại</div>
                    <div class="info-val" id="m-sdt"></div>
                </div>
                <div class="info-item">
                    <div class="info-lbl">Email</div>
                    <div class="info-val" id="m-email"></div>
                </div>
                <div class="info-item">
                    <div class="info-lbl">Thanh toán</div>
                    <div class="info-val" id="m-thanhtoan"></div>
                </div>
                <div class="info-item full">
                    <div class="info-lbl">Địa chỉ giao hàng</div>
                    <div class="info-val" id="m-diachi"></div>
                </div>
                <div class="info-item full">
                    <div class="info-lbl">Ghi chú</div>
                    <div class="info-val" id="m-ghichu" style="color:var(--muted); font-style:italic;"></div>
                </div>
            </div>

            <!-- Sản phẩm -->
            <div class="products-title">SẢN PHẨM TRONG ĐƠN</div>
            <div class="product-list" id="m-products">
                <div style="padding:20px; text-align:center; color:var(--muted);">Đang tải...</div>
            </div>

            <!-- Tổng tiền -->
            <div class="modal-total">
                <span class="modal-total-lbl">TỔNG THANH TOÁN</span>
                <span class="modal-total-val" id="m-tongtien"></span>
            </div>

            <!-- Cập nhật trạng thái -->
            <form method="POST" action="quan_ly_don_hang.php">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="maDH" id="m-maDH">

                <div class="update-section">
                    <div class="update-title">CẬP NHẬT TRẠNG THÁI</div>
                    <div class="status-options">
                        <label class="status-opt" id="opt-cho" onclick="chonTrangThai(this,'Chờ xử lý')">
                            <input type="radio" name="trangThai" value="Chờ xử lý">
                            <div class="s-icon">⏳</div>
                            <div class="s-lbl">Chờ xử lý</div>
                        </label>
                        <label class="status-opt" id="opt-dang" onclick="chonTrangThai(this,'Đang giao')">
                            <input type="radio" name="trangThai" value="Đang giao">
                            <div class="s-icon">🚚</div>
                            <div class="s-lbl">Đang giao</div>
                        </label>
                        <label class="status-opt" id="opt-da" onclick="chonTrangThai(this,'Đã giao')">
                            <input type="radio" name="trangThai" value="Đã giao">
                            <div class="s-icon">✅</div>
                            <div class="s-lbl">Đã giao</div>
                        </label>
                        <label class="status-opt" id="opt-huy" onclick="chonTrangThai(this,'Đã hủy')">
                            <input type="radio" name="trangThai" value="Đã hủy">
                            <div class="s-icon">❌</div>
                            <div class="s-lbl">Đã hủy</div>
                        </label>
                    </div>
                    <button type="submit" class="btn-update">⚡ XÁC NHẬN CẬP NHẬT</button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
// Lấy chi tiết sản phẩm qua AJAX
function moModal(dh) {
    document.getElementById('modalChiTiet').style.display = 'flex';
    document.getElementById('modalChiTiet').classList.add('active');
    document.body.style.overflow = 'hidden';

    const pad = n => String(n).padStart(6,'0');
    document.getElementById('m-order-id').textContent  = 'Mã đơn: #' + pad(dh.MaDH);
    document.getElementById('m-maDH').value            = dh.MaDH;
    document.getElementById('m-hoten').textContent     = dh.HoTen   || 'N/A';
    document.getElementById('m-sdt').textContent       = dh.SoDienThoai || 'N/A';
    document.getElementById('m-email').textContent     = dh.Email   || 'N/A';
    document.getElementById('m-diachi').textContent    = [dh.DiaChi, dh.ThanhPho].filter(Boolean).join(', ') || 'N/A';
    document.getElementById('m-ghichu').textContent    = dh.GhiChu  || 'Không có ghi chú';
    document.getElementById('m-tongtien').textContent  = Number(dh.TongTien).toLocaleString('vi-VN') + 'đ';

    const payMap = { COD: '💵 Tiền mặt (COD)', CK: '🏦 Chuyển khoản', MOMO: '💜 Ví MoMo' };
    document.getElementById('m-thanhtoan').textContent = payMap[dh.ThanhToan] || dh.ThanhToan || 'N/A';

    // Set trạng thái hiện tại
    document.querySelectorAll('.status-opt').forEach(o => {
        o.className = 'status-opt';
        o.querySelector('input').checked = false;
    });
    const tt = dh.TrangThai || '';
    if (tt.includes('Chờ'))       setActive('opt-cho',  'cho');
    else if (tt.includes('giao') && tt.includes('Đang')) setActive('opt-dang', 'dang');
    else if (tt.includes('Đã giao')) setActive('opt-da', 'da');
    else if (tt.includes('hủy'))   setActive('opt-huy',  'huy');

    // Load sản phẩm
    document.getElementById('m-products').innerHTML = '<div style="padding:16px; text-align:center; color:var(--muted);">Đang tải...</div>';
    fetch('get_chi_tiet_don.php?maDH=' + dh.MaDH)
        .then(r => r.json())
        .then(items => {
            if (!items.length) {
                document.getElementById('m-products').innerHTML = '<div style="padding:16px; color:var(--muted); text-align:center;">Không có sản phẩm</div>';
                return;
            }
            document.getElementById('m-products').innerHTML = items.map(sp => `
                <div class="product-item">
                    <div>
                        <div class="product-name">${sp.TenSP}</div>
                        <div class="product-qty">SL: ${sp.SoLuong} × ${Number(sp.DonGia).toLocaleString('vi-VN')}đ</div>
                    </div>
                    <div class="product-price">${Number(sp.SoLuong * sp.DonGia).toLocaleString('vi-VN')}đ</div>
                </div>
            `).join('');
        })
        .catch(() => {
            document.getElementById('m-products').innerHTML = '<div style="padding:16px; color:var(--muted); text-align:center;">Không thể tải sản phẩm</div>';
        });
}

function setActive(id, cls) {
    const el = document.getElementById(id);
    if (!el) return;
    el.classList.add('selected-' + cls);
    el.querySelector('input').checked = true;
}

function chonTrangThai(el, val) {
    document.querySelectorAll('.status-opt').forEach(o => {
        o.className = 'status-opt';
        o.querySelector('input').checked = false;
    });
    const clsMap = {'Chờ xử lý':'cho','Đang giao':'dang','Đã giao':'da','Đã hủy':'huy'};
    el.classList.add('selected-' + (clsMap[val] || 'cho'));
    el.querySelector('input').checked = true;
}

function dongModal() {
    document.getElementById('modalChiTiet').style.display = 'none';
    document.getElementById('modalChiTiet').classList.remove('active');
    document.body.style.overflow = '';
}

document.getElementById('modalChiTiet').addEventListener('click', function(e) {
    if (e.target === this) dongModal();
});
document.addEventListener('keydown', e => { if (e.key === 'Escape') dongModal(); });
</script>

</body>
</html>
