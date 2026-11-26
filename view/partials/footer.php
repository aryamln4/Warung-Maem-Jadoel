<?php if($isAdminArea ?? false): ?>
    </main>
</div>
<?php elseif(!($isCustomerArea ?? false) && !($isAuthPage ?? false)): ?><footer class="public-footer"><div class="public-footer-inner">
  <div class="public-footer-brand"><div class="footer-brand-line"><span class="footer-logo"><img src="<?=APP_URL?>/assets/images/Logo.jpg" alt="Logo Warung Maem Jadoel"></span><div><strong>Warung Maem Jadoel</strong><small>ANEKA JAJANAN DENGAN CITARASA YANG AUTENTIK</small></div></div><p>Rasa jadoel yang bikin kangen, dengan cara pesan yang lebih praktis.</p></div>
  <div><h6>Navigasi</h6><a href="<?=APP_URL?>/#promo">Promo</a><a href="<?=APP_URL?>/#menu">Menu</a><a href="<?=APP_URL?>/#tentang">Tentang</a><a href="<?=APP_URL?>/#faq">FAQ</a></div>
  <div><h6>Informasi Warung</h6><a href="<?=APP_URL?>/#lokasi">Lokasi</a><a href="<?=APP_URL?>/#lokasi">Jam buka</a><a href="<?=APP_URL?>/#ulasan-pelanggan">Ulasan pelanggan</a></div>
  <div><h6>Hubungi Kami</h6><p><?= icon('phone') ?> WhatsApp: 08xx-xxxx-xxxx</p><p><?= icon('instagram') ?> Instagram: @maemjadoel</p><a class="footer-contact-btn" href="<?=APP_URL?>/#lokasi">Kunjungi warung <?= icon('arrow-right') ?></a></div>
</div><div class="public-footer-bottom"><span>© <?=date('Y')?> Warung Maem Jadoel</span><span>Rasa yang khas, cara yang lebih praktis.</span></div></footer>
<?php endif;
?>
<?php if($isCustomerArea ?? false): ?>
<nav class="ec-bottom-nav" aria-label="Navigasi cepat pelanggan">
  <a class="<?= ($route ?? '') === 'order/menu' ? 'active' : '' ?>" href="<?=APP_URL?>/index.php?r=order/menu"><?= icon('home') ?><span>Beranda</span></a>
  <a class="<?= ($route ?? '') === 'customer/cart' ? 'active' : '' ?>" href="<?=APP_URL?>/index.php?r=order/menu#products"><?= icon('menu') ?><span>Menu</span></a>
  <a class="<?= in_array(($route ?? ''),
['order/history',
'order/detail'],
true) ? 'active' : '' ?>" href="<?=APP_URL?>/index.php?r=order/history"><?= icon('receipt') ?><span>Pesanan</span></a>
  <a class="<?= str_starts_with((string)($route ?? ''),
'chat') ? 'active' : '' ?>" href="<?=APP_URL?>/index.php?r=chat"><?= icon('chat') ?><span>Chat</span><?php if(($chatUnread ?? 0) > 0): ?><span class="ec-badge"><?= (int)$chatUnread ?></span><?php endif;
?></a>
  <a class="<?= ($route ?? '') === 'auth/profile' ? 'active' : '' ?>" href="<?=APP_URL?>/index.php?r=auth/profile"><?= icon('user') ?><span>Akun</span></a>
</nav>
<?php endif;
?>
<script src="<?=APP_URL?>/assets/js/app.js?v=<?= asset_version('assets/js/app.js') ?>"></script>
<script>document.addEventListener('click',function(e){var minus=e.target.closest('[data-qty-minus]'),plus=e.target.closest('[data-qty-plus]');if(!minus&&!plus)return;var wrap=(minus||plus).closest('.landing-qty-control');if(!wrap)return;var input=wrap.querySelector('input[type="number"]');if(!input)return;var min=parseInt(input.min||'1',10),max=parseInt(input.max||'999',10),value=parseInt(input.value||'1',10);value=minus?value-1:value+1;input.value=Math.min(max,Math.max(min,value));});</script>
<script>
/* Tombol "Tambah ke keranjang" di modal produk landing:
   modal produk ditutup, lalu modal login dibuka setelah animasi selesai. */
document.addEventListener('click',function(e){
  var b=e.target.closest('[data-landing-login-after]');
  if(!b||b.disabled)return;
  var productModal=b.closest('.modal');
  var login=document.getElementById('loginModal');
  if(!login||!window.WMJ)return;
  if(productModal){
    productModal.addEventListener('wmj:hidden',function(){
      WMJ.modal.open(login);
    },{once:true});
    WMJ.modal.close(productModal);
  }else{
    WMJ.modal.open(login);
  }
});
</script>
<script>document.addEventListener('click',function(e){var b=e.target.closest('.auth-password-toggle');if(!b)return;var input=document.getElementById(b.getAttribute('data-target'));if(!input)return;var visible=input.type==='text';input.type=visible?'password':'text';b.classList.toggle('is-visible',!visible);b.setAttribute('aria-label',visible?'Tampilkan kata sandi':'Sembunyikan kata sandi');});</script>
<?php if (($route ?? '') === 'landing' && ($_GET['login'] ?? '') === '1'): ?>
<script>document.addEventListener('DOMContentLoaded',function(){var modal=document.getElementById('loginModal');if(modal&&window.WMJ){WMJ.modal.open(modal);}});</script>
<?php endif;
?>
<?php if (($route ?? '') === 'landing' && ($_GET['forgot'] ?? '') === '1'): ?>
<script>document.addEventListener('DOMContentLoaded',function(){var modal=document.getElementById('forgotModal');if(modal&&window.WMJ){WMJ.modal.open(modal);var input=document.getElementById('modalForgotEmail');if(input){input.focus();}}});</script>
<?php endif;
?>
<?php if (($route ?? '') === 'landing' && ($_GET['register'] ?? '') === '1'): ?>
<script>document.addEventListener('DOMContentLoaded',function(){var modal=document.getElementById('registerModal');if(modal&&window.WMJ){WMJ.modal.open(modal);var input=document.getElementById('modalRegUsername');if(input){input.focus();}}});</script>
<?php endif;
?>
</body></html>
