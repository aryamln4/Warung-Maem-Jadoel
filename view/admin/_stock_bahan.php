<?php
/** Partial stok bahan baku + pembelian — dipakai tab "Stok Bahan Baku". */
$bahan = $bahan ?? [];
$bahanPurchases = $bahanPurchases ?? [];
$today = strtotime(date('Y-m-d'));
$activeBahan = [];
$expiredBahan = [];
$bahanTotal = 0.0;
foreach ($bahan as $b) {
    if (strtotime((string)$b['tanggal_kadaluarsa']) < $today) {
        $expiredBahan[] = $b;
    } else {
        $activeBahan[] = $b;
        $bahanTotal += (float)$b['harga'];
    }
}
?>
<section class="ops-workspace">
  <div class="stat-grid mb-3">
    <div class="stat-card">
      <span class="stat-label">Bahan Aktif</span>
      <span class="stat-value"><?=count($activeBahan)?></span>
      <span class="stat-note">Jenis bahan yang masih dapat dipakai.</span>
    </div>
    <div class="stat-card <?=$expiredBahan ? 'stat-card-alert' : ''?>">
      <span class="stat-label">Kedaluwarsa</span>
      <span class="stat-value"><?=count($expiredBahan)?></span>
      <span class="stat-note">Dipisahkan dari stok aktif agar tidak tercampur.</span>
    </div>
    <div class="stat-card">
      <span class="stat-label">Nilai Bahan Aktif</span>
      <span class="stat-value" style="font-size:1.35rem"><?=rupiah($bahanTotal)?></span>
      <span class="stat-note">Jumlah harga bahan aktif, tanpa dikalikan stok.</span>
    </div>
  </div>

  <div class="admin-tabs bahan-stock-tabs" role="tablist" aria-label="Tab stok bahan baku"><button type="button" class="admin-tab active" data-bahan-tab="aktif" aria-selected="true">Stok Aktif</button><button type="button" class="admin-tab" data-bahan-tab="riwayat" aria-selected="false">Riwayat</button><button type="button" class="admin-tab" data-bahan-tab="kadaluarsa" aria-selected="false">Kadaluarsa</button></div>
  <section class="panel-card bahan-stock-tab-panel" data-bahan-panel="aktif">
    <div class="panel-header">
      <div>
        <span class="eyebrow">Inventori bahan</span>
        <h2 class="section-title mb-0">Stok bahan baku aktif</h2>
        <p class="text-muted-custom mb-0">Bahan diurutkan berdasarkan tanggal kedaluwarsa terdekat (FEFO). Pembelian bahan menambah stok sekaligus mencatat pengeluaran; koreksi dilakukan dengan membatalkan pembelian atau mengedit data bahan.</p>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <button type="button" class="btn btn-secondary-soft" data-modal-open="#crudModal-bahanPurchase"><?= icon('shopping-bag') ?> Catat Pembelian</button>
        <button type="button" class="btn btn-primary" data-modal-open="#crudModal-bahanCreate"><?= icon('plus') ?> Tambah Bahan</button>
      </div>
    </div>

    <div class="table-responsive admin-table-wrap">
      <table class="table admin-table align-middle mb-0" data-table-paginate data-page-size="10">
        <thead><tr><th>Kode</th><th>Nama Bahan</th><th>Kadaluarsa</th><th>Stok</th><th>Satuan</th><th>Harga Bahan</th><th>Status FEFO</th><th class="text-end">Aksi</th></tr></thead>
        <tbody>
        <?php if(!$activeBahan): ?><tr><td colspan="8"><div class="empty-state"><strong>Belum ada bahan baku aktif.</strong><span>Klik Tambah Bahan untuk mencatat bahan baku pertama.</span></div></td></tr><?php endif;
