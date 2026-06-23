<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="max-w-3xl mx-auto px-4 py-12">
    <div class="text-center mb-8" data-aos="zoom-in">
        <div id="status-icon" class="text-6xl block mb-4"><?= $order->payment_status === 'settlement' ? '✅' : '⏳' ?></div>
        <div id="status-card" class="<?= $order->payment_status === 'settlement' ? 'neo-card-green' : 'neo-card-yellow' ?>">
            <h1 class="text-3xl font-black">
                <?= $order->payment_status === 'settlement' ? 'PAYMENT CONFIRMED!' : 'ORDER PLACED!' ?>
            </h1>
            <p class="font-bold mt-2">
                <?= $order->payment_status === 'settlement'
                    ? 'Your payment has been confirmed. Thank you!'
                    : 'Your order is pending payment confirmation.' ?>
            </p>
            <?php if ($order->payment_status === 'pending'): ?>
            <div id="status-polling" class="mt-3 text-sm">
                <span>⏳ Waiting for payment confirmation...</span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="neo-card mb-6" data-aos="fade-up">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-black">ORDER DETAILS</h2>
            <span id="status-badge" class="neo-badge <?= $order->payment_status === 'settlement' ? 'bg-[#22C55E]' : 'bg-[#FFDE4D]' ?>">
                <?= strtoupper($order->payment_status) ?>
            </span>
        </div>

        <div class="space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="font-bold">Order Number</span>
                <span class="font-mono"><?= $order->order_number ?></span>
            </div>
            <div class="flex justify-between">
                <span class="font-bold">Buyer</span>
                <span><?= $order->buyer_name ?></span>
            </div>
            <div class="flex justify-between">
                <span class="font-bold">Date</span>
                <span><?= date('d M Y H:i', strtotime($order->created_at)) ?></span>
            </div>
            <?php if ($order->courier_name): ?>
            <div class="flex justify-between">
                <span class="font-bold">Shipping</span>
                <span><?= $order->courier_name ?> - <?= $order->courier_service ?></span>
            </div>
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
                    <td class="py-2 font-bold"><?= $item->name ?? 'Item' ?></td>
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

    <div class="text-center space-x-3">
        <a href="<?= base_url('/') ?>" class="neo-btn-yellow">Continue Shopping</a>
        <a href="<?= base_url('orders') ?>" class="neo-btn-white">My Orders</a>
    </div>
</div>

<?php if ($order->payment_status === 'pending'): ?>
<script>
(function pollStatus() {
    fetch('<?= base_url('order/detail/' . $order->order_number) ?>?ajax=1', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.status === 'settlement') {
            document.getElementById('status-icon').textContent = '✅';
            document.getElementById('status-card').className = 'neo-card-green';
            document.getElementById('status-card').querySelector('h1').textContent = 'PAYMENT CONFIRMED!';
            document.getElementById('status-card').querySelector('p').textContent = 'Your payment has been confirmed. Thank you!';
            document.getElementById('status-polling').innerHTML = '<span class="text-sm font-bold">✅ Payment confirmed!</span>';
            document.getElementById('status-badge').textContent = 'SETTLEMENT';
            document.getElementById('status-badge').className = 'neo-badge bg-[#22C55E]';
        } else if (data.status === 'expire' || data.status === 'deny') {
            document.getElementById('status-icon').textContent = '❌';
            document.getElementById('status-card').className = 'neo-card-orange';
            document.getElementById('status-card').querySelector('h1').textContent = 'PAYMENT FAILED';
            document.getElementById('status-polling').innerHTML = '<span class="text-sm font-bold text-white">Payment ' + data.status + ' — please try again.</span>';
            document.getElementById('status-badge').textContent = data.status.toUpperCase();
            document.getElementById('status-badge').className = 'neo-badge bg-[#EF4444] text-white';
        } else {
            setTimeout(pollStatus, 3000);
        }
    })
    .catch(function() {
        setTimeout(pollStatus, 5000);
    });
})();
</script>
<?php endif; ?>
<?= $this->endSection() ?>
