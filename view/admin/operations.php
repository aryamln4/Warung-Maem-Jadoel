<?php
$tab = $tab ?? 'pengeluaran';
$titles = ['pengeluaran'=>'Pengeluaran',
'toko'=>'Toko & Ongkir',
'stok'=>'Stok Produk',
'bahan'=>'Stok Bahan Baku',
'meja'=>'No. Meja'];
$title = $title ?? ($titles[$tab] ?? 'Operasional');
require BASE_PATH.'/view/partials/header.php';
$isExpense = $tab === 'pengeluaran';
$isStore = $tab === 'toko';
$activeCount = 0;
$criticalCount = 0;
foreach ($items as $i) {
    if ((int)$i['jumlah_stok'] <= (int)$i['stok_minimum']) $criticalCount++;
}
foreach ($tables as $t) if ((int)$t['status_aktif'] === 1) $activeCount++;
$expenseSummary = $expenseSummary ?? ['jumlah'=>0,
'total'=>0,
'kategori'=>[]];
$expenses = $expenses ?? [];
$expenseCategories = $expenseCategories ?? [];
$editExpense = $editExpense ?? null;
$storeSettings = $storeSettings ?? [];
$topCategory = $expenseSummary['kategori'][0] ?? null;
?>
<div class="admin-content ops-redesign">
  <nav class="admin-tabs ops-tabs" aria-label="Operasional warung">
    <a class="admin-tab <?= $isExpense ? 'active' : '' ?>" href="<?=APP_URL?>/index.php?r=admin/operations&tab=pengeluaran" <?= $isExpense ? 'aria-current="page"' : '' ?>>Pengeluaran</a>
    <a class="admin-tab <?= $isStore ? 'active' : '' ?>" href="<?=APP_URL?>/index.php?r=admin/operations&tab=toko" <?= $isStore ? 'aria-current="page"' : '' ?>>Toko & Ongkir</a>
    <a class="admin-tab <?= $tab === 'meja' ? 'active' : '' ?>" href="<?=APP_URL?>/index.php?r=admin/operations&tab=meja" <?= $tab === 'meja' ? 'aria-current="page"' : '' ?>>No. Meja</a>
  </nav>

  <?php if($msg=flash('success')): ?><div class="alert alert-success ops-alert"><?=e($msg)?></div><?php endif;
?>
  <?php if($msg=flash('error')): ?><div class="alert alert-danger ops-alert"><?=e($msg)?></div><?php endif;
?>

  <?php if($isExpense): ?>
    <section class="ops-workspace">
      <div class="ops-summary-grid">
        <div class="ops-summary-card"><span>Total Pengeluaran</span><strong style="font-size:1.35rem"><?=rupiah($expenseSummary['total'])?></strong><small><?=e(date('d M',
strtotime($from)))?> Ã¢â‚¬â€œ <?=e(date('d M Y',
strtotime($to)))?></small></div>
        <div class="ops-summary-card"><span>Catatan</span><strong><?=(int)$expenseSummary['jumlah']?></strong><small>transaksi operasional</small></div>
        <div class="ops-summary-card ops-summary-accent"><span>Kategori Terbesar</span><strong style="font-size:1.05rem"><?= $topCategory ? e($topCategory['kategori']) : 'Ã¢â‚¬â€' ?></strong><small><?= $topCategory ? rupiah($topCategory['total']) : 'belum ada data' ?></small></div>
      </div>

      <section class="ops-panel">
        <div class="ops-panel-head">
          <div><span class="ops-kicker">CATAT KAS KELUAR</span><h2><?= $editExpense ? 'Ubah pengeluaran' : 'Catat pengeluaran' ?></h2><p>Gas, listrik, gaji, bahan, dan biaya lain otomatis mengurangi laba pada laporan Keuangan.</p></div>
          <?php if($editExpense): ?><a class="ops-ghost-btn" href="<?=APP_URL?>/index.php?r=admin/operations&tab=pengeluaran">Batal edit</a><?php endif;
?>
        </div>
        <form method="post" action="<?=APP_URL?>/index.php?r=admin/save-expense" data-crud-toggle data-crud-label="Form Pengeluaran" class="ops-expense-form">
          <?=csrf_field()?>
          <?php if($editExpense): ?><input type="hidden" name="id" value="<?=(int)$editExpense['id']?>"><?php endif;