?>
        <?php foreach($activeBahan as $b):
            $days = (int)floor((strtotime($b['tanggal_kadaluarsa'].' 23:59:59') - time()) / 86400);
            $qtyText = rtrim(rtrim(number_format((float)$b['stok'], 3, '.', ''), '0'), '.');
            ?>
          <tr>
            <td><?=e($b['kode_produk'])?></td>
            <td><strong><?=e($b['nama_bahan'])?></strong></td>
            <td><?=e(date('d M Y', strtotime($b['tanggal_kadaluarsa'])))?></td>
            <td><strong class="stock-current-value"><?=e($qtyText)?></strong></td>
            <td><?=e($b['satuan'])?></td>
            <td><?=rupiah($b['harga'])?></td>
            <td><span class="badge <?=$days <= 7 ? 'text-bg-warning' : 'text-bg-success'?>"><?=$days <= 7 ? 'Prioritas FEFO' : 'Aman'?></span></td>
            <td class="text-end">
              <div class="table-actions">
                <button type="button" class="btn btn-sm btn-secondary-soft" data-bahan-buy="<?=(int)$b['id']?>">Beli / Tambah Stok</button>
                <button type="button" class="btn btn-sm btn-secondary-soft" data-edit-bahan data-id="<?=(int)$b['id']?>" data-kode="<?=e($b['kode_produk'])?>" data-nama="<?=e($b['nama_bahan'])?>" data-kadaluarsa="<?=e($b['tanggal_kadaluarsa'])?>" data-satuan="<?=e($b['satuan'])?>" data-harga="<?=e((string)(float)$b['harga'])?>">Edit</button>
                <form method="post" action="<?=APP_URL?>/index.php?r=admin/delete-bahan" data-confirm="Hapus bahan <?=e($b['nama_bahan'])?>? Data yang sudah dipakai resep atau punya riwayat pembelian tidak dapat dihapus."><?=csrf_field()?><input type="hidden" name="id" value="<?=(int)$b['id']?>"><button class="btn btn-sm btn-outline-danger" type="submit">Hapus</button></form>
              </div>
            </td>
          </tr>
        <?php endforeach;
?>
        </tbody>
      </table>
    </div>
  </section>

  <section class="panel-card bahan-stock-tab-panel" data-bahan-panel="kadaluarsa" hidden>
    <div class="panel-header">
      <div><span class="eyebrow">Perlu tindakan</span><h2 class="section-title mb-0">Bahan baku kedaluwarsa</h2><p class="text-muted-custom mb-0">Daftar ini dipisahkan dari stok aktif. Perbarui tanggal kedaluwarsa bila bahan masih layak. Hapus hanya tersedia untuk bahan yang belum punya riwayat pembelian; bila sudah ada riwayat, batalkan pembeliannya lebih dulu agar laporan keuangan tetap akurat.</p></div>
      <span class="menu-count"><?=count($expiredBahan)?> bahan</span>
    </div>
    <div class="table-responsive admin-table-wrap">
      <table class="table admin-table align-middle mb-0" data-table-paginate data-page-size="10">
        <thead><tr><th>Kode</th><th>Nama Bahan</th><th>Kadaluarsa</th><th>Stok</th><th>Satuan</th><th>Harga Bahan</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
        <tbody>
        <?php if(!$expiredBahan): ?><tr><td colspan="8"><div class="empty-state"><strong>Tidak ada bahan kedaluwarsa.</strong><span>Semua bahan aktif masih dalam masa layak pakai.</span></div></td></tr><?php endif;
