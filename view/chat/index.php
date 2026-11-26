<?php $title = 'Chat';
require BASE_PATH.'/view/partials/header.php';
?>
<div class="ec-page">
  <div class="ec-page-head">
    <div>
      <span class="ec-eyebrow">Pusat komunikasi pesanan</span>
      <h1>Pesan &amp; bantuan.</h1>
      <p>Setiap pesanan memiliki satu ruang chat agar percakapan dan detailnya tidak tercampur.</p>
    </div>
    <a class="ec-btn ec-btn-light" href="<?=APP_URL?>/index.php?r=order/history"><?= icon('receipt') ?> Lihat pesanan</a>
  </div>

  <div class="ec-chat-info">
    <?= icon('chat') ?>
    <div><strong>Chat terhubung ke pesanan, bukan ke kurir</strong>Owner akan mengirim tautan pelacakan dari aplikasi kurir di sini setelah pesanan mulai diantar.</div>
  </div>

  <section class="ec-panel">
    <div class="ec-chat-inbox-head" style="border-bottom:1px solid var(--c-line)">
      <h2>Inbox pesanan</h2>
      <small><?= $threads ? 'Pilih pesanan untuk membuka percakapan.' : 'Belum ada percakapan.' ?></small>
    </div>
    <?php if($threads): ?>
      <div class="ec-chat-search" style="max-width:360px">
        <?= icon('search') ?>
        <input type="search" id="chatInboxSearch" placeholder="Cari nomor atau menu…" autocomplete="off">
      </div>
    <?php endif;
?>
    <div class="ec-chat-list" id="chatInboxList">
      <?php if(!$threads): ?>
        <div class="ec-chat-empty">
          <?= icon('chat') ?>
          <div>Belum ada percakapan. Chat otomatis dibuat saat kamu membuat pesanan.</div>
          <a class="ec-btn ec-btn-dark ec-btn-sm" style="margin-top:14px" href="<?=APP_URL?>/index.php?r=order/menu">Mulai pesan</a>
        </div>
      <?php endif;
?>
      <?php foreach($threads as $t): $unread=(int)$t['unread'];
?>
        <a class="ec-chat-item" href="<?=APP_URL?>/index.php?r=chat/thread&id=<?=$t['id']?>"
           data-chat-search="<?= e(strtolower($t['nomor_pesanan'].' '.($t['last_message'] ?? ''))) ?>">
          <span class="ec-chat-item-thumb">
            <?php if(!empty($t['foto'])): ?><img src="<?=APP_URL.'/'.e($t['foto'])?>" alt=""><?php else: ?><?= icon('receipt') ?><?php endif;
?>
          </span>
          <span class="ec-chat-item-copy">
            <strong>Pesanan #<?=e($t['nomor_pesanan'])?></strong>
            <small><?=e($t['last_message'] ?: 'Ruang chat siap dipakai. Tanyakan apa saja soal pesanan ini.')?></small>
          </span>
          <span class="ec-chat-item-meta">
            <span class="ec-status-chip st-<?=strtolower(e($t['status_pesanan']))?>"><?=e(order_status_label((string)$t['status_pesanan']))?></span>
            <small><?=e(time_ago($t['last_at'] ?: $t['created_at']))?></small>
            <?php if($unread>0): ?><span class="ec-chat-unread"><?=$unread?></span><?php endif;
?>
          </span>
        </a>
      <?php endforeach;
?>
    </div>
  </section>
</div>
<script>
(function(){
  const input=document.getElementById('chatInboxSearch');
  if(!input)return;
  input.addEventListener('input',function(){
    const term=this.value.toLowerCase().trim();
    document.querySelectorAll('#chatInboxList .ec-chat-item').forEach(item=>{
      item.hidden=term!=='' && !(item.dataset.chatSearch||'').includes(term);
    });
  });
})();
</script>
<?php require BASE_PATH.'/view/partials/footer.php';
?>
