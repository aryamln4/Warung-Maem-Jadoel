<?php $title = 'Pesanan Saya';
require BASE_PATH.'/view/partials/header.php';
?>
<?php
$runningCount = 0;
$doneCount = 0;
foreach($orders as $o){
    if(in_array($o['status_pesanan'],
    ['SELESAI',
    'DIBATALKAN'],
    true)) $doneCount++;
    else $runningCount++;
}
?>
<div class="ec-page">
  <div class="ec-page-head">
    <div>
      <span class="ec-eyebrow">Aktivitas belanja</span>
      <h1>Pesanan saya.</h1>
      <p>Pantau status pesanan dan buka chat warung untuk informasi pengiriman.</p>
    </div>
    <a class="ec-btn ec-btn-dark" href="<?=APP_URL?>/index.php?r=order/menu"><?= icon('plus') ?> Pesan lagi</a>
  </div>

  <div class="ec-orders-toolbar">
    <div class="ec-tabs" role="tablist" aria-label="Filter pesanan">
      <button class="ec-tab active" type="button" data-order-filter="all">Semua <b><?=count($orders)?></b></button>
      <button class="ec-tab" type="button" data-order-filter="running">Berlangsung <b><?=$runningCount?></b></button>
      <button class="ec-tab" type="button" data-order-filter="done">Selesai <b><?=$doneCount?></b></button>
    </div>
  </div>

  <?php if(!$orders): ?>
    <div class="ec-panel" style="text-align:center;padding:60px 24px">
      <div style="font-size:2rem;color:var(--c-accent);margin-bottom:10px"><?= icon('receipt') ?></div>
      <h2 style="margin:0 0 8px;font-size:1.2rem">Belum ada pesanan.</h2>
      <p style="color:var(--c-muted);margin:0 0 20px">Yuk pilih menu favoritmu dan mulai pesan.</p>
      <a class="ec-btn ec-btn-dark" href="<?=APP_URL?>/index.php?r=order/menu">Lihat menu</a>
    </div>
  <?php endif;
?>

  <div id="orderList">
    <?php foreach($orders as $o): $txId=(int)$o['id'];
$existingReview=$reviewsByTransaction[$txId]??null;
$canEditReview=$existingReview && time()<=strtotime($existingReview['created_at'])+7*86400;
$isDone=in_array($o['status_pesanan'],
['SELESAI',
'DIBATALKAN'],
true);
?>
      <article class="ec-order-card" data-order-group="<?= $isDone ? 'done' : 'running' ?>">
        <div class="ec-order-head">
          <span class="ec-status-chip st-<?=strtolower(e($o['status_pesanan']))?>"><?=e(order_status_label((string)$o['status_pesanan']))?></span>
          <small><?=date('d M Y · H:i',
strtotime($o['tanggal']))?></small>
          <span class="ec-order-number">#<?=e($o['nomor_pesanan'])?></span>
        </div>

        <div class="ec-order-body">
          <?php $first=$o['grouped_items'][0]??null;
?>
          <span class="ec-order-thumb">
            <?php if($first && !empty($first['foto'])): ?><img src="<?=APP_URL.'/'.e($first['foto'])?>" alt=""><?php else: ?><span><?=e($first['nama_produk'] ?? 'MAEM')?></span><?php endif;
?>
          </span>
          <div class="ec-order-info">
            <?php
 $names=[];
$totalQty=0;
foreach(($o['grouped_items'] ?? []) as $gi){
    $names[]=e($gi['nama_produk']).' × '.(int)$gi['qty'];
    $totalQty+=(int)$gi['qty'];
}
?>
            <strong><?=implode(', ',
$names)?></strong>
            <small><?=$totalQty?> item · <?=e(order_type_label((string)$o['jenis_pesanan']))?><?php if($o['jenis_pesanan']==='DINE_IN' && $o['nomor_meja']): ?> · Meja <?=e($o['nomor_meja'])?><?php endif;
?></small>
          </div>
          <span class="ec-order-total"><?=rupiah($o['total_bayar'])?></span>
        </div>

        <?php if(!$isDone && !empty($o['progress'])): ?>
          <div class="ec-tracker" aria-label="Progres pesanan">
            <?php foreach($o['progress'] as $step): ?>
              <div class="ec-track <?=e($step['state'])?>">
                <span class="ec-track-dot"><?= $step['state']==='done' ? icon('check') : '' ?></span>
                <span><?=e($step['label'])?></span>
              </div>
            <?php endforeach;
?>
          </div>
        <?php endif;
?>

        <div class="ec-order-foot">
          <small>
            <?= icon('chat') ?>
            <?php if($o['jenis_pesanan']==='DELIVERY'): ?>
              <?= $o['status_pesanan']==='SELESAI' ? 'Pesanan telah diterima. Terima kasih sudah maem!' : 'Link kurir akan dikirim owner melalui chat' ?>
            <?php else: ?>
              <?= $o['status_pesanan']==='SELESAI' ? 'Pesanan telah diterima. Terima kasih sudah maem!' : 'Ada pertanyaan? Chat warung dari sini.' ?>
            <?php endif;
?>
          </small>
          <div class="ec-order-actions">
            <?php if($o['status_pesanan']==='SELESAI'): ?>
              <?php if(!$existingReview): ?><a class="ec-btn ec-btn-sm ec-btn-light" href="<?=APP_URL?>/index.php?r=review/create&id=<?=$txId?>"><?= icon('star') ?> Beri ulasan</a><?php elseif($canEditReview): ?><a class="ec-btn ec-btn-sm ec-btn-light" href="<?=APP_URL?>/index.php?r=review/create&id=<?=$txId?>">Edit ulasan</a><?php endif;
