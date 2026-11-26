<?php
$title = 'Beranda';
require BASE_PATH . '/view/partials/header.php';
?>
<div class="ec-page">
    <a class="ec-btn ec-btn-sm ec-btn-light ec-back-link" href="<?= APP_URL ?>/">
        <?= icon('arrow-right',
['style' => 'transform:rotate(180deg)']) ?>
        Kembali ke beranda
    </a>

    <div class="ec-menu-intro">
        <div>
            <span class="ec-eyebrow">Dapur sedang buka</span>
            <h1 class="ec-menu-title">Mau maem apa<br>hari ini?</h1>
            <p class="ec-menu-lead">
                Pilih menu favoritmu, kami siapkan segar setiap hari dan antar langsung ke tempatmu.
            </p>
        </div>

        <?php if ($promo): ?>
            <div class="ec-voucher-card">
                <span class="ec-voucher-kicker">VOUCHER PERTAMA</span>
                <strong>
                    <?= $promo['tipe'] === 'PERSEN'
? 'Potongan ' . (float) $promo['nilai'] . '%'
: 'Potongan ' . rupiah($promo['nilai']) ?>
                </strong>
                <small>
                    Pakai kode <b><?= e($promo['kode']) ?></b> saat checkout.
                    Minimum belanja <?= rupiah($promo['min_belanja']) ?>.
                </small>
            </div>
        <?php else: ?>
            <div class="ec-voucher-card">
                <span class="ec-voucher-kicker">PAKET HEMAT</span>
                <strong>Mulai dari <?= rupiah(39000) ?></strong>
                <small>Dua menu utama + dua es teh jadoel.</small>
            </div>
        <?php endif;
?>
    </div>

    <div class="ec-menu-layout">
        <div class="ec-menu-main">
            <div class="customer-category-row" role="tablist" aria-label="Kategori menu">
                <button class="category-pill active" type="button" data-category="all">Semua</button>
                <?php
 $categories = array_values(array_unique(array_filter(array_map(
fn($item) => (string) ($item['kategori'] ?? ''),
$menus
))));
?>
                <?php foreach ($categories as $category): ?>
                    <button
                        class="category-pill"
                        type="button"
                        data-category="<?= e(strtolower($category)) ?>"
                    ><?= e($category) ?></button>
                <?php endforeach;
?>
            </div>

            <div class="ec-menu-toolbar">
                <div>
                    <strong class="ec-menu-toolbar-title">Pilih menu</strong>
                    <div class="ec-menu-toolbar-meta">
                        <span id="visibleMenuCount"><?= count($menus) ?></span> menu tersedia hari ini
                    </div>
                </div>
                <label class="customer-search ec-menu-search" aria-label="Cari menu">
                    <?= icon('search') ?>
                    <input id="menuSearch" type="search" placeholder="Cari menu…" autocomplete="off">
                </label>
            </div>

            <div class="customer-product-grid" id="products">
                <?php foreach ($menus as $menu): ?>
                    <?php $stok = (int) ($menu['stok'] ?? 0);
?>
                    <article
                        class="customer-product-card"
                        data-name="<?= e(strtolower($menu['nama_produk'] . ' ' . $menu['kategori'] . ' ' . ($menu['deskripsi'] ?? ''))) ?>"
                        data-category="<?= e(strtolower($menu['kategori'])) ?>"
                    >
                        <button
                            class="customer-product-clickarea"
                            type="button"
                            data-product-id="<?= (int) $menu['id'] ?>"
                            aria-label="Lihat detail <?= e($menu['nama_produk']) ?>"
                        >
                            <div class="customer-product-image">
                                <?php if (!empty($menu['foto'])): ?>
                                    <img src="<?= APP_URL ?>/<?= e($menu['foto']) ?>" alt="<?= e($menu['nama_produk']) ?>">
                                <?php else: ?>
                                    <span><?= e($menu['kategori']) ?></span>
                                <?php endif;
