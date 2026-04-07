<?php // includes/footer.php ?>
<footer class="site-footer">
    <div class="container">
        <div class="footer-columns">
            <div class="footer-col"><h4>Giới thiệu TNS</h4><ul><li><a href="gioi-thieu.php">Về chúng tôi</a></li><li><a href="https://topdev.vn/">Tuyển dụng</a></li><li><a href="chinh-sach-bao-mat.php">Bảo mật</a></li></ul></div>
            <div class="footer-col"><h4>Chính sách chung</h4><ul><li><a href="chinh-sach-bao-mat.php">Bảo mật</a></li><li><a href="chinh-sach-giao-nhan.php">Giao nhận</a></li><li><a href="tragop.php">Trả góp</a></li><li><a href="chinh-sach-thanh-toan.php">Thanh toán</a></li><li><a href="chinh-sach-khieu-nai.php">Khiếu nại</a></li><li><a href="chinh-sach-bao-ve-thong-tin.php">Bảo vệ TTCN</a></li><li><a href="chinh-sach-bao-hanh.php">Đổi - trả hàng</a></li></ul></div>
            <div class="footer-col"><h4>Hỗ trợ khách hàng</h4><ul><li><a href="lien-he.php">Hotline CSKH</a></li></ul></div>
            <div class="footer-col"><h4>Kết nối</h4><ul><li><a href="https://www.facebook.com/hutechuniversity">Facebook</a></li><li><a href="https://www.youtube.com/@hutechuniversity">Youtube</a></li><li><a href="https://www.tiktok.com/@hutechuniversity">Tiktok</a></li></ul><p style="margin-top: 15px;"><strong>Hotline:</strong> 1900 1155</p><p><strong>Email:</strong> yhiennguyeny@gmail.com</p></div>
        </div>
        <div class="footer-bottom"><p>© <?php echo date("Y"); ?> Cửa hàng PCshopNasa.</p><p>Tập đoàn PC Shop Nasa | MST: 0123456789 | Đ/c: Khu CNC, Q.9, TP.HCM</p></div>
    </div>
</footer>

<div class="fixed-social-icons">
    <a href="https://m.me/YOUR_FACEBOOK_PAGE_ID" target="_blank" class="social-icon messenger-icon" title="Chat qua Messenger">
        <i class="fab fa-facebook-messenger"></i>
    </a>
    
    <!-- ĐÃ THAY TIKTOK THÀNH HỖ TRỢ TRỰC TUYẾN TẠI ĐÂY -->
    <button id="openChatBtn" class="social-icon support-icon" title="Hỗ trợ trực tuyến" style="background-color: #ff9800; border: none; color: white; cursor: pointer;">
        <i class="fas fa-headset"></i>
    </button>

    <button id="scrollToTopBtn" class="social-icon scroll-top-icon" title="Lên đầu trang">
        <i class="fas fa-arrow-up"></i>
    </button>
</div>

<!-- KHUNG CHAT LIVE (Ẩn mặc định) -->
<div id="liveChatWidget" class="live-chat-widget" style="display: none;">
    <div class="chat-header">
        <span><i class="fas fa-headset"></i> Hỗ trợ trực tuyến</span>
        <button id="closeChatBtn">&times;</button>
    </div>
    <div id="chat-messages-container" class="chat-body">
        <div class="message-bubble left">Chào bạn! PC Shop Nasa có thể giúp gì cho bạn?</div>
    </div>
    <div class="chat-footer">
        <input type="text" id="user-chat-input" placeholder="Nhập tin nhắn...">
        <button id="user-send-button"><i class="fas fa-paper-plane"></i></button>
    </div>
</div>

<style>
/* Đảm bảo icon mới khớp với các icon cũ */
.support-icon { display: flex; align-items: center; justify-content: center; font-size: 20px; }

/* CSS Khung Chat */
.live-chat-widget {
    position: fixed; bottom: 90px; right: 20px; width: 320px; height: 450px;
    background: white; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    display: flex; flex-direction: column; z-index: 10000; overflow: hidden; font-family: sans-serif;
}
.chat-header { background: #0d6efd; color: white; padding: 15px; display: flex; justify-content: space-between; align-items: center; font-weight: bold; }
.chat-header button { background: none; border: none; color: white; font-size: 24px; cursor: pointer; }
.chat-body { flex: 1; padding: 15px; overflow-y: auto; background: #f8f9fa; display: flex; flex-direction: column; gap: 10px; }
.chat-footer { padding: 10px; display: flex; background: white; border-top: 1px solid #eee; }
.chat-footer input { flex: 1; border: 1px solid #ddd; padding: 8px 12px; border-radius: 20px; outline: none; }
.chat-footer button { background: #0d6efd; color: white; border: none; width: 40px; height: 40px; border-radius: 50%; margin-left: 8px; cursor: pointer; }

/* Bong bóng chat */
.message-bubble { padding: 10px 14px; border-radius: 18px; max-width: 80%; font-size: 14px; line-height: 1.4; }
.message-bubble.left { align-self: flex-start; background: #e9ecef; color: #333; border-bottom-left-radius: 2px; }
.message-bubble.right { align-self: flex-end; background: #0d6efd; color: white; border-bottom-right-radius: 2px; }
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const chatWidget = document.getElementById('liveChatWidget');
    const openBtn = document.getElementById('openChatBtn');
    const closeBtn = document.getElementById('closeChatBtn');
    const chatInput = document.getElementById('user-chat-input');
    const sendBtn = document.getElementById('user-send-button');
    const chatContainer = document.getElementById('chat-messages-container');

    // Mở/Đóng chat
    openBtn.onclick = () => {
        chatWidget.style.display = (chatWidget.style.display === 'none') ? 'flex' : 'none';
        fetchMessages();
    };
    closeBtn.onclick = () => chatWidget.style.display = 'none';

    // Gửi tin nhắn
    function sendMessage() {
        const msg = chatInput.value.trim();
        if (!msg) return;

        const fd = new FormData();
        fd.append('action', 'send_message');
        fd.append('message', msg);

        fetch('api/live_chat_api.php', { method: 'POST', body: fd })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                chatInput.value = '';
                fetchMessages();
            } else {
                console.error("Lỗi gửi tin nhắn:", data.error);
            }
        })
        .catch(err => console.error("Lỗi kết nối API:", err));
    }

    sendBtn.onclick = sendMessage;
    chatInput.onkeypress = (e) => { if(e.key === 'Enter') sendMessage(); };

    // Lấy tin nhắn
    function fetchMessages() {
        if (chatWidget.style.display === 'none') return;
        fetch('api/live_chat_api.php?action=get_messages')
        .then(res => res.json())
        .then(data => {
            chatContainer.innerHTML = '';
            data.forEach(m => {
                const div = document.createElement('div');
                div.className = `message-bubble ${m.sender_type === 'user' ? 'right' : 'left'}`;
                div.innerText = m.message;
                chatContainer.appendChild(div);
            });
            chatContainer.scrollTop = chatContainer.scrollHeight;
        });
    }
    setInterval(fetchMessages, 4000);
});
</script>

<!-- Link JS của Bootstrap 5 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/script.js"></script>
</body>
</html>