?>
          <div class="row g-3 p-3">
            <div class="col-md-3"><label class="form-label">Tanggal</label><input class="form-control" type="date" name="tanggal" required value="<?=e($editExpense['tanggal'] ?? date('Y-m-d'))?>"></div>
            <div class="col-md-3"><label class="form-label">Kategori</label><input class="form-control" name="kategori" list="expenseCategories" required maxlength="80" placeholder="Gas, listrik, gajiÃ¢â‚¬Â¦" value="<?=e($editExpense['kategori'] ?? '')?>">
              <datalist id="expenseCategories"><?php foreach($expenseCategories as $c): ?><option value="<?=e($c)?>"><?php endforeach;
?><option value="Gas"><option value="Listrik"><option value="Air"><option value="Gaji"><option value="Bahan Baku"><option value="Kemasan"><option value="Transportasi"><option value="Lainnya"></datalist>
            </div>
            <div class="col-md-3"><label class="form-label">Jumlah (Rp)</label><input class="form-control" type="number" name="jumlah" min="1" step="1" required value="<?= $editExpense ? (float)$editExpense['jumlah'] : '' ?>"></div>
            <div class="col-md-3"><label class="form-label">Bukti <span style="font-weight:400;color:var(--c-muted)">(opsional)</span></label><input class="form-control" name="bukti" maxlength="255" placeholder="No. nota / tautan" value="<?=e($editExpense['bukti'] ?? '')?>"></div>
            <div class="col-12"><label class="form-label">Keterangan</label><input class="form-control" name="keterangan" maxlength="255" placeholder="Contoh: Isi ulang gas 3 kg dapur" value="<?=e($editExpense['keterangan'] ?? '')?>"></div>
            <div class="col-12"><button class="ops-primary-btn" type="submit"><?= $editExpense ? 'Simpan Perubahan' : 'Catat Pengeluaran' ?></button></div>
          </div>
        </form>
      </section>

      <section class="ops-panel" style="margin-top:18px">
        <div class="ops-panel-head">
          <div><span class="ops-kicker">RIWAYAT</span><h2>Pengeluaran operasional periode ini</h2><p>Data ini sama dengan yang dipakai laporan laba/rugi di halaman Keuangan. Pembelian bahan baku otomatis tercatat dari tab Produk &amp; Stok â†’ Stok Bahan Baku.</p></div>
          <form method="get" class="ops-date-filter">
            <input type="hidden" name="r" value="admin/operations"><input type="hidden" name="tab" value="pengeluaran">
            <input type="date" name="from" value="<?=e($from)?>"><span>s/d</span><input type="date" name="to" value="<?=e($to)?>">
            <button class="ops-ghost-btn" type="submit">Terapkan</button>
          </form>
        </div>
        <div class="table-responsive p-3">
          <table class="table align-middle">
            <thead><tr><th>Tanggal</th><th>Kategori</th><th>Keterangan</th><th>Bukti</th><th>Dicatat</th><th class="text-end">Jumlah</th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
            <?php if(!$expenses): ?><tr><td colspan="7" class="text-center text-muted py-4">Belum ada pengeluaran pada periode ini.</td></tr><?php endif;
?>
            <?php foreach($expenses as $x): ?>
              <tr>
                <td><?=e(date('d M Y',
strtotime($x['tanggal'])))?></td>
                <td><span class="badge text-bg-light"><?=e($x['kategori'])?></span></td>
                <td><?=e($x['keterangan'] ?: 'Ã¢â‚¬â€')?></td>
                <td><?=e($x['bukti'] ?: 'Ã¢â‚¬â€')?></td>
                <td><small><?=e($x['created_by'] ?: 'admin')?></small></td>
                <td class="text-end"><strong>Ã¢Ë†â€™ <?=rupiah($x['jumlah'])?></strong></td>
                <td class="text-end" style="white-space:nowrap">
                  <a class="ops-ghost-btn" href="<?=APP_URL?>/index.php?r=admin/operations&tab=pengeluaran&edit_expense=<?=$x['id']?>">Edit</a>
                  <form method="post" action="<?=APP_URL?>/index.php?r=admin/delete-expense" class="d-inline" onsubmit="return confirm('Hapus pengeluaran ini? Laporan laba/rugi ikut menyesuaikan.')"><?=csrf_field()?><input type="hidden" name="id" value="<?=$x['id']?>"><button class="ops-delete-btn" type="submit">Hapus</button></form>
                </td>
              </tr>
            <?php endforeach;
