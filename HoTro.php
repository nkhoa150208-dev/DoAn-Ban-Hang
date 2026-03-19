<?php
session_start();
// Lấy tab mặc định nếu người dùng bấm từ Footer truyền lên (VD: HoTro.php?tab=faq)
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'baohanh';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Hỗ Trợ Khách Hàng - KON TechVN</title>
<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Exo+2:wght@400;600;700&display=swap" rel="stylesheet">
<style>
    :root { --navy:#050d1a; --navy2:#071223; --panel:#0d1f38; --panel2:#0f2444; --cyan:#00e5ff; --purple:#a855f7; --green:#22c55e; --tx:#e2eaf5; --muted:#7a92b0; --border:rgba(0,229,255,0.15); }
    *,*::before,*::after { box-sizing:border-box; margin:0; padding:0; }
    body { font-family: 'Exo 2', sans-serif; background: var(--navy); color: var(--tx); line-height: 1.6; padding: 40px 20px; }
    
    .container { max-width: 1100px; margin: 0 auto; display: grid; grid-template-columns: 280px 1fr; gap: 30px; }
    @media (max-width: 768px) { .container { grid-template-columns: 1fr; } }
    
    .card { background: var(--panel); border: 1px solid var(--border); border-radius: 12px; padding: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
    .card-title { font-family: 'Orbitron', monospace; font-size: 18px; color: var(--cyan); margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
    .card-title::after { content: ''; flex: 1; height: 1px; background: linear-gradient(90deg, rgba(0,229,255,0.4), transparent); }
    
    /* MENU TRÁI */
    .menu-hotro { display: flex; flex-direction: column; gap: 8px; }
    .menu-item { background: var(--panel2); color: var(--muted); padding: 14px 20px; border-radius: 8px; cursor: pointer; border: 1px solid transparent; transition: 0.3s; font-weight: 600; display: flex; align-items: center; gap: 10px; }
    .menu-item:hover { background: rgba(0,229,255,0.05); color: var(--cyan); }
    .menu-item.active { background: rgba(0,229,255,0.1); color: var(--cyan); border-color: rgba(0,229,255,0.3); border-left: 4px solid var(--cyan); }
    
    /* NỘI DUNG PHẢI */
    .tab-content { display: none; animation: fadeIn 0.4s ease; }
    .tab-content.active { display: block; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    
    h3 { color: #fff; margin: 25px 0 10px 0; font-size: 18px; }
    h3:first-child { margin-top: 0; }
    p { margin-bottom: 15px; color: #b0c4de; text-align: justify; }
    ul { margin-left: 20px; margin-bottom: 20px; color: #b0c4de; }
    li { margin-bottom: 8px; }
    strong { color: var(--cyan); }
    
    /* FORM LIÊN HỆ */
    .contact-form input, .contact-form textarea { width: 100%; background: var(--navy2); border: 1px solid var(--border); border-radius: 8px; padding: 12px 15px; color: #fff; font-family: 'Exo 2'; outline: none; margin-bottom: 15px; }
    .contact-form input:focus, .contact-form textarea:focus { border-color: var(--cyan); box-shadow: 0 0 10px rgba(0,229,255,0.2); }
    .btn-submit { background: linear-gradient(135deg, var(--cyan), #0088cc); color: #000; font-weight: bold; border: none; padding: 12px 25px; border-radius: 8px; cursor: pointer; text-transform: uppercase; letter-spacing: 1px; transition: 0.3s; }
    .btn-submit:hover { box-shadow: 0 0 15px rgba(0,229,255,0.6); transform: translateY(-2px); }
    
    .btn-home { display: inline-block; margin-bottom: 20px; color: var(--muted); text-decoration: none; border: 1px solid var(--muted); padding: 5px 15px; border-radius: 20px; font-size: 14px; transition: 0.3s; }
    .btn-home:hover { color: var(--cyan); border-color: var(--cyan); }
</style>
</head>
<body>

<div class="container" style="max-width: 1100px; display: block;">
    <a href="TrangChuDaDangNhap.php" class="btn-home">&#x2190; Quay lại Trang Chủ</a>
</div>

<div class="container">
    <div class="card" style="height: fit-content;">
        <div class="card-title">Danh Mục Hỗ Trợ</div>
        <div class="menu-hotro">
            <div class="menu-item <?= $activeTab=='baohanh'?'active':'' ?>" onclick="switchTab('baohanh', this)">🛡️ Chính Sách Bảo Hành</div>
            <div class="menu-item <?= $activeTab=='doitra'?'active':'' ?>" onclick="switchTab('doitra', this)">🔄 Đổi Trả Hàng</div>
            <div class="menu-item <?= $activeTab=='huongdan'?'active':'' ?>" onclick="switchTab('huongdan', this)">🛒 Hướng Dẫn Mua</div>
            <div class="menu-item <?= $activeTab=='faq'?'active':'' ?>" onclick="switchTab('faq', this)">❓ Câu Hỏi Thường Gặp (FAQ)</div>
            <div class="menu-item <?= $activeTab=='lienhe'?'active':'' ?>" onclick="switchTab('lienhe', this)">📞 Liên Hệ Chúng Tôi</div>
        </div>
    </div>

    <div class="card">
        
        <div id="tab-baohanh" class="tab-content <?= $activeTab=='baohanh'?'active':'' ?>">
            <div class="card-title">CHÍNH SÁCH BẢO HÀNH CHUYÊN NGHIỆP</div>
            <p>Chào mừng bạn đến với trung tâm bảo hành của <strong>KON TechVN</strong>. Chúng tôi cam kết mang đến dịch vụ hậu mãi tốt nhất, đảm bảo quyền lợi tối đa cho mọi sản phẩm công nghệ bạn đã mua.</p>
            
            <h3>1. Thời Gian Bảo Hành</h3>
            <ul>
                <li><strong>PC Gaming & Laptop:</strong> Bảo hành chính hãng từ 12 đến 36 tháng tùy theo nhà sản xuất (Dell, Asus, MSI, v.v.).</li>
                <li><strong>Điện Thoại thông minh:</strong> Bảo hành 12 tháng phần cứng, hỗ trợ phần mềm trọn đời máy.</li>
                <li><strong>Gaming Gear & Phụ Kiện:</strong> Bảo hành 1 đổi 1 trong vòng 6 đến 12 tháng đối với lỗi từ nhà sản xuất.</li>
            </ul>

            <h3>2. Điều Kiện Được Bảo Hành Miễn Phí</h3>
            <ul>
                <li>Sản phẩm còn trong thời hạn bảo hành (tính từ ngày in trên hóa đơn hoặc hệ thống điện tử).</li>
                <li>Sản phẩm bị lỗi kỹ thuật do nhà sản xuất (hỏng mainboard, liệt phím tự nhiên, chết điểm ảnh màn hình quá quy định...).</li>
                <li>Tem bảo hành, mã vạch, số Serial/IMEI trên sản phẩm phải còn nguyên vẹn, không bị rách rời, chắp vá hay cạo sửa.</li>
            </ul>

            <h3>3. Trường Hợp TỪ CHỐI Bảo Hành</h3>
            <ul>
                <li>Sản phẩm hết hạn bảo hành.</li>
                <li>Thiết bị có dấu hiệu bị vào nước, chập cháy do sử dụng sai nguồn điện, hoặc thiên tai, hỏa hoạn.</li>
                <li>Hư hỏng vật lý: Rơi vỡ, móp méo, trầy xước nặng, đứt cáp, gãy chân cắm do người dùng tác động.</li>
                <li>Sản phẩm đã bị tự ý tháo dỡ, sửa chữa bởi các cá nhân/đơn vị không thuộc ủy quyền của KON TechVN.</li>
            </ul>
        </div>

        <div id="tab-doitra" class="tab-content <?= $activeTab=='doitra'?'active':'' ?>">
            <div class="card-title">QUY ĐỊNH ĐỔI TRẢ HÀNG & HOÀN TIỀN</div>
            <p>KON TechVN áp dụng chính sách <strong>"1 ĐỔI 1 TRONG 30 NGÀY"</strong> cực kỳ linh hoạt để khách hàng an tâm mua sắm, kể cả khi mua Online.</p>

            <h3>1. Điều kiện áp dụng Đổi / Trả</h3>
            <ul>
                <li><strong>Sản phẩm lỗi:</strong> Lỗi phần cứng phát sinh từ nhà sản xuất (không lên nguồn, lỗi màn hình, lỗi tản nhiệt...).</li>
                <li><strong>Hàng giao sai:</strong> Sai model, sai màu sắc, hoặc thiếu phụ kiện so với đơn đặt hàng ban đầu.</li>
                <li><strong>Tình trạng máy:</strong> Máy không bị trầy xước, móp méo, không dính tài khoản bảo mật (iCloud, Google, Mi Account...). Phải còn nguyên hộp (box) và đầy đủ phụ kiện, quà tặng kèm theo.</li>
            </ul>

            <h3>2. Quy định quay Video Unbox (Bắt buộc)</h3>
            <p>Để đảm bảo quyền lợi khi nhận hàng qua các đơn vị vận chuyển (GHTK, Viettel Post...), quý khách <strong>BẮT BUỘC PHẢI QUAY VIDEO LIỀN MẠCH</strong> quá trình rọc và mở kiện hàng. Nếu phát hiện hàng bị bể vỡ hoặc tráo đổi mà không có video unbox, KON TechVN xin phép từ chối hỗ trợ bồi thường.</p>

            <h3>3. Thời gian xử lý hoàn tiền</h3>
            <p>Trong trường hợp khách hàng muốn trả hàng hoàn tiền (do hết hàng đổi), KON TechVN sẽ tiến hành kiểm định máy trong 24h. Sau khi xác nhận đủ điều kiện, tiền sẽ được hoàn về tài khoản Ngân hàng / MoMo của quý khách từ <strong>1 đến 3 ngày làm việc</strong>.</p>
        </div>

        <div id="tab-huongdan" class="tab-content <?= $activeTab=='huongdan'?'active':'' ?>">
            <div class="card-title">HƯỚNG DẪN MUA HÀNG ONLINE</div>
            <p>Mua sắm đồ công nghệ chưa bao giờ dễ dàng và an toàn đến thế tại KON TechVN. Chỉ với vài thao tác cơ bản, sản phẩm sẽ được giao đến tận cửa nhà bạn.</p>

            <h3>Bước 1: Tìm kiếm sản phẩm</h3>
            <p>Sử dụng thanh tìm kiếm hoặc duyệt qua các danh mục (Laptop, Điện thoại, PC, Gear...) để tìm món đồ ưng ý. Bạn có thể đọc kỹ thông số cấu hình và đánh giá sản phẩm ngay trên web.</p>

            <h3>Bước 2: Thêm vào giỏ hàng</h3>
            <p>Chọn số lượng cần mua và bấm nút <strong>"Thêm vào giỏ"</strong> hoặc <strong>"Mua ngay"</strong>. Hệ thống sẽ lưu giữ sản phẩm cho bạn.</p>

            <h3>Bước 3: Đăng nhập & Thu thập mã giảm giá</h3>
            <p>Bạn cần Đăng nhập (bằng tài khoản thường, Facebook hoặc Google) để tiếp tục. Đừng quên ghé thăm mục "Ví Voucher" để thu thập các mã giảm giá hấp dẫn đang có sẵn nhé!</p>

            <h3>Bước 4: Đặt hàng & Thanh toán</h3>
            <ul>
                <li>Vào Giỏ hàng, kiểm tra lại số lượng và điền mã giảm giá (nếu có).</li>
                <li>Chọn hoặc thêm <strong>Địa chỉ giao hàng</strong> của bạn.</li>
                <li>Chọn Phương thức thanh toán: Bạn có thể chọn <strong>Thanh toán khi nhận hàng (COD)</strong> hoặc <strong>Quét mã QR MoMo/Ngân hàng</strong> tự động cực kỳ nhanh chóng.</li>
                <li>Bấm <strong>"Chốt Đơn"</strong> và đợi KON TechVN xác nhận, đóng gói gửi hàng thôi!</li>
            </ul>
        </div>

        <div id="tab-faq" class="tab-content <?= $activeTab=='faq'?'active':'' ?>">
            <div class="card-title">CÂU HỎI THƯỜNG GẶP (FAQ)</div>
            
            <h3>❓ 1. KON TechVN có giao hàng toàn quốc không? Chi phí thế nào?</h3>
            <p><strong>Trả lời:</strong> Có! Chúng tôi giao hàng 63 tỉnh thành toàn quốc. Cước phí tiêu chuẩn là 30.000đ. ĐẶC BIỆT: Miễn phí vận chuyển (Freeship) cho mọi đơn hàng có giá trị từ 2.000.000đ trở lên.</p>

            <h3>❓ 2. Tôi có được kiểm tra hàng trước khi thanh toán (COD) không?</h3>
            <p><strong>Trả lời:</strong> Chắc chắn rồi! Nhằm đảm bảo tính minh bạch, KON TechVN khuyến khích khách hàng đồng kiểm tra ngoại quan (xem hộp có móp méo, xem đúng màu máy) cùng shipper trước khi thanh toán. Tuy nhiên, quý khách không được bóc seal nilon của các sản phẩm Apple/Samsung khi chưa thanh toán.</p>

            <h3>❓ 3. Cửa hàng có hỗ trợ trả góp không?</h3>
            <p><strong>Trả lời:</strong> Hiện tại hệ thống Website đang nâng cấp tính năng trả góp qua thẻ tín dụng (Visa/Mastercard) với lãi suất 0%. Dự kiến tính năng sẽ ra mắt vào tháng tới. Nếu bạn muốn trả góp qua CCCD, vui lòng đến trực tiếp Showroom của chúng tôi.</p>

            <h3>❓ 4. Làm sao để tôi theo dõi tình trạng đơn hàng của mình?</h3>
            <p><strong>Trả lời:</strong> Rất đơn giản! Hãy đăng nhập vào tài khoản của bạn, nhấn vào "Hồ sơ cá nhân" -> mục <strong>"Đơn hàng của tôi"</strong>. Mọi trạng thái như (Chờ xử lý, Đang giao, Đã giao) sẽ được cập nhật liên tục (Real-time).</p>

            <h3>❓ 5. Tôi lỡ đặt nhầm hàng, làm sao để hủy đơn?</h3>
            <p><strong>Trả lời:</strong> Nếu đơn hàng đang ở trạng thái "Chờ xử lý", bạn có thể vào chi tiết đơn và bấm nút <strong>"Yêu cầu hủy"</strong>, nhớ ghi rõ lý do. Admin sẽ duyệt hủy cho bạn ngay lập tức.</p>
        </div>

        <div id="tab-lienhe" class="tab-content <?= $activeTab=='lienhe'?'active':'' ?>">
            <div class="card-title">LIÊN HỆ VỚI KON TECHVN</div>
            <p>Đội ngũ Chăm sóc khách hàng của chúng tôi luôn sẵn sàng hỗ trợ bạn 24/7. Hãy liên hệ với chúng tôi qua các kênh dưới đây hoặc gửi form trực tiếp.</p>

            <?php if(isset($_GET['success'])): ?>
            <div style="background: rgba(34,197,94,0.1); border: 1px solid #22c55e; color: #4ade80; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: bold;">
                ✅ Cảm ơn bạn! Chúng tôi đã nhận được tin nhắn và sẽ phản hồi qua Email sớm nhất.
            </div>
            <?php endif; ?>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
                <div style="background: var(--navy); padding: 20px; border-radius: 10px; border: 1px dashed var(--cyan);">
                    <h3 style="margin-top:0; color: var(--cyan);">📍 Trụ Sở Chính</h3>
                    <p>123 Nguyễn Huệ, Quận 1, TP. Hồ Chí Minh.</p>
                    <a href="https://www.google.com/maps/search/123+Nguyễn+Huệ,+Quận+1,+TP+HCM" target="_blank" class="btn-home" style="margin-top: 5px; display:inline-block; font-size: 12px;">🗺️ Xem trên Google Maps</a>
                    
                    <h3 style="color: var(--cyan);">📞 Hotline Hỗ Trợ</h3>
                    <p style="font-family: 'Orbitron'; font-size: 18px; font-weight: bold; color: #fff;">0585.246.973</p>
                    <p>Hoạt động từ 08:00 - 22:00 (Cả T7 & CN)</p>
                    
                    <h3 style="color: var(--cyan);">✉️ Email CSKH</h3>
                    <p>thanhdoan1012008@gmail.com</p>
                </div>

               <div class="contact-form">
                    <h3 style="margin-top:0;">Gửi Yêu Cầu Hỗ Trợ</h3>
                    
                    <form id="formLienHe">
                        <input type="text" name="Họ_Tên" placeholder="Họ và tên của bạn..." required>
                        <input type="email" name="Email" placeholder="Email liên hệ..." required>
                        <input type="text" name="Mã_Đơn_Hàng" placeholder="Mã đơn hàng (Nếu có)...">
                        <textarea name="Nội_Dung" rows="4" placeholder="Nhập nội dung bạn cần hỗ trợ..." required></textarea>
                        
                        <input type="hidden" name="_subject" value="[KON TechVN] Yêu cầu hỗ trợ mới từ khách hàng!">
                        <input type="hidden" name="_template" value="table">
                        <input type="hidden" name="_captcha" value="false">

                        <button type="submit" id="btnGui" class="btn-submit" style="width: 100%;">GỬI TIN NHẮN 🚀</button>
                    </form>
                </div>

                <script>
                document.getElementById('formLienHe').addEventListener('submit', function(e) {
                    e.preventDefault(); // Ngăn chặn chuyển trang mặc định của HTML

                    const btn = document.getElementById('btnGui');
                    const originalText = btn.innerHTML;
                    
                    // Đổi nút thành trạng thái đang gửi
                    btn.innerHTML = 'ĐANG GỬI... ⏳';
                    btn.style.opacity = '0.7';
                    btn.disabled = true;

                    // Lấy dữ liệu từ form
                    const formData = new FormData(this);

                    // Gửi ngầm qua API của FormSubmit
                    fetch('https://formsubmit.co/ajax/thanhdoan1012008@gmail.com', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('✅ Đã gửi tin nhắn thành công! Chúng tôi sẽ phản hồi qua Email sớm nhất.');
                            // Chuyển thẳng về trang chủ đã đăng nhập
                            window.location.href = 'TrangChuDaDangNhap.php'; 
                        } else {
                            alert('❌ Có lỗi xảy ra, vui lòng thử lại!');
                            btn.innerHTML = originalText;
                            btn.style.opacity = '1';
                            btn.disabled = false;
                        }
                    })
                    .catch(error => {
                        alert('❌ Không thể gửi. Vui lòng kiểm tra kết nối mạng!');
                        btn.innerHTML = originalText;
                        btn.style.opacity = '1';
                        btn.disabled = false;
                    });
                });
                </script>
            </div>
        </div>

    </div>
</div>

<script>
    function switchTab(tabId, element) {
        // Xóa active menu
        document.querySelectorAll('.menu-item').forEach(el => el.classList.remove('active'));
        // Thêm active menu hiện tại
        element.classList.add('active');
        
        // Ẩn tất cả nội dung
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        // Hiện nội dung tương ứng
        document.getElementById('tab-' + tabId).classList.add('active');
        
        // Cập nhật URL (để khi F5 không bị mất tab)
        window.history.replaceState(null, null, "?tab=" + tabId);
    }
</script>

</body>
</html>