<?php $title='Keuangan & Laporan';
require BASE_PATH.'/view/partials/header.php';
?>
<div class="admin-content">

  <!-- Filter periode -->
  <div class="page-heading">
    <div>
      <span class="eyebrow">KAS & LAPORAN</span>
      <h1 class="page-title mb-0">Keuangan</h1>
      <p class="page-subtitle mb-0">Ringkasan pemasukan, pengeluaran, dan riwayat transaksi warung.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
      <a class="btn btn-secondary-soft" href="<?=APP_URL?>/index.php?r=admin/export&type=sales"><?= icon('download') ?> Export Penjualan</a>
      <a class="btn btn-secondary-soft" href="<?=APP_URL?>/index.php?r=admin/export&type=finance"><?= icon('download') ?> Export Keuangan</a>
    </div>
  </div>

  <section class="panel-card mb-3">
    <div class="panel-header">
      <div><span class="eyebrow">Periode laporan</span><h2 class="section-title mb-0">Pilih rentang tanggal</h2></div>
    </div>
    <form class="row g-2 p-4 align-items-end" method="get">
      <input type="hidden" name="r" value="admin/finance">
      <div class="col-md-4"><label class="form-label">Dari tanggal</label><input class="form-control" type="date" name="from" value="<?=e($from)?>"></div>
      <div class="col-md-4"><label class="form-label">Sampai tanggal</label><input class="form-control" type="date" name="to" value="<?=e($to)?>"></div>
      <div class="col-md-4"><button class="btn btn-dark w-100"><?= icon('filter') ?> Terapkan</button></div>
    </form>
  </section>

  <!-- Ringkasan -->
  <div class="stat-grid mb-3">
    <div class="stat-card">
      <span class="stat-label"><?= icon('trending-up') ?> Total Pendapatan</span>
      <span class="stat-value">Rp <?=number_format($summary['PEMASUKAN_TOTAL'],
0,
',',
'.')?></span>
      <span class="stat-note">Penjualan selesai: Rp <?=number_format($summary['PENJUALAN'],
0,
',',
'.')?><br>Pemasukan manual: Rp <?=number_format($summary['PEMASUKAN'],
0,
',',
'.')?></span>
    </div>
    <div class="stat-card">
      <span class="stat-label"><?= icon('trending-down') ?> Uang Pengeluaran</span>
      <span class="stat-value">− Rp <?=number_format($summary['PENGELUARAN'],
0,
',',
'.')?></span>
      <span class="stat-note">Total kas keluar pada periode terpilih</span>
    </div>
    <div class="stat-card <?= $summary['LABA_RUGI'] < 0 ? 'stat-card-alert' : '' ?>">
      <span class="stat-label"><?= icon('wallet') ?> <?= $summary['LABA_RUGI'] >= 0 ? 'Pendapatan Bersih' : 'Rugi' ?></span>
      <span class="stat-value"><?= $summary['LABA_RUGI'] < 0 ? '− ' : '' ?>Rp <?=number_format(abs($summary['LABA_RUGI']),
0,
',',
'.')?></span>
      <span class="stat-note">Total Pendapatan − Uang Pengeluaran</span>
    </div>
  </div>

  <!-- Input manual -->
  <section class="panel-card mb-3">
    <div class="panel-header">
      <div><span class="eyebrow">Pencatatan kas</span><h2 class="section-title mb-0">Input Manual</h2><p class="page-subtitle mb-0 mt-2">Catat pemasukan atau pengeluaran kas di luar transaksi penjualan dan pembelian bahan.</p></div>
      <button type="button" class="btn btn-primary" data-modal-open="#crudModal-financeManual"><?= icon('plus') ?> Pencatatan Kas</button>
    </div>
    <form id="financeManual" method="post" action="<?=APP_URL?>/index.php?r=admin/add-finance" class="row g-3 p-4" data-crud-toggle data-crud-no-trigger data-crud-label="Pencatatan Kas">
      <?php echo csrf_field();
