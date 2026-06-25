<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6" data-aos="fade-down">
        <h1 class="text-3xl font-black flex items-center gap-3">
            <span>🏕️</span> PRODUCTS
        </h1>
        <div class="flex gap-2">
            <a href="<?= base_url('admin/products/create') ?>" class="neo-btn-cyan text-sm">+ Add Product</a>
            <a href="<?= base_url('admin/dashboard') ?>" class="neo-btn-white text-sm">← Dashboard</a>
        </div>
    </div>

    <div class="neo-card mb-6" data-aos="fade-up">
        <form method="GET" class="flex items-center gap-3 flex-wrap">
            <div class="flex gap-1 flex-wrap">
                <a href="<?= base_url('admin/products') ?>" class="neo-btn-white text-xs !px-3 !py-1.5 <?= !$selectedCat ? 'bg-[#FFDE4D]' : '' ?>">All</a>
                <?php foreach ($categories as $c): ?>
                <a href="<?= base_url('admin/products?category=' . urlencode($c->name) . ($search ? '&search=' . urlencode($search) : '')) ?>"
                   class="neo-btn-white text-xs !px-3 !py-1.5 <?= $selectedCat === $c->name ? 'bg-[#FFDE4D]' : '' ?>">
                    <?= $c->name ?>
                </a>
                <?php endforeach; ?>
            </div>
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" class="neo-input !py-2 text-sm" placeholder="Search products..." value="<?= esc($search ?? '') ?>" />
            </div>
            <button type="submit" class="neo-btn-cyan text-sm">Search</button>
        </form>
    </div>

    <?php if (empty($grouped)): ?>
    <div class="neo-card text-center py-12">
        <span class="text-6xl block mb-4">🏕️</span>
        <h2 class="text-2xl font-black">No Products Found</h2>
        <a href="<?= base_url('admin/products/create') ?>" class="neo-btn-cyan mt-4 inline-block">+ Add Your First Product</a>
    </div>
    <?php else: ?>
        <?php foreach ($grouped as $cat => $items): ?>
        <div class="mb-8" data-aos="fade-up">
            <h2 class="text-xl font-black mb-4 flex items-center gap-2 bg-black text-white px-4 py-2 inline-block">
                <?= $cat ?> <span class="text-sm opacity-70">(<?= count($items) ?>)</span>
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($items as $p): ?>
                <div class="neo-card" data-aos="zoom-in" data-aos-delay="50">
                    <div class="flex gap-4">
                        <div class="w-20 h-20 border-4 border-black bg-[#FFDE4D] flex items-center justify-center font-black text-2xl flex-shrink-0 overflow-hidden">
                            <?php if ($p->image): ?>
                            <img src="<?= base_url($p->image) ?>" class="w-full h-full object-cover" alt="" />
                            <?php else: ?>
                            <?= strtoupper(substr($p->name, 0, 2)) ?>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-heading font-bold text-sm leading-tight"><?= $p->name ?></h3>
                            <p class="text-xs opacity-60 mt-1 font-mono"><?= $p->slug ?></p>
                            <div class="flex items-center gap-2 mt-2">
                                <span class="font-black">Rp <?= number_format($p->price, 0, ',', '.') ?></span>
                                <span class="neo-badge <?= $p->stock > 5 ? 'bg-[#22C55E]' : ($p->stock > 0 ? 'bg-[#FFDE4D]' : 'bg-[#EF4444] text-white') ?>">
                                    <?= $p->stock ?> left
                                </span>
                            </div>
                            <div class="flex items-center gap-2 mt-2 flex-wrap">
                                <span class="text-xs opacity-60"><?= $p->weight_grams ?>g</span>
                                <?php if (!empty($p->size)): ?><span class="text-xs opacity-60">📏 <?= esc($p->size) ?></span><?php endif; ?>
                                <?php if (!empty($p->color)): ?><span class="text-xs opacity-60">🎨 <?= esc($p->color) ?></span><?php endif; ?>
                                <?php if (!empty($p->material)): ?><span class="text-xs opacity-60">🧵 <?= esc($p->material) ?></span><?php endif; ?>
                                <?php if (!empty($p->brand)): ?><span class="text-xs opacity-60">🏷️ <?= esc($p->brand) ?></span><?php endif; ?>
                                <?php if ($p->image): ?>
                                <span class="text-xs opacity-60">🖼️</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-2 mt-4 pt-3 border-t-2 border-black flex-wrap">
                        <a href="<?= base_url('admin/products/edit/' . $p->id) ?>" class="neo-btn-white text-xs !px-3 !py-1.5 text-center">✏️ Edit</a>
                        <a href="<?= base_url('admin/products/sizes/' . $p->id) ?>" class="neo-btn-white text-xs !px-3 !py-1.5 text-center">📏 Sizes</a>
                        <form action="<?= base_url('admin/products/delete/' . $p->id) ?>" method="POST" onsubmit="return confirm('Delete <?= esc($p->name, 'js') ?>?')" class="inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="neo-btn-white text-xs !px-3 !py-1.5 hover:!bg-[#EF4444] hover:!text-white">🗑️ Delete</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
