<?php
$tab = $tab ?? 'pengeluaran';
$titles = ['pengeluaran'=>'Pengeluaran',
'toko'=>'Toko & Ongkir',
'meja'=>'No. Meja'];
$title = $title ?? ($titles[$tab] ?? 'Operasional');
require BASE_PATH.'/view/partials/header.php';
$isExpense = $tab === 'pengeluaran';
$isStore = $tab === 'toko';
$activeCount = 0;
$totalTables = count($tables ?? []);
foreach ($tables ?? [] as $t) if ((int)$t['status_aktif'] === 1) $activeCount++;
$expenseSummary = $expenseSummary ?? ['jumlah'=>0,
'total'=>0,
'kategori'=>[]];
$expenses = $expenses ?? [];
$expenseCategories = $expenseCategories ?? [];
$editExpense = $editExpense ?? null;
$storeSettings = $storeSettings ?? [];
$topCategory = $expenseSummary['kategori'][0] ?? null;
$baseFare = (float)($storeSettings['ongkir_dasar'] ?? 10000);
$baseKm = max(0, (float)($storeSettings['jarak_dasar_km'] ?? 1));
$per500 = (float)($storeSettings['tarif_per_500m'] ?? 2000);
$maxKm = (float)($storeSettings['batas_jarak_km'] ?? 10);
$sampleKm = 2.3;
$sampleSteps = (int)ceil(max(0, $sampleKm - $baseKm) / 0.5);
$sampleTotal = $baseFare + ($sampleSteps * $per500);
function ops_distance(?float $km): string {
    return rtrim(rtrim(number_format((float)$km, 2, ',', '.'), '0'), ',');
}
?>
<div class="admin-content">
  <nav class="admin-tabs" aria-label="Operasional warung">
    <a class="admin-tab <?= $isExpense ? 'active' : '' ?>" href="<?=APP_URL?>/index.php?r=admin/operations&tab=pengeluaran" <?= $isExpense ? 'aria-current="page"' : '' ?>>Pengeluaran</a>
    <a class="admin-tab <?= $isStore ? 'active' : '' ?>" href="<?=APP_URL?>/index.php?r=admin/operations&tab=toko" <?= $isStore ? 'aria-current="page"' : '' ?>>Toko &amp; Ongkir</a>
    <a class="admin-tab <?= $tab === 'meja' ? 'active' : '' ?>" href="<?=APP_URL?>/index.php?r=admin/operations&tab=meja" <?= $tab === 'meja' ? 'aria-current="page"' : '' ?>>No. Meja</a>
  </nav>

  <?php if($msg=flash('success')): ?><div class="alert alert-success ops-alert"><?=e($msg)?></div><?php endif;
?>
  <?php if($msg=flash('error')): ?><div class="alert alert-danger ops-alert"><?=e($msg)?></div><?php endif;
?>

  <?php if($isExpense): ?>
    <div class="stat-grid mb-3">
      <div class="stat-card">
        <span class="stat-label">Total Pengeluaran</span>
        <span class="stat-value" style="font-size:1.45rem"><?=rupiah($expenseSummary['total'])?></span>
        <span class="stat-note"><?=e(date('d M', strtotime($from)))?> – <?=e(date('d M Y', strtotime($to)))?></span>
      </div>
      <div class="stat-card">
        <span class="stat-label">Catatan</span>
        <span class="stat-value"><?=(int)$expenseSummary['jumlah']?></span>
        <span class="stat-note">Transaksi operasional pada periode ini.</span>
      </div>
      <div class="stat-card">
        <span class="stat-label">Kategori Terbesar</span>
        <span class="stat-value" style="font-size:1.2rem"><?= $topCategory ? e($topCategory['kategori']) : '—' ?></span>
        <span class="stat-note"><?= $topCategory ? rupiah($topCategory['total']) : 'Belum ada data pengeluaran.' ?></span>
      </div>
    </div>

    <section class="panel-card">
      <div class="panel-header">
        <div><span class="eyebrow">Kas keluar</span><h2 class="section-title mb-0">Riwayat pengeluaran</h2><p class="text-muted-custom mb-0">Gas, listrik, gaji, bahan, dan biaya lain otomatis mengurangi laba pada laporan Keuangan. Pembelian bahan baku tercatat otomatis dari tab Produk &amp; Stok → Stok Bahan Baku.</p></div>
        <div class="d-flex gap-2 flex-wrap">
          <?php if($editExpense): ?><a class="btn btn-secondary-soft" href="<?=APP_URL?>/index.php?r=admin/operations&tab=pengeluaran">Batalkan edit</a><?php endif;
