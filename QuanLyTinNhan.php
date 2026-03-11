<?php
session_start();

$serverName = "localhost\\SQLEXPRESS";
$database   = "QLBanHang";

$connectionInfo = [
    "Database" => $database,
    "TrustServerCertificate" => true
];

$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Giả sử Admin có MaND = 1. Nếu chưa đăng nhập hoặc không phải admin thì chặn lại.
if (!isset($_SESSION['MaND']) || $_SESSION['MaND'] != 1) {
    die("<h3>Bạn không có quyền truy cập trang này. Vui lòng đăng nhập bằng tài khoản Admin!</h3>");
}

// Lấy danh sách các khách hàng đã từng nhắn tin với Admin
$sql_users = "SELECT DISTINCT ND.MaND, ND.HoTen 
              FROM NguoiDung ND
              JOIN TinNhan TN ON ND.MaND = TN.MaNguoiGui OR ND.MaND = TN.MaNguoiNhan
              WHERE ND.MaND != 1"; // Bỏ qua chính Admin
$stmt_users = sqlsrv_query($conn, $sql_users);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý tin nhắn khách hàng</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f7f6; margin: 0; padding: 20px; }
        .chat-container { display: flex; max-width: 1000px; margin: 0 auto; background: #fff; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); overflow: hidden; height: 600px; }
        /* Cột bên trái: Danh sách khách */
        .user-list { width: 30%; border-right: 1px solid #ddd; background: #fafafa; overflow-y: auto; }
        .user-list h3 { padding: 15px; margin: 0; background: #007bff; color: white; text-align: center; }
        .user-item { padding: 15px; border-bottom: 1px solid #eee; cursor: pointer; font-weight: bold; }
        .user-item:hover { background: #e9ecef; }
        .user-item.active { background: #d0e8ff; border-left: 4px solid #007bff; }
        /* Cột bên phải: Khung chat */


        a.back {
    border: 2px solid #242342;
    border-radius: 8px;
    width: 100px;
    height: 30px;
    display: flex;
    justify-content: center;
    text-decoration: none;
    color: #000000;
    align-items: center;
}
        .chat-area { width: 70%; display: flex; flex-direction: column; }
        .chat-header { padding: 15px; background: #fff; border-bottom: 1px solid #ddd; font-weight: bold; font-size: 18px; color: #333;      display: flex;  align-items: center;
    justify-content: space-between;  }
        .chat-history { flex: 1; padding: 20px; overflow-y: auto; background: #fff; }
        .chat-input { padding: 15px; border-top: 1px solid #ddd; display: flex; background: #fafafa; }
        .chat-input input { flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 5px; outline: none; }
        .chat-input button { background: #007bff; color: white; border: none; padding: 10px 20px; margin-left: 10px; border-radius: 5px; cursor: pointer; font-weight: bold; }
        .chat-input button:hover { background: #0056b3; }
        #no-chat-selected { text-align: center; margin-top: 200px; color: #888; }
    </style>
</head>
<body>

<div class="chat-container">
    <div class="user-list">
        <h3>Danh sách Chat</h3>
        <?php while($user = sqlsrv_fetch_array($stmt_users, SQLSRV_FETCH_ASSOC)) { ?>
            <div class="user-item" onclick="openChat(<?php echo $user['MaND']; ?>, '<?php echo $user['HoTen']; ?>', this)">
                👤 <?php echo $user['HoTen']; ?>
            </div>
        <?php } ?>
    </div>

    <div class="chat-area">
        <div class="chat-header" id="chat-header-title">Chọn một khách hàng để bắt đầu chat 
              <a href="ChinhSuaProfile.php" class="back">&#x2190; Ho so</a>

        </div>
        
        <div id="no-chat-selected">
            <h2>👈 Vui lòng chọn khách hàng bên trái</h2>
        </div>

        <div class="chat-history" id="chat-content" style="display: none;">
            </div>

        <div class="chat-input" id="chat-input-box" style="display: none;">
            <input type="hidden" id="current-khach-id" value="">
            <input type="text" id="txt-admin-msg" placeholder="Nhập câu trả lời..." onkeypress="handleEnter(event)">
            <button onclick="sendAdminMsg()">Gửi</button>
        </div>
    </div>
</div>

<script>
    var chatInterval;

    function openChat(khachId, khachTen, element) {
        // Đổi màu user được chọn
        $('.user-item').removeClass('active');
        $(element).addClass('active');

        // Gán thông tin vào khung bên phải
        $('#chat-header-title').text('Đang chat với: ' + khachTen);
        $('#current-khach-id').val(khachId);
        $('#no-chat-selected').hide();
        $('#chat-content').show();
        $('#chat-input-box').show();

        // Xóa bộ đếm cũ nếu có, tải tin nhắn mới và bắt đầu lặp
        clearInterval(chatInterval);
        loadAdminMessages(); 
        chatInterval = setInterval(loadAdminMessages, 2000); // Quét 2s/lần
    }

    function loadAdminMessages() {
        var khachId = $('#current-khach-id').val();
        if(khachId !== "") {
            $.ajax({
                url: "admin_load_messages.php",
                type: "GET",
                data: { id_khach: khachId },
                success: function(data) {
                    var chatBox = $("#chat-content");
                    var isAtBottom = chatBox[0].scrollHeight - chatBox[0].clientHeight <= chatBox[0].scrollTop + 20;
                    
                    chatBox.html(data);
                    if(isAtBottom) {
                        chatBox.scrollTop(chatBox[0].scrollHeight);
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
                    loadAdminMessages();
                    setTimeout(function(){
                        var chatBox = $("#chat-content");
                        chatBox.scrollTop(chatBox[0].scrollHeight);
                    }, 100);
                }
            });
        }
    }

    function handleEnter(e) {
        if(e.keyCode === 13) sendAdminMsg();
    }
</script>

</body>
</html>