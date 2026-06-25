<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="max-w-3xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-black mb-6 flex items-center gap-3" data-aos="fade-down">
        <span>📄</span> ORDER DETAIL
    </h1>

    <div class="neo-card mb-6" data-aos="fade-up">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-black"><?= $order->order_number ?></h2>
            <span class="neo-badge <?= $order->payment_status === 'settlement' ? 'bg-neo-green' : 'bg-neo-yellow' ?>">
                <?= strtoupper($order->payment_status) ?>
            </span>
        </div>
        <div class="space-y-2 text-sm">
            <p><span class="font-bold">Name:</span> <?= $order->buyer_name ?? 'Customer' ?></p>
            <p><span class="font-bold">Date:</span> <?= date('d M Y H:i', strtotime($order->created_at)) ?></p>
            <?php if ($order->shipping_address): ?>
            <p><span class="font-bold">Address:</span> <?= $order->shipping_address ?></p>
            <p><span class="font-bold">Courier:</span> <?= $order->courier_name ?> - <?= $order->courier_service ?></p>
            <?php endif; ?>
        </div>

        <div class="neo-divider my-4"></div>

        <table class="w-full text-sm">
            <thead>
                <tr class="border-b-4 border-black">
                    <th class="text-left font-heading font-bold py-2">Item</th>
                    <th class="text-center font-heading font-bold py-2">Qty</th>
                    <th class="text-right font-heading font-bold py-2">Price</th>
                    <th class="text-right font-heading font-bold py-2">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr class="border-b-2 border-black">
                    <td class="py-2 font-bold"><?= $item->name ?? 'Item' ?><?= !empty($item->size) ? ' <span class="text-xs opacity-60">(' . esc($item->size) . ')</span>' : '' ?></td>
                    <td class="py-2 text-center"><?= $item->quantity ?></td>
                    <td class="py-2 text-right">Rp <?= number_format($item->price, 0, ',', '.') ?></td>
                    <td class="py-2 text-right font-bold">Rp <?= number_format($item->subtotal, 0, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="space-y-1 mt-4 text-sm">
            <div class="flex justify-between">
                <span>Subtotal</span>
                <span>Rp <?= number_format($order->gross_amount - $order->shipping_cost, 0, ',', '.') ?></span>
            </div>
            <?php if ($order->shipping_cost > 0): ?>
            <div class="flex justify-between">
                <span>Shipping</span>
                <span>Rp <?= number_format($order->shipping_cost, 0, ',', '.') ?></span>
            </div>
            <?php endif; ?>
            <div class="neo-divider"></div>
            <div class="flex justify-between font-heading font-black text-lg">
                <span>Total</span>
                <span>Rp <?= number_format($order->gross_amount, 0, ',', '.') ?></span>
            </div>
        </div>
    </div>

    <?php if ($order->payment_status === 'pending'): ?>
    <div class="text-center space-x-3">
        <button id="pay-now-btn" class="neo-btn-green" data-order="<?= $order->order_number ?>">
            💳 Pay Now (Rp <?= number_format($order->gross_amount, 0, ',', '.') ?>)
        </button>
        <a href="<?= base_url('/') ?>" class="neo-btn-yellow">Back to Store</a>
    </div>
    <?php else: ?>
    <a href="<?= base_url('/') ?>" class="neo-btn-yellow">Back to Store</a>
    <?php endif; ?>
</div>

<?php if ($order->payment_status === 'pending'): ?>
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="<?= trim(env('MIDTRANS_CLIENT_KEY', '')) ?>"></script>
<script>
document.getElementById('pay-now-btn')?.addEventListener('click', function() {
    const btn = this;
    btn.disabled = true;
    btn.textContent = 'Loading...';

    fetch('<?= base_url('payment/getPayToken') ?>', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'order_number=' + encodeURIComponent(this.dataset.order)
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success && data.snap_token) {
            window.snap.pay(data.snap_token, {
                onSuccess: function() {
                    location.reload();
                },
                onPending: function() {
                    alert('Payment is pending. Please complete your payment.');
                },
                onError: function(result) {
                    alert('Payment error: ' + (result.status_message || 'Unknown error'));
                    btn.disabled = false;
                    btn.textContent = '💳 Pay Now';
                },
                onClose: function() {
                    btn.disabled = false;
                    btn.textContent = '💳 Pay Now';
                }
            });
        } else {
            alert(data.error || 'Failed to load payment');
            btn.disabled = false;
            btn.textContent = '💳 Pay Now';
        }
    })
    .catch(function() {
        alert('Network error');
        btn.disabled = false;
        btn.textContent = '💳 Pay Now';
    });
});
</script>
<?php endif; ?>
<?= $this->endSection() ?>
