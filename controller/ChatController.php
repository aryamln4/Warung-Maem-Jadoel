<?php
declare(strict_types=1);
/** Chat per pesanan untuk pelanggan dan admin (polling ringan). */
class ChatController {
    private function customerId(): int {
        if (empty($_SESSION['customer_id'])) redirect(APP_URL.'/index.php?r=auth/login');
        return (int)$_SESSION['customer_id'];
    }
    private function adminGuard(): void {
        if (empty($_SESSION['admin_id'])) redirect(APP_URL.'/index.php?r=auth/admin-login');
    }
    private function json(array $data,
    int $code = 200): void {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data,
        JSON_UNESCAPED_UNICODE);
        exit;
    }
    private function ownedThread(int $threadId,
    int $customerId): array {
        $thread = (new ChatModel())->thread($threadId);
        if (!$thread || (int)$thread['pelanggan_id'] !== $customerId) {
            http_response_code(404);
            exit('Ruang chat tidak ditemukan.');
        }
        return $thread;
    }
    public function index(): void {
        $cid = $this->customerId();
        $threads = (new ChatModel())->threadsForCustomer($cid);
        $title = 'Chat';
        require BASE_PATH.'/view/chat/index.php';
    }
    public function thread(): void {
        $cid = $this->customerId();
        $chat = new ChatModel();
        $threadId = (int)($_GET['id'] ?? 0);
        $orderId = (int)($_GET['order'] ?? 0);
        if (!$threadId && $orderId) {
            $order = (new OrderModel())->customerOrder($orderId,
            $cid);
            if (!$order) {
                flash('error',
                'Pesanan tidak ditemukan.');
                redirect(APP_URL.'/index.php?r=order/history');
            }
            $threadId = (int)$chat->threadForOrder($orderId,
            $cid)['id'];
        }
        $thread = $this->ownedThread($threadId,
        $cid);
        $chat->markRead($threadId,
        'PELANGGAN');
        $messages = $chat->messages($threadId);
        $threads = $chat->threadsForCustomer($cid);
        $title = 'Chat Pesanan';
        require BASE_PATH.'/view/chat/thread.php';
    }
    public function send(): void {
        $cid = $this->customerId();
        verify_csrf();
        $threadId = (int)($_POST['thread_id'] ?? 0);
        $this->ownedThread($threadId,
        $cid);
        try {
            $id = (new ChatModel())->send($threadId,
            'PELANGGAN',
            (string)($_POST['isi'] ?? ''));
            if (!empty($_POST['ajax'])) $this->json(['ok' => true,
            'id' => $id]);
        }  catch (Throwable $e) {
            if (!empty($_POST['ajax'])) $this->json(['ok' => false,
            'message' => $e->getMessage()],
            422);
        }
        redirect(APP_URL.'/index.php?r=chat/thread&id='.$threadId);
    }
    public function fetch(): void {
        $cid = $this->customerId();
        $threadId = (int)($_GET['id'] ?? 0);
        $this->ownedThread($threadId,
        $cid);
        $chat = new ChatModel();
        $after = (int)($_GET['after'] ?? 0);
        $messages = $chat->messages($threadId,
        $after);
        $chat->markRead($threadId,
        'PELANGGAN');
        $this->json(['ok' => true,
        'messages' => $messages]);
    }
    // ---------------------------------------------------------------
    // Admin
    // ---------------------------------------------------------------
    public function adminIndex(): void {
        $this->adminGuard();
        $threads = (new ChatModel())->allThreads();
        $title = 'Chat Pelanggan';
        require BASE_PATH.'/view/admin/chat.php';
    }
    public function adminThread(): void {
        $this->adminGuard();
        $chat = new ChatModel();
        $threadId = (int)($_GET['id'] ?? 0);
        if (!$threadId && !empty($_GET['order'])) {
            $orderId = (int)$_GET['order'];
            $order = (new OrderModel())->adminOrderDetail($orderId);
            if (!$order) {
                flash('error',
                'Pesanan tidak ditemukan.');
                redirect(APP_URL.'/index.php?r=admin/chat');
            }
            $threadId = (int)$chat->threadForOrder($orderId,
            (int)$order['pelanggan_id'])['id'];
        }
        $thread = $chat->thread($threadId);
        if (!$thread) {
            flash('error',
            'Ruang chat tidak ditemukan.');
            redirect(APP_URL.'/index.php?r=admin/chat');
        }
        $chat->markRead($threadId,
        'ADMIN');
        $messages = $chat->messages($threadId);
        $threads = $chat->allThreads();
        $title = 'Chat · '.$thread['pelanggan'];
        require BASE_PATH.'/view/admin/chat_thread.php';
    }
    public function adminSend(): void {
        $this->adminGuard();
        verify_csrf();
        $threadId = (int)($_POST['thread_id'] ?? 0);
        try {
            $id = (new ChatModel())->send($threadId,
            'ADMIN',
            (string)($_POST['isi'] ?? ''));
            if (!empty($_POST['ajax'])) $this->json(['ok' => true,
            'id' => $id]);
        }  catch (Throwable $e) {
            if (!empty($_POST['ajax'])) $this->json(['ok' => false,
            'message' => $e->getMessage()],
            422);
        }
        redirect(APP_URL.'/index.php?r=admin/chat-thread&id='.$threadId);
    }
    public function adminFetch(): void {
        $this->adminGuard();
        $threadId = (int)($_GET['id'] ?? 0);
        $chat = new ChatModel();
        $messages = $chat->messages($threadId,
        (int)($_GET['after'] ?? 0));
        $chat->markRead($threadId,
        'ADMIN');
        $this->json(['ok' => true,
        'messages' => $messages]);
    }
    public function adminUnread(): void {
        $this->adminGuard();
        $this->json(['ok' => true,
        'unread' => (new ChatModel())->unreadForAdmin()]);
    }
}
