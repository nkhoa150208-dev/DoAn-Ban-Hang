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
// Cập nhật thời gian hoạt động mới nhất của User vào Database
sqlsrv_query($conn, "UPDATE NguoiDung SET NgayHoatDong = GETDATE() WHERE MaND = ?", [$user_id]);
// 3. LẤY THÔNG TIN USER
$res  = sqlsrv_query($conn, "SELECT * FROM dbo.NguoiDung WHERE MaND=?", [$user_id]);
$user = $res ? sqlsrv_fetch_array($res, SQLSRV_FETCH_ASSOC) : null;
// NẾU KHÔNG TÌM THẤY TÀI KHOẢN HOẶC TÀI KHOẢN BỊ KHÓA (TrangThai = 0) -> ĐUỔI RA NGOÀI
if (!$user || $user['TrangThai'] == 0) { 
    session_destroy(); 
    echo "<script>alert('Tài khoản của bạn đã bị Admin khóa!'); window.location.href='DangNhap.php';</script>";
    exit; 
}
$success = isset($_GET['s']) ? $_GET['s'] : "";
$error = isset($_GET['e']) ? $_GET['e'] : "";
$sMsg = ""; 

if ($success === 'info') $sMsg = 'Cập nhật thông tin thành công!';
elseif ($success === 'avatar') $sMsg = 'Cập nhật ảnh đại diện thành công!';
elseif ($success === 'product') $sMsg = 'Thao tác sản phẩm thành công!';
elseif ($success === 'saved_coupon') $sMsg = 'Đã lưu mã giảm giá vào thẻ của bạn thành công!'; 
elseif ($success === 'mgg') $sMsg = 'Thao tác Mã Giảm Giá thành công!'; 
elseif ($success === 'donhang') $sMsg = 'Cập nhật trạng thái đơn hàng thành công!'; 
elseif ($success === 'nguoidung') $sMsg = 'Thao tác tài khoản khách hàng thành công!'; 
elseif ($success === 'add_address') $sMsg = 'Đã thêm địa chỉ giao hàng thành công!'; 
elseif ($success === 'delete_address') $sMsg = 'Đã xóa địa chỉ thành công!'; 
elseif ($success === 'set_default') $sMsg = 'Đã cập nhật địa chỉ mặc định!'; 
elseif ($success === 'yeu_cau_huy') $sMsg = 'Đã gửi yêu cầu hủy đơn. Vui lòng chờ Admin xác nhận!'; 
elseif ($success === 'yeu_cau_tra') $sMsg = 'Đã gửi yêu cầu đổi trả hàng thành công!';
elseif ($success === 'duyet_tra') $sMsg = 'Đã xác nhận hoàn tiền cho đơn hàng!';
elseif ($success === 'tu_choi_tra') $sMsg = 'Đã từ chối yêu cầu trả hàng!';

$vTxt = ((int)$user['VaiTro'] === 1) ? "Quản trị viên" : "Khách hàng";

$uploadPath = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'avatars';
if (!file_exists($uploadPath)) mkdir($uploadPath, 0777, true);

$uploadProdPath = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'products';
if (!file_exists($uploadProdPath)) mkdir($uploadProdPath, 0777, true);

// ==============================================================
// XỬ LÝ LỆNH GET (Cho Admin xóa/tắt bật mã)
// ==============================================================
if (isset($_GET['xoa_mgg']) && $user['VaiTro'] == 1) {
    sqlsrv_query($conn, "DELETE FROM MaGiamGia WHERE MaMGG=?", [(int)$_GET['xoa_mgg']]);
    header('Location: ChinhSuaProfile.php?s=mgg'); exit;
}
if (isset($_GET['toggle_mgg']) && $user['VaiTro'] == 1) {
    $tid = (int)$_GET['toggle_mgg'];
    $cur = sqlsrv_query($conn, "SELECT TrangThai FROM MaGiamGia WHERE MaMGG=?", [$tid]);
    if ($cur) {
        $row = sqlsrv_fetch_array($cur, SQLSRV_FETCH_ASSOC);
        $new = ($row['TrangThai'] == 1) ? 0 : 1;
        sqlsrv_query($conn, "UPDATE MaGiamGia SET TrangThai=? WHERE MaMGG=?", [$new,$tid]);
    }
    header('Location: ChinhSuaProfile.php?s=mgg'); exit;
}

// ==============================================================
// XỬ LÝ YÊU CẦU POST
// ==============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // --- CẬP NHẬT ẢNH ĐẠI DIỆN CHUNG ---
    if ($action === 'update_avatar') {
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
            $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $fileName = 'av_' . $user_id . '_' . time() . '.' . $ext;
            $destPath = $uploadPath . DIRECTORY_SEPARATOR . $fileName;
            $dbPath = 'uploads/avatars/' . $fileName;
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $destPath)) {
                sqlsrv_query($conn, "UPDATE dbo.NguoiDung SET Avatar = ? WHERE MaND = ?", [$dbPath, $user_id]);
                header('Location: ChinhSuaProfile.php?s=avatar'); exit;
            }
        }
        $error = "Không thể tải ảnh lên.";
    }

    // ================= KHÁCH HÀNG (VaiTro == 0) =================
    if ($user['VaiTro'] == 0) {
        if ($action === 'update_info') {
            $hoTen = $_POST['HoTen']; $email = $_POST['Email']; $sdt = $_POST['SoDienThoai']; $diaChi = $_POST['DiaChi'];
            sqlsrv_query($conn, "UPDATE NguoiDung SET HoTen=?, Email=?, SoDienThoai=?, DiaChi=? WHERE MaND=?", [$hoTen, $email, $sdt, $diaChi, $user_id]);
            header('Location: ChinhSuaProfile.php?s=info'); exit;
        }
        if ($action === 'save_coupon') {
            $maMGG = (int)$_POST['MaMGG'];
            $check = sqlsrv_query($conn, "SELECT MaVi FROM ViGiamGia WHERE MaND=? AND MaMGG=?", [$user_id, $maMGG]);
            if ($check && !sqlsrv_fetch_array($check)) { sqlsrv_query($conn, "INSERT INTO ViGiamGia (MaND, MaMGG) VALUES (?, ?)", [$user_id, $maMGG]); }
            header('Location: ChinhSuaProfile.php?s=saved_coupon'); exit;
        }
        if ($action === 'add_address') {
            $hoten = trim($_POST['HoTen'] ?? ''); $sdt = trim($_POST['SoDienThoai'] ?? '');
            $thanhpho = trim($_POST['ThanhPho'] ?? ''); $diachi = trim($_POST['DiaChi'] ?? '');
            $macdinh = isset($_POST['MacDinh']) ? 1 : 0;
            if ($macdinh == 1) sqlsrv_query($conn, "UPDATE SoDiaChi SET MacDinh = 0 WHERE MaND = ?", [$user_id]);
            else {
                $chk = sqlsrv_query($conn, "SELECT COUNT(*) as Cnt FROM SoDiaChi WHERE MaND = ?", [$user_id]);
                if (sqlsrv_fetch_array($chk, SQLSRV_FETCH_ASSOC)['Cnt'] == 0) $macdinh = 1;
            }
            sqlsrv_query($conn, "INSERT INTO SoDiaChi (MaND, HoTenNguoiNhan, SoDienThoai, ThanhPho, DiaChiCuThe, MacDinh) VALUES (?, ?, ?, ?, ?, ?)", [$user_id, $hoten, $sdt, $thanhpho, $diachi, $macdinh]);
            header('Location: ChinhSuaProfile.php?s=add_address'); exit;
        }
        if ($action === 'delete_address') {
            $madc = (int)$_POST['MaDC'];
            sqlsrv_query($conn, "DELETE FROM SoDiaChi WHERE MaDC = ? AND MaND = ?", [$madc, $user_id]);
            header('Location: ChinhSuaProfile.php?s=delete_address'); exit;
        }
        if ($action === 'set_default') {
            $madc = (int)$_POST['MaDC'];
            sqlsrv_query($conn, "UPDATE SoDiaChi SET MacDinh = 0 WHERE MaND = ?", [$user_id]);
            sqlsrv_query($conn, "UPDATE SoDiaChi SET MacDinh = 1 WHERE MaDC = ? AND MaND = ?", [$madc, $user_id]);
            header('Location: ChinhSuaProfile.php?s=set_default'); exit;
        }
        if ($action === 'yeu_cau_huy') {
            $maDH  = (int)($_POST['MaDH'] ?? 0); $lyDo  = trim($_POST['LyDoHuy'] ?? '');
            sqlsrv_query($conn, "UPDATE DonHang SET TrangThai=N'Chờ xác nhận hủy', LyDoHuy=? WHERE MaDH=? AND MaND=?", [$lyDo, $maDH, $user_id]);
            header('Location: ChinhSuaProfile.php?s=yeu_cau_huy'); exit;
        }
        if ($action === 'yeu_cau_tra_hang') {
            $maDH  = (int)($_POST['MaDH'] ?? 0);
            $lyDo  = trim($_POST['LyDoTra'] ?? '') . ' - Chi tiết: ' . trim($_POST['ChiTietTra'] ?? '');
            $link  = trim($_POST['LinkVideo'] ?? ''); 

            if (isset($_FILES['FileChungMinh']) && $_FILES['FileChungMinh']['error'] === 0) {
                $returnPath = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'returns';
                if (!file_exists($returnPath)) mkdir($returnPath, 0777, true);
                $ext = pathinfo($_FILES['FileChungMinh']['name'], PATHINFO_EXTENSION);
                $fileName = 'return_dh' . $maDH . '_' . time() . '.' . $ext;
                $destPath = $returnPath . DIRECTORY_SEPARATOR . $fileName;
                
                if (move_uploaded_file($_FILES['FileChungMinh']['tmp_name'], $destPath)) {
                    $link = 'uploads/returns/' . $fileName;
                }
            }
            sqlsrv_query($conn, "UPDATE DonHang SET TrangThai=N'Yêu cầu đổi trả', LyDoTraHang=?, LinkVideoProof=? WHERE MaDH=? AND MaND=?", [$lyDo, $link, $maDH, $user_id]);
            header('Location: ChinhSuaProfile.php?s=yeu_cau_tra'); exit;
        }
    }

    // ================= ADMIN (VaiTro == 1) =================
    if ($user['VaiTro'] == 1) {
        if ($action === 'update_product_img_ajax') {
            $idSP = (int)$_POST['id'];
            if (isset($_FILES['HinhAnh']) && $_FILES['HinhAnh']['error'] === 0) {
                $ext = pathinfo($_FILES['HinhAnh']['name'], PATHINFO_EXTENSION);
                $dbPath = 'uploads/products/prod_' . $idSP . '_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['HinhAnh']['tmp_name'], __DIR__ . DIRECTORY_SEPARATOR . $dbPath)) {
                    sqlsrv_query($conn, "UPDATE SanPham SET HinhAnh = ? WHERE MaSP = ?", [$dbPath, $idSP]); echo $dbPath; 
                } else echo "Error moving file";
            } exit;
        }
        if ($action === 'quick_update_stock') {
            // Cộng dồn hàng mới vào kho
            echo (sqlsrv_query($conn, "UPDATE SanPham SET SoLuongTon = SoLuongTon + ? WHERE MaSP = ?", [(int)$_POST['qty'], (int)$_POST['id']])) ? "OK" : "Error"; exit;
        }
        if ($action === 'add_product') {
            $ten = trim($_POST['TenSP'] ?? ''); $madm = (int)($_POST['MaDM'] ?? 1); $gia = (float)($_POST['Gia'] ?? 0);
            $kho = (int)($_POST['SoLuongTon'] ?? 0); $mota = trim($_POST['MoTa'] ?? '');
            $cpu = trim($_POST['CPU'] ?? ''); $ram = trim($_POST['RAM'] ?? ''); $ocung = trim($_POST['O_Cung'] ?? '');
            $manhinh = trim($_POST['ManHinh'] ?? ''); $vga = trim($_POST['VGA'] ?? ''); $camera = trim($_POST['Camera'] ?? '');
            $pin = trim($_POST['Pin'] ?? ''); $ketnoi = trim($_POST['KetNoi'] ?? ''); $tuongthich = trim($_POST['TuongThich'] ?? '');
            $baohanh = trim($_POST['BaoHanh'] ?? '');
            $hinhAnh = "";
            if (isset($_FILES['HinhAnh']) && $_FILES['HinhAnh']['error'] === 0) {
                $hinhAnh = 'uploads/products/prod_new_' . time() . '.' . pathinfo($_FILES['HinhAnh']['name'], PATHINFO_EXTENSION);
                move_uploaded_file($_FILES['HinhAnh']['tmp_name'], __DIR__ . DIRECTORY_SEPARATOR . $hinhAnh);
            }
            sqlsrv_query($conn, "INSERT INTO SanPham (TenSP, MaDM, Gia, SoLuongTon, MoTa, CPU, RAM, O_Cung, ManHinh, VGA, Camera, Pin, KetNoi, TuongThich, BaoHanh, HinhAnh) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [$ten, $madm, $gia, $kho, $mota, $cpu, $ram, $ocung, $manhinh, $vga, $camera, $pin, $ketnoi, $tuongthich, $baohanh, $hinhAnh]);
            header('Location: ChinhSuaProfile.php?s=product'); exit;
        }
        if ($action === 'delete_product') {
            $idDel = (int)($_POST['MaSP']??0);
            sqlsrv_query($conn, "DELETE FROM ChiTietDonHang WHERE MaSP=?", [$idDel]); sqlsrv_query($conn, "DELETE FROM YeuThich WHERE MaSP=?", [$idDel]); sqlsrv_query($conn, "DELETE FROM SanPham WHERE MaSP=?", [$idDel]);
            header('Location: ChinhSuaProfile.php?s=product'); exit;
        }
        if ($action === 'edit_product') {
            $idEdit = (int)($_POST['MaSP']??0); $tenSP = trim($_POST['TenSP'] ?? ''); $gia = (float)($_POST['Gia'] ?? 0);
            $maDM = (int)($_POST['MaDM'] ?? 1); $moTa = trim($_POST['MoTa'] ?? '');
            $cpu = trim($_POST['CPU'] ?? ''); $ram = trim($_POST['RAM'] ?? ''); $oCung = trim($_POST['O_Cung'] ?? '');
            $manHinh = trim($_POST['ManHinh'] ?? ''); $vga = trim($_POST['VGA'] ?? ''); $camera = trim($_POST['Camera'] ?? '');
            $pin = trim($_POST['Pin'] ?? ''); $ketnoi = trim($_POST['KetNoi'] ?? ''); $tuongthich = trim($_POST['TuongThich'] ?? '');
            $baoHanh = trim($_POST['BaoHanh'] ?? '');
            
            $sql_upd = "UPDATE SanPham SET TenSP=?, Gia=?, MaDM=?, CPU=?, RAM=?, O_Cung=?, ManHinh=?, VGA=?, Camera=?, Pin=?, KetNoi=?, TuongThich=?, BaoHanh=?, MoTa=? WHERE MaSP=?";
            $params_upd = [$tenSP, $gia, $maDM, $cpu, $ram, $oCung, $manHinh, $vga, $camera, $pin, $ketnoi, $tuongthich, $baoHanh, $moTa, $idEdit];

            if (isset($_FILES['HinhAnhEdit']) && $_FILES['HinhAnhEdit']['error'] === 0) {
                $ext = pathinfo($_FILES['HinhAnhEdit']['name'], PATHINFO_EXTENSION);
                $dbPath = 'uploads/products/prod_' . $idEdit . '_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['HinhAnhEdit']['tmp_name'], __DIR__ . DIRECTORY_SEPARATOR . $dbPath)) {
                    $sql_upd = "UPDATE SanPham SET TenSP=?, Gia=?, MaDM=?, CPU=?, RAM=?, O_Cung=?, ManHinh=?, VGA=?, Camera=?, Pin=?, KetNoi=?, TuongThich=?, BaoHanh=?, MoTa=?, HinhAnh=? WHERE MaSP=?";
                    $params_upd = [$tenSP, $gia, $maDM, $cpu, $ram, $oCung, $manHinh, $vga, $camera, $pin, $ketnoi, $tuongthich, $baoHanh, $moTa, $dbPath, $idEdit];
                }
            }
            
            sqlsrv_query($conn, $sql_upd, $params_upd);
            header('Location: ChinhSuaProfile.php?s=product'); exit;
        }
        if ($action === 'add_mgg') {
            $code = strtoupper(trim($_POST['Code'] ?? '')); $loai = (int)($_POST['LoaiGiam'] ?? 0); $giaTri = (float)($_POST['GiaTri'] ?? 0);
            $giamToiDa = (float)($_POST['GiamToiDa'] ?? 0); $donToiThieu = (float)($_POST['DonToiThieu'] ?? 0); $soLan = (int)($_POST['SoLanDung'] ?? 1);
            $hetHan = trim($_POST['NgayHetHan'] ?? '');
            if (!empty($code) && $giaTri > 0) {
                $ngayHH = empty($hetHan) ? null : date('Y-m-d H:i:s', strtotime($hetHan));
                sqlsrv_query($conn, "INSERT INTO MaGiamGia (Code,LoaiGiam,GiaTri,GiamToiDa,DonToiThieu,SoLanDung,NgayHetHan) VALUES (?,?,?,?,?,?,?)", [$code,$loai,$giaTri,$giamToiDa,$donToiThieu,$soLan,$ngayHH]);
            }
            header('Location: ChinhSuaProfile.php?s=mgg'); exit;
        }
        if ($action === 'update_order') {
            $trangThaiMoi = $_POST['TrangThai'];
            $maDH = (int)$_POST['MaDH'];
            
            $q_old = sqlsrv_query($conn, "SELECT TrangThai, MaND, TongTien FROM DonHang WHERE MaDH=?", [$maDH]);
            $dh_old = sqlsrv_fetch_array($q_old, SQLSRV_FETCH_ASSOC);
            $trangThaiCu = $dh_old['TrangThai'];

            sqlsrv_query($conn, "UPDATE DonHang SET TrangThai=? WHERE MaDH=?", [$trangThaiMoi, $maDH]);
            
            if ($trangThaiMoi === 'Đã giao' && $trangThaiCu !== 'Đã giao') {
                $khach_id = $dh_old['MaND'];
                $tongTien = (float)$dh_old['TongTien'];
                $xuThuong = floor($tongTien * 0.05); // Tính 5%
                
                if ($xuThuong > 0) {
                    sqlsrv_query($conn, "UPDATE NguoiDung SET XuTichLuy = ISNULL(XuTichLuy, 0) + ? WHERE MaND=?", [$xuThuong, $khach_id]);
                }
            }
            header('Location: ChinhSuaProfile.php?s=donhang'); exit;
        }        
        if ($action === 'duyet_huy') {
            sqlsrv_query($conn, "UPDATE DonHang SET TrangThai=N'Đã hủy' WHERE MaDH=?", [(int)$_POST['MaDH']]);
            header('Location: ChinhSuaProfile.php?s=donhang'); exit;
        }
        if ($action === 'tu_choi_huy') {
            sqlsrv_query($conn, "UPDATE DonHang SET TrangThai=N'Chờ xử lý', LyDoHuy=NULL WHERE MaDH=?", [(int)$_POST['MaDH']]);
            header('Location: ChinhSuaProfile.php?s=donhang'); exit;
        }
        if ($action === 'duyet_tra_hang') {
            sqlsrv_query($conn, "UPDATE DonHang SET TrangThai=N'Đã hoàn tiền' WHERE MaDH=?", [(int)$_POST['MaDH']]);
            header('Location: ChinhSuaProfile.php?s=duyet_tra'); exit;
        }
        if ($action === 'tu_choi_tra_hang') {
            sqlsrv_query($conn, "UPDATE DonHang SET TrangThai=N'Từ chối đổi trả' WHERE MaDH=?", [(int)$_POST['MaDH']]);
            header('Location: ChinhSuaProfile.php?s=tu_choi_tra'); exit;
        }
        if ($action === 'toggle_user') {
            sqlsrv_query($conn, "UPDATE NguoiDung SET TrangThai = ? WHERE MaND = ?", [(int)$_POST['TrangThaiMoi'], (int)$_POST['MaND']]);
            header('Location: ChinhSuaProfile.php?s=nguoidung'); exit;
        }
        if ($action === 'delete_user') {
            $idDel = (int)$_POST['MaND'];
            if (!sqlsrv_has_rows(sqlsrv_query($conn, "SELECT TOP 1 MaDH FROM DonHang WHERE MaND=?", [$idDel]))) {
                sqlsrv_query($conn, "DELETE FROM ViGiamGia WHERE MaND=?", [$idDel]); sqlsrv_query($conn, "DELETE FROM YeuThich WHERE MaND=?", [$idDel]); sqlsrv_query($conn, "DELETE FROM SoDiaChi WHERE MaND=?", [$idDel]); sqlsrv_query($conn, "DELETE FROM NguoiDung WHERE MaND=?", [$idDel]);
            }
            header('Location: ChinhSuaProfile.php?s=nguoidung'); exit;
        }
    }
}

