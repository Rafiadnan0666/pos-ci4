<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6" data-aos="fade-down">
        <h1 class="text-3xl font-black flex items-center gap-3">
            <span>📄</span> ORDER DETAIL
        </h1>
        <div class="flex gap-2">
            <a href="<?= base_url('admin/orders') ?>" class="neo-btn-white text-sm">← Orders</a>
            <a href="<?= base_url('admin/dashboard') ?>" class="neo-btn-white text-sm">Dashboard</a>
        </div>
    </div>

    <div class="neo-card mb-6" data-aos="fade-up">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-black font-mono"><?= $order->order_number ?></h2>
                <p class="text-xs opacity-60 mt-1"><?= date('d M Y H:i', strtotime($order->created_at)) ?></p>
            </div>
            <div class="flex items-center gap-3">
                <span class="neo-badge <?= $order->payment_status === 'settlement' ? 'bg-[#22C55E]' : ($order->payment_status === 'pending' ? 'bg-[#FFDE4D]' : 'bg-[#EF4444] text-white') ?>">
                    <?= strtoupper($order->payment_status) ?>
                </span>
                <select id="status-select" class="neo-input !py-1 !px-2 text-sm w-auto" data-order-id="<?= $order->id ?>">
                    <option value="pending" <?= $order->payment_status === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="settlement" <?= $order->payment_status === 'settlement' ? 'selected' : '' ?>>Settlement</option>
                    <option value="expire" <?= $order->payment_status === 'expire' ? 'selected' : '' ?>>Expire</option>
                    <option value="deny" <?= $order->payment_status === 'deny' ? 'selected' : '' ?>>Deny</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm neo-divider pt-4">
            <div>
                <p><span class="font-bold">Buyer:</span> <?= $order->buyer_name ?? 'N/A' ?></p>
                <p><span class="font-bold">Email:</span> <?= $order->buyer_email ?? 'N/A' ?></p>
            </div>
            <?php if ($order->shipping_address): ?>
            <div>
                <p><span class="font-bold">Address:</span> <?= $order->shipping_address ?></p>
                <p><span class="font-bold">Courier:</span> <?= $order->courier_name ?> - <?= $order->courier_service ?></p>
                <p><span class="font-bold">Shipping Cost:</span> Rp <?= number_format($order->shipping_cost, 0, ',', '.') ?></p>
                <?php if (!empty($order->tracking_number)): ?>
                <p><span class="font-bold">Tracking:</span> <?= esc($order->tracking_number) ?></p>
                <?php endif; ?>
                <?php if (!empty($order->tracking_url)): ?>
                <p><a href="<?= esc($order->tracking_url) ?>" target="_blank" class="neo-btn-cyan text-xs !px-2 !py-1 mt-1 inline-block">🔗 Track Shipment</a></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="neo-card" data-aos="fade-up">
        <h3 class="text-lg font-black mb-4">ORDER ITEMS</h3>
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
                    <td class="py-2 font-bold"><?= $item->name ?? 'Item #' . $item->product_id ?><?= !empty($item->size) ? ' <span class="text-xs opacity-60">(' . esc($item->size) . ')</span>' : '' ?></td>
                    <td class="py-2 text-center"><?= $item->quantity ?></td>
                    <td class="py-2 text-right">Rp <?= number_format($item->price, 0, ',', '.') ?></td>
                    <td class="py-2 text-right font-bold">Rp <?= number_format($item->subtotal, 0, ',', '.') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="space-y-1 mt-4 text-sm neo-divider pt-4">
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
</div>

<script>
document.getElementById('status-select')?.addEventListener('change', function() {
    if (!confirm('Change order status to ' + this.value + '?')) return;
    fetch('<?= base_url('admin/updateOrderStatus') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
        body: new URLSearchParams({ order_id: this.dataset.orderId, status: this.value })
    })
    .then(r => r.json())
    .then(d => { if (d.success) location.reload(); else alert(d.error); });
});
</script>
<?= $this->endSection() ?>
