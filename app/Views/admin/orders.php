<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6" data-aos="fade-down">
        <h1 class="text-3xl font-black flex items-center gap-3">
            <span>📦</span> ALL ORDERS
        </h1>
        <a href="<?= base_url('admin/dashboard') ?>" class="neo-btn-white text-sm">← Dashboard</a>
    </div>

    <div class="neo-card mb-6" data-aos="fade-up">
        <form method="GET" class="flex items-center gap-3 flex-wrap">
            <div class="flex gap-1 flex-wrap">
                <a href="<?= base_url('admin/orders') ?>" class="neo-btn-white text-xs !px-3 !py-1.5 <?= !$selectedStatus ? 'bg-[#FFDE4D]' : '' ?>">All</a>
                <?php foreach ($statuses as $s): ?>
                <a href="<?= base_url('admin/orders?status=' . $s->payment_status . ($search ? '&search=' . urlencode($search) : '')) ?>"
                   class="neo-btn-white text-xs !px-3 !py-1.5 <?= $selectedStatus === $s->payment_status ? 'bg-[#FFDE4D]' : '' ?>">
                    <?= ucfirst($s->payment_status) ?> (<?= $s->count ?>)
                </a>
                <?php endforeach; ?>
            </div>
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" class="neo-input !py-2 text-sm" placeholder="Search order # or buyer..." value="<?= esc($search ?? '') ?>" />
            </div>
            <button type="submit" class="neo-btn-cyan text-sm">Search</button>
        </form>
    </div>

    <?php if (empty($orders)): ?>
    <div class="neo-card text-center py-12">
        <span class="text-6xl block mb-4">📭</span>
        <h2 class="text-2xl font-black">No Orders Found</h2>
        <p class="text-sm mt-2 opacity-60">No orders match your criteria.</p>
    </div>
    <?php else: ?>
    <div class="space-y-3">
        <?php foreach ($orders as $i => $o): ?>
        <a href="<?= base_url('admin/order/' . $o->order_number) ?>" class="flex items-center justify-between p-4 border-4 border-black bg-white hover:bg-[#FFDE4D] transition-colors no-underline text-black" style="box-shadow:4px 4px 0px 0px rgba(0,0,0,1);" data-aos="fade-up" data-aos-delay="<?= ($i % 10) * 30 ?>">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-3">
                    <span class="font-black font-mono text-sm"><?= esc($o->order_number) ?></span>
                    <span class="text-xs font-bold opacity-60"><?= date('d M Y H:i', strtotime($o->created_at)) ?></span>
                </div>
                <div class="flex items-center gap-3 mt-1">
                    <span class="text-sm font-bold"><?= esc($o->buyer_name ?? 'N/A') ?></span>
                    <span class="text-xs opacity-60"><?= esc($o->buyer_email ?? '') ?></span>
                </div>
                <?php if ($o->courier_name): ?>
                <span class="text-xs opacity-60"><?= esc($o->courier_name) ?> - <?= esc($o->courier_service) ?></span>
                <?php endif; ?>
            </div>
            <div class="text-right flex-shrink-0">
                <div class="font-black">Rp <?= number_format($o->gross_amount, 0, ',', '.') ?></div>
                <span class="neo-badge text-xs mt-1 inline-block <?= $o->payment_status === 'settlement' ? 'bg-[#22C55E]' : ($o->payment_status === 'pending' ? 'bg-[#FFDE4D]' : 'bg-[#EF4444] text-white') ?>">
                    <?= strtoupper($o->payment_status) ?>
                </span>
            </div>
        </a>
        <?php endforeach; ?>
    </div>

    <?php if ($total > $perPage): ?>
    <div class="flex items-center justify-between mt-6">
        <span class="text-sm font-bold"><?= $total ?> total orders</span>
        <div class="flex gap-2">
            <?php if ($page > 1): ?>
            <a href="<?= base_url('admin/orders?' . http_build_query(array_merge($_GET, ['page' => $page - 1]))) ?>" class="neo-btn-white text-sm">← Prev</a>
            <?php endif; ?>
            <?php if ($page * $perPage < $total): ?>
            <a href="<?= base_url('admin/orders?' . http_build_query(array_merge($_GET, ['page' => $page + 1]))) ?>" class="neo-btn-white text-sm">Next →</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
