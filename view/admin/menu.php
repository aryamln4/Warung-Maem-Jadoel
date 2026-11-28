<?php
$title='Produk & Stok';
require BASE_PATH.'/view/partials/header.php';
$isEdit=(bool)$editMenu;
?>
<div class="admin-content">
  <div class="admin-tabs admin-master-switch" role="tablist" aria-label="Menu Produk & Stok: produk, resep, stok produk, stok bahan baku, voucher, kategori dan topping">
    <button type="button" class="admin-tab active" data-panel="produk" role="tab" aria-selected="true">Produk</button>
    <button type="button" class="admin-tab" data-panel="resep" role="tab" aria-selected="false">Resep</button>
    <button type="button" class="admin-tab" data-panel="stok" role="tab" aria-selected="false">Stok Produk</button>
    <button type="button" class="admin-tab" data-panel="bahan" role="tab" aria-selected="false">Stok Bahan Baku</button>
    <button type="button" class="admin-tab" data-panel="voucher" role="tab" aria-selected="false">Voucher</button>
    <button type="button" class="admin-tab" data-panel="master" role="tab" aria-selected="false">Kategori &amp; Topping</button>
  </div>
  <div class="admin-switch-hint"><span class="switch-dot"></span><span id="adminSwitchLabel">Mengelola produk</span><span class="switch-separator">•</span><span>Bagian Produk &amp; Stok: data produk, resep, stok produk, stok bahan baku, voucher, kategori, dan topping.</span></div>

  <section id="produk" class="admin-section-anchor admin-switch-panel is-active" data-panel-view="produk" role="tabpanel">
    <div class="modal fade admin-crud-modal" id="productModal" tabindex="-1" aria-labelledby="productModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
      <button type="button" class="admin-modal-close admin-modal-close--float" data-modal-close aria-label="Tutup formulir produk"><?= icon('x') ?></button>
      <section id="produk-form" class="modal-body panel-card form-panel border-0 shadow-none">
        <div class="panel-header">
          <div><h2 class="section-title mb-1" id="productModalLabel"><?= $isEdit ? 'Perbarui data produk' : 'Tambah produk baru' ?></h2></div>
          <?php if($isEdit): ?><a class="btn btn-sm btn-secondary-soft" href="<?=APP_URL?>/index.php?r=admin/menu#produk-form">Batal edit</a><?php endif;
?>
        </div>
        <form class="admin-form" method="post" action="<?=APP_URL?>/index.php?r=admin/save-menu" enctype="multipart/form-data">
          <?=csrf_field()?>
          <?php if($isEdit): ?><input type="hidden" name="id" value="<?=$editMenu['id']?>"><input type="hidden" name="foto_lama" value="<?=e($editMenu['foto']??'')?>"><?php endif;
?>

          <div class="form-section-title">Data produk</div>
          <div class="row g-3">
            <div class="col-12"><label class="form-label">Nama produk <span class="required-mark">*</span></label><input class="form-control" name="nama_produk" required value="<?=e($editMenu['nama_produk']??'')?>" placeholder="Contoh: Mie Jebew"></div>
            <div class="col-md-6"><label class="form-label">Kategori <span class="required-mark">*</span></label><select class="form-select" name="kategori_id" required><option value="">Pilih kategori</option><?php foreach($cats as $c): ?><option value="<?=$c['id']?>" <?=($editMenu && (int)$editMenu['kategori_id']===(int)$c['id'])?'selected':''?>><?=e($c['nama'])?></option><?php endforeach;
?></select></div>
            <div class="col-md-6"><label class="form-label">Harga <span class="required-mark">*</span></label><div class="input-group"><span class="input-group-text">Rp</span><input class="form-control" type="number" name="harga" min="0" required value="<?=isset($editMenu['harga'])?(int)$editMenu['harga']:''?>"></div></div>
            <?php if(!$isEdit): ?><div class="col-md-6"><label class="form-label">Stok awal</label><input class="form-control" type="number" name="stok_awal" min="0" value="0" inputmode="numeric"></div><?php endif;
?>
          </div>

          <div class="mt-3">
            <label class="form-label">Deskripsi produk</label>
            <textarea class="form-control" name="deskripsi" rows="3" maxlength="1000" placeholder="Contoh: Mie kenyal dengan bumbu gurih pedas dan topping pilihan."><?=e($editMenu['deskripsi']??'')?></textarea>
            <div class="form-help">Deskripsi ini akan ditampilkan kepada pelanggan saat membuka detail produk sebelum menambahkan ke keranjang.</div>
          </div>

          <div class="photo-upload-field mt-4">
            <div class="photo-preview" id="photoPreview">
              <?php if($isEdit && !empty($editMenu['foto'])): ?><img src="<?=APP_URL.'/'.e($editMenu['foto'])?>" alt="<?=e($editMenu['nama_produk'])?>"><?php else: ?><div class="photo-placeholder"><strong>Foto produk</strong><span>JPG / PNG / WEBP<br>maks. 6 MB</span></div><?php endif;
