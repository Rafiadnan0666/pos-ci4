<?= $this->extend('layouts/pos_layout') ?>

<?= $this->section('content') ?>
<div class="flex h-[calc(100vh-64px)]">
    <div class="flex-1 overflow-y-auto p-4">
        <?php if (!empty($lowStock) || !empty($outOfStock)): ?>
        <div class="neo-card-orange mb-4">
            <h2 class="text-lg font-black text-white flex items-center gap-2">
                <span>⚠️</span> INVENTORY ALERT
            </h2>
            <div class="mt-2 space-y-1">
                <?php foreach ($lowStock as $p): ?>
                <div class="flex items-center gap-2 text-sm text-white">
                    <span class="neo-badge bg-white text-black"><?= $p->stock ?> left</span>
                    <span><?= $p->name ?></span>
                </div>
                <?php endforeach; ?>
                <?php foreach ($outOfStock as $p): ?>
                <div class="flex items-center gap-2 text-sm text-white">
                    <span class="neo-badge bg-black text-white">OUT</span>
                    <span><?= $p->name ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="flex items-center gap-2 mb-4 flex-wrap">
            <a href="<?= base_url('pos') ?>" class="neo-btn-white text-xs !px-3 !py-1.5 <?= !$selectedCat ? 'bg-neo-yellow' : '' ?>">All</a>
            <?php foreach ($categories as $cat): ?>
            <a href="<?= base_url('pos?category=' . urlencode($cat->name)) ?>"
               class="neo-btn-white text-xs !px-3 !py-1.5 <?= $selectedCat === $cat->name ? 'bg-neo-yellow' : '' ?>">
                <?= $cat->name ?>
            </a>
            <?php endforeach; ?>
            <div class="ml-auto">
                <form action="<?= base_url('pos') ?>" method="GET" class="flex gap-2">
                    <input type="text" name="search" class="neo-input !py-1.5 !px-3 text-sm" placeholder="Search products..." value="<?= esc($search ?? '') ?>" />
                </form>
            </div>
        </div>

        <?php foreach ($grouped as $category => $products): ?>
        <div class="mb-6">
            <h2 class="text-xl font-black mb-3 bg-black text-white px-3 py-1 inline-block"><?= $category ?></h2>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
                <?php foreach ($products as $p):
                    $hasVariants = isset($variantsByProduct[$p->id]) && !empty($variantsByProduct[$p->id]);
                ?>
                <div class="neo-card cursor-pointer hover:bg-neo-yellow transition-colors product-card"
                     data-id="<?= $p->id ?>"
                     data-stock="<?= $p->stock ?>"
                     data-has-variants="<?= $hasVariants ? 'true' : 'false' ?>">
                    <div class="relative bg-gray-100 border-2 border-black mb-2 h-28 flex items-center justify-center text-4xl overflow-hidden">
                        <?php if ($hasVariants): ?>
                        <span class="absolute top-1 right-1 neo-badge bg-neo-cyan text-white text-[10px] !px-1.5 !py-0">VARIANTS</span>
                        <?php endif; ?>
                        <?php if ($p->image): ?>
                        <img src="<?= base_url($p->image) ?>" alt="<?= $p->name ?>" class="w-full h-full object-cover" />
                        <?php else: ?>
                        <?php
                        $icons = ['Tents' => '⛺', 'Packs' => '🎒', 'Apparel' => '🧥', 'Cooking' => '🍳'];
                        echo $icons[$p->category] ?? '📦';
                        ?>
                        <?php endif; ?>
                    </div>
                    <h3 class="font-heading font-bold text-sm uppercase leading-tight"><?= $p->name ?></h3>
                    <?php $pattrs = array_filter([$p->size ?? '', $p->color ?? '', $p->material ?? '']); ?>
                    <?php if (!empty($pattrs)): ?>
                    <p class="text-xs font-bold text-gray-500"><?= implode(' | ', $pattrs) ?></p>
                    <?php endif; ?>
                    <div class="flex items-center justify-between mt-1">
                        <span class="font-bold text-sm">Rp <?= number_format($p->price, 0, ',', '.') ?></span>
                        <span class="text-xs font-bold <?= $p->stock < 5 ? 'text-neo-red' : '' ?>">
                            Stok: <?= $p->stock ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="w-96 bg-white border-l-4 border-black flex flex-col">
        <div class="bg-black text-white px-4 py-3">
            <h2 class="font-heading font-bold text-lg flex items-center gap-2">
                <span>🧾</span> ACTIVE TICKET
            </h2>
        </div>

        <div id="cart-items" class="flex-1 overflow-y-auto p-4 space-y-3">
            <?php if (empty($cart)): ?>
            <p class="text-center text-gray-400 font-bold mt-8">No items yet</p>
            <?php else: ?>
            <?php foreach ($cart as $ckey => $item): ?>
            <div class="neo-card !p-3 flex items-start gap-3 cart-item" data-key="<?= $ckey ?>">
                <div class="flex-1 min-w-0">
                    <h4 class="font-heading font-bold text-xs uppercase"><?= $item['name'] ?></h4>
                    <?php if (!empty($item['variant_label'])): ?>
                    <p class="text-[10px] font-bold text-neo-cyan mt-0.5"><?= esc($item['variant_label']) ?></p>
                    <?php endif; ?>
                    <p class="text-xs font-bold mt-1">Rp <?= number_format($item['price'], 0, ',', '.') ?></p>
                    <div class="flex items-center gap-2 mt-2">
                        <button class="neo-btn-white !px-2 !py-0.5 text-xs qty-dec" data-key="<?= $ckey ?>">-</button>
                        <span class="font-bold text-sm qty"><?= $item['quantity'] ?></span>
                        <button class="neo-btn-white !px-2 !py-0.5 text-xs qty-inc" data-key="<?= $ckey ?>">+</button>
                    </div>
                </div>
                <div class="text-right">
                    <p class="font-bold text-sm">Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></p>
                    <button class="neo-btn-white text-xs !px-2 !py-0.5 mt-1 remove-item" data-key="<?= $ckey ?>">Remove</button>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="border-t-4 border-black p-4 space-y-3">
            <div class="flex justify-between font-heading font-bold text-lg">
                <span>TOTAL</span>
                <span id="cart-total">Rp <?= number_format($total ?? 0, 0, ',', '.') ?></span>
            </div>

            <form id="pos-checkout-form" method="POST" action="<?= base_url('pos/checkout') ?>" class="space-y-2">
                <?= csrf_field() ?>
                <input type="hidden" name="payment_method" value="cash">
                <input type="text" name="buyer_name" class="neo-input !py-2 text-sm" placeholder="Buyer name (optional)" />
                <input type="text" name="buyer_phone" class="neo-input !py-2 text-sm" placeholder="Phone (optional)" />
                <div class="flex gap-2">
                    <button type="submit" class="neo-btn-green flex-1 text-sm" id="cash-checkout-btn">
                        💵 CASH
                    </button>
                    <button type="button" class="neo-btn-cyan flex-1 text-sm" id="payment-link-btn">
                        🔗 QRIS
                    </button>
                </div>
            </form>

            <button id="clear-cart" class="neo-btn-white w-full text-sm">Clear Cart</button>
        </div>
    </div>