?>
            </tbody>
          </table>
        </div>
      </section>
    </section>

  <?php elseif($isStore): ?>
    <section class="ops-workspace">
      <div class="ops-summary-grid">
        <div class="ops-summary-card"><span>Ongkir Dasar</span><strong style="font-size:1.2rem"><?=rupiah($storeSettings['ongkir_dasar'] ?? 10000)?></strong><small>untuk <?=e(rtrim(rtrim(number_format((float)($storeSettings['jarak_dasar_km'] ?? 1),
2,
'.',
''),
'0'),
'.'))?> km pertama</small></div>
        <div class="ops-summary-card"><span>Tarif Tambahan</span><strong style="font-size:1.2rem"><?=rupiah($storeSettings['tarif_per_500m'] ?? 2000)?></strong><small>setiap 500 m berikutnya</small></div>
        <div class="ops-summary-card ops-summary-accent"><span>Batas Jangkauan</span><strong><?=e(rtrim(rtrim(number_format((float)($storeSettings['batas_jarak_km'] ?? 10),
2,
'.',
''),
'0'),
'.'))?> km</strong><small>alamat di luar ini ditolak</small></div>
      </div>

      <section class="ops-panel">
        <div class="ops-panel-head"><div><span class="ops-kicker">PENGATURAN TOKO</span><h2>Titik warung &amp; aturan ongkir</h2><p>Ongkir dihitung server-side dari jarak garis lurus (haversine) titik toko ke titik alamat pelanggan.</p></div></div>
        <form method="post" action="<?=APP_URL?>/index.php?r=admin/save-store" data-crud-toggle data-crud-label="Form Pengaturan Toko" class="row g-3 p-3">
          <?=csrf_field()?>
          <div class="col-md-6"><label class="form-label">Nama toko</label><input class="form-control" name="nama_toko" required maxlength="150" value="<?=e($storeSettings['nama_toko'] ?? 'Warung Maem Jadoel')?>"></div>
          <div class="col-md-6"><label class="form-label">Jam buka</label><input class="form-control" name="jam_buka" maxlength="80" placeholder="10.00 - 22.00" value="<?=e($storeSettings['jam_buka'] ?? '')?>"></div>
          <div class="col-12"><label class="form-label">Alamat toko</label><input class="form-control" name="alamat" maxlength="255" value="<?=e($storeSettings['alamat'] ?? '')?>"></div>
          <div class="col-md-6"><label class="form-label">Latitude</label><input class="form-control" name="latitude" type="number" step="0.0000001" required value="<?=e((string)($storeSettings['latitude'] ?? -6.4024840))?>"></div>
          <div class="col-md-6"><label class="form-label">Longitude</label><input class="form-control" name="longitude" type="number" step="0.0000001" required value="<?=e((string)($storeSettings['longitude'] ?? 106.7942430))?>"></div>
          <div class="col-md-3"><label class="form-label">Ongkir dasar (Rp)</label><input class="form-control" type="number" name="ongkir_dasar" min="0" step="500" required value="<?=e((string)($storeSettings['ongkir_dasar'] ?? 10000))?>"></div>
          <div class="col-md-3"><label class="form-label">Jarak dasar (km)</label><input class="form-control" type="number" name="jarak_dasar_km" min="0" step="0.1" required value="<?=e((string)($storeSettings['jarak_dasar_km'] ?? 1))?>"></div>
          <div class="col-md-3"><label class="form-label">Tarif / 500 m (Rp)</label><input class="form-control" type="number" name="tarif_per_500m" min="0" step="500" required value="<?=e((string)($storeSettings['tarif_per_500m'] ?? 2000))?>"></div>
          <div class="col-md-3"><label class="form-label">Batas jarak (km)</label><input class="form-control" type="number" name="batas_jarak_km" min="0" step="0.5" required value="<?=e((string)($storeSettings['batas_jarak_km'] ?? 10))?>"></div>
          <div class="col-12"><label class="form-label">Catatan</label><textarea class="form-control" name="catatan" rows="2" maxlength="500" placeholder="Contoh: pengantaran hanya sampai pukul 21.30"><?=e($storeSettings['catatan'] ?? '')?></textarea></div>
          <div class="col-12"><button class="ops-primary-btn" type="submit">Simpan Pengaturan</button></div>
        </form>
        <div class="ops-panel-note">
          <strong>Contoh hitung:</strong> jarak 2,3 km Ã¢â€ â€™ <?=rupiah($storeSettings['ongkir_dasar'] ?? 10000)?> + 3 Ãƒâ€” <?=rupiah($storeSettings['tarif_per_500m'] ?? 2000)?> = <?=rupiah((float)($storeSettings['ongkir_dasar'] ?? 10000) + 3 * (float)($storeSettings['tarif_per_500m'] ?? 2000))?>. Ongkir tetap <em>estimasi</em> sampai owner mengonfirmasi nominal final di papan Pesanan.
        </div>
      </section>
    </section>
  <?php else: ?>
    <section class="ops-workspace">
      <div class="ops-summary-grid">
        <div class="ops-summary-card"><span>Total Meja</span><strong><?=count($tables)?></strong><small>master meja tersimpan</small></div>
        <div class="ops-summary-card"><span>Meja Aktif</span><strong><?=$activeCount?></strong><small>muncul di checkout Dine-in</small></div>
        <div class="ops-summary-card ops-summary-accent"><span>QR Per Meja</span><strong>TIDAK</strong><small>QR tidak dipakai untuk meja</small></div>
      </div>

      <section class="ops-panel">
        <div class="ops-panel-head">
          <div><span class="ops-kicker">MASTER MEJA</span><h2>Daftar meja</h2><p>Kelola meja yang dapat dipilih pelanggan. Nonaktifkan meja tanpa menghapus data.</p></div>
          <span class="ops-count-badge"><?=count($tables)?> MEJA</span>
        </div>
        <div class="ops-add-table">
          <div><span class="ops-stock-label">TAMBAH MEJA</span><strong>Masukkan nomor atau nama meja</strong><small>Contoh: 1, 2, 3, 4 atau Ã¢â‚¬Å“Teras 1Ã¢â‚¬Â.</small></div>
          <form method="post" action="<?=APP_URL?>/index.php?r=admin/add-table" data-crud-toggle data-crud-label="Form Tambah Meja">
            <?=csrf_field()?>
            <input name="nomor_meja" placeholder="Contoh: 5" required>
            <button class="ops-primary-btn" type="submit">Tambah Meja <span>+</span></button>
          </form>
        </div>

        <div class="ops-table-grid">
          <?php if(!$tables): ?><div class="ops-empty"><strong>Belum ada meja.</strong><span>Tambahkan meja pertama melalui form di atas.</span></div><?php endif;