?>
            </div>
            <div>
              <label class="form-label">Upload gambar</label>
              <input class="form-control" type="file" name="foto" accept="image/jpeg,image/png,image/webp" id="fotoInput">
              <div class="form-help">Pilih gambar dari komputer. Preview muncul sebelum disimpan.</div>
              <?php if($isEdit && !empty($editMenu['foto'])): ?><label class="form-check mt-2"><input class="form-check-input" type="checkbox" name="hapus_foto" value="1"> <span class="form-check-label">Hapus foto lama</span></label><?php endif;
?>
            </div>
          </div>


          <div class="form-section-title">Pilihan pelanggan</div>
          <div class="option-grid mb-3">
            <label class="check-card"><input type="checkbox" name="punya_topping" <?=($editMenu && $editMenu['punya_topping'])?'checked':''?>><span><strong>Produk memakai topping</strong><small>Jika aktif, pelanggan dapat memilih topping yang dicentang di bawah.</small></span></label>
            <label class="check-card"><input type="checkbox" name="punya_level_pedas" <?=($editMenu && $editMenu['punya_level_pedas'])?'checked':''?>><span><strong>Produk memakai level pedas</strong><small>Jika aktif, pelanggan memilih level 0 sampai 5.</small></span></label>
          </div>

          <div class="topping-picker">
            <div class="d-flex justify-content-between align-items-end gap-3 mb-2"><div><label class="form-label mb-0">Topping yang tersedia</label><div class="form-help">Klik kartu topping. Tidak perlu Ctrl/Cmd.</div></div><span class="form-help">Bisa pilih lebih dari satu</span></div>
            <div class="topping-check-grid">
              <?php foreach($tops as $t): $checked=in_array((int)$t['id'],
$menuToppingIds??[],
true);
?>
                <label class="topping-check <?= $checked?'selected':'' ?>"><input type="checkbox" name="topping_ids[]" value="<?=$t['id']?>" <?=$checked?'checked':''?>><span><strong><?=e($t['nama'])?></strong><small>+Rp <?=number_format($t['harga_tambahan'],
0,
',',
'.')?></small></span></label>
              <?php endforeach;
?>
              <?php if(!$tops): ?><div class="empty-mini">Belum ada topping. Buka tab <button type="button" class="link-button" data-switch-to="master">Kategori & Topping</button> untuk menambahkannya.</div><?php endif;
?>
            </div>
          </div>

          <div class="form-actions"><button class="btn btn-primary btn-lg touch" type="submit"><?= $isEdit ? 'Simpan Perubahan' : 'Simpan Produk' ?></button><?php if($isEdit): ?><a class="btn btn-secondary-soft btn-lg touch" href="<?=APP_URL?>/index.php?r=admin/menu">Batal edit</a><?php else: ?><button class="btn btn-secondary-soft btn-lg touch" type="button" data-modal-close>Batal</button><?php endif;
?></div>
        </form>
      </section>
      </div></div>
    </div>

    <section class="panel-card mt-4">
      <div class="panel-header"><div><span class="eyebrow">Katalog</span><h2 class="section-title mb-0">Daftar produk</h2><p class="text-muted-custom mb-0">Edit untuk memperbarui data, Arsipkan untuk menyembunyikan, atau Hapus untuk menghapus permanen jika belum dipakai.</p></div><div class="d-flex align-items-center gap-2 flex-wrap"><span class="menu-count"><?=count($menus)?> produk</span><button type="button" class="btn btn-primary" data-modal-open="#productModal"><?= icon('plus') ?> Tambah Produk</button></div></div>
      <div class="admin-product-table">
        <?php if(!$menus): ?><div class="empty-state"><strong>Belum ada produk.</strong><span>Klik tombol Tambah Produk untuk menambahkan produk.</span></div><?php endif;
?>
        <?php foreach($menus as $m): ?>
          <article class="admin-product-row <?=!(int)$m['status_aktif']?'is-inactive':''?>">
            <div class="admin-product-thumb"><?php if(!empty($m['foto'])): ?><img src="<?=APP_URL.'/'.e($m['foto'])?>" alt="<?=e($m['nama_produk'])?>"><?php else: ?><span><?=e($m['kategori'])?></span><?php endif;
