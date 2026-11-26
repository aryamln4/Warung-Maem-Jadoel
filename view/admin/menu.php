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
  <div class="admin-switch-hint"><span class="switch-dot"></span><span id="adminSwitchLabel">Mengelola produk</span><span class="switch-separator">â€¢</span><span>Bagian Produk &amp; Stok: data produk, resep, stok produk, stok bahan baku, voucher, kategori, dan topping.</span></div>

  <section id="produk" class="admin-section-anchor admin-switch-panel is-active" data-panel-view="produk" role="tabpanel">
    <div class="d-flex justify-content-end mb-3">
      <button type="button" class="btn btn-primary" data-modal-open="#productModal"><?= icon('plus') ?> Tambah Produk</button>
    </div>
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

          <div class="form-actions"><button class="btn btn-primary btn-lg touch" type="submit"><?= $isEdit ? 'Simpan Perubahan' : 'Simpan Produk' ?></button><?php if($isEdit): ?><a class="btn btn-secondary-soft btn-lg touch" href="<?=APP_URL?>/index.php?r=admin/menu">Batal</a><?php endif;
?></div>
        </form>
      </section>
      </div></div>
    </div>

    <section class="panel-card mt-4">
      <div class="panel-header"><div><span class="eyebrow">Katalog</span><h2 class="section-title mb-0">Daftar produk</h2><p class="text-muted-custom mb-0">Edit untuk memperbarui data, Arsipkan untuk menyembunyikan, atau Hapus untuk menghapus permanen jika belum dipakai.</p></div><span class="menu-count"><?=count($menus)?> produk</span></div>
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
'.')?></strong><div class="product-meta"><span><?= $m['punya_topping']?'Topping aktif':'Tanpa topping' ?></span><span><?= $m['punya_level_pedas']?'Level 0â€“5':'Tanpa level' ?></span><span><?= (int)$m['status_aktif']?'Aktif':'Nonaktif' ?></span></div></div>
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
    <div class="section-heading mb-3"><div><span class="eyebrow">Takaran per porsi</span><h2 class="section-title mb-1">Resep produk</h2><p class="text-muted-custom mb-0">Tentukan bahan baku dan takaran untuk satu porsi produk. Saat stok produk ditambah di tab Stok Produk, stok bahan otomatis ikut bertambah sesuai resep.</p></div></div>
    <div class="master-grid">
      <section class="panel-card">
        <div class="panel-header"><div><span class="eyebrow">Daftar produk</span><h2 class="section-title mb-0">Pilih produk</h2></div><span class="menu-count"><?=count($recipeSummary)?> produk</span></div>
        <div class="master-list">
          <?php if(!$recipeSummary): ?><div class="empty-state"><strong>Belum ada produk.</strong><span>Tambahkan produk terlebih dahulu.</span></div><?php endif;
?>
          <?php foreach($recipeSummary as $r): ?>
            <div class="master-item">
              <div><strong><?=e($r['nama_produk'])?></strong><small><?=e($r['kategori'])?> Â· <?= (int)$r['bahan_count'] ?> bahan Â· stok <?= (int)$r['stok'] ?></small></div>
              <div class="master-actions">
                <a class="btn btn-sm <?= (int)$recipeMenuId === (int)$r['id'] ? 'btn-dark' : 'btn-secondary-soft' ?>" href="<?=APP_URL?>/index.php?r=admin/menu&resep=<?=$r['id']?>#resep"><?= (int)$r['bahan_count'] ? 'Ubah resep' : 'Atur resep' ?></a>
              </div>
            </div>
          <?php endforeach;
?>
        </div>
      </section>

      <section class="panel-card">
        <div class="panel-header"><div><span class="eyebrow">Takaran</span><h2 class="section-title mb-0"><?= $recipeMenu ? 'Resep '.e($recipeMenu['nama_produk']) : 'Editor resep' ?></h2><p class="text-muted-custom mb-0"><?= $recipeMenu ? 'Isi jumlah bahan untuk 1 porsi. Kosongkan bahan yang tidak dipakai.' : 'Pilih produk di daftar sebelah untuk mengatur resepnya.' ?></p></div></div>
        <?php if($recipeMenu): ?>
        <form method="post" action="<?=APP_URL?>/index.php?r=admin/save-recipe" data-crud-toggle data-crud-label="Form Resep" class="compact-form">
          <?=csrf_field()?>
          <input type="hidden" name="menu_id" value="<?=(int)$recipeMenu['id']?>">
          <div id="recipeRows">
            <?php
 $recipeRowCount = 0;