?>
          <button type="button" class="btn btn-primary" data-modal-open="#crudModal-expenseForm"><?= icon($editExpense ? 'edit' : 'plus') ?> <?= $editExpense ? 'Edit Pengeluaran' : 'Catat Pengeluaran' ?></button>
        </div>
      </div>
      <div class="admin-table-toolbar">
        <form method="get" class="admin-inline-filter">
          <input type="hidden" name="r" value="admin/operations"><input type="hidden" name="tab" value="pengeluaran">
          <div><label class="form-label">Dari</label><input class="form-control" type="date" name="from" value="<?=e($from)?>"></div>
          <span class="filter-separator">s/d</span>
          <div><label class="form-label">Sampai</label><input class="form-control" type="date" name="to" value="<?=e($to)?>"></div>
          <button class="btn btn-secondary-soft" type="submit"><?= icon('filter') ?> Terapkan</button>
        </form>
      </div>
      <div class="table-responsive admin-table-wrap">
        <table class="table admin-table align-middle mb-0">
          <thead><tr><th>Tanggal</th><th>Kategori</th><th>Keterangan</th><th>Bukti</th><th>Dicatat</th><th class="text-end">Jumlah</th><th class="text-end">Aksi</th></tr></thead>
          <tbody>
          <?php if(!$expenses): ?><tr><td colspan="7"><div class="empty-state"><strong>Belum ada pengeluaran.</strong><span>Tidak ada pengeluaran pada periode ini.</span></div></td></tr><?php endif;
?>
          <?php foreach($expenses as $x): ?>
            <tr>
              <td><?=e(date('d M Y', strtotime($x['tanggal'])))?></td>
              <td><span class="badge text-bg-light"><?=e($x['kategori'])?></span></td>
              <td><?=e($x['keterangan'] ?: '—')?></td>
              <td><?=e($x['bukti'] ?: '—')?></td>
              <td><small><?=e($x['created_by'] ?: 'admin')?></small></td>
              <td class="text-end"><strong>− <?=rupiah($x['jumlah'])?></strong></td>
              <td class="text-end" style="white-space:nowrap">
                <div class="table-actions">
                  <a class="btn btn-sm btn-secondary-soft" href="<?=APP_URL?>/index.php?r=admin/operations&tab=pengeluaran&edit_expense=<?=$x['id']?>">Edit</a>
                  <form method="post" action="<?=APP_URL?>/index.php?r=admin/delete-expense" data-confirm="Hapus pengeluaran ini? Laporan laba/rugi ikut menyesuaikan."><?=csrf_field()?><input type="hidden" name="id" value="<?=$x['id']?>"><button class="btn btn-sm btn-outline-danger" type="submit">Hapus</button></form>
                </div>
              </td>
            </tr>
          <?php endforeach;
?>
          </tbody>
        </table>
      </div>
    </section>

    <form id="expenseForm" method="post" action="<?=APP_URL?>/index.php?r=admin/save-expense" data-crud-toggle data-crud-no-trigger data-crud-label="<?= $editExpense ? 'Edit Pengeluaran' : 'Catat Pengeluaran' ?>" class="row g-3">
      <?=csrf_field()?>
      <?php if($editExpense): ?><input type="hidden" name="id" value="<?=(int)$editExpense['id']?>"><?php endif;
