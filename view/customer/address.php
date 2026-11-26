<?php $title = 'Alamat Saya';
require BASE_PATH.'/view/partials/header.php';
?>
<div class="ec-page">
  <div class="ec-page-head">
    <div>
      <span class="ec-eyebrow">Buku alamat</span>
      <h1>Alamat saya.</h1>
      <p>Simpan alamat dan titik lokasi agar checkout delivery berikutnya lebih cepat. Ongkir dihitung dari jarak titik ini ke warung.</p>
    </div>
    <button class="ec-btn ec-btn-dark" type="button" data-address-open data-address-mode="new"><?= icon('plus') ?> Tambah alamat</button>
  </div>

  <div class="ec-address-grid">
    <?php foreach($addresses as $a): $isPrimary=(int)$a['is_utama']===1;
?>
      <article class="ec-address-card <?= $isPrimary ? 'is-primary' : '' ?>">
        <div class="ec-address-top">
          <span class="ec-address-icon"><?= icon($a['label'] === 'Kantor' ? 'store' : 'home') ?></span>
          <div class="ec-address-label">
            <strong><?=e($a['label'])?></strong>
            <small><?= $isPrimary ? 'Terpakai' : 'Alamat tersimpan' ?></small>
          </div>
        </div>
        <div class="ec-address-body">
          <strong><?=e($a['nama_penerima'])?> · <?=e($a['no_hp'])?></strong>
          <?=e($a['alamat'])?>
          <?php if(!empty($a['patokan'])): ?><br><em style="font-style:normal;color:var(--c-muted)">Patokan: <?=e($a['patokan'])?></em><?php endif;
?>
        </div>
        <div class="ec-address-map" aria-hidden="true">
          <?php if($a['latitude'] !== null && $a['longitude'] !== null): ?>
            <span class="ec-map-pin"><?= icon('map-pin') ?></span>
          <?php else: ?>
            <span style="position:absolute;inset:auto 10px 8px 10px;font-size:.62rem;color:var(--c-muted);text-align:center">Titik lokasi belum diatur</span>
          <?php endif;
?>
        </div>
        <div class="ec-address-actions">
          <?php if($isPrimary): ?>
            <span class="ec-btn ec-btn-sm" style="background:var(--c-success-bg);color:var(--c-success)"><?= icon('check') ?> Terpakai</span>
          <?php else: ?>
            <form method="post" action="<?=APP_URL?>/index.php?r=customer/primary-address">
              <?=csrf_field()?><input type="hidden" name="id" value="<?=$a['id']?>">
              <button class="ec-btn ec-btn-sm ec-btn-dark" type="submit">Pakai</button>
            </form>
          <?php endif;
?>
          <button class="ec-btn ec-btn-sm ec-btn-light" type="button" data-address-open data-address-mode="edit"
            data-id="<?=$a['id']?>" data-label="<?=e($a['label'])?>" data-nama="<?=e($a['nama_penerima'])?>"
            data-hp="<?=e($a['no_hp'])?>" data-alamat="<?=e($a['alamat'])?>" data-patokan="<?=e($a['patokan'] ?? '')?>"
            data-lat="<?=e((string)($a['latitude'] ?? ''))?>" data-lng="<?=e((string)($a['longitude'] ?? ''))?>"
            data-utama="<?=$isPrimary?'1':'0'?>">Ubah alamat</button>
          <form method="post" action="<?=APP_URL?>/index.php?r=customer/delete-address" onsubmit="return confirm('Hapus alamat ini dari daftar?')">
            <?=csrf_field()?><input type="hidden" name="id" value="<?=$a['id']?>">
            <button class="ec-btn ec-btn-sm ec-btn-danger" type="submit">Hapus</button>
          </form>
        </div>
      </article>
    <?php endforeach;
?>

    <button class="ec-address-add" type="button" data-address-open data-address-mode="new">
      <span class="ec-address-add-icon"><?= icon('plus') ?></span>
      <strong>Tambah alamat baru</strong>
      <small>Tambahkan rumah, kantor, atau alamat lainnya.</small>
    </button>
  </div>

  <div class="ec-chat-info" style="margin-top:22px">
    <?= icon('map-pin') ?>
    <div><strong>Jangkauan pengiriman</strong>Maksimal <?=e(rtrim(rtrim(number_format((float)($settings['batas_jarak_km'] ?? 10),
2,
'.',
''),
'0'),
'.'))?> km dari <?=e($settings['nama_toko'] ?? 'warung')?>. Ongkir dasar <?=rupiah($settings['ongkir_dasar'] ?? 10000)?> untuk <?=e(rtrim(rtrim(number_format((float)($settings['jarak_dasar_km'] ?? 1),
2,
'.',
''),
'0'),
'.'))?> km pertama, lalu <?=rupiah($settings['tarif_per_500m'] ?? 2000)?> per 500 m.</div>
  </div>
</div>