$existing = [];
foreach($recipeRows as $rr){
    $existing[] = $rr;
}
$rowCount = max(3,
count($existing) + 2);
for($i=0;
$i<$rowCount;
$i++): $row=$existing[$i] ?? null;
$recipeRowCount++;
?>
            <div class="row g-2 align-items-end recipe-row" style="margin-bottom:10px">
              <div class="col-6"><label class="form-label">Bahan</label><select class="form-select" name="bahan_baku_id[]"><option value="">â€” Pilih bahan â€”</option><?php foreach($ingredients as $ing): ?><option value="<?=$ing['id']?>" <?= $row && (int)$row['bahan_baku_id']===(int)$ing['id'] ? 'selected' : '' ?>><?=e($ing['nama_bahan'])?> (<?=e($ing['satuan'])?>)</option><?php endforeach;
?></select></div>
              <div class="col-3"><label class="form-label">Jumlah</label><input class="form-control" type="number" name="jumlah[]" min="0" step="0.001" value="<?= $row ? e(rtrim(rtrim(number_format((float)$row['jumlah'],
3,
'.',
''),
'0'),
'.')) : '' ?>" placeholder="0"></div>
              <div class="col-3"><label class="form-label">Catatan</label><input class="form-control" name="keterangan[]" maxlength="120" value="<?=e($row['keterangan'] ?? '')?>" placeholder="opsional"></div>
            </div>
            <?php endfor;
?>
          </div>
          <div class="form-actions"><button class="btn btn-secondary-soft" type="button" id="addRecipeRow"><?= icon('plus') ?> Tambah baris</button><button class="btn btn-primary" type="submit">Simpan Resep</button></div>
          <div class="form-help" style="margin-top:8px">Contoh: Mie Jebew memakai 150 gram mie, 30 gram bumbu, 1 pcs telur. Menambah 10 stok Mie Jebew akan menambah 1.500 gram mie, 300 gram bumbu, dan 10 pcs telur.</div>
        </form>
        <?php else: ?>
          <div class="empty-state" style="padding:36px 20px"><strong>Belum ada produk dipilih.</strong><span>Klik â€œAtur resepâ€ pada produk di sebelah kiri.</span></div>
        <?php endif;
?>
      </section>
    </div>
  </section>

  <section id="stok" class="admin-section-anchor admin-switch-panel" data-panel-view="stok" role="tabpanel" hidden>
    <?php require BASE_PATH.'/view/admin/_stock_products.php'; ?>
  </section>

  <section id="bahan" class="admin-section-anchor admin-switch-panel" data-panel-view="bahan" role="tabpanel" hidden>
    <?php require BASE_PATH.'/view/admin/_stock_bahan.php'; ?>
  </section>

  <section id="voucher" class="admin-section-anchor admin-switch-panel" data-panel-view="voucher" role="tabpanel" hidden>
    <div class="section-heading mb-3"><div><span class="eyebrow">Promo pelanggan</span><h2 class="section-title mb-1">Voucher</h2><p class="text-muted-custom mb-0">Tiga tipe: nominal, persen, dan gratis ongkir. Kuota dihitung dari pemakaian nyata pada pesanan.</p></div></div>
    <div class="master-grid">
      <section class="panel-card">
        <div class="panel-header"><div><span class="eyebrow">Daftar voucher</span><h2 class="section-title mb-0">Voucher aktif &amp; arsip</h2></div><span class="menu-count"><?=count($vouchers)?> voucher</span></div>
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
                <td><?= (int)$v['kuota_total'] > 0 ? (int)$v['terpakai'].' / '.(int)$v['kuota_total'] : (int)$v['terpakai'].' / âˆž' ?><br><small><?=(int)$v['kuota_per_pelanggan']?>Ã— per pelanggan</small></td>
                <td><small><?=e($v['tanggal_mulai'] ?: 'â€”')?><br><?=e($v['tanggal_selesai'] ?: 'â€”')?></small></td>
                <td><span class="status-chip <?=$active?'status-selesai':'status-dibatalkan'?>"><?=$active?'Aktif':'Nonaktif'?></span></td>
                <td class="text-end" style="white-space:nowrap">
                  <a class="btn btn-sm btn-secondary-soft" href="<?=APP_URL?>/index.php?r=admin/menu&edit_voucher=<?=$v['id']?>#voucher">Edit</a>
                  <form class="d-inline" method="post" action="<?=APP_URL?>/index.php?r=admin/delete-voucher" data-confirm="Hapus voucher ini? Riwayat pemakaian pada pesanan lama tetap tersimpan."><?=csrf_field()?><input type="hidden" name="id" value="<?=$v['id']?>"><button class="btn btn-sm btn-outline-danger">Hapus</button></form>
                </td>
              </tr>
            <?php endforeach;
