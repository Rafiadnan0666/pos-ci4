<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6" data-aos="fade-down">
        <h1 class="text-3xl font-black flex items-center gap-3">
            <span>📦</span> MY ORDERS
        </h1>
        <a href="<?= base_url('/') ?>" class="neo-btn-white text-sm">← Shop</a>
    </div>

    <?php if (empty($orders)): ?>
    <div class="neo-card text-center py-12" data-aos="zoom-in">
        <span class="text-6xl block mb-4">📭</span>
        <h2 class="text-2xl font-black">No Orders Yet</h2>
        <p class="text-sm mt-2 opacity-60">Start shopping to see your orders here.</p>
        <a href="<?= base_url('/products') ?>" class="neo-btn-yellow mt-4 inline-block">Browse Products</a>
    </div>
    <?php else: ?>
    <div class="space-y-4">
        <?php foreach ($orders as $i => $o): ?>
        <div class="neo-card flex items-center justify-between" data-aos="fade-up" data-aos-delay="<?= ($i % 10) * 30 ?>">
            <a href="<?= base_url('order/detail/' . $o->order_number) ?>" class="flex-1 min-w-0 no-underline text-black">
                <div class="flex items-center gap-3">
                    <span class="font-black font-mono text-sm"><?= esc($o->order_number) ?></span>
                    <span class="text-xs opacity-60"><?= date('d M Y', strtotime($o->created_at)) ?></span>
                </div>
                <?php if ($o->courier_name): ?>
                <span class="text-xs opacity-60"><?= esc($o->courier_name) ?> - <?= esc($o->courier_service) ?></span>
                <?php endif; ?>
            </a>
            <div class="text-right flex items-center gap-3">
                <div>
                    <div class="font-black">Rp <?= number_format($o->gross_amount, 0, ',', '.') ?></div>
                    <span class="neo-badge text-xs mt-1 inline-block <?= $o->payment_status === 'settlement' ? 'bg-[#22C55E]' : ($o->payment_status === 'pending' ? 'bg-[#FFDE4D]' : 'bg-[#EF4444] text-white') ?>">
                        <?= strtoupper($o->payment_status) ?>
                    </span>
                </div>
                <?php if ($o->payment_status === 'pending'): ?>
                <button class="neo-btn-green !px-3 !py-1.5 text-xs pay-now-list" data-order="<?= esc($o->order_number) ?>">
                    💳 Pay
                </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php if (!empty($orders) && session()->get('role') !== 'owner'): ?>
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="<?= trim(env('MIDTRANS_CLIENT_KEY', '')) ?>"></script>
<?php endif; ?>
<?php if (!empty($orders)): ?>
<script>
document.querySelectorAll('.pay-now-list').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const orderNumber = this.dataset.order;
        const btnEl = this;
        btnEl.disabled = true;
        btnEl.textContent = '...';

        fetch('<?= base_url('payment/getPayToken') ?>', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'order_number=' + encodeURIComponent(orderNumber)
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success && data.snap_token) {
                window.snap.pay(data.snap_token, {
                    onSuccess: function() { location.reload(); },
                    onPending: function() { alert('Payment is pending.'); btnEl.disabled = false; btnEl.textContent = '💳 Pay'; },
                    onError: function() { alert('Payment error'); btnEl.disabled = false; btnEl.textContent = '💳 Pay'; },
                    onClose: function() { btnEl.disabled = false; btnEl.textContent = '💳 Pay'; }
                });
            } else {
                alert(data.error || 'Failed to load payment');
                btnEl.disabled = false;
                btnEl.textContent = '💳 Pay';
            }
        })
        .catch(function() { alert('Network error'); btnEl.disabled = false; btnEl.textContent = '💳 Pay'; });
    });
});
</script>
<?php endif; ?>
<?= $this->endSection() ?>
