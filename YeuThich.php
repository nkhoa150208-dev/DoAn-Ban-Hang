<?php
session_start();
$serverName = "localhost\\SQLEXPRESS";
$connectionInfo = ["Database"=>"QLBanHang","TrustServerCertificate"=>true,"CharacterSet"=>"UTF-8"];
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) die(print_r(sqlsrv_errors(), true));

if (!isset($_SESSION['MaND'])) { header('Location: DangNhap.php'); exit; }
$user_id = (int)$_SESSION['MaND'];

// Lấy thông tin user (để hiện thanh menu trái)
$res_u  = sqlsrv_query($conn,"SELECT * FROM dbo.NguoiDung WHERE MaND=?",[$user_id]);
$user = sqlsrv_fetch_array($res_u, SQLSRV_FETCH_ASSOC);

// Truy vấn lấy danh sách sản phẩm yêu thích
$sql_yt = "SELECT yt.MaYT, sp.* FROM YeuThich yt 
           JOIN SanPham sp ON yt.MaSP = sp.MaSP 
           WHERE yt.MaND = ? ORDER BY yt.NgayThem DESC";
$stmt_yt = sqlsrv_query($conn, $sql_yt, [$user_id]);
$hasFav = false;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@300;400;600;700;900&family=Orbitron:wght@400;700;900&display=swap" rel="stylesheet">
<title>Sản Phẩm Yêu Thích - TechVN</title>
<style>
/* CSS CHUẨN CỦA BẠN */
:root { --navy: #050d1a; --navy2: #071223; --panel: #0d1f38; --panel2: #0f2444; --cyan: #00e5ff; --purple2: #a855f7; --green: #22c55e; --tx: #e2eaf5; --muted: #7a92b0; --border: rgba(0,229,255,0.12); --r: 14px; }
*,*::before,*::after { box-sizing:border-box; margin:0; padding:0; }
body { font-family: 'Exo 2', sans-serif; background: var(--navy); color: var(--tx); min-height: 100vh; padding: 24px 16px 60px; }
a { text-decoration: none; }

.topbar { max-width: 980px; margin: 0 auto 28px; display: flex; align-items: center; gap: 12px; background: rgba(5,13,26,0.92); border: 1px solid var(--border); border-radius: var(--r); padding: 12px 20px; }
.logo { font-family: 'Orbitron', monospace; font-size: 18px; font-weight: 900; color: var(--cyan); }
.lay { max-width: 980px; margin: 0 auto; display: grid; grid-template-columns: 260px 1fr; gap: 20px; }
.card { background: var(--panel); border: 1px solid var(--border); border-radius: var(--r); padding: 24px; box-shadow: 0 8px 32px rgba(0,0,0,.5); }

/* Sidebar */
.sb { display: flex; flex-direction: column; gap: 20px; }
.snav { display: flex; flex-direction: column; gap: 4px; }
.ni { padding: 10px 12px; border-radius: 10px; font-size: 14px; color: var(--muted); transition: .15s; border: 1px solid transparent; }
.ni:hover { background: rgba(0,229,255,0.06); color: var(--cyan); }
.ni.act { background: rgba(0,229,255,0.1); color: var(--cyan); border-color: rgba(0,229,255,0.3); font-weight: 600; box-shadow: 0 0 15px rgba(0,229,255,0.08); }

/* Favorite Grid */
.st { font-family: 'Orbitron', monospace; font-size: 16px; font-weight: 700; margin-bottom: 20px; color: var(--cyan); display: flex; align-items: center; gap: 8px; }
.st::after { content: ''; flex: 1; height: 1px; background: linear-gradient(90deg, rgba(0,229,255,0.4), transparent); }

.grid-yt { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; }
.sp-card { background: var(--panel2); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; transition: 0.3s; position: relative; }
.sp-card:hover { border-color: #ef4444; box-shadow: 0 5px 15px rgba(239,68,68,0.2); transform: translateY(-5px); }
.sp-img { height: 150px; background: var(--navy); display: flex; align-items: center; justify-content: center; font-size: 50px; }
.sp-info { padding: 15px; }
.sp-name { font-size: 14px; font-weight: bold; color: var(--tx); margin-bottom: 5px; line-height: 1.3; }
.sp-price { color: var(--cyan); font-weight: bold; font-family: 'Orbitron', sans-serif; font-size: 15px; margin-bottom: 10px; }

/* Nút xóa */
.btn-del { position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.6); color: #ef4444; border: none; border-radius: 50%; width: 30px; height: 30px; font-size: 14px; cursor: pointer; transition: 0.2s; }
.btn-del:hover { background: #ef4444; color: white; }
</style>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<div class="topbar">
  <a href="TrangChuDaDangNhap.php" class="logo">&#x1F6CD; KhoaOngNghiem Tech</a>
  <a href="TrangChuDaDangNhap.php" style="margin-left:auto; color:var(--cyan);">&#x2190; Về Trang Chủ</a>
</div>

<div class="lay">
  <aside class="sb">
    <div class="card">
      <nav class="snav">
        <a href="ChinhSuaProfile.php" class="ni">👤 Hồ sơ cá nhân</a>
        <?php if ($user['VaiTro'] == 0): ?>
            <a href="DonHang.php" class="ni">📦 Đơn hàng của tôi</a>
            <a href="YeuThich.php" class="ni act">❤️ Sản phẩm yêu thích</a>
            <a href="diachigiaohang.php" class="ni">🏠 Địa chỉ giao hàng</a>
        <?php endif; ?>
        <?php if ($user['VaiTro'] == 1): ?>
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
      <div class="st">❤️ SẢN PHẨM BẠN ĐÃ THẢ TIM</div>
      
      <div class="grid-yt">
        <?php while ($sp = sqlsrv_fetch_array($stmt_yt, SQLSRV_FETCH_ASSOC)): 
            $hasFav = true;
        ?>
            <div class="sp-card" id="fav-<?= $sp['MaSP'] ?>">
                <button class="btn-del" title="Bỏ yêu thích" onclick="removeFav(<?= $sp['MaSP'] ?>)">✖</button>
                
                <a href="ChiTietSanPham.php?id=<?= $sp['MaSP'] ?>" style="display:block; text-decoration:none;">
                    <div class="sp-img">
                        <?= (!empty($sp['HinhAnh'])) ? "<img src='".$sp['HinhAnh']."' style='max-width:100%; max-height:100%; object-fit:contain;'>" : (($sp['MaDM']==1)?'💻':'📱') ?>
                    </div>
                    <div class="sp-info">
                        <div class="sp-name"><?= htmlspecialchars($sp['TenSP']) ?></div>
                        <div class="sp-price"><?= number_format($sp['Gia'], 0, ',', '.') ?> đ</div>
                    </div>
                </a>
            </div>
        <?php endwhile; ?>
      </div>

      <?php if(!$hasFav): ?>
        <div style="text-align:center; padding: 50px; color: var(--muted);">
            <div style="font-size: 50px; margin-bottom: 10px;">🤍</div>
            <h3>Danh sách yêu thích đang trống!</h3>
            <p>Hãy lướt trang chủ và thả tim cho sản phẩm bạn thích nhé.</p>
        </div>
      <?php endif; ?>
      
    </div>
  </main>
</div>

<script>
// Hàm xóa trực tiếp trên trang Yêu thích
function removeFav(maSP) {
    if(confirm("Bạn muốn bỏ thả tim sản phẩm này?")) {
        $.ajax({
            url: 'xu_ly_yeu_thich.php',
            type: 'POST',
            data: { id_sanpham: maSP },
            success: function(res) {
                if(res === 'removed') {
                    // Hiệu ứng mờ dần rồi xóa cục sản phẩm đó khỏi màn hình
                    $('#fav-' + maSP).fadeOut(300, function() { $(this).remove(); });
                }
            }
        });
    }
}
</script>

</body>
</html>