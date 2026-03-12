<?php
session_start();
if (!isset($_SESSION['MaND'])) { header('Location: DangNhap.php'); exit; }
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi Tiết Giỏ Hàng</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Exo+2:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Exo 2', sans-serif; background: #050d1a; color: #e2eaf5; padding: 40px; }
        .cart-container { max-width: 900px; margin: 0 auto; background: #0d1f38; border: 1px solid rgba(0,229,255,0.2); border-radius: 12px; padding: 30px; }
        h2 { font-family: 'Orbitron', sans-serif; color: #00e5ff; text-align: center; margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th, td { padding: 15px; border-bottom: 1px solid rgba(0,229,255,0.1); text-align: left; }
        th { color: #7a92b0; text-transform: uppercase; font-size: 13px; }
        .total-row { font-size: 20px; font-weight: bold; color: #22c55e; text-align: right; padding: 10px 0; }
        .btn-group { display: flex; justify-content: space-between; margin-top: 20px; }
        .btn { padding: 12px 25px; border-radius: 8px; border: none; font-weight: bold; cursor: pointer; text-decoration: none; color: white; display: inline-block; }
        .btn-back { background: #334155; }
        .btn-checkout { background: linear-gradient(135deg, #22c55e, #16a34a); font-size: 16px; }
        .btn-checkout:hover { background: linear-gradient(135deg, #16a34a, #15803d); }
        .empty-cart { text-align: center; padding: 50px; color: #7a92b0; }

        /* ===== MODAL OVERLAY ===== */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.75);
            backdrop-filter: blur(4px);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        .modal-overlay.active { display: flex !important; }

        .modal {
            background: #0d1f38;
            border: 1px solid rgba(0,229,255,0.3);
            border-radius: 16px;
            padding: 35px;
            width: 100%;
            max-width: 520px;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            animation: slideIn 0.25s ease;
        }
        @keyframes slideIn {
            from { transform: translateY(-30px); opacity: 0; }
            to   { transform: translateY(0);    opacity: 1; }
        }

        .modal h3 {
            font-family: 'Orbitron', sans-serif;
            color: #00e5ff;
            font-size: 18px;
            margin-bottom: 25px;
            text-align: center;
        }

        .modal-close {
            position: absolute;
            top: 15px; right: 20px;
            background: none; border: none;
            color: #7a92b0; font-size: 22px;
            cursor: pointer; line-height: 1;
        }
        .modal-close:hover { color: #e2eaf5; }

        /* ===== FORM STYLES ===== */
        .form-group { margin-bottom: 18px; }
        .form-group label {
            display: block;
            color: #7a92b0;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 7px;
        }
        .form-group label span { color: #f87171; }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 11px 14px;
            background: #0a1628;
            border: 1px solid rgba(0,229,255,0.2);
            border-radius: 8px;
            color: #e2eaf5;
            font-family: 'Exo 2', sans-serif;
            font-size: 15px;
            transition: border-color 0.2s;
            outline: none;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #00e5ff;
            box-shadow: 0 0 0 3px rgba(0,229,255,0.08);
        }
        .form-group textarea { resize: vertical; min-height: 80px; }
        .form-group select option { background: #0d1f38; }

        /* Payment options */
        .payment-options { display: flex; gap: 12px; flex-wrap: wrap; }
        .payment-option {
            flex: 1; min-width: 130px;
            background: #0a1628;
            border: 2px solid rgba(0,229,255,0.15);
            border-radius: 10px;
            padding: 12px;
            cursor: pointer;
            text-align: center;
            transition: all 0.2s;
        }
        .payment-option:hover { border-color: rgba(0,229,255,0.4); }
        .payment-option input[type="radio"] { display: none; }
        .payment-option.selected {
            border-color: #00e5ff;
            background: rgba(0,229,255,0.07);
        }
        .payment-option .icon { font-size: 24px; margin-bottom: 5px; }
        .payment-option .label { font-size: 13px; font-weight: 600; color: #b0c4de; }

        /* Order summary in modal */
        .order-summary {
            background: #0a1628;
            border: 1px solid rgba(0,229,255,0.1);
            border-radius: 10px;
            padding: 14px 16px;
            margin-bottom: 22px;
            font-size: 14px;
            color: #7a92b0;
        }
        .order-summary .summary-total {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid rgba(0,229,255,0.1);
            color: #22c55e;
            font-weight: bold;
            font-size: 16px;
        }

        /* Submit button */
        .btn-submit-order {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #22c55e, #16a34a);
            border: none;
            border-radius: 10px;
            color: white;
            font-family: 'Orbitron', sans-serif;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            letter-spacing: 1px;
            margin-top: 10px;
            transition: opacity 0.2s;
        }
        .btn-submit-order:hover { opacity: 0.9; }

        /* Error messages */
        .error-msg { color: #f87171; font-size: 12px; margin-top: 4px; display: none; }
    </style>
</head>
<body>

<div class="cart-container">
    <h2>🛒 GIỎ HÀNG CỦA BẠN</h2>


    <?php if(isset($_SESSION['order_error'])): ?>
        <div style="background:#3b0a0a; border:1px solid #f87171; border-radius:10px; padding:14px 18px; margin-bottom:20px; color:#f87171; font-weight:600;">
            ⚠️ <?php echo $_SESSION['order_error']; unset($_SESSION['order_error']); ?>
        </div>
        <script>window.addEventListener('DOMContentLoaded', () => moModalDatHang());</script>
    <?php endif; ?>

    <?php if(isset($_SESSION['giohang']) && count($_SESSION['giohang']) > 0): ?>
        <?php
        $tongTien = 0;
        foreach($_SESSION['giohang'] as $sp) {
            $tongTien += $sp['Gia'] * $sp['SoLuong'];
        }
        ?>
        <table>
            <thead>
                <tr>
                    <th>Tên Sản Phẩm</th>
                    <th>Đơn Giá</th>
                    <th>Số Lượng</th>
                    <th>Thành Tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($_SESSION['giohang'] as $maSP => $sp): 
                    $thanhTien = $sp['Gia'] * $sp['SoLuong'];
                ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($sp['TenSP']); ?></strong></td>
                    <td style="color: #00e5ff;"><?php echo number_format($sp['Gia'], 0, ',', '.'); ?>đ</td>
                    <td><?php echo $sp['SoLuong']; ?></td>
                    <td style="color: #a855f7; font-weight: bold;"><?php echo number_format($thanhTien, 0, ',', '.'); ?>đ</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="total-row">
            Tổng cộng: <?php echo number_format($tongTien, 0, ',', '.'); ?>đ
        </div>

        <div class="btn-group">
            <a href="TrangChuDaDangNhap.php" class="btn btn-back">← Tiếp tục mua sắm</a>
            <button type="button" class="btn btn-checkout" onclick="moModalDatHang()">
                ⚡ TIẾN HÀNH ĐẶT HÀNG
            </button>
        </div>

    <?php else: ?>
        <div class="empty-cart">
            <h3>Giỏ hàng của bạn đang trống!</h3>
            <p>Hãy quay lại trang chủ và chọn cho mình những món đồ công nghệ yêu thích nhé.</p>
            <br>
            <a href="TrangChuDaDangNhap.php" class="btn btn-checkout">Quay lại Cửa Hàng</a>
        </div>
    <?php endif; ?>
</div>


<!-- ===== MODAL ĐẶT HÀNG ===== -->
<?php if(isset($_SESSION['giohang']) && count($_SESSION['giohang']) > 0): ?>
<div class="modal-overlay" id="modalDatHang">
    <div class="modal">
        <button class="modal-close" onclick="dongModal()" title="Đóng">✕</button>
        <h3>📦 THÔNG TIN ĐẶT HÀNG</h3>

        <!-- Tóm tắt đơn hàng -->
        <div class="order-summary">
            <div style="color:#b0c4de; margin-bottom:6px; font-weight:600; font-size:13px;">TÓM TẮT ĐƠN HÀNG</div>
            <?php foreach($_SESSION['giohang'] as $sp): ?>
                <div style="display:flex; justify-content:space-between; padding: 3px 0;">
                    <span><?php echo htmlspecialchars($sp['TenSP']); ?> × <?php echo $sp['SoLuong']; ?></span>
                    <span style="color:#a855f7;"><?php echo number_format($sp['Gia'] * $sp['SoLuong'], 0, ',', '.'); ?>đ</span>
                </div>
            <?php endforeach; ?>
            <div class="summary-total">
                Tổng: <?php echo number_format($tongTien, 0, ',', '.'); ?>đ
            </div>
        </div>

        <!-- Form đặt hàng -->
        <form id="formDatHang" action="xu_ly_dat_hang.php" method="POST" onsubmit="return validateForm()">

            <div class="form-group">
                <label>Họ và tên <span>*</span></label>
                <input type="text" name="hoTen" id="hoTen" placeholder="Nguyễn Văn A"
                    value="<?php echo htmlspecialchars($_SESSION['HoTen'] ?? ''); ?>">
                <div class="error-msg" id="err-hoTen">Vui lòng nhập họ tên.</div>
            </div>

            <div class="form-group">
                <label>Số điện thoại <span>*</span></label>
                <input type="tel" name="soDienThoai" id="soDienThoai" placeholder="0912 345 678"
                    value="<?php echo htmlspecialchars($_SESSION['SDT'] ?? ''); ?>">
                <div class="error-msg" id="err-sdt">Vui lòng nhập số điện thoại hợp lệ (10 số).</div>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" placeholder="example@email.com"
                    value="<?php echo htmlspecialchars($_SESSION['Email'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label>Địa chỉ giao hàng <span>*</span></label>
                <input type="text" name="diaChi" id="diaChi" placeholder="Số nhà, tên đường, phường/xã">
                <div class="error-msg" id="err-diaChi">Vui lòng nhập địa chỉ giao hàng.</div>
            </div>

            <div class="form-group">
                <label>Tỉnh / Thành phố <span>*</span></label>
                <select name="thanhPho" id="thanhPho">
                    <option value="">-- Chọn tỉnh/thành phố --</option>
                    <option>Hà Nội</option>
                    <option>TP. Hồ Chí Minh</option>
                    <option>Đà Nẵng</option>
                    <option>Cần Thơ</option>
                    <option>Hải Phòng</option>
                    <option>Bình Dương</option>
                    <option>Đồng Nai</option>
                    <option>An Giang</option>
                    <option>Khác</option>
                </select>
                <div class="error-msg" id="err-thanhPho">Vui lòng chọn tỉnh/thành phố.</div>
            </div>

            <!-- Phương thức thanh toán -->
            <div class="form-group">
                <label>Phương thức thanh toán <span>*</span></label>
                <div class="payment-options">
                    <label class="payment-option selected" onclick="chonThanhToan(this)">
                        <input type="radio" name="thanhToan" value="COD" checked>
                        <div class="icon">💵</div>
                        <div class="label">Tiền mặt (COD)</div>
                    </label>
                    <label class="payment-option" onclick="chonThanhToan(this)">
                        <input type="radio" name="thanhToan" value="CK">
                        <div class="icon">🏦</div>
                        <div class="label">Chuyển khoản</div>
                    </label>
                    <label class="payment-option" onclick="chonThanhToan(this)">
                        <input type="radio" name="thanhToan" value="MOMO">
                        <div class="icon">💜</div>
                        <div class="label">Ví MoMo</div>
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label>Ghi chú đơn hàng</label>
                <textarea name="ghiChu" placeholder="Yêu cầu đặc biệt, thời gian giao hàng..."></textarea>
            </div>

            <input type="hidden" name="tongTien" value="<?php echo $tongTien; ?>">

            <button type="submit" class="btn-submit-order">✅ XÁC NHẬN ĐẶT HÀNG</button>
        </form>
    </div>
</div>

<script>
function moModalDatHang() {
    var modal = document.getElementById('modalDatHang');
    if (!modal) { alert('Không tìm thấy modal!'); return; }
    modal.style.display = 'flex';
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function dongModal() {
    var modal = document.getElementById('modalDatHang');
    if (!modal) return;
    modal.style.display = 'none';
    modal.classList.remove('active');
    document.body.style.overflow = '';
}

// Đóng modal khi click bên ngoài
document.getElementById('modalDatHang').addEventListener('click', function(e) {
    if (e.target === this) dongModal();
});

// Chọn phương thức thanh toán
function chonThanhToan(el) {
    document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');
}

// Validate form
function validateForm() {
    let valid = true;

    // Reset errors
    document.querySelectorAll('.error-msg').forEach(e => e.style.display = 'none');

    const hoTen = document.getElementById('hoTen').value.trim();
    if (!hoTen) {
        document.getElementById('err-hoTen').style.display = 'block';
        valid = false;
    }

    const sdt = document.getElementById('soDienThoai').value.trim();
    if (!/^(0|\+84)[0-9]{9}$/.test(sdt)) {
        document.getElementById('err-sdt').style.display = 'block';
        valid = false;
    }

    const diaChi = document.getElementById('diaChi').value.trim();
    if (!diaChi) {
        document.getElementById('err-diaChi').style.display = 'block';
        valid = false;
    }

    const thanhPho = document.getElementById('thanhPho').value;
    if (!thanhPho) {
        document.getElementById('err-thanhPho').style.display = 'block';
        valid = false;
    }

    return valid;
}

// Phím ESC đóng modal
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') dongModal();
});
</script>
<?php endif; ?>

</body>
</html>