?>
                                <span class="ec-product-flag ec-product-flag--<?= $stok > 0 ? ($stok <= 5 ? 'low' : 'ready') : 'empty' ?>">
                                    <?= $stok > 0 ? ($stok <= 5 ? 'Stok terbatas' : 'Tersedia') : 'Habis' ?>
                                </span>
                            </div>
                        </button>

                        <div class="customer-product-info">
                            <div class="customer-product-label"><?= e(strtoupper($menu['kategori'])) ?></div>
                            <h3><?= e($menu['nama_produk']) ?></h3>
                            <p class="ec-product-desc">
                                <?= e(mb_strimwidth(
(string) ($menu['deskripsi'] ?: 'Menu favorit Warung Maem Jadoel dengan rasa khas.'),
0,
90,
'…'
)) ?>
                            </p>
                            <div class="customer-product-bottom">
                                <strong><?= rupiah($menu['harga']) ?></strong>
                                <button
                                    class="customer-add-btn add"
                                    type="button"
                                    data-id="<?= (int) $menu['id'] ?>"
                                    aria-label="Tambah <?= e($menu['nama_produk']) ?>"
                                ><?= icon('plus') ?></button>
                            </div>
                        </div>
                    </article>
                <?php endforeach;
?>
            </div>

            <div class="customer-empty" id="emptyMenu" hidden>Menu yang kamu cari belum tersedia.</div>
        </div>

        <aside class="customer-cart-panel" id="cartPanel" aria-hidden="true" aria-label="Keranjang pesanan">
            <div class="cart-panel-head">
                <h2>Pesananmu</h2>
                <span id="cartBadge"><?= (int) $cartCount ?> item</span>
                <button class="cart-panel-close" type="button" id="cartCloseBtn" aria-label="Tutup keranjang">
                    <?= icon('x') ?>
                </button>
            </div>

            <div class="cart-panel-items" id="cartItems">
                <div class="cart-empty-state">Keranjangmu masih kosong.</div>
            </div>

            <div class="cart-panel-total">
                <div>
                    <span>Subtotal</span>
                    <strong id="cartSubtotal"><?= rupiah($cartSubtotal) ?></strong>
                </div>
                <div>
                    <span>Biaya layanan</span>
                    <strong>Rp 0</strong>
                </div>
                <div class="cart-total-line">
                    <span>Total</span>
                    <strong id="cartTotalSide"><?= rupiah($cartSubtotal) ?></strong>
                </div>
                <button class="customer-checkout-btn" type="button" id="checkoutBtn">Lanjut checkout</button>
                <div class="ec-cart-note">Harga sudah termasuk pajak</div>
            </div>
        </aside>
    </div>
</div>

<div class="cart-panel-backdrop" id="cartBackdrop" hidden></div>

<div class="customer-product-modal" id="productModal" hidden aria-hidden="true">
    <div class="customer-product-modal-backdrop" data-close-product-modal></div>
    <section
        class="customer-product-modal-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="productModalTitle"
    >
        <button class="customer-modal-close" type="button" data-close-product-modal aria-label="Tutup">
            <?= icon('x') ?>
        </button>
        <div class="customer-modal-media" id="productModalMedia"></div>
        <div class="customer-modal-content">
            <span class="customer-product-label" id="productModalCategory"></span>
            <h2 id="productModalTitle"></h2>
            <p id="productModalDescription"></p>
            <div class="customer-modal-price" id="productModalPrice"></div>
            <div class="customer-modal-options" id="productModalOptions"></div>
            <div class="customer-modal-actions">
                <div class="customer-modal-qty">
                    <label for="productModalQty">Jumlah</label>
                    <div>
                        <button type="button" id="qtyMinus" aria-label="Kurangi jumlah">
                            <?= icon('minus',
['class' => 'qty-ico']) ?>
                        </button>
                        <input id="productModalQty" type="number" min="1" value="1">
                        <button type="button" id="qtyPlus" aria-label="Tambah jumlah">
                            <?= icon('plus',
['class' => 'qty-ico']) ?>
                        </button>
                    </div>
                </div>
                <button class="customer-modal-add" type="button" id="modalAddBtn">Tambah ke keranjang</button>
            </div>
        </div>
    </section>
</div>

