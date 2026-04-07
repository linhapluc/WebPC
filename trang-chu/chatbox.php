<!-- 🌟 Chatbox AI WebPC – phiên bản hiện đại -->
<style>
    :root {
        --chat-primary: #6c63ff;
        --chat-primary-dark: #5a54d6;
        --chat-bg: #f7f8ff;
        --chat-radius: 18px;
    }

    #chatbox {
        position: fixed;
        bottom: 20px;
        right: 25px;
        z-index: 9999;
        font-family: 'Poppins', 'Segoe UI', sans-serif;
    }

    /* Nút mở chat */
    #chat-toggle {
        position: fixed;
        bottom: 150px;
        right: 18px;
        width: 64px;
        height: 64px;
        border-radius: 50%;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        background: radial-gradient(circle at 30% 20%, #ffffff 0, #d1cfff 35%, #6c63ff 100%);
        color: #fff;
        cursor: grab;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.35);
        transition: transform 0.25s ease, box-shadow 0.25s ease, background 0.25s ease;
    }

    #chat-toggle:hover {
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 14px 40px rgba(15, 23, 42, 0.4);
        background: radial-gradient(circle at 30% 20%, #ffffff 0, #c2beff 35%, #5a54d6 100%);
    }

    #chat-toggle:active {
        cursor: grabbing;
        transform: scale(0.97);
    }

    /* Khung chat */
    #chat-window {
        position: fixed;
        bottom: 20px;
        right: 10px;
        width: 370px;
        height: 530px;
        display: none;
        flex-direction: column;
        border-radius: var(--chat-radius);
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.96), rgba(245, 247, 255, 0.96));
        backdrop-filter: blur(18px);
        -webkit-backdrop-filter: blur(18px);
        box-shadow: 0 18px 45px rgba(15, 23, 42, 0.35);
        overflow: hidden;
        transform-origin: bottom right;
        animation: chat-pop 0.25s ease;
    }

    @keyframes chat-pop {
        from {
            opacity: 0;
            transform: scale(0.9) translateY(10px);
        }

        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    /* Header */
    .chat-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 14px;
        background: linear-gradient(135deg, var(--chat-primary), var(--chat-primary-dark));
        color: #fff;
        cursor: move;
        /* để user hiểu có thể kéo ở đây */
    }

    .chat-header-left {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .chat-header img {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #fff;
        padding: 4px;
    }

    .chat-title {
        font-size: 14px;
        font-weight: 600;
        line-height: 1.2;
    }

    .chat-subtitle {
        font-size: 11px;
        opacity: 0.9;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .chat-dot-online {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #4ade80;
        box-shadow: 0 0 0 4px rgba(74, 222, 128, 0.35);
    }

    .chat-header-close {
        border: none;
        background: transparent;
        color: #e5e7ff;
        font-size: 18px;
        cursor: pointer;
        padding: 4px;
        border-radius: 999px;
        transition: background 0.2s ease, transform 0.2s ease;
    }

    .chat-header-close:hover {
        background: rgba(15, 23, 42, 0.25);
        transform: scale(1.05);
    }

    /* Nội dung tin nhắn */
    #chat-messages {
        flex: 1;
        padding: 14px 16px 10px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 10px;
        scrollbar-width: thin;
        scrollbar-color: #c4c6ff transparent;
    }

    #chat-messages::-webkit-scrollbar {
        width: 6px;
    }

    #chat-messages::-webkit-scrollbar-thumb {
        background: #c4c6ff;
        border-radius: 999px;
    }

    .msg {
        max-width: 82%;
        padding: 10px 13px;
        border-radius: 14px;
        font-size: 14px;
        line-height: 1.5;
        word-break: break-word;
    }

    .msg-ai {
        align-self: flex-start;
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid #e1e5ff;
        position: relative;
        padding-left: 40px;
    }

    .msg-ai::before {
        content: "";
        background: url('https://cdn-icons-png.flaticon.com/512/4712/4712035.png') no-repeat center/cover;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        position: absolute;
        left: 10px;
        top: 10px;
        box-shadow: 0 0 0 2px #f1f0ff;
    }

    .msg-user {
        align-self: flex-end;
        background: linear-gradient(135deg, var(--chat-primary), var(--chat-primary-dark));
        color: #fff;
        border-bottom-right-radius: 4px;
        box-shadow: 0 6px 16px rgba(88, 80, 236, 0.35);
    }

    /* Input */
    .chat-input {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 10px;
        border-top: 1px solid #e1e5ff;
        background: rgba(249, 250, 255, 0.98);
    }

    .chat-input input {
        flex: 1;
        border: none;
        outline: none;
        padding: 9px 11px;
        border-radius: 999px;
        font-size: 13px;
        background: #f1f2ff;
        color: #111827;
    }

    .chat-input input::placeholder {
        color: #9ca3af;
        font-size: 12px;
    }

    .chat-input button {
        border: none;
        border-radius: 999px;
        padding: 9px 14px;
        background: linear-gradient(135deg, var(--chat-primary), var(--chat-primary-dark));
        color: #fff;
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        box-shadow: 0 7px 18px rgba(88, 80, 236, 0.35);
        transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
    }

    .chat-input button:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 24px rgba(88, 80, 236, 0.45);
        background: linear-gradient(135deg, #5a54d6, #4f46e5);
    }

    .chat-input button:active {
        transform: scale(0.97);
    }

    .chat-input button svg {
        width: 15px;
        height: 15px;
    }

    /* Typing dots */
    .typing {
        width: 6px;
        height: 6px;
        background: #a6a1ff;
        border-radius: 999px;
        margin: 0 2px;
        animation: blink 1.2s infinite;
        display: inline-block;
    }

    .typing:nth-child(2) {
        animation-delay: 0.2s;
    }

    .typing:nth-child(3) {
        animation-delay: 0.4s;
    }

    @keyframes blink {

        0%,
        80%,
        100% {
            opacity: 0.25;
        }

        40% {
            opacity: 1;
        }
    }

    /* ===== Chat Teaser Bubble (simple one-column) ===== */
    #chat-teaser {
        position: fixed;
        bottom: 150px;
        /* vị trí so với nút chat */
        right: 90px;
        width: 310px;
        /* rộng hơn để chữ không bị gãy xấu */

        background: #ffffff;
        border-radius: 16px;
        padding: 10px 14px;
        font-size: 13px;
        color: #111827;
        line-height: 1.45;

        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.22);
        border: 1px solid rgba(226, 232, 255, 0.9);

        display: flex;
        align-items: flex-start;
        gap: 8px;

        opacity: 0;
        transform: translateY(12px) scale(0.96);
        pointer-events: none;
        transition: all .28s ease-out;
        z-index: 9999;
    }

    /* Đuôi bubble chĩa vào nút chat */
    #chat-teaser::after {
        content: "";
        position: absolute;
        right: -7px;
        bottom: 18px;
        border-width: 8px;
        border-style: solid;
        border-color: transparent transparent transparent #ffffff;
        filter: drop-shadow(-2px 2px 4px rgba(15, 23, 42, 0.15));
    }

    #chat-teaser.show {
        opacity: 1;
        transform: translateY(0) scale(1);
        pointer-events: auto;
    }

    .chat-teaser-text {
        flex: 1;
    }

    .chat-teaser-emoji {
        margin-right: 4px;
    }

    .chat-teaser-text b.brand {
        color: #4f46e5;
        /* tím tiệp gradient nút */
    }

    .chat-teaser-close {
        margin-left: 4px;
        border: none;
        background: transparent;
        font-size: 14px;
        cursor: pointer;
        color: #9ca3af;
        align-self: flex-start;
        padding: 0;
        line-height: 1;
    }

    .chat-teaser-close:hover {
        color: #4b5563;
    }
