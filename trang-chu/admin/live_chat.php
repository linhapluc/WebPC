<?php
require('../includes/connect.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_name'])) {
    header("Location: login_admin.php");
    exit();
}
$adminName = $_SESSION['admin_name'];
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title>Hỗ trợ trực tuyến - Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <style>
        /* CSS dành riêng cho khung Chat */
        .chat-container-wrapper {
            display: flex;
            height: calc(100vh - 160px); /* Khớp với chiều cao còn lại của màn hình */
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        /* Danh sách bên trái */
        .chat-list {
            width: 320px;
            border-right: 1px solid #eee;
            display: flex;
            flex-direction: column;
        }
        .chat-list-header {
            padding: 15px;
            background: #f8f9fa;
            border-bottom: 1px solid #eee;
            font-weight: bold;
        }
        #conversation-list {
            flex: 1;
            overflow-y: auto;
        }
        .user-item {
            padding: 15px;
            border-bottom: 1px solid #f1f1f1;
            cursor: pointer;
            transition: 0.2s;
        }
        .user-item:hover { background: #f0f7ff; }
        .user-item.active { background: #e7f1ff; border-left: 4px solid #0d6efd; }
        .unread-count {
            background: #ff4d4f;
            color: white;
            border-radius: 50%;
            padding: 2px 7px;
            font-size: 11px;
            float: right;
        }

        /* Khung chat bên phải */
        .chat-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #fdfdfd;
        }
        .chat-header {
            padding: 15px;
            border-bottom: 1px solid #eee;
            background: #fff;
            font-weight: bold;
        }
        #admin-chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 10px;
            background: #f4f6f9;
        }
        .msg {
            max-width: 70%;
            padding: 10px 15px;
            border-radius: 15px;
            font-size: 14px;
            line-height: 1.4;
        }
        .msg-user {
            align-self: flex-start;
            background: #fff;
            border: 1px solid #ddd;
        }
        .msg-admin {
            align-self: flex-end;
            background: #0d6efd;
            color: #fff;
        }
        .chat-input-area {
            padding: 15px;
            background: #fff;
            border-top: 1px solid #eee;
        }
        #no-chat-selected {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            flex-direction: column;
        }
    </style>
</head>