?>
            </tbody>
          </table>
        </div>
      </section>

      <section class="panel-card">
        <div class="panel-header"><div><span class="eyebrow">Promo pelanggan</span><h2 class="section-title mb-0">Buat voucher</h2><p class="text-muted-custom mb-0">Atur kode promo, nominal potongan, kuota, dan masa berlaku voucher.</p></div></div>
        <div class="voucher-cta">
          <button type="button" class="btn btn-primary" data-modal-open="#voucherModal"><?= icon('plus') ?> <?= $editVoucher ? 'Edit Voucher' : 'Buat Voucher' ?></button>
        </div>
      </section>
    </div>

    <div class="modal fade admin-crud-modal" id="voucherModal" tabindex="-1" aria-labelledby="voucherModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
        <div class="admin-modal-head">
          <div><span class="admin-modal-kicker">Promo pelanggan</span><h2 id="voucherModalLabel"><?= $editVoucher ? 'Edit voucher' : 'Buat voucher' ?></h2></div>
          <button type="button" class="admin-modal-close" data-modal-close aria-label="Tutup formulir voucher"><?= icon('x') ?></button>
        </div>
        <div class="modal-body">
          <form method="post" action="<?=APP_URL?>/index.php?r=admin/save-voucher" class="compact-form">
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
              <div class="col-md-6"><label class="form-label">Nilai</label><input class="form-control" type="number" name="nilai" min="0" step="0.01" required value="<?=e((string)($editVoucher['nilai'] ?? ''))?>"><div class="form-help">Nominal Rp atau persen (0â€“100). Untuk gratis ongkir, 0 = potong seluruh ongkir.</div></div>
              <div class="col-md-6"><label class="form-label">Min. belanja (Rp)</label><input class="form-control" type="number" name="min_belanja" min="0" step="500" value="<?=e((string)($editVoucher['min_belanja'] ?? 0))?>"></div>
              <div class="col-md-6"><label class="form-label">Maks. potongan (Rp)</label><input class="form-control" type="number" name="maks_potongan" min="0" step="500" value="<?=e((string)($editVoucher['maks_potongan'] ?? ''))?>" placeholder="opsional"></div>
              <div class="col-md-3"><label class="form-label">Kuota total</label><input class="form-control" type="number" name="kuota_total" min="0" step="1" value="<?=e((string)($editVoucher['kuota_total'] ?? 0))?>"><div class="form-help">0 = tanpa batas</div></div>
              <div class="col-md-3"><label class="form-label">Per pelanggan</label><input class="form-control" type="number" name="kuota_per_pelanggan" min="1" step="1" value="<?=e((string)($editVoucher['kuota_per_pelanggan'] ?? 1))?>"></div>
              <div class="col-md-6"><label class="form-label">Mulai</label><input class="form-control" type="date" name="tanggal_mulai" value="<?=e($editVoucher['tanggal_mulai'] ?? '')?>"></div>
              <div class="col-md-6"><label class="form-label">Selesai</label><input class="form-control" type="date" name="tanggal_selesai" value="<?=e($editVoucher['tanggal_selesai'] ?? '')?>"></div>
              <div class="col-12"><label class="check-card"><input type="checkbox" name="status_aktif" value="1" <?=(!$editVoucher || (int)$editVoucher['status_aktif'])?'checked':''?>><span><strong>Aktifkan voucher</strong><small>Voucher nonaktif tidak dapat dipakai saat checkout.</small></span></label></div>
            </div>
            <div class="form-actions"><button class="btn btn-primary" type="submit"><?= $editVoucher ? 'Simpan Perubahan' : 'Simpan Voucher' ?></button></div>
          </form>
        </div>
      </div></div>
    </div>
  </section>

  <section id="master" class="admin-section-anchor admin-switch-panel" data-panel-view="master" role="tabpanel" hidden>
    <div class="section-heading mb-3"><div><span class="eyebrow">Master pendukung</span><h2 class="section-title mb-1">Kategori & Topping</h2><p class="text-muted-custom mb-0">Kelola kategori produk dan topping dari satu tempat.</p></div></div>
    <div class="master-grid">
      <section class="panel-card"><div class="panel-header"><div><span class="eyebrow">Kategori</span><h2 class="section-title mb-0">Kategori produk</h2></div></div>
        <form class="compact-form" method="post" action="<?=APP_URL?>/index.php?r=admin/save-category" data-crud-toggle data-crud-label="Form Kategori"><?=csrf_field()?><label class="form-label">Nama kategori</label><div class="input-group"><input class="form-control" name="nama" required placeholder="Contoh: Makanan"><button class="btn btn-primary">Tambah</button></div></form>
        <div class="master-list"><?php foreach($cats as $c): ?><div class="master-item"><div><strong><?=e($c['nama'])?></strong><small>ID kategori #<?=$c['id']?></small></div><div class="master-actions"><button type="button" class="btn btn-sm btn-secondary-soft" data-edit-category data-id="<?=(int)$c['id']?>" data-name="<?=e($c['nama'])?>">Edit</button><form method="post" action="<?=APP_URL?>/index.php?r=admin/delete-category" data-confirm="Hapus kategori ini? Pastikan tidak sedang dipakai produk."><?=csrf_field()?><input type="hidden" name="id" value="<?=$c['id']?>"><button class="btn btn-sm btn-outline-danger">Hapus</button></form></div></div><?php endforeach;
