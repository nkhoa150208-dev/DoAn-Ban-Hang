<?php
session_start();
include "config.php"; 


// 1. CẤU HÌNH APP FACEBOOK (NHỚ DÁN LẠI ID VÀ SECRET CỦA BẠN NHÉ)
$app_id = '1576585920091889'; // Dán ID
$app_secret = '11472af9c855fa0b045dc28e52222bb2'; // Dán Khóa bí mật
$redirect_uri = 'https://doanbanhang.io.vn/DoAn-Ban-Hang/fb_callback.php';

// --- HÀM VŨ KHÍ BÍ MẬT: cURL (Giúp vượt qua lỗi SSL của XAMPP) ---
function get_data_from_url($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // TẮT KIỂM TRA BẢO MẬT ĐỂ XAMPP KHÔNG CHẶN
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

if (isset($_GET['code'])) {
    $code = $_GET['code'];

    // 2. Đổi code lấy Token
    $token_url = "https://graph.facebook.com/v19.0/oauth/access_token?client_id={$app_id}&redirect_uri=" . urlencode($redirect_uri) . "&client_secret={$app_secret}&code={$code}";
    
    // Dùng hàm cURL thay vì file_get_contents
    $response = get_data_from_url($token_url);
    $params = json_decode($response, true);
    
    if (isset($params['access_token'])) {
        $access_token = $params['access_token'];

        // 3. Lấy thông tin khách hàng từ Facebook
        $graph_url = "https://graph.facebook.com/me?fields=id,name,email,picture.type(large)&access_token={$access_token}";
        $user_info_json = get_data_from_url($graph_url);
        $user_info = json_decode($user_info_json, true);

        if (isset($user_info['id'])) {
            $fb_id = $user_info['id'];
            $fb_name = $user_info['name'];
// Tự tạo một email ảo duy nhất nếu Facebook không cung cấp
$fb_email = $user_info['email'] ?? ($fb_id . '@fb.com');            $fb_avatar = $user_info['picture']['data']['url'] ?? '';

            // 4. KIỂM TRA XEM KHÁCH NÀY ĐÃ TỪNG ĐĂNG NHẬP BẰNG FB CHƯA
            $sql_check = "SELECT * FROM NguoiDung WHERE FacebookID = ?";
            $stmt_check = sqlsrv_query($conn, $sql_check, [$fb_id]);
            
            if ($stmt_check && sqlsrv_has_rows($stmt_check)) {
                // ĐÃ CÓ TÀI KHOẢN -> ĐĂNG NHẬP LUÔN
                $row = sqlsrv_fetch_array($stmt_check, SQLSRV_FETCH_ASSOC);
                
                $_SESSION['MaND']        = $row['MaND'];
                $_SESSION['TenDangNhap'] = $row['TenDangNhap'];
                $_SESSION['HoTen']       = $row['HoTen'];
                $_SESSION['VaiTro']      = $row['VaiTro'];
                
                header("Location: TrangChuDaDangNhap.php");
                exit;
           } else {
                // CHƯA CÓ TÀI KHOẢN -> TỰ ĐỘNG TẠO TÀI KHOẢN MỚI
// Lấy trọn bộ ID Facebook làm tên đăng nhập để không bao giờ bị trùng
$username_moi = 'fb_' . $fb_id;                $mk_ao = 'facebook_login'; 
                $mk_3lop = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT); // Tự động tạo 4 số ngẫu nhiên cho MK 3 Lớp
                
                $sql_insert = "INSERT INTO NguoiDung (TenDangNhap, MatKhau, MatKhau3Lop, HoTen, Email, FacebookID, Avatar, VaiTro) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, 0)";
                $stmt_insert = sqlsrv_query($conn, $sql_insert, [$username_moi, $mk_ao, $mk_3lop, $fb_name, $fb_email, $fb_id, $fb_avatar]);
                
                if ($stmt_insert) {
                    $sql_get_new = "SELECT * FROM NguoiDung WHERE FacebookID = ?";
                    $stmt_new = sqlsrv_query($conn, $sql_get_new, [$fb_id]);
                    $row_new = sqlsrv_fetch_array($stmt_new, SQLSRV_FETCH_ASSOC);
                    
                    $_SESSION['MaND']        = $row_new['MaND'];
                    $_SESSION['TenDangNhap'] = $row_new['TenDangNhap'];
                    $_SESSION['HoTen']       = $row_new['HoTen'];
                    $_SESSION['VaiTro']      = 0; 
                    
                    header("Location: TrangChuDaDangNhap.php");
                    exit;
                } else {
                    // In ra lỗi chi tiết nếu SQL vẫn từ chối
                    die("Lỗi tạo tài khoản vào CSDL: <pre>" . print_r(sqlsrv_errors(), true) . "</pre>");
                }
            }
        } else {
            die("Không lấy được dữ liệu cá nhân từ Facebook! Lỗi: " . $user_info_json);
        }
    } else {
        // NẾU LỖI NÓ SẼ IN RA CHI TIẾT BỊ SAI Ở ĐÂU
        die("Lỗi lấy Token từ Facebook! Chi tiết Facebook trả về: " . $response);
    }
} else {
    echo "Lỗi đăng nhập Facebook: Không nhận được Code!";
}
?>