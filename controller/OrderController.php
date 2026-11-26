<?php
declare(strict_types=1);
class OrderController {
    public function __construct(private MenuModel $menu,
    private OrderModel $orders,
    private RewardService $reward) {
    }
    private function customerId(): int {
        if (empty($_SESSION['customer_id'])) redirect(APP_URL.'/index.php?r=auth/login');
        return (int)$_SESSION['customer_id'];
    }
    private function normalizeItems(array $items): array {
        $normalized = [];
        $total = 0;
        $qty = 0;
        foreach ($items as $i) {
            $m = $this->menu->byId((int)($i['menu_id'] ?? 0));
            if (!$m || !(int)$m['status_aktif']) continue;
            $q = max(1,
            (int)($i['qty'] ?? 1));
            $toppingId = !empty($i['topping_id']) ? (int)$i['topping_id'] : null;
            $level = isset($i['level_pedas']) && $i['level_pedas'] !== null ? (int)$i['level_pedas'] : null;
            if ($m['punya_level_pedas'] && ($level === null || $level < 0 || $level > 5)) {
                flash('error',
                'Level pedas tidak valid.');
                redirect(APP_URL.'/index.php?r=order/menu');
            }
            if (!$m['punya_topping']) $toppingId = null;
            $extra = 0;
            $toppingName = null;
            if ($toppingId) {
                $allowed = false;
                foreach ($this->menu->toppingsFor((int)$m['id']) as $t) {
                    if ((int)$t['id'] === $toppingId) {
                        $extra = (float)$t['harga_tambahan'];
                        $toppingName = $t['nama'];
                        $allowed = true;
                        break;
                    }
                }
                if (!$allowed) {
                    flash('error',
                    'Topping tidak valid.');
                    redirect(APP_URL.'/index.php?r=order/menu');
                }
            }
            $unit = (float)$m['harga'] + $extra;
            $sub = $unit * $q;
            $total += $sub;
            $qty += $q;
            $normalized[] = ['menu_id' => (int)$m['id'],
            'nama_produk' => $m['nama_produk'],
            'qty' => $q,
            'unit_price' => $unit,
            'subtotal' => $sub,
            'topping_id' => $toppingId,
            'topping_name' => $toppingName,
            'level_pedas' => $level,
            'is_gratis' => 0];
        }
        if (!$normalized) {
            flash('error',
            'Keranjang kosong atau item tidak valid.');
            redirect(APP_URL.'/index.php?r=order/menu');
        }
        return ['items' => $normalized,
        'total' => $total,
        'qty' => $qty];
    }
    private function currentCart(int $cid): array {
        $data = (new CartModel())->checkoutItems($cid);
        if (!$data['items'] && !empty($_SESSION['checkout']['items'])) {
            $data = ['items' => $_SESSION['checkout']['items'],
            'total' => (float)$_SESSION['checkout']['total'],
            'qty' => (int)($_SESSION['checkout']['qty'] ?? 0)];
        }
        return $data;
    }
    public function menu(): void {
        $cid = $this->customerId();
        $menus = $this->menu->allActive();
        $toppings = $this->menu->toppings();
        $menuToppings = [];
        foreach ($menus as $m) $menuToppings[(int)$m['id']] = $this->menu->toppingsFor((int)$m['id']);
        $cart = new CartModel();
        $cartItems = $cart->items($cid);
        $cartCount = $cart->count($cid);
        $cartSubtotal = 0.0;
        foreach ($cartItems as $i) $cartSubtotal += (float)$i['subtotal'];
        $promo = (new VoucherModel())->activePromo();
        $settings = (new ShippingModel())->settings();
        $title = 'Beranda';
        require BASE_PATH.'/view/order/menu.php';
    }
    public function create(): void {
        $cid = $this->customerId();
        verify_csrf();
        $checkout = $this->currentCart($cid);
        if (!$checkout['items']) {
            flash('error',
            'Keranjang masih kosong. Silakan pilih menu lagi.');
            redirect(APP_URL.'/index.php?r=order/menu');
        }
        $orderType = in_array($_POST['jenis_pesanan'] ?? '',
        ['DINE_IN',
        'TAKEAWAY',
        'DELIVERY'],
        true) ? $_POST['jenis_pesanan'] : 'DINE_IN';
        $tableId = null;
        if ($orderType === 'DINE_IN') {
            $tableId = (int)($_POST['meja_id'] ?? 0);
            $table = $tableId ? $this->orders->findActiveTable($tableId) : null;
            if (!$table) {
                flash('error',
                'Meja tidak valid. Silakan pilih meja yang tersedia.');
                redirect(APP_URL.'/index.php?r=order/checkout');
            }
        }
        $items = $checkout['items'];
        $subtotal = (float)$checkout['total'];
        foreach ($items as $i) {
            $s = Database::connection()->prepare("SELECT jumlah_stok FROM stok_produk WHERE menu_id=?");
            $s->execute([$i['menu_id']]);
            if ((int)$s->fetchColumn() < $i['qty']) {
                flash('error',
                'Stok tidak cukup untuk salah satu produk.');
                redirect(APP_URL.'/index.php?r=order/checkout');
            }
        }
        $extra = ['sumber_pesanan' => 'WEBSITE',
        'ongkir' => 0.0,
        'potongan_voucher' => 0.0];
        $address = null;
        if ($orderType === 'DELIVERY') {
            $addressId = (int)($_POST['alamat_id'] ?? 0);
            $address = (new AddressModel())->find($addressId,
            $cid);
            if (!$address) {
                flash('error',
                'Pilih alamat pengiriman terlebih dahulu.');
                redirect(APP_URL.'/index.php?r=order/checkout');
            }
            if ($address['latitude'] === null || $address['longitude'] === null) {
                flash('error',
                'Alamat belum memiliki titik lokasi. Lengkapi alamat terlebih dahulu.');
                redirect(APP_URL.'/index.php?r=order/checkout');
            }
            $quote = (new ShippingModel())->quote((float)$address['latitude'],
            (float)$address['longitude']);
            if (!$quote['ok']) {
                flash('error',
                $quote['message']);
                redirect(APP_URL.'/index.php?r=order/checkout');
            }
            $extra['alamat_id'] = $addressId;
            $extra['ongkir'] = $quote['ongkir'];
            $extra['jarak_km'] = $quote['jarak_km'];
            $extra['shipping'] = [
            'nama_penerima' => $address['nama_penerima'],
            'no_hp' => $address['no_hp'],
            'alamat' => $address['alamat'],
            'patokan' => $address['patokan'] ?? '',
            'latitude' => $address['latitude'],
            'longitude' => $address['longitude'],
            ];
            $extra['status'] = 'MENUNGGU_KONFIRMASI';
        }
        $voucher = $_SESSION['checkout_voucher'] ?? null;
        if ($voucher) {
            $check = (new VoucherModel())->validate((string)$voucher['kode'],
            $cid,
            $subtotal,
            (float)$extra['ongkir']);
            if ($check['ok']) {
                $extra['voucher_id'] = (int)$check['voucher']['id'];
                $extra['potongan_voucher'] = (float)$check['potongan'];
            }
        }
        $total = max(0,
        $subtotal + (float)$extra['ongkir'] - (float)$extra['potongan_voucher']);
        $method = ($_POST['metode_bayar'] ?? '') === 'QRIS' ? 'QRIS' : 'TUNAI';
        $tx = $this->orders->createOrder($cid,
        $tableId,
        $orderType,
        $method,
        $items,
        $total,
        $extra);
        (new CartModel())->clear($cid);
        unset($_SESSION['checkout'],
        $_SESSION['checkout_voucher']);
        $_SESSION['last_order_id'] = $tx;
        redirect(APP_URL.'/index.php?r=order/checkout&id='.$tx);
    }
    public function checkout(): void {
        $cid = $this->customerId();
        // POST tanpa id = simpan keranjang sesi lama (kompatibilitas) lalu tampilkan checkout.
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_GET['id'])) {
            verify_csrf();
            $items = json_decode($_POST['items'] ?? '[]',
            true);
            if (is_array($items) && $items) {
                $normalized = $this->normalizeItems($items);
                $_SESSION['checkout'] = ['items' => $normalized['items'],
                'total' => $normalized['total'],
                'qty' => $normalized['qty']];
            }
        }
        if (empty($_GET['id'])) {
            $checkout = $this->currentCart($cid);
            if (!$checkout['items']) redirect(APP_URL.'/index.php?r=order/menu');
            $tables = $this->orders->activeTables();
            $addresses = (new AddressModel())->all($cid);
            $settings = (new ShippingModel())->settings();
            $selectedAddressId = (int)($_GET['address_id'] ?? 0) ?: (int)($addresses[0]['id'] ?? 0);
            $selectedAddress = null;
            foreach ($addresses as $a) if ((int)$a['id'] === $selectedAddressId) $selectedAddress = $a;
            $quote = $selectedAddress ? (new ShippingModel())->quote($selectedAddress['latitude'] !== null ? (float)$selectedAddress['latitude'] : null,
            $selectedAddress['longitude'] !== null ? (float)$selectedAddress['longitude'] : null) : null;
            $ongkir = $quote && $quote['ok'] ? (float)$quote['ongkir'] : 0.0;
            $voucher = $_SESSION['checkout_voucher'] ?? null;
            $potongan = 0.0;
            if ($voucher) {
                $check = (new VoucherModel())->validate((string)$voucher['kode'],
                $cid,
                (float)$checkout['total'],
                $ongkir);
                if ($check['ok']) {
                    $potongan = (float)$check['potongan'];
                    $voucher['potongan'] = $potongan;
                }  else {
                    unset($_SESSION['checkout_voucher']);
                    $voucher = null;
                }
            }
            $grandTotal = max(0,
            (float)$checkout['total'] + $ongkir - $potongan);
            $checkoutMode = true;
            require BASE_PATH.'/view/order/checkout.php';
            return;
        }
        $tx = $this->orders->customerOrder((int)($_GET['id'] ?? 0),
        $cid);
        if (!$tx) {
            flash('error',
            'Transaksi tidak ditemukan atau bukan milik akunmu.');
            redirect(APP_URL.'/index.php?r=order/history');
        }
        $paymentError = flash('payment_error');
        $ongkirPending = $tx['jenis_pesanan'] === 'DELIVERY' && ($tx['ongkir_status'] ?? '') !== 'DIKONFIRMASI';
        // QRIS dibuat hanya jika transaksi QRIS belum punya QR DAN ongkir delivery sudah dikonfirmasi.
        if (!$ongkirPending && $tx['metode_bayar'] === 'QRIS' && $tx['status_pembayaran'] !== 'TERVERIFIKASI' && empty($tx['qris_url']) && empty($tx['payment_reference'])) {
            try {
                $customer = (new AuthModel())->findCustomer($cid);
                if (!$customer) throw new RuntimeException('Data pelanggan tidak ditemukan.');
                $gateway = QrisGatewayFactory::make();
                $providerOrderId = (string)$tx['nomor_pesanan'];
                $payment = $gateway->createPayment($providerOrderId,
                (int)round((float)$tx['total_bayar']),
                (string)$customer['email'],
                (string)$customer['nama']);
                $save = Database::connection()->prepare("UPDATE transaksi_penjualan SET payment_reference=?,qris_url=?,payment_transaction_id=? WHERE id=? AND status_pembayaran='MENUNGGU'");
                $save->execute([$providerOrderId,
                $payment['qr_url'],
                $payment['reference'],
                $tx['id']]);
                $tx = $this->orders->customerOrder((int)$tx['id'],
                $cid);
            }  catch (Throwable $e) {
                error_log('[Midtrans QRIS] '.$e->getMessage());
                $paymentError = 'QRIS belum dapat dibuat. Pastikan Server Key Sandbox benar, metode QRIS aktif, dan ekstensi cURL PHP tersedia. Muat ulang halaman setelah konfigurasi diperbaiki.';
            }
        }
        $items = $this->orders->items((int)$tx['id']);
        $groupedItems = $this->orders->groupItems($items);
        $qrisImageUrl = $tx['qris_url'] ?? null;
        $chatThread = (new ChatModel())->threadByOrder((int)$tx['id']);
        $checkoutMode = false;
        require BASE_PATH.'/view/order/checkout.php';
    }
    public function applyVoucher(): void {
        $cid = $this->customerId();
        verify_csrf();
        $code = trim((string)($_POST['kode'] ?? ''));
        $data = $this->currentCart($cid);
        $addressId = (int)($_POST['alamat_id'] ?? 0);
        $address = $addressId ? (new AddressModel())->find($addressId,
        $cid) : null;
        $ongkir = 0.0;
        if ($address && $address['latitude'] !== null) {
            $q = (new ShippingModel())->quote((float)$address['latitude'],
            (float)$address['longitude']);
            if ($q['ok']) $ongkir = (float)$q['ongkir'];
        }
        $check = (new VoucherModel())->validate($code,
        $cid,
        (float)$data['total'],
        $ongkir);
        if ($check['ok']) {
            $_SESSION['checkout_voucher'] = ['kode' => $check['voucher']['kode'],
            'potongan' => $check['potongan']];
            flash('success',
            $check['message']);
        }  else {
            unset($_SESSION['checkout_voucher']);
            flash('error',
            $check['message']);
        }
        redirect(APP_URL.'/index.php?r=order/checkout'.($addressId ? '&address_id='.$addressId : ''));
    }
    public function removeVoucher(): void {
        $this->customerId();
        verify_csrf();
        unset($_SESSION['checkout_voucher']);
        flash('success',
        'Voucher dilepas.');
        redirect(APP_URL.'/index.php?r=order/checkout');
    }
    public function status(): void {
        $cid = $this->customerId();
        $tx = $this->orders->customerOrder((int)($_GET['id'] ?? 0),
        $cid);
        header('Content-Type: application/json');
        if (!$tx) {
            echo json_encode(['error' => 'not_found']);
            return;
        }
        $tx['status_label'] = order_status_label((string)$tx['status_pesanan']);
        $tx['chat_unread'] = (new ChatModel())->unreadForCustomer($cid);
        echo json_encode($tx,
        JSON_UNESCAPED_UNICODE);
    }
    public function qrisImage(): void {
        $cid = $this->customerId();
        $tx = $this->orders->customerOrder((int)($_GET['id'] ?? 0),
        $cid);
        if (!$tx || $tx['metode_bayar'] !== 'QRIS' || empty($tx['qris_url'])) {
            http_response_code(404);
            exit;
        }
        $url = $tx['qris_url'];
        $img = false;
        $ct = 'image/png';
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch,
            [CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2]);
            $body = curl_exec($ch);
            $code = (int)curl_getinfo($ch,
            CURLINFO_HTTP_CODE);
            $type = (string)curl_getinfo($ch,
            CURLINFO_CONTENT_TYPE);
            curl_close($ch);
            if ($body !== false && $code >= 200 && $code < 300 && str_starts_with($type,
            'image/')) {
                $img = $body;
                $ct = $type;
            }
        }
        if ($img === false) {
            header('Location: '.$url);
            exit;
        }
        if (!headers_sent()) {
            header('Content-Type: '.$ct);
            if (($_GET['dl'] ?? '') === '1') {
                $ext = str_contains($ct,
                'jpeg') || str_contains($ct,
                'jpg') ? 'jpg' : (str_contains($ct,
                'webp') ? 'webp' : 'png');
                header('Content-Disposition: attachment; filename="QRIS-'.preg_replace('/[^A-Za-z0-9._-]/',
                '',
                (string)$tx['nomor_pesanan']).'.'.$ext.'"');
            }  else {
                header('Content-Disposition: inline');
            }
            header('Cache-Control: private, max-age=3600');
        }
        echo $img;
    }
    public function confirmQris(): void {
        $cid = $this->customerId();
        verify_csrf();
        $txId = (int)($_POST['id'] ?? 0);
        $tx = $this->orders->customerOrder($txId,
        $cid);
        if (!$tx) {
            flash('error',
            'Transaksi tidak ditemukan.');
            redirect(APP_URL.'/index.php?r=order/history');
        }
        if ($tx['metode_bayar'] !== 'QRIS') {
            flash('error',
            'Transaksi ini bukan pembayaran QRIS.');
            redirect(APP_URL.'/index.php?r=order/checkout&id='.$txId);
        }
        flash('error',
        'Status pembayaran QRIS hanya berubah setelah konfirmasi resmi dari penyedia pembayaran diterima.');
        redirect(APP_URL.'/index.php?r=order/checkout&id='.$txId);
    }
    public function history(): void {
        $cid = $this->customerId();
        $orders = $this->orders->customerOrders($cid);
        foreach ($orders as &$o) {
            $o['grouped_items'] = $this->orders->groupItems($this->orders->items((int)$o['id']));
            $o['progress'] = order_progress((string)$o['status_pesanan'],
            (string)$o['jenis_pesanan']);
        }
        unset($o);
        $reviewsByTransaction = [];
        $reviewIds = array_map(static fn($o) => (int)$o['id'],
        $orders);
        if ($reviewIds) {
            $placeholders = implode(',',
            array_fill(0,
            count($reviewIds),
            '?'));
            $stmt = Database::connection()->prepare("SELECT * FROM ulasan_pelanggan WHERE pelanggan_id=? AND transaksi_id IN ($placeholders)");
            $stmt->execute(array_merge([$cid],
            $reviewIds));
            foreach ($stmt->fetchAll() as $review) $reviewsByTransaction[(int)$review['transaksi_id']] = $review;
        }
        $openReviewId = (int)($_GET['review'] ?? 0);
        $rewards = (new RewardModel())->rewardsForCustomer($cid);
        $chatUnread = (new ChatModel())->unreadForCustomer($cid);
        $title = 'Pesanan Saya';
        require BASE_PATH.'/view/order/history.php';
    }
    public function detail(): void {
        $cid = $this->customerId();
        $txId = (int)($_GET['id'] ?? 0);
        $order = $this->orders->customerOrder($txId,
        $cid);
        if (!$order) {
            flash('error',
            'Pesanan tidak ditemukan atau bukan milik akunmu.');
            redirect(APP_URL.'/index.php?r=order/history');
        }
        $items = $this->orders->items($txId);
        $groupedItems = $this->orders->groupItems($items);
        $chatThread = (new ChatModel())->threadByOrder($txId);
        $title = 'Detail Pesanan';
        require BASE_PATH.'/view/order/detail.php';
    }
}
