<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<div class="max-w-2xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6" data-aos="fade-down">
        <h1 class="text-3xl font-black flex items-center gap-3">
            <span><?= $category ? '✏️' : '➕' ?></span> <?= $category ? 'EDIT CATEGORY' : 'ADD CATEGORY' ?>
        </h1>
        <a href="<?= base_url('admin/categories') ?>" class="neo-btn-white text-sm">← Categories</a>
    </div>

    <?php if (session('errors')): ?>
    <div class="neo-card bg-[#FEE2E2] mb-6">
        <h3 class="font-black text-sm">⚠️ ERRORS</h3>
        <ul class="mt-2 text-sm space-y-1">
            <?php foreach (session('errors') as $e): ?><li>• <?= esc($e) ?></li><?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <div class="neo-card" data-aos="fade-up">
        <form method="POST">
            <?= csrf_field() ?>
            <div class="space-y-4">
                <div>
                    <label class="block font-bold text-sm mb-1">Category Name *</label>
                    <input type="text" name="name" class="neo-input" value="<?= esc(old('name', $category->name ?? '')) ?>" required />
                </div>
                <div>
                    <label class="block font-bold text-sm mb-1">Icon (emoji)</label>
                    <input type="text" name="icon" class="neo-input" placeholder="e.g. ⛺ 🎒 🧥 🍳" value="<?= esc(old('icon', $category->icon ?? '')) ?>" />
                    <p class="text-xs opacity-60 mt-1">Single emoji character, e.g. ⛺ for Tents</p>
                </div>
            </div>
            <div class="mt-6 flex gap-3">
                <button type="submit" class="neo-btn-cyan flex-1"><?= $category ? '✏️ Update' : '➕ Create' ?></button>
                <a href="<?= base_url('admin/categories') ?>" class="neo-btn-white">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
