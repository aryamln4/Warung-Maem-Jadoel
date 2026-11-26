<?php $title='Pelanggan & Ulasan';
require BASE_PATH.'/view/partials/header.php';
$initialTab=($_GET['tab']??'')==='ulasan' || isset($_GET['status']) || isset($_GET['rating']) || isset($_GET['q']) ? 'ulasan' : 'pelanggan';
?>
<div class="admin-content">
  <div class="admin-tabs admin-master-switch" role="tablist" aria-label="Pelanggan dan ulasan">
    <button type="button" class="admin-tab <?=$initialTab==='pelanggan'?'active':''?>" data-panel="pelanggan" role="tab" aria-selected="<?=$initialTab==='pelanggan'?'true':'false'?>">Pelanggan</button>
    <button type="button" class="admin-tab <?=$initialTab==='ulasan'?'active':''?>" data-panel="ulasan" role="tab" aria-selected="<?=$initialTab==='ulasan'?'true':'false'?>">Ulasan</button>
  </div>
  <div class="admin-switch-hint"><span class="switch-dot"></span><span id="adminSwitchLabel"><?=$initialTab==='ulasan'?'Memoderasi ulasan pelanggan':'Mengelola data pelanggan'?></span><span class="switch-separator">•</span><span>Pilih tab untuk berganti tampilan tanpa meninggalkan halaman.</span></div>
  <section id="pelanggan" class="admin-switch-panel <?=$initialTab==='pelanggan'?'is-active':''?>" data-panel-view="pelanggan" role="tabpanel">
    <section class="panel-card"><div class="panel-header"><div><span class="eyebrow">CRM pelanggan</span><h2 class="section-title mb-0">Akun Pelanggan</h2></div></div><div class="table-responsive admin-table-wrap"><table class="table admin-table align-middle mb-0"><thead><tr><th>Nama</th><th>Email</th><th>HP</th><th>Status</th><th>Aksi</th></tr></thead><tbody><?php if(!$customers): ?><tr><td colspan="5" class="text-center py-4">Belum ada pelanggan.</td></tr><?php else: foreach($customers as $c): ?><tr><td><?=e($c['nama'])?></td><td><?=e($c['email'])?></td><td><?=e($c['no_hp']??'-')?></td><td><?=e($c['status'])?></td><td><form method="post" action="<?=APP_URL?>/index.php?r=admin/toggle-customer"><?=csrf_field()?><input type="hidden" name="id" value="<?=$c['id']?>"><input type="hidden" name="status" value="<?=$c['status']==='AKTIF'?'DIBLOKIR':'AKTIF'?>"><button class="btn btn-sm btn-outline-danger"><?=$c['status']==='AKTIF'?'Blokir':'Aktifkan'?></button></form></td></tr><?php endforeach;
endif;
?></tbody></table></div></section>
  </section>
  <section id="ulasan" class="admin-switch-panel review-admin-area <?=$initialTab==='ulasan'?'is-active':''?>" data-panel-view="ulasan" role="tabpanel">
    <section class="panel-card mb-3"><div class="panel-header"><div><span class="eyebrow">Tampilan ulasan</span><h2 class="section-title mb-0">Ulasan Pelanggan</h2><p class="text-secondary mb-0 mt-2">Admin hanya dapat menampilkan atau menyembunyikan ulasan. Rata-rata publik <?=number_format((float)$stats['rata'],
1,
',',
'.')?>/5 dari <?= (int)$stats['jumlah']?> ulasan yang ditampilkan.</p></div></div>
      <form class="row g-3 review-filter-form" method="get"><input type="hidden" name="r" value="admin/customers"><input type="hidden" name="tab" value="ulasan"><div class="col-md-4"><input class="form-control" name="q" value="<?=e($search)?>" placeholder="Cari nama pelanggan / transaksi"></div><div class="col-md-3"><select class="form-select" name="status"><option value="">Semua status</option><?php foreach(['DITAMPILKAN',
'DISEMBUNYIKAN'] as $x): ?><option value="<?=$x?>" <?=$status===$x?'selected':''?>><?=$x?></option><?php endforeach;
?></select></div><div class="col-md-2"><select class="form-select" name="rating"><option value="0">Semua rating</option><?php for($i=5;
$i>=1;
$i--): ?><option value="<?=$i?>" <?=$rating===$i?'selected':''?>><?=$i?> bintang</option><?php endfor;
?></select></div><div class="col-md-2"><button class="btn btn-dark">Filter</button></div></form>
    </section>
    <?php if(!$reviews): ?><div class="alert alert-light">Belum ada ulasan untuk filter ini.</div><?php endif;