?></div>
            <div class="admin-product-info"><div class="product-category"><?=e($m['kategori'])?></div><h3><?=e($m['nama_produk'])?></h3><strong>Rp <?=number_format($m['harga'],
0,
',',
'.')?></strong><div class="product-meta"><span><?= $m['punya_topping']?'Topping aktif':'Tanpa topping' ?></span><span><?= $m['punya_level_pedas']?'Level 0–5':'Tanpa level' ?></span><span><?= (int)$m['status_aktif']?'Aktif':'Nonaktif' ?></span></div></div>
            <div class="admin-product-actions">
              <a class="btn btn-sm btn-secondary-soft" href="<?=APP_URL?>/index.php?r=admin/menu&edit=<?=$m['id']?>#produk-form">Edit</a>
              <?php if((int)$m['status_aktif']): ?>
                <form method="post" action="<?=APP_URL?>/index.php?r=admin/delete-menu" data-confirm="Arsipkan produk ini? Produk akan disembunyikan dari katalog pelanggan, tetapi datanya tetap tersimpan."><?=csrf_field()?><input type="hidden" name="id" value="<?=$m['id']?>"><button class="btn btn-sm btn-outline-warning">Arsipkan</button></form>
              <?php else: ?>
                <form method="post" action="<?=APP_URL?>/index.php?r=admin/restore-menu" data-confirm="Pulihkan produk ini ke katalog pelanggan?"><?=csrf_field()?><input type="hidden" name="id" value="<?=$m['id']?>"><button class="btn btn-sm btn-outline-success">Pulihkan</button></form>
              <?php endif;
?>
              <form method="post" action="<?=APP_URL?>/index.php?r=admin/hard-delete-menu" data-confirm="Hapus produk ini PERMANEN? Data yang sudah terhapus tidak dapat dikembalikan."><?=csrf_field()?><input type="hidden" name="id" value="<?=$m['id']?>"><button class="btn btn-sm btn-outline-danger">Hapus</button></form>
            </div>
          </article>
        <?php endforeach;
?>
      </div>
    </section>
  </section>

  <section id="resep" class="admin-section-anchor admin-switch-panel" data-panel-view="resep" role="tabpanel" hidden>
    <div class="section-heading mb-3"><div><span class="eyebrow">Takaran per porsi</span><h2 class="section-title mb-1">Resep produk</h2><p class="text-muted-custom mb-0">Pilih produk untuk mengatur bahan baku dan takarannya. Saat stok produk ditambah di tab Stok Produk, stok bahan otomatis ikut bertambah sesuai resep.</p></div></div>
    <section class="panel-card">
      <div class="panel-header"><div><span class="eyebrow">Daftar produk</span><h2 class="section-title mb-0">Pilih produk</h2><p class="text-muted-custom mb-0">Klik Atur resep untuk membuka formulir takaran bahan dalam pop-up.</p></div><span class="menu-count"><?=count($recipeSummary)?> produk</span></div>
      <div class="master-list">
        <?php if(!$recipeSummary): ?><div class="empty-state"><strong>Belum ada produk.</strong><span>Tambahkan produk terlebih dahulu melalui tab Produk.</span></div><?php endif;
?>
        <?php foreach($recipeSummary as $r): ?>
          <div class="master-item recipe-item">
            <div class="recipe-item-info">
              <strong><?=e($r['nama_produk'])?></strong>
              <small><?=e($r['kategori'])?> · <?= (int)$r['bahan_count'] ?> bahan · stok <?= (int)$r['stok'] ?></small>
            </div>
            <div class="master-actions">
              <a class="btn btn-sm <?= (int)$recipeMenuId === (int)$r['id'] ? 'btn-dark' : 'btn-secondary-soft' ?>" data-recipe-id="<?=(int)$r['id']?>" href="<?=APP_URL?>/index.php?r=admin/menu&resep=<?=$r['id']?>#resep"><?= (int)$r['bahan_count'] ? 'Ubah resep' : 'Atur resep' ?></a>
            </div>
          </div>
        <?php endforeach;
?>
      </div>
    </section>

    <?php if($recipeMenu): ?>
    <div class="modal fade admin-crud-modal" id="recipeModal" tabindex="-1" aria-labelledby="recipeModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
        <div class="admin-modal-head">
          <div><span class="admin-modal-kicker">Takaran per porsi</span><h2 id="recipeModalLabel">Resep <?=e($recipeMenu['nama_produk'])?></h2></div>
          <button type="button" class="admin-modal-close" data-modal-close aria-label="Tutup formulir resep"><?= icon('x') ?></button>
        </div>
        <div class="modal-body">
          <p class="text-muted-custom mb-3">Isi bahan, jumlah takaran, satuan (kg/gram/pieces), dan catatan opsional. Sistem mengonversi kg↔gram sesuai satuan stok bahan ketika resep menyinkronkan stok.</p>
          <form method="post" action="<?=APP_URL?>/index.php?r=admin/save-recipe" class="recipe-form">
            <?=csrf_field()?>
            <input type="hidden" name="menu_id" value="<?=(int)$recipeMenu['id']?>">
            <div id="recipeRows">
              <?php
