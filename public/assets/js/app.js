/* =========================================================
   WMJ UI — Modal & Collapse vanilla (pengganti Bootstrap JS)
   API: WMJ.modal.open('#id') / WMJ.modal.close(el) / WMJ.modal.toggle('#id')
   Data attribute:
     [data-modal-open="#id"]     → buka modal
     [data-modal-close]          → tutup modal terdekat (atau #id)
     [data-collapse-target="#x"] → toggle .show pada elemen target
   ========================================================= */
window.WMJ=(function(){
  let openStack=[];

  function backdrop(){return document.querySelector('.modal-backdrop');}
  function ensureBackdrop(){
    let bd=backdrop();
    if(!bd){bd=document.createElement('div');bd.className='modal-backdrop';document.body.appendChild(bd);}
    requestAnimationFrame(()=>bd.classList.add('show'));
    return bd;
  }
  function open(target){
    const el=typeof target==='string'?document.querySelector(target):target;
    if(!el||!el.classList.contains('modal'))return null;
    // Satu modal aktif dalam satu waktu: cegah form Login & Daftar tampil bertumpuk
    // ketika pengguna berpindah lewat tombol "Daftar sekarang"/"Masuk".
    [...openStack].filter(m=>m!==el).forEach(close);
    if(openStack.includes(el))return el;
    openStack.push(el);
    el.classList.add('show');
    el.setAttribute('aria-hidden','false');
    ensureBackdrop();
    document.body.classList.add('modal-open');
    const focusable=el.querySelector('input:not([type="hidden"]),select,textarea');
    if(focusable)setTimeout(()=>focusable.focus(),80);
    el.dispatchEvent(new CustomEvent('wmj:shown',{bubbles:true}));
    return el;
  }
  function close(target){
    const el=typeof target==='string'?document.querySelector(target):target;
    if(!el)return null;
    openStack=openStack.filter(m=>m!==el);
    el.classList.remove('show');
    el.setAttribute('aria-hidden','true');
    const bd=backdrop();
    if(bd){
      if(openStack.length===0)bd.classList.remove('show');
      setTimeout(()=>{if(openStack.length===0&&bd.isConnected)bd.remove();},240);
    }
    if(openStack.length===0)document.body.classList.remove('modal-open');
    el.dispatchEvent(new CustomEvent('wmj:hidden',{bubbles:true}));
    return el;
  }
  function toggle(target){
    const el=typeof target==='string'?document.querySelector(target):target;
    if(!el)return null;
    return openStack.includes(el)?close(el):open(el);
  }

  document.addEventListener('click',e=>{
    const opener=e.target.closest('[data-modal-open]');
    if(opener){e.preventDefault();open(opener.getAttribute('data-modal-open'));return;}
    const closer=e.target.closest('[data-modal-close]');
    if(closer){
      e.preventDefault();
      const sel=closer.getAttribute('data-modal-close');
      const el=sel?document.querySelector(sel):closer.closest('.modal');
      close(el);return;
    }
    const bd=e.target.classList&&e.target.classList.contains('modal-backdrop')?e.target:null;
    if(bd&&openStack.length)close(openStack[openStack.length-1]);
  });
  document.addEventListener('keydown',e=>{
    if(e.key==='Escape'&&openStack.length)close(openStack[openStack.length-1]);
  });
  return {modal:{open,close,toggle},collapse:{toggle:t=>{
    const el=typeof t==='string'?document.querySelector(t):t;
    if(el)el.classList.toggle('show');
  }}};
})();
const WMJ=window.WMJ;

/* Collapse: [data-collapse-target="#id"] */
document.addEventListener('click',e=>{
  const btn=e.target.closest('[data-collapse-target]');
  if(!btn)return;
  e.preventDefault();
  const el=document.querySelector(btn.getAttribute('data-collapse-target'));
  if(el)el.classList.toggle('show');
});

