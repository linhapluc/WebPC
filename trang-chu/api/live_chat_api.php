<?php
require '../includes/connect.php'; 
if (session_status() === PHP_SESSION_NONE) { session_start(); }
header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$is_admin = isset($_SESSION['admin_name']);
$sender_type = $is_admin ? 'admin' : 'user';

// Ép kiểu lấy ID: Thử mọi cách để có ID
$conversation_id = $_REQUEST['conversation_id'] ?? $_SESSION['user_id'] ?? $_SESSION['id_khachhang'] ?? session_id();

switch ($action) {
    case 'send_message':
        $message = trim($_POST['message'] ?? '');
        if (!empty($message)) {
            $sql = "INSERT INTO chat_live (conversation_id, sender_type, message) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $success = $stmt->execute([$conversation_id, $sender_type, $message]);
            echo json_encode(['status' => $success ? 'success' : 'error']);
        }
        break;

    case 'get_messages':
        $sql = "SELECT * FROM chat_live WHERE conversation_id = ? ORDER BY timestamp ASC";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$conversation_id]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($messages);
        break;
    
    // Giữ nguyên các case khác...
    case 'get_conversations':
        if (!$is_admin) { exit; }
        $sql = "SELECT lc.conversation_id, kh.ho_ten, 
                (SELECT message FROM chat_live WHERE conversation_id = lc.conversation_id ORDER BY timestamp DESC LIMIT 1) as last_message,
                SUM(CASE WHEN lc.is_read_by_admin = 0 AND lc.sender_type = 'user' THEN 1 ELSE 0 END) as unread_count
                FROM chat_live lc LEFT JOIN khachhang kh ON lc.conversation_id = kh.id_khachhang
                GROUP BY lc.conversation_id ORDER BY last_message DESC";
        $stmt = $conn->query($sql);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        break;
}