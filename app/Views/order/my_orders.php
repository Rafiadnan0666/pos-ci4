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
        <a href="<?= base_url('order/detail/' . $o->order_number) ?>" class="neo-card flex items-center justify-between no-underline text-black" data-aos="fade-up" data-aos-delay="<?= ($i % 10) * 30 ?>">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-3">
                    <span class="font-black font-mono text-sm"><?= $o->order_number ?></span>
                    <span class="text-xs opacity-60"><?= date('d M Y', strtotime($o->created_at)) ?></span>
                </div>
                <?php if ($o->courier_name): ?>
                <span class="text-xs opacity-60"><?= $o->courier_name ?> - <?= $o->courier_service ?></span>
                <?php endif; ?>
            </div>
            <div class="text-right">
                <div class="font-black">Rp <?= number_format($o->gross_amount, 0, ',', '.') ?></div>
                <span class="neo-badge text-xs mt-1 inline-block <?= $o->payment_status === 'settlement' ? 'bg-[#22C55E]' : ($o->payment_status === 'pending' ? 'bg-[#FFDE4D]' : 'bg-[#EF4444] text-white') ?>">
                    <?= strtoupper($o->payment_status) ?>
                </span>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
