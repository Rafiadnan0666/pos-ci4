<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6" data-aos="fade-down">
        <h1 class="text-3xl font-black flex items-center gap-3">
            <span>👥</span> USERS
        </h1>
        <a href="<?= base_url('admin/dashboard') ?>" class="neo-btn-white text-sm">← Dashboard</a>
    </div>

    <?php if (empty($users)): ?>
    <div class="neo-card text-center py-12">
        <span class="text-6xl block mb-4">👥</span>
        <h2 class="text-2xl font-black">No Users</h2>
    </div>
    <?php else: ?>
    <div class="space-y-3">
        <?php foreach ($users as $i => $u): ?>
        <div class="neo-card flex items-center justify-between" data-aos="fade-up" data-aos-delay="<?= ($i % 10) * 30 ?>">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 border-4 border-black flex items-center justify-center font-black text-lg overflow-hidden <?= empty($u->avatar) ? 'bg-black text-white' : 'bg-gray-100' ?>">
                    <?php if (!empty($u->avatar)): ?>
                    <img src="<?= base_url($u->avatar) ?>" class="w-full h-full object-cover" alt="" />
                    <?php else: ?>
                    <?= strtoupper(substr($u->name, 0, 1)) ?>
                    <?php endif; ?>
                </div>
                <div>
                    <h3 class="font-heading font-bold"><?= $u->name ?></h3>
                    <p class="text-xs opacity-60"><?= $u->email ?></p>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <span class="neo-badge <?= $u->role === 'owner' ? 'bg-[#FFDE4D]' : 'bg-[#06B6D4] text-white' ?>">
                    <?= strtoupper($u->role) ?>
                </span>
                <span class="text-sm font-bold"><?= $u->order_count ?? 0 ?> orders</span>
                <div class="flex gap-1">
                    <a href="<?= base_url('admin/users/edit/' . $u->id) ?>" class="neo-btn-white text-xs !px-3 !py-1.5">✏️</a>
                    <a href="<?= base_url('admin/user-orders/' . $u->id) ?>" class="neo-btn-white text-xs !px-3 !py-1.5">Orders</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
