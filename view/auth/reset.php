<?php $title='Buat Sandi Baru';
require BASE_PATH.'/view/partials/header.php';
?>
<main class="auth-page">
  <div class="auth-card panel-card">
    <span class="eyebrow">KEAMANAN AKUN</span>
    <h2 class="section-title mb-3">Buat Sandi Baru</h2>
    <p class="page-subtitle mb-4">Gunakan minimal delapan karakter dan jangan bagikan kata sandimu kepada siapa pun.</p>
    <form method="post">
      <?php echo csrf_field();
?>
      <input type="hidden" name="token" value="<?=e($raw)?>">
      <?php if(!empty($error)): ?><div class="alert alert-danger"><?= icon('alert-circle') ?> <?=e($error)?></div><?php endif;
?>
      <label class="form-label" for="resetPassword">Sandi baru</label>
      <input id="resetPassword" class="form-control mb-3" type="password" name="password" minlength="8" autocomplete="new-password" placeholder="Minimal 8 karakter" required>
      <label class="form-label" for="resetConfirm">Konfirmasi</label>
      <input id="resetConfirm" class="form-control mb-4" type="password" name="password_confirmation" minlength="8" autocomplete="new-password" placeholder="Ulangi kata sandi" required>
      <button class="btn btn-dark w-100 touch"><?= icon('check') ?> Simpan</button>
    </form>
  </div>
</main>
<?php require BASE_PATH.'/view/partials/footer.php';
?>
