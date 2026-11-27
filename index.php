<?php
/**
 * Warung Maem Jadoel
 * Front Controller - Lazy Initialization
 */

try {
	require_once __DIR__ . '/config/app.php';
    $route = (string) ($_GET['r'] ?? 'landing');

    switch ($route) {

        case 'landing':
            require BASE_PATH . '/view/landing.php';
            break;

        case 'auth/register':
            $auth = new AuthController(new AuthModel());
            $auth->register();
            break;

        case 'auth/login':
            $auth = new AuthController(new AuthModel());
            $auth->login();
            break;

        case 'auth/admin-login':
            $auth = new AuthController(new AuthModel());
            $auth->adminLogin();
            break;

        case 'auth/logout':
            $auth = new AuthController(new AuthModel());
            $auth->logout();
            break;

        case 'auth/admin-logout':
            $auth = new AuthController(new AuthModel());
            $auth->adminLogout();
            break;

        case 'auth/profile':
            $auth = new AuthController(new AuthModel());
            $auth->profile();
            break;

        case 'auth/forgot':
            $auth = new AuthController(new AuthModel());
            $auth->forgot();
            break;

        case 'auth/reset':
            $auth = new AuthController(new AuthModel());
            $auth->reset();
            break;

        case 'order/menu':
            $orders = new OrderController(
                new MenuModel(),
                new OrderModel(),
                new RewardService(
                    Database::connection(),
                    new RewardModel()
                )
            );
            $orders->menu();
            break;

        case 'order/create':
            $orders = new OrderController(
                new MenuModel(),
                new OrderModel(),
                new RewardService(Database::connection(), new RewardModel())
            );
            $orders->create();
            break;

        case 'order/checkout':
            $orders = new OrderController(
                new MenuModel(),
                new OrderModel(),
                new RewardService(Database::connection(), new RewardModel())
            );
            $orders->checkout();
            break;

        case 'order/apply-voucher':
            $orders = new OrderController(
                new MenuModel(),
                new OrderModel(),
                new RewardService(Database::connection(), new RewardModel())
            );
            $orders->applyVoucher();
            break;

        case 'order/remove-voucher':
            $orders = new OrderController(
                new MenuModel(),
                new OrderModel(),
                new RewardService(Database::connection(), new RewardModel())
            );
            $orders->removeVoucher();
            break;

        case 'order/status':
            $orders = new OrderController(
                new MenuModel(),
                new OrderModel(),
                new RewardService(Database::connection(), new RewardModel())
            );
            $orders->status();
            break;

        case 'order/qris-image':
            $orders = new OrderController(
                new MenuModel(),
                new OrderModel(),
                new RewardService(Database::connection(), new RewardModel())
            );
            $orders->qrisImage();
            break;

        case 'order/confirm-qris':
            $orders = new OrderController(
                new MenuModel(),
                new OrderModel(),
                new RewardService(Database::connection(), new RewardModel())
            );
            $orders->confirmQris();
            break;

        case 'order/history':
            $orders = new OrderController(
                new MenuModel(),
                new OrderModel(),
                new RewardService(Database::connection(), new RewardModel())
            );
            $orders->history();
            break;

        case 'order/detail':
            $orders = new OrderController(
                new MenuModel(),
                new OrderModel(),
                new RewardService(Database::connection(), new RewardModel())
            );
            $orders->detail();
            break;

        case 'customer/address':
            $customer = new CustomerController();
            $customer->address();
            break;

        case 'customer/save-address':
            $customer = new CustomerController();
            $customer->saveAddress();
            break;

        case 'customer/delete-address':
            $customer = new CustomerController();
            $customer->deleteAddress();
            break;

        case 'customer/primary-address':
            $customer = new CustomerController();
            $customer->setPrimaryAddress();
            break;

        case 'customer/cart':
            $customer = new CustomerController();
            $customer->cart();
            break;

        case 'cart/add':
            $customer = new CustomerController();
            $customer->cartAdd();
            break;

        case 'cart/update':
            $customer = new CustomerController();
            $customer->cartUpdate();
            break;

        case 'cart/remove':
            $customer = new CustomerController();
            $customer->cartRemove();
            break;

        case 'chat':
            $chat = new ChatController();
            $chat->index();
            break;

        case 'chat/thread':
            $chat = new ChatController();
            $chat->thread();
            break;

        case 'chat/send':
            $chat = new ChatController();
            $chat->send();
            break;

        case 'chat/fetch':
            $chat = new ChatController();
            $chat->fetch();
            break;

        case 'admin/chat':
            $chat = new ChatController();
            $chat->adminIndex();
            break;

        case 'admin/chat-thread':
            $chat = new ChatController();
            $chat->adminThread();
            break;

        case 'admin/chat-send':
            $chat = new ChatController();
            $chat->adminSend();
            break;

        case 'admin/chat-fetch':
            $chat = new ChatController();
            $chat->adminFetch();
            break;

        case 'admin/chat-unread':
            $chat = new ChatController();
            $chat->adminUnread();
            break;

        case 'review/create':
            $review = new ReviewController();
            $review->create();
            break;

        case 'review/mine':
            $review = new ReviewController();
            $review->mine();
            break;

        case 'admin/review-status':
            $review = new ReviewController();
            $review->setStatus();
            break;

        case 'admin/dashboard':
            $admin = new AdminController();
            $admin->dashboard();
            break;

        case 'admin/orders':
            $admin = new AdminController();
            $admin->orders();
            break;

        case 'admin/confirm-ongkir':
            $admin = new AdminController();
            $admin->confirmOngkir();
            break;

        case 'admin/set-courier':
            $admin = new AdminController();
            $admin->setCourier();
            break;

        case 'admin/running':
            $admin = new AdminController();
            $admin->runningJson();
            break;

        case 'admin/order-detail':
            $admin = new AdminController();
            $admin->orderDetail();
            break;

        case 'payment/qris-webhook':
            $admin = new AdminController();
            $admin->qrisWebhook();
            break;

        case 'admin/confirm-cash':
            $admin = new AdminController();
            $admin->confirmCash();
            break;

        case 'admin/check-payment':
            $admin = new AdminController();
            $admin->checkPayment();
            break;

        case 'admin/status':
            $admin = new AdminController();
            $admin->status();
            break;

        case 'admin/bonuses':
            $admin = new AdminController();
            $admin->bonuses();
            break;

        case 'admin/save-bonus':
            $admin = new AdminController();
            $admin->saveBonus();
            break;

        case 'admin/delete-bonus':
            $admin = new AdminController();
            $admin->deleteBonus();
            break;

        case 'admin/finance':
            $admin = new AdminController();
            $admin->finance();
            break;

        case 'admin/upload-qris':
            $admin = new AdminController();
            $admin->uploadQris();
            break;

        case 'admin/add-finance':
            $admin = new AdminController();
            $admin->addFinance();
            break;

        case 'admin/inventory':
            $admin = new AdminController();
            $admin->inventory();
            break;

        case 'admin/operations':
            $admin = new AdminController();
            $admin->operations();
            break;

        case 'admin/save-expense':
            $admin = new AdminController();
            $admin->saveExpense();
            break;

        case 'admin/delete-expense':
            $admin = new AdminController();
            $admin->deleteExpense();
            break;

        case 'admin/save-store':
            $admin = new AdminController();
            $admin->saveStore();
            break;

        case 'admin/save-recipe':
            $admin = new AdminController();
            $admin->saveRecipe();
            break;

        case 'admin/save-voucher':
            $admin = new AdminController();
            $admin->saveVoucher();
            break;

        case 'admin/delete-voucher':
            $admin = new AdminController();
            $admin->deleteVoucher();
            break;

        case 'admin/adjust-stock':
            $admin = new AdminController();
            $admin->adjustStock();
            break;

        case 'admin/save-bahan':
            $admin = new AdminController();
            $admin->saveBahan();
            break;

        case 'admin/update-bahan':
            $admin = new AdminController();
            $admin->updateBahan();
            break;

        case 'admin/delete-bahan':
            $admin = new AdminController();
            $admin->deleteBahan();
            break;

        case 'admin/add-bahan-stock':
            $admin = new AdminController();
            $admin->addBahanStock();
            break;

        case 'admin/cancel-purchase':
            $admin = new AdminController();
            $admin->cancelPurchase();
            break;

        case 'admin/menu':
            $admin = new AdminController();
            $admin->menu();
            break;

        case 'admin/save-menu':
            $admin = new AdminController();
            $admin->saveMenu();
            break;

        case 'admin/delete-menu':
            $admin = new AdminController();
            $admin->deleteMenu();
            break;

        case 'admin/hard-delete-menu':
            $admin = new AdminController();
            $admin->hardDeleteMenu();
            break;

        case 'admin/restore-menu':
            $admin = new AdminController();
            $admin->restoreMenu();
            break;

        case 'admin/save-category':
            $admin = new AdminController();
            $admin->saveCategory();
            break;

        case 'admin/delete-category':
            $admin = new AdminController();
            $admin->deleteCategory();
            break;

        case 'admin/save-topping':
            $admin = new AdminController();
            $admin->saveTopping();
            break;

        case 'admin/delete-topping':
            $admin = new AdminController();
            $admin->deleteTopping();
            break;

        case 'admin/tables':
            $admin = new AdminController();
            $admin->tables();
            break;

        case 'admin/customers':
            $admin = new AdminController();
            $admin->customers();
            break;

        case 'admin/toggle-customer':
            $admin = new AdminController();
            $admin->toggleCustomer();
            break;

        case 'admin/add-table':
            $admin = new AdminController();
            $admin->addTable();
            break;

        case 'admin/edit-table':
            $admin = new AdminController();
            $admin->editTable();
            break;

        case 'admin/delete-table':
            $admin = new AdminController();
            $admin->deleteTable();
            break;

        case 'admin/toggle-table':
            $admin = new AdminController();
            $admin->toggleTable();
            break;

        case 'admin/export':
            $admin = new AdminController();
            $admin->export();
            break;

        default:
            http_response_code(404);
            $title = '404';
            require BASE_PATH . '/view/errors/404.php';
            break;
    }

 } catch (Throwable $e) {

    http_response_code(500);
    error_log(
        '[Warung Maem Jadoel] ' . $e->getMessage() .
        ' in ' . $e->getFile() . ':' . $e->getLine() .
        "\\nStack Trace: " . $e->getTraceAsString()
    );

    $isDebug = (($_ENV['APP_DEBUG'] ?? '0') === '1');
    $message = $e->getMessage();
    $isSetupError = str_contains($message, 'Konfigurasi database belum lengkap')
        || str_contains($message, 'Koneksi ke database gagal');
    if ($isDebug) {
        echo '<h2>ERROR PHP APLIKASI (MODE DEBUG)</h2><pre>';
        echo htmlspecialchars(
            $e->getMessage() . "\\nFile: " . $e->getFile() .
            "\\nLine: " . $e->getLine() . "\\n\\n" . $e->getTraceAsString(),
            ENT_QUOTES, 'UTF-8'
        );
        echo '</pre>';
    } elseif ($isSetupError) {
        echo '<main style="max-width:720px;margin:48px auto;padding:24px;font-family:system-ui;line-height:1.6">';
        echo '<h1>Konfigurasi database belum siap.</h1>';
        echo '<p>Buat file <code>.env</code> dari <code>.env.example</code>, lalu isi nilai berikut persis seperti di panel hosting:</p>';
        echo '<ul><li><code>DB_HOST</code> (contoh: sqlXXX.infinityfree.com)</li>';
        echo '<li><code>DB_NAME</code> (contoh: if0_XXXXXXXX_maem)</li>';
        echo '<li><code>DB_USER</code> (contoh: if0_XXXXXXXX)</li>';
        echo '<li><code>DB_PASS</code> (password database)</li></ul>';
        echo '<p>Import juga <code>database/database.sql</code> ke database tersebut melalui phpMyAdmin.</p>';
        echo '<p>Setelah itu muat ulang halaman ini.</p>';
        echo '</main>';
    } else {
        echo '<main style="max-width:720px;margin:48px auto;padding:24px;font-family:system-ui">';
        echo '<h1>Website sedang mengalami kendala.</h1>';
        echo '<p>Silakan coba kembali beberapa saat lagi. Jika Anda pengelola website, periksa konfigurasi database dan error log hosting.</p>';
        echo '</main>';
    }
}
