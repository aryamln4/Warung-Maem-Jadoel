<?php
/** Partial stok bahan baku + pembelian — dipakai tab "Stok Bahan Baku". */
$bahan = $bahan ?? [];
$bahanPurchases = $bahanPurchases ?? [];
$bahanTotal = 0.0;
foreach ($bahan as $b) {
    $bahanTotal += (float)$b['stok'] * (float)$b['harga'];
}
?>
<section class="ops-workspace">
  <div class="ops-summary-grid">
    <div class="ops-summary-card"><span>Jenis Bahan</span><strong><?=count($bahan)?></strong><small>tercatat di inventori</small></div>
    <div class="ops-summary-card"><span>Nilai Persediaan</span><strong style="font-size:1.15rem"><?=rupiah($bahanTotal)?></strong><small>stok × harga satuan</small></div>
    <div class="ops-summary-card ops-summary-accent"><span>Pembelian Tercatat</span><strong><?=count($bahanPurchases)?></strong><small>otomatis masuk ke Keuangan</small></div>
  </div>

  <section class="ops-panel">
    <div class="ops-panel-head">
      <div>
        <span class="ops-kicker">INVENTORI BAHAN</span>
        <h2>Stok bahan baku</h2>
        <p>Bahan diurutkan berdasarkan tanggal kedaluwarsa terdekat (FEFO). Pembelian bahan menambah stok sekaligus mencatat pengeluaran; koreksi dilakukan dengan membatalkan pembelian.</p>
      </div>
      <div class="d-flex gap-2 flex-wrap">
        <button type="button" class="btn btn-secondary-soft" data-modal-open="#crudModal-bahanPurchase"><?= icon('shopping-bag') ?> Catat Pembelian</button>
        <button type="button" class="btn btn-primary" data-modal-open="#crudModal-bahanCreate"><?= icon('plus') ?> Tambah Bahan</button>
      </div>
    </div>

    <div class="table-responsive p-3">
      <table class="table align-middle">
        <thead><tr><th>Kode</th><th>Nama Bahan</th><th>Kadaluarsa</th><th>Stok</th><th>Satuan</th><th>Harga</th><th>Status FEFO</th><th class="text-end">Aksi</th></tr></thead>
        <tbody>
        <?php if(empty($bahan)): ?><tr><td colspan="8" class="text-center text-muted py-4">Belum ada bahan baku.</td></tr><?php endif; ?>
        <?php foreach($bahan as $b):
            $days = (int)floor((strtotime($b['tanggal_kadaluarsa'].' 23:59:59') - time()) / 86400);
            $qtyText = rtrim(rtrim(number_format((float)$b['stok'], 3, '.', ''), '0'), '.');
            ?>
          <tr>
            <td><?=e($b['kode_produk'])?></td>
            <td><?=e($b['nama_bahan'])?></td>
            <td><?=e(date('d M Y', strtotime($b['tanggal_kadaluarsa'])))?></td>
            <td><?=e($qtyText)?></td>
            <td><?=e($b['satuan'])?></td>
            <td><?=rupiah($b['harga'])?></td>
            <td><span class="badge <?=$days < 0 ? 'text-bg-danger' : ($days <= 7 ? 'text-bg-warning' : 'text-bg-success')?>"><?=$days < 0 ? 'Kedaluwarsa' : ($days <= 7 ? 'Prioritas FEFO' : 'Aman')?></span></td>
            <td class="text-end">
              <button type="button" class="ops-ghost-btn" data-bahan-buy="<?=(int)$b['id']?>">Beli / Tambah Stok</button>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>

  <section class="ops-panel" style="margin-top:18px">
    <div class="ops-panel-head">
      <div><span class="ops-kicker">RIWAYAT PEMBELIAN</span><h2>Pembelian bahan terakhir</h2><p>Setiap pembelian aktif terhubung ke satu baris pengeluaran di halaman Keuangan. Membatalkan pembelian akan mengembalikan stok dan menghapus catatan pengeluarannya.</p></div>
    </div>
    <div class="table-responsive p-3">
      <table class="table align-middle">
        <thead><tr><th>Tanggal</th><th>Bahan</th><th>Jumlah</th><th>Harga Satuan</th><th class="text-end">Total</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
        <tbody>
        <?php if(empty($bahanPurchases)): ?><tr><td colspan="7" class="text-center text-muted py-4">Belum ada pembelian bahan yang tercatat.</td></tr><?php endif; ?>
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
                  <?=csrf_field()?><input type="hidden" name="id" value="<?=(int)$p['id']?>"><button class="ops-delete-btn" type="submit">Batalkan</button>
                </form>
              <?php else: ?><small class="text-muted">Dibatalkan</small><?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>

  <form id="bahanCreate" method="post" action="<?=APP_URL?>/index.php?r=admin/save-bahan" data-crud-toggle data-crud-no-trigger data-crud-label="Form Tambah Bahan Baku" class="row g-3 p-3">
    <?=csrf_field()?>
    <div class="col-md-4"><label class="form-label">Kode Produk</label><input class="form-control" name="kode_produk" required maxlength="40"></div>
    <div class="col-md-4"><label class="form-label">Nama Bahan</label><input class="form-control" name="nama_bahan" required></div>
    <div class="col-md-4"><label class="form-label">Kadaluarsa</label><input class="form-control" type="date" name="tanggal_kadaluarsa" required min="<?=date('Y-m-d')?>"></div>
    <div class="col-md-3"><label class="form-label">Stok Awal</label><input class="form-control" type="number" name="stok" min="0" step="0.001" required value="0"><div class="form-help">Stok awal dianggap pembelian dan otomatis dicatat sebagai pengeluaran.</div></div>
    <div class="col-md-3"><label class="form-label">Satuan</label><select class="form-select" name="satuan" required><option value="">Pilih satuan</option><option>gram</option><option>kilo</option><option>pcs</option></select></div>
    <div class="col-md-3"><label class="form-label">Harga Satuan (Rp)</label><input class="form-control" type="number" name="harga" min="0" step="0.01" required></div>
    <div class="col-md-3 d-flex align-items-end"><button class="ops-primary-btn w-100" type="submit">Simpan Bahan</button></div>
  </form>

  <form id="bahanPurchase" method="post" action="<?=APP_URL?>/index.php?r=admin/add-bahan-stock" data-crud-toggle data-crud-no-trigger data-crud-label="Form Pembelian Bahan Baku" class="row g-3 p-3">
    <?=csrf_field()?>
    <div class="col-md-5">
      <label class="form-label">Bahan Baku</label>
      <select class="form-select" name="bahan_baku_id" id="purchaseBahan" required>
        <option value="">— Pilih bahan —</option>
        <?php foreach($bahan as $b): ?><option value="<?=(int)$b['id']?>" data-harga="<?=e((string)$b['harga'])?>" data-satuan="<?=e($b['satuan'])?>"><?=e($b['nama_bahan'])?> · Rp <?=number_format((float)$b['harga'], 0, ',', '.')?>/<?=e($b['satuan'])?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2"><label class="form-label">Jumlah</label><input class="form-control" type="number" name="jumlah" id="purchaseQty" min="0.001" step="0.001" required></div>
    <div class="col-md-2"><label class="form-label">Tanggal</label><input class="form-control" type="date" name="tanggal" required value="<?=date('Y-m-d')?>"></div>
    <div class="col-md-3"><label class="form-label">Catatan</label><input class="form-control" name="keterangan" maxlength="180" placeholder="Nomor nota / pemasok"></div>
    <div class="col-12">
      <div class="form-help" id="purchaseEstimate">Pilih bahan dan jumlah untuk melihat estimasi total. Harga mengikuti master bahan baku.</div>
      <div class="form-actions mt-3"><button class="btn btn-primary" type="submit">Catat Pembelian &amp; Pengeluaran</button></div>
    </div>
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
    if (!select.value || !jumlah) { estimate.textContent = 'Pilih bahan dan jumlah untuk melihat estimasi total. Harga mengikuti master bahan baku.'; return; }
    estimate.textContent = 'Estimasi total: ' + rupiah(harga * jumlah) + ' (' + jumlah + ' ' + satuan + ' × ' + rupiah(harga) + '). Pengeluaran dicatat otomatis saat disimpan.';
  }
  select.addEventListener('change', refresh);
  qty.addEventListener('input', refresh);
  document.querySelectorAll('[data-bahan-buy]').forEach(btn => btn.addEventListener('click', () => {
    select.value = btn.dataset.bahanBuy;
    refresh();
    if (window.WMJ) WMJ.modal.open('#crudModal-bahanPurchase');
  }));
})();
</script>
