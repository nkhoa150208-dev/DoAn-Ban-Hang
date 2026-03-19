<?php
session_start();

$app_id = '1576585920091889'; // Dán ID bạn vừa lấy ở Bước 2 vào đây
$redirect_uri = 'https://doanbanhang.io.vn/DoAn-Ban-Hang/fb_callback.php'; 

// Chuyển hướng sang trang ủy quyền của Facebook
$fb_login_url = "https://www.facebook.com/v19.0/dialog/oauth?client_id=" . $app_id . "&redirect_uri=" . urlencode($redirect_uri) . "&scope=public_profile";
header("Location: " . $fb_login_url);
exit;
?>