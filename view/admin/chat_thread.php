<?php $title = 'Chat · '.($thread['pelanggan'] ?? 'Pelanggan');
require BASE_PATH.'/view/partials/header.php';
?>
<div class="admin-content">
  <div class="page-heading">
    <div>
      <span class="eyebrow">PERCAKAPAN PESANAN</span>
      <h1 class="page-title mb-0"><?=e($thread['pelanggan'] ?? 'Pelanggan')?></h1>
      <p class="page-subtitle mb-0">Pesanan #<?=e($thread['nomor_pesanan'])?> · <?=rupiah($thread['total_bayar'])?>. Balasan dikirim langsung ke akun pelanggan.</p>
    </div>
    <a class="btn btn-secondary-soft" href="<?=APP_URL?>/index.php?r=admin/chat"><?= icon('chat') ?> Semua chat</a>
  </div>

  <div class="ec-chat-layout">
    <aside class="ec-chat-inbox">
      <div class="ec-chat-inbox-head"><h2>Inbox pesanan</h2><small><?=count($threads)?> percakapan</small></div>
      <div class="ec-chat-list">
        <?php foreach($threads as $t): $active=(int)$t['id']===(int)$thread['id'];
?>
          <a class="ec-chat-item <?=$active?'active':''?>" href="<?=APP_URL?>/index.php?r=admin/chat-thread&id=<?=$t['id']?>">
            <span class="ec-chat-item-thumb">
              <?php if(!empty($t['foto'])): ?><img src="<?=APP_URL.'/'.e($t['foto'])?>" alt=""><?php else: ?><?= icon('receipt') ?><?php endif;
?>
            </span>
            <span class="ec-chat-item-copy">
              <strong><?=e($t['pelanggan'] ?? 'Pelanggan')?></strong>
              <small>#<?=e($t['nomor_pesanan'])?> · <?=e($t['last_message'] ?: 'Belum ada pesan.')?></small>
            </span>
            <span class="ec-chat-item-meta">
              <small><?=e(time_ago($t['last_at'] ?: $t['created_at']))?></small>
              <?php if((int)$t['unread']>0): ?><span class="ec-chat-unread"><?=(int)$t['unread']?></span><?php endif;
?>
            </span>
          </a>
        <?php endforeach;
?>
      </div>
    </aside>

    <section class="ec-chat-panel">
      <div class="ec-chat-top">
        <div class="ec-chat-top-copy">
          <span class="ec-avatar"><?=e(strtoupper(substr((string)($thread['pelanggan'] ?? 'P'),
0,
1)))?></span>
          <div>
            <strong><?=e($thread['pelanggan'] ?? 'Pelanggan')?></strong>
            <small>#<?=e($thread['nomor_pesanan'])?> · <?=e(order_type_label((string)$thread['jenis_pesanan']))?></small>
          </div>
        </div>
        <span class="ec-status-chip st-<?=strtolower(e($thread['status_pesanan']))?>"><?=e(order_status_label((string)$thread['status_pesanan']))?></span>
      </div>

      <div class="ec-chat-context">
        <span class="ec-chat-item-thumb">
          <?php if(!empty($thread['foto'])): ?><img src="<?=APP_URL.'/'.e($thread['foto'])?>" alt=""><?php else: ?><?= icon('receipt') ?><?php endif;
?>
        </span>
        <div class="ec-chat-context-copy">
          <strong>Konteks percakapan</strong>
          <span><?=e($thread['ringkasan'] ?: 'Pesanan #'.$thread['nomor_pesanan'])?></span>
          <span>#<?=e($thread['nomor_pesanan'])?> · <?=rupiah($thread['total_bayar'])?></span>
        </div>
        <a class="ec-btn ec-btn-sm ec-btn-light" href="<?=APP_URL?>/index.php?r=admin/orders">Papan pesanan</a>
      </div>

      <div class="ec-chat-scroll" id="chatScroll" data-thread="<?=(int)$thread['id']?>" data-after="<?= $messages ? (int)end($messages)['id'] : 0 ?>">
        <?php if(!$messages): ?>
          <div class="ec-chat-empty" id="chatEmpty"><?= icon('chat') ?><div>Belum ada pesan dari pelanggan.</div></div>
        <?php endif;
?>
        <?php foreach($messages as $m): $me=$m['pengirim']==='ADMIN';
