<?php
/** Partial stok produk — dipakai tab "Stok Produk" di halaman Produk & Stok. */
$items = $items ?? [];
$criticalCount = $criticalCount ?? 0;
?>
<section class="ops-workspace">
  <div class="ops-summary-grid">
    <div class="ops-summary-card"><span>Total Produk</span><strong><?=count($items)?></strong><small>terdaftar di inventori</small></div>
    <div class="ops-summary-card"><span>Stok Kritis</span><strong><?=$criticalCount?></strong><small>perlu diperiksa</small></div>
    <div class="ops-summary-card ops-summary-accent"><span>Kontrol Stok</span><strong>LIVE</strong><small>nilai tersimpan saat klik Simpan</small></div>
  </div>

  <section class="ops-panel">
    <div class="ops-panel-head">
      <div><span class="ops-kicker">INVENTORI</span><h2>Daftar stok</h2><p>Perbarui jumlah stok produk di sini. Bila produk punya resep, penambahan stok produk otomatis menambah stok bahan bakunya. Penyesuaian stok tidak membuat catatan pengeluaran.</p></div>
      <span class="ops-count-badge"><?=count($items)?> ITEM</span>
    </div>
    <form method="post" action="<?=APP_URL?>/index.php?r=admin/adjust-stock" data-crud-toggle data-crud-label="Form Penyesuaian Stok Produk">
      <?=csrf_field()?>
      <div class="ops-stock-grid">
        <?php foreach($items as $i):
            $critical = (int)$i['jumlah_stok'] <= (int)$i['stok_minimum'];
            $foto = trim((string)($i['foto'] ?? ''));
            ?>
          <article class="ops-stock-card">
            <div class="ops-stock-image">
              <?php if($foto): ?><img src="<?=APP_URL.'/'.e($foto)?>" alt="<?=e($i['nama_produk'])?>"><?php else: ?><span>MAEM</span><?php endif; ?>
              <span class="ops-stock-state <?=$critical ? 'critical' : 'safe'?>"><?=$critical ? 'KRITIS' : 'AMAN'?></span>
            </div>
            <div class="ops-stock-body">
              <div><span class="ops-stock-label">PRODUK</span><h3><?=e($i['nama_produk'])?></h3></div>
              <div class="ops-stock-editor">
                <label for="stock<?=$i['id']?>">Jumlah stok</label>
                <div class="ops-number"><input id="stock<?=$i['id']?>" type="number" name="stok[<?=$i['id']?>]" value="<?=$i['jumlah_stok']?>" min="0" step="1" inputmode="numeric"><span>unit</span></div>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
      <?php if($items): ?><div class="ops-save-bar"><span>Perubahan belum aktif sampai disimpan.</span><button class="ops-primary-btn" type="submit">Simpan Perubahan</button></div><?php endif; ?>
      <?php if(!$items): ?><div class="ops-empty"><strong>Belum ada produk.</strong><span>Tambahkan produk terlebih dahulu melalui tab Produk.</span></div><?php endif; ?>
    </form>
  </section>
</section>