?>
            <?php endif;
?>
            <a class="ec-btn ec-btn-sm ec-btn-light" href="<?=APP_URL?>/index.php?r=order/detail&id=<?=$txId?>">Detail</a>
            <a class="ec-btn ec-btn-sm ec-btn-dark" href="<?=APP_URL?>/index.php?r=chat/thread&order=<?=$txId?>"><?= icon('chat') ?> Buka chat pesanan</a>
          </div>
        </div>
      </article>
    <?php endforeach;
?>
  </div>
</div>

<?php foreach($orders as $o): $txId=(int)$o['id'];
if($o['status_pesanan']!=='SELESAI') continue;
$existingReview=$reviewsByTransaction[$txId]??null;
$canEditReview=$existingReview && time()<=strtotime($existingReview['created_at'])+7*86400;
if($existingReview && !$canEditReview) continue;
?>
<div class="review-popup" id="reviewModal-<?=$txId?>" data-review-modal hidden aria-hidden="true">
  <div class="review-popup-backdrop" data-review-close></div>
  <div class="review-popup-dialog" role="dialog" aria-modal="true" aria-labelledby="reviewTitle-<?=$txId?>">
    <button type="button" class="review-popup-close" data-review-close aria-label="Tutup"><?= icon('x') ?></button>
    <span class="review-popup-eyebrow">PENGALAMANMU</span>
    <h2 id="reviewTitle-<?=$txId?>"><?= $existingReview ? 'Edit ulasanmu.' : 'Bagaimana pengalamanmu?' ?></h2>
    <p class="review-popup-subtitle">Pesanan <?=e($o['nomor_pesanan'])?> · <?=date('d M Y',
strtotime($o['tanggal']))?></p>
    <form method="post" action="<?=APP_URL?>/index.php?r=review/create&id=<?=$txId?>" class="review-popup-form">
      <?=csrf_field()?>
      <input type="hidden" name="transaksi_id" value="<?=$txId?>">
      <div class="review-star-picker" role="radiogroup" aria-label="Nilai rating">
        <?php for($i=1;
$i<=5;
$i++): $selected=((int)($existingReview['rating']??0)===$i);
?>
          <button type="button" class="review-star-button<?=((int)($existingReview['rating']??0)>=$i?' is-selected':'')?>" data-rating-value="<?=$i?>" role="radio" aria-checked="<?=$selected?'true':'false'?>" aria-label="<?=$i?> bintang"><?= icon('star') ?></button>
        <?php endfor;
?>
        <input type="hidden" name="rating" value="<?=e($existingReview['rating']??'')?>" required>
      </div>
      <label for="review-comment-<?=$txId?>">Komentar <span>(opsional)</span></label>
      <textarea id="review-comment-<?=$txId?>" name="komentar" rows="4" maxlength="2000" placeholder="Ceritakan pengalamanmu di Warung Maem Jadoel..."><?=e($existingReview['komentar']??'')?></textarea>
      <button class="review-popup-submit" type="submit"><?= $existingReview ? 'Simpan Perubahan' : 'Kirim Ulasan' ?></button>
    </form>
  </div>
</div>
<?php endforeach;
?>
<script>
document.addEventListener('DOMContentLoaded',()=>{
  const tabs=[...document.querySelectorAll('[data-order-filter]')];
  const cards=[...document.querySelectorAll('[data-order-group]')];
  tabs.forEach(tab=>tab.addEventListener('click',()=>{
    tabs.forEach(t=>t.classList.remove('active')); tab.classList.add('active');
    const filter=tab.dataset.orderFilter;
    cards.forEach(card=>{ card.hidden = filter!=='all' && card.dataset.orderGroup!==filter; });
  }));
  const modals=[...document.querySelectorAll('[data-review-modal]')];
  const open=(modal)=>{if(!modal)return;modal.hidden=false;modal.setAttribute('aria-hidden','false');document.body.classList.add('modal-open');};
  const close=(modal)=>{if(!modal)return;modal.hidden=true;modal.setAttribute('aria-hidden','true');if(!modals.some(m=>!m.hidden))document.body.classList.remove('modal-open');};
  modals.forEach(modal=>{
    modal.querySelectorAll('[data-review-close]').forEach(btn=>btn.addEventListener('click',()=>close(modal)));
    const picker=modal.querySelector('.review-star-picker'); const hidden=modal.querySelector('input[name="rating"]');
    const stars=[...modal.querySelectorAll('.review-star-button')];
    const paint=(value)=>stars.forEach((star,i)=>{const active=i<value;star.classList.toggle('is-selected',active);star.setAttribute('aria-checked',i+1===value?'true':'false');});
    stars.forEach(star=>star.addEventListener('click',()=>{const value=Number(star.dataset.ratingValue);hidden.value=value;paint(value);}));
    picker?.addEventListener('keydown',e=>{if(!['ArrowLeft','ArrowRight','Home','End'].includes(e.key))return;e.preventDefault();let value=Number(hidden.value)||1;if(e.key==='ArrowRight')value=Math.min(5,value+1);if(e.key==='ArrowLeft')value=Math.max(1,value-1);if(e.key==='Home')value=1;if(e.key==='End')value=5;hidden.value=value;paint(value);});
    modal.querySelector('form')?.addEventListener('submit',e=>{if(!hidden.value){e.preventDefault();stars[0]?.focus();}});
  });
  const requested=<?=json_encode($openReviewId)?>;
  if(requested){open(document.getElementById('reviewModal-'+requested));}
  document.addEventListener('keydown',e=>{if(e.key==='Escape')modals.filter(m=>!m.hidden).forEach(close);});
});
</script>
<?php require BASE_PATH.'/view/partials/footer.php';
?>