?>
        <?php foreach($expiredBahan as $b):
            $qtyText = rtrim(rtrim(number_format((float)$b['stok'], 3, '.', ''), '0'), '.');
            ?>
          <tr>
            <td><?=e($b['kode_produk'])?></td>
            <td><strong><?=e($b['nama_bahan'])?></strong></td>
            <td><?=e(date('d M Y', strtotime($b['tanggal_kadaluarsa'])))?></td>
            <td><strong class="stock-current-value"><?=e($qtyText)?></strong></td>
            <td><?=e($b['satuan'])?></td>
            <td><?=rupiah($b['harga'])?></td>
            <td><span class="badge text-bg-danger">Kedaluwarsa</span></td>
            <td class="text-end">
              <div class="table-actions">
                <button type="button" class="btn btn-sm btn-secondary-soft" data-edit-bahan data-id="<?=(int)$b['id']?>" data-kode="<?=e($b['kode_produk'])?>" data-nama="<?=e($b['nama_bahan'])?>" data-kadaluarsa="<?=e($b['tanggal_kadaluarsa'])?>" data-satuan="<?=e($b['satuan'])?>" data-harga="<?=e((string)(float)$b['harga'])?>">Edit</button>
                <form method="post" action="<?=APP_URL?>/index.php?r=admin/delete-bahan" data-confirm="Hapus bahan <?=e($b['nama_bahan'])?>? Data yang sudah dipakai resep atau punya riwayat pembelian tidak dapat dihapus."><?=csrf_field()?><input type="hidden" name="id" value="<?=(int)$b['id']?>"><button class="btn btn-sm btn-outline-danger" type="submit">Hapus</button></form>
              </div>
            </td>
          </tr>
        <?php endforeach;
?>
        </tbody>
      </table>
    </div>
  </section>

  <section class="panel-card bahan-stock-tab-panel" data-bahan-panel="riwayat" hidden>
    <div class="panel-header">
      <div><span class="eyebrow">Riwayat pembelian</span><h2 class="section-title mb-0">Pembelian bahan terakhir</h2><p class="text-muted-custom mb-0">Setiap pembelian aktif terhubung ke satu baris pengeluaran di halaman Keuangan. Membatalkan pembelian akan mengembalikan stok dan menghapus catatan pengeluarannya.</p></div>
    </div>
    <div class="table-responsive admin-table-wrap">
      <table class="table admin-table align-middle mb-0" data-table-paginate data-page-size="10">
        <thead><tr><th>Tanggal</th><th>Bahan</th><th>Jumlah</th><th>Harga Satuan</th><th class="text-end">Total</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
        <tbody>
        <?php if(empty($bahanPurchases)): ?><tr><td colspan="7"><div class="empty-state"><strong>Belum ada pembelian bahan.</strong><span>Pembelian yang dicatat akan muncul di sini.</span></div></td></tr><?php endif;
?>
        <?php foreach($bahanPurchases as $p): $aktif = ($p['status'] ?? 'AKTIF') === 'AKTIF'; ?>
          <tr>
            <td><?=e(date('d M Y', strtotime($p['tanggal'])))?></td>
            <td><?=e($p['nama_bahan'])?></td>
            <td><?=e(rtrim(rtrim(number_format((float)$p['jumlah'], 3, '.', ''), '0'), '.'))?> <?=e($p['satuan'])?></td>
            <td><?=rupiah($p['harga_satuan'])?></td>
            <td class="text-end"><strong><?=rupiah($p['total'])?></strong></td>
            <td><span class="status-chip <?=$aktif ? 'status-selesai' : 'status-dibatalkan'?>"><?=e($p['status'] ?? 'AKTIF')?></span></td>
            <td class="text-end">
              <?php if($aktif): ?>
                <form method="post" action="<?=APP_URL?>/index.php?r=admin/cancel-purchase" class="d-inline" data-confirm="Batalkan pembelian ini? Stok bahan akan dikurangi dan catatan pengeluaran dihapus.">
                  <?=csrf_field()?><input type="hidden" name="id" value="<?=(int)$p['id']?>"><button class="btn btn-sm btn-outline-danger" type="submit">Batalkan</button>
                </form>
              <?php else: ?><small class="text-muted">Dibatalkan</small><?php endif;
?>
            </td>
          </tr>
        <?php endforeach;