<div class="ec-modal" id="addressModal" hidden aria-hidden="true">
  <div class="ec-modal-backdrop" data-address-close></div>
  <div class="ec-modal-dialog" role="dialog" aria-modal="true" aria-labelledby="addressModalTitle">
    <button class="ec-modal-close" type="button" data-address-close aria-label="Tutup"><?= icon('x') ?></button>
    <span class="ec-eyebrow">Alamat pengiriman</span>
    <h2 id="addressModalTitle">Tambah alamat</h2>
    <form method="post" action="<?=APP_URL?>/index.php?r=customer/save-address" id="addressForm">
      <?=csrf_field()?>
      <input type="hidden" name="id" id="addressId" value="">
      <input type="hidden" name="latitude" id="addressLat" value="">
      <input type="hidden" name="longitude" id="addressLng" value="">
      <div class="ec-form-grid">
        <div class="ec-field">
          <label for="addressLabel">Label alamat</label>
          <select id="addressLabel" name="label">
            <option value="Rumah">Rumah</option>
            <option value="Kantor">Kantor</option>
            <option value="Lainnya">Lainnya</option>
          </select>
        </div>
        <div class="ec-field">
          <label for="addressNama">Nama penerima</label>
          <input id="addressNama" name="nama_penerima" required maxlength="120" placeholder="Nama lengkap">
        </div>
        <div class="ec-field">
          <label for="addressHp">Nomor HP</label>
          <input id="addressHp" name="no_hp" required maxlength="30" inputmode="tel" placeholder="08xx-xxxx-xxxx">
        </div>
        <div class="ec-field">
          <label for="addressPatokan">Patokan <span style="font-weight:400;color:var(--c-muted)">(opsional)</span></label>
          <input id="addressPatokan" name="patokan" maxlength="255" placeholder="Contoh: dekat masjid">
        </div>
        <div class="ec-field ec-field-wide">
          <label for="addressAlamat">Alamat lengkap</label>
          <textarea id="addressAlamat" name="alamat" rows="3" required maxlength="255" placeholder="Jalan, nomor rumah, RT/RW, kelurahan, kota"></textarea>
        </div>
        <div class="ec-field ec-field-wide">
          <label>Titik lokasi</label>
          <button class="ec-locate-btn" type="button" id="locateBtn"><?= icon('map-pin') ?> Gunakan lokasi saya</button>
          <div class="ec-map-preview"><span class="ec-map-pin" id="mapPin" hidden><?= icon('map-pin') ?></span></div>
          <div class="ec-coord" id="coordText">Titik lokasi belum diatur. Tekan “Gunakan lokasi saya” saat berada di alamat ini.</div>
        </div>
        <div class="ec-field ec-field-wide">
          <label class="check-card" style="display:flex;gap:10px;align-items:flex-start;border:1px solid var(--c-line);border-radius:12px;padding:12px">
            <input type="checkbox" name="is_utama" value="1" id="addressUtama" style="width:auto;margin-top:3px">
            <span><strong style="display:block;font-size:.82rem">Jadikan alamat utama</strong><small style="font-weight:400">Alamat utama otomatis terpilih saat checkout delivery.</small></span>
          </label>
        </div>
      </div>
      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px">
        <button class="ec-btn ec-btn-light" type="button" data-address-close>Batal</button>
        <button class="ec-btn ec-btn-dark" type="submit">Simpan alamat</button>
      </div>
    </form>
  </div>
</div>

<script>
(function(){
  const modal=document.getElementById('addressModal');
  const form=document.getElementById('addressForm');
  const latInput=document.getElementById('addressLat'), lngInput=document.getElementById('addressLng');
  const pin=document.getElementById('mapPin'), coord=document.getElementById('coordText');
  const title=document.getElementById('addressModalTitle');
  function setCoord(lat,lng){
    latInput.value=lat||''; lngInput.value=lng||'';
    if(lat&&lng){ pin.hidden=false; coord.textContent='Titik tersimpan: '+Number(lat).toFixed(6)+', '+Number(lng).toFixed(6); }
    else { pin.hidden=true; coord.textContent='Titik lokasi belum diatur. Tekan “Gunakan lokasi saya” saat berada di alamat ini.'; }
  }
  function openModal(mode,data){
    form.reset();
    document.getElementById('addressId').value=data?.id||'';
    document.getElementById('addressLabel').value=data?.label||'Rumah';
    document.getElementById('addressNama').value=data?.nama||'';
    document.getElementById('addressHp').value=data?.hp||'';
    document.getElementById('addressAlamat').value=data?.alamat||'';
    document.getElementById('addressPatokan').value=data?.patokan||'';
    document.getElementById('addressUtama').checked=data?.utama==='1';
    title.textContent=mode==='edit'?'Ubah alamat':'Tambah alamat';
    setCoord(data?.lat||'',data?.lng||'');
    modal.hidden=false; modal.setAttribute('aria-hidden','false'); document.body.classList.add('modal-open');
  }
  function closeModal(){ modal.hidden=true; modal.setAttribute('aria-hidden','true'); document.body.classList.remove('modal-open'); }
  document.querySelectorAll('[data-address-open]').forEach(btn=>btn.addEventListener('click',()=>{
    openModal(btn.dataset.addressMode,btn.dataset);
  }));
  document.querySelectorAll('[data-address-close]').forEach(btn=>btn.addEventListener('click',closeModal));
  document.addEventListener('keydown',e=>{ if(e.key==='Escape'&&!modal.hidden) closeModal(); });
  document.getElementById('locateBtn').addEventListener('click',function(){
    if(!navigator.geolocation){ coord.textContent='Browser tidak mendukung deteksi lokasi. Isi alamat secara manual.'; return; }
    this.disabled=true; const self=this; coord.textContent='Mendeteksi lokasi…';
    navigator.geolocation.getCurrentPosition(pos=>{
      setCoord(pos.coords.latitude.toFixed(7),pos.coords.longitude.toFixed(7));
      self.disabled=false;
    },()=>{
      coord.textContent='Lokasi tidak dapat diambil. Izinkan akses lokasi pada browser, lalu coba lagi.';
      self.disabled=false;
    },{enableHighAccuracy:true,timeout:12000,maximumAge:0});
  });
})();
</script>
<?php require BASE_PATH.'/view/partials/footer.php';
?>
