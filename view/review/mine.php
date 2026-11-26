<?php $title='Ulasan Saya';
require BASE_PATH.'/view/partials/header.php';
?>
<main class="customer-simple-page review-page-v10"><div class="customer-simple-shell review-shell"><span class="customer-eyebrow">PENGALAMANMU</span><h1>Ulasan saya.</h1><p class="customer-lead">Bagikan pengalaman setelah pesanan selesai dan bantu pelanggan lain menemukan menu favorit.</p>
<div class="review-v10-grid"><?php $reviewed=$reviews;
if(!$reviewed): ?><div class="customer-empty-card">Kamu belum mengirim ulasan.</div><?php endif;
?><?php foreach($reviewed as $r): ?><article class="review-v10-card"><div class="landing-stars"><?=str_repeat(icon('star'),
(int)$r['rating'])?><?=str_repeat(icon('star',
['class'=>'is-empty']),
5-(int)$r['rating'])?></div><p>“<?=nl2br(e($r['komentar']??'—'))?>”</p><small>Pesanan #MJ-<?=str_pad((int)$r['transaksi_id'],
4,
'0',
STR_PAD_LEFT)?> · <?=date('d M Y',
strtotime($r['created_at']))?></small><span class="review-status-v10"><?=e($r['status_ulasan'])?></span><?php if(time()<=strtotime($r['created_at'])+7*86400): ?><a href="<?=APP_URL?>/index.php?r=review/create&id=<?=(int)$r['transaksi_id']?>">Edit ulasan</a><?php endif;
?></article><?php endforeach;
?>
<?php $reviewedIds=[];
foreach($reviews as $r){
    $reviewedIds[]=(int)$r['transaksi_id'];
}
?><article class="review-v10-cta"><strong>Pesanan yang sudah selesai</strong><p>Punya pengalaman baru? Beri ulasan dari Riwayat Pesanan setelah pesanan berstatus Selesai.</p><a href="<?=APP_URL?>/index.php?r=order/history">Lihat riwayat pesanan</a></article></div></div></main>
<?php require BASE_PATH.'/view/partials/footer.php';
?>