?></div>
        <form id="categoryEdit" class="compact-form" method="post" action="<?=APP_URL?>/index.php?r=admin/save-category" data-crud-toggle data-crud-label="Form Edit Kategori" data-crud-no-trigger><?=csrf_field()?><input type="hidden" name="id" id="categoryId"><label class="form-label">Nama kategori</label><div class="input-group"><input class="form-control" name="nama" id="categoryName" required><button class="btn btn-dark">Simpan</button><button type="button" class="btn btn-secondary-soft" data-modal-close>Batal</button></div></form>
      </section>

      <section class="panel-card"><div class="panel-header"><div><span class="eyebrow">Topping</span><h2 class="section-title mb-0">Daftar topping</h2></div></div>
        <form class="compact-form" method="post" action="<?=APP_URL?>/index.php?r=admin/save-topping" data-crud-toggle data-crud-label="Form Topping"><input type="hidden" name="id" value=""><?=csrf_field()?><div class="row g-2"><div class="col-7"><label class="form-label">Nama topping</label><input class="form-control" name="nama" required placeholder="Baso"></div><div class="col-5"><label class="form-label">Tambahan</label><input class="form-control" type="number" min="0" name="harga_tambahan" required value="0"></div></div><button class="btn btn-primary mt-3">Tambah topping</button></form>
        <div class="master-list"><?php foreach($tops as $t): ?><div class="master-item"><div><strong><?=e($t['nama'])?></strong><small>+Rp <?=number_format($t['harga_tambahan'],
0,
',',
'.')?></small></div><div class="master-actions"><button type="button" class="btn btn-sm btn-secondary-soft" data-edit-topping data-id="<?=(int)$t['id']?>" data-name="<?=e($t['nama'])?>" data-price="<?=e((string)(float)$t['harga_tambahan'])?>">Edit</button><form method="post" action="<?=APP_URL?>/index.php?r=admin/delete-topping" data-confirm="Hapus topping ini? Pastikan tidak sedang dipakai produk."><?=csrf_field()?><input type="hidden" name="id" value="<?=$t['id']?>"><button class="btn btn-sm btn-outline-danger">Hapus</button></form></div></div><?php endforeach;
?></div>
        <form id="toppingEdit" class="compact-form" method="post" action="<?=APP_URL?>/index.php?r=admin/save-topping" data-crud-toggle data-crud-label="Form Edit Topping" data-crud-no-trigger><?=csrf_field()?><input type="hidden" name="id" id="toppingId"><div class="row g-2"><div class="col-7"><label class="form-label">Nama topping</label><input class="form-control" name="nama" id="toppingName" required></div><div class="col-5"><label class="form-label">Tambahan</label><input class="form-control" type="number" min="0" name="harga_tambahan" id="toppingPrice" required></div></div><div class="form-actions"><button class="btn btn-dark">Simpan</button><button type="button" class="btn btn-secondary-soft" data-modal-close>Batal</button></div></form>
      </section>
    </div>
  </section>
</div>
<script>
// Modal manual harus berada langsung di body agar tidak terpengaruh panel hidden.
['productModal','voucherModal'].forEach(id=>{const modal=document.getElementById(id);if(modal) document.body.appendChild(modal);});
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
  if (<?= $recipeMenu ? 'true' : 'false' ?>) activateAdminPanel('resep',false);
  if (<?= $editVoucher ? 'true' : 'false' ?>) { activateAdminPanel('voucher',false); const el=document.getElementById('voucherModal'); if(el && window.WMJ) WMJ.modal.open(el); }
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
