<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl md:text-4xl font-black flex items-center gap-3">
                <span>📊</span> ADMIN DASHBOARD
            </h1>
            <p class="text-sm font-bold mt-1 opacity-60">Store overview & management</p>
        </div>
        <div class="flex gap-2">
            <a href="<?= base_url('admin/orders') ?>" class="neo-btn-yellow text-sm">All Orders</a>
            <a href="<?= base_url('admin/users') ?>" class="neo-btn-white text-sm">Users</a>
            <a href="<?= base_url('pos') ?>" class="neo-btn-white text-sm">POS</a>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="neo-card text-center" data-aos="zoom-in" data-aos-delay="0">
            <span class="text-4xl block mb-2">💰</span>
            <div class="text-2xl font-black">Rp <?= number_format($totalRevenue, 0, ',', '.') ?></div>
            <div class="text-xs font-bold opacity-60">Total Revenue</div>
        </div>
        <div class="neo-card text-center" data-aos="zoom-in" data-aos-delay="50">
            <span class="text-4xl block mb-2">📦</span>
            <div class="text-2xl font-black"><?= $totalOrders ?></div>
            <div class="text-xs font-bold opacity-60">Total Orders</div>
        </div>
        <div class="neo-card text-center" data-aos="zoom-in" data-aos-delay="100">
            <span class="text-4xl block mb-2">👥</span>
            <div class="text-2xl font-black"><?= $totalUsers ?></div>
            <div class="text-xs font-bold opacity-60">Total Users</div>
        </div>
        <div class="neo-card text-center" data-aos="zoom-in" data-aos-delay="150">
            <span class="text-4xl block mb-2">🏕️</span>
            <div class="text-2xl font-black"><?= $totalProducts ?></div>
            <div class="text-xs font-bold opacity-60">Total Products</div>
        </div>
    </div>

    <?php if (!empty($lowStock)): ?>
    <div class="neo-card-orange mb-8">
        <h2 class="text-lg font-black text-white flex items-center gap-2">
            <span>⚠️</span> LOW STOCK ALERT
        </h2>
        <div class="mt-3 space-y-2">
            <?php foreach ($lowStock as $p): ?>
            <div class="flex items-center gap-3 text-sm text-white">
                <span class="neo-badge bg-white text-black font-bold"><?= $p->stock ?> left</span>
                <span class="font-bold"><?= $p->name ?></span>
                <span class="opacity-70">(<?= $p->category ?>)</span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <div class="neo-card">
                <h2 class="text-lg font-black mb-4 flex items-center gap-2">
                    <span>📋</span> RECENT ORDERS
                </h2>
                <?php if (empty($recentOrders)): ?>
                <p class="text-sm opacity-60 text-center py-8">No orders yet</p>
                <?php else: ?>
                <div class="space-y-2">
                    <?php foreach ($recentOrders as $o): ?>
                    <a href="<?= base_url('admin/order/' . $o->order_number) ?>" class="flex items-center justify-between p-3 border-2 border-black hover:bg-[#FFDE4D] transition-colors no-underline text-black">
                        <div>
                            <span class="font-bold text-sm font-mono"><?= $o->order_number ?></span>
                            <span class="text-xs ml-2 opacity-60"><?= $o->buyer_name ?></span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="font-bold text-sm">Rp <?= number_format($o->gross_amount, 0, ',', '.') ?></span>
                            <span class="neo-badge text-xs <?= $o->payment_status === 'settlement' ? 'bg-[#22C55E] text-black' : ($o->payment_status === 'pending' ? 'bg-[#FFDE4D] text-black' : 'bg-[#EF4444] text-white') ?>">
                                <?= strtoupper($o->payment_status) ?>
                            </span>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <a href="<?= base_url('admin/orders') ?>" class="neo-btn-white w-full mt-4 text-sm">View All Orders</a>
            </div>
        </div>

        <div>
            <div class="neo-card-yellow">
                <h2 class="text-lg font-black mb-4 flex items-center gap-2">
                    <span>📊</span> ORDERS BY STATUS
                </h2>
                <?php
                $statusColors = [
                    'pending'    => ['bg' => '#FFDE4D', 'text' => '#000'],
                    'settlement' => ['bg' => '#22C55E', 'text' => '#000'],
                    'expire'     => ['bg' => '#EF4444', 'text' => '#fff'],
                    'deny'       => ['bg' => '#F97316', 'text' => '#fff'],
                ];
                ?>
                <?php if (empty($ordersByStatus)): ?>
                <p class="text-sm opacity-60 text-center py-4">No data</p>
                <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($ordersByStatus as $s): ?>
                    <?php $c = $statusColors[$s->payment_status] ?? ['bg' => '#9CA3AF', 'text' => '#fff']; ?>
                    <a href="<?= base_url('admin/orders?status=' . $s->payment_status) ?>" class="flex items-center justify-between p-3 border-2 border-black no-underline text-black" style="background:<?= $c['bg'] ?>;color:<?= $c['text'] ?>">
                        <span class="font-bold text-sm uppercase"><?= $s->payment_status ?></span>
                        <span class="font-black text-lg"><?= $s->count ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <div class="neo-card mt-4">
                <h2 class="text-lg font-black mb-3 flex items-center gap-2">
                    <span>⚡</span> QUICK ACTIONS
                </h2>
                <div class="space-y-2">
                    <a href="<?= base_url('admin/products') ?>" class="neo-btn-white w-full text-sm text-center">Manage Products</a>
                    <a href="<?= base_url('admin/categories') ?>" class="neo-btn-white w-full text-sm text-center">Manage Categories</a>
                    <a href="<?= base_url('admin/orders') ?>" class="neo-btn-white w-full text-sm text-center">Manage Orders</a>
                    <a href="<?= base_url('admin/users') ?>" class="neo-btn-white w-full text-sm text-center">Manage Users</a>
                    <a href="<?= base_url('pos') ?>" class="neo-btn-yellow w-full text-sm text-center">POS Terminal</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
