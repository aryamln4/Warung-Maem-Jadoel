<?php $title='Stok Produk';
require BASE_PATH.'/view/partials/header.php';
?>
<div class="admin-content inventory-page">
</div>

  <section class="panel-card inventory-card">
    <form method="post" action="<?=APP_URL?>/index.php?r=admin/adjust-stock" data-crud-toggle data-crud-label="Form Penyesuaian Stok">
      <?=csrf_field()?>
      <div class="inventory-table-wrap">
        <div class="inventory-table-head" aria-hidden="true">
          <span>Produk</span>
          <span>Status</span>
          <span>Stok Produk</span>
        </div>

        <div class="inventory-list">
          <?php foreach($items as $i):
$isCritical=(int)$i['jumlah_stok'] <= (int)$i['stok_minimum'];
$foto=trim((string)($i['foto']??''));
?>
            <div class="inventory-row">
              <div class="inventory-product">
                <div class="inventory-product-image">
                  <?php if($foto): ?>
                    <img src="<?=APP_URL.'/'.e($foto)?>" alt="<?=e($i['nama_produk'])?>">
                  <?php else: ?>
                    <span>Foto produk</span>
                  <?php endif;
?>
                </div>
                <div class="inventory-product-copy">
                  <strong><?=e($i['nama_produk'])?></strong>
                </div>
              </div>

              <div>
                <span class="inventory-status <?=$isCritical?'is-critical':'is-safe'?>">
                  <?=$isCritical?'Kritis':'Aman'?>
                </span>
              </div>

              <div class="inventory-stock-field">
                <input class="form-control" type="number" name="stok[<?=$i['id']?>]" value="<?=$i['jumlah_stok']?>" min="0" step="1" inputmode="numeric" aria-label="Stok <?=e($i['nama_produk'])?>">
              </div>
            </div>
          <?php endforeach;
?>

          <?php if(!$items): ?>
            <div class="empty-state inventory-empty">
              <strong>Belum ada produk.</strong>
              <span>Tambahkan produk terlebih dahulu melalui Produk &amp; Stok.</span>
            </div>
          <?php endif;
?>
        </div>
      </div>

      <?php if($items): ?>
        <div class="inventory-actions">
          <button class="btn btn-dark inventory-save-btn" type="submit">Simpan</button>
        </div>
      <?php endif;
?>
    </form>
  </section>
</div>
<?php require BASE_PATH.'/view/partials/footer.php';
?>
