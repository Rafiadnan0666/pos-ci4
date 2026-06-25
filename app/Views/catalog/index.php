<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-8" data-aos="fade-down">
        <div>
            <h1 class="text-4xl md:text-5xl font-black tracking-tighter">GEAR UP</h1>
            <p class="text-sm font-bold mt-1">Premium outdoor equipment for every adventure</p>
        </div>
        <div class="flex gap-2 flex-wrap">
            <a href="<?= base_url('/') ?>" class="neo-btn-white text-xs !px-3 !py-1.5 <?= !$selectedCat ? 'bg-neo-yellow' : '' ?>">All</a>
            <?php foreach ($categories as $cat): ?>
            <a href="<?= base_url('/?category=' . urlencode($cat->name)) ?>"
               class="neo-btn-white text-xs !px-3 !py-1.5 <?= $selectedCat === $cat->name ? 'bg-neo-yellow' : '' ?>">
                <?= $cat->name ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if (empty($grouped)): ?>
    <div class="neo-card text-center py-12">
        <span class="text-6xl block mb-4">🏕️</span>
        <h2 class="text-2xl font-black">No Products Found</h2>
        <p class="text-sm mt-2">Check back later for new arrivals.</p>
    </div>
    <?php endif; ?>

    <?php foreach ($grouped as $category => $products): ?>
    <div class="mb-10" data-aos="fade-up">
        <div class="flex items-center gap-3 mb-4">
            <?php
            $catIcons = ['Tents' => '⛺', 'Packs' => '🎒', 'Apparel' => '🧥', 'Cooking' => '🍳'];
            $catIcon = $catIcons[$category] ?? '📦';
            ?>
            <h2 class="text-2xl font-black"><?= $category ?></h2>
            <span class="neo-badge bg-black text-white"><?= count($products) ?> items</span>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <?php foreach ($products as $i => $p): ?>
            <a href="<?= base_url('product/' . $p->slug) ?>" class="neo-card hover:bg-neo-yellow transition-colors flex flex-col no-underline text-black" data-aos="zoom-in" data-aos-delay="<?= ($i % 4) * 50 ?>">
                <div class="bg-gray-100 border-2 border-black h-36 flex items-center justify-center text-5xl mb-3 overflow-hidden relative">
                    <?php if ($p->image): ?>
                    <img src="<?= base_url($p->image) ?>" alt="<?= $p->name ?>" class="w-full h-full object-cover" />
                    <?php else: ?>
                    <span><?= $catIcon ?></span>
                    <?php endif; ?>
                    <?php if ($p->stock < 1): ?>
                    <span class="absolute top-1 right-1 neo-badge bg-neo-red text-white text-[10px]">OUT</span>
                    <?php elseif ($p->stock < 5): ?>
                    <span class="absolute top-1 right-1 neo-badge bg-neo-orange text-white text-[10px]"><?= $p->stock ?> left</span>
                    <?php endif; ?>
                </div>
                <?php if (!empty($p->brand)): ?>
                <p class="text-[10px] font-bold opacity-60 uppercase tracking-wider"><?= esc($p->brand) ?></p>
                <?php endif; ?>
                <h3 class="font-heading font-bold text-sm uppercase leading-tight flex-1"><?= $p->name ?></h3>
                <?php $attrs = array_filter([$p->size ?? '', $p->color ?? '', $p->material ?? '']); ?>
                <?php if (!empty($attrs)): ?>
                <p class="text-xs font-bold text-gray-500 mt-1"><?= implode(' | ', $attrs) ?></p>
                <?php endif; ?>
                <p class="text-xs text-gray-500 mt-1 line-clamp-2"><?= esc($p->description) ?></p>
                <div class="flex items-center justify-between mt-3 pt-3 neo-divider">
                    <span class="font-black text-lg">Rp <?= number_format($p->price, 0, ',', '.') ?></span>
                    <form action="<?= base_url('cart/add') ?>" method="POST" onclick="event.stopPropagation()">
                        <input type="hidden" name="product_id" value="<?= $p->id ?>" />
                        <input type="hidden" name="quantity" value="1" />
                        <button type="submit" class="neo-btn-yellow text-xs !px-3 !py-1.5"
                            <?= $p->stock < 1 ? 'disabled' : '' ?>>
                            <?= $p->stock < 1 ? 'OUT' : '🛒' ?>
                        </button>
                    </form>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?= $this->endSection() ?>
