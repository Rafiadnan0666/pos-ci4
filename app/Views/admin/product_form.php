<?php
/**
 * @var object|null $product
 * @var object[] $categories
 */
?>
<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<div class="max-w-3xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6" data-aos="fade-down">
        <h1 class="text-3xl font-black flex items-center gap-3">
            <span><?= $product ? '✏️' : '➕' ?></span> <?= $product ? 'EDIT PRODUCT' : 'ADD PRODUCT' ?>
        </h1>
        <a href="<?= base_url('admin/products') ?>" class="neo-btn-white text-sm">← Products</a>
    </div>

    <?php if (session('errors')): ?>
    <div class="neo-card bg-[#FEE2E2] mb-6" data-aos="fade-up">
        <h3 class="font-black text-sm">⚠️ ERRORS</h3>
        <ul class="mt-2 text-sm space-y-1">
            <?php foreach (session('errors') as $e): ?><li>• <?= $e ?></li><?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <div class="neo-card" data-aos="fade-up">
        <form method="POST" enctype="multipart/form-data" action="<?= base_url($product ? 'admin/products/edit/' . $product->id : 'admin/products/create') ?>">
            <?= csrf_field() ?>
            <div class="space-y-4">
                <div>
                    <label class="block font-bold text-sm mb-1">Product Name *</label>
                    <input type="text" name="name" class="neo-input" value="<?= esc(old('name', $product->name ?? '')) ?>" required />
                </div>

                <div>
                    <label class="block font-bold text-sm mb-1">Description</label>
                    <textarea name="description" rows="3" class="neo-input"><?= esc(old('description', $product->description ?? '')) ?></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-sm mb-1">Category *</label>
                        <select name="category" class="neo-input" required>
                            <option value="">-- Select --</option>
                            <?php $selectedCat = old('category', $product->category ?? ''); ?>
                            <?php foreach ($categories as $c): ?>
                            <option value="<?= esc($c->name) ?>" <?= $selectedCat === $c->name ? 'selected' : '' ?>><?= esc($c->name) ?></option>
                            <?php endforeach; ?>
                            <option value="new">-- New Category --</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-sm mb-1">Or New Category Name</label>
                        <input type="text" name="new_category" class="neo-input" placeholder="Leave blank if selecting above" value="<?= esc(old('new_category')) ?>" />
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block font-bold text-sm mb-1">Price (Rp) *</label>
                        <input type="number" name="price" class="neo-input" min="0" value="<?= old('price', $product->price ?? '') ?>" required />
                    </div>
                    <div>
                        <label class="block font-bold text-sm mb-1">Stock *</label>
                        <input type="number" name="stock" class="neo-input" min="0" value="<?= old('stock', $product->stock ?? '') ?>" required />
                    </div>
                    <div>
                        <label class="block font-bold text-sm mb-1">Weight (grams) *</label>
                        <input type="number" name="weight_grams" class="neo-input" min="1" value="<?= old('weight_grams', $product->weight_grams ?? '') ?>" required />
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block font-bold text-sm mb-1">Size</label>
                        <input type="text" name="size" class="neo-input" placeholder="e.g. L, 42, One Size" value="<?= esc(old('size', $product->size ?? '')) ?>" />
                    </div>
                    <div>
                        <label class="block font-bold text-sm mb-1">Color</label>
                        <input type="text" name="color" class="neo-input" placeholder="e.g. Red, Blue" value="<?= esc(old('color', $product->color ?? '')) ?>" />
                    </div>
                    <div>
                        <label class="block font-bold text-sm mb-1">Material</label>
                        <input type="text" name="material" class="neo-input" placeholder="e.g. Cotton, Nylon" value="<?= esc(old('material', $product->material ?? '')) ?>" />
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-sm mb-1">Product Image</label>
                    <input type="file" name="image_file" id="image_file" class="neo-input !p-2" accept="image/jpeg,image/png,image/webp,image/gif" />
                    <p class="text-xs opacity-60 mt-1">Accepted: JPG, PNG, WebP, GIF — Max 2MB</p>
                    <div id="image_preview" class="mt-2 <?= ($product && $product->image) ? '' : 'hidden' ?>">
                        <img id="preview_img" src="<?= ($product && $product->image) ? base_url($product->image) : '' ?>" class="w-32 h-32 border-4 border-black object-cover" alt="Preview" />
                    </div>
                    <?php if ($product && $product->image): ?>
                    <div class="mt-2 flex items-center gap-3">
                        <span class="text-xs font-bold">Current image</span>
                        <label class="text-xs flex items-center gap-1">
                            <input type="checkbox" name="remove_image" value="1" /> Remove
                        </label>
                    </div>
                    <input type="hidden" name="existing_image" value="<?= esc($product->image) ?>" />
                    <?php endif; ?>
                </div>
            </div>

            <div class="mt-6 flex gap-3">
                <button type="submit" class="neo-btn-cyan flex-1">
                    <?= $product ? '✏️ Update Product' : '➕ Create Product' ?>
                </button>
                <a href="<?= base_url('admin/products') ?>" class="neo-btn-white">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelector('[name="category"]')?.addEventListener('change', function() {
    const newCat = document.querySelector('[name="new_category"]');
    if (this.value === 'new') {
        newCat.disabled = false;
        newCat.focus();
    } else {
        newCat.disabled = true;
        newCat.value = '';
    }
});
document.querySelector('[name="new_category"]')?.addEventListener('input', function() {
    if (this.value.trim()) {
        document.querySelector('[name="category"]').value = 'new';
    }
});
(function() {
    const cat = document.querySelector('[name="category"]');
    const newCat = document.querySelector('[name="new_category"]');
    if (cat && newCat && cat.value !== 'new') {
        newCat.disabled = true;
    }
})();

// Image preview on file select
document.getElementById('image_file')?.addEventListener('change', function(e) {
    const preview = document.getElementById('image_preview');
    const img = document.getElementById('preview_img');
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(ev) {
            img.src = ev.target.result;
            preview.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    } else {
        preview.classList.add('hidden');
        img.src = '';
    }
});
</script>
<?= $this->endSection() ?>
