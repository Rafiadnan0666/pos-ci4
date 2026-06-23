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
                <?php foreach ($products as $p): ?>
                <div class="neo-card cursor-pointer hover:bg-neo-yellow transition-colors product-card"
                     data-id="<?= $p->id ?>"
                     data-stock="<?= $p->stock ?>">
                    <div class="bg-gray-100 border-2 border-black mb-2 h-28 flex items-center justify-center text-4xl overflow-hidden">
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
            <?php foreach ($cart as $id => $item): ?>
            <div class="neo-card !p-3 flex items-start gap-3 cart-item" data-id="<?= $id ?>">
                <div class="flex-1 min-w-0">
                    <h4 class="font-heading font-bold text-xs uppercase"><?= $item['name'] ?></h4>
                    <p class="text-xs font-bold mt-1">Rp <?= number_format($item['price'], 0, ',', '.') ?></p>
                    <div class="flex items-center gap-2 mt-2">
                        <button class="neo-btn-white !px-2 !py-0.5 text-xs qty-dec" data-id="<?= $id ?>">-</button>
                        <span class="font-bold text-sm qty"><?= $item['quantity'] ?></span>
                        <button class="neo-btn-white !px-2 !py-0.5 text-xs qty-inc" data-id="<?= $id ?>">+</button>
                    </div>
                </div>
                <div class="text-right">
                    <p class="font-bold text-sm">Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></p>
                    <button class="text-xs text-neo-red font-bold mt-1 remove-item" data-id="<?= $id ?>">Remove</button>
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

            <form id="pos-checkout-form" class="space-y-2">
                <input type="text" name="buyer_name" class="neo-input !py-2 text-sm" placeholder="Buyer name (optional)" />
                <input type="text" name="buyer_phone" class="neo-input !py-2 text-sm" placeholder="Phone (optional)" />
                <div class="flex gap-2">
                    <button type="submit" name="payment_method" value="cash" class="neo-btn-green flex-1 text-sm">
                        💵 CASH
                    </button>
                    <button type="button" name="payment_method" value="payment_link" class="neo-btn-cyan flex-1 text-sm" id="payment-link-btn">
                        🔗 QRIS
                    </button>
                </div>
            </form>

            <button id="clear-cart" class="neo-btn-white w-full text-sm">Clear Cart</button>
        </div>
    </div>
</div>

<script>
document.getElementById('pos-checkout-form')?.addEventListener('submit', function (e) {
    e.preventDefault();
    var btn = e.submitter;
    if (!btn || btn.value !== 'cash') return;
    this.action = '<?= base_url('pos/checkout') ?>';
    this.method = 'POST';
    var input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'payment_method';
    input.value = 'cash';
    this.appendChild(input);
    this.submit();
});

document.getElementById('payment-link-btn')?.addEventListener('click', function () {
    var form = document.getElementById('pos-checkout-form');
    var name = form.querySelector('[name="buyer_name"]').value || 'Walk-in Customer';
    var phone = form.querySelector('[name="buyer_phone"]').value || '-';

    var btn = this;
    btn.disabled = true;
    btn.textContent = 'Processing...';

    var formData = new FormData();
    formData.append('buyer_name', name);
    formData.append('buyer_phone', phone);
    formData.append('payment_method', 'payment_link');

    fetch('<?= base_url('pos/checkout') ?>', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (data.success && data.snap_token) {
            window.snap.pay(data.snap_token, {
                onSuccess: function () {
                    fetch('<?= base_url('payment/verifyStatus') ?>', {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'order_number=' + encodeURIComponent(data.order_number)
                    }).then(function () {
                        window.location.href = '<?= base_url('order/success') ?>' + '/' + data.order_number;
                    }).catch(function () {
                        window.location.href = '<?= base_url('order/success') ?>' + '/' + data.order_number;
                    });
                },
                onPending: function () {
                    window.location.href = '<?= base_url('order/success') ?>' + '/' + data.order_number;
                },
                onClose: function () {
                    btn.disabled = false;
                    btn.textContent = '🔗 QRIS';
                }
            });
        } else {
            alert(data.error || 'Payment failed');
            btn.disabled = false;
            btn.textContent = '🔗 QRIS';
        }
    })
    .catch(function () {
        alert('An error occurred');
        btn.disabled = false;
        btn.textContent = '🔗 QRIS';
    });
});
</script>
<?= $this->endSection() ?>
