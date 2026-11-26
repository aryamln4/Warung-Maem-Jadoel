<?php $title='Beri Ulasan';
require BASE_PATH.'/view/partials/header.php';
?>
<main class="auth-page">
  <div class="auth-card panel-card">
    <span class="eyebrow">PENGALAMANMU</span>
    <h2 class="section-title mb-3">Beri Ulasan</h2>
    <p class="page-subtitle mb-4">Transaksi #<?= (int)$id ?>. Ulasan dapat diedit selama 7 hari sejak pertama dikirim.</p>
    <form method="post">
      <?=csrf_field()?>
      <input type="hidden" name="transaksi_id" value="<?= (int)$id ?>">
      <label class="form-label" for="rating">Rating</label>
      <select class="form-select mb-3" id="rating" name="rating" required>
        <option value="">Pilih rating</option>
        <?php for($i=5;
$i>=1;
$i--): ?><option value="<?=$i?>" <?=((int)($review['rating']??0)===$i?'selected':'')?>><?=str_repeat(icon('star'),
$i)?> <?=$i?> bintang</option><?php endfor;
?>
      </select>
      <label class="form-label" for="komentar">Komentar (opsional)</label>
      <textarea class="form-control mb-4" id="komentar" name="komentar" rows="4" maxlength="2000" placeholder="Ceritakan pengalamanmu di Warung Maem Jadoel..."><?=e($review['komentar']??'')?></textarea>
      <div class="form-actions">
        <button class="btn btn-warning touch"><?= icon('message') ?> Kirim Ulasan</button>
        <a class="btn btn-secondary-soft touch" href="<?=APP_URL?>/index.php?r=order/history">Kembali</a>
      </div>
    </form>
  </div>
</main>
<?php require BASE_PATH.'/view/partials/footer.php';
?>