?>
          <div class="ec-msg <?=$me?'me':''?>" data-message-id="<?=(int)$m['id']?>">
            <div class="ec-msg-bubble"><?=nl2br(e($m['isi']))?></div>
            <small><?=date('d M H:i',
strtotime($m['created_at']))?><?= $me && !empty($m['dibaca_pelanggan_at']) ? ' · Dibaca' : '' ?></small>
          </div>
        <?php endforeach;
?>
      </div>

      <div class="ec-chat-quick">
        <button type="button" data-quick="Pesanan sedang kami siapkan, ya.">Pesanan sedang disiapkan</button>
        <button type="button" data-quick="Ongkir sudah dikonfirmasi. Silakan lanjutkan pembayaran QRIS.">Ongkir dikonfirmasi</button>
        <button type="button" data-quick="Pesanan sudah diambil kurir. Berikut tautan pelacakannya: ">Kirim tautan kurir</button>
      </div>

      <form class="ec-chat-form" method="post" action="<?=APP_URL?>/index.php?r=admin/chat-send" id="chatForm">
        <?=csrf_field()?>
        <input type="hidden" name="thread_id" value="<?=(int)$thread['id']?>">
        <input type="hidden" name="ajax" value="1">
        <input type="text" name="isi" id="chatInput" maxlength="2000" placeholder="Balas <?=e($thread['pelanggan'] ?? 'pelanggan')?>…" autocomplete="off" required>
        <button class="ec-chat-send" type="submit" aria-label="Kirim pesan"><?= icon('send') ?></button>
      </form>
      <div class="ec-chat-note">Pesan ini tersimpan di sistem dan terlihat oleh pelanggan pada halaman Chat pesanan.</div>
    </section>
  </div>
</div>
<script>
(function(){
  const scroll=document.getElementById('chatScroll');
  const form=document.getElementById('chatForm');
  const input=document.getElementById('chatInput');
  let after=parseInt(scroll.dataset.after||'0',10);
  let sending=false;
  const threadId=scroll.dataset.thread;
  function esc(v){return String(v??'').replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));}
  function addMessage(m,me,id){
    if(id && scroll.querySelector('[data-message-id=\"'+String(id)+'\"]')) return;
    document.getElementById('chatEmpty')?.remove();
    const wrap=document.createElement('div');
    wrap.className='ec-msg'+(me?' me':'');
    if(id) wrap.dataset.messageId=String(id);
    wrap.innerHTML='<div class="ec-msg-bubble">'+esc(m.isi).replace(/\n/g,'<br>')+'</div><small>'+esc(m.jam||'')+'</small>';
    scroll.appendChild(wrap);
    scroll.scrollTop=scroll.scrollHeight;
  }
  scroll.scrollTop=scroll.scrollHeight;
  async function poll(){
    try{
      const r=await fetch('<?=APP_URL?>/index.php?r=admin/chat-fetch&id='+threadId+'&after='+after,{cache:'no-store'});
      const d=await r.json();
      if(d.ok && Array.isArray(d.messages)){
        d.messages.forEach(m=>{ if(Number(m.id)>after){ addMessage({isi:m.isi,jam:(m.created_at||'').slice(11,16)}, m.pengirim==='ADMIN',m.id); after=Math.max(after,Number(m.id)); } });
      }
    }catch(e){}
  }
  setInterval(poll,4000);
  document.querySelectorAll('[data-quick]').forEach(b=>b.addEventListener('click',()=>{ input.value=b.dataset.quick; input.focus(); }));
  form.addEventListener('submit',async function(e){
    e.preventDefault();
    if(sending)return;
    const text=input.value.trim(); if(!text)return;
    sending=true;
    const body=new FormData(form); input.value='';
    try{
      const r=await fetch(form.action,{method:'POST',body,headers:{'Accept':'application/json'}});
      const d=await r.json();
      if(!d.ok) throw new Error(d.message||'Gagal mengirim.');
      after=Math.max(after,Number(d.id||0));
      addMessage({isi:text,jam:new Date().toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'})},true,d.id);
    }catch(err){ input.value=text; if(window.WMJ&&WMJ.toast) WMJ.toast(err.message||'Pesan gagal dikirim.','error'); } finally { sending=false; }
  });
})();
</script>
<?php require BASE_PATH.'/view/partials/footer.php';
?>