<script>
(function () {
    const csrf = '<?= e(csrf_token()) ?>';
    const appUrl = '<?= APP_URL ?>';
    const money = (value) => new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(Number(value) || 0);

    const icoX = '<svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>';
    const icoMinus = '<svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M5 12h14"/></svg>';
    const icoPlus = '<svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>';

    let cart = <?= json_encode(array_map(fn($item) => [
'id' => (int) $item['id'],
'menu_id' => (int) $item['menu_id'],
'name' => $item['nama_produk'],
'foto' => $item['foto'] ?? '',
'qty' => (int) $item['qty'],
'unit_price' => (float) $item['unit_price'],
'subtotal' => (float) $item['subtotal'],
'topping_id' => $item['topping_id'] !== null ? (int) $item['topping_id'] : null,
'topping_name' => $item['topping_name'] ?? '',
'level_pedas' => $item['level_pedas'] !== null ? (int) $item['level_pedas'] : null,
],
$cartItems),
JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    const productData = <?= json_encode(array_map(function ($menu) use ($menuToppings) {
    return [
    'id' => (int) $menu['id'],
    'name' => $menu['nama_produk'],
    'description' => $menu['deskripsi'] ?? '',
    'category' => $menu['kategori'],
    'price' => (float) $menu['harga'],
    'photo' => $menu['foto'] ?? '',
    'stock' => (int) ($menu['stok'] ?? 0),
    'topping' => (bool) $menu['punya_topping'],
    'level' => (bool) $menu['punya_level_pedas'],
    'toppings' => $menuToppings[(int) $menu['id']] ?? [],
    ];
},
$menus),
JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

    const modal = document.getElementById('productModal');
    let activeProduct = null;

    function esc(value) {
        return String(value ?? '').replace(/[&<>"']/g, (char) => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;',
        }[char]));
    }

    function mapItems(items) {
        return items.map((item) => ({
            id: Number(item.id),
            menu_id: Number(item.menu_id),
            name: item.nama_produk,
            foto: item.foto || '',
            qty: Number(item.qty),
            unit_price: Number(item.unit_price),
            subtotal: Number(item.subtotal),
            topping_id: item.topping_id ? Number(item.topping_id) : null,
            topping_name: item.topping_name || '',
            level_pedas: item.level_pedas !== null ? Number(item.level_pedas) : null,
        }));
    }

    function syncBadges(count) {
        ['headerCartBadge', 'fabCartBadge'].forEach((id) => {
            const el = document.getElementById(id);
            if (!el) {
                return;
            }
            el.textContent = count > 99 ? '99+' : count;
            el.hidden = count <= 0;
        });

        const badge = document.getElementById('cartBadge');
        if (badge) {
            badge.classList.remove('bounce');
            void badge.offsetWidth;
            badge.classList.add('bounce');
            badge.textContent = count + ' item';
        }
    }

    function renderCart() {
        const quantity = cart.reduce((total, item) => total + item.qty, 0);
        const subtotal = cart.reduce((total, item) => total + item.subtotal, 0);

        syncBadges(quantity);

        const subtotalEl = document.getElementById('cartSubtotal');
        const totalEl = document.getElementById('cartTotalSide');
        if (subtotalEl) {
            subtotalEl.textContent = money(subtotal);
        }
        if (totalEl) {
            totalEl.textContent = money(subtotal);
        }

        const panel = document.getElementById('cartPanel');
        if (panel) {
            panel.classList.toggle('has-items', cart.length > 0);
        }

        const box = document.getElementById('cartItems');
        if (!box) {
            return;
        }

        if (!cart.length) {
            box.innerHTML = '<div class="cart-empty-state">Keranjangmu masih kosong.</div>';
            return;
        }

        box.innerHTML = cart.map((item) => {
            const topping = item.topping_name ? ' · ' + esc(item.topping_name) : '';
            const level = item.level_pedas !== null ? ' · Level ' + item.level_pedas : '';

            return `
                <div class="cart-item">
                    <div class="ec-cart-item-copy">
                        <strong>${esc(item.name)}</strong>
                        <small>${money(item.unit_price)}${topping}${level}</small>
                        <div class="ec-qty">
                            <button type="button" data-cart-minus="${item.id}" aria-label="Kurangi">${icoMinus}</button>
                            <span class="ec-qty-value">${item.qty}</span>
                            <button type="button" data-cart-plus="${item.id}" aria-label="Tambah">${icoPlus}</button>
                        </div>
                    </div>
                    <div class="cart-item-right">
                        <b>${money(item.subtotal)}</b>
                        <button type="button" data-cart-remove="${item.id}" aria-label="Hapus ${esc(item.name)}">${icoX}</button>
                    </div>
                </div>
            `;
        }).join('');
    }

    async function post(url, data) {
        const body = new FormData();
        body.append('_csrf', csrf);
        body.append('ajax', '1');

        Object.entries(data).forEach(([key, value]) => {
            if (value !== null && value !== undefined) {
                body.append(key, value);
            }
        });

        const response = await fetch(url, {
            method: 'POST',
            body,
            headers: { Accept: 'application/json' },
        });
        const result = await response.json();

        if (!result.ok) {
            throw new Error(result.message || 'Terjadi kesalahan.');
        }

        cart = mapItems(result.items || []);
        renderCart();
        return result;
    }

    function toast(message, type) {
        if (window.WMJ && WMJ.toast) {
            WMJ.toast(message, type);
        }
    }

    function openProduct(id) {
        activeProduct = productData.find((product) => product.id === Number(id));
        if (!activeProduct) {
            return;
        }

        document.getElementById('productModalTitle').textContent = activeProduct.name;
        document.getElementById('productModalCategory').textContent = String(activeProduct.category || '').toUpperCase();
        document.getElementById('productModalDescription').textContent = activeProduct.description || '';
        document.getElementById('productModalDescription').classList.toggle('d-none', !activeProduct.description);
        document.getElementById('productModalPrice').textContent = money(activeProduct.price);
        document.getElementById('productModalQty').value = 1;

        const media = document.getElementById('productModalMedia');
        media.innerHTML = activeProduct.photo
            ? `<img src="${appUrl}/${esc(activeProduct.photo)}" alt="${esc(activeProduct.name)}">`
            : `<span>${esc(activeProduct.category)}</span>`;

        const options = document.getElementById('productModalOptions');
        let html = '';

        if (activeProduct.topping) {
            html += '<div class="modal-option-block"><label for="modalTopping">Topping</label><select id="modalTopping"><option value="" data-price="0">Tanpa topping</option>';
            activeProduct.toppings.forEach((topping) => {
                html += `<option value="${topping.id}" data-price="${topping.harga_tambahan}" data-name="${esc(topping.nama)}">${esc(topping.nama)} +${money(topping.harga_tambahan)}</option>`;
            });
            html += '</select></div>';
        }

        if (activeProduct.level) {
            html += '<div class="modal-option-block"><label for="modalLevel">Level pedas</label><select id="modalLevel">';
            for (let level = 0; level <= 5; level++) {
                html += `<option value="${level}">Level ${level}${level === 0 ? ' · Tidak pedas' : ''}</option>`;
            }
            html += '</select></div>';
        }

        options.innerHTML = html || '<div class="modal-no-options">Produk ini tidak memiliki pilihan tambahan.</div>';
        modal.hidden = false;
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');
    }

    function closeProduct() {
        modal.hidden = true;
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
        activeProduct = null;
    }

    async function addActiveProduct() {
        if (!activeProduct) {
            return;
        }

        const quantity = Math.max(1, parseInt(document.getElementById('productModalQty').value || 1, 10));
        const topping = document.getElementById('modalTopping');
        const level = document.getElementById('modalLevel');
        const button = document.getElementById('modalAddBtn');
        button.disabled = true;

        try {
            await post(appUrl + '/index.php?r=cart/add', {
                menu_id: activeProduct.id,
                qty: quantity,
                topping_id: topping && topping.value ? parseInt(topping.value, 10) : null,
                level_pedas: level ? parseInt(level.value, 10) : null,
            });

            const name = activeProduct.name;
            closeProduct();
            toast(quantity + '× ' + name + ' masuk keranjang');
        } catch (error) {
            toast(error.message, 'error');
        }

        button.disabled = false;
    }

    document.querySelectorAll('.customer-product-clickarea, .customer-add-btn').forEach((button) => {
        button.addEventListener('click', () => {
            const id = button.dataset.productId || button.dataset.id;
            const product = productData.find((item) => item.id === Number(id));

            if (product && !product.topping && !product.level && button.classList.contains('customer-add-btn')) {
                post(appUrl + '/index.php?r=cart/add', { menu_id: product.id, qty: 1 })
                    .then(() => toast('1× ' + product.name + ' masuk keranjang'))
                    .catch((error) => toast(error.message, 'error'));
                return;
            }

            openProduct(id);
        });
    });

    document.querySelectorAll('[data-close-product-modal]').forEach((button) => {
        button.addEventListener('click', closeProduct);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.hidden) {
            closeProduct();
        }
    });

    document.getElementById('qtyMinus').addEventListener('click', () => {
        const input = document.getElementById('productModalQty');
        input.value = Math.max(1, parseInt(input.value || 1, 10) - 1);
    });

    document.getElementById('qtyPlus').addEventListener('click', () => {
        const input = document.getElementById('productModalQty');
        input.value = Math.max(1, parseInt(input.value || 1, 10) + 1);
    });

    document.getElementById('modalAddBtn').addEventListener('click', addActiveProduct);

    document.getElementById('cartItems')?.addEventListener('click', async (event) => {
        const minus = event.target.closest('[data-cart-minus]');
        const plus = event.target.closest('[data-cart-plus]');
        const remove = event.target.closest('[data-cart-remove]');

        if (!minus && !plus && !remove) {
            return;
        }

        const trigger = minus || plus || remove;
        const id = Number(trigger.dataset.cartMinus || trigger.dataset.cartPlus || trigger.dataset.cartRemove);
        const item = cart.find((entry) => entry.id === id);

        if (!item) {
            return;
        }

        try {
            if (remove) {
                await post(appUrl + '/index.php?r=cart/remove', { item_id: id });
            } else {
                await post(appUrl + '/index.php?r=cart/update', {
                    item_id: id,
                    qty: minus ? item.qty - 1 : item.qty + 1,
                });
            }
        } catch (error) {
            toast(error.message, 'error');
        }
    });

    const cartPanel = document.getElementById('cartPanel');
    const cartBackdrop = document.getElementById('cartBackdrop');

    function setCartOpen(isOpen) {
        if (!cartPanel) {
            return;
        }

        cartPanel.classList.toggle('is-open', isOpen);
        cartPanel.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
        document.body.classList.toggle('cart-open', isOpen);

        if (cartBackdrop) {
            cartBackdrop.hidden = !isOpen;
            if (isOpen) {
                requestAnimationFrame(() => cartBackdrop.classList.add('show'));
            } else {
                cartBackdrop.classList.remove('show');
            }
        }
    }

    function openCart() {
        setCartOpen(true);
    }

    function closeCart() {
        setCartOpen(false);
    }

    document.querySelectorAll('[data-cart-open]').forEach((trigger) => {
        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            document.getElementById('customerMobileNav')?.classList.remove('open');
            openCart();
        });
    });

    document.getElementById('cartCloseBtn')?.addEventListener('click', closeCart);
    cartBackdrop?.addEventListener('click', closeCart);
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && cartPanel?.classList.contains('is-open')) {
            closeCart();
        }
    });

    if (window.location.hash === '#cart') {
        openCart();
    }

    document.getElementById('checkoutBtn')?.addEventListener('click', () => {
        if (!cart.length) {
            toast('Keranjang masih kosong.', 'error');
            return;
        }

        window.location.href = appUrl + '/index.php?r=order/checkout';
    });

    function filterMenu(term = '') {
        term = term.toLowerCase().trim();

        const activeCategory = document.querySelector('.category-pill.active')?.dataset.category || 'all';
        let visible = 0;

        document.querySelectorAll('.customer-product-card').forEach((card) => {
            const matchesCategory = activeCategory === 'all' || card.dataset.category === activeCategory;
            const matchesSearch = !term || card.dataset.name.includes(term);
            const show = matchesCategory && matchesSearch;

            card.hidden = !show;

            if (show) {
                visible++;
            }
        });

        document.getElementById('visibleMenuCount').textContent = visible;
        document.getElementById('emptyMenu').hidden = visible !== 0;
    }

    document.querySelectorAll('.category-pill').forEach((button) => {
        button.addEventListener('click', () => {
            document.querySelectorAll('.category-pill').forEach((pill) => pill.classList.remove('active'));
            button.classList.add('active');
            filterMenu(document.getElementById('menuSearch')?.value || '');
        });
    });

    document.getElementById('menuSearch')?.addEventListener('input', (event) => {
        filterMenu(event.target.value);
    });

    renderCart();
})();
</script>
<?php require BASE_PATH . '/view/partials/footer.php';
?>