?>
        </tbody>
      </table>
    </div>
  </section>

  <form id="bahanCreate" method="post" action="<?=APP_URL?>/index.php?r=admin/save-bahan" data-crud-toggle data-crud-no-trigger data-crud-label="Tambah Bahan Baku" class="row g-3">
    <?=csrf_field()?>
    <div class="col-md-4"><label class="form-label">Kode Produk <span class="required-mark">*</span></label><input class="form-control" name="kode_produk" required maxlength="40"></div>
    <div class="col-md-4"><label class="form-label">Nama Bahan <span class="required-mark">*</span></label><input class="form-control" name="nama_bahan" required></div>
    <div class="col-md-4"><label class="form-label">Kadaluarsa <span class="required-mark">*</span></label><input class="form-control" type="date" name="tanggal_kadaluarsa" required min="<?=date('Y-m-d')?>"></div>
    <div class="col-md-3"><label class="form-label">Stok Awal</label><input class="form-control" type="number" name="stok" min="0" step="0.001" required value="0"><div class="form-help">Stok awal dianggap pembelian dan otomatis dicatat sebagai pengeluaran.</div></div>
    <div class="col-md-3"><label class="form-label">Satuan <span class="required-mark">*</span></label><select class="form-select" name="satuan" required><option value="">Pilih satuan</option><option>gram</option><option>kilo</option><option>pcs</option></select></div>
    <div class="col-md-3"><label class="form-label">Harga per Unit (Rp) <span class="required-mark">*</span></label><input class="form-control" type="number" name="harga" min="0" step="0.01" required></div>
    <div class="col-12 form-actions"><button class="btn btn-primary" type="submit">Simpan Bahan</button><button type="button" class="btn btn-secondary-soft" data-modal-close>Batal</button></div>
  </form>

  <form id="bahanPurchase" method="post" action="<?=APP_URL?>/index.php?r=admin/add-bahan-stock" data-crud-toggle data-crud-no-trigger data-crud-label="Catat Pembelian Bahan Baku" class="row g-3">
    <?=csrf_field()?>
    <div class="col-md-5">
      <label class="form-label">Bahan Baku <span class="required-mark">*</span></label>
      <select class="form-select" name="bahan_baku_id" id="purchaseBahan" required>
        <option value="">— Pilih bahan —</option>
        <?php foreach($activeBahan as $b): ?><option value="<?=(int)$b['id']?>" data-harga="<?=e((string)$b['harga'])?>" data-satuan="<?=e($b['satuan'])?>"><?=e($b['nama_bahan'])?> · Rp <?=number_format((float)$b['harga'], 0, ',', '.')?>/<?=e($b['satuan'])?></option><?php endforeach;
?>
      </select>
    </div>
    <div class="col-md-2"><label class="form-label">Jumlah <span class="required-mark">*</span></label><input class="form-control" type="number" name="jumlah" id="purchaseQty" min="0.001" step="0.001" required></div>
    <div class="col-md-2"><label class="form-label">Tanggal <span class="required-mark">*</span></label><input class="form-control" type="date" name="tanggal" required value="<?=date('Y-m-d')?>"></div>
    <div class="col-md-3"><label class="form-label">Catatan</label><input class="form-control" name="keterangan" maxlength="180" placeholder="Nomor nota / pemasok"></div>
    <div class="col-12">
      <div class="form-help" id="purchaseEstimate">Pilih bahan dan jumlah untuk melihat estimasi total. Harga mengikuti master bahan baku. Pembelian dicatat sebagai pengeluaran keuangan.</div>
      <div class="form-actions mt-3"><button class="btn btn-primary" type="submit">Catat Pembelian &amp; Pengeluaran</button><button type="button" class="btn btn-secondary-soft" data-modal-close>Batal</button></div>
    </div>
  </form>

  <form id="bahanEdit" method="post" action="<?=APP_URL?>/index.php?r=admin/update-bahan" data-crud-toggle data-crud-no-trigger data-crud-label="Edit Bahan Baku" class="row g-3">
    <?=csrf_field()?>
    <input type="hidden" name="id" id="bahanEditId">
    <div class="col-md-4"><label class="form-label">Kode Produk <span class="required-mark">*</span></label><input class="form-control" name="kode_produk" id="bahanEditKode" required maxlength="40"></div>
    <div class="col-md-4"><label class="form-label">Nama Bahan <span class="required-mark">*</span></label><input class="form-control" name="nama_bahan" id="bahanEditNama" required></div>
    <div class="col-md-4"><label class="form-label">Kadaluarsa <span class="required-mark">*</span></label><input class="form-control" type="date" name="tanggal_kadaluarsa" id="bahanEditKadaluarsa" required></div>
    <div class="col-md-6"><label class="form-label">Satuan <span class="required-mark">*</span></label><select class="form-select" name="satuan" id="bahanEditSatuan" required><option value="">Pilih satuan</option><option>gram</option><option>kilo</option><option>pcs</option></select></div>
    <div class="col-md-6"><label class="form-label">Harga per Unit (Rp) <span class="required-mark">*</span></label><input class="form-control" type="number" name="harga" id="bahanEditHarga" min="0" step="0.01" required><div class="form-help">Stok tidak diubah dari form ini. Gunakan Beli / Tambah Stok agar riwayat pembelian tetap tercatat.</div></div>
    <div class="col-12 form-actions"><button class="btn btn-primary" type="submit">Simpan Perubahan</button><button type="button" class="btn btn-secondary-soft" data-modal-close>Batal</button></div>
  </form>