</div>

<!-- Variant Selection Modal -->
<div id="variant-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
    <div class="bg-white border-4 border-black shadow-neo w-full max-w-md mx-4 p-6 max-h-[90vh] overflow-y-auto">
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
                <div id="modal-product-image" class="w-16 h-16 bg-gray-100 border-2 border-black overflow-hidden flex-shrink-0 flex items-center justify-center text-2xl"></div>
                <div>
                    <h3 id="modal-product-name" class="font-heading font-bold text-sm uppercase"></h3>
                    <p id="modal-base-price" class="text-xs font-bold text-gray-500"></p>
                </div>
            </div>
            <button id="modal-close" class="neo-btn-white !px-2 !py-1 text-sm">✕</button>
        </div>

        <div id="modal-attr-container" class="space-y-3 mb-4"></div>

        <div id="modal-variant-info" class="neo-card-yellow !p-3 mb-4 hidden">
            <p id="modal-variant-stock" class="text-sm font-bold"></p>
            <p id="modal-variant-price" class="text-lg font-black"></p>
        </div>

        <div class="flex items-center gap-3 mb-4">
            <label class="font-bold text-xs">QTY:</label>
            <button id="modal-qty-dec" class="neo-btn-white !px-2 !py-1 text-sm">-</button>
            <span id="modal-qty" class="font-bold text-lg w-8 text-center">1</span>
            <button id="modal-qty-inc" class="neo-btn-white !px-2 !py-1 text-sm">+</button>
        </div>

        <div class="flex gap-2">
            <button id="modal-add-cart" class="neo-btn-green flex-1 text-sm">Add to Cart</button>
            <button id="modal-cancel" class="neo-btn-white flex-1 text-sm">Cancel</button>
        </div>
    </div>
</div>

