<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<div class="max-w-3xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6" data-aos="fade-down">
        <h1 class="text-3xl font-black flex items-center gap-3">
            <span>📏</span> MANAGE SIZES
        </h1>
        <div class="flex gap-2">
            <a href="<?= base_url('admin/products') ?>" class="neo-btn-white text-sm">← Products</a>
        </div>
    </div>

    <div class="neo-card mb-6" data-aos="fade-up">
        <h2 class="font-black text-lg"><?= esc($product->name) ?></h2>
        <p class="text-sm opacity-60">Category: <?= esc($product->category) ?> | Base Stock: <?= $product->stock ?></p>
    </div>

    <div class="neo-card" data-aos="fade-up">
        <form method="POST">
            <?= csrf_field() ?>
            <div class="space-y-3" id="sizes-container">
                <?php if ($hasSizes): ?>
                <?php foreach ($sizes as $i => $s): ?>
                <div class="size-row flex items-center gap-3">
                    <div class="flex-1">
                        <label class="block font-bold text-xs mb-1">Size</label>
                        <input type="text" name="sizes[<?= $i ?>][size]" class="neo-input" value="<?= esc($s->size) ?>" placeholder="e.g. S, M, L, XL" required />
                    </div>
                    <div class="flex-1">
                        <label class="block font-bold text-xs mb-1">Stock</label>
                        <input type="number" name="sizes[<?= $i ?>][stock]" class="neo-input" value="<?= $s->stock ?>" min="0" required />
                    </div>
                    <button type="button" class="remove-size neo-btn-red text-xs !px-3 !py-2 mt-5">✕</button>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="size-row flex items-center gap-3">
                    <div class="flex-1">
                        <label class="block font-bold text-xs mb-1">Size</label>
                        <input type="text" name="sizes[0][size]" class="neo-input" placeholder="e.g. S, M, L, XL" />
                    </div>
                    <div class="flex-1">
                        <label class="block font-bold text-xs mb-1">Stock</label>
                        <input type="number" name="sizes[0][stock]" class="neo-input" value="0" min="0" />
                    </div>
                    <button type="button" class="remove-size neo-btn-red text-xs !px-3 !py-2 mt-5">✕</button>
                </div>
                <?php endif; ?>
            </div>

            <button type="button" id="add-size" class="neo-btn-white text-sm mt-3">+ Add Size</button>

            <div class="mt-6 flex gap-3">
                <button type="submit" class="neo-btn-cyan flex-1">💾 Save Sizes</button>
                <a href="<?= base_url('admin/products') ?>" class="neo-btn-white">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
let sizeIndex = <?= $hasSizes ? count($sizes) : 1 ?>;

document.getElementById('add-size')?.addEventListener('click', function() {
    const container = document.getElementById('sizes-container');
    const row = document.createElement('div');
    row.className = 'size-row flex items-center gap-3';
    row.innerHTML = `
        <div class="flex-1">
            <label class="block font-bold text-xs mb-1">Size</label>
            <input type="text" name="sizes[${sizeIndex}][size]" class="neo-input" placeholder="e.g. S, M, L, XL" required />
        </div>
        <div class="flex-1">
            <label class="block font-bold text-xs mb-1">Stock</label>
            <input type="number" name="sizes[${sizeIndex}][stock]" class="neo-input" value="0" min="0" required />
        </div>
        <button type="button" class="remove-size neo-btn-red text-xs !px-3 !py-2 mt-5">✕</button>
    `;
    container.appendChild(row);
    sizeIndex++;
    attachRemoveHandlers();
});

function attachRemoveHandlers() {
    document.querySelectorAll('.remove-size').forEach(btn => {
        btn.removeEventListener('click', handleRemove);
        btn.addEventListener('click', handleRemove);
    });
}

function handleRemove(e) {
    const rows = document.querySelectorAll('.size-row');
    if (rows.length > 1) {
        this.closest('.size-row').remove();
    } else {
        alert('At least one size row is required');
    }
}

attachRemoveHandlers();
</script>
<?= $this->endSection() ?>