$existing = $recipeRows;
$rowCount = max(2, count($existing) + 1);
for($i=0; $i<$rowCount; $i++): $row=$existing[$i] ?? null; $unitDefault=$row ? ($row['satuan_resep'] ?? ($row['satuan']==='kilo'?'kg':($row['satuan']==='pcs'?'pieces':'gram'))) : 'gram';
?>
              <div class="row g-2 align-items-end recipe-row mb-2">
                <div class="col-md-3"><label class="form-label">Bahan</label><select class="form-select recipe-ingredient" name="bahan_baku_id[]"><option value="">— Pilih bahan —</option><?php foreach($ingredients as $ing): ?><option value="<?=$ing['id']?>" data-stock-unit="<?=e($ing['satuan']==='kilo'?'kg':($ing['satuan']==='pcs'?'pieces':'gram'))?>" <?= $row && (int)$row['bahan_baku_id']===(int)$ing['id'] ? 'selected' : '' ?>><?=e($ing['nama_bahan'])?> (<?=e($ing['satuan'])?>)</option><?php endforeach; ?></select></div>
                <div class="col-md-3"><label class="form-label">Jumlah</label><input class="form-control" type="number" name="jumlah[]" min="0" step="0.001" value="<?= $row ? e(rtrim(rtrim(number_format((float)$row['jumlah'],3,'.',''),'0'),'.')) : '' ?>" placeholder="0"></div>
                <div class="col-md-2"><label class="form-label">Satuan</label><select class="form-select recipe-unit" name="satuan_resep[]"><option value="kg" <?=$unitDefault==='kg'?'selected':''?>>kg</option><option value="gram" <?=$unitDefault==='gram'?'selected':''?>>gram</option><option value="pieces" <?=$unitDefault==='pieces'?'selected':''?>>pieces</option></select></div>
                <div class="col-md-3"><label class="form-label">Catatan</label><input class="form-control" name="keterangan[]" maxlength="120" value="<?=e($row['keterangan'] ?? '')?>" placeholder="Opsional"></div>
                <div class="col-md-1"><button type="button" class="btn btn-outline-danger btn-sm recipe-remove-row" title="Hapus baris" aria-label="Hapus baris">×</button></div>
              </div>
              <?php endfor;
?>
            </div>
            <div class="form-actions">
              <button class="btn btn-secondary-soft" type="button" id="addRecipeRow"><?= icon('plus') ?> Tambah Bahan</button>
              <button class="btn btn-primary" type="submit">Simpan Resep</button>
              <button class="btn btn-secondary-soft" type="button" data-modal-close>Batal</button>
            </div>
            <div class="form-help mt-2">Contoh: Mie Jebew memakai 150 gram mie, 30 gram bumbu, dan 1 pcs telur. Menambah 10 stok Mie Jebew akan menambah 1.500 gram mie, 300 gram bumbu, dan 10 pcs telur.</div>
            <?php if(!$ingredients): ?><div class="alert alert-warning mt-3 mb-0">Belum ada bahan baku. Tambahkan bahan terlebih dahulu di tab Stok Bahan Baku.</div><?php endif;
?>
          </form>
        </div>
      </div></div>
    </div>
    <?php endif;
?>
  </section>

  <section id="stok" class="admin-section-anchor admin-switch-panel" data-panel-view="stok" role="tabpanel" hidden>
    <?php require BASE_PATH.'/view/admin/_stock_products.php'; ?>
  </section>

  <section id="bahan" class="admin-section-anchor admin-switch-panel" data-panel-view="bahan" role="tabpanel" hidden>
    <?php require BASE_PATH.'/view/admin/_stock_bahan.php'; ?>
  </section>

  <section id="voucher" class="admin-section-anchor admin-switch-panel" data-panel-view="voucher" role="tabpanel" hidden>
    <div class="section-heading mb-3"><div><span class="eyebrow">Promo pelanggan</span><h2 class="section-title mb-1">Voucher</h2><p class="text-muted-custom mb-0">Tiga tipe: nominal, persen, dan gratis ongkir. Kuota dihitung dari pemakaian nyata pada pesanan.</p></div></div>
    <section class="panel-card">
      <div class="panel-header">
        <div><span class="eyebrow">Daftar voucher</span><h2 class="section-title mb-0">Voucher aktif &amp; arsip</h2><p class="text-muted-custom mb-0">Atur kode promo, nominal potongan, kuota, dan masa berlaku voucher dari satu tempat.</p></div>
        <div class="d-flex gap-2 flex-wrap">
          <?php if($editVoucher): ?><a class="btn btn-secondary-soft" href="<?=APP_URL?>/index.php?r=admin/menu#voucher">Batalkan edit</a><?php endif;
?>
          <button type="button" class="btn btn-primary" data-modal-open="#voucherModal"><?= icon($editVoucher ? 'edit' : 'plus') ?> <?= $editVoucher ? 'Edit Voucher' : 'Buat Voucher' ?></button>
        </div>
      </div>
      <div class="table-responsive admin-table-wrap">
        <table class="table admin-table align-middle mb-0">
          <thead><tr><th>Kode</th><th>Tipe</th><th>Nilai</th><th>Min. belanja</th><th>Kuota</th><th>Periode</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
          <tbody>
          <?php if(!$vouchers): ?><tr><td colspan="8"><div class="empty-state"><strong>Belum ada voucher.</strong><span>Klik tombol Buat Voucher untuk menambahkan voucher pertama.</span></div></td></tr><?php endif;
