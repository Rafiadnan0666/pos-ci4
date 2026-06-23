<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="max-w-7xl mx-auto px-4 py-8">
    <a href="<?= base_url('/') ?>" class="neo-btn-white text-sm mb-6 inline-block">← Back to Catalog</a>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
        <div class="neo-card" data-aos="fade-right">
            <div class="bg-gray-100 border-4 border-black h-80 flex items-center justify-center text-8xl">
                <?php
                $catIcons = ['Tents' => '⛺', 'Packs' => '🎒', 'Apparel' => '🧥', 'Cooking' => '🍳'];
                echo $catIcons[$product->category] ?? '📦';
                ?>
            </div>
        </div>
        <div data-aos="fade-left">
            <span class="neo-badge bg-black text-white"><?= strtoupper($product->category) ?></span>
            <h1 class="text-3xl md:text-4xl font-black mt-3"><?= $product->name ?></h1>
            <p class="text-sm mt-2"><?= $product->description ?></p>
            <div class="neo-divider my-4"></div>
            <div class="text-4xl font-black mb-4">Rp <?= number_format($product->price, 0, ',', '.') ?></div>
            <div class="flex items-center gap-4 text-sm mb-4">
                <span class="font-bold">Weight: <?= $product->weight_grams ?> g</span>
                <span class="neo-badge <?= $product->stock > 0 ? 'bg-neo-green' : 'bg-neo-red text-white' ?>">
                    <?= $product->stock > 0 ? 'In Stock: ' . $product->stock : 'Out of Stock' ?>
                </span>
            </div>

            <form action="<?= base_url('cart/add') ?>" method="POST" class="flex items-center gap-3">
                <input type="hidden" name="product_id" value="<?= $product->id ?>" />
                <div class="flex items-center border-4 border-black">
                    <button type="button" class="px-3 py-2 font-bold text-lg" onclick="this.nextElementSibling.stepDown()">-</button>
                    <input type="number" name="quantity" value="1" min="1" max="<?= $product->stock ?>" class="w-16 text-center font-bold border-x-4 border-black py-2" />
                    <button type="button" class="px-3 py-2 font-bold text-lg" onclick="this.previousElementSibling.stepUp()">+</button>
                </div>
                <button type="submit" class="neo-btn-yellow flex-1 text-base" <?= $product->stock < 1 ? 'disabled' : '' ?>>
                    <?= $product->stock < 1 ? 'OUT OF STOCK' : 'ADD TO CART' ?>
                </button>
            </form>
        </div>
    </div>

    <?php if (!empty($related)): ?>
    <div data-aos="fade-up">
        <h2 class="text-2xl font-black mb-4">RELATED PRODUCTS</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <?php foreach ($related as $r): ?>
            <a href="<?= base_url('product/' . $r->slug) ?>" class="neo-card hover:bg-neo-yellow transition-colors">
                <div class="bg-gray-100 border-2 border-black h-24 flex items-center justify-center text-3xl mb-2">
                    <?= $catIcons[$r->category] ?? '📦' ?>
                </div>
                <h3 class="font-heading font-bold text-xs uppercase"><?= $r->name ?></h3>
                <p class="font-bold text-sm mt-1">Rp <?= number_format($r->price, 0, ',', '.') ?></p>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