<body>
    <div class="admin-layout">
        <!-- SIDEBAR (Giống hệt home.php) -->
        <aside class="admin-sidebar">
            <div class="admin-sidebar-header">
                <a href="home.php" class="admin-sidebar-brand">
                    <div class="admin-sidebar-logo">NS</div>
                    <div class="admin-sidebar-title">NASA Admin</div>
                </a>
            </div>
            <nav class="admin-sidebar-nav">
                <a href="home.php" class="admin-nav-item"><i class="fa-solid fa-house"></i><span>Trang chủ</span></a>
                <a href="donhang.php" class="admin-nav-item"><i class="fa-regular fa-clipboard"></i><span>Đơn hàng</span></a>
                <a href="sanpham.php" class="admin-nav-item"><i class="fa-solid fa-box"></i><span>Sản phẩm</span></a>
                <div class="admin-sidebar-divider"></div>
                
                <!-- MENU CHAT MỚI THÊM -->
                <a href="live_chat.php" class="admin-nav-item active">
                    <i class="fa-solid fa-comments"></i>
                    <span>Chat hỗ trợ</span>
                </a>

                <a href="khuyenmai.php" class="admin-nav-item"><i class="fa-solid fa-bullhorn"></i><span>Khuyến mãi</span></a>
                <a href="doanhthu.php" class="admin-nav-item"><i class="fa-solid fa-database"></i><span>La bàn dữ liệu</span></a>
                <a href="qladmin.php" class="admin-nav-item"><i class="fa-regular fa-id-badge"></i><span>Tài khoản</span></a>
                <div class="admin-sidebar-divider"></div>
                <a href="khachhang.php" class="admin-nav-item"><i class="fa-solid fa-users"></i><span>Khách hàng</span></a>
                <a href="danhgia.php" class="admin-nav-item"><i class="fa-regular fa-star"></i><span>Đánh giá</span></a>
                <a href="lienket.php" class="admin-nav-item"><i class="fa-solid fa-link"></i><span>Liên kết</span></a>
                <div class="admin-sidebar-divider"></div>
                <a href="logout.php" class="admin-nav-item admin-sidebar-logout"><i class="fa-solid fa-arrow-right-from-bracket"></i><span>Đăng xuất</span></a>
            </nav>
        </aside>

        <!-- MAIN -->
        <div class="admin-main">
            <header class="admin-topbar">
                <div class="admin-topbar-left">
                    <div class="admin-page-title">Hỗ trợ khách hàng trực tuyến</div>
                </div>
                <div class="admin-topbar-right">
                    <span>👋 Admin: <strong><?php echo htmlspecialchars($adminName); ?></strong></span>
                </div>
            </header>

            <main class="admin-content">
                <div class="chat-container-wrapper">
                    <!-- Cột trái: Danh sách khách -->
                    <div class="chat-list">
                        <div class="chat-list-header">Đang chờ hỗ trợ</div>
                        <div id="conversation-list">
                            <!-- JS load danh sách vào đây -->
                        </div>
                    </div>

                    <!-- Cột phải: Nội dung chat -->
                    <div class="chat-content">
                        <div id="chat-window" style="display: none; height: 100%; flex-direction: column;">
                            <div class="chat-header" id="current-user-name">Đang chat với...</div>
                            <div id="admin-chat-messages">
                                <!-- JS load tin nhắn vào đây -->
                            </div>
                            <div class="chat-input-area">
                                <div class="input-group">
                                    <input type="text" id="admin-chat-input" class="form-control" placeholder="Nhập tin nhắn trả lời...">
                                    <button class="btn btn-primary" id="admin-send-btn"><i class="fa-solid fa-paper-plane"></i> Gửi</button>
                                </div>
                            </div>
                        </div>

                        <div id="no-chat-selected">
                            <i class="fa-regular fa-comments fa-4x mb-3 text-light-emphasis"></i>
                            <p>Chọn một khách hàng để bắt đầu trả lời</p>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        let currentUserId = null;

        function loadConversations() {
            fetch('../api/live_chat_api.php?action=get_conversations')
            .then(res => res.json())
            .then(data => {
                const list = document.getElementById('conversation-list');
                list.innerHTML = '';
                data.forEach(conv => {
                    const div = document.createElement('div');
                    div.className = `user-item ${currentUserId == conv.conversation_id ? 'active' : ''}`;
                    div.onclick = () => selectUser(conv.conversation_id, conv.ho_ten || 'Khách vãng lai');
                    
                    let unread = conv.unread_count > 0 ? `<span class="unread-count">${conv.unread_count}</span>` : '';
                    div.innerHTML = `
                        <div><strong>${conv.ho_ten || 'Khách vãng lai'}</strong> ${unread}</div>
                        <small class="text-muted text-truncate d-block">${conv.last_message || '...'}</small>
                    `;
                    list.appendChild(div);
                });
            });
        }

        function selectUser(id, name) {
            currentUserId = id;
            document.getElementById('no-chat-selected').style.display = 'none';
            document.getElementById('chat-window').style.display = 'flex';
            document.getElementById('current-user-name').innerText = 'Đang hỗ trợ: ' + name;
            fetchMessages();
        }

        function fetchMessages() {
            if (!currentUserId) return;
            fetch(`../api/live_chat_api.php?action=get_messages&conversation_id=${currentUserId}`)
            .then(res => res.json())
            .then(data => {
                const container = document.getElementById('admin-chat-messages');
                container.innerHTML = '';
                data.forEach(msg => {
                    const div = document.createElement('div');
                    div.className = `msg ${msg.sender_type === 'admin' ? 'msg-admin' : 'msg-user'}`;
                    div.innerText = msg.message;
                    container.appendChild(div);
                });
                container.scrollTop = container.scrollHeight;
            });
        }

        function sendMessage() {
            const input = document.getElementById('admin-chat-input');
            const msg = input.value.trim();
            if (!msg || !currentUserId) return;

            const fd = new FormData();
            fd.append('action', 'send_message');
            fd.append('conversation_id', currentUserId);
            fd.append('message', msg);

            fetch('../api/live_chat_api.php', { method: 'POST', body: fd })
            .then(() => {
                input.value = '';
                fetchMessages();
                loadConversations();
            });
        }

        document.getElementById('admin-send-btn').onclick = sendMessage;
        document.getElementById('admin-chat-input').onkeypress = (e) => { if(e.key === 'Enter') sendMessage(); };

        setInterval(loadConversations, 5000); // Cập nhật danh sách mỗi 5 giây
        setInterval(fetchMessages, 3000);    // Cập nhật tin nhắn mỗi 3 giây
        loadConversations();
    </script>
</body>
</html>