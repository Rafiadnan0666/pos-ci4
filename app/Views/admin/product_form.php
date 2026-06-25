<?php
/**
 * @var object|null $product
 * @var object[] $categories
 * @var object[] $galleryImages
 */
?>
<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<div class="max-w-4xl mx-auto px-4 py-8">
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

    <div class="space-y-6">
        <form method="POST" enctype="multipart/form-data" action="<?= base_url($product ? 'admin/products/edit/' . $product->id : 'admin/products/create') ?>">
            <?= csrf_field() ?>

            <!-- BASIC INFO -->
            <div class="neo-card" data-aos="fade-up">
                <h2 class="text-lg font-black mb-4 flex items-center gap-2"><span>📋</span> BASIC INFORMATION</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block font-bold text-sm mb-1">Product Name *</label>
                        <input type="text" name="name" class="neo-input" value="<?= esc(old('name', $product->name ?? '')) ?>" required />
                    </div>

                    <div>
                        <label class="block font-bold text-sm mb-1">Description</label>
                        <textarea name="description" rows="4" class="neo-input"><?= esc(old('description', $product->description ?? '')) ?></textarea>
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

                    <div>
                        <label class="block font-bold text-sm mb-1">Brand</label>
                        <input type="text" name="brand" class="neo-input" placeholder="e.g. Eiger, Rei, The North Face" value="<?= esc(old('brand', $product->brand ?? '')) ?>" />
                    </div>
                </div>
            </div>

            <!-- ATTRIBUTES -->
            <div class="neo-card" data-aos="fade-up">
                <h2 class="text-lg font-black mb-4 flex items-center gap-2"><span>🎨</span> ATTRIBUTES</h2>
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
            </div>

            <!-- DIMENSIONS & WARRANTY -->
            <div class="neo-card" data-aos="fade-up">
                <h2 class="text-lg font-black mb-4 flex items-center gap-2"><span>📐</span> DIMENSIONS & WARRANTY</h2>
                <div class="grid grid-cols-4 gap-4">
                    <div>
                        <label class="block font-bold text-sm mb-1">Length (cm)</label>
                        <input type="number" name="dimension_length" class="neo-input" min="0" placeholder="0" value="<?= old('dimension_length', $product->dimension_length ?? '') ?>" />
                    </div>
                    <div>
                        <label class="block font-bold text-sm mb-1">Width (cm)</label>
                        <input type="number" name="dimension_width" class="neo-input" min="0" placeholder="0" value="<?= old('dimension_width', $product->dimension_width ?? '') ?>" />
                    </div>
                    <div>
                        <label class="block font-bold text-sm mb-1">Height (cm)</label>
                        <input type="number" name="dimension_height" class="neo-input" min="0" placeholder="0" value="<?= old('dimension_height', $product->dimension_height ?? '') ?>" />
                    </div>
                    <div>
                        <label class="block font-bold text-sm mb-1">Warranty</label>
                        <input type="text" name="warranty" class="neo-input" placeholder="e.g. 1 Year" value="<?= esc(old('warranty', $product->warranty ?? '')) ?>" />
                    </div>
                </div>
            </div>

            <!-- DETAILS -->
            <div class="neo-card" data-aos="fade-up">
                <h2 class="text-lg font-black mb-4 flex items-center gap-2"><span>📝</span> DETAILS</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block font-bold text-sm mb-1">Features <span class="text-xs opacity-60">(one per line)</span></label>
                        <textarea name="features" rows="5" class="neo-input" placeholder="Enter product features, one per line..."><?php
                            $featuresVal = old('features', $product->features ?? '');
                            if (is_string($featuresVal) && !empty($featuresVal) && $featuresVal[0] === '[') {
                                $decoded = json_decode($featuresVal, true);
                                echo is_array($decoded) ? implode("\n", $decoded) : $featuresVal;
                            } else {
                                echo esc($featuresVal);
                            }
                        ?></textarea>
                    </div>
                    <div>
                        <label class="block font-bold text-sm mb-1">Specifications <span class="text-xs opacity-60">(key: value, one per line)</span></label>
                        <textarea name="specifications" rows="5" class="neo-input" placeholder="Material: 100% Nylon&#10;Capacity: 40L&#10;Waterproof: Yes"><?php
                            $specsVal = old('specifications', $product->specifications ?? '');
                            if (is_string($specsVal) && !empty($specsVal) && $specsVal[0] === '{') {
                                $decoded = json_decode($specsVal, true);
                                if (is_array($decoded)) {
                                    $lines = [];
                                    foreach ($decoded as $k => $v) { $lines[] = "$k: $v"; }
                                    echo implode("\n", $lines);
                                } else { echo $specsVal; }
                            } else {
                                echo esc($specsVal);
                            }
                        ?></textarea>
                    </div>
                    <div>
                        <label class="block font-bold text-sm mb-1">Care Instructions</label>
                        <textarea name="care_instructions" rows="3" class="neo-input" placeholder="How to care for this product..."><?= esc(old('care_instructions', $product->care_instructions ?? '')) ?></textarea>
                    </div>
                    <div>
                        <label class="block font-bold text-sm mb-1">Video URL <span class="text-xs opacity-60">(YouTube/Vimeo)</span></label>
                        <input type="url" name="video_url" class="neo-input" placeholder="https://www.youtube.com/watch?v=..." value="<?= esc(old('video_url', $product->video_url ?? '')) ?>" />
                    </div>
                </div>
            </div>

            <!-- MAIN IMAGE -->
            <div class="neo-card" data-aos="fade-up">
                <h2 class="text-lg font-black mb-4 flex items-center gap-2"><span>🖼️</span> MAIN IMAGE</h2>
                <div>
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

                <!-- GALLERY IMAGES -->
                <div class="neo-divider my-4"></div>
                <h3 class="font-black text-sm mb-3 flex items-center gap-2"><span>🏞️</span> GALLERY IMAGES <span class="text-xs opacity-60">(additional photos)</span></h3>
                <?php if (!empty($galleryImages)): ?>
                <div class="flex flex-wrap gap-3 mb-4" id="gallery-preview">
                    <?php foreach ($galleryImages as $gi): ?>
                    <div class="relative gallery-item" data-id="<?= $gi->id ?>">
                        <img src="<?= base_url($gi->image) ?>" class="w-24 h-24 border-4 border-black object-cover" alt="" />
                        <button type="button" class="absolute -top-2 -right-2 neo-btn-red text-xs !px-1 !py-0.5 remove-gallery-btn" data-id="<?= $gi->id ?>">✕</button>
                    </div>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="remove_gallery" id="remove_gallery" value="" />
                <p class="text-xs opacity-60 mb-2">Click ✕ to remove gallery images (saved on form submit)</p>
                <?php endif; ?>
                <div>
                    <input type="file" name="gallery_images[]" id="gallery_images" class="neo-input !p-2" accept="image/jpeg,image/png,image/webp,image/gif" multiple />
                    <p class="text-xs opacity-60 mt-1">Select multiple images to add to gallery</p>
                </div>
            </div>

            <?php if ($product): ?>
            <div class="neo-divider my-4"></div>
            <div class="flex gap-3">
                <a href="<?= base_url('admin/products/sizes/' . $product->id) ?>" class="neo-btn-white text-sm flex-1 text-center">📏 Manage Sizes & Stock</a>
            </div>
            <?php endif; ?>

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
    if (this.value === 'new') { newCat.disabled = false; newCat.focus(); }
    else { newCat.disabled = true; newCat.value = ''; }
});
document.querySelector('[name="new_category"]')?.addEventListener('input', function() {
    if (this.value.trim()) { document.querySelector('[name="category"]').value = 'new'; }
});
(function() {
    const cat = document.querySelector('[name="category"]');
    const newCat = document.querySelector('[name="new_category"]');
    if (cat && newCat && cat.value !== 'new') { newCat.disabled = true; }
})();

document.getElementById('image_file')?.addEventListener('change', function(e) {
    const preview = document.getElementById('image_preview');
    const img = document.getElementById('preview_img');
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(ev) { img.src = ev.target.result; preview.classList.remove('hidden'); };
        reader.readAsDataURL(file);
    } else {
        preview.classList.add('hidden');
        img.src = '';
    }
});

let removeGalleryIds = [];
document.querySelectorAll('.remove-gallery-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const id = parseInt(this.dataset.id);
        if (!removeGalleryIds.includes(id)) removeGalleryIds.push(id);
        document.getElementById('remove_gallery').value = removeGalleryIds.join(',');
        this.closest('.gallery-item').remove();
    });
});
</script>
<?= $this->endSection() ?>
