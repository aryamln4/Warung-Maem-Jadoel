<?php $title='Halaman tidak ditemukan';
require BASE_PATH.'/view/partials/header.php';
?>
<main class="auth-page" style="min-height:60vh">
  <div class="auth-card panel-card" style="text-align:center">
    <span class="eyebrow">404</span>
    <h2 class="section-title mb-2">Halaman tidak ditemukan</h2>
    <p class="page-subtitle mb-4">Alamat yang kamu buka tidak tersedia. Periksa kembali tautan atau kembali ke halaman utama.</p>
    <div class="d-flex justify-content-center gap-2 flex-wrap">
      <a class="btn btn-dark" href="<?= APP_URL ?>/index.php?r=landing"><?= icon('arrow-right') ?> Ke Halaman Utama</a>
      <a class="btn btn-outline-secondary" href="<?= APP_URL ?>/index.php?r=order/menu"><?= icon('utensils') ?> Lihat Menu</a>
    </div>
  </div>
</main>
<?php require BASE_PATH.'/view/partials/footer.php';
?>