?>
          <?php foreach($vouchers as $v): $active=(int)$v['status_aktif']===1;
?>
            <tr>
              <td><strong><?=e($v['kode'])?></strong><?php if($v['deskripsi']): ?><br><small><?=e($v['deskripsi'])?></small><?php endif;
?></td>
              <td><span class="badge text-bg-light"><?=e($v['tipe'])?></span></td>
              <td><?= $v['tipe']==='PERSEN' ? (float)$v['nilai'].'%' : rupiah($v['nilai']) ?><?php if($v['maks_potongan']): ?><br><small>maks <?=rupiah($v['maks_potongan'])?></small><?php endif;
?></td>
              <td><?=rupiah($v['min_belanja'])?></td>
              <td><?= (int)$v['kuota_total'] > 0 ? (int)$v['terpakai'].' / '.(int)$v['kuota_total'] : (int)$v['terpakai'].' / ∞' ?><br><small><?=(int)$v['kuota_per_pelanggan']?>× per pelanggan</small></td>
              <td><small><?=e($v['tanggal_mulai'] ?: '—')?><br><?=e($v['tanggal_selesai'] ?: '—')?></small></td>
              <td><span class="status-chip <?=$active?'status-selesai':'status-dibatalkan'?>"><?=$active?'Aktif':'Nonaktif'?></span></td>
              <td class="text-end">
                <div class="table-actions">
                  <a class="btn btn-sm btn-secondary-soft" href="<?=APP_URL?>/index.php?r=admin/menu&edit_voucher=<?=$v['id']?>#voucher">Edit</a>
                  <form method="post" action="<?=APP_URL?>/index.php?r=admin/delete-voucher" data-confirm="Hapus voucher ini? Riwayat pemakaian pada pesanan lama tetap tersimpan."><?=csrf_field()?><input type="hidden" name="id" value="<?=$v['id']?>"><button class="btn btn-sm btn-outline-danger">Hapus</button></form>
                </div>
              </td>
            </tr>
          <?php endforeach;
?>
          </tbody>
        </table>
      </div>
    </section>

    <div class="modal fade admin-crud-modal" id="voucherModal" tabindex="-1" aria-labelledby="voucherModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
        <div class="admin-modal-head">
          <div><span class="admin-modal-kicker">Promo pelanggan</span><h2 id="voucherModalLabel"><?= $editVoucher ? 'Edit voucher' : 'Buat voucher' ?></h2></div>
          <button type="button" class="admin-modal-close" data-modal-close aria-label="Tutup formulir voucher"><?= icon('x') ?></button>
        </div>
        <div class="modal-body">
          <form method="post" action="<?=APP_URL?>/index.php?r=admin/save-voucher">
            <?=csrf_field()?>
            <?php if($editVoucher): ?><input type="hidden" name="id" value="<?=(int)$editVoucher['id']?>"><?php endif;
