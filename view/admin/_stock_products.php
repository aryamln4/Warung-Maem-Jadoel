<?php
/** Partial stok produk — dipakai tab "Stok Produk" di halaman Produk & Stok. */
$items = $items ?? [];
$criticalCount = $criticalCount ?? 0;
?>
<section class="ops-workspace">
  <div class="stat-grid mb-3">
    <div class="stat-card">
      <span class="stat-label">Total Produk</span>
      <span class="stat-value"><?=count($items)?></span>
      <span class="stat-note">Terdaftar di inventori produk.</span>
    </div>
    <div class="stat-card <?=$criticalCount ? 'stat-card-alert' : ''?>">
      <span class="stat-label">Stok Kritis</span>
      <span class="stat-value"><?=$criticalCount?></span>
      <span class="stat-note">Produk dengan stok sama atau di bawah batas minimum.</span>
    </div>
    <div class="stat-card">
      <span class="stat-label">Pembaruan Stok</span>
      <span class="stat-value" style="font-size:1.45rem">Langsung</span>
      <span class="stat-note">Jumlah stok terlihat di daftar. Simpan sekali untuk semua perubahan.</span>
    </div>
  </div>

  <section class="panel-card">
    <div class="panel-header">
      <div><span class="eyebrow">Inventori</span><h2 class="section-title mb-0">Daftar stok produk</h2><p class="text-muted-custom mb-0">Jumlah stok setiap produk tampil langsung di tabel. Isi jumlah stok akhir yang diinginkan, lalu simpan sekali untuk semua perubahan. Bila produk punya resep, penambahan stok produk otomatis menambah stok bahan bakunya. Penyesuaian stok tidak membuat catatan pengeluaran.</p></div>
      <span class="menu-count"><?=count($items)?> item</span>
    </div>
    <form method="post" action="<?=APP_URL?>/index.php?r=admin/adjust-stock">
      <?=csrf_field()?>
      <div class="table-responsive admin-table-wrap">
        <table class="table admin-table align-middle mb-0">
          <thead>
            <tr><th>Produk</th><th>Satuan</th><th>Stok Saat Ini</th><th>Stok Minimum</th><th>Status</th><th class="text-end">Sesuaikan Stok</th></tr>
          </thead>
          <tbody>
          <?php if(!$items): ?>
            <tr><td colspan="6"><div class="empty-state"><strong>Belum ada produk.</strong><span>Tambahkan produk terlebih dahulu melalui tab Produk.</span></div></td></tr>
          <?php endif;
?>
          <?php foreach($items as $i):
              $critical = (int)$i['jumlah_stok'] <= (int)$i['stok_minimum'];
              $foto = trim((string)($i['foto'] ?? ''));
              ?>
            <tr>
              <td>
                <div class="stock-product-cell">
                  <span class="stock-product-thumb"><?php if($foto): ?><img src="<?=APP_URL.'/'.e($foto)?>" alt="<?=e($i['nama_produk'])?>"><?php else: ?><?= icon('package') ?><?php endif;
?></span>
                  <strong><?=e($i['nama_produk'])?></strong>
                </div>
              </td>
              <td><?=e($i['satuan'] ?: 'unit')?></td>
              <td><strong class="stock-current-value"><?=(int)$i['jumlah_stok']?></strong> <?=e($i['satuan'] ?: 'unit')?></td>
              <td><?=(int)$i['stok_minimum']?></td>
              <td><span class="badge <?=$critical ? 'text-bg-danger' : 'text-bg-success'?>"><?=$critical ? 'Kritis' : 'Aman'?></span></td>
              <td class="text-end">
                <input class="form-control form-control-sm stock-adjust-input" id="stock<?=$i['id']?>" type="number" name="stok[<?=$i['id']?>]" value="<?=(int)$i['jumlah_stok']?>" min="0" step="1" inputmode="numeric" aria-label="Stok <?=e($i['nama_produk'])?>">
              </td>
            </tr>
          <?php endforeach;
?>
          </tbody>
        </table>
      </div>
      <?php if($items): ?>
      <div class="panel-footer-actions">
        <span class="text-muted-custom">Isi jumlah stok akhir (bukan tambahan). Perubahan aktif setelah tombol simpan ditekan.</span>
        <button class="btn btn-primary" type="submit"><?= icon('check') ?> Simpan Perubahan</button>
      </div>
      <?php endif;
?>
    </form>
  </section>
</section>