?>
      <div class="col-md-4"><label class="form-label">Tanggal <span class="required-mark">*</span></label><input class="form-control" type="date" name="tanggal" required value="<?=e($editExpense['tanggal'] ?? date('Y-m-d'))?>"></div>
      <div class="col-md-4"><label class="form-label">Kategori <span class="required-mark">*</span></label><input class="form-control" name="kategori" list="expenseCategories" required maxlength="80" placeholder="Gas, listrik, gaji…" value="<?=e($editExpense['kategori'] ?? '')?>">
        <datalist id="expenseCategories"><?php foreach($expenseCategories as $c): ?><option value="<?=e($c)?>"><?php endforeach;
?><option value="Gas"><option value="Listrik"><option value="Air"><option value="Gaji"><option value="Bahan Baku"><option value="Kemasan"><option value="Transportasi"><option value="Lainnya"></datalist>
      </div>
      <div class="col-md-4"><label class="form-label">Jumlah (Rp) <span class="required-mark">*</span></label><input class="form-control" type="number" name="jumlah" min="1" step="1" required value="<?= $editExpense ? (float)$editExpense['jumlah'] : '' ?>"></div>
      <div class="col-md-6"><label class="form-label">Bukti <span class="label-optional">(opsional)</span></label><input class="form-control" name="bukti" maxlength="255" placeholder="No. nota / tautan" value="<?=e($editExpense['bukti'] ?? '')?>"></div>
      <div class="col-md-6"><label class="form-label">Keterangan</label><input class="form-control" name="keterangan" maxlength="255" placeholder="Contoh: Isi ulang gas 3 kg dapur" value="<?=e($editExpense['keterangan'] ?? '')?>"></div>
      <div class="col-12 form-actions"><button class="btn btn-primary" type="submit"><?= $editExpense ? 'Simpan Perubahan' : 'Catat Pengeluaran' ?></button><button type="button" class="btn btn-secondary-soft" data-modal-close>Batal</button></div>
    </form>

  <?php elseif($isStore): ?>
    <div class="stat-grid mb-3">
      <div class="stat-card">
        <span class="stat-label">Ongkir Dasar</span>
        <span class="stat-value" style="font-size:1.35rem"><?=rupiah($baseFare)?></span>
        <span class="stat-note">Untuk <?=ops_distance($baseKm)?> km pertama.</span>
      </div>
      <div class="stat-card">
        <span class="stat-label">Tarif Tambahan</span>
        <span class="stat-value" style="font-size:1.35rem"><?=rupiah($per500)?></span>
        <span class="stat-note">Setiap 500 m berikutnya.</span>
      </div>
      <div class="stat-card">
        <span class="stat-label">Batas Jangkauan</span>
        <span class="stat-value"><?=ops_distance($maxKm)?> km</span>
        <span class="stat-note">Alamat di luar jarak ini ditolak.</span>
      </div>
    </div>

    <section class="panel-card">
      <div class="panel-header"><div><span class="eyebrow">Pengaturan toko</span><h2 class="section-title mb-0">Titik warung &amp; aturan ongkir</h2><p class="text-muted-custom mb-0">Ongkir dihitung server-side dari jarak garis lurus (haversine) titik toko ke titik alamat pelanggan.</p></div></div>
      <form method="post" action="<?=APP_URL?>/index.php?r=admin/save-store" class="admin-form">
        <?=csrf_field()?>
        <div class="form-section-title">Identitas toko</div>
        <div class="row g-3">
          <div class="col-md-6"><label class="form-label">Nama toko <span class="required-mark">*</span></label><input class="form-control" name="nama_toko" required maxlength="150" value="<?=e($storeSettings['nama_toko'] ?? 'Warung Maem Jadoel')?>"></div>
          <div class="col-md-6"><label class="form-label">Jam buka</label><input class="form-control" name="jam_buka" maxlength="80" placeholder="10.00 - 22.00" value="<?=e($storeSettings['jam_buka'] ?? '')?>"></div>
          <div class="col-12"><label class="form-label">Alamat toko</label><input class="form-control" name="alamat" maxlength="255" value="<?=e($storeSettings['alamat'] ?? '')?>"></div>
        </div>

        <div class="form-section-title">Lokasi peta</div>
        <div class="row g-3">
          <div class="col-md-6"><label class="form-label">Latitude</label><input class="form-control" name="latitude" type="number" step="0.0000001" required value="<?=e((string)($storeSettings['latitude'] ?? -6.4024840))?>"></div>
          <div class="col-md-6"><label class="form-label">Longitude</label><input class="form-control" name="longitude" type="number" step="0.0000001" required value="<?=e((string)($storeSettings['longitude'] ?? 106.7942430))?>"></div>
        </div>

        <div class="form-section-title">Aturan ongkir</div>
        <div class="row g-3">
          <div class="col-md-3"><label class="form-label">Ongkir dasar (Rp)</label><input class="form-control" type="number" name="ongkir_dasar" min="0" step="500" required value="<?=e((string)($storeSettings['ongkir_dasar'] ?? 10000))?>"></div>
          <div class="col-md-3"><label class="form-label">Jarak dasar (km)</label><input class="form-control" type="number" name="jarak_dasar_km" min="0" step="0.1" required value="<?=e((string)($storeSettings['jarak_dasar_km'] ?? 1))?>"></div>
          <div class="col-md-3"><label class="form-label">Tarif / 500 m (Rp)</label><input class="form-control" type="number" name="tarif_per_500m" min="0" step="500" required value="<?=e((string)($storeSettings['tarif_per_500m'] ?? 2000))?>"></div>
          <div class="col-md-3"><label class="form-label">Batas jarak (km)</label><input class="form-control" type="number" name="batas_jarak_km" min="0" step="0.5" required value="<?=e((string)($storeSettings['batas_jarak_km'] ?? 10))?>"></div>
          <div class="col-12"><label class="form-label">Catatan</label><textarea class="form-control" name="catatan" rows="2" maxlength="500" placeholder="Contoh: pengantaran hanya sampai pukul 21.30"><?=e($storeSettings['catatan'] ?? '')?></textarea></div>
        </div>

        <div class="form-actions"><button class="btn btn-primary" type="submit">Simpan Pengaturan</button></div>
      </form>
      <div class="admin-note">
        <strong>Contoh hitung ongkir</strong>
        <p>Jarak <?=ops_distance($sampleKm)?> km → <?=rupiah($baseFare)?> + (<?=$sampleSteps?> × <?=rupiah($per500)?>) = <strong><?=rupiah($sampleTotal)?></strong>. Ongkir tetap <em>estimasi</em> sampai owner mengonfirmasi nominal final di papan Pesanan.</p>
      </div>
    </section>

  <?php else: ?>
    <div class="stat-grid mb-3">
      <div class="stat-card">
        <span class="stat-label">Total Meja</span>
        <span class="stat-value"><?=$totalTables?></span>
        <span class="stat-note">Master meja tersimpan.</span>
      </div>
      <div class="stat-card">
        <span class="stat-label">Meja Aktif</span>
        <span class="stat-value"><?=$activeCount?></span>
        <span class="stat-note">Muncul di checkout Dine-in.</span>
      </div>
      <div class="stat-card">
        <span class="stat-label">QR Per Meja</span>
        <span class="stat-value" style="font-size:1.35rem">Tidak dipakai</span>
        <span class="stat-note">Pemesanan meja memakai nomor meja.</span>
      </div>
    </div>

    <section class="panel-card">
      <div class="panel-header">
        <div><span class="eyebrow">Master meja</span><h2 class="section-title mb-0">Daftar meja</h2><p class="text-muted-custom mb-0">Kelola meja yang dapat dipilih pelanggan. Nonaktifkan meja tanpa menghapus data.</p></div>
        <button type="button" class="btn btn-primary" data-modal-open="#crudModal-tableCreate"><?= icon('plus') ?> Tambah Meja</button>
      </div>
      <div class="table-responsive admin-table-wrap">
        <table class="table admin-table align-middle mb-0">
          <thead><tr><th>Meja</th><th>Status</th><th>Dibuat</th><th class="text-end">Aksi</th></tr></thead>
          <tbody>
          <?php if(!$tables): ?><tr><td colspan="4"><div class="empty-state"><strong>Belum ada meja.</strong><span>Klik Tambah Meja untuk membuat meja pertama.</span></div></td></tr><?php endif;
