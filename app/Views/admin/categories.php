<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6" data-aos="fade-down">
        <h1 class="text-3xl font-black flex items-center gap-3">
            <span>📁</span> CATEGORIES
        </h1>
        <div class="flex gap-2">
            <a href="<?= base_url('admin/categories/create') ?>" class="neo-btn-cyan text-sm">+ Add Category</a>
            <a href="<?= base_url('admin/dashboard') ?>" class="neo-btn-white text-sm">← Dashboard</a>
        </div>
    </div>

    <?php if (empty($categories)): ?>
    <div class="neo-card text-center py-12">
        <span class="text-6xl block mb-4">📁</span>
        <h2 class="text-2xl font-black">No Categories</h2>
        <a href="<?= base_url('admin/categories/create') ?>" class="neo-btn-cyan mt-4 inline-block">+ Add Your First Category</a>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <?php foreach ($categories as $i => $c): ?>
        <div class="neo-card flex items-center justify-between" data-aos="fade-up" data-aos-delay="<?= ($i % 10) * 30 ?>">
            <div class="flex items-center gap-4">
                <span class="text-3xl"><?= $c->icon ?? '📦' ?></span>
                <div>
                    <h3 class="font-heading font-bold"><?= esc($c->name) ?></h3>
                    <p class="text-xs opacity-60 font-mono"><?= esc($c->slug) ?></p>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="<?= base_url('admin/categories/edit/' . $c->id) ?>" class="neo-btn-white text-xs !px-3 !py-1.5">✏️</a>
                <form action="<?= base_url('admin/categories/delete/' . $c->id) ?>" method="POST" onsubmit="return confirm('Delete category <?= esc($c->name, 'js') ?>?')">
                    <?= csrf_field() ?>
                    <button type="submit" class="neo-btn-white text-xs !px-3 !py-1.5 hover:!bg-[#EF4444] hover:!text-white">🗑️</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
