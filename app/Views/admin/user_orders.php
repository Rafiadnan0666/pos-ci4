<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6" data-aos="fade-down">
        <h1 class="text-3xl font-black flex items-center gap-3">
            <span>👤</span> <?= esc($user->name) ?>'S ORDERS
        </h1>
        <div class="flex gap-2">
            <a href="<?= base_url('admin/users') ?>" class="neo-btn-white text-sm">← Users</a>
            <a href="<?= base_url('admin/dashboard') ?>" class="neo-btn-white text-sm">Dashboard</a>
        </div>
    </div>

    <div class="neo-card-yellow mb-6" data-aos="fade-up">
        <div class="flex items-center gap-4">
            <div class="bg-black text-white w-14 h-14 border-4 border-black flex items-center justify-center font-black text-2xl">
                <?= strtoupper(substr($user->name, 0, 1)) ?>
            </div>
            <div>
                <h2 class="font-black text-xl"><?= esc($user->name) ?></h2>
                <p class="text-sm font-bold"><?= esc($user->email) ?> · <?= strtoupper($user->role) ?> · <?= count($orders) ?> orders</p>
            </div>
        </div>
    </div>

    <?php if (empty($orders)): ?>
    <div class="neo-card text-center py-12">
        <span class="text-6xl block mb-4">📭</span>
        <h2 class="text-2xl font-black">No Orders Yet</h2>
        <p class="text-sm mt-2 opacity-60">This user hasn't placed any orders.</p>
    </div>
    <?php else: ?>
    <div class="space-y-3">
        <?php foreach ($orders as $i => $o): ?>
        <a href="<?= base_url('admin/order/' . $o->order_number) ?>" class="flex items-center justify-between p-4 border-4 border-black bg-white hover:bg-[#FFDE4D] transition-colors no-underline text-black" style="box-shadow:4px 4px 0px 0px rgba(0,0,0,1);" data-aos="fade-up" data-aos-delay="<?= ($i % 10) * 30 ?>">
            <div>
                <span class="font-black font-mono"><?= esc($o->order_number) ?></span>
                <span class="text-xs ml-3 opacity-60"><?= date('d M Y H:i', strtotime($o->created_at)) ?></span>
                <?php if ($o->courier_name): ?>
                <span class="text-xs block opacity-60"><?= esc($o->courier_name) ?> - <?= esc($o->courier_service) ?></span>
                <?php endif; ?>
            </div>
            <div class="text-right">
                <div class="font-black">Rp <?= number_format($o->gross_amount, 0, ',', '.') ?></div>
                <span class="neo-badge text-xs mt-1 <?= $o->payment_status === 'settlement' ? 'bg-[#22C55E]' : ($o->payment_status === 'pending' ? 'bg-[#FFDE4D]' : 'bg-[#EF4444] text-white') ?>">
                    <?= strtoupper($o->payment_status) ?>
                </span>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