</section>
<script>
(function(){
  const select = document.getElementById('purchaseBahan');
  const qty = document.getElementById('purchaseQty');
  const estimate = document.getElementById('purchaseEstimate');
  if (!select || !qty || !estimate) return;
  function rupiah(n){ return 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(n)); }
  function refresh(){
    const opt = select.options[select.selectedIndex];
    const harga = parseFloat(opt?.dataset.harga || '0');
    const satuan = opt?.dataset.satuan || '';
    const jumlah = parseFloat(qty.value || '0');
    if (!select.value || !jumlah) { estimate.textContent = 'Pilih bahan dan jumlah untuk melihat estimasi total. Harga mengikuti master bahan baku. Pembelian dicatat sebagai pengeluaran keuangan.'; return; }
    estimate.textContent = 'Estimasi total: ' + rupiah(harga * jumlah) + ' (' + jumlah + ' ' + satuan + ' × ' + rupiah(harga) + '). Pengeluaran dicatat otomatis saat disimpan.';
  }
  select.addEventListener('change', refresh);
  qty.addEventListener('input', refresh);
  document.querySelectorAll('[data-bahan-buy]').forEach(btn => btn.addEventListener('click', () => {
    select.value = btn.dataset.bahanBuy;
    refresh();
    if (window.WMJ) WMJ.modal.open('#crudModal-bahanPurchase');
  }));
  document.querySelectorAll('[data-edit-bahan]').forEach(btn => btn.addEventListener('click', () => {
    const set = (id, value) => { const el = document.getElementById(id); if (el) el.value = value || ''; };
    set('bahanEditId', btn.dataset.id);
    set('bahanEditKode', btn.dataset.kode);
    set('bahanEditNama', btn.dataset.nama);
    set('bahanEditKadaluarsa', btn.dataset.kadaluarsa);
    set('bahanEditSatuan', btn.dataset.satuan);
    set('bahanEditHarga', btn.dataset.harga);
    if (window.WMJ) WMJ.modal.open('#crudModal-bahanEdit');
  }));
})();
</script>

<script>
(function(){
 const tabs=[...document.querySelectorAll('[data-bahan-tab]')], panels=[...document.querySelectorAll('[data-bahan-panel]')];
 if(!tabs.length)return;
 tabs.forEach(tab=>tab.addEventListener('click',()=>{const key=tab.dataset.bahanTab;tabs.forEach(t=>{const on=t===tab;t.classList.toggle('active',on);t.setAttribute('aria-selected',on?'true':'false');});panels.forEach(p=>p.hidden=p.dataset.bahanPanel!==key);}));
})();
</script>