?>
            <div class="row g-3">
              <div class="col-md-6"><label class="form-label">Kode</label><input class="form-control" name="kode" required maxlength="40" style="text-transform:uppercase" value="<?=e($editVoucher['kode'] ?? '')?>" placeholder="MAEM10"></div>
              <div class="col-md-6"><label class="form-label">Tipe</label><select class="form-select" name="tipe"><?php foreach(['NOMINAL'=>'Nominal',
'PERSEN'=>'Persen',
'ONGKIR'=>'Gratis ongkir'] as $val=>$label): ?><option value="<?=$val?>" <?=($editVoucher['tipe'] ?? '')===$val?'selected':''?>><?=$label?></option><?php endforeach;
?></select></div>
              <div class="col-12"><label class="form-label">Deskripsi</label><input class="form-control" name="deskripsi" maxlength="180" value="<?=e($editVoucher['deskripsi'] ?? '')?>" placeholder="Contoh: Potongan Rp10.000 untuk pesanan pertama"></div>
              <div class="col-md-6"><label class="form-label">Nilai</label><input class="form-control" type="number" name="nilai" min="0" step="0.01" required value="<?=e((string)($editVoucher['nilai'] ?? ''))?>"><div class="form-help">Nominal Rp atau persen (0–100). Untuk gratis ongkir, 0 = potong seluruh ongkir.</div></div>
              <div class="col-md-6"><label class="form-label">Min. belanja (Rp)</label><input class="form-control" type="number" name="min_belanja" min="0" step="500" value="<?=e((string)($editVoucher['min_belanja'] ?? 0))?>"></div>
              <div class="col-md-6"><label class="form-label">Maks. potongan (Rp)</label><input class="form-control" type="number" name="maks_potongan" min="0" step="500" value="<?=e((string)($editVoucher['maks_potongan'] ?? ''))?>" placeholder="opsional"></div>
              <div class="col-md-3"><label class="form-label">Kuota total</label><input class="form-control" type="number" name="kuota_total" min="0" step="1" value="<?=e((string)($editVoucher['kuota_total'] ?? 0))?>"><div class="form-help">0 = tanpa batas</div></div>
              <div class="col-md-3"><label class="form-label">Per pelanggan</label><input class="form-control" type="number" name="kuota_per_pelanggan" min="1" step="1" value="<?=e((string)($editVoucher['kuota_per_pelanggan'] ?? 1))?>"></div>
              <div class="col-md-6"><label class="form-label">Mulai</label><input class="form-control" type="date" name="tanggal_mulai" value="<?=e($editVoucher['tanggal_mulai'] ?? '')?>"></div>
              <div class="col-md-6"><label class="form-label">Selesai</label><input class="form-control" type="date" name="tanggal_selesai" value="<?=e($editVoucher['tanggal_selesai'] ?? '')?>"></div>
              <div class="col-12"><label class="check-card"><input type="checkbox" name="status_aktif" value="1" <?=(!$editVoucher || (int)$editVoucher['status_aktif'])?'checked':''?>><span><strong>Aktifkan voucher</strong><small>Voucher nonaktif tidak dapat dipakai saat checkout.</small></span></label></div>
            </div>
            <div class="form-actions"><button class="btn btn-primary" type="submit"><?= $editVoucher ? 'Simpan Perubahan' : 'Simpan Voucher' ?></button><button type="button" class="btn btn-secondary-soft" data-modal-close>Batal</button></div>
          </form>
        </div>
      </div></div>
    </div>
  </section>

  <section id="master" class="admin-section-anchor admin-switch-panel" data-panel-view="master" role="tabpanel" hidden>
    <div class="section-heading mb-3"><div><span class="eyebrow">Master pendukung</span><h2 class="section-title mb-1">Kategori & Topping</h2><p class="text-muted-custom mb-0">Kelola kategori produk di sisi kiri dan topping di sisi kanan. Edit atau hapus data langsung dari daftar.</p></div></div>
    <div class="master-grid">
      <section class="panel-card">
        <div class="panel-header">
          <div><span class="eyebrow">Kategori</span><h2 class="section-title mb-0">Kategori produk</h2><p class="text-muted-custom mb-0">Kelompokkan produk agar mudah dicari pelanggan.</p></div>
          <button type="button" class="btn btn-primary" data-modal-open="#crudModal-categoryCreate"><?= icon('plus') ?> Tambah Kategori</button>
        </div>
        <div class="master-list">
          <?php if(!$cats): ?><div class="empty-state"><strong>Belum ada kategori.</strong><span>Klik Tambah Kategori untuk membuat kategori pertama.</span></div><?php endif;
?>
          <?php foreach($cats as $c): ?>
          <div class="master-item">
            <div><strong><?=e($c['nama'])?></strong><small>ID kategori #<?=$c['id']?></small></div>
            <div class="master-actions">
              <button type="button" class="btn btn-sm btn-secondary-soft" data-edit-category data-id="<?=(int)$c['id']?>" data-name="<?=e($c['nama'])?>">Edit</button>
              <form method="post" action="<?=APP_URL?>/index.php?r=admin/delete-category" data-confirm="Hapus kategori ini? Pastikan tidak sedang dipakai produk."><?=csrf_field()?><input type="hidden" name="id" value="<?=$c['id']?>"><button class="btn btn-sm btn-outline-danger">Hapus</button></form>
            </div>
          </div>
          <?php endforeach;
?>
        </div>
        <form id="categoryCreate" class="row g-3" method="post" action="<?=APP_URL?>/index.php?r=admin/save-category" data-crud-toggle data-crud-no-trigger data-crud-label="Tambah Kategori">
          <?=csrf_field()?>
          <div class="col-12"><label class="form-label">Nama kategori <span class="required-mark">*</span></label><input class="form-control" name="nama" required placeholder="Contoh: Makanan"></div>
          <div class="col-12 form-actions"><button class="btn btn-primary" type="submit">Simpan Kategori</button><button type="button" class="btn btn-secondary-soft" data-modal-close>Batal</button></div>
        </form>
        <form id="categoryEdit" class="row g-3" method="post" action="<?=APP_URL?>/index.php?r=admin/save-category" data-crud-toggle data-crud-no-trigger data-crud-label="Edit Kategori">
          <?=csrf_field()?>
          <input type="hidden" name="id" id="categoryId">
          <div class="col-12"><label class="form-label">Nama kategori <span class="required-mark">*</span></label><input class="form-control" name="nama" id="categoryName" required placeholder="Contoh: Makanan"></div>
          <div class="col-12 form-actions"><button class="btn btn-primary" type="submit">Simpan Perubahan</button><button type="button" class="btn btn-secondary-soft" data-modal-close>Batal</button></div>
        </form>
      </section>

      <section class="panel-card">
        <div class="panel-header">
          <div><span class="eyebrow">Topping</span><h2 class="section-title mb-0">Daftar topping</h2><p class="text-muted-custom mb-0">Topping tambahan dengan harga sesuai pilihan pelanggan.</p></div>
          <button type="button" class="btn btn-primary" data-modal-open="#crudModal-toppingCreate"><?= icon('plus') ?> Tambah Topping</button>
        </div>
        <div class="master-list">
          <?php if(!$tops): ?><div class="empty-state"><strong>Belum ada topping.</strong><span>Klik Tambah Topping untuk membuat topping pertama.</span></div><?php endif;