<?php
$jsVariants = [];
foreach ($variantsByProduct as $pid => $vlist) {
    $jsVariants[$pid] = [
        'attrs'    => $variantAttrsByProduct[$pid] ?? [],
        'variants' => array_map(function($v) {
            return [
                'id'         => $v->id,
                'price'      => $v->price !== null ? (float) $v->price : null,
                'stock'      => (int) $v->stock,
                'attributes' => json_decode($v->attributes ?? '{}', true) ?? [],
            ];
        }, $vlist),
    ];
}
?>
<script>
window.posVariants = <?= json_encode($jsVariants) ?>;
</script>

<script>
document.getElementById('pos-checkout-form')?.addEventListener('submit', function (e) {
    var btn = e.submitter;
    if (btn && btn.id === 'cash-checkout-btn') {
        e.preventDefault();
        var form = this;
        showConfirm('Confirm cash payment for this order?').then(function (confirmed) {
            if (!confirmed) return;
            btn.disabled = true;
            btn.textContent = 'Processing...';
            form.submit();
        });
    }
});

document.getElementById('payment-link-btn')?.addEventListener('click', function () {
    var form = document.getElementById('pos-checkout-form');
    var name = form.querySelector('[name="buyer_name"]').value || 'Walk-in Customer';
    var phone = form.querySelector('[name="buyer_phone"]').value || '-';

    var btn = this;
    showConfirm('Send payment link for this order?').then(function (confirmed) {
        if (!confirmed) return;

        btn.disabled = true;
        btn.textContent = 'Processing...';

        var formData = new FormData();
        formData.append('buyer_name', name);
        formData.append('buyer_phone', phone);
        formData.append('payment_method', 'payment_link');

        var csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (csrfMeta && window.csrfTokenName) formData.append(window.csrfTokenName, csrfMeta.content);

        fetch('<?= base_url('pos/checkout') ?>', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(function (r) {
            return r.json().catch(function () { return null; });
        })
        .then(function (data) {
            if (data && data.success && data.snap_token) {
                window.snap.pay(data.snap_token, {
                    onSuccess: function () {
                        location.reload();
                    },
                    onPending: function () {
                        showAlert('Payment is pending.');
                        btn.disabled = false;
                        btn.textContent = '🔗 QRIS';
                    },
                    onClose: function () {
                        btn.disabled = false;
                        btn.textContent = '🔗 QRIS';
                    }
                });
            } else {
                showAlert((data && data.error) || 'Payment failed');
                btn.disabled = false;
                btn.textContent = '🔗 QRIS';
            }
        })
        .catch(function () {
            showAlert('Payment failed');
            btn.disabled = false;
            btn.textContent = '🔗 QRIS';
        });
    });
});

// Variant Modal
var modal = document.getElementById('variant-modal');
var modalProductName = document.getElementById('modal-product-name');
var modalProductImage = document.getElementById('modal-product-image');
var modalBasePrice = document.getElementById('modal-base-price');
var modalAttrContainer = document.getElementById('modal-attr-container');
var modalVariantInfo = document.getElementById('modal-variant-info');
var modalVariantStock = document.getElementById('modal-variant-stock');
var modalVariantPrice = document.getElementById('modal-variant-price');
var modalQty = document.getElementById('modal-qty');
var modalQtyDec = document.getElementById('modal-qty-dec');
var modalQtyInc = document.getElementById('modal-qty-inc');
var modalAddCart = document.getElementById('modal-add-cart');
var modalCancel = document.getElementById('modal-cancel');
var modalClose = document.getElementById('modal-close');

var currentModalProductId = null;
var currentModalVariants = [];
var currentModalAttrs = {};
var selectedAttributes = {};

function openVariantModal(productId) {
    if (!window.posVariants || !window.posVariants[productId]) return;

    var data = window.posVariants[productId];
    currentModalProductId = productId;
    currentModalVariants = data.variants || [];
    currentModalAttrs = data.attrs || {};
    selectedAttributes = {};

    var $card = document.querySelector('.product-card[data-id="' + productId + '"]');
    if ($card) {
        modalProductName.textContent = $card.querySelector('h3').textContent;
        var img = $card.querySelector('img');
        if (img) {
            modalProductImage.innerHTML = '<img src="' + img.src + '" class="w-full h-full object-cover" />';
        } else {
            modalProductImage.textContent = '📦';
        }
    }
    modalBasePrice.textContent = '';

    // Build attribute selectors
    modalAttrContainer.innerHTML = '';
    var attrNames = Object.keys(currentModalAttrs);
    if (attrNames.length === 0) return;

    attrNames.forEach(function(attrName) {
        var group = document.createElement('div');
        group.className = 'space-y-1';
        group.innerHTML = '<p class="font-heading font-bold text-xs uppercase">' + attrName + '</p><div class="flex flex-wrap gap-1.5 attr-group" data-attr-name="' + attrName + '"></div>';

        var groupDiv = group.querySelector('.attr-group');
        (currentModalAttrs[attrName] || []).forEach(function(val) {
            var btn = document.createElement('button');
            btn.className = 'neo-btn-white !px-2.5 !py-1 text-xs variant-attr-btn';
            btn.textContent = val;
            btn.dataset.attrName = attrName;
            btn.dataset.attrValue = val;
            btn.addEventListener('click', function() {
                var grp = this.closest('.attr-group');
                if (grp) grp.querySelectorAll('.variant-attr-btn').forEach(function(b) {
                    b.classList.remove('!bg-neo-yellow', 'selected');
                });
                this.classList.add('!bg-neo-yellow', 'selected');
                selectedAttributes[attrName] = val;
                updateModalVariantDisplay();
            });
            groupDiv.appendChild(btn);
        });

        modalAttrContainer.appendChild(group);
    });

    modalVariantInfo.classList.add('hidden');
    modalQty.textContent = '1';
    modal.classList.remove('hidden');
}

function updateModalVariantDisplay() {
    var match = findMatchingVariant();
    if (match) {
        var mStock = match.stock || 0;
        var vPrice = match.price || 0;
        modalVariantInfo.classList.remove('hidden');
        modalVariantStock.textContent = (mStock > 0 ? '✅ ' : '❌ ') + mStock + ' units available';
        modalVariantStock.className = 'text-sm font-bold ' + (mStock > 0 ? 'text-green-700' : 'text-red-600');
        modalVariantPrice.textContent = vPrice > 0 ? 'Rp ' + vPrice.toLocaleString('id-ID') : '';
        modalAddCart.disabled = mStock < 1;
        modalAddCart.className = mStock < 1 ? 'neo-btn flex-1 text-sm opacity-50 cursor-not-allowed' : 'neo-btn-green flex-1 text-sm';
        if (parseInt(modalQty.textContent) > mStock) modalQty.textContent = mStock;
    } else {
        modalVariantInfo.classList.add('hidden');
        modalAddCart.disabled = false;
        modalAddCart.className = 'neo-btn-green flex-1 text-sm';
    }
}

function findMatchingVariant() {
    var selectedCount = Object.keys(selectedAttributes).length;
    var totalAttrs = Object.keys(currentModalAttrs).length;
    if (selectedCount < totalAttrs) return null;

    for (var i = 0; i < currentModalVariants.length; i++) {
        var v = currentModalVariants[i];
        var vAttrs = v.attributes || {};
        var match = true;
        for (var key in selectedAttributes) {
            if (vAttrs[key] !== selectedAttributes[key]) { match = false; break; }
        }
        if (match) return v;
    }
    return null;
}

function closeModal() {
    modal.classList.add('hidden');
    currentModalProductId = null;
    currentModalVariants = [];
    currentModalAttrs = {};
    selectedAttributes = {};
}

modalCancel.addEventListener('click', closeModal);
modalClose.addEventListener('click', closeModal);
modal.addEventListener('click', function(e) { if (e.target === modal) closeModal(); });

modalQtyDec.addEventListener('click', function() {
    var q = parseInt(modalQty.textContent);
    if (q > 1) modalQty.textContent = q - 1;
});

modalQtyInc.addEventListener('click', function() {
    var q = parseInt(modalQty.textContent);
    var match = findMatchingVariant();
    var max = match ? (match.stock || 0) : 99;
    if (q < max) modalQty.textContent = q + 1;
});

modalAddCart.addEventListener('click', function() {
    if (this.disabled) return;
    var match = findMatchingVariant();
    if (!match) { showAlert('Please select all attributes'); return; }

    var qty = parseInt(modalQty.textContent);
    var mStock = match.stock || 0;
    if (qty < 1 || qty > mStock) { showAlert('Invalid quantity'); return; }

    var formData = new FormData();
    formData.append('product_id', currentModalProductId);
    formData.append('variant_id', match.id);
    formData.append('quantity', qty);

    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    if (csrfMeta && window.csrfTokenName) formData.append(window.csrfTokenName, csrfMeta.content);

    fetch('<?= base_url('pos/addToCart') ?>', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(function(r) {
        return r.json().catch(function() { return null; });
    })
    .then(function(data) {
        if (data && data.success) {
            closeModal();
            location.reload();
        } else {
            showAlert((data && data.error) || 'Failed to add item');
        }
    })
    .catch(function() { showAlert('Failed to add item'); });
});
</script>
<?= $this->endSection() ?>