?>
          <?php foreach($tables as $t): $active=(int)$t['status_aktif']===1;
?>
            <article class="ops-table-card <?= $active?'active':'inactive' ?>">
              <div class="ops-table-top"><span class="ops-table-number">MEJA</span><span class="ops-table-status"><?=$active?'AKTIF':'NONAKTIF'?></span></div>
              <div class="ops-table-name">#<?=e($t['nomor_meja'])?></div>
              <div class="ops-table-meta">Dibuat <?=e(date('d M Y',
strtotime($t['created_at'])))?></div>
              <div class="ops-table-actions">
                <button type="button" class="ops-ghost-btn" data-collapse-target="#edit<?=$t['id']?>">Edit</button>
                <form method="post" action="<?=APP_URL?>/index.php?r=admin/toggle-table"><?=csrf_field()?><input type="hidden" name="id" value="<?=$t['id']?>"><button class="ops-ghost-btn"><?= $active ? 'Nonaktifkan' : 'Aktifkan' ?></button></form>
                <form method="post" action="<?=APP_URL?>/index.php?r=admin/delete-table" onsubmit="return confirm('Hapus meja ini? Riwayat transaksi tetap aman, tetapi transaksi lama tidak lagi menampilkan nomor meja.')"><?=csrf_field()?><input type="hidden" name="id" value="<?=$t['id']?>"><button class="ops-delete-btn">Hapus</button></form>
              </div>
              <div class="collapse ops-edit-table" id="edit<?=$t['id']?>">
                <form method="post" action="<?=APP_URL?>/index.php?r=admin/edit-table" data-crud-toggle data-crud-label="Form Edit Meja">
                  <?=csrf_field()?><input type="hidden" name="id" value="<?=$t['id']?>">
                  <input name="nomor_meja" value="<?=e($t['nomor_meja'])?>" required>
                  <button class="ops-primary-btn" type="submit">Simpan</button>
                </form>
              </div>
            </article>
          <?php endforeach;
?>
        </div>
      </section>
    </section>
  <?php endif;
?>
</div>
<?php require BASE_PATH.'/view/partials/footer.php';
?>