?>
          <?php foreach($tables as $t): $active=(int)$t['status_aktif']===1;
?>
            <tr>
              <td><strong>Meja <?=e($t['nomor_meja'])?></strong></td>
              <td><span class="status-chip <?=$active?'status-selesai':'status-dibatalkan'?>"><?=$active?'Aktif':'Nonaktif'?></span></td>
              <td><?=e(date('d M Y', strtotime($t['created_at'])))?></td>
              <td class="text-end">
                <div class="table-actions">
                  <button type="button" class="btn btn-sm btn-secondary-soft" data-edit-table data-id="<?=(int)$t['id']?>" data-nomor="<?=e($t['nomor_meja'])?>">Edit</button>
                  <form method="post" action="<?=APP_URL?>/index.php?r=admin/toggle-table"><?=csrf_field()?><input type="hidden" name="id" value="<?=$t['id']?>"><button class="btn btn-sm btn-outline-secondary" type="submit"><?= $active ? 'Nonaktifkan' : 'Aktifkan' ?></button></form>
                  <form method="post" action="<?=APP_URL?>/index.php?r=admin/delete-table" data-confirm="Hapus meja ini? Riwayat transaksi tetap aman, tetapi transaksi lama tidak lagi menampilkan nomor meja."><?=csrf_field()?><input type="hidden" name="id" value="<?=$t['id']?>"><button class="btn btn-sm btn-outline-danger" type="submit">Hapus</button></form>
                </div>
              </td>
            </tr>
          <?php endforeach;
