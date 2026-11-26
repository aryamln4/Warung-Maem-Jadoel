<?php $title='Meja Dine-in';
require BASE_PATH.'/view/partials/header.php';
?>
<div class="admin-content">
</div>

  <?php if($msg=flash('success')): ?><div class="alert alert-success"><?=e($msg)?></div><?php endif;
?>
  <?php if($msg=flash('error')): ?><div class="alert alert-danger"><?=e($msg)?></div><?php endif;
?>

  <section class="panel-card mb-4">
    <div class="panel-header"><div><span class="eyebrow">Tambah</span><h2 class="section-title mb-1">Tambah meja baru</h2><p class="text-muted-custom mb-0">Contoh: 1, 2, 3, 4 atau nama meja lain.</p></div></div>
    <form method="post" action="<?=APP_URL?>/index.php?r=admin/add-table" data-crud-toggle data-crud-label="Form Tambah Meja" class="row g-3 align-items-end">
      <?=csrf_field()?>
      <div class="col-md-5"><label class="form-label">Nomor / nama meja</label><input class="form-control" name="nomor_meja" placeholder="Contoh: 5" required></div>
      <div class="col-md-3"><button class="btn btn-dark w-100 touch">Tambah Meja</button></div>
    </form>
  </section>

  <section class="panel-card">
    <div class="panel-header"><div><span class="eyebrow">Daftar meja</span><h2 class="section-title mb-1">Meja tersedia</h2><p class="text-muted-custom mb-0">Pelanggan akan melihat meja aktif di checkout.</p></div></div>
    <div class="table-responsive admin-table-wrap">
      <table class="table admin-table align-middle mb-0">
        <thead><tr><th>Meja</th><th>Status</th><th>Dibuat</th><th class="text-end">Aksi</th></tr></thead>
        <tbody>
        <?php if(!$tables): ?><tr><td colspan="4"><div class="empty-state"><strong>Belum ada meja.</strong><span>Tambahkan meja pertama di atas.</span></div></td></tr>
        <?php else: foreach($tables as $t): ?>
          <tr>
            <td><strong>Meja <?=e($t['nomor_meja'])?></strong></td>
            <td><span class="status-chip <?=((int)$t['status_aktif']===1?'status-selesai':'status-dibatalkan')?>"><?=((int)$t['status_aktif']===1?'Aktif':'Nonaktif')?></span></td>
            <td><?=e(date('d M Y',
strtotime($t['created_at'])))?></td>
            <td class="text-end">
              <button type="button" class="btn btn-sm btn-outline-dark" data-collapse-target="#edit<?=$t['id']?>">Edit</button>
              <form method="post" action="<?=APP_URL?>/index.php?r=admin/toggle-table" class="d-inline"><?=csrf_field()?><input type="hidden" name="id" value="<?=$t['id']?>"><button class="btn btn-sm btn-outline-secondary"><?=((int)$t['status_aktif']===1?'Nonaktifkan':'Aktifkan')?></button></form>
              <form method="post" action="<?=APP_URL?>/index.php?r=admin/delete-table" class="d-inline" onsubmit="return confirm('Hapus meja ini? Riwayat transaksi tetap aman, tetapi transaksi lama tidak lagi menampilkan nomor meja.')"><?=csrf_field()?><input type="hidden" name="id" value="<?=$t['id']?>"><button class="btn btn-sm btn-outline-danger">Hapus</button></form>
            </td>
          </tr>
          <tr class="collapse" id="edit<?=$t['id']?>"><td colspan="4">
            <form method="post" action="<?=APP_URL?>/index.php?r=admin/edit-table" data-crud-toggle data-crud-label="Form Edit Meja" class="row g-2 align-items-end py-2">
              <?=csrf_field()?><input type="hidden" name="id" value="<?=$t['id']?>">
              <div class="col-md-5"><label class="form-label">Nomor / nama meja</label><input class="form-control" name="nomor_meja" value="<?=e($t['nomor_meja'])?>" required></div>
              <div class="col-md-3"><button class="btn btn-dark">Simpan Perubahan</button></div>
            </form>
          </td></tr>
        <?php endforeach;
endif;
?>
        </tbody>
      </table>
    </div>
  </section>
</div>
<?php require BASE_PATH.'/view/partials/footer.php';
?>