?>
    <?php foreach($reviews as $r): ?><article class="panel-card review-card mb-3"><div class="d-flex flex-wrap justify-content-between"><div><strong><?=e($r['nama'])?></strong> <small class="text-secondary">@<?=e($r['username'])?> · Transaksi #<?=(int)$r['transaksi_id']?></small><div class="text-warning"><?=str_repeat(icon('star'),
(int)$r['rating'])?><?=str_repeat(icon('star',
['class'=>'is-empty']),
5-(int)$r['rating'])?></div></div><span class="badge review-status-badge <?=($r['status_ulasan']==='DITAMPILKAN'?'review-status-shown':'review-status-hidden')?> align-self-start"><?=e($r['status_ulasan'])?></span></div><p class="review-comment mt-3 mb-2"><?=nl2br(e($r['komentar']??'Tanpa komentar'))?></p><small class="text-secondary">Dikirim <?=e($r['created_at'])?></small><div class="d-flex flex-wrap gap-2 mt-3"><?php if($r['status_ulasan']==='DITAMPILKAN'): ?><form method="post" action="<?=APP_URL?>/index.php?r=admin/review-status"><?=csrf_field()?><input type="hidden" name="id" value="<?=(int)$r['id']?>"><input type="hidden" name="status" value="DISEMBUNYIKAN"><button class="btn btn-sm btn-outline-danger">Sembunyikan</button></form><?php else: ?><form method="post" action="<?=APP_URL?>/index.php?r=admin/review-status"><?=csrf_field()?><input type="hidden" name="id" value="<?=(int)$r['id']?>"><input type="hidden" name="status" value="DITAMPILKAN"><button class="btn btn-sm btn-outline-success">Tampilkan</button></form><?php endif;
?></div></article><?php endforeach;
?>
  </section>
</div>
<script>
(function(){const tabs=[...document.querySelectorAll('.admin-master-switch .admin-tab')],panels=[...document.querySelectorAll('.admin-switch-panel')],label=document.getElementById('adminSwitchLabel');function activate(name,hash=true){tabs.forEach(t=>{const on=t.dataset.panel===name;t.classList.toggle('active',on);t.setAttribute('aria-selected',on?'true':'false')});panels.forEach(p=>p.classList.toggle('is-active',p.dataset.panelView===name));if(label)label.textContent=name==='ulasan'?'Memoderasi ulasan pelanggan':'Mengelola data pelanggan';if(hash)history.replaceState(null,'','#'+name)}tabs.forEach(t=>t.addEventListener('click',()=>activate(t.dataset.panel)));const requested=location.hash.replace('#','');activate(['pelanggan','ulasan'].includes(requested)?requested:<?=json_encode($initialTab)?>,false)})();
</script>
<style>
/* Perapihan khusus tab Ulasan: beri ruang konsisten agar teks tidak menempel ke tepi kartu. */
.review-admin-area { min-width: 0; }
.review-admin-area .panel-card { border-radius: 16px; }
.review-admin-area > .panel-card:first-child .panel-header { padding: 24px 26px 20px; }
.review-admin-area .review-filter-form { margin: 0; padding: 22px 26px 26px; align-items: end; }
.review-admin-area .review-filter-form > div { min-width: 0; }
.review-admin-area .review-filter-form .btn { min-height: 44px; width: 100%; }
.review-admin-area .review-card { padding: 24px 26px; overflow-wrap: anywhere; }
.review-admin-area .review-card + .review-card { margin-top: 16px; }
.review-admin-area .review-card > .d-flex { gap: 14px 20px; align-items: flex-start; }
.review-admin-area .review-card small { line-height: 1.6; }
.review-admin-area .review-comment { line-height: 1.75; color: #343a40; white-space: normal; overflow-wrap: anywhere; }
.review-admin-area .review-card .d-flex form { margin: 0; }
.review-admin-area .review-card .btn { min-height: 36px; padding: 7px 12px; }
.review-admin-area .review-status-badge { display: inline-flex; align-items: center; justify-content: center; padding: 8px 12px; border-radius: 999px; font-size: .72rem; font-weight: 700; letter-spacing: .035em; line-height: 1; white-space: nowrap; }
.review-admin-area .review-status-shown { color: #176b3a; background: #e2f5e9; border: 1px solid #b7e4c7; }
.review-admin-area .review-status-hidden { color: #a52834; background: #fde8ea; border: 1px solid #f5c2c7; }
@media (max-width: 767px) {
  .review-admin-area > .panel-card:first-child .panel-header { padding: 20px 18px 16px; }
  .review-admin-area .review-filter-form { padding: 18px; }
  .review-admin-area .review-card { padding: 19px 18px; }
  .review-admin-area .review-card .d-flex form { flex: 1 1 100%; }
  .review-admin-area .review-card .d-flex form .btn { width: 100%; }
}
</style>
<?php require BASE_PATH.'/view/partials/footer.php';
?>