</style>

<div id="chatbox">
    <div id="chat-window">
        <div class="chat-header">
            <div class="chat-header-left">
                <img src="https://cdn-icons-png.flaticon.com/512/4712/4712035.png" alt="AI">
                <div>
                    <div class="chat-title">Trợ lý AI WebPC</div>
                    <div class="chat-subtitle">
                        <span class="chat-dot-online"></span>
                        <span> Chatbox AI luôn hỗ trợ bạn</span>
                    </div>
                </div>
            </div>
            <button type="button" class="chat-header-close" id="chat-close" aria-label="Đóng">
                ×
            </button>
        </div>

        <div id="chat-messages"></div>

        <div class="chat-input">
            <input id="chat-input" type="text" placeholder="Nhập câu hỏi về sản phẩm, giá, cấu hình..." />
            <button id="chat-send" type="button">
                <svg viewBox="0 0 24 24" fill="none">
                    <path d="M4 4L20 12L4 20L7 12L4 4Z" stroke="white" stroke-width="2" stroke-linejoin="round" />
                </svg>
                Gửi
            </button>
        </div>
    </div>

    <button id="chat-toggle">💬</button>
    <div id="chat-teaser">
        <div class="chat-teaser-text">
            <span class="chat-teaser-emoji">👋</span>
            <span>
                Xin chào bạn!<br>
                <b class="brand">AI Shop Nasa</b> đang online, bạn có cần mình tư vấn hay giúp chọn sản phẩm không? 😊
            </span>
        </div>
        <button type="button" class="chat-teaser-close" aria-label="Đóng">×</button>
    </div>