// LẤY DỮ LIỆU NẾU LÀ KHÁCH HÀNG (Phục vụ cho các Tab)
if ($user['VaiTro'] == 0) {
    $stmt_diachi = sqlsrv_query($conn, "SELECT * FROM SoDiaChi WHERE MaND = ? ORDER BY MacDinh DESC, MaDC DESC", [$user_id]);
    $stmt_yt = sqlsrv_query($conn, "SELECT yt.MaYT, sp.* FROM YeuThich yt JOIN SanPham sp ON yt.MaSP = sp.MaSP WHERE yt.MaND = ? ORDER BY yt.NgayThem DESC", [$user_id]);
    $dsDH = sqlsrv_query($conn, "SELECT * FROM DonHang WHERE MaND=? ORDER BY NgayDat DESC", [$user_id]);
    $ttColor = ['Chờ xử lý'=>'#f59e0b', 'Đang giao'=>'#6366f1', 'Đã giao'=>'#22c55e', 'Đã hủy'=>'#ef4444', 'Chờ xác nhận hủy'=>'#f97316', 'Yêu cầu đổi trả'=>'#a855f7', 'Đã hoàn tiền'=>'#0ea5e9', 'Từ chối đổi trả'=>'#ef4444'];    
    $chiTiet = []; $maDHChon = null;
    if (isset($_GET['id_don'])) {
        $maDHChon = (int)$_GET['id_don'];
        $rsCT = sqlsrv_query($conn, "SELECT ct.*, sp.TenSP, sp.HinhAnh FROM ChiTietDonHang ct JOIN SanPham sp ON ct.MaSP=sp.MaSP WHERE ct.MaDH=?", [$maDHChon]);
        while ($row = sqlsrv_fetch_array($rsCT, SQLSRV_FETCH_ASSOC)) $chiTiet[] = $row;
    }
}

$avSrc = (!empty($user['Avatar']) && file_exists(__DIR__ . '/' . $user['Avatar'])) ? $user['Avatar'] : 'https://ui-avatars.com/api/?name='.urlencode($user['HoTen']).'&background=6366f1&color=fff&size=200';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Exo+2:wght@300;400;500;600;700;900&family=Orbitron:wght@400;700;900&display=swap" rel="stylesheet">
<title>Tài Khoản / Quản Trị</title>
<style>

