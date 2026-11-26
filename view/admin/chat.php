<?php $title = 'Chat Pelanggan';
require BASE_PATH.'/view/partials/header.php';
?>
<div class="admin-content">
  <div class="page-heading">
    <div>
      <span class="eyebrow">PUSAT KOMUNIKASI</span>
      <h1 class="page-title mb-0">Chat pelanggan</h1>
      <p class="page-subtitle mb-0">Satu ruang chat untuk setiap pesanan. Balas dari sini agar riwayat tetap rapi.</p>
    </div>
    <a class="btn btn-secondary-soft" href="<?=APP_URL?>/index.php?r=admin/orders"><?= icon('receipt') ?> Papan pesanan</a>
  </div>

  <section class="ec-panel">
    <div class="ec-chat-inbox-head" style="border-bottom:1px solid var(--c-line)">
      <h2>Inbox pesanan</h2>
      <small><?=count($threads)?> percakapan · pesan pelanggan yang belum dibaca ditandai</small>
    </div>
    <?php if($threads): ?>
      <div class="ec-chat-search" style="max-width:380px">
        <?= icon('search') ?>
        <input type="search" id="adminChatSearch" placeholder="Cari nama, nomor pesanan, atau pesan…" autocomplete="off">
      </div>
    <?php endif;
?>
    <div class="ec-chat-list" id="adminChatList">
      <?php if(!$threads): ?>
        <div class="ec-chat-empty"><?= icon('chat') ?><div>Belum ada percakapan pelanggan.</div></div>
      <?php endif;
?>
      <?php foreach($threads as $t): ?>
        <a class="ec-chat-item" href="<?=APP_URL?>/index.php?r=admin/chat-thread&id=<?=$t['id']?>"
           data-chat-search="<?= e(strtolower(($t['pelanggan'] ?? '').' '.$t['nomor_pesanan'].' '.($t['last_message'] ?? ''))) ?>">
          <span class="ec-chat-item-thumb">
            <?php if(!empty($t['foto'])): ?><img src="<?=APP_URL.'/'.e($t['foto'])?>" alt=""><?php else: ?><?= icon('receipt') ?><?php endif;
?>
          </span>
          <span class="ec-chat-item-copy">
            <strong><?=e($t['pelanggan'] ?? 'Pelanggan')?> · #<?=e($t['nomor_pesanan'])?></strong>
            <small><?=e($t['last_message'] ?: 'Belum ada pesan.')?></small>
          </span>
          <span class="ec-chat-item-meta">
            <span class="ec-status-chip st-<?=strtolower(e($t['status_pesanan']))?>"><?=e(order_status_label((string)$t['status_pesanan']))?></span>
            <small><?=e(time_ago($t['last_at'] ?: $t['created_at']))?></small>
            <?php if((int)$t['unread']>0): ?><span class="ec-chat-unread"><?=(int)$t['unread']?></span><?php endif;
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
  const input=document.getElementById('adminChatSearch');
  if(!input)return;
  input.addEventListener('input',function(){
    const term=this.value.toLowerCase().trim();
    document.querySelectorAll('#adminChatList .ec-chat-item').forEach(item=>{
      item.hidden=term!=='' && !(item.dataset.chatSearch||'').includes(term);
    });
  });
})();
</script>
<?php require BASE_PATH.'/view/partials/footer.php';
?>