?>
          <?php foreach($tops as $t): ?>
          <div class="master-item">
            <div><strong><?=e($t['nama'])?></strong><small>+Rp <?=number_format($t['harga_tambahan'],
0,
',',
'.')?></small></div>
            <div class="master-actions">
              <button type="button" class="btn btn-sm btn-secondary-soft" data-edit-topping data-id="<?=(int)$t['id']?>" data-name="<?=e($t['nama'])?>" data-price="<?=e((string)(float)$t['harga_tambahan'])?>">Edit</button>
              <form method="post" action="<?=APP_URL?>/index.php?r=admin/delete-topping" data-confirm="Hapus topping ini? Pastikan tidak sedang dipakai produk."><?=csrf_field()?><input type="hidden" name="id" value="<?=$t['id']?>"><button class="btn btn-sm btn-outline-danger">Hapus</button></form>
            </div>
          </div>
          <?php endforeach;
?>
        </div>
        <form id="toppingCreate" class="row g-3" method="post" action="<?=APP_URL?>/index.php?r=admin/save-topping" data-crud-toggle data-crud-no-trigger data-crud-label="Tambah Topping">
          <?=csrf_field()?>
          <div class="col-md-7"><label class="form-label">Nama topping <span class="required-mark">*</span></label><input class="form-control" name="nama" required placeholder="Baso"></div>
          <div class="col-md-5"><label class="form-label">Tambahan (Rp) <span class="required-mark">*</span></label><input class="form-control" type="number" min="0" name="harga_tambahan" required value="0"></div>
          <div class="col-12 form-actions"><button class="btn btn-primary" type="submit">Simpan Topping</button><button type="button" class="btn btn-secondary-soft" data-modal-close>Batal</button></div>
        </form>
        <form id="toppingEdit" class="row g-3" method="post" action="<?=APP_URL?>/index.php?r=admin/save-topping" data-crud-toggle data-crud-no-trigger data-crud-label="Edit Topping">
          <?=csrf_field()?>
          <input type="hidden" name="id" id="toppingId">
          <div class="col-md-7"><label class="form-label">Nama topping <span class="required-mark">*</span></label><input class="form-control" name="nama" id="toppingName" required></div>
          <div class="col-md-5"><label class="form-label">Tambahan (Rp) <span class="required-mark">*</span></label><input class="form-control" type="number" min="0" name="harga_tambahan" id="toppingPrice" required></div>
          <div class="col-12 form-actions"><button class="btn btn-primary" type="submit">Simpan Perubahan</button><button type="button" class="btn btn-secondary-soft" data-modal-close>Batal</button></div>
        </form>
      </section>
    </div>
  </section>
</div>
<script>
// Modal manual harus berada langsung di body agar tidak terpengaruh panel hidden.
['productModal','voucherModal','recipeModal'].forEach(id=>{const modal=document.getElementById(id);if(modal) document.body.appendChild(modal);});
const fi=document.getElementById('fotoInput'),pv=document.getElementById('photoPreview');
fi?.addEventListener('change',()=>{const f=fi.files?.[0];if(!f)return;const u=URL.createObjectURL(f);pv.innerHTML='<img src="'+u+'" alt="Preview foto produk">';});
document.querySelectorAll('.topping-check input').forEach(i=>i.addEventListener('change',()=>i.closest('.topping-check').classList.toggle('selected',i.checked)));