</div>

<script>
    const chatToggle = document.getElementById('chat-toggle');
    const chatWindow = document.getElementById('chat-window');
    const chatMessages = document.getElementById('chat-messages');
    const chatInput = document.getElementById('chat-input');
    const chatSend = document.getElementById('chat-send');
    const chatClose = document.getElementById('chat-close');

    let isSending = false;
    let justDragged = false;


    // 🟣 Toggle mở/đóng chat
    function toggleChat(openForce = null) {
        const isOpen = chatWindow.style.display === 'flex';
        const shouldOpen = (openForce === null) ? !isOpen : openForce;

        chatWindow.style.display = shouldOpen ? 'flex' : 'none';

        if (shouldOpen) {
            chatInput.focus();
            if (!chatWindow.dataset.greeted) {
                const typing = showTyping();
                setTimeout(() => {
                    typing.remove();
                    addMessage(
                        "🤖 Xin chào! Mình là <b>Trợ lý AI Shop Nasa</b> 💻<br>" +
                        "Bạn có thể hỏi về <b>bàn phím, chuột, màn hình, cấu hình, giá...</b> mình sẽ gợi ý sản phẩm phù hợp cho bạn 😄",
                        "ai"
                    );
                    chatWindow.dataset.greeted = "true";
                }, 900);
            }
        }
    }

    chatToggle.addEventListener('click', () => {
        if (justDragged) return;
        toggleChat();
    });

    chatClose.addEventListener('click', () => toggleChat(false));

    // 📨 Gửi tin nhắn
    chatSend.onclick = sendMessage;
    chatInput.addEventListener('keypress', e => {
        if (e.key === 'Enter') {
            e.preventDefault();
            sendMessage();
        }
    });

    async function sendMessage() {
        const msg = chatInput.value.trim();
        if (!msg || isSending) return;

        addMessage(msg, 'user');
        chatInput.value = '';
        chatInput.focus();

        const typing = showTyping();
        isSending = true;
        chatSend.disabled = true;

        try {
            const res = await fetch('api/chat_process.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    message: msg
                })
            });

            let data;
            try {
                data = await res.json();
            } catch {
                data = {
                    reply: "<b style='color:red;'>Lỗi:</b> Dữ liệu trả về không hợp lệ."
                };
            }

            typing.remove();
            addMessage(data.reply || 'Mình chưa hiểu rõ câu hỏi, bạn mô tả chi tiết hơn giúp mình nha 😅', 'ai');
        } catch {
            typing.remove();
            addMessage('<b style="color:red;">Lỗi:</b> Không thể kết nối tới máy chủ.', 'ai');
        } finally {
            isSending = false;
            chatSend.disabled = false;
        }
    }

    function addMessage(text, sender) {
        const msg = document.createElement('div');
        msg.className = `msg msg-${sender}`;
        msg.innerHTML = text;
        chatMessages.appendChild(msg);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function showTyping() {
        const div = document.createElement('div');
        div.className = 'msg msg-ai';
        div.innerHTML = "<span class='typing'></span><span class='typing'></span><span class='typing'></span>";
        chatMessages.appendChild(div);
        chatMessages.scrollTop = chatMessages.scrollHeight;
        return div;
    }

    /* 🟣 Kéo nút chat (button tròn) và lưu vị trí */
    let isDragging = false;
    let hasMoved = false;
    let offsetX = 0;
    let offsetY = 0;

    chatToggle.addEventListener('mousedown', startDrag);
    document.addEventListener('mousemove', onDrag);
    document.addEventListener('mouseup', endDrag);

    chatToggle.addEventListener('touchstart', startDrag);
    document.addEventListener('touchmove', onDrag, {
        passive: false
    });
    document.addEventListener('touchend', endDrag);

    function startDrag(e) {
        const ev = e.touches ? e.touches[0] : e;
        const rect = chatToggle.getBoundingClientRect();

        offsetX = ev.clientX - rect.left;
        offsetY = ev.clientY - rect.top;

        isDragging = true;
        hasMoved = false;
        chatToggle.style.transition = 'none';
    }

    function onDrag(e) {
        if (!isDragging) return;

        const ev = e.touches ? e.touches[0] : e;
        hasMoved = true;

        if (e.cancelable) e.preventDefault();

        const x = ev.clientX - offsetX;
        const y = ev.clientY - offsetY;

        chatToggle.style.left = x + 'px';
        chatToggle.style.top = y + 'px';
        chatToggle.style.right = 'auto';
        chatToggle.style.bottom = 'auto';
    }

    function endDrag() {
        if (!isDragging) return;
        isDragging = false;
        chatToggle.style.transition = 'transform 0.25s ease, box-shadow 0.25s ease';

        if (hasMoved) {
            // chỉ để tránh click mở chat ngay sau khi kéo
            justDragged = true;
            setTimeout(() => {
                justDragged = false;
            }, 200);
        }
    }

    /* 🟣 Kéo luôn cả Ô CHAT bằng header */
    const chatHeader = document.querySelector('#chat-window .chat-header');
    let isDraggingWin = false,
        winMoved = false,
        winOffX = 0,
        winOffY = 0;

    if (chatHeader) {
        chatHeader.addEventListener('mousedown', startWinDrag);
        document.addEventListener('mousemove', onWinDrag);
        document.addEventListener('mouseup', stopWinDrag);

        chatHeader.addEventListener('touchstart', startWinDrag);
        document.addEventListener('touchmove', onWinDrag, {
            passive: false
        });
        document.addEventListener('touchend', stopWinDrag);
    }

    function startWinDrag(e) {
        const ev = e.touches ? e.touches[0] : e;
        const rect = chatWindow.getBoundingClientRect();
        isDraggingWin = true;
        winMoved = false;
        winOffX = ev.clientX - rect.left;
        winOffY = ev.clientY - rect.top;
        chatWindow.style.transition = 'none';
    }

    function onWinDrag(e) {
        if (!isDraggingWin) return;
        const ev = e.touches ? e.touches[0] : e;
        winMoved = true;
        if (e.cancelable) e.preventDefault();

        const x = ev.clientX - winOffX;
        const y = ev.clientY - winOffY;

        chatWindow.style.left = x + 'px';
        chatWindow.style.top = y + 'px';
        chatWindow.style.right = 'auto';
        chatWindow.style.bottom = 'auto';
    }

    function stopWinDrag() {
        if (!isDraggingWin) return;
        isDraggingWin = false;
        chatWindow.style.transition = 'all 0.2s ease';
    }

    /* ======🎉 Chat Teaser Bubble====== */
    const teaser = document.getElementById("chat-teaser");
    const teaserClose = document.querySelector(".chat-teaser-close");

    // ⏱️ Teaser hiện sau 1.6s MỖI LẦN load trang
    setTimeout(() => {
        if (teaser) {
            teaser.classList.add("show");
        }
    }, 1600);

    // Bấm teaser → mở chat (vẫn đóng bubble, nhưng reload lại sẽ hiện nữa)
    teaser.addEventListener("click", () => {
        teaser.classList.remove("show");
        toggleChat(true);
    });

    // Bấm nút X → chỉ ẩn bubble hiện tại, reload lại sẽ xuất hiện tiếp
    teaserClose.addEventListener("click", (e) => {
        e.stopPropagation();
        teaser.classList.remove("show");
    });
</script>