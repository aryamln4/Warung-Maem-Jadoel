<?php
declare(strict_types=1);
class AdminController {
    private function guard(): void {
        if(empty($_SESSION['admin_id']))redirect(APP_URL.'/index.php?r=auth/admin-login');
    }
    /** Tabel bahan baku & kartu stok dibuat otomatis pada database lama (idempoten). */
    private function ensureInventoryTables(): void {
        $db=Database::connection();
        $db->exec("CREATE TABLE IF NOT EXISTS bahan_baku (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, kode_produk VARCHAR(40) NOT NULL UNIQUE, nama_bahan VARCHAR(150) NOT NULL, tanggal_kadaluarsa DATE NOT NULL, stok DECIMAL(12,3) NOT NULL DEFAULT 0, satuan ENUM('gram','kilo','pcs') NOT NULL, harga DECIMAL(14,2) NOT NULL DEFAULT 0, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, INDEX idx_bahan_fefo (tanggal_kadaluarsa,id)) ENGINE=InnoDB");
        $db->exec("CREATE TABLE IF NOT EXISTS kartu_stok_bahan (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, bahan_baku_id BIGINT UNSIGNED NOT NULL, jenis ENUM('MASUK','KELUAR','PENYESUAIAN') NOT NULL, jumlah DECIMAL(12,3) NOT NULL, keterangan VARCHAR(255) NULL, tanggal TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, INDEX idx_kartu_bahan_tanggal (bahan_baku_id,tanggal), CONSTRAINT fk_kartu_bahan FOREIGN KEY (bahan_baku_id) REFERENCES bahan_baku(id) ON DELETE RESTRICT) ENGINE=InnoDB");
        $db->exec("CREATE TABLE IF NOT EXISTS pembelian_bahan (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, bahan_baku_id BIGINT UNSIGNED NOT NULL, keuangan_id BIGINT UNSIGNED NULL, jumlah DECIMAL(12,3) NOT NULL, harga_satuan DECIMAL(14,2) NOT NULL DEFAULT 0, total DECIMAL(14,2) NOT NULL DEFAULT 0, tanggal DATE NOT NULL, status ENUM('AKTIF','DIBATALKAN') NOT NULL DEFAULT 'AKTIF', keterangan VARCHAR(255) NULL, created_by VARCHAR(60) NULL, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, INDEX idx_pembelian_bahan (bahan_baku_id,tanggal), CONSTRAINT fk_pembelian_bahan FOREIGN KEY (bahan_baku_id) REFERENCES bahan_baku(id) ON DELETE RESTRICT) ENGINE=InnoDB");
    }
    public function dashboard(): void {
        $this->guard();
        $db=Database::connection();
        $sales=(float)$db->query("SELECT COALESCE(SUM(total_bayar),0) FROM transaksi_penjualan WHERE status_pembayaran='TERVERIFIKASI' AND status_pesanan='SELESAI' AND DATE(tanggal)=CURDATE()")->fetchColumn();
        $transactionCount=(int)$db->query("SELECT COUNT(*) FROM transaksi_penjualan WHERE status_pembayaran='TERVERIFIKASI' AND status_pesanan='SELESAI' AND DATE(tanggal)=CURDATE()")->fetchColumn();
        $customerCount=(int)$db->query("SELECT COUNT(*) FROM pelanggan WHERE status='AKTIF'")->fetchColumn();
        // Total unit produk yang benar-benar keluar hari ini (pembayaran terverifikasi).
        // DIBATALKAN tidak dihitung; item bonus tetap ikut karena juga mengurangi stok.
        $soldQty=(int)$db->query("SELECT COALESCE(SUM(d.qty),0) FROM detail_transaksi d JOIN transaksi_penjualan t ON t.id=d.transaksi_id WHERE t.status_pembayaran='TERVERIFIKASI' AND t.status_pesanan<>'DIBATALKAN' AND DATE(t.tanggal)=CURDATE()")->fetchColumn();
        $topProducts=$db->query("SELECT m.nama_produk, COALESCE(SUM(d.qty),0) AS qty_terjual FROM detail_transaksi d JOIN transaksi_penjualan t ON t.id=d.transaksi_id JOIN menu m ON m.id=d.menu_id WHERE t.status_pembayaran='TERVERIFIKASI' AND t.status_pesanan<>'DIBATALKAN' AND DATE(t.tanggal)=CURDATE() GROUP BY m.id,m.nama_produk ORDER BY qty_terjual DESC,m.nama_produk ASC LIMIT 4")->fetchAll();
        $orders=(new OrderModel())->allRunning();
        $statusCounts=['masuk'=>0,
        'proses'=>0,
        'siap'=>0];
        foreach($orders as $o){
            if(in_array($o['status_pesanan'],
            ['MENUNGGU_PEMBAYARAN',
            'MENUNGGU_KONFIRMASI'],
            true))$statusCounts['masuk']++;
            elseif(in_array($o['status_pesanan'],
            ['DIPROSES'],
            true))$statusCounts['proses']++;
            else $statusCounts['siap']++;
        }
        foreach($orders as &$order){
            $order['grouped_items']=(new OrderModel())->groupItems((new OrderModel())->items((int)$order['id']));
        }
        unset($order);
        $low=(new OrderModel())->lowStock();
        $openNotif=$db->query("SELECT * FROM system_notifications WHERE status='OPEN' ORDER BY id DESC")->fetchAll();
        require BASE_PATH.'/view/admin/dashboard.php';
    }
    public function orders(): void {
        $this->guard();
        $db=Database::connection();
        $rows=(new OrderModel())->allRunning();
        $orderModel=new OrderModel();
        foreach($rows as &$row){
            $row['grouped_items']=$orderModel->groupItems($orderModel->items((int)$row['id']));
        }
        unset($row);
        $ordersIn=array_values(array_filter($rows,
        fn($o)=>in_array($o['status_pesanan'],
        ['MENUNGGU_PEMBAYARAN',
        'MENUNGGU_KONFIRMASI'],
        true)));
        $ordersProcess=array_values(array_filter($rows,
        fn($o)=>in_array($o['status_pesanan'],
        ['DIPROSES',
        'DIKEMAS'],
        true)));
        $ordersReady=array_values(array_filter($rows,
        fn($o)=>in_array($o['status_pesanan'],
        ['SIAP_DIAMBIL',
        'SEDANG_DIANTAR'],
        true)));
        $pendingOngkir=(new ShippingModel())->pending();
        $chatUnread=(new ChatModel())->unreadForAdmin();
        $title='Pesanan';
        require BASE_PATH.'/view/admin/orders.php';
    }
    public function runningJson(): void {
        $this->guard();
        $orders=(new OrderModel())->allRunning();
        $statusCounts=['masuk'=>0,
        'proses'=>0,
        'siap'=>0];
        foreach($orders as $o){
            if(in_array($o['status_pesanan'],
            ['MENUNGGU_PEMBAYARAN',
            'MENUNGGU_KONFIRMASI'],
            true))$statusCounts['masuk']++;
            elseif(in_array($o['status_pesanan'],
            ['DIPROSES'],
            true))$statusCounts['proses']++;
            else $statusCounts['siap']++;
        }
        $orderModel=new OrderModel();
        foreach($orders as &$order){
            $order['grouped_items']=$orderModel->groupItems($orderModel->items((int)$order['id']));
        }
        unset($order);
        ob_start();
        require BASE_PATH.'/view/admin/_dashboard_running.php';
        $html=ob_get_clean();
        header('Content-Type: application/json');
        echo json_encode(['count'=>count($orders),
        'html'=>$html,
        'orders'=>$orders,
        'statusCounts'=>$statusCounts],
        JSON_UNESCAPED_UNICODE);
    }
    public function orderDetail(): void {
        $this->guard();
        $id=(int)($_GET['id']??0);
        $order=(new OrderModel())->adminOrderDetail($id);
        header('Content-Type: application/json; charset=utf-8');
        if(!$order){
            http_response_code(404);
            echo json_encode(['error'=>'Pesanan tidak ditemukan'],
            JSON_UNESCAPED_UNICODE);
            return;
        }
        echo json_encode($order,
        JSON_UNESCAPED_UNICODE);
    }
    public function qrisWebhook(): void {
        header('Content-Type: application/json; charset=utf-8');
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            echo json_encode(['ok'=>false,
            'error'=>'Method not allowed']);
            return;
        }
        $raw=file_get_contents('php://input') ?: '';
        $data=json_decode($raw,
        true);
        if(!is_array($data)){
            http_response_code(400);
            echo json_encode(['ok'=>false,
            'error'=>'JSON tidak valid']);
            return;
        }
        try {
            $gateway=QrisGatewayFactory::make();
            if(!$gateway->verifyWebhook($data)){
                http_response_code(401);
                echo json_encode(['ok'=>false,
                'error'=>'Signature Midtrans tidak valid']);
                return;
            }
        }  catch(Throwable $e) {
            error_log('[Midtrans webhook] '.$e->getMessage());
            http_response_code(500);
            echo json_encode(['ok'=>false,
            'error'=>'Konfigurasi payment gateway belum siap']);
            return;
        }
        $providerOrderId=(string)($data['order_id']??'');
        $status=strtolower((string)($data['transaction_status']??''));
        $fraud=strtolower((string)($data['fraud_status']??''));
        $gross=isset($data['gross_amount'])?(float)$data['gross_amount']:null;
        if($providerOrderId==='' || $gross===null){
            http_response_code(422);
            echo json_encode(['ok'=>false,
            'error'=>'Data transaksi tidak lengkap']);
            return;
        }
        $db=Database::connection();
        $q=$db->prepare("SELECT id,total_bayar,metode_bayar,status_pembayaran,pelanggan_id,stock_processed_at,reward_processed_at FROM transaksi_penjualan WHERE payment_reference=? LIMIT 1");
        $q->execute([$providerOrderId]);
        $tx=$q->fetch();
        if(!$tx || $tx['metode_bayar']!=='QRIS'){
            http_response_code(404);
            echo json_encode(['ok'=>false,
            'error'=>'Transaksi QRIS tidak ditemukan']);
            return;
        }
        if(abs((float)$tx['total_bayar']-$gross)>0.01){
            http_response_code(422);
            echo json_encode(['ok'=>false,
            'error'=>'Nominal pembayaran tidak sesuai']);
            return;
        }
        if($tx['status_pembayaran']==='TERVERIFIKASI' && !empty($tx['stock_processed_at']) && !empty($tx['reward_processed_at'])){
            echo json_encode(['ok'=>true,
            'already_paid'=>true]);
            return;
        }
        $paid=($status==='settlement') || ($status==='capture' && ($fraud==='' || $fraud==='accept'));
        if(!$paid){
            if(in_array($status,
            ['expire',
            'cancel',
            'deny'],
            true)) {
                $db->prepare("UPDATE transaksi_penjualan SET status_pembayaran='DITOLAK' WHERE id=? AND status_pembayaran='MENUNGGU'")->execute([(int)$tx['id']]);
            }
            echo json_encode(['ok'=>true,
            'status'=>$status]);
            return;
        }
        try {
            $this->settleQris($tx,
            (string)($data['transaction_id'] ?? ''));
            echo json_encode(['ok'=>true,
            'status'=>'TERVERIFIKASI']);
        }  catch(Throwable $e) {
            error_log('[Midtrans settlement order '.$tx['id'].'] '.$e->getMessage());
            http_response_code(500);
            echo json_encode(['ok'=>false,
            'error'=>'Pembayaran terverifikasi, tetapi pemrosesan pesanan membutuhkan pemeriksaan admin']);
        }
    }
    /** Proses transaksi QRIS yang sudah terbayar: status, transaksi id, reward, dan stok. Idempoten (aman dipanggil ulang). */
    private function settleQris(array $tx,
    string $transactionId): void {
        $db=Database::connection();
        $orders=new OrderModel();
        $orders->updatePayment((int)$tx['id'],
        'TERVERIFIKASI',
        $transactionId);
        $db->prepare('UPDATE transaksi_penjualan SET payment_transaction_id=? WHERE id=?')->execute([$transactionId,
        (int)$tx['id']]);
        // Reward dibuat lebih dulu agar item bonus ikut masuk ke pengurangan stok; marker mencegah reward ganda saat webhook diulang.
        if(empty($tx['reward_processed_at'])) {
            (new RewardService($db,
            new RewardModel()))->apply((int)$tx['pelanggan_id'],
            (int)$tx['id']);
            $db->prepare('UPDATE transaksi_penjualan SET reward_processed_at=NOW() WHERE id=?')->execute([(int)$tx['id']]);
        }
        if(empty($tx['stock_processed_at'])) $orders->stockOutForOrder((int)$tx['id']);
    }
    /**
     * Tarik status pembayaran QRIS langsung dari API Midtrans (server-to-server).
     * Dipakai ketika webhook tidak sampai (mis. pengujian di localhost) â€” klik tombol
     * "Cek Status QRIS" pada pesanan, sistem akan menyinkronkan status dari Midtrans.
     */
    public function checkPayment(): void {
        $this->guard();
        verify_csrf();
        $txId=(int)($_POST['id']??0);
        $db=Database::connection();
        $q=$db->prepare("SELECT id,nomor_pesanan,payment_reference,total_bayar,metode_bayar,status_pembayaran,pelanggan_id,stock_processed_at,reward_processed_at FROM transaksi_penjualan WHERE id=? LIMIT 1");
        $q->execute([$txId]);
        $tx=$q->fetch();
        if(!$tx || $tx['metode_bayar']!=='QRIS'){
            flash('error',
            'Transaksi QRIS tidak ditemukan.');
            redirect(APP_URL.'/index.php?r=admin/orders');
        }
        if($tx['status_pembayaran']==='TERVERIFIKASI'){
            flash('success',
            'Pembayaran pesanan #'.$txId.' sudah terverifikasi.');
            redirect(APP_URL.'/index.php?r=admin/orders');
        }
        if(empty($tx['payment_reference'])){
            flash('error',
            'Tagihan Midtrans untuk pesanan #'.$txId.' belum dibuat. Pelanggan harus membuka halaman QR-nya dahulu.');
            redirect(APP_URL.'/index.php?r=admin/orders');
        }
        try {
            $gateway=QrisGatewayFactory::make();
            $resp=$gateway->status($tx['payment_reference']);
            $status=strtolower((string)($resp['transaction_status']??''));
            $fraud=strtolower((string)($resp['fraud_status']??''));
            $gross=isset($resp['gross_amount'])?(float)$resp['gross_amount']:null;
            if($gross!==null && abs((float)$tx['total_bayar']-$gross)>0.01){
                flash('error',
                'Nominal di Midtrans ('.number_format($gross,
                0,
                ',',
                '.').') tidak cocok dengan pesanan #'.$txId.'.');
                redirect(APP_URL.'/index.php?r=admin/orders');
            }
            $paid=($status==='settlement') || ($status==='capture' && ($fraud==='' || $fraud==='accept'));
            if($paid){
                $this->settleQris($tx,
                (string)($resp['transaction_id']??$tx['payment_reference']));
                flash('success',
                'Pembayaran QRIS pesanan #'.$txId.' terverifikasi dari Midtrans. Stok & bonus sudah diproses.');
            }  elseif(in_array($status,
            ['expire',
            'cancel',
            'deny'],
            true)) {
                $db->prepare("UPDATE transaksi_penjualan SET status_pembayaran='DITOLAK' WHERE id=? AND status_pembayaran='MENUNGGU'")->execute([$txId]);
                flash('error',
                'Midtrans melaporkan status "'.strtoupper($status).'" (tidak dibayar) untuk pesanan #'.$txId.'.');
            }  else {
                flash('error',
                'Midtrans melaporkan status "'.strtoupper($status ?: 'TIDAK DIKETAHUI').'" untuk pesanan #'.$txId.'. Pembayaran belum masuk.');
            }
        }  catch(Throwable $e) {
            error_log('[Midtrans checkPayment #'.$txId.'] '.$e->getMessage());
            flash('error',
            'Pemeriksaan ke Midtrans gagal. Periksa konfigurasi key di .env dan koneksi internet, lalu coba lagi.');
        }
        redirect(APP_URL.'/index.php?r=admin/orders');
    }
    public function confirmCash(): void {
        $this->guard();
        verify_csrf();
        $tx=(int)($_POST['id']??0);
        $db=Database::connection();
        $q=$db->prepare('SELECT metode_bayar,status_pembayaran,pelanggan_id FROM transaksi_penjualan WHERE id=? LIMIT 1');
        $q->execute([$tx]);
        $payment=$q->fetch();
        try {
            (new OrderModel())->confirmCash($tx);
            // QRIS sudah mengurangi stok dan menerapkan reward saat pembayaran terverifikasi.
            // Untuk cash, keduanya baru dijalankan saat owner menerima pesanan.
            if($payment && $payment['metode_bayar']==='TUNAI') {
                (new OrderModel())->stockOutForOrder($tx);
                (new RewardService($db,
                new RewardModel()))->apply((int)$payment['pelanggan_id'],
                $tx);
            }
        }  catch(Throwable $e) {
            flash('error',
            $e->getMessage());
        }
        redirect(APP_URL.'/index.php?r=admin/orders');
    }
    public function status(): void {
        $this->guard();
        verify_csrf();
        (new OrderModel())->adminSetStatus((int)$_POST['id'],
        $_POST['status']);
        redirect(APP_URL.'/index.php?r=admin/orders');
    }
    public function bonuses(): void {
        redirect(APP_URL.'/index.php?r=admin/menu');
    }
    public function saveBonus(): void {
        $this->guard();
        verify_csrf();
        $d=['menu_id'=>(int)$_POST['menu_id'],
        'jenis_reward'=>$_POST['jenis_reward'],
        'status_aktif'=>isset($_POST['status_aktif'])?1:0,
        'tanggal_mulai'=>$_POST['tanggal_mulai']??'',
        'tanggal_selesai'=>$_POST['tanggal_selesai']??'',
        'keterangan'=>trim($_POST['keterangan']??'')];
        $id=!empty($_POST['id'])?(int)$_POST['id']:null;
        (new RewardModel())->saveBonus($d,
        $id);
        flash('success',
        'Bonus berhasil disimpan.');
        redirect(APP_URL.'/index.php?r=admin/menu');
    }
    public function deleteBonus(): void {
        $this->guard();
        verify_csrf();
        $id=(int)($_POST['id']??0);
        if($id>0){
            (new RewardModel())->deleteBonus($id);
            flash('success',
            'Konfigurasi bonus dihapus.');
        }
        redirect(APP_URL.'/index.php?r=admin/menu');
    }
    public function finance(): void {
        $this->guard();
        $this->ensureInventoryTables();
        $from=$_GET['from']??date('Y-m-01');
        $to=$_GET['to']??date('Y-m-d');
        $f=new FinanceModel();
        $summary=$f->summary($from,
        $to);
        $sales=$f->salesSummary($from,
        $to);
        $transactions=$f->transactionSales($from,
        $to);
        $top=$f->topProducts($from,
        $to);
        $qrisDiagnostics=QrisGatewayFactory::diagnostics();
        require BASE_PATH.'/view/admin/finance.php';
    }
    public function uploadQris(): void {
        $this->guard();
        verify_csrf();
        $file=$_FILES['qris']??[];
        $uploadError=(int)($file['error']??UPLOAD_ERR_NO_FILE);
        if($uploadError!==UPLOAD_ERR_OK){
            flash('error',
            upload_error_message($uploadError));
            redirect(APP_URL.'/index.php?r=admin/finance');
        }
        if(!is_uploaded_file($file['tmp_name']??'')){
            flash('error',
            'File QRIS tidak dikenali sebagai upload yang valid.');
            redirect(APP_URL.'/index.php?r=admin/finance');
        }
        if((int)($file['size']??0)>MAX_IMAGE_UPLOAD_BYTES){
            flash('error',
            'Ukuran gambar QRIS maksimal 6 MB.');
            redirect(APP_URL.'/index.php?r=admin/finance');
        }
        $mime=uploaded_image_mime($file);
        $allowed=['image/jpeg'=>'jpg',
        'image/png'=>'png',
        'image/webp'=>'webp'];
        if(!isset($allowed[$mime])){
            flash('error',
            'QRIS harus berupa JPG, PNG atau WEBP dan file harus benar-benar gambar.');
            redirect(APP_URL.'/index.php?r=admin/finance');
        }
        $dir=BASE_PATH.'/public/assets/images/payment';
        if(!is_dir($dir) && !mkdir($dir,
        0755,
        true) && !is_dir($dir)){
            flash('error',
            'Folder penyimpanan QRIS tidak dapat dibuat server.');
            redirect(APP_URL.'/index.php?r=admin/finance');
        }
        if(!is_writable($dir)){
            flash('error',
            'Folder penyimpanan QRIS tidak dapat ditulis server. Periksa permission folder payment.');
            redirect(APP_URL.'/index.php?r=admin/finance');
        }
        foreach(['png',
        'jpg',
        'jpeg',
        'webp'] as $ext){
            $old=$dir.'/qris-warung.'.$ext;
            if(is_file($old)) @unlink($old);
        }
        $dest=$dir.'/qris-warung.'.$allowed[$mime];
        if(!move_uploaded_file($file['tmp_name'],
        $dest)){
            flash('error',
            'Gambar QRIS gagal disimpan ke server.');
            redirect(APP_URL.'/index.php?r=admin/finance');
        }
        flash('success',
        'Gambar QRIS berhasil diperbarui.');
        redirect(APP_URL.'/index.php?r=admin/finance');
    }
    public function addFinance(): void {
        $this->guard();
        verify_csrf();
        (new FinanceModel())->add($_POST);
        redirect(APP_URL.'/index.php?r=admin/finance');
    }
    public function inventory(): void {
        redirect(APP_URL.'/index.php?r=admin/menu#stok');
    }
    public function operations(): void {
        $this->guard();
        $tab = (string)($_GET['tab'] ?? 'pengeluaran');
        // Stok produk & bahan baku kini berada di halaman Produk & Stok.
        if ($tab === 'stok') {
            redirect(APP_URL.'/index.php?r=admin/menu#stok');
        }
        if ($tab === 'bahan') {
            redirect(APP_URL.'/index.php?r=admin/menu#bahan');
        }
        $this->ensureInventoryTables();
        $db=Database::connection();
        $tab = in_array($tab,
        ['pengeluaran',
        'meja',
        'toko'],
        true) ? $tab : 'pengeluaran';
        $title = ['pengeluaran'=>'Operasional',
        'meja'=>'No. Meja',
        'toko'=>'Pengaturan Toko'][$tab];
        $items=(new InventoryModel())->all();
        $tables=$db->query("SELECT * FROM meja ORDER BY status_aktif DESC, nomor_meja+0, nomor_meja")->fetchAll();
        $from=$_GET['from']??date('Y-m-01');
        $to=$_GET['to']??date('Y-m-d');
        $f=new FinanceModel();
        $expenses=$f->expenses($from,
        $to);
        $expenseSummary=$f->expenseSummary($from,
        $to);
        $expenseCategories=$f->expenseCategories();
        $editExpense=!empty($_GET['edit_expense'])?$f->findExpense((int)$_GET['edit_expense']):null;
        $storeSettings=(new ShippingModel())->settings();
        require BASE_PATH.'/view/admin/operations.php';
    }
    public function saveExpense(): void {
        $this->guard();
        verify_csrf();
        $this->ensureInventoryTables();
        $id=(int)($_POST['id']??0);
        $d=[
        'tanggal'=>$_POST['tanggal']??date('Y-m-d'),
        'kategori'=>trim((string)($_POST['kategori']??'')),
        'jumlah'=>(float)($_POST['jumlah']??0),
        'keterangan'=>trim((string)($_POST['keterangan']??'')),
        'bukti'=>trim((string)($_POST['bukti']??'')),
        'created_by'=>$_SESSION['username']??'admin',
        ];
        if($d['kategori']===''||$d['jumlah']<=0){
            flash('error',
            'Kategori dan nominal pengeluaran wajib diisi.');
            redirect(APP_URL.'/index.php?r=admin/operations&tab=pengeluaran');
        }
        try {
            $f=new FinanceModel();
            if($id && $f->isPurchaseExpense($id)){
                flash('error',
                'Pengeluaran pembelian bahan tidak dapat diubah dari sini. Batalkan pembelian pada Produk & Stok.');
                redirect(APP_URL.'/index.php?r=admin/operations&tab=pengeluaran');
            }
            if($id){
                $f->updateExpense($id,
                $d);
                flash('success',
                'Pengeluaran diperbarui. Laporan laba/rugi ikut menyesuaikan.');
            }  else {
                $f->addExpense($d);
                flash('success',
                'Pengeluaran dicatat. Laporan laba/rugi ikut menyesuaikan.');
            }
        }  catch(Throwable $e) {
            flash('error',
            'Pengeluaran gagal disimpan.');
        }
        redirect(APP_URL.'/index.php?r=admin/operations&tab=pengeluaran');
    }
    public function deleteExpense(): void {
        $this->guard();
        verify_csrf();
        $this->ensureInventoryTables();
        $id=(int)($_POST['id']??0);
        $f=new FinanceModel();
        if($id>0 && $f->isPurchaseExpense($id)){
            flash('error',
            'Pengeluaran ini berasal dari pembelian bahan baku. Batalkan lewat tab Produk & Stok â†’ Stok Bahan Baku agar stok ikut kembali.');
            redirect(APP_URL.'/index.php?r=admin/operations&tab=pengeluaran');
        }
        if($id>0) $f->deleteExpense($id);
        flash('success',
        'Pengeluaran dihapus dari laporan.');
        redirect(APP_URL.'/index.php?r=admin/operations&tab=pengeluaran');
    }
    public function saveStore(): void {
        $this->guard();
        verify_csrf();
        try {
            (new ShippingModel())->saveSettings($_POST);
            flash('success',
            'Pengaturan toko & ongkir disimpan.');
        }  catch(Throwable $e) {
            flash('error',
            'Pengaturan toko gagal disimpan.');
        }
        redirect(APP_URL.'/index.php?r=admin/operations&tab=toko');
    }
    public function saveRecipe(): void {
        $this->guard();
        verify_csrf();
        $menuId=(int)($_POST['menu_id']??0);
        if($menuId<1){
            flash('error',
            'Pilih produk terlebih dahulu.');
            redirect(APP_URL.'/index.php?r=admin/menu#resep');
        }
        $rows=[];
        $bahanIds=$_POST['bahan_baku_id']??[];
        $jumlah=$_POST['jumlah']??[];
        $keterangan=$_POST['keterangan']??[];
        if(is_array($bahanIds)&&is_array($jumlah)){
            foreach($bahanIds as $i=>$bid){
                $rows[]=['bahan_baku_id'=>(int)$bid,
                'jumlah'=>(float)($jumlah[$i]??0),
                'keterangan'=>trim((string)($keterangan[$i]??''))];
            }
        }
        try {
            (new RecipeModel())->save($menuId,
            $rows);
            flash('success',
            'Resep produk disimpan. Penambahan stok produk akan menambah stok bahan sesuai takaran.');
        }  catch(Throwable $e) {
            flash('error',
            'Resep gagal disimpan.');
        }
        redirect(APP_URL.'/index.php?r=admin/menu&resep='.$menuId.'#resep');
    }
    public function confirmOngkir(): void {
        $this->guard();
        verify_csrf();
        $txId=(int)($_POST['id']??0);
        $ongkir=(float)($_POST['ongkir']??0);
        try {
            (new ShippingModel())->confirmOngkir($txId,
            $ongkir);
            flash('success',
            'Ongkir dikonfirmasi. Pesanan QRIS pelanggan siap menampilkan QR.');
        }  catch(Throwable $e) {
            flash('error',
            $e->getMessage());
        }
        redirect(APP_URL.'/index.php?r=admin/orders');
    }
    public function setCourier(): void {
        $this->guard();
        verify_csrf();
        $txId=(int)($_POST['id']??0);
        (new ShippingModel())->setCourier($txId,
        (string)($_POST['ekspedisi']??''),
        (string)($_POST['link_lacak']??''));
        Database::connection()->prepare("UPDATE transaksi_penjualan SET status_pesanan='SEDANG_DIANTAR' WHERE id=? AND jenis_pesanan='DELIVERY'")->execute([$txId]);
        flash('success',
        'Kurir dicatat & status pesanan menjadi Dikirim.');
        redirect(APP_URL.'/index.php?r=admin/orders');
    }
    public function saveVoucher(): void {
        $this->guard();
        verify_csrf();
        try {
            (new VoucherModel())->save($_POST,
            !empty($_POST['id'])?(int)$_POST['id']:null);
            flash('success',
            'Voucher disimpan.');
        }  catch(Throwable $e) {
            flash('error',
            'Voucher gagal disimpan: '.$e->getMessage());
        }
        redirect(APP_URL.'/index.php?r=admin/menu#voucher');
    }
    public function deleteVoucher(): void {
        $this->guard();
        verify_csrf();
        $id=(int)($_POST['id']??0);
        if($id>0) (new VoucherModel())->delete($id);
        flash('success',
        'Voucher dihapus.');
        redirect(APP_URL.'/index.php?r=admin/menu#voucher');
    }
    public function saveBahan(): void {
        $this->guard();
        verify_csrf();
        $this->ensureInventoryTables();
        $d=$_POST;
        $kode=trim((string)($d['kode_produk']??''));
        $nama=trim((string)($d['nama_bahan']??''));
        $exp=(string)($d['tanggal_kadaluarsa']??'');
        $stok=(float)($d['stok']??-1);
        $unit=(string)($d['satuan']??'');
        $harga=(float)($d['harga']??-1);
        if($kode===''||$nama===''||$exp===''||$stok<0||!in_array($unit,
        ['gram',
        'kilo',
        'pcs'],
        true)||$harga<0){
            flash('error',
            'Lengkapi data bahan baku dengan benar.');
            redirect(APP_URL.'/index.php?r=admin/menu#bahan');
        }
        try{
            (new InventoryModel())->createBahan([
                'kode_produk'=>$kode,
                'nama_bahan'=>$nama,
                'tanggal_kadaluarsa'=>$exp,
                'stok'=>$stok,
                'satuan'=>$unit,
                'harga'=>$harga,
            ], (string)($_SESSION['username']??'admin'));
            flash('success',
            'Bahan baku berhasil ditambahkan. Stok awal dan pengeluarannya tercatat otomatis.');
        } catch(Throwable $e){
            flash('error',
            $e->getMessage() ?: 'Gagal menyimpan bahan baku. Kode produk mungkin sudah digunakan.');
        }
        redirect(APP_URL.'/index.php?r=admin/menu#bahan');
    }

    /** Pembelian bahan baku: stok bertambah, pengeluaran tercatat satu kali. */
    public function addBahanStock(): void {
        $this->guard();
        verify_csrf();
        $this->ensureInventoryTables();
        $bahanId=(int)($_POST['bahan_baku_id']??0);
        $jumlah=(float)($_POST['jumlah']??0);
        $tanggal=(string)($_POST['tanggal']??date('Y-m-d'));
        $keterangan=trim((string)($_POST['keterangan']??''));
        if($bahanId<1||$jumlah<=0){
            flash('error',
            'Pilih bahan dan isi jumlah pembelian lebih dari nol.');
            redirect(APP_URL.'/index.php?r=admin/menu#bahan');
        }
        try{
            $id=(new InventoryModel())->recordPurchase($bahanId,
            $jumlah,
            $tanggal,
            $keterangan,
            (string)($_SESSION['username']??'admin'));
            flash('success',
            'Pembelian bahan #'.$id.' dicatat. Stok bertambah dan pengeluaran masuk ke laporan Keuangan.');
        } catch(Throwable $e){
            flash('error',
            'Pembelian bahan gagal dicatat: '.$e->getMessage());
        }
        redirect(APP_URL.'/index.php?r=admin/menu#bahan');
    }

    /** Pembatalan pembelian: stok dikurangi dan catatan pengeluaran dihapus. */
    public function cancelPurchase(): void {
        $this->guard();
        verify_csrf();
        $this->ensureInventoryTables();
        $id=(int)($_POST['id']??0);
        if($id<1){
            flash('error',
            'Pembelian tidak ditemukan.');
            redirect(APP_URL.'/index.php?r=admin/menu#bahan');
        }
        try{
            (new InventoryModel())->cancelPurchase($id,
            (string)($_SESSION['username']??'admin'));
            flash('success',
            'Pembelian dibatalkan. Stok bahan dikembalikan dan pengeluaran dihapus.');
        } catch(Throwable $e){
            flash('error',
            'Pembelian tidak dapat dibatalkan: '.$e->getMessage());
        }
        redirect(APP_URL.'/index.php?r=admin/menu#bahan');
    }
    public function adjustStock(): void {
        $this->guard();
        verify_csrf();
        $stocks=$_POST['stok']??[];
        if(!is_array($stocks))$stocks=[];
        try{
            (new InventoryModel())->setStocks($stocks);
            flash('success',
            'Stok produk berhasil disimpan.');
        } catch(Throwable $e){
            flash('error',
            $e->getMessage());
        }
redirect(APP_URL.'/index.php?r=admin/menu#stok');
    }
    public function menu(): void {
        $this->guard();
        $this->ensureInventoryTables();
        $m=new MenuModel();
        $menus=$m->allForAdmin();
        $cats=$m->categories();
        $tops=$m->toppings();
        $editMenu=!empty($_GET['edit']) ? $m->byId((int)$_GET['edit']) : null;
        $menuToppingIds=[];
        if($editMenu){
            foreach($m->toppingsFor((int)$editMenu['id']) as $t) $menuToppingIds[]=(int)$t['id'];
        }
        $recipeModel=new RecipeModel();
        $recipeSummary=$recipeModel->summary();
        $ingredients=$recipeModel->ingredients();
        $recipeMenuId=(int)($_GET['resep']??0);
        $recipeRows=$recipeMenuId?$recipeModel->forMenu($recipeMenuId):[];
        $recipeMenu=$recipeMenuId?$m->byId($recipeMenuId):null;
        $vouchers=(new VoucherModel())->all();
        $editVoucher=!empty($_GET['edit_voucher'])?(new VoucherModel())->find((int)$_GET['edit_voucher']):null;
        $inventory=new InventoryModel();
        $items=$inventory->all();
        $criticalCount=0;
        foreach($items as $i) if((int)$i['jumlah_stok'] <= (int)$i['stok_minimum']) $criticalCount++;
        $db=Database::connection();
        $bahan=$db->query("SELECT b.*, COALESCE(SUM(CASE WHEN k.jenis='MASUK' THEN k.jumlah WHEN k.jenis='KELUAR' THEN -k.jumlah ELSE 0 END),0) AS mutasi FROM bahan_baku b LEFT JOIN kartu_stok_bahan k ON k.bahan_baku_id=b.id GROUP BY b.id ORDER BY b.tanggal_kadaluarsa ASC, b.id ASC")->fetchAll();
        $bahanPurchases=$inventory->purchases(20);
        require BASE_PATH.'/view/admin/menu.php';
    }
    public function saveMenu(): void {
        $this->guard();
        verify_csrf();
        $m=new MenuModel();
        $d=$_POST;
        $d['deskripsi']=trim((string)($_POST['deskripsi']??''));
        if (empty($d['kategori_id'])) {
            flash('error',
            'Kategori produk wajib dipilih.');
            redirect(APP_URL.'/index.php?r=admin/menu#produk-form');
        }
        $d['punya_topping']=isset($_POST['punya_topping'])?1:0;
        $d['punya_level_pedas']=isset($_POST['punya_level_pedas'])?1:0;
        $d['stok_awal']=(int)($_POST['stok_awal']??0);
        $id=!empty($_POST['id'])?(int)$_POST['id']:null;
        $contentLength=(int)($_SERVER['CONTENT_LENGTH']??0);
        $postMax=ini_bytes((string)ini_get('post_max_size'));
        if($contentLength>0 && $postMax>0 && $contentLength>$postMax && empty($_FILES['foto'])){
            flash('error',
            'Upload tidak diproses karena total data form melebihi batas post_max_size server. Kecilkan foto atau naikkan post_max_size di PHP.');
            redirect(APP_URL.'/index.php?r=admin/menu');
        }
        $uploadProvided=isset($_FILES['foto']) && (($_FILES['foto']['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_NO_FILE || !empty($_FILES['foto']['name']));
        if($uploadProvided){
            $file=$_FILES['foto'];
            $uploadError=(int)($file['error']??UPLOAD_ERR_NO_FILE);
            if($uploadError!==UPLOAD_ERR_OK){
                flash('error',
                upload_error_message($uploadError));
                redirect(APP_URL.'/index.php?r=admin/menu');
            }
            if(!is_uploaded_file($file['tmp_name']??'')){
                flash('error',
                'File foto tidak dikenali sebagai upload yang valid. Silakan pilih ulang foto.');
                redirect(APP_URL.'/index.php?r=admin/menu');
            }
            if((int)($file['size']??0)>MAX_IMAGE_UPLOAD_BYTES){
                flash('error',
                'Ukuran foto maksimal 6 MB.');
                redirect(APP_URL.'/index.php?r=admin/menu');
            }
            $mime=uploaded_image_mime($file);
            $allowed=['image/jpeg'=>'jpg',
            'image/png'=>'png',
            'image/webp'=>'webp'];
            if(!isset($allowed[$mime])){
                flash('error',
                'Format foto harus JPG, PNG, atau WEBP. Pastikan file benar-benar gambar.');
                redirect(APP_URL.'/index.php?r=admin/menu');
            }
            $dir=BASE_PATH.'/public/assets/uploads/menu';
            if(!is_dir($dir) && !mkdir($dir,
            0755,
            true) && !is_dir($dir)){
                flash('error',
                'Folder penyimpanan foto produk tidak dapat dibuat oleh server.');
                redirect(APP_URL.'/index.php?r=admin/menu');
            }
            if(!is_writable($dir)){
                flash('error',
                'Folder penyimpanan foto produk tidak dapat ditulis server. Periksa permission folder uploads/menu.');
                redirect(APP_URL.'/index.php?r=admin/menu');
            }
            $name='menu-'.bin2hex(random_bytes(8)).'.'.$allowed[$mime];
            if(!move_uploaded_file($file['tmp_name'],
            $dir.'/'.$name)){
                flash('error',
                'Foto produk gagal disimpan ke server. Periksa permission folder uploads/menu.');
                redirect(APP_URL.'/index.php?r=admin/menu');
            }
            $d['foto']='assets/uploads/menu/'.$name;
        }  else {
            $d['foto']=isset($_POST['hapus_foto']) && $_POST['hapus_foto']==='1' ? null : ($_POST['foto_lama']??null);
        }
        try {
            $id=$m->save($d,
            $id);
            $m->syncToppings($id,
            $_POST['topping_ids']??[]);
            flash('success',
            'Produk berhasil disimpan.');
        }  catch(Throwable $e) {
            flash('error',
            'Produk gagal disimpan. Periksa data produk dan koneksi database.');
        }
        redirect(APP_URL.'/index.php?r=admin/menu');
    }
    public function deleteMenu(): void {
        $this->guard();
        verify_csrf();
        $id=(int)($_POST['id']??0);
        if($id>0){
            (new MenuModel())->setActive($id,
            0);
            flash('success',
            'Produk berhasil diarsipkan. Produk tidak tampil di katalog pelanggan.');
        }
        redirect(APP_URL.'/index.php?r=admin/menu');
    }
    public function hardDeleteMenu(): void {
        $this->guard();
        verify_csrf();
        $id=(int)($_POST['id']??0);
        try {
            if($id>0){
                $m=new MenuModel();
                $product=$m->byId($id);
                if(!$product) throw new RuntimeException('Produk tidak ditemukan.');
                $m->deletePermanently($id);
                if(!empty($product['foto'])){
                    $path=BASE_PATH.'/public/'.ltrim((string)$product['foto'],
                    '/');
                    if(is_file($path)) @unlink($path);
                }
                flash('success',
                'Produk berhasil dihapus permanen.');
            }
        }  catch(Throwable $e) {
            flash('error',
            $e->getMessage());
        }
        redirect(APP_URL.'/index.php?r=admin/menu');
    }
    public function restoreMenu(): void {
        $this->guard();
        verify_csrf();
        $id=(int)($_POST['id']??0);
        if($id>0){
            (new MenuModel())->setActive($id,
            1);
            flash('success',
            'Produk diaktifkan kembali.');
        }
        redirect(APP_URL.'/index.php?r=admin/menu');
    }
    public function saveCategory(): void {
        $this->guard();
        verify_csrf();
        $name=trim($_POST['nama']??'');
        $id=(int)($_POST['id']??0);
        if($name!=='') (new MenuModel())->saveCategory($name,
        $id?:null);
        else flash('error',
        'Nama kategori wajib diisi.');
        redirect(APP_URL.'/index.php?r=admin/menu#master');
    }
    public function deleteCategory(): void {
        $this->guard();
        verify_csrf();
        $id=(int)($_POST['id']??0);
        try {
            if($id>0) (new MenuModel())->deleteCategory($id);
            flash('success',
            'Kategori dihapus.');
        }  catch(Throwable $e){
            flash('error',
            'Kategori tidak dapat dihapus karena masih dipakai produk.');
        }
        redirect(APP_URL.'/index.php?r=admin/menu#master');
    }
    public function saveTopping(): void {
        $this->guard();
        verify_csrf();
        $id=(int)($_POST['id']??0);
        $name=trim($_POST['nama']??'');
        $price=max(0,
        (float)($_POST['harga_tambahan']??0));
        if($name===''){
            flash('error',
            'Nama topping wajib diisi.');
            redirect(APP_URL.'/index.php?r=admin/menu#master');
        }
        (new MenuModel())->saveTopping($name,
        $price,
        $id?:null);
        flash('success',
        'Topping berhasil disimpan.');
        redirect(APP_URL.'/index.php?r=admin/menu#master');
    }
    public function deleteTopping(): void {
        $this->guard();
        verify_csrf();
        $id=(int)($_POST['id']??0);
        try {
            if($id>0) (new MenuModel())->deleteTopping($id);
            flash('success',
            'Topping dihapus.');
        }  catch(Throwable $e){
            flash('error',
            'Topping masih digunakan oleh produk.');
        }
        redirect(APP_URL.'/index.php?r=admin/menu#master');
    }
    public function tables(): void {
        redirect(APP_URL.'/index.php?r=admin/operations&tab=meja');
    }
    public function addTable(): void {
        $this->guard();
        verify_csrf();
        $n=trim($_POST['nomor_meja']??'');
        if($n===''){
            flash('error',
            'Nomor meja wajib diisi.');
            redirect(APP_URL.'/index.php?r=admin/operations&tab=meja');
        }
        $db=Database::connection();
        $db->prepare("INSERT INTO meja(nomor_meja,status_aktif) VALUES(?,1)")->execute([$n]);
        redirect(APP_URL.'/index.php?r=admin/operations&tab=meja');
    }
    public function editTable(): void {
        $this->guard();
        verify_csrf();
        $id=(int)($_POST['id']??0);
        $n=trim($_POST['nomor_meja']??'');
        if($id<1 || $n===''){
            flash('error',
            'Data meja tidak lengkap.');
            redirect(APP_URL.'/index.php?r=admin/operations&tab=meja');
        }
        Database::connection()->prepare("UPDATE meja SET nomor_meja=? WHERE id=?")->execute([$n,
        $id]);
        redirect(APP_URL.'/index.php?r=admin/operations&tab=meja');
    }
    public function deleteTable(): void {
        $this->guard();
        verify_csrf();
        $id=(int)($_POST['id']??0);
        if($id>0) Database::connection()->prepare("DELETE FROM meja WHERE id=?")->execute([$id]);
        redirect(APP_URL.'/index.php?r=admin/operations&tab=meja');
    }
    public function toggleTable(): void {
        $this->guard();
        verify_csrf();
        $id=(int)($_POST['id']??0);
        Database::connection()->prepare("UPDATE meja SET status_aktif=IF(status_aktif=1,0,1) WHERE id=?")->execute([$id]);
        redirect(APP_URL.'/index.php?r=admin/operations&tab=meja');
    }
    public function customers(): void {
        $this->guard();
        $db=Database::connection();
        $customers=$db->query("SELECT p.*,COALESCE(NULLIF(TRIM(p.nama),''),NULLIF(TRIM(p.username),''),'Pelanggan') AS nama,m.level FROM pelanggan p LEFT JOIN membership m ON m.pelanggan_id=p.id ORDER BY p.id DESC")->fetchAll();
        // Status lama MENUNGGU dinormalisasi: ulasan baru langsung tampil, tanpa tahap menunggu.
        $db->exec("UPDATE ulasan_pelanggan SET status_ulasan='DITAMPILKAN', updated_at=NOW() WHERE status_ulasan='MENUNGGU'");
        $status=$_GET['status']??'';
        $rating=(int)($_GET['rating']??0);
        $search=trim($_GET['q']??'');
        $where=[];
        $args=[];
        if(in_array($status,
        ['DITAMPILKAN',
        'DISEMBUNYIKAN'],
        true)){
            $where[]='u.status_ulasan=?';
            $args[]=$status;
        }
        if($rating>=1&&$rating<=5){
            $where[]='u.rating=?';
            $args[]=$rating;
        }
        if($search!==''){
            $where[]='(p.nama LIKE ? OR p.username LIKE ? OR CAST(t.id AS CHAR)=?)';
            $args[]='%'.$search.'%';
            $args[]='%'.$search.'%';
            $args[]=$search;
        }
        $sql="SELECT u.*,COALESCE(NULLIF(TRIM(p.nama),''),NULLIF(TRIM(p.username),''),'Pelanggan') AS nama,p.username,t.tanggal FROM ulasan_pelanggan u JOIN pelanggan p ON p.id=u.pelanggan_id JOIN transaksi_penjualan t ON t.id=u.transaksi_id".($where?" WHERE ".implode(" AND ",
        $where):"")." ORDER BY u.created_at DESC";
        $stmt=$db->prepare($sql);
        $stmt->execute($args);
        $reviews=$stmt->fetchAll();
        $stats=$db->query("SELECT COUNT(*) jumlah,COALESCE(AVG(rating),0) rata FROM ulasan_pelanggan WHERE status_ulasan='DITAMPILKAN'")->fetch();
        $title='Pelanggan & Ulasan';
        require BASE_PATH.'/view/admin/customers.php';
    }
    public function toggleCustomer(): void {
        $this->guard();
        verify_csrf();
        $id=(int)$_POST['id'];
        $status=$_POST['status']==='DIBLOKIR'?'DIBLOKIR':'AKTIF';
        Database::connection()->prepare("UPDATE pelanggan SET status=? WHERE id=?")->execute([$status,
        $id]);
        redirect(APP_URL.'/index.php?r=admin/customers');
    }
    public function export(): void {
        $this->guard();
        $type=$_GET['type']??'sales';
        header('Content-Type:text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=laporan-'.$type.'-'.date('Ymd').'.csv');
        $out=fopen('php://output',
        'w');
        $db=Database::connection();
        if($type==='sales'){
            $r=$db->query("SELECT t.nomor_pesanan,COALESCE(NULLIF(TRIM(p.nama),''),NULLIF(TRIM(p.username),''),'Pelanggan') AS pelanggan,m.nomor_meja,t.metode_bayar,t.status_pesanan,t.total_bayar,t.tanggal FROM transaksi_penjualan t JOIN pelanggan p ON p.id=t.pelanggan_id LEFT JOIN meja m ON m.id=t.meja_id ORDER BY t.id DESC");
            fputcsv($out,
            ['No. Pesanan',
            'Pelanggan',
            'Meja',
            'Bayar',
            'Status',
            'Total',
            'Tanggal']);
        } else{
            $r=$db->query("SELECT tanggal,jenis,kategori,jumlah,keterangan FROM keuangan ORDER BY tanggal DESC");
            fputcsv($out,
            ['Tanggal',
            'Jenis',
            'Kategori',
            'Jumlah',
            'Keterangan']);
        }
foreach($r as $row)fputcsv($out,
        $row);
        fclose($out);
        exit;
    }
}