/* --- Form CRUD admin otomatis menjadi pop-up dengan tombol silang --- */
const WMJ_CRUD_X='<svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>';
function wmjEscapeHtml(value){
  return String(value??'').replace(/[&<>"']/g,char=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));
}

document.addEventListener('DOMContentLoaded',()=>{
 document.querySelectorAll('form[data-crud-toggle]').forEach((form,index)=>{
   if(form.dataset.crudReady==='1')return;
   form.dataset.crudReady='1';

   const label=form.dataset.crudLabel||'Form Data';
   const editing=!!form.querySelector('input[name="id"]:not([value=""])');
   const noTrigger=form.hasAttribute('data-crud-no-trigger');
   const modalId='crudModal-'+(form.id||index);
   const titleId=modalId+'-title';

   // Siapkan modal di body agar tetap bisa dibuka meski form berada di tab tersembunyi.
   const modal=document.createElement('div');
   modal.className='modal fade admin-crud-modal';
   modal.id=modalId;
   modal.setAttribute('aria-hidden','true');
   modal.setAttribute('aria-labelledby',titleId);
   modal.innerHTML=`
     <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
       <div class="modal-content">
         <div class="admin-modal-head">
           <div>
             <span class="admin-modal-kicker">Formulir admin</span>
             <h2 id="${titleId}">${wmjEscapeHtml(label)}</h2>
           </div>
           <button type="button" class="admin-modal-close" data-modal-close aria-label="Tutup formulir">${WMJ_CRUD_X}</button>
         </div>
         <div class="modal-body"></div>
       </div>
     </div>`;
   document.body.appendChild(modal);

   // Form yang berada di dalam elemen collapse: tombol Edit yang ada diubah menjadi pembuka modal.
   const collapse=form.closest('.collapse');
   let externalTrigger=false;
   if(collapse&&collapse.id){
     document.querySelectorAll('[data-collapse-target="#'+collapse.id+'"]').forEach(btn=>{
       btn.setAttribute('data-modal-open','#'+modalId);
       btn.removeAttribute('data-collapse-target');
       externalTrigger=true;
     });
   }

   if(!noTrigger&&!externalTrigger){
     const trigger=document.createElement('button');
     trigger.type='button';
     trigger.className='btn btn-secondary-soft admin-crud-toggle';
     trigger.setAttribute('data-modal-open','#'+modalId);
     trigger.innerHTML='<svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg><span>'+
       (editing?'Buka Form Edit':'Buka '+wmjEscapeHtml(label))+'</span>';
     form.parentNode.insertBefore(trigger,form);
   }

   form.hidden=false;
   form.classList.remove('d-none','p-3','p-4');
   modal.querySelector('.modal-body').appendChild(form);

   if(editing&&!noTrigger&&!externalTrigger){
     setTimeout(()=>{ if(window.WMJ) WMJ.modal.open('#'+modalId); },80);
   }
 });

 document.querySelectorAll('[data-confirm]').forEach(el=>el.addEventListener('click',e=>{
   if(!confirm(el.dataset.confirm)) e.preventDefault();
 }));
});
function rupiah(n){return new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',maximumFractionDigits:0}).format(n)}


// VE18 — Landing hero: reliable 5-second auto-slide + synchronized progress indicators
document.addEventListener('DOMContentLoaded',()=>{
  const hero=document.querySelector('.landing-hero-slides');
  if(!hero) return;
  const slides=[...hero.querySelectorAll('.landing-hero-slide')];
  const dots=[...hero.querySelectorAll('.landing-dots button')];
  if(slides.length<2) return;

  const DURATION=3500;
  let current=0;
  let timer=null;
  let progressToken=0;

  const restartProgress=()=>{
    progressToken++;
    dots.forEach(dot=>{
      dot.classList.remove('active');
      dot.style.setProperty('--ve18-progress-reset', progressToken);
    });
    requestAnimationFrame(()=>{
      if(dots[current]) dots[current].classList.add('active');
    });
  };

  const show=(index)=>{
    current=(index+slides.length)%slides.length;
    slides.forEach((slide,i)=>slide.classList.toggle('is-active',i===current));
    restartProgress();
  };

  const scheduleNext=()=>{
    clearTimeout(timer);
    timer=setTimeout(()=>{
      show(current+1);
      scheduleNext();
    },DURATION);
  };

  dots.forEach(dot=>dot.addEventListener('click',()=>{
    show(Number(dot.dataset.slideTo||0));
    scheduleNext();
  }));

  show(0);
  scheduleNext();
});

// VE19 — Public reviews: continuous infinite marquee (keeps scrolling sideways).
// Berjalan hanya bila total ulasan lebih lebar dari viewport (>3 kartu),
// loop mulus tanpa lompatan, dan berhenti sesaat saat kursor di atas kartu.
document.addEventListener('DOMContentLoaded',()=>{
  const carousel=document.querySelector('[data-review-carousel]');
  if(!carousel) return;
  const track=carousel.querySelector('.landing-review-track');
  const viewport=carousel.querySelector('.landing-review-viewport');
  if(!track||!viewport) return;
  if(window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

  const originals=[...track.children];
  const count=originals.length;
  if(!count) return;

  const SPEED=26;          // px per detik — kecepatan scroll yang tenang
  const getGap=()=>parseFloat(getComputedStyle(track).gap)||0;
  let setWidth=0;          // lebar satu set kartu asli + gap
  let offset=0;
  let running=false;
  let raf=null;
  let last=null;
  let paused=false;
  let resizeTimer=null;

  const cardW=()=>{
    const card=track.querySelector('article');
    return card?card.getBoundingClientRect().width+getGap():0;
  };

  // Duplikasi set kartu sampai total lebar >= viewport + satu set,
  // supaya pergeseran hingga `setWidth` selalu tertutup konten identik (loop mulus).
  const build=()=>{
    track.querySelectorAll('[data-review-clone]').forEach(el=>el.remove());
    offset=0;
    track.style.transform='translate3d(0,0,0)';
    setWidth=count*cardW();
    const needed=Math.ceil(((viewport.clientWidth+setWidth)/Math.max(setWidth,1))+1);
    const copies=Math.max(2,needed);
    for(let c=0;c<copies;c++){
      originals.forEach(card=>{
        const clone=card.cloneNode(true);
        clone.dataset.reviewClone='1';
        clone.setAttribute('aria-hidden','true');
        track.appendChild(clone);
      });
    }
  };

  const frame=(now)=>{
    if(!running)return;
    if(last===null)last=now;
    const dt=Math.min(64,now-last)/1000;
    last=now;
    if(!paused){
      offset+=SPEED*dt;
      if(offset>=setWidth)offset-=setWidth;
      track.style.transform=`translate3d(${-offset}px,0,0)`;
    }
    raf=requestAnimationFrame(frame);
  };

  const start=()=>{
    if(setWidth<=viewport.clientWidth){   // konten muat (<=3 kartu terlihat): statis
      running=false;
      track.style.transform='translate3d(0,0,0)';
      return;
    }
    if(running)return;
    running=true;
    last=null;
    raf=requestAnimationFrame(frame);
  };

  const stop=()=>{running=false;if(raf)cancelAnimationFrame(raf);raf=null;};

  carousel.addEventListener('mouseenter',()=>{paused=true;});
  carousel.addEventListener('mouseleave',()=>{paused=false;last=null;});
  document.addEventListener('visibilitychange',()=>{
    if(document.hidden)stop();else{running=false;start();}
  });

  build();
  start();

  window.addEventListener('resize',()=>{
    clearTimeout(resizeTimer);
    resizeTimer=setTimeout(()=>{stop();build();start();},180);
  });
});


// VE20 — Customer banner: Banner 2 + 3, 5-second auto-slide with synchronized progress
document.addEventListener('DOMContentLoaded',()=>{
  const wrap=document.querySelector('[data-customer-banner]');
  if(!wrap) return;
  const slides=[...wrap.querySelectorAll('.customer-banner-slide')];
  const dots=[...wrap.querySelectorAll('.customer-banner-dots button')];
  if(slides.length<2) return;
  const DURATION=3500;
  let current=0;
  let timer=null;
  let token=0;
  const resetProgress=()=>{
    token++;
    dots.forEach(dot=>{
      dot.classList.remove('active');
      dot.style.setProperty('--customer-progress-reset',token);
    });
    requestAnimationFrame(()=>{ if(dots[current]) dots[current].classList.add('active'); });
  };
  const show=(index)=>{
    current=(index+slides.length)%slides.length;
    slides.forEach((slide,i)=>slide.classList.toggle('is-active',i===current));
    resetProgress();
  };
  const schedule=()=>{
    clearTimeout(timer);
    timer=setTimeout(()=>{ show(current+1); schedule(); },DURATION);
  };
  dots.forEach(dot=>dot.addEventListener('click',()=>{ show(Number(dot.dataset.customerSlideTo||0)); schedule(); }));
  show(0);
  schedule();
});


/* =========================================================
   MOTION — animasi masuk saat scroll, scroll-spy nav,
   header shadow, count-up, toast, FAQ accordion.
   Progressive enhancement: elemen hanya disembunyikan saat
   JS aktif (html.wmj-motion) dan hormati prefers-reduced-motion.
   ========================================================= */
(function(){
  const root=document.documentElement;
  const reduce=window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if(reduce) return;              // tanpa animasi kalau user minta minimal motion
  root.classList.add('wmj-motion');

  /* ---- 1) Reveal saat scroll (fade + rise, stagger per grup) ---- */
  const revealSelectors=[
    '.landing-section .landing-narrow > *',
    '.landing-promo-card','.landing-menu-card',
    '.landing-about-grid > *','.landing-faq-grid > *',
    '.landing-review-carousel','.landing-location-card',
    '.customer-menu-heading','.customer-category-row','.customer-product-card',
    '.panel-card','.stat-card'
  ].join(',');
  const targets=[...document.querySelectorAll(revealSelectors)]
    .filter(el=>!el.closest('.modal')&&!el.closest('[hidden]'));
  targets.forEach(el=>{
    el.setAttribute('data-reveal','');
    const sibs=[...el.parentElement.children].filter(c=>c.hasAttribute('data-reveal'));
    el.style.setProperty('--rd',Math.min(sibs.indexOf(el),8)*70+'ms');
  });
  if('IntersectionObserver' in window&&targets.length){
    const io=new IntersectionObserver(entries=>{
      entries.forEach(en=>{
        if(en.isIntersecting){en.target.classList.add('is-inview');io.unobserve(en.target);}
      });
    },{threshold:.12,rootMargin:'0px 0px -6% 0px'});
    /* Pass sinkron: elemen yang sudah terlihat saat halaman dibuka langsung reveal
       (tidak menunggu callback IO — deterministik & aman untuk render pertama) */
    targets.forEach(el=>{
      const r=el.getBoundingClientRect();
      if(r.top<window.innerHeight*0.92&&r.bottom>0){
        el.classList.add('is-inview');
      }else{
        io.observe(el);
      }
    });
  }else{
    targets.forEach(el=>el.classList.add('is-inview'));
  }

  /* ---- 2) Scroll-spy: nav landing mengikuti section aktif ---- */
  const landingNav=[...document.querySelectorAll('.public-home-nav a[href*="#"]')];
  const spySections=landingNav
    .map(a=>{
      const hash=(a.getAttribute('href')||'').split('#')[1];
      return hash?document.getElementById(hash):null;
    })
    .filter(Boolean);
  if(spySections.length&&landingNav.length){
    const header=document.querySelector('.public-home-header');
    const offset=()=> (header?header.offsetHeight:0)+16;
    const setActive=()=>{
      const line=window.scrollY+offset()+window.innerHeight*0.22;
      let current=spySections[0];
      spySections.forEach(s=>{
        const top=s.getBoundingClientRect().top+window.scrollY;
        if(top<=line)current=s;
      });
      landingNav.forEach(a=>{
        const hash=(a.getAttribute('href')||'').split('#')[1]||'';
        const active=!!current&&hash===current.id;
        a.classList.toggle('active',active);
        a.classList.toggle('is-active',active);
        if(active)a.setAttribute('aria-current','true');
        else a.removeAttribute('aria-current');
      });
    };
    let spyTick=false;
    window.addEventListener('scroll',()=>{
      if(spyTick)return;spyTick=true;
      requestAnimationFrame(()=>{setActive();spyTick=false;});
    },{passive:true});
    window.addEventListener('resize',setActive);
    setActive();
  }

  /* ---- 2b) Nav pelanggan: indikator "Menu" mengikuti posisi grid produk ---- */
  const customerMenuLink=document.querySelector('.customer-layout-body .ec-nav[data-customer-nav] a[data-nav-section="products"]');
  const customerHomeLink=document.querySelector('.customer-layout-body .ec-nav[data-customer-nav] a[data-nav-section="home"]');
  const productSection=document.getElementById('products');
  if(customerMenuLink&&productSection){
    const header=document.querySelector('.ec-header');
    const setCustomerActive=()=>{
      const headerH=header?header.offsetHeight:0;
      const top=productSection.getBoundingClientRect().top;
      const menuActive=top<=headerH+120;
      customerMenuLink.classList.toggle('active',menuActive);
      if(customerHomeLink)customerHomeLink.classList.toggle('active',!menuActive);
    };
    let tick=false;
    window.addEventListener('scroll',()=>{
      if(tick)return;tick=true;
      requestAnimationFrame(()=>{setCustomerActive();tick=false;});
    },{passive:true});
    window.addEventListener('resize',setCustomerActive);
    setCustomerActive();
  }

  /* ---- 3) Header bayangan saat discroll ---- */
  const headers=[...document.querySelectorAll('.ec-header,.admin-topbar')];
  if(headers.length){
    let ticking=false;
    const onScroll=()=>{
      if(ticking)return;ticking=true;
      requestAnimationFrame(()=>{
        headers.forEach(h=>h.classList.toggle('is-scrolled',window.scrollY>8));
        ticking=false;
      });
    };
    window.addEventListener('scroll',onScroll,{passive:true});
    onScroll();
  }

  /* ---- 4) Count-up angka statistik (100%, 15 menit, 4,9) ---- */
  const stats=document.querySelectorAll('.landing-about-stats strong');
  const easeOut=t=>1-Math.pow(1-t,3);
  const animateCount=el=>{
    const m=el.textContent.match(/^([\d.,]+)(.*)$/);
    if(!m)return;
    const raw=m[1].replace(/\./g,'').replace(',', '.');
    const value=parseFloat(raw);
    if(isNaN(value))return;
    const decimals=(raw.split('.')[1]||'').length;
    const suffix=m[2];
    const DUR=1100, t0=performance.now();
    const tick=now=>{
      const p=Math.min(1,(now-t0)/DUR);
      const v=(value*easeOut(p)).toFixed(decimals).replace('.', ',');
      el.textContent=v+suffix;
      if(p<1)requestAnimationFrame(tick);
    };
    requestAnimationFrame(tick);
  };
  if(stats.length&&'IntersectionObserver' in window){
    const io2=new IntersectionObserver(entries=>{
      entries.forEach(en=>{
        if(en.isIntersecting){animateCount(en.target);io2.unobserve(en.target);}
      });
    },{threshold:.6});
    stats.forEach(s=>{
      if(s.getBoundingClientRect().top<window.innerHeight)animateCount(s);
      else io2.observe(s);
    });
  }else{
    stats.forEach(animateCount);
  }

  /* ---- 5) FAQ accordion: buka satu, tutup yang lain ---- */
  document.querySelectorAll('.landing-faq-list').forEach(list=>{
    list.querySelectorAll('details').forEach(d=>{
      d.addEventListener('toggle',()=>{
        if(d.open)list.querySelectorAll('details[open]').forEach(o=>{if(o!==d)o.open=false;});
      });
    });
  });

  /* ---- 6) Toast global: WMJ.toast('pesan') ---- */
  let toastBox=null;
  function toast(msg,type){
    if(!toastBox){
      toastBox=document.createElement('div');
      toastBox.className='wmj-toast-box';
      document.body.appendChild(toastBox);
    }
    const t=document.createElement('div');
    t.className='wmj-toast'+(type?' wmj-toast-'+type:'');
    t.setAttribute('role','status');
    t.innerHTML='<svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg><span></span>';
    t.querySelector('span').textContent=msg;
    toastBox.appendChild(t);
    requestAnimationFrame(()=>t.classList.add('show'));
    setTimeout(()=>{t.classList.remove('show');setTimeout(()=>t.remove(),320);},2400);
  }
  window.WMJ=window.WMJ||{};
  window.WMJ.toast=toast;
  window.WMJ.reveal=targets;
})();

// Admin mobile sidebar: backdrop click closes the drawer.
document.addEventListener('DOMContentLoaded',()=>{
 const sidebar=document.getElementById('adminSidebar');const toggle=document.querySelector('.admin-menu-toggle');if(!sidebar)return;
 let backdrop=document.getElementById('adminSidebarBackdrop');if(!backdrop){backdrop=document.createElement('button');backdrop.type='button';backdrop.id='adminSidebarBackdrop';backdrop.className='admin-sidebar-backdrop';backdrop.setAttribute('aria-label','Tutup menu admin');sidebar.insertAdjacentElement('afterend',backdrop);}
 const close=()=>sidebar.classList.remove('open');backdrop.addEventListener('click',close);
 sidebar.querySelectorAll('a').forEach(a=>a.addEventListener('click',close));
 toggle?.addEventListener('click',()=>{sidebar.classList.toggle('open');backdrop.classList.toggle('visible',sidebar.classList.contains('open'));});
 const sync=()=>backdrop.classList.toggle('visible',sidebar.classList.contains('open'));sidebar.addEventListener('transitionend',sync);
});

// Consistent 10-row pagination for admin data tables.
document.addEventListener('DOMContentLoaded',()=>{
 document.querySelectorAll('.admin-table').forEach(table=>{
  if(table.dataset.paginationReady==='1')return;table.dataset.paginationReady='1';
  const body=table.tBodies[0];if(!body)return;const rows=[...body.rows];const size=10;if(rows.length<=size)return;
  let page=0;const pages=Math.ceil(rows.length/size);const controls=document.createElement('div');controls.className='admin-table-pagination';
  const prev=document.createElement('button');prev.type='button';prev.className='btn btn-secondary-soft btn-sm';prev.textContent='‹ Sebelumnya';
  const label=document.createElement('span');label.className='pagination-label';
  const next=document.createElement('button');next.type='button';next.className='btn btn-secondary-soft btn-sm';next.textContent='Berikutnya ›';
  controls.append(prev,label,next);table.closest('.admin-table-wrap')?.insertAdjacentElement('afterend',controls);
  const render=()=>{rows.forEach((r,i)=>r.hidden=!(i>=page*size&&i<(page+1)*size));label.textContent=`Halaman ${page+1} dari ${pages} · ${rows.length} baris`;prev.disabled=page===0;next.disabled=page===pages-1;};
  prev.addEventListener('click',()=>{if(page>0){page--;render();}});next.addEventListener('click',()=>{if(page<pages-1){page++;render();}});render();
 });
});