?>
      <div class="col-md-3"><label class="form-label">Tanggal</label><input class="form-control" type="date" name="tanggal" value="<?=date('Y-m-d')?>" required></div>
      <div class="col-md-3"><label class="form-label">Jenis</label><select class="form-select" name="jenis"><option>PEMASUKAN</option><option>PENGELUARAN</option></select></div>
      <div class="col-md-3"><label class="form-label">Kategori</label><input class="form-control" name="kategori" placeholder="Kategori" required></div>
      <div class="col-md-3"><label class="form-label">Jumlah (Rp)</label><input class="form-control" type="number" name="jumlah" min="1" required></div>
      <div class="col-12"><label class="form-label">Keterangan</label><input class="form-control" name="keterangan" placeholder="Keterangan"></div>
      <div class="col-12 form-actions">
        <button class="btn btn-primary" type="submit"><?= icon('check') ?> Simpan</button>
        <button class="btn btn-secondary-soft" type="button" data-modal-close>Batal</button>
      </div>
    </form>
  </section>

  <?php $qris = $qrisDiagnostics ?? null; ?>
  <?php if($qris): ?>
  <section class="panel-card mb-3">
    <div class="panel-header">
      <div><span class="eyebrow">Payment gateway</span><h2 class="section-title mb-0">Midtrans QRIS</h2><p class="page-subtitle mb-0 mt-2">Diagnostik konfigurasi. Server Key tidak pernah ditampilkan di halaman ini.</p></div>
      <span class="status-chip <?= !empty($qris['ready']) ? 'status-selesai' : 'status-dibatalkan' ?>"><?= !empty($qris['ready']) ? 'Siap' : 'Perlu diperiksa' ?></span>
    </div>
    <div class="table-responsive admin-table-wrap">
      <table class="table admin-table align-middle mb-0">
        <tbody>
          <tr><th>Provider</th><td><?=e($qris['provider'])?> <?= $qris['provider_ok'] ? '✓' : '—' ?></td></tr>
          <tr><th>Mode</th><td><?=e($qris['mode'])?> <?= $qris['mode_valid'] ? '✓' : '—' ?></td></tr>
          <tr><th>Server Key</th><td><?= !empty($qris['server_key_set']) ? 'Terisi' : 'Belum diisi' ?></td></tr>
          <tr><th>Client Key</th><td><?= !empty($qris['client_key_set']) ? 'Terisi (opsional untuk Core API)' : 'Belum diisi (opsional untuk Core API)' ?></td></tr>
          <tr><th>cURL</th><td><?= !empty($qris['curl_ok']) ? 'Aktif' : 'Tidak aktif' ?></td></tr>
          <tr><th>Webhook</th><td><code><?=e($qris['webhook_url'])?></code></td></tr>
        </tbody>
      </table>
    </div>
  </section>
  <?php endif; ?>

  <!-- Riwayat penjualan -->
  <section class="panel-card mb-3">
    <div class="panel-header">
      <div><span class="eyebrow">Laporan penjualan</span><h2 class="section-title mb-0">Riwayat Transaksi Penjualan</h2><p class="page-subtitle mb-0 mt-2">Nomor pesanan tersimpan di database dan menjadi identitas transaksi yang dipakai pada riwayat pelanggan serta laporan keuangan.</p></div>
    </div>
    <div class="table-responsive admin-table-wrap">
      <table class="table admin-table align-middle mb-0">
        <thead><tr><th>No. Pesanan</th><th>Tanggal</th><th>Pelanggan</th><th>Jenis</th><th>Pembayaran</th><th>Status</th><th class="text-end">Total</th></tr></thead>
        <tbody>
        <?php if(empty($transactions)): ?>
          <tr><td colspan="7"><div class="empty-state"><strong>Belum ada transaksi selesai</strong><span>Tidak ada transaksi selesai pada periode ini.</span></div></td></tr>
        <?php else: foreach($transactions as $tx): ?>
          <tr>
            <td><strong><?=e($tx['nomor_pesanan'])?></strong></td>
            <td><?=e($tx['tanggal'])?></td>
            <td><?=e($tx['pelanggan'])?></td>
            <td><?=e($tx['jenis_pesanan'])?></td>
            <td><?=e($tx['metode_bayar'])?></td>
            <td><?=e($tx['status_pesanan'])?></td>
            <td class="text-end">Rp <?=number_format($tx['total_bayar'],
0,
',',
'.')?></td>
          </tr>
        <?php endforeach;
endif;
?>
        </tbody>
      </table>
    </div>
  </section>

  <div class="row g-3 mb-3">
    <div class="col-lg-7">
      <section class="panel-card h-100">
        <div class="panel-header"><div><span class="eyebrow">Rincian harian</span><h2 class="section-title mb-0">Penjualan Harian</h2><p class="page-subtitle mb-0 mt-2">Penjualan dari transaksi berstatus Selesai, dihitung otomatis sebagai pendapatan pada periode yang dipilih.</p></div></div>
        <div class="table-responsive admin-table-wrap">
          <table class="table admin-table mb-0">
            <thead><tr><th>Tanggal</th><th>Transaksi</th><th class="text-end">Total</th></tr></thead>
            <tbody><?php foreach($sales as $s): ?><tr><td><?=e($s['tanggal'])?></td><td><?=$s['transaksi']?></td><td class="text-end">Rp <?=number_format($s['total'],
0,
',',
'.')?></td></tr><?php endforeach;
?></tbody>
          </table>
        </div>
      </section>
    </div>
    <div class="col-lg-5">
      <section class="panel-card h-100">
        <div class="panel-header"><div><span class="eyebrow">Favorit pelanggan</span><h2 class="section-title mb-0">Produk Terlaris</h2></div></div>
        <div class="table-responsive admin-table-wrap">
          <table class="table admin-table mb-0">
            <thead><tr><th>Produk</th><th class="text-end">Qty</th></tr></thead>
            <tbody><?php foreach($top as $p): ?><tr><td><?=e($p['nama_produk'])?></td><td class="text-end"><?=$p['qty']?></td></tr><?php endforeach;
?></tbody>
          </table>
        </div>
      </section>
    </div>
  </div>

</div>
<?php require BASE_PATH.'/view/partials/footer.php';
?>
