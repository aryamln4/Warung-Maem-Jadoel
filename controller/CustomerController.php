<?php
declare(strict_types=1);
/** Area pelanggan: buku alamat, keranjang, dan pengaturan pengiriman. */
class CustomerController {
    private function customerId(): int {
        if (empty($_SESSION['customer_id'])) {
            if ($this->wantsJson()) {
                http_response_code(401);
                echo json_encode(['ok' => false,
                'message' => 'Silakan masuk terlebih dahulu.']);
                exit;
            }
            redirect(APP_URL.'/index.php?r=auth/login');
        }
        return (int)$_SESSION['customer_id'];
    }
    private function wantsJson(): bool {
        return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'
        && (str_contains(strtolower($_SERVER['HTTP_ACCEPT'] ?? ''),
        'application/json') || ($_POST['ajax'] ?? '') === '1');
    }
    private function json(array $data,
    int $code = 200): void {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data,
        JSON_UNESCAPED_UNICODE);
        exit;
    }
    public function address(): void {
        $cid = $this->customerId();
        $addresses = (new AddressModel())->all($cid);
        $primary = $addresses[0] ?? null;
        $settings = (new ShippingModel())->settings();
        $title = 'Alamat Saya';
        require BASE_PATH.'/view/customer/address.php';
    }
    public function saveAddress(): void {
        $cid = $this->customerId();
        verify_csrf();
        $id = (int)($_POST['id'] ?? 0) ?: null;
        $nama = trim((string)($_POST['nama_penerima'] ?? ''));
        $hp = trim((string)($_POST['no_hp'] ?? ''));
        $alamat = trim((string)($_POST['alamat'] ?? ''));
        if ($nama === '' || $hp === '' || $alamat === '') {
            flash('error',
            'Nama penerima, nomor HP, dan alamat wajib diisi.');
            redirect(APP_URL.'/index.php?r=customer/address');
        }
        $lat = ($_POST['latitude'] ?? '') !== '' ? (float)$_POST['latitude'] : null;
        $lng = ($_POST['longitude'] ?? '') !== '' ? (float)$_POST['longitude'] : null;
        if ($lat !== null && ($lat < -90 || $lat > 90)) {
            flash('error',
            'Titik lintang tidak valid.');
            redirect(APP_URL.'/index.php?r=customer/address');
        }
        if ($lng !== null && ($lng < -180 || $lng > 180)) {
            flash('error',
            'Titik bujur tidak valid.');
            redirect(APP_URL.'/index.php?r=customer/address');
        }
        try {
            (new AddressModel())->save($cid,
            [
            'label' => $_POST['label'] ?? 'Rumah',
            'nama_penerima' => $nama,
            'no_hp' => $hp,
            'alamat' => $alamat,
            'patokan' => $_POST['patokan'] ?? '',
            'latitude' => $lat,
            'longitude' => $lng,
            'is_utama' => !empty($_POST['is_utama']),
            ],
            $id);
            flash('success',
            'Alamat berhasil disimpan.');
        }  catch (Throwable $e) {
            flash('error',
            'Alamat gagal disimpan: '.$e->getMessage());
        }
        redirect(APP_URL.'/index.php?r=customer/address');
    }
    public function deleteAddress(): void {
        $cid = $this->customerId();
        verify_csrf();
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) (new AddressModel())->deactivate($id,
        $cid);
        flash('success',
        'Alamat dihapus dari daftar.');
        redirect(APP_URL.'/index.php?r=customer/address');
    }
    public function setPrimaryAddress(): void {
        $cid = $this->customerId();
        verify_csrf();
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) (new AddressModel())->setPrimary($id,
        $cid);
        redirect(APP_URL.'/index.php?r=customer/address');
    }
    public function cart(): void {
        $cid = $this->customerId();
        $cart = new CartModel();
        $items = $cart->items($cid);
        $subtotal = 0.0;
        foreach ($items as $i) $subtotal += (float)$i['subtotal'];
        $settings = (new ShippingModel())->settings();
        $promo = (new VoucherModel())->activePromo();
        $title = 'Keranjang';
        require BASE_PATH.'/view/order/cart.php';
    }
    public function cartAdd(): void {
        $cid = $this->customerId();
        verify_csrf();
        try {
            $topping = isset($_POST['topping_id']) && $_POST['topping_id'] !== '' ? (int)$_POST['topping_id'] : null;
            $level = isset($_POST['level_pedas']) && $_POST['level_pedas'] !== '' ? (int)$_POST['level_pedas'] : null;
            (new CartModel())->add($cid,
            (int)($_POST['menu_id'] ?? 0),
            (int)($_POST['qty'] ?? 1),
            $topping,
            $level);
            if (!$this->wantsJson()) {
                flash('success',
                'Menu ditambahkan ke keranjang.');
                redirect(APP_URL.'/index.php?r=order/menu');
            }
            $cart = new CartModel();
            $this->json(['ok' => true,
            'count' => $cart->count($cid),
            'subtotal' => $cart->subtotal($cid),
            'items' => $cart->items($cid)]);
        }  catch (Throwable $e) {
            $this->json(['ok' => false,
            'message' => $e->getMessage()],
            422);
        }
    }
    public function cartUpdate(): void {
        $cid = $this->customerId();
        verify_csrf();
        try {
            (new CartModel())->update((int)($_POST['item_id'] ?? 0),
            $cid,
            (int)($_POST['qty'] ?? 1));
            if (!$this->wantsJson()) {
                flash('success',
                'Jumlah item diperbarui.');
                redirect(APP_URL.'/index.php?r=customer/cart');
            }
            $cart = new CartModel();
            $this->json(['ok' => true,
            'count' => $cart->count($cid),
            'subtotal' => $cart->subtotal($cid),
            'items' => $cart->items($cid)]);
        }  catch (Throwable $e) {
            if (!$this->wantsJson()) {
                flash('error',
                $e->getMessage());
                redirect(APP_URL.'/index.php?r=customer/cart');
            }
            $this->json(['ok' => false,
            'message' => $e->getMessage()],
            422);
        }
    }
    public function cartRemove(): void {
        $cid = $this->customerId();
        verify_csrf();
        try {
            (new CartModel())->remove((int)($_POST['item_id'] ?? 0),
            $cid);
            if (!$this->wantsJson()) {
                flash('success',
                'Item dihapus dari keranjang.');
                redirect(APP_URL.'/index.php?r=customer/cart');
            }
            $cart = new CartModel();
            $this->json(['ok' => true,
            'count' => $cart->count($cid),
            'subtotal' => $cart->subtotal($cid),
            'items' => $cart->items($cid)]);
        }  catch (Throwable $e) {
            if (!$this->wantsJson()) {
                flash('error',
                $e->getMessage());
                redirect(APP_URL.'/index.php?r=customer/cart');
            }
            $this->json(['ok' => false,
            'message' => $e->getMessage()],
            422);
        }
    }
}
