
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
        .chat-area { width: 70%; display: flex; flex-direction: column; }
        .chat-header {padding: 15px;background: #fff;border-bottom: 1px solid #ddd;font-weight: bold;font-size: 18px;color: #333;display: flex;justify-content: space-between;align-items: center;}

a.back77 {
    border: 3px solid #b9b9b9;
    border-radius: 8px;
    width: 100px;
    height: 30px;
    display: flex;
    justify-content: center;
    text-decoration: none;
    color: black;
    align-items: center;
}
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
                    <div class="user-item" onclick="openChat(6, 'Tien', this)">
                👤 Tien            </div>
                    <div class="user-item" onclick="openChat(7, 'Nguyen Dinh Khoa', this)">
                👤 Nguyen Dinh Khoa            </div>
            </div>

    <div class="chat-area">
        <div class="chat-header" id="chat-header-title">Chọn một khách hàng để bắt đầu chat 
              <a href="ChinhSuaProfile.php" class="back77">&#x2190; Hồ Sơ</a>

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

<script defer src="https://static.cloudflareinsights.com/beacon.min.js/v8c78df7c7c0f484497ecbca7046644da1771523124516" integrity="sha512-8DS7rgIrAmghBFwoOTujcf6D9rXvH8xm8JQ1Ja01h9QX8EzXldiszufYa4IFfKdLUKTTrnSFXLDkUEOTrZQ8Qg==" data-cf-beacon='{"version":"2024.11.0","token":"b272e33e38b74216b8d84cd2915abed9","r":1,"server_timing":{"name":{"cfCacheStatus":true,"cfEdge":true,"cfExtPri":true,"cfL4":true,"cfOrigin":true,"cfSpeedBrain":true},"location_startswith":null}}' crossorigin="anonymous"></script>
<script defer src="https://static.cloudflareinsights.com/beacon.min.js/v8c78df7c7c0f484497ecbca7046644da1771523124516" integrity="sha512-8DS7rgIrAmghBFwoOTujcf6D9rXvH8xm8JQ1Ja01h9QX8EzXldiszufYa4IFfKdLUKTTrnSFXLDkUEOTrZQ8Qg==" data-cf-beacon='{"version":"2024.11.0","token":"b272e33e38b74216b8d84cd2915abed9","r":1,"server_timing":{"name":{"cfCacheStatus":true,"cfEdge":true,"cfExtPri":true,"cfL4":true,"cfOrigin":true,"cfSpeedBrain":true},"location_startswith":null}}' crossorigin="anonymous"></script>
</body>
</html>