:root { --navy:#050d1a; --navy2:#071223; --panel:#0d1f38; --panel2:#0f2444; --panel3:#0d2240; --cyan:#00e5ff; --cyan2:#00b8d4; --purple:#7c3aed; --purple2:#a855f7; --green:#22c55e; --tx:#e2eaf5; --muted:#7a92b0; --border:rgba(0,229,255,0.12); --glow-cyan:0 0 20px rgba(0,229,255,0.4); --glow-purple:0 0 20px rgba(168,85,247,0.4); --orange:#f97316; --r: 14px; }
*,*::before,*::after { box-sizing:border-box; margin:0; padding:0; }
a.back { border: 2px solid #242342; border-radius: 8px; width: 100px; height: 30px; display: flex; justify-content: center; text-decoration: none; color: #bbbbbb; align-items: center; }
body { font-family: 'Exo 2', system-ui, sans-serif; background: var(--navy); color: var(--tx); min-height: 100vh; padding: 24px 16px 60px; }
::-webkit-scrollbar { width: 6px; } ::-webkit-scrollbar-track { background: var(--navy2); } ::-webkit-scrollbar-thumb { background: var(--cyan2); border-radius: 3px; }

/* LAYOUT & CHUNG */
.topbar { max-width: 980px; margin: 0 auto 28px; display: flex; align-items: center; gap: 12px; background: rgba(5,13,26,0.92); border: 1px solid var(--border); border-radius: var(--r); padding: 12px 20px; }
.tr { margin-left: auto; display: flex; align-items: center; gap: 10px; font-size: 13px; color: var(--muted); }
.tav { width: 34px; height: 34px; border-radius: 50%; object-fit: cover; border: 2px solid var(--cyan); box-shadow: var(--glow-cyan); }
.lay { max-width: 980px; margin: 0 auto; display: grid; grid-template-columns: 260px 1fr; gap: 20px; }
@media (max-width: 700px) { .lay { grid-template-columns: 1fr; } }
.card { background: var(--panel); border: 1px solid var(--border); border-radius: var(--r); padding: 24px; box-shadow: 0 8px 32px rgba(0,0,0,.5); }
.sb { display: flex; flex-direction: column; gap: 20px; }
.aw { display: flex; flex-direction: column; align-items: center; gap: 14px; }
.ar { position: relative; width: 110px; height: 110px; } .ar img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; border: 3px solid var(--cyan); box-shadow: var(--glow-cyan); }
.ab { position: absolute; bottom: 4px; right: 4px; width: 28px; height: 28px; background: var(--purple); border-radius: 50%; border: 2px solid var(--panel); display: flex; align-items: center; justify-content: center; font-size: 12px; cursor: pointer; transition: .2s; box-shadow: var(--glow-purple); }
.un { font-family: 'Orbitron', monospace; font-size: 15px; font-weight: 700; color: var(--tx); } .us { font-size: 12px; color: var(--muted); margin-top: -10px; }
.rb { padding: 4px 14px; border-radius: 20px; font-size: 11px; font-weight: 700; text-transform: uppercase; background: rgba(0,229,255,0.1); color: var(--cyan); border: 1px solid rgba(0,229,255,0.3); }
.snav { display: flex; flex-direction: column; gap: 4px; }
.ni { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 10px; font-size: 14px; color: var(--muted); text-decoration: none; transition: .15s; border: 1px solid transparent; cursor: pointer; }
.ni:hover { background: rgba(0,229,255,0.06); color: var(--cyan); border-color: var(--border); }
.ni.act { background: rgba(0,229,255,0.1); color: var(--cyan); border-color: rgba(0,229,255,0.3); font-weight: 600; box-shadow: 0 0 15px rgba(0,229,255,0.08); }
.st { font-family: 'Orbitron', monospace; font-size: 14px; font-weight: 700; letter-spacing: 0.05em; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; color: var(--cyan); text-transform: uppercase; }
.st::after { content: ''; flex: 1; height: 1px; background: linear-gradient(90deg, rgba(0,229,255,0.4), transparent); }
.tabs { display: flex; gap: 6px; margin-bottom: 24px; flex-wrap: wrap; }
.tb { padding: 8px 18px; border-radius: 8px; font-size: 11px; font-weight: 700; text-transform: uppercase; border: 1.5px solid var(--border); background: transparent; color: var(--muted); cursor: pointer; transition: .2s; }
.tb.act { background: rgba(0,229,255,0.12); border-color: var(--cyan); color: var(--cyan); box-shadow: var(--glow-cyan); }
.tp { display: none; } .tp.act { display: block; }
.btn { display: inline-flex; align-items: center; gap: 8px; padding: 11px 26px; border-radius: 10px; font-size: 13px; font-weight: 700; text-transform: uppercase; border: none; cursor: pointer; transition: .2s; justify-content: center; text-decoration: none; }
.bp { background: linear-gradient(135deg, var(--green), #16a34a); color: #fff; }
.bg2 { background: var(--panel2); color: var(--muted); border: 1.5px solid var(--border); }
.al { display: flex; align-items: center; gap: 10px; padding: 12px 16px; border-radius: 10px; margin-bottom: 20px; font-size: 14px; animation: fadeIn .3s ease; }
.ok { background: rgba(34,197,94,.1); border: 1px solid rgba(34,197,94,.3); color: #4ade80; }
.er { background: rgba(239,68,68,.1); border: 1px solid rgba(239,68,68,.3); color: #f87171; }
@keyframes fadeIn { from { opacity:0; transform:translateY(-6px); } to { opacity:1; transform:none; } }

/* FORM CHUNG */
.ig { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.ii label { font-size: 10px; text-transform: uppercase; color: var(--cyan); display: block; margin-bottom: 5px; font-weight: 600; }
.iv { background: var(--panel2); border: 1px solid var(--border); border-radius: 8px; padding: 10px 14px; font-size: 14px; color: var(--tx); }
.fg { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 15px;}
.fi { display: flex; flex-direction: column; gap: 6px; } .fi.full { grid-column: 1/-1; }
.fi label { font-size: 10px; color: var(--cyan); text-transform: uppercase; font-weight: 600; }
.fi input, .fi textarea, .fi select { background: var(--panel2); border: 1.5px solid var(--border); border-radius: 10px; color: var(--tx); font-size: 14px; font-family: 'Exo 2', sans-serif; padding: 10px 14px; outline: none; transition: .2s; }
.fi input:focus, .fi textarea:focus, .fi select:focus { border-color: var(--cyan); }
.fa { display: flex; gap: 10px; margin-top: 24px; justify-content: flex-end; }

/* CSS KHÁCH HÀNG: ĐƠN HÀNG, ĐỊA CHỈ, YÊU THÍCH */
.order-item{background:var(--panel2);border:1px solid var(--border);border-radius:10px;padding:16px;margin-bottom:12px;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;transition:.2s}
.order-item.pending-cancel{border-color:rgba(249,115,22,.35);background:rgba(249,115,22,.04)}
.order-id{font-weight:700;font-size:15px;color:var(--cyan);font-family:'Orbitron',sans-serif;}
.order-date{font-size:12px;color:var(--muted);margin-top:3px}
.order-total{font-weight:700;color:var(--purple2);font-size:16px}
.badge-status{padding:4px 12px;border-radius:20px;font-size:11px;font-weight:700;white-space:nowrap}
.btn-det{padding:7px 16px;border-radius:8px;font-size:12px;font-weight:600;border:1.5px solid var(--cyan);color:var(--cyan);background:transparent;text-decoration:none;transition:.2s;white-space:nowrap}
.btn-huy{padding:7px 14px;border-radius:8px;font-size:12px;font-weight:600;border:1.5px solid var(--orange);color:var(--orange);background:transparent;cursor:pointer;transition:.2s;white-space:nowrap;}
.ct-item{display:flex;gap:14px;align-items:center;padding:12px 0;border-bottom:1px solid var(--border)}
.ct-img{width:60px;height:60px;border-radius:8px;object-fit:cover;background:var(--panel2);border:1px solid var(--border);flex-shrink:0}

.grid-yt { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 15px; }
.sp-card { background: var(--panel2); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; transition: 0.3s; position: relative; }
.sp-card:hover { border-color: #ef4444; box-shadow: 0 5px 15px rgba(239,68,68,0.2); transform: translateY(-5px); }
.sp-img { height: 140px; background: var(--navy); display: flex; align-items: center; justify-content: center; font-size: 50px; }
.btn-del-fav { position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.6); color: #ef4444; border: none; border-radius: 50%; width: 30px; height: 30px; font-size: 14px; cursor: pointer; transition: 0.2s; }
.logo{
      --navy: #050d1a;
    --navy2: #071223;
    --navy3: #0a1a30;
    --panel: #0d1f38;
    --panel2: #0f2444;
    --cyan: #00e5ff;
    --cyan2: #00b8d4;
    --purple: #7c3aed;
    --purple2: #a855f7;
    --green: #22c55e;
    --green2: #16a34a;
    --text: #e2eaf5;
    --muted: #7a92b0;
    --border: rgba(0, 229, 255, 0.12);
    --glow-cyan: 0 0 20px rgba(0, 229, 255, 0.4);
    --glow-purple: 0 0 20px rgba(168, 85, 247, 0.4);
        font-family: 'REVERT';
    font-weight: 900;
    font-size: 20px;
    letter-spacing: 0.05em;
    text-decoration: none;
    margin-right: 16px;

}.logo span:first-child { color: var(--cyan); }
  .logo span:last-child { color: var(--text); }
.addr-card { background: var(--panel2); border: 1px solid var(--border); border-radius: 10px; padding: 16px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: flex-start; }
.badge-default { background: rgba(34,197,94,0.15); color: #4ade80; border: 1px solid rgba(34,197,94,0.3); padding: 2px 8px; border-radius: 12px; font-size: 10px; font-weight: bold; text-transform: uppercase; margin-left: 8px;}
  
/* BẢNG CHUNG ADMIN */
.stock-table { width:100%; border-collapse: separate; border-spacing: 0 8px; font-size: 13px; }
.stock-table tr { background: rgba(13, 31, 56, 0.4); transition: 0.3s; }
.stock-table td { padding: 12px 10px; border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); }
.stock-table td:first-child { border-left: 1px solid var(--border); border-radius: 10px 0 0 10px; padding-left: 15px; }
.stock-table td:last-child { border-right: 1px solid var(--border); border-radius: 0 10px 10px 0; }
.badge-tt { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; white-space: nowrap; display: inline-block; }
.ly-do-box { background: rgba(249,115,22,.1); border: 1px solid rgba(249,115,22,.25); border-radius: 6px; padding: 6px 10px; font-size: 12px; color: #fb923c; margin-top: 6px; max-width: 200px; }
.btn-ok-huy { background: rgba(239,68,68,.15); color: #f87171; border: 1px solid rgba(239,68,68,.35); padding: 5px 10px; border-radius: 6px; font-weight: 700; cursor: pointer; font-size: 11px;}
.btn-no-huy { background: rgba(34,197,94,.12); color: #4ade80; border: 1px solid rgba(34,197,94,.3); padding: 5px 10px; border-radius: 6px; font-weight: 700; cursor: pointer; font-size: 11px;}
.pagination-nav { display: flex; gap: 8px; justify-content: center; margin-top: 15px; }
.pagination-nav button { background: var(--panel2); border: 1px solid var(--border); color: var(--muted); padding: 5px 12px; border-radius: 6px; cursor: pointer; font-family: 'Orbitron'; transition: 0.2s; }
.pagination-nav button.active { background: rgba(0,229,255,0.1); border-color: var(--cyan); color: var(--cyan); }
</style>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<div class="topbar">
 <a class="logo" href="#"><span>KON</span><span> TechVN </span></a>  <div class="tr">
    <img class="tav" src="<?= htmlspecialchars($avSrc) ?>" alt="">
    <span><?= htmlspecialchars($user['HoTen']) ?></span>
  </div>
  <a href="TrangChuDaDangNhap.php" class="back">&#x2190; Trang Chủ</a>
</div>

<div class="lay">
  <aside class="sb">
    <div class="card aw">
      <div class="ar" style="margin-bottom: 5px;">
        <img id="mai" src="<?= htmlspecialchars($avSrc) ?>" alt="avatar">
        <div class="ab" onclick="document.getElementById('avi').click()" title="Thay đổi ảnh đại diện" style="font-size: 14px;">&#x1F4F7;</div>
      </div>
      <div class="un"><?= htmlspecialchars($user['HoTen']) ?></div>
      <div class="us">@<?= htmlspecialchars($user['TenDangNhap']) ?></div>
      <div class="rbadge" id="role-badge"><?= $vTxt ?></div>
      
      <form class="auf" method="post" enctype="multipart/form-data" style="width:100%;">
        <input type="hidden" name="action" value="update_avatar">
        <input type="file" name="avatar" id="avi" accept="image/*" onchange="prevAv(this)" style="display:none;">
        <div id="pw" style="display:none; flex-direction:row; gap:8px; margin-top:15px; justify-content:center;">
          <button type="submit" class="btn bp" style="padding:6px 12px; font-size:11px; flex:1;">💾 Lưu</button>
          <button type="button" class="btn bg2" style="padding:6px 12px; font-size:11px; flex:1;" onclick="cancelPrev()">✕ Hủy</button>
        </div>
      </form>
    </div>
    
    <div class="card">
      <nav class="snav">
        <a class="ni act" onclick="sw('view', this)">👤 Hồ sơ cá nhân</a>
        
        <?php if ($user['VaiTro'] == 0): ?>
            <a class="ni" onclick="sw('donhang_khach', this)">📦 Đơn hàng của tôi</a>
            <a class="ni" onclick="sw('yeuthich', this)">❤️ Sản phẩm yêu thích</a>
            <a class="ni" onclick="sw('diachi', this)">🏠 Địa chỉ giao hàng</a>
            <a class="ni" onclick="sw('vi_voucher', this)">🎫 Mã thẻ của bạn</a>
        <?php endif; ?> 
        
        <?php if ($user['VaiTro'] == 1): ?>
            <a class="ni" onclick="sw('mgg', this)">🎫 Quản lý mã giảm giá</a>
            <a class="ni" onclick="sw('donhang', this)">📦 Quản lý đơn hàng</a>
            <a class="ni" onclick="sw('nguoidung', this)">&#x1F6E1; Quản lý người dùng</a>
            <a class="ni" onclick="sw('tinnhan', this)">&#x1F4AC; Quản lý tin nhắn</a>
        <?php endif; ?>
        
        <a href="TrangChu.php" class="ni" style="color:#ef4444; margin-top: 10px;">🚪 Đăng xuất</a>
      </nav>
    </div>
  </aside>

  <main>
    <div class="card" style="min-height: 600px;">
      <?php if ($sMsg): ?><div class="al ok">&#x2705; <?= htmlspecialchars($sMsg) ?></div><?php endif; ?>
      <?php if ($error): ?><div class="al er">&#x274C; <?= htmlspecialchars($error) ?></div><?php endif; ?>

      <div class="tabs">
          <button class="tb act" onclick="sw('view',this)">&#x1F441; Thông tin</button>
          <?php if ($user['VaiTro'] == 1): ?>
              <button class="tb" onclick="sw('add_sp',this)">&#x1F4E6; Thêm SP</button>
              <button class="tb" onclick="sw('update_stock',this)">⚙️ Nhập Kho</button>
              <button class="tb" onclick="sw('sua_sp',this)">✏️ Sửa SP</button>
          <?php else: ?>
              <button class="tb" onclick="sw('edit',this)">&#x270F; Chỉnh sửa</button>
          <?php endif; ?>
      </div>

      <div id="tv" class="tp act">
        <div class="st">Thông tin cá nhân</div>
        <div class="ig">
          <div class="ii"><label>Họ và tên</label><div class="iv"><?= htmlspecialchars($user['HoTen']) ?></div></div>
<div class="ii"><label>Tên đăng nhập</label><div class="iv"><?= htmlspecialchars($user['TenDangNhap']) ?></div></div>
          
          <?php if ($user['VaiTro'] == 0): ?>
          <div class="ii" style="grid-column:1/-1;">
            <div style="background:rgba(245,158,11,0.1); border:1px solid rgba(245,158,11,0.4); padding:15px; border-radius:10px; display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <label style="color:#fbbf24; font-size:12px; font-weight:bold;">SỐ DƯ XU TÍCH LŨY</label>
                    <div style="color:#fbbf24; font-family:'Orbitron'; font-size:24px; font-weight:bold; margin-top:5px;">🪙 <?= number_format($user['XuTichLuy'] ?? 0, 0, ',', '.') ?> Xu</div>
                </div>
                <div style="text-align:right; font-size:12px; color:var(--muted);">
                    (1 Xu = 1 VNĐ)<br>Được giảm trực tiếp khi mua hàng
                </div>
            </div>
          </div>
          <?php endif; ?>
          <div class="ii"><label>Email</label><div class="iv"><?= htmlspecialchars($user['Email'] ?? '—') ?></div></div>          <div class="ii"><label>Số điện thoại</label><div class="iv"><?= htmlspecialchars($user['SoDienThoai'] ?? '—') ?></div></div>
          <div class="ii" style="grid-column:1/-1">
            <label>Địa chỉ</label>
            <div class="iv"><?= htmlspecialchars($user['DiaChi'] ?? '—') ?></div>
          </div>
        </div>
      </div>

      <?php if ($user['VaiTro'] == 0): ?>
      
      <div id="te" class="tp">
        <div class="st">Chỉnh sửa thông tin</div>
        <form method="post">
          <input type="hidden" name="action" value="update_info">
          <div class="fg">
            <div class="fi"><label>Họ và tên *</label><input type="text" name="HoTen" value="<?= htmlspecialchars($user['HoTen']) ?>" required></div>
            <div class="fi"><label>Tên đăng nhập</label><input type="text" value="<?= htmlspecialchars($user['TenDangNhap']) ?>" disabled></div>
            <div class="fi"><label>Email *</label><input type="email" name="Email" value="<?= htmlspecialchars($user['Email'] ?? '') ?>" required></div>
            <div class="fi"><label>Số điện thoại</label><input type="tel" name="SoDienThoai" value="<?= htmlspecialchars($user['SoDienThoai'] ?? '') ?>"></div>
            <div class="fi full"><label>Địa chỉ</label><textarea name="DiaChi"><?= htmlspecialchars($user['DiaChi'] ?? '') ?></textarea></div>
          </div>
          <div class="fa"><button type="submit" class="btn bp">&#x1F4BE; Lưu thay đổi</button></div>
        </form>
      </div>

      <div id="t_dh_khach" class="tp">
        <div class="st">&#x1F4E6; Đơn hàng của tôi</div>
        
        <?php if ($dsDH && sqlsrv_has_rows($dsDH)): ?>
          <div id="khach-order-list">
              <?php while ($dh = sqlsrv_fetch_array($dsDH, SQLSRV_FETCH_ASSOC)): 
                $c = $ttColor[$dh['TrangThai']] ?? '#888899';
                $isPendingCancel = ($dh['TrangThai'] === 'Chờ xác nhận hủy');
                $canCancel = ($dh['TrangThai'] === 'Chờ xử lý');
                $canReturn = ($dh['TrangThai'] === 'Đã giao');
              ?>
             
               <div class="order-item <?= $isPendingCancel ? 'pending-cancel' : '' ?>">
                  <div>
                    <div class="order-id">Đơn #<?= $dh['MaDH'] ?></div>
                    <div class="order-date"><?= ($dh['NgayDat'] instanceof DateTime) ? $dh['NgayDat']->format('d/m/Y H:i') : '' ?></div>
                    <?php if ($isPendingCancel && !empty($dh['LyDoHuy'])): ?>
                      <div style="font-size:12px; color:var(--orange); margin-top:6px;">&#x23F3; Chờ admin duyệt hủy: "<?= htmlspecialchars($dh['LyDoHuy']) ?>"</div>
                    <?php endif; ?>
                  </div>
                  <div class="badge-status" style="background:<?= $c ?>22;color:<?= $c ?>;border:1px solid <?= $c ?>55"><?= htmlspecialchars($dh['TrangThai']) ?></div>
                  <div class="order-total"><?= number_format($dh['TongTien'],0,',','.') ?>đ</div>
                  
                  <div style="display:flex;gap:8px;align-items:center;">
                    <?php if ($canCancel): ?>
                      <button class="btn-huy" onclick="openHuyModal(<?= $dh['MaDH'] ?>)">&#x274C; Yêu cầu hủy</button>
                    <?php endif; ?>
                    
                    <?php if ($canReturn): ?>
                      <button class="btn-huy" style="border-color:#a855f7; color:#a855f7;" onclick="openTraHangModal(<?= $dh['MaDH'] ?>)">🔄 Yêu cầu đổi trả</button>
                    <?php endif; ?>

                    <a href="?id_don=<?= $dh['MaDH'] ?>" class="btn-det">Chi tiết</a>
                  </div>
                </div>
              <?php endwhile; ?>
          </div>
        <?php else: ?>
          <div style="text-align:center; padding:40px; color:var(--muted);">Bạn chưa có đơn hàng nào</div>
        <?php endif; ?>

        <?php if ($maDHChon && count($chiTiet)): ?>
        <div style="margin-top: 30px; background: var(--panel2); padding: 20px; border-radius: 10px; border: 1px solid var(--border);">
          <div class="st" style="font-size:13px;">&#x1F4CB; Chi tiết đơn #<?= $maDHChon ?></div>
          <?php foreach ($chiTiet as $ct): ?>
            <div class="ct-item">
              <img class="ct-img" src="<?= htmlspecialchars($ct['HinhAnh'] ?? '') ?>" onerror="this.style.display='none'">
              <div style="flex:1;">
                <div style="font-size:14px; font-weight:600;"><?= htmlspecialchars($ct['TenSP']) ?></div>
                <div style="font-size:12px; color:var(--muted); margin-top:4px;">SL: <?= $ct['SoLuong'] ?> | Đơn giá: <?= number_format($ct['DonGia'],0,',','.') ?>đ</div>
              </div>
              <div style="font-weight:700; color:var(--purple2);"><?= number_format($ct['SoLuong']*$ct['DonGia'],0,',','.') ?>đ</div>
            </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

      <div id="t_yt" class="tp">
        <div class="st">❤️ SẢN PHẨM ĐÃ THẢ TIM</div>
        <div class="grid-yt">
          <?php $hasFav = false; if($stmt_yt): while ($sp = sqlsrv_fetch_array($stmt_yt, SQLSRV_FETCH_ASSOC)): $hasFav = true; ?>
            <div class="sp-card" id="fav-<?= $sp['MaSP'] ?>">
                <button class="btn-del-fav" title="Bỏ yêu thích" onclick="removeFav(<?= $sp['MaSP'] ?>)">✖</button>
                <a href="ChiTietSanPham.php?id=<?= $sp['MaSP'] ?>" style="display:block; text-decoration:none;">
                    <div class="sp-img"><?= (!empty($sp['HinhAnh'])) ? "<img src='".$sp['HinhAnh']."' style='max-width:100%; max-height:100%; object-fit:cover;'>" : '📦' ?></div>
                    <div style="padding:15px;">
                        <div style="font-size:13px; font-weight:bold; color:var(--tx); margin-bottom:5px;"><?= htmlspecialchars($sp['TenSP']) ?></div>
                        <div style="color:var(--cyan); font-weight:bold; font-family:'Orbitron'; font-size:14px;"><?= number_format($sp['Gia'], 0, ',', '.') ?> đ</div>
                    </div>
                </a>
            </div>
          <?php endwhile; endif; ?>
        </div>
        <?php if(!$hasFav): ?>
          <div style="text-align:center; padding: 50px; color: var(--muted);"><div style="font-size: 40px; margin-bottom: 10px;">🤍</div><p>Danh sách yêu thích đang trống.</p></div>
        <?php endif; ?>
      </div>

      <div id="t_dc" class="tp">
        <div class="st">🏠 Sổ Địa Chỉ Giao Hàng</div>
        <button class="btn bg2" style="width:100%; padding:12px; margin-bottom:20px; border-style:dashed;" onclick="document.getElementById('form-add-dc').style.display='block'">+ Thêm Địa Chỉ Mới</button>
        
        <div id="form-add-dc" style="display: none; background: var(--panel2); padding: 20px; border-radius: 10px; border: 1px solid var(--border); margin-bottom:20px;">
          <h4 style="color:var(--cyan); margin-bottom:10px;">Thêm địa chỉ giao hàng</h4>
          <form method="post">
            <input type="hidden" name="action" value="add_address">
            <div class="fg">
              <div class="fi"><label>Họ tên nhận *</label><input type="text" name="HoTen" required></div>
              <div class="fi"><label>Số điện thoại *</label><input type="tel" name="SoDienThoai" required></div>
              <div class="fi full"><label>Thành phố *</label><input type="text" name="ThanhPho" required></div>
              <div class="fi full"><label>Địa chỉ cụ thể *</label><textarea name="DiaChi" required></textarea></div>
              <div class="fi full" style="flex-direction:row; align-items:center;">
                <input type="checkbox" name="MacDinh" id="md" value="1" style="width:auto;"> <label for="md" style="margin-top:2px; cursor:pointer;">Đặt làm mặc định</label>
              </div>
            </div>
            <div class="fa"><button type="button" class="btn bg2" onclick="document.getElementById('form-add-dc').style.display='none'">Hủy</button><button type="submit" class="btn bp">Lưu Địa Chỉ</button></div>
          </form>
        </div>

        <?php $hasAddr = false; if($stmt_diachi): while ($dc = sqlsrv_fetch_array($stmt_diachi, SQLSRV_FETCH_ASSOC)): $hasAddr = true; ?>
        <div class="addr-card">
          <div>
            <h4 style="margin:0 0 5px; color:var(--tx); display:flex; align-items:center; gap:8px;">
              <?= htmlspecialchars($dc['HoTenNguoiNhan']) ?> <span style="color:var(--muted); font-weight:normal; font-size:12px;">| <?= htmlspecialchars($dc['SoDienThoai']) ?></span>
              <?php if($dc['MacDinh'] == 1): ?><span class="badge-default">Mặc định</span><?php endif; ?>
            </h4>
            <p style="font-size:13px; color:var(--muted); margin:0 0 4px;"><?= htmlspecialchars($dc['DiaChiCuThe']) ?></p>
            <p style="font-size:13px; color:var(--muted); margin:0;"><?= htmlspecialchars($dc['ThanhPho']) ?></p>
          </div>
          <div style="display:flex; gap:8px; flex-direction:column; align-items:flex-end;">
            <?php if($dc['MacDinh'] == 0): ?>
            <form method="post" style="margin:0;"><input type="hidden" name="action" value="set_default"><input type="hidden" name="MaDC" value="<?= $dc['MaDC'] ?>"><button type="submit" class="btn" style="background:transparent; color:var(--cyan); border:1px solid var(--cyan); padding:5px 10px; font-size:10px;">Đặt Mặc Định</button></form>
            <?php endif; ?>
            <form method="post" style="margin:0;" onsubmit="return confirm('Xóa địa chỉ này?');"><input type="hidden" name="action" value="delete_address"><input type="hidden" name="MaDC" value="<?= $dc['MaDC'] ?>"><button type="submit" class="btn bg2" style="padding:5px 10px; font-size:10px;">Xóa</button></form>
          </div>
        </div>
        <?php endwhile; endif; ?>
        <?php if(!$hasAddr): ?><p style="text-align:center; color:var(--muted);">Chưa có địa chỉ nào.</p><?php endif; ?>
      </div>

      <div id="t_vi_voucher" class="tp">
        <div class="st">🎫 Kho Mã Giảm Giá Của Bạn</div>
        <div style="display:flex; flex-direction:column; gap:12px;">
          <?php
            $sql_vi = "SELECT m.*, v.NgayLuu FROM ViGiamGia v JOIN MaGiamGia m ON v.MaMGG = m.MaMGG WHERE v.MaND = ? AND v.TrangThaiSuDung = 0 ORDER BY v.NgayLuu DESC";
            $stmt_vi = sqlsrv_query($conn, $sql_vi, [$user_id]);
            $hasVoucher = false;
            if ($stmt_vi) {
                while($v = sqlsrv_fetch_array($stmt_vi, SQLSRV_FETCH_ASSOC)):
                    $hasVoucher = true;
                    $loai = $v['LoaiGiam'] == 0 ? "Giảm ".$v['GiaTri']."%" : "Giảm ".number_format($v['GiaTri'],0,',','.')."đ";
                    $hsd = $v['NgayHetHan'] ? $v['NgayHetHan']->format('H:i d/m/Y') : "Không giới hạn";
          ?>
          <div style="border-left: 4px solid var(--cyan); display: flex; justify-content: space-between; align-items: center; background: rgba(0,229,255,0.05); padding:15px; border-radius:8px;">
              <div>
                  <h4 style="color: var(--cyan); margin:0 0 5px; font-family: 'Orbitron'; font-size: 18px;"><?= htmlspecialchars($v['Code']) ?></h4>
                  <p style="font-size: 13px; color: var(--text); margin:0 0 5px;">Mô tả: <?= $loai ?> (Áp dụng đơn từ <?= number_format($v['DonToiThieu'],0,',','.') ?>đ)</p>
                  <p style="font-size: 12px; color: #f87171; margin:0;">⏳ Hạn dùng: <?= $hsd ?></p>
              </div>
              <button class="btn bp" style="padding: 8px 16px; font-size: 12px;" onclick="window.location.href='TrangChuDaDangNhap.php#products'">Dùng Ngay</button>
          </div>
          <?php endwhile; } ?>
          
          <?php if(!$hasVoucher): ?>
            <div style="text-align: center; padding: 30px; color: var(--muted);">
                <div style="font-size: 40px; margin-bottom: 10px;">🎫</div>
                <p>Ví của bạn chưa có mã giảm giá nào.</p>
                <button class="btn bg2" style="margin-top: 15px;" onclick="window.location.href='TrangChuDaDangNhap.php#promo'">Đi săn mã ngay!</button>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div id="modalHuyDon" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(5px);">
        <div style="background:var(--panel); border:1px solid var(--border); border-radius:14px; padding:28px; width:90%; max-width:460px;">
          <div style="font-size:16px; font-weight:700; margin-bottom:6px; color:#f97316;">&#x274C; Yêu cầu hủy đơn hàng</div>
          <div style="font-size:13px; color:var(--muted); margin-bottom:18px;">Đơn #<span id="txtModalMaDH"></span></div>
          <form method="post">
            <input type="hidden" name="action" value="yeu_cau_huy">
            <input type="hidden" name="MaDH" id="inpModalMaDH">
            <textarea name="LyDoHuy" placeholder="Vui lòng nêu rõ lý do bạn muốn hủy đơn hàng này..." required style="width:100%; background:var(--panel2); border:1px solid var(--border); border-radius:8px; color:var(--tx); padding:10px; min-height:90px; outline:none; resize:vertical;"></textarea>
            <div style="display:flex; gap:10px; margin-top:15px; justify-content:flex-end;">
              <button type="button" class="btn bg2" onclick="document.getElementById('modalHuyDon').style.display='none'">Hủy bỏ</button>
              <button type="submit" class="btn" style="background:#f97316; color:#fff;">Gửi yêu cầu hủy</button>
            </div>
          </form>
        </div>
      </div>
      <div id="modalTraHang" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(5px);">
        <div style="background:var(--panel); border:1px solid var(--border); border-radius:14px; padding:28px; width:90%; max-width:500px;">
          <div style="font-size:16px; font-weight:700; margin-bottom:6px; color:var(--purple2);">🔄 Yêu cầu Đổi/Trả hàng & Hoàn tiền</div>
          <div style="font-size:13px; color:var(--muted); margin-bottom:18px;">Đơn #<span id="txtModalTraMaDH"></span></div>
          <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="yeu_cau_tra_hang">
            <input type="hidden" name="MaDH" id="inpModalTraMaDH">
            <div class="fi" style="margin-bottom:10px;">
                <label>Lý do chính *</label>
                <select name="LyDoTra" required style="width:100%; background:var(--panel2); border:1px solid var(--border); border-radius:8px; color:var(--tx); padding:10px; margin-top:5px;">
                    <option value="Sản phẩm bị lỗi kỹ thuật">Sản phẩm bị lỗi kỹ thuật</option>
                    <option value="Giao sai sản phẩm / màu sắc">Giao sai sản phẩm / màu sắc</option>
                    <option value="Thiếu phụ kiện / Quà tặng">Thiếu phụ kiện / Quà tặng</option>
                    <option value="Bể vỡ do vận chuyển">Bể vỡ do vận chuyển</option>
                </select>
            </div>
            <div class="fi" style="margin-bottom:10px;">
                <label>Mô tả chi tiết tình trạng *</label>
                <textarea name="ChiTietTra" placeholder="Mô tả rõ sản phẩm bị lỗi như thế nào..." required style="width:100%; background:var(--panel2); border:1px solid var(--border); border-radius:8px; color:var(--tx); padding:10px; min-height:80px; outline:none; resize:vertical; margin-top:5px;"></textarea>
            </div>
            
            <div class="fi" style="margin-bottom:20px; background:rgba(0,229,255,0.05); padding:15px; border-radius:10px; border:1px dashed var(--cyan);">
                <label>Tải Video / Ảnh chứng minh lên (Ưu tiên) *</label>
                <input type="file" name="FileChungMinh" accept="video/*, image/*" style="width:100%; background:var(--panel2); border:1px solid var(--border); border-radius:8px; color:var(--tx); padding:7px; outline:none; margin-top:5px;">
                
                <div style="text-align:center; font-size:12px; color:var(--muted); margin:10px 0; font-weight:bold;">-- HOẶC CHÈN LINK --</div>
                
                <input type="url" name="LinkVideo" placeholder="Dán link Drive, Youtube nếu file quá nặng..." style="width:100%; background:var(--panel2); border:1px solid var(--border); border-radius:8px; color:var(--tx); padding:10px; outline:none;">
            </div>

            <div style="display:flex; gap:10px; justify-content:flex-end;">
              <button type="button" class="btn bg2" onclick="document.getElementById('modalTraHang').style.display='none'">Hủy bỏ</button>
              <button type="submit" class="btn" style="background:var(--purple); color:#fff;">Gửi yêu cầu đổi trả</button>
            </div>
          </form>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($user['VaiTro'] == 1): ?>
      
      <div id="ts" class="tp">
        <div class="st">Thêm sản phẩm bán hàng</div>
        <form method="post" enctype="multipart/form-data">
          <input type="hidden" name="action" value="add_product">
          <div class="fg">
            <div class="fi full"><label>Tên sản phẩm *</label><input type="text" name="TenSP" required></div>
            <div class="fi"><label>Danh mục</label><select name="MaDM" onchange="toggleSpecs(this.value, '')"><option value="1">💻 Laptop</option><option value="2">📱 Điện thoại</option><option value="3">🖥️ PC Gaming</option><option value="4">🎧 Phụ Kiện</option><option value="5">🖱️ Gaming Gear</option></select></div>
            <div class="fi"><label>Giá bán (VNĐ) *</label><input type="number" name="Gia" required></div>
            <div class="fi"><label>Số lượng trong kho *</label><input type="number" name="SoLuongTon" value="10" required></div>
            <div class="fi full"><label>Ảnh sản phẩm</label><input type="file" name="HinhAnh" accept="image/*" style="padding: 7px;"></div>
            <div class="fi full"><div class="st" style="font-size:12px; margin: 15px 0 0 0;">Cấu hình chi tiết</div></div>
            <div id="grp-dientu" style="display:contents;"><div class="fi"><label>CPU</label><input type="text" name="CPU"></div><div class="fi"><label>RAM</label><input type="text" name="RAM"></div><div class="fi"><label>Ổ Cứng</label><input type="text" name="O_Cung"></div><div class="fi"><label>Màn hình</label><input type="text" name="ManHinh"></div></div>
            <div id="grp-laptop" style="display:contents;"><div class="fi"><label>VGA</label><input type="text" name="VGA"></div></div>
            <div id="grp-phone" style="display:none;"><div class="fi"><label>Camera</label><input type="text" name="Camera"></div><div class="fi"><label>Pin</label><input type="text" name="Pin"></div></div>
            <div id="grp-gear" style="display:none;"><div class="fi"><label>Kết Nối</label><input type="text" name="KetNoi"></div><div class="fi"><label>Tương thích</label><input type="text" name="TuongThich"></div></div>
            <div class="fi full"><label>Thời gian bảo hành</label><input type="text" name="BaoHanh"></div>
            <div class="fi full"><label>Mô tả</label><textarea name="MoTa"></textarea></div>
          </div>
          <div class="fa"><button type="submit" class="btn bp">&#x1F4E6; Đăng bán sản phẩm</button></div>
        </form>
      </div>
      
      <div id="tuk" class="tp">
        <div class="st">Quản lý tồn kho & Hình ảnh</div>
        <div style="overflow-x: auto;">
          <table class="stock-table" id="table-tuk">
            <thead><tr style="color: var(--cyan);"><th style="padding: 10px;">ID</th><th style="padding: 10px;">Ảnh</th><th style="padding: 10px;">Sản phẩm</th><th style="padding: 10px;">Nhập thêm số lượng</th><th style="text-align: right; padding: 10px;">Lưu</th></tr></thead>
            <tbody>
              <?php
              $q_list = sqlsrv_query($conn, "SELECT MaSP, TenSP, SoLuongTon, HinhAnh FROM SanPham ORDER BY MaSP DESC");
              while($row = sqlsrv_fetch_array($q_list, SQLSRV_FETCH_ASSOC)):
              ?>
              <tr>
                <td style="color: var(--muted);">#<?= $row['MaSP'] ?></td>
                <td><img src="<?= $row['HinhAnh'] ?>" style="width:45px; height:45px; object-fit:contain; background:var(--panel2); border-radius:6px; padding:2px;" onerror="this.src='https://via.placeholder.com/50x50?text=Img'"></td>
                <td style="font-weight: 600;"><?= htmlspecialchars($row['TenSP']) ?></td>
                <td>
                    <div style="font-size:11px; color:var(--muted); margin-bottom:4px;">Trong kho đang có: <strong style="color:var(--green); font-size:13px;"><?= $row['SoLuongTon'] ?></strong></div>
                    <input type="number" id="stock-<?= $row['MaSP'] ?>" value="0" min="0" placeholder="+ Thêm" style="width:90px; background:var(--panel2); border:1.5px solid var(--border); color:var(--cyan); padding:5px 8px; border-radius:4px; font-weight:bold;">
                </td>
                <td style="text-align: right;"><button onclick="saveStock(<?= $row['MaSP'] ?>)" class="btn bp" style="padding: 6px 12px; background:var(--cyan); color:var(--navy);">➕ CỘNG VÀO KHO</button></td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div id="tsp" class="tp">
        <div class="st">Chỉnh sửa / Xóa sản phẩm</div>
        <div style="overflow-x:auto">
          <table class="stock-table" id="table-tsp">
            <thead><tr style="color:var(--cyan);"><th style="padding:10px;">ID</th><th style="padding:10px;">Sản phẩm</th><th style="padding:10px;">Giá</th><th style="padding:10px;">Tồn kho</th><th style="padding:10px;text-align:right;">Thao tác</th></tr></thead>
            <tbody>
              <?php
              // Phải select thêm HinhAnh để show ra
              $q2 = sqlsrv_query($conn, "SELECT MaSP,TenSP,Gia,SoLuongTon,HinhAnh FROM SanPham ORDER BY MaSP DESC");
              while($r2 = sqlsrv_fetch_array($q2, SQLSRV_FETCH_ASSOC)):
              ?>
              <tr>
                <td style="color:var(--muted);padding:10px">#<?= $r2['MaSP'] ?></td>
                <td style="padding:10px">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <img src="<?= $r2['HinhAnh'] ?>" style="width:40px; height:40px; object-fit:contain; background:var(--panel2); border-radius:6px; padding:2px;" onerror="this.src='https://via.placeholder.com/40x40?text=Img'">
                        <span style="font-weight:600; color:var(--text);"><?= htmlspecialchars($r2['TenSP']) ?></span>
                    </div>
                </td>
                <td style="color:var(--cyan);padding:10px; font-weight:bold;"><?= number_format($r2['Gia'],0,',','.') ?>đ</td>
                <td style="padding:10px; font-weight:bold; color:var(--green);"><?= $r2['SoLuongTon'] ?></td>
                <td style="text-align:right;padding:10px">
                  <button onclick="openEdit(<?= $r2['MaSP'] ?>)" class="btn" style="padding:6px 14px;background:rgba(99,102,241,.15);color:#818cf8;border:1px solid rgba(99,102,241,.3)">✏️ Sửa</button>
                  <form method="post" style="display:inline" onsubmit="return confirm('Xóa sản phẩm này?')"><input type="hidden" name="action" value="delete_product"><input type="hidden" name="MaSP" value="<?= $r2['MaSP'] ?>"><button type="submit" class="btn" style="padding:6px 14px;background:rgba(239,68,68,.1);color:#f87171;border:1px solid rgba(239,68,68,.25)">🗑 Xóa</button></form>
                </td>
              </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div id="t_mgg" class="tp">
        <div class="st">🎫 Quản lý mã giảm giá</div>
        <div class="wrap-mgg" style="display: grid; grid-template-columns: 1fr 340px; gap: 20px; align-items: start;">
            <div class="card" style="padding:15px; border-color:var(--border);">
                <div style="overflow-x:auto;">
                    <table class="stock-table" id="table-mgg">
                        <thead><tr style="color:var(--cyan);"><th style="padding:10px;">Mã</th><th style="padding:10px;">Giá trị</th><th style="padding:10px;">Lượt dùng</th><th style="padding:10px;">Trạng thái</th><th style="padding:10px;"></th></tr></thead>
                        <tbody>
                            <?php
                            $q_mgg = sqlsrv_query($conn, "SELECT * FROM MaGiamGia ORDER BY MaMGG DESC");
                            if ($q_mgg && sqlsrv_has_rows($q_mgg)):
                                while($mg = sqlsrv_fetch_array($q_mgg, SQLSRV_FETCH_ASSOC)):
                                    $pct = ($mg['SoLanDung'] > 0) ? min(100, round($mg['DaDung'] / $mg['SoLanDung'] * 100)) : 0;
                                    $barColor = $pct >= 100 ? '#ef4444' : ($pct >= 70 ? '#f59e0b' : '#00e5ff');
                            ?>
                            <tr>
                                <td><span style="font-family:'Orbitron';font-size:12px;font-weight:700;color:var(--cyan);background:rgba(0,229,255,.08);border:1px solid rgba(0,229,255,.2);padding:5px 12px;border-radius:6px;"><?= htmlspecialchars($mg['Code']) ?></span></td>
                                <td style="font-weight:700; font-size:14px;"><?= $mg['LoaiGiam']==0 ? number_format($mg['GiaTri'],0).'%' : number_format($mg['GiaTri'],0,',','.').'đ' ?></td>
                                <td>
                                    <div style="font-size:12px; font-weight:600;"><?= $mg['DaDung'] ?> <span style="color:var(--muted); font-weight:normal;">/ <?= $mg['SoLanDung'] ?></span></div>
                                    <div style="width:72px; height:5px; background:var(--panel3); border-radius:3px; margin-top:5px; overflow:hidden;"><div style="height:100%; width:<?= $pct ?>%; background:<?= $barColor ?>;"></div></div>
                                </td>
<td><span style="white-space: nowrap; padding:4px 10px; border-radius:20px; font-size:10px; font-weight:700; text-transform:uppercase; <?= $mg['TrangThai']==1 ? 'background:rgba(34,197,94,.1);color:#4ade80;border:1px solid rgba(34,197,94,.22);' : 'background:rgba(239,68,68,.1);color:#f87171;border:1px solid rgba(239,68,68,.22);' ?>"><?= $mg['TrangThai']==1 ? 'Hoạt động' : 'Vô hiệu' ?></span></td>                                <td style="text-align:right; white-space:nowrap;">
                                    <a href="?toggle_mgg=<?= $mg['MaMGG'] ?>" style="padding:6px 10px; border-radius:7px; text-decoration:none; font-size:11px; <?= $mg['TrangThai']==1 ? 'background:rgba(245,158,11,.1);color:#fbbf24;border:1px solid rgba(245,158,11,.25);' : 'background:rgba(34,197,94,.1);color:#4ade80;border:1px solid rgba(34,197,94,.22);' ?>"><?= $mg['TrangThai']==1 ? '⏸' : '▶' ?></a>
                                    <a href="?xoa_mgg=<?= $mg['MaMGG'] ?>" onclick="return confirm('Xóa mã <?= htmlspecialchars($mg['Code']) ?>?')" style="padding:6px 10px; border-radius:7px; text-decoration:none; font-size:11px; background:rgba(239,68,68,.08);color:#f87171;border:1px solid rgba(239,68,68,.2);">🗑</a>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr><td colspan="5" style="text-align:center; padding:30px; color:var(--muted);">Chưa có mã giảm giá nào</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="card" style="padding:20px; border-color:var(--border);">
                <div class="st" style="font-size:12px;">✚ TẠO MÃ MỚI</div>
                <form method="post">
                    <input type="hidden" name="action" value="add_mgg">
                    <div class="fi"><label>Mã Code *</label><input type="text" name="Code" id="codeInput" placeholder="VD: SALE20" required></div>
                    <div class="fi">
                        <label>Loại giảm *</label>
                        <div style="display:flex; gap:8px; margin-top:4px;">
                            <div style="flex:1; position:relative;"><input type="radio" name="LoaiGiam" id="loai0" value="0" checked onchange="toggleLoai()" style="position:absolute; opacity:0; width:0;"><label for="loai0" style="display:flex; flex-direction:column; align-items:center; padding:10px; border:1.5px solid var(--border); border-radius:9px; cursor:pointer; background:var(--panel3); color:var(--muted);"><span style="font-size:18px;">%</span>Phần trăm</label></div>
                            <div style="flex:1; position:relative;"><input type="radio" name="LoaiGiam" id="loai1" value="1" onchange="toggleLoai()" style="position:absolute; opacity:0; width:0;"><label for="loai1" style="display:flex; flex-direction:column; align-items:center; padding:10px; border:1.5px solid var(--border); border-radius:9px; cursor:pointer; background:var(--panel3); color:var(--muted);"><span style="font-size:18px;">₫</span>Cố định</label></div>
                        </div>
                    </div>
                    <div class="fi"><label id="lblGiaTri">Giá trị giảm *</label><input type="number" name="GiaTri" id="giaTri" placeholder="VD: 10" required></div>
                    <div class="fi" id="wrapToiDa"><label>Giảm tối đa (đ)</label><input type="number" name="GiamToiDa" placeholder="0 = Vô hạn" value="0"></div>
                    <div class="fi"><label>Đơn tối thiểu (đ)</label><input type="number" name="DonToiThieu" value="0"></div>
                    <div class="fi"><label>Số lượt dùng *</label><input type="number" name="SoLanDung" value="100" required></div>
                    <div class="fi full"><label>Ngày hết hạn</label><input type="datetime-local" name="NgayHetHan"></div>
                    <button type="submit" class="btn bp" style="width:100%; margin-top:10px; justify-content:center;">💾 TẠO MÃ</button>
                </form>
            </div>
        </div>
      </div>

     <div id="t_dh" class="tp">
        <div class="st">📦 Quản lý đơn hàng</div>
        <div style="margin-bottom: 15px;">
            <input type="text" id="searchDH" placeholder="🔍 Tìm mã đơn, tên khách, SĐT, trạng thái..." onkeyup="filterAdminTable('searchDH', 'table-dh', 'nav-table-dh')" style="width: 100%; padding: 10px 15px; border-radius: 8px; border: 1px solid var(--border); background: var(--panel2); color: var(--tx); outline: none; font-family: 'Exo 2';">
        </div>
        <?php 
          $cntHuy = sqlsrv_query($conn, "SELECT COUNT(*) as cnt FROM DonHang WHERE TrangThai=N'Chờ xác nhận hủy'");
          $soChoHuy = $cntHuy ? (int)(sqlsrv_fetch_array($cntHuy, SQLSRV_FETCH_ASSOC)['cnt'] ?? 0) : 0;
          $ttColor = ['Chờ xử lý'=>'#f59e0b', 'Đang giao'=>'#3b82f6', 'Đã giao'=>'#22c55e', 'Đã hủy'=>'#ef4444', 'Chờ xác nhận hủy'=>'#f97316', 'Yêu cầu đổi trả'=>'#a855f7', 'Đã hoàn tiền'=>'#0ea5e9', 'Từ chối đổi trả'=>'#ef4444'];
        ?>
        <?php if ($soChoHuy > 0): ?>
        <div style="background:rgba(249,115,22,.1); border:1px solid rgba(249,115,22,.4); border-radius:10px; padding:14px 18px; margin-bottom:20px; font-size:13px; font-weight:600; color:#fb923c;">
          <span style="background:#f97316; color:#fff; border-radius:50%; width:22px; height:22px; display:inline-flex; align-items:center; justify-content:center; font-size:11px; margin-right:8px;"><?= $soChoHuy ?></span> Có <strong><?= $soChoHuy ?> đơn hàng</strong> đang chờ xác nhận hủy!
        </div>
        <?php endif; ?>
        <div style="overflow-x:auto;">
            <table class="stock-table" id="table-dh">
                <thead><tr style="color:var(--cyan);"><th style="padding:10px;">Mã ĐH</th><th style="padding:10px;">Khách Hàng</th><th style="padding:10px;">Tổng Tiền</th><th style="padding:10px;">Trạng Thái / Lý Do</th><th style="padding:10px; text-align:right;">Thao Tác</th></tr></thead>
                <tbody>
                    <?php
                    $q_dh = sqlsrv_query($conn, "SELECT dh.*, nd.TenDangNhap FROM DonHang dh JOIN NguoiDung nd ON dh.MaND=nd.MaND ORDER BY CASE WHEN dh.TrangThai=N'Chờ xác nhận hủy' THEN 0 ELSE 1 END, dh.NgayDat DESC");
                    while($dh = sqlsrv_fetch_array($q_dh, SQLSRV_FETCH_ASSOC)):
                        $c = $ttColor[$dh['TrangThai']] ?? '#888899';
                        $isChoHuy = ($dh['TrangThai'] === 'Chờ xác nhận hủy');
                        $isTraHang = ($dh['TrangThai'] === 'Yêu cầu đổi trả');
                    ?>
                    <tr style="<?= $isChoHuy ? 'background:rgba(249,115,22,.05); border-left:3px solid var(--orange);' : ($isTraHang ? 'background:rgba(168,85,247,.05); border-left:3px solid var(--purple2);' : '') ?>">
                        <td style="font-family:'Orbitron'; padding:10px; color:var(--cyan); font-weight:bold;">#<?= $dh['MaDH'] ?><br><span style="font-size:10px;color:var(--muted);font-family:'Exo 2'"><?= $dh['NgayDat']->format('d/m/Y H:i') ?></span></td>
                        <td style="padding:10px;"><?= htmlspecialchars($dh['HoTen'] ?? '') ?><br><span style="font-size:11px;color:var(--muted);">@<?= htmlspecialchars($dh['TenDangNhap'] ?? '') ?></span><br><span style="font-size:11px;color:var(--muted);">Ghi chú: <?= htmlspecialchars($dh['GhiChu'] ?? 'Không') ?></span></td>
                        <td style="color:var(--purple2); font-weight:bold; padding:10px;"><?= number_format($dh['TongTien'] ?? 0, 0, ',', '.') ?>đ</td>
                        <td style="padding:10px;">
                            <span class="badge-tt" style="background:<?= $c ?>22; color:<?= $c ?>; border:1px solid <?= $c ?>55"><?= htmlspecialchars($dh['TrangThai']) ?></span>
                            <?php if ($isChoHuy && !empty($dh['LyDoHuy'])): ?>
                                <div class="ly-do-box">&#x1F4AC; "<?= htmlspecialchars($dh['LyDoHuy']) ?>"</div>
                            <?php endif; ?>
                            <?php if ($isTraHang && !empty($dh['LyDoTraHang'])): ?>
                                <div class="ly-do-box" style="color:var(--purple2); border-color:rgba(168,85,247,.3); background:rgba(168,85,247,.1);">&#x1F4AC; Khách có gửi Video</div>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:right; padding:10px;">
                            <?php if ($isTraHang): ?>
                                <button type="button" class="btn bg2" style="padding:5px 10px; font-size:11px; color:var(--purple2); border-color:var(--purple2);" 
                                    onclick="openChiTietTraHang(this)" 
                                    data-id="<?= $dh['MaDH'] ?>" 
                                    data-lydo="<?= htmlspecialchars($dh['LyDoTraHang'] ?? '') ?>" 
                                    data-link="<?= htmlspecialchars($dh['LinkVideoProof'] ?? '') ?>">
                                    🔍 Xét duyệt
                                </button>
                            <?php elseif ($isChoHuy): ?>
                                <form method="post" style="display:inline" onsubmit="return confirm('Đồng ý hủy đơn #<?= $dh['MaDH'] ?>?')"><input type="hidden" name="action" value="duyet_huy"><input type="hidden" name="MaDH" value="<?= $dh['MaDH'] ?>"><button type="submit" class="btn-no-huy">&#x2714; Hủy Đơn</button></form>
                                <form method="post" style="display:inline"><input type="hidden" name="action" value="tu_choi_huy"><input type="hidden" name="MaDH" value="<?= $dh['MaDH'] ?>"><button type="submit" class="btn-ok-huy">&#x2715; Từ chối</button></form>
                            <?php else: ?>
                                <form method="post" style="display:flex; gap:5px; justify-content:flex-end;">
                                    <input type="hidden" name="action" value="update_order"><input type="hidden" name="MaDH" value="<?= $dh['MaDH'] ?>">
                                    <select name="TrangThai" style="background:var(--navy); color:var(--tx); border:1px solid var(--border); padding:5px; border-radius:4px; font-size:12px;">
                                        <option value="Chờ xử lý" <?= $dh['TrangThai']==='Chờ xử lý'?'selected':'' ?>>Chờ xử lý</option>
                                        <option value="Đang giao" <?= $dh['TrangThai']==='Đang giao'?'selected':'' ?>>Đang giao</option>
                                        <option value="Đã giao" <?= $dh['TrangThai']==='Đã giao'?'selected':'' ?>>Đã giao</option>
                                    </select>
                                    <button class="btn bp" style="padding:5px 10px; font-size:11px;">LƯU</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
      </div>

      <div id="modalChiTietTraHang" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(5px);">
          <div style="background:var(--panel); border:1px solid var(--border); border-radius:14px; padding:28px; width:90%; max-width:500px; text-align: left;">
              <div style="font-size:16px; font-weight:700; margin-bottom:15px; color:var(--purple2);">🔍 Chi Tiết Yêu Cầu Đổi Trả</div>
              <div style="margin-bottom:10px; font-size:14px;"><strong>Mã đơn hàng:</strong> <span style="color:var(--cyan); font-family:'Orbitron'; font-weight:bold;">#<span id="adTraMaDH"></span></span></div>
              
              <div style="margin-bottom:5px; font-size:14px;"><strong>Lý do & Mô tả:</strong></div>
              <div id="adTraLyDo" style="background:var(--panel2); padding:12px; border-radius:8px; color:var(--tx); font-size:13px; margin-bottom:15px; line-height:1.5; border: 1px solid rgba(168,85,247,0.3);"></div>

              <div style="margin-bottom:5px; font-size:14px;"><strong>Video / Ảnh bằng chứng từ Khách:</strong></div>
              <a id="adTraLink" href="#" target="_blank" style="display:inline-flex; align-items:center; gap: 8px; background:rgba(0,229,255,0.1); color:var(--cyan); padding:12px 20px; border-radius:8px; text-decoration:none; font-size:14px; font-weight:bold; margin-bottom:25px; border:1px solid rgba(0,229,255,0.3); transition:0.3s;">
                  ▶ Click để mở Link Video xem xét
              </a>

              <div style="display:flex; gap:10px; justify-content:flex-end; border-top:1px solid var(--border); padding-top:20px;">
                  <form method="post" style="margin:0;">
                      <input type="hidden" name="action" value="tu_choi_tra_hang">
                      <input type="hidden" name="MaDH" id="adInpTuChoi">
                      <button type="submit" class="btn" style="background:rgba(239,68,68,.1); color:#f87171; border:1px solid rgba(239,68,68,.3);" onclick="return confirm('Xác nhận TỪ CHỐI yêu cầu trả hàng này?')">✖ Từ chối</button>
                  </form>
                  <form method="post" style="margin:0;">
                      <input type="hidden" name="action" value="duyet_tra_hang">
                      <input type="hidden" name="MaDH" id="adInpDuyet">
                      <button type="submit" class="btn bp" style="background:linear-gradient(135deg, var(--purple), var(--purple2)); border:none;" onclick="return confirm('Xác nhận đã nhận lại hàng và HOÀN TIỀN cho khách?')">✔ Duyệt & Hoàn Tiền</button>
                  </form>
                  <button type="button" class="btn bg2" onclick="document.getElementById('modalChiTietTraHang').style.display='none'">Đóng</button>
              </div>
          </div>
      </div>

     <div id="t_nd" class="tp">
        <div class="st">&#x1F6E1; Quản lý người dùng</div>
        <div style="margin-bottom: 15px;">
            <input type="text" id="searchND" placeholder="🔍 Tìm ID, tài khoản, SĐT, tên khách..." onkeyup="filterAdminTable('searchND', 'table-nd', 'nav-table-nd')" style="width: 100%; padding: 10px 15px; border-radius: 8px; border: 1px solid var(--border); background: var(--panel2); color: var(--tx); outline: none; font-family: 'Exo 2';">
        </div>
        <div style="overflow-x:auto;">
            <table class="stock-table" id="table-nd">
                <thead><tr style="color:var(--cyan);"><th style="padding:10px;">ID</th><th style="padding:10px;">Tài Khoản</th><th style="padding:10px;">SĐT</th><th style="padding:10px;">Vai Trò</th><th style="padding:10px; text-align:center;">Trạng Thái</th><th style="padding:10px; text-align:right;">Thao tác</th></tr></thead>
                <tbody>
                    <?php
                    $q_nd = sqlsrv_query($conn, "SELECT * FROM NguoiDung ORDER BY MaND DESC");
                    while($r = sqlsrv_fetch_array($q_nd, SQLSRV_FETCH_ASSOC)):
                        
                        // KIỂM TRA ONLINE / OFFLINE (Nếu tương tác trong vòng 5 phút = 300 giây thì tính là Online)
                        $isOnline = false;
                        if ($r['NgayHoatDong']) {
                            $lastActive = $r['NgayHoatDong']->getTimestamp();
                            if (time() - $lastActive <= 300) {
                                $isOnline = true;
                            }
                        }
                    ?>
                    <tr>
                        <td style="font-family:'Orbitron'; padding:10px;">#<?= $r['MaND'] ?></td>
                        <td style="font-weight:bold; color:var(--tx); padding:10px;">@<?= htmlspecialchars($r['TenDangNhap']) ?><br><span style="font-weight:normal;font-size:11px;color:var(--muted);"><?= htmlspecialchars($r['HoTen']) ?></span></td>
                        <td style="padding:10px;"><?= htmlspecialchars($r['SoDienThoai']) ?></td>
                        <td style="padding:10px;"><span class="rb" style="background: <?= $r['VaiTro']==1 ? 'rgba(168,85,247,0.2)' : 'rgba(0,229,255,0.1)' ?>; color: <?= $r['VaiTro']==1 ? 'var(--purple2)' : 'var(--cyan)' ?>;"><?= $r['VaiTro']==1 ? 'Admin' : 'Khách' ?></span></td>
                        
                        <td style="padding:10px; text-align:center;">
                            <?php if ($r['TrangThai'] == 0): ?>
                                <span style="background:rgba(239,68,68,0.15); color:#f87171; border:1px solid rgba(239,68,68,0.3); padding:4px 10px; border-radius:12px; font-size:11px; font-weight:bold; white-space:nowrap;">🚫 Bị khóa</span>
                            <?php elseif ($isOnline): ?>
                                <span style="background:rgba(34,197,94,0.15); color:#4ade80; border:1px solid rgba(34,197,94,0.3); padding:4px 10px; border-radius:12px; font-size:11px; font-weight:bold; white-space:nowrap;">● Online</span>
                            <?php else: ?>
                                <span style="background:rgba(255,255,255,0.05); color:var(--muted); border:1px solid rgba(255,255,255,0.1); padding:4px 10px; border-radius:12px; font-size:11px; font-weight:bold; white-space:nowrap;">● Offline</span>
                            <?php endif; ?>
                        </td>

                        <td style="padding:10px; text-align:right;">
                            <?php if($r['VaiTro'] != 1): ?>
                            <div style="display:flex; gap:5px; justify-content:flex-end;">
                                <form method="post" style="margin:0;"><input type="hidden" name="action" value="toggle_user"><input type="hidden" name="MaND" value="<?= $r['MaND'] ?>"><input type="hidden" name="TrangThaiMoi" value="<?= $r['TrangThai'] == 1 ? 0 : 1 ?>"><button class="btn" style="padding:5px 10px; font-size:11px; <?= $r['TrangThai'] == 1 ? 'background:rgba(239,68,68,0.1);color:#f87171;border:1px solid rgba(239,68,68,0.3);' : 'background:rgba(34,197,94,0.1);color:#4ade80;border:1px solid rgba(34,197,94,0.3);' ?>"><?= $r['TrangThai'] == 1 ? '🔒 Khóa' : '🔓 Mở' ?></button></form>
                                <form method="post" style="margin:0;" onsubmit="return confirm('Xóa tài khoản này vĩnh viễn?');"><input type="hidden" name="action" value="delete_user"><input type="hidden" name="MaND" value="<?= $r['MaND'] ?>"><button type="submit" class="btn" style="padding:5px 10px; font-size:11px; background:var(--panel2); color:var(--muted); border:1px solid var(--border);">🗑 Xóa</button></form>
                            </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
      </div>

      <div id="t_tn" class="tp">
        <div class="st">&#x1F4AC; Quản lý tin nhắn khách hàng</div>
        <?php
        $sql_users = "SELECT DISTINCT ND.MaND, ND.HoTen FROM NguoiDung ND JOIN TinNhan TN ON ND.MaND = TN.MaNguoiGui OR ND.MaND = TN.MaNguoiNhan WHERE ND.MaND != ?";
        $stmt_users = sqlsrv_query($conn, $sql_users, [$user_id]);
        ?>
        <div class="chat-container" style="display: flex; background: var(--panel2); border: 1px solid var(--border); border-radius: 10px; overflow: hidden; height: 500px;">
            <div class="user-list" style="width: 30%; border-right: 1px solid var(--border); background: var(--panel); overflow-y: auto;">
                <h3 style="padding: 15px; margin: 0; background: rgba(0,229,255,0.1); color: var(--cyan); text-align: center; border-bottom: 1px solid var(--border); font-size: 14px; text-transform: uppercase; position: sticky; top: 0;">Danh sách Chat</h3>
                <div style="padding:10px; background:var(--panel); position:sticky; top:49px; border-bottom: 1px solid var(--border);">
                    <input type="text" id="searchChatUser" placeholder="🔍 Tìm tên khách..." onkeyup="filterChat()" style="width:100%; padding:8px 12px; border-radius:6px; border:1px solid var(--border); background:var(--panel2); color:var(--text); outline:none; font-size:13px;">
                </div>
                
                <script>
                // Hàm lọc người dùng trong danh sách chat
                function filterChat() {
                    let val = document.getElementById('searchChatUser').value.toLowerCase();
                    let items = document.querySelectorAll('.user-item');
                    items.forEach(item => {
                        let name = item.innerText.toLowerCase();
                        item.style.display = name.includes(val) ? 'block' : 'none';
                    });
                }
                </script>
                <?php if($stmt_users): while($u = sqlsrv_fetch_array($stmt_users, SQLSRV_FETCH_ASSOC)): ?>
                    <div class="user-item" onclick="openChat(<?= $u['MaND'] ?>, '<?= htmlspecialchars($u['HoTen']) ?>', this)" style="padding: 15px; border-bottom: 1px solid var(--border); cursor: pointer; font-weight: bold; color: var(--tx); transition: 0.2s;">👤 <?= htmlspecialchars($u['HoTen']) ?></div>
                <?php endwhile; endif; ?>
            </div>
            <div class="chat-area" style="width: 70%; display: flex; flex-direction: column; background: var(--navy);">
                <div class="chat-header" id="chat-header-title" style="padding: 15px; background: rgba(0,229,255,0.05); border-bottom: 1px solid var(--border); font-weight: bold; font-size: 16px; color: var(--cyan); display: flex; align-items: center; justify-content: space-between;">Chọn khách hàng để chat</div>
                <div id="no-chat-selected" style="text-align: center; margin-top: 150px; color: var(--muted);"><h2>👈 Vui lòng chọn khách bên trái</h2></div>
                <div class="chat-history" id="chat-content" style="flex: 1; padding: 20px; overflow-y: auto; display: none;"></div>
                <div class="chat-input" id="chat-input-box" style="padding: 15px; border-top: 1px solid var(--border); display: none; background: var(--panel); gap: 10px;">
                    <input type="hidden" id="current-khach-id" value="">
                    <input type="text" id="txt-admin-msg" placeholder="Nhập câu trả lời..." onkeypress="handleEnter(event)" style="flex: 1; padding: 10px 15px; border: 1px solid var(--border); border-radius: 20px; background: var(--navy); color: var(--tx); outline: none;">
                    <button onclick="sendAdminMsg()" class="btn bp" style="border-radius: 20px; padding: 10px 20px;">Gửi ✈</button>
                </div>
            </div>
        </div>
      </div>
      <?php endif; ?>

<div id="editModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:9999;align-items:center;justify-content:center;backdrop-filter:blur(6px)">
  <div style="background:#0d1f38;border:1px solid rgba(0,229,255,.2);border-radius:14px;padding:28px;width:90%;max-width:620px;max-height:85vh;overflow-y:auto">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
      <div style="font-family:'Orbitron',monospace;font-size:14px;color:var(--cyan)">✏️ CHỈNH SỬA SP</div>
      <button type="button" onclick="closeEdit()" style="background:none;border:none;color:var(--muted);font-size:22px;cursor:pointer">✕</button>
    </div>
    <form method="post" enctype="multipart/form-data">
      <input type="hidden" name="action" value="edit_product">
      <input type="hidden" name="MaSP" id="eMaSP">
      <div class="fg">
        <div class="fi full"><label>Tên SP *</label><input type="text" name="TenSP" id="eTenSP" required></div>
        
        <div class="fi full">
            <label>Đổi Ảnh Sản Phẩm (Bỏ trống nếu không đổi ảnh)</label>
            <input type="file" name="HinhAnhEdit" accept="image/*" style="padding:7px; background:var(--panel);">
        </div>
        
        <div class="fi"><label>Danh mục</label><select name="MaDM" id="eMaDM" onchange="toggleSpecs(this.value, 'e-')"><option value="1">Laptop</option><option value="2">Điện thoại</option><option value="3">PC Gaming</option><option value="4">Phụ Kiện</option><option value="5">Gaming Gear</option></select></div>
        <div class="fi"><label>Giá</label><input type="number" name="Gia" id="eGia" required></div>
        
        <div id="e-grp-dientu" style="display:contents;"><div class="fi"><label>CPU</label><input type="text" name="CPU" id="eCPU"></div><div class="fi"><label>RAM</label><input type="text" name="RAM" id="eRAM"></div><div class="fi"><label>Ổ cứng</label><input type="text" name="O_Cung" id="eOCung"></div><div class="fi"><label>Màn hình</label><input type="text" name="ManHinh" id="eManHinh"></div></div>
        <div id="e-grp-laptop" style="display:contents;"><div class="fi"><label>VGA</label><input type="text" name="VGA" id="eVGA"></div></div>
        <div id="e-grp-phone" style="display:none;"><div class="fi"><label>Camera</label><input type="text" name="Camera" id="eCamera"></div><div class="fi"><label>Pin</label><input type="text" name="Pin" id="ePin"></div></div>
        <div id="e-grp-gear" style="display:none;"><div class="fi"><label>Kết nối</label><input type="text" name="KetNoi" id="eKetNoi"></div><div class="fi"><label>Tương thích</label><input type="text" name="TuongThich" id="eTuongThich"></div></div>
        <div class="fi full"><label>Bảo hành</label><input type="text" name="BaoHanh" id="eBaoHanh"></div>
        <div class="fi full"><label>Mô tả</label><textarea name="MoTa" id="eMoTa"></textarea></div>
      </div>
      <div class="fa"><button type="button" onclick="closeEdit()" class="btn bg2">Hủy</button><button type="submit" class="btn bp">💾 Lưu</button></div>
    </form>
  </div>
</div>

    </div>
  </main>
</div>

<script>
// KHAI BÁO TABS
<?php if ($user['VaiTro'] == 1): ?>
    const panels = { view: 'tv', add_sp: 'ts', update_stock: 'tuk', sua_sp: 'tsp', mgg: 't_mgg', donhang: 't_dh', nguoidung: 't_nd', tinnhan: 't_tn' };
<?php else: ?>
    const panels = { view: 'tv', edit: 'te', vi_voucher: 't_vi_voucher', donhang_khach: 't_dh_khach', yeuthich: 't_yt', diachi: 't_dc' };
<?php endif; ?>

function sw(name, btn) {
    Object.values(panels).forEach(id => { const el = document.getElementById(id); if(el) el.classList.remove('act'); });
    document.querySelectorAll('.tb, .ni').forEach(b => b.classList.remove('act'));
    const target = document.getElementById(panels[name]);
    if(target) { target.classList.add('act'); if(btn) btn.classList.add('act'); }
}
function swn(name){ const btn = document.querySelector('.tb[onclick*="'+name+'"]') || document.querySelector('.ni[onclick*="'+name+'"]'); if(btn) sw(name, btn); }

// CHAT AJAX
let lastAdminChatData = "";
var chatInterval;

function openChat(khachId, khachTen, element) {
    $('.user-item').css({'background': 'transparent', 'border-left': 'none'});
    $(element).css({'background': 'rgba(0,229,255,0.1)', 'border-left': '4px solid var(--cyan)'});
    $('#chat-header-title').text('Đang chat với: ' + khachTen);
    $('#current-khach-id').val(khachId);
    $('#no-chat-selected').hide(); $('#chat-content').show(); $('#chat-input-box').css('display', 'flex');
    
    clearInterval(chatInterval); 
    loadAdminMessages(true); 
    chatInterval = setInterval(function(){ loadAdminMessages(false); }, 2000);
}

function loadAdminMessages(forceScroll = false) {
    var khachId = $('#current-khach-id').val();
    if(khachId !== "") {
        $.ajax({ 
            url: "admin_load_messages.php", 
            type: "GET", 
            data: { id_khach: khachId }, 
            success: function(data) {
                var chatBox = document.getElementById("chat-content"); 
                var isNearBottom = false;
                if(chatBox) {
                    // Nhạy hơn: Nếu thanh cuộn cách đáy < 150px thì coi như ở đáy
                    isNearBottom = (chatBox.scrollHeight - chatBox.clientHeight) <= (chatBox.scrollTop + 150);
                }

                // Phát hiện có tin nhắn mới
                let isNewMessage = (lastAdminChatData !== "" && lastAdminChatData !== data);
                lastAdminChatData = data;
                
                $("#chat-content").html(data); 
                
                if(forceScroll || isNewMessage || isNearBottom) { 
                    setTimeout(function(){
                        if(chatBox) chatBox.scrollTop = chatBox.scrollHeight + 1000;
                    }, 50);
                }
            }
        });
    }
}

function sendAdminMsg() {
    var khachId = $('#current-khach-id').val(); 
    var msg = $('#txt-admin-msg').val();
    if(msg.trim() !== "" && khachId !== "") {
        $.ajax({ 
            url: "admin_send_message.php", 
            type: "POST", 
            data: { id_khach: khachId, noidung: msg }, 
            success: function() {
                $('#txt-admin-msg').val(''); 
                loadAdminMessages(true); 
            }
        });
    }
}
function handleEnter(e) { if(e.keyCode === 13) sendAdminMsg(); }

// UI SẢN PHẨM ADMIN
function toggleSpecs(maDM, prefix) {
    const grpDienTu = document.getElementById(prefix + 'grp-dientu'); const grpLaptop = document.getElementById(prefix + 'grp-laptop');
    const grpPhone = document.getElementById(prefix + 'grp-phone'); const grpGear = document.getElementById(prefix + 'grp-gear');
    if(grpDienTu) grpDienTu.style.display = 'none'; if(grpLaptop) grpLaptop.style.display = 'none';
    if(grpPhone) grpPhone.style.display = 'none'; if(grpGear) grpGear.style.display = 'none';
    maDM = parseInt(maDM);
    if (maDM === 1 || maDM === 3) { if(grpDienTu) grpDienTu.style.display = 'contents'; if(grpLaptop) grpLaptop.style.display = 'contents'; } 
    else if (maDM === 2) { if(grpDienTu) grpDienTu.style.display = 'contents'; if(grpPhone) grpPhone.style.display = 'contents'; } 
    else if (maDM === 4 || maDM === 5) { if(grpGear) grpGear.style.display = 'contents'; }
}
const spData = <?php $allSP = sqlsrv_query($conn, "SELECT * FROM SanPham"); $arr = []; if ($allSP) { while($r = sqlsrv_fetch_array($allSP, SQLSRV_FETCH_ASSOC)) $arr[$r['MaSP']] = $r; } echo json_encode($arr); ?>;
function openEdit(id) {
    const sp = spData[id]; if (!sp) return;
    document.getElementById('eMaSP').value = sp.MaSP; 
    document.getElementById('eTenSP').value = sp.TenSP; 
    document.getElementById('eMaDM').value = sp.MaDM;
    toggleSpecs(sp.MaDM, 'e-'); 
    document.getElementById('eGia').value = sp.Gia; 
    
    // Reset ô File ảnh để không bị dính file cũ lúc sửa sản phẩm khác
    const fileInput = document.querySelector('input[name="HinhAnhEdit"]');
    if (fileInput) fileInput.value = '';
    
    document.getElementById('eCPU').value = sp.CPU || ''; 
    document.getElementById('eRAM').value = sp.RAM || '';
    document.getElementById('eOCung').value = sp.O_Cung || ''; 
    document.getElementById('eManHinh').value = sp.ManHinh || '';
    document.getElementById('eVGA').value = sp.VGA || ''; 
    document.getElementById('eCamera').value = sp.Camera || '';
    document.getElementById('ePin').value = sp.Pin || ''; 
    document.getElementById('eKetNoi').value = sp.KetNoi || '';
    document.getElementById('eTuongThich').value = sp.TuongThich || ''; 
    document.getElementById('eBaoHanh').value = sp.BaoHanh || '';
    document.getElementById('eMoTa').value = sp.MoTa || ''; 
    
    document.getElementById('editModal').style.display = 'flex';
}
function closeEdit() { document.getElementById('editModal').style.display = 'none'; }
function updateProductImage(id) {
    const fileInput = document.getElementById('file-' + id); if (!fileInput.files[0]) return;
    const fd = new FormData(); fd.append('action', 'update_product_img_ajax'); fd.append('id', id); fd.append('HinhAnh', fileInput.files[0]);
    document.getElementById('img-' + id).style.opacity = '0.5';
    fetch('ChinhSuaProfile.php', { method: 'POST', body: fd }).then(r => r.text()).then(res => {
        document.getElementById('img-' + id).style.opacity = '1';
        if (res.trim().startsWith('uploads/')) { document.getElementById('img-' + id).src = res.trim() + '?t=' + new Date().getTime(); } else { alert("❌ Lỗi: " + res); }
    });
}
function saveStock(id) {
    const qty = document.getElementById('stock-' + id).value; const fd = new FormData();
    fd.append('action', 'quick_update_stock'); fd.append('id', id); fd.append('qty', qty);
    fetch('ChinhSuaProfile.php', { method: 'POST', body: fd }).then(r => r.text()).then(txt => { if(txt.trim() === "OK") alert("✅ Thành công!"); else alert("❌ Lỗi: " + txt); });
}

// UI KHÁC
// UI KHÁC - PREVIEW ẢNH ĐẠI DIỆN TRỰC TIẾP
function prevAv(input) { 
    if(!input.files[0]) return; 
    const r = new FileReader(); 
    r.onload = e => { 
        document.getElementById('mai').src = e.target.result; // Áp luôn ảnh mới lên Avatar chính
        document.getElementById('pw').style.display = 'flex'; // Trồi nút Lưu/Hủy ra
        document.getElementById('role-badge').style.display = 'none'; // Tạm ẩn cái chức danh cho đỡ chật
    }; 
    r.readAsDataURL(input.files[0]); 
}

function cancelPrev() { 
    document.getElementById('avi').value = ''; 
    document.getElementById('pw').style.display = 'none'; // Giấu nút Lưu/Hủy đi
    document.getElementById('mai').src = <?= json_encode($avSrc) ?>; // Phục hồi lại ảnh cũ
    document.getElementById('role-badge').style.display = 'inline-block'; // Hiện lại chức danh
}
// MÃ GIẢM GIÁ UI
function toggleLoai() {
    const loai = document.querySelector('input[name=LoaiGiam]:checked').value;
    const lbl = document.getElementById('lblGiaTri'); const inp = document.getElementById('giaTri'); const wrap = document.getElementById('wrapToiDa');
    if (loai === '0') { lbl.innerHTML = 'Phần trăm (%) <span class="req">*</span>'; inp.placeholder = 'VD: 10'; inp.max = '100'; wrap.style.display = 'flex'; } 
    else { lbl.innerHTML = 'Số tiền giảm (đ) <span class="req">*</span>'; inp.placeholder = 'VD: 50000'; inp.removeAttribute('max'); wrap.style.display = 'none'; }
}
const codeInp = document.getElementById('codeInput'); if(codeInp) codeInp.addEventListener('input', function() { this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g,''); });
const giaTriInp = document.getElementById('giaTri'); if(giaTriInp) giaTriInp.addEventListener('input', function() { const loai = document.querySelector('input[name=LoaiGiam]:checked').value; if (loai === '0' && parseFloat(this.value) > 100) this.value = 100; });

// YÊU THÍCH KHÁCH HÀNG UI
function removeFav(maSP) {
    if(confirm("Bạn muốn bỏ thả tim sản phẩm này?")) {
        $.ajax({
            url: 'xu_ly_yeu_thich.php', type: 'POST', data: { id_sanpham: maSP },
            success: function(res) { if(res === 'removed') { $('#fav-' + maSP).fadeOut(300, function() { $(this).remove(); }); } }
        });
    }
}
function openHuyModal(id) { document.getElementById('txtModalMaDH').textContent = id; document.getElementById('inpModalMaDH').value = id; document.getElementById('modalHuyDon').style.display = 'flex'; }

// PHÂN TRANG JAVASCRIPT
// --- PHÂN TRANG VẠN NĂNG (DÙNG CỬA SỔ TRƯỢT 1 2 3 4 5 ❯) ---
function paginateList(containerId, itemSelector, rowsPerPage) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    // Tìm các phần tử cần phân trang (div hoặc tr)
    const items = Array.from(container.querySelectorAll(itemSelector));
    if (items.length === 0) return;

    const totalPages = Math.ceil(items.length / rowsPerPage);
    if (totalPages <= 1) return;

    // Tạo thanh điều hướng nút bấm
    let nav = document.getElementById('nav-' + containerId);
    if(!nav) { 
        nav = document.createElement('div'); 
        nav.id = 'nav-' + containerId; 
        nav.className = 'pagination-nav'; 
        container.parentNode.insertBefore(nav, container.nextSibling); 
    }

    let currentPage = 1;
    const maxVisibleButtons = 5; // Số nút hiển thị tối đa

    function renderPagination() {
        nav.innerHTML = '';
        let startPage = Math.max(1, currentPage - Math.floor(maxVisibleButtons / 2));
        let endPage = Math.min(totalPages, startPage + maxVisibleButtons - 1);

        if (endPage - startPage + 1 < maxVisibleButtons) {
            startPage = Math.max(1, endPage - maxVisibleButtons + 1);
        }

        // Nút Prev ❮
        if (currentPage > 1) {
            const prevBtn = document.createElement('button');
            prevBtn.innerText = '❮';
            prevBtn.onclick = () => { currentPage--; showPage(currentPage); };
            nav.appendChild(prevBtn);
        }

        // Nút số 1, 2, 3...
        for (let i = startPage; i <= endPage; i++) {
            const btn = document.createElement('button');
            btn.innerText = i;
            btn.dataset.page = i;
            if (i === currentPage) btn.classList.add('active');
            btn.onclick = () => { currentPage = i; showPage(currentPage); };
            nav.appendChild(btn);
        }

        // Nút Next ❯
        if (currentPage < totalPages) {
            const nextBtn = document.createElement('button');
            nextBtn.innerText = '❯';
            nextBtn.onclick = () => { currentPage++; showPage(currentPage); };
            nav.appendChild(nextBtn);
        }
    }

    function showPage(page) {
        items.forEach((item, index) => {
            if (index >= (page - 1) * rowsPerPage && index < page * rowsPerPage) {
                item.style.display = ''; // Reset CSS display
            } else {
                item.style.display = 'none';
            }
        });
        renderPagination();
    }

    showPage(1);
}
// --- HÀM TÌM KIẾM THÔNG MINH CHO ADMIN ---
function filterAdminTable(inputId, tableId, navId) {
    let val = document.getElementById(inputId).value.toLowerCase();
    let rows = document.querySelectorAll('#' + tableId + ' tbody tr');
    let nav = document.getElementById(navId);
    
    // Nếu xóa rỗng ô tìm kiếm -> Trả lại phân trang ban đầu
    if (val.trim() === '') {
        if(nav) nav.style.display = 'flex';
        // Bấm tự động vào trang 1 để reset lại view
        let firstBtn = nav ? nav.querySelector('button[data-page="1"]') : null;
        if(firstBtn) firstBtn.click();
        return;
    }
    
    // Đang tìm kiếm -> Ẩn thanh phân trang đi
    if(nav) nav.style.display = 'none';
    
    // Lọc các dòng: Chữ nào khớp thì hiện, không thì ẩn
    rows.forEach(row => {
        let text = row.innerText.toLowerCase();
        row.style.display = text.includes(val) ? '' : 'none';
    });
}
// KHỞI CHẠY PHÂN TRANG CHO TOÀN BỘ CÁC TAB
window.addEventListener('DOMContentLoaded', () => {
    // Phân trang Đơn Hàng Của Khách (6 đơn/trang)
    paginateList('khach-order-list', '.order-item', 6);
    
    // Phân trang Bảng của Admin (10 dòng/trang)
    paginateList('table-mgg', 'tbody tr', 10); 
    paginateList('table-dh', 'tbody tr', 10); 
    paginateList('table-nd', 'tbody tr', 10); 
    paginateList('table-tuk', 'tbody tr', 10); 
    paginateList('table-tsp', 'tbody tr', 10);
});

// TỰ CHUYỂN TAB SAU KHI LÀM MỚI
<?php if(in_array($success, ['add_address', 'delete_address', 'set_default'])): ?> window.onload = () => swn('diachi'); <?php endif; ?>
<?php if($success === 'yeu_cau_huy' || isset($_GET['id_don'])): ?> window.onload = () => swn('donhang_khach'); <?php endif; ?>
<?php if($success==='info'): ?> window.onload = () => swn('view'); <?php endif; ?>
<?php if($success==='product'): ?> window.onload = () => swn('add_sp'); <?php endif; ?>
<?php if($success==='saved_coupon'): ?> window.onload = () => swn('vi_voucher'); <?php endif; ?>
<?php if($success==='mgg'): ?> window.onload = () => swn('mgg'); <?php endif; ?>
<?php if($success==='donhang'): ?> window.onload = () => swn('donhang'); <?php endif; ?>
<?php if($success==='nguoidung'): ?> window.onload = () => swn('nguoidung'); <?php endif; ?>

function openTraHangModal(id) {
    document.getElementById('txtModalTraMaDH').textContent = id;
    document.getElementById('inpModalTraMaDH').value = id;
    document.getElementById('modalTraHang').style.display = 'flex';
}

function openChiTietTraHang(btn) {
    let id = btn.getAttribute('data-id');
    let lydo = btn.getAttribute('data-lydo');
    let link = btn.getAttribute('data-link');

    document.getElementById('adTraMaDH').textContent = id;
    document.getElementById('adTraLyDo').textContent = lydo;
    document.getElementById('adTraLink').href = link;
    
    document.getElementById('adInpTuChoi').value = id;
    document.getElementById('adInpDuyet').value = id;

    document.getElementById('modalChiTietTraHang').style.display = 'flex';
}
</script>
</body>
</html>
<?php sqlsrv_close($conn); ?>
không hiểu sao có lúc nó kéo có lúc nó lỗi không kéo mệt quá thôi cho tôi code khi nó có tin nhắn mới cho nó thông báo số lượng có tin nhắn trên nút chat được không  người ta ấn vô mất số lượng là ok đỡ lỗi lười quá ròi