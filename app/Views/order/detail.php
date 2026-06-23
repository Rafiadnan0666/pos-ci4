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

    <a href="<?= base_url('/') ?>" class="neo-btn-yellow">Back to Store</a>
</div>
<?= $this->endSection() ?>