?>
          </tbody>
        </table>
      </div>
    </section>

    <form id="tableCreate" method="post" action="<?=APP_URL?>/index.php?r=admin/add-table" data-crud-toggle data-crud-no-trigger data-crud-label="Tambah Meja" class="row g-3">
      <?=csrf_field()?>
      <div class="col-12"><label class="form-label">Nomor / nama meja <span class="required-mark">*</span></label><input class="form-control" name="nomor_meja" placeholder="Contoh: 5 atau Teras 1" required></div>
      <div class="col-12 form-actions"><button class="btn btn-primary" type="submit">Simpan Meja</button><button type="button" class="btn btn-secondary-soft" data-modal-close>Batal</button></div>
    </form>

    <form id="tableEdit" method="post" action="<?=APP_URL?>/index.php?r=admin/edit-table" data-crud-toggle data-crud-no-trigger data-crud-label="Edit Meja" class="row g-3">
      <?=csrf_field()?>
      <input type="hidden" name="id" id="tableEditId">
      <div class="col-12"><label class="form-label">Nomor / nama meja <span class="required-mark">*</span></label><input class="form-control" name="nomor_meja" id="tableEditNomor" required></div>
      <div class="col-12 form-actions"><button class="btn btn-primary" type="submit">Simpan Perubahan</button><button type="button" class="btn btn-secondary-soft" data-modal-close>Batal</button></div>
    </form>
  <?php endif;
?>
</div>
<script>
document.addEventListener('click',function(event){
  const button=event.target.closest('[data-edit-table]');
  if(!button) return;
  event.preventDefault();
  const idField=document.getElementById('tableEditId');
  const numberField=document.getElementById('tableEditNomor');
  if(idField) idField.value=button.dataset.id||'';
  if(numberField) numberField.value=button.dataset.nomor||'';
  if(window.WMJ) WMJ.modal.open('#crudModal-tableEdit');
});
<?php if($isExpense && $editExpense): ?>
window.addEventListener('load',function(){
  if(window.WMJ) WMJ.modal.open('#crudModal-expenseForm');
});
<?php endif;
?>
</script>
<?php require BASE_PATH.'/view/partials/footer.php';
?>
