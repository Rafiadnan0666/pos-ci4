<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-8" data-aos="fade-down">
        <h1 class="text-4xl font-black flex items-center gap-3">
            <span>🛒</span> SHOPPING CART
        </h1>
        <div class="flex gap-2">
            <a href="<?= base_url('/') ?>" class="neo-btn-white text-sm">Continue Shopping</a>
            <?php if (!empty($cart)): ?>
            <a href="<?= base_url('cart/clear') ?>" class="neo-btn-red text-sm">Clear All</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($cart)): ?>
    <div class="neo-card text-center py-16">
        <span class="text-7xl block mb-4">🛒</span>
        <h2 class="text-2xl font-black">Your Cart is Empty</h2>
        <p class="text-sm mt-2 mb-6">Time to gear up! Browse our catalog.</p>
        <a href="<?= base_url('/') ?>" class="neo-btn-yellow">Browse Catalog</a>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-4">
            <?php foreach ($cart as $id => $item): ?>
            <div class="neo-card flex items-start gap-4 cart-item" data-id="<?= $id ?>" data-aos="fade-up">
                <div class="bg-gray-100 border-2 border-black w-20 h-20 flex items-center justify-center text-2xl flex-shrink-0 overflow-hidden">
                    <?php if (!empty($item['image'])): ?>
                    <img src="<?= base_url($item['image']) ?>" alt="<?= $item['name'] ?>" class="w-full h-full object-cover" />
                    <?php else: ?>
                    📦
                    <?php endif; ?>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-heading font-bold text-base uppercase"><?= $item['name'] ?></h3>
                    <?php $cartAttrs = array_filter([$item['size'] ?? '', $item['color'] ?? '', $item['material'] ?? '']); ?>
                    <?php if (!empty($cartAttrs)): ?>
                    <p class="text-xs font-bold text-gray-500 mt-1"><?= implode(' | ', $cartAttrs) ?></p>
                    <?php endif; ?>
                    <p class="font-bold text-sm mt-1">Rp <?= number_format($item['price'], 0, ',', '.') ?></p>
                    <div class="flex items-center gap-3 mt-2">
                        <button class="neo-btn-white !px-2 !py-0.5 text-sm qty-dec" data-id="<?= $id ?>">-</button>
                        <span class="font-bold text-base qty"><?= $item['quantity'] ?></span>
                        <button class="neo-btn-white !px-2 !py-0.5 text-sm qty-inc" data-id="<?= $id ?>">+</button>
                        <span class="text-sm font-bold ml-4">Sub: Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></span>
                    </div>
                </div>
                <form action="<?= base_url('cart/remove') ?>" method="POST">
                    <input type="hidden" name="cart_key" value="<?= $id ?>" />
                    <button type="submit" class="neo-btn-white text-xs !px-2 !py-1">✕</button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="neo-card-yellow h-fit" data-aos="fade-left">
            <h3 class="text-lg font-black mb-4">ORDER SUMMARY</h3>
            <div class="space-y-3">
                <div class="flex justify-between text-sm">
                    <span>Subtotal</span>
                    <span class="font-bold" id="subtotal">Rp <?= number_format($subtotal, 0, ',', '.') ?></span>
                </div>
                <div class="flex justify-between text-sm">
                    <span>Total Weight</span>
                    <span class="font-bold" id="total-weight"><?= number_format($totalWeight, 0, ',', '.') ?> g</span>
                </div>
                <div class="neo-divider"></div>
                <div class="flex justify-between font-heading font-black text-lg">
                    <span>Total</span>
                    <span id="total">Rp <?= number_format($subtotal, 0, ',', '.') ?></span>
                </div>
                <a href="<?= base_url('checkout') ?>" class="neo-btn-green w-full text-center">
                    PROCEED TO CHECKOUT
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.querySelectorAll('.qty-inc, .qty-dec').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        const item = this.closest('.cart-item');
        const qtySpan = item.querySelector('.qty');
        let qty = parseInt(qtySpan.textContent);

        if (this.classList.contains('qty-inc')) {
            qty++;
        } else if (qty > 1) {
            qty--;
        } else {
            return;
        }

        fetch('<?= base_url('cart/update') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
            body: new URLSearchParams({ cart_key: id, quantity: qty })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    });
});
</script>
<?= $this->endSection() ?>