const switchTabs=[...document.querySelectorAll('.admin-master-switch .admin-tab')];
const switchPanels=[...document.querySelectorAll('.admin-switch-panel')];
const switchLabel=document.getElementById('adminSwitchLabel');
const switchLabels={
  produk:'Mengelola produk',
  resep:'Mengelola resep produk',
  stok:'Mengelola stok produk',
  bahan:'Mengelola stok bahan baku & pembelian',
  voucher:'Mengelola voucher',
  master:'Mengelola kategori & topping'
};
const panelNames=Object.keys(switchLabels);
function activateAdminPanel(name, updateHash=true){
  if(!panelNames.includes(name)) name='produk';
  switchTabs.forEach(tab=>{const active=tab.dataset.panel===name;tab.classList.toggle('active',active);tab.setAttribute('aria-selected',active?'true':'false');});
  switchPanels.forEach(panel=>{const active=panel.dataset.panelView===name;panel.classList.toggle('is-active',active);panel.hidden=!active;});
  if(switchLabel) switchLabel.textContent=switchLabels[name]||'';
  if(updateHash && history.replaceState) history.replaceState(null,'','#'+name);
  window.scrollTo({top:0,behavior:'smooth'});
}
switchTabs.forEach(tab=>tab.addEventListener('click',()=>activateAdminPanel(tab.dataset.panel)));
document.querySelectorAll('[data-switch-to]').forEach(btn=>btn.addEventListener('click',()=>activateAdminPanel(btn.dataset.switchTo)));
const initialHash=location.hash.replace('#','');
activateAdminPanel(panelNames.includes(initialHash)?initialHash:'produk',false);
document.addEventListener('DOMContentLoaded',()=>{
  if (<?= $isEdit ? 'true' : 'false' ?>) { const el=document.getElementById('productModal'); if(el && window.WMJ) WMJ.modal.open(el); }
  if (<?= $recipeMenu ? 'true' : 'false' ?>) { activateAdminPanel('resep',false); const el=document.getElementById('recipeModal'); if(el && window.WMJ) WMJ.modal.open(el); }
  if (<?= $editVoucher ? 'true' : 'false' ?>) { activateAdminPanel('voucher',false); const el=document.getElementById('voucherModal'); if(el && window.WMJ) WMJ.modal.open(el); }
});

// Jika produk yang sama dipilih lagi, cukup buka ulang pop-up tanpa memuat halaman.
document.querySelectorAll('[data-recipe-id]').forEach(link=>{
  link.addEventListener('click',event=>{
    if(link.dataset.recipeId !== '<?=(int)$recipeMenuId?>') return;
    const modal=document.getElementById('recipeModal');
    if(!modal || !window.WMJ) return;
    event.preventDefault();
    WMJ.modal.open(modal);
  });
});

document.getElementById('addRecipeRow')?.addEventListener('click',()=>{
  const box=document.getElementById('recipeRows');
  const first=box?.querySelector('.recipe-row');
  if(!first)return;
  const row=first.cloneNode(true);
  row.querySelectorAll('select').forEach(s=>s.value='');
  row.querySelectorAll('input').forEach(i=>i.value='');
  box.appendChild(row);
  row.querySelector('select')?.focus();
  recipeUnitFilter(row);
});
function recipeUnitFilter(row){const ingredient=row.querySelector('.recipe-ingredient');const unit=row.querySelector('.recipe-unit');const stockUnit=ingredient?.selectedOptions[0]?.dataset.stockUnit||'';if(!unit)return;unit.querySelectorAll('option').forEach(option=>{option.disabled=stockUnit==='pieces'?option.value!=='pieces':(stockUnit?option.value==='pieces':false);});if(stockUnit&&unit.selectedOptions[0]?.disabled)unit.value=stockUnit;}
document.querySelectorAll('#recipeRows .recipe-row').forEach(recipeUnitFilter);
document.getElementById('recipeRows')?.addEventListener('change',event=>{
  if(event.target.matches('.recipe-ingredient')){const row=event.target.closest('.recipe-row');const unit=row?.querySelector('.recipe-unit');const opt=event.target.selectedOptions[0];if(unit&&opt?.dataset.stockUnit)unit.value=opt.dataset.stockUnit;recipeUnitFilter(row);}
});
document.getElementById('recipeRows')?.addEventListener('click',event=>{
  const remove=event.target.closest('.recipe-remove-row');if(!remove)return;
  const rows=document.querySelectorAll('#recipeRows .recipe-row');if(rows.length>1)remove.closest('.recipe-row').remove();else{const row=remove.closest('.recipe-row');row.querySelectorAll('select,input').forEach(el=>el.value='');}
});

// Edit kategori/topping memakai data-atribut agar nama dengan tanda kutip tetap aman.
document.addEventListener('click',event=>{
  const categoryButton=event.target.closest('[data-edit-category]');
  if(categoryButton){
    event.preventDefault();
    const idField=document.getElementById('categoryId');
    const nameField=document.getElementById('categoryName');
    if(idField) idField.value=categoryButton.dataset.id||'';
    if(nameField) nameField.value=categoryButton.dataset.name||'';
    if(window.WMJ) WMJ.modal.open('#crudModal-categoryEdit');
    return;
  }
  const toppingButton=event.target.closest('[data-edit-topping]');
  if(toppingButton){
    event.preventDefault();
    const idField=document.getElementById('toppingId');
    const nameField=document.getElementById('toppingName');
    const priceField=document.getElementById('toppingPrice');
    if(idField) idField.value=toppingButton.dataset.id||'';
    if(nameField) nameField.value=toppingButton.dataset.name||'';
    if(priceField) priceField.value=toppingButton.dataset.price||'0';
    if(window.WMJ) WMJ.modal.open('#crudModal-toppingEdit');
  }
});
</script>
<?php require BASE_PATH.'/view/partials/footer.php';
?>
