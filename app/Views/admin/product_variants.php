<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<div class="max-w-5xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6" data-aos="fade-down">
        <h1 class="text-3xl font-black flex items-center gap-3">
            <span>🎨</span> MANAGE VARIANTS
        </h1>
        <div class="flex gap-2">
            <a href="<?= base_url('admin/products/edit/' . $product->id) ?>" class="neo-btn-white text-sm">✏️ Edit Product</a>
            <a href="<?= base_url('admin/products') ?>" class="neo-btn-white text-sm">← Products</a>
        </div>
    </div>

    <div class="neo-card mb-6" data-aos="fade-up">
        <h2 class="font-black text-lg"><?= esc($product->name) ?></h2>
        <p class="text-sm opacity-60">Category: <?= esc($product->category) ?> | Base Price: Rp <?= number_format($product->price, 0, ',', '.') ?></p>
    </div>

    <div class="neo-card" data-aos="fade-up">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-black">🎯 VARIANT COMBINATIONS</h2>
            <p class="text-xs opacity-60">Create color, size, material combinations with unique prices & stock</p>
        </div>

        <form method="POST">
            <?= csrf_field() ?>
            <div class="space-y-4" id="variants-container">
                <?php if ($hasVariants): ?>
                <?php foreach ($variants as $i => $v):
                    $vAttrs = json_decode($v->attributes, true) ?? [];
                    $attrNames = array_keys($vAttrs);
                    $attrValues = array_values($vAttrs);
                ?>
                <div class="variant-row neo-card !border-2 !shadow-[2px_2px_0px_0px_#000]">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-bold text-sm">Variant #<?= $i + 1 ?></span>
                        <button type="button" class="remove-variant neo-btn-red text-xs !px-2 !py-1">✕ Remove</button>
                    </div>
                    <div class="grid grid-cols-12 gap-2 items-start">
                        <div class="col-span-3">
                            <label class="block font-bold text-[10px] mb-0.5">Attribute Name</label>
                            <input type="text" name="variants[<?= $i ?>][attr_names][]" class="neo-input !py-1.5 !px-2 text-xs" value="<?= isset($attrNames[0]) ? esc($attrNames[0]) : '' ?>" placeholder="e.g. Color" />
                            <input type="text" name="variants[<?= $i ?>][attr_names][]" class="neo-input !py-1.5 !px-2 text-xs mt-1" value="<?= isset($attrNames[1]) ? esc($attrNames[1]) : '' ?>" placeholder="e.g. Size" />
                            <input type="text" name="variants[<?= $i ?>][attr_names][]" class="neo-input !py-1.5 !px-2 text-xs mt-1" value="<?= isset($attrNames[2]) ? esc($attrNames[2]) : '' ?>" placeholder="e.g. Material" />
                        </div>
                        <div class="col-span-3">
                            <label class="block font-bold text-[10px] mb-0.5">Attribute Value</label>
                            <input type="text" name="variants[<?= $i ?>][attr_values][]" class="neo-input !py-1.5 !px-2 text-xs" value="<?= isset($attrValues[0]) ? esc($attrValues[0]) : '' ?>" placeholder="e.g. Red" />
                            <input type="text" name="variants[<?= $i ?>][attr_values][]" class="neo-input !py-1.5 !px-2 text-xs mt-1" value="<?= isset($attrValues[1]) ? esc($attrValues[1]) : '' ?>" placeholder="e.g. XL" />
                            <input type="text" name="variants[<?= $i ?>][attr_values][]" class="neo-input !py-1.5 !px-2 text-xs mt-1" value="<?= isset($attrValues[2]) ? esc($attrValues[2]) : '' ?>" placeholder="e.g. Cotton" />
                        </div>
                        <div class="col-span-2">
                            <label class="block font-bold text-[10px] mb-0.5">Price (override)</label>
                            <input type="number" name="variants[<?= $i ?>][price]" class="neo-input !py-1.5 !px-2 text-xs" value="<?= $v->price ?>" placeholder="Base price" step="0.01" min="0" />
                        </div>
                        <div class="col-span-2">
                            <label class="block font-bold text-[10px] mb-0.5">Stock</label>
                            <input type="number" name="variants[<?= $i ?>][stock]" class="neo-input !py-1.5 !px-2 text-xs" value="<?= $v->stock ?>" min="0" required />
                        </div>
                        <div class="col-span-2">
                            <label class="block font-bold text-[10px] mb-0.5">SKU / Order</label>
                            <input type="text" name="variants[<?= $i ?>][sku]" class="neo-input !py-1.5 !px-2 text-xs" value="<?= esc($v->sku ?? '') ?>" placeholder="SKU" />
                            <input type="number" name="variants[<?= $i ?>][sort_order]" class="neo-input !py-1.5 !px-2 text-xs mt-1" value="<?= $v->sort_order ?>" placeholder="Order" min="0" />
                        </div>
                    </div>
                    <?php
                    $previewAttrs = json_decode($v->attributes, true) ?? [];
                    if (!empty($previewAttrs)):
                    ?>
                    <div class="mt-2 flex gap-1 flex-wrap">
                        <?php foreach ($previewAttrs as $an => $av): ?>
                        <span class="neo-badge bg-neo-yellow text-[10px]"><?= esc($an) ?>: <?= esc($av) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php else: ?>
                <div class="variant-row neo-card !border-2 !shadow-[2px_2px_0px_0px_#000]">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-bold text-sm">Variant #1</span>
                        <button type="button" class="remove-variant neo-btn-red text-xs !px-2 !py-1">✕ Remove</button>
                    </div>
                    <div class="grid grid-cols-12 gap-2 items-start">
                        <div class="col-span-3">
                            <label class="block font-bold text-[10px] mb-0.5">Attribute Name</label>
                            <input type="text" name="variants[0][attr_names][]" class="neo-input !py-1.5 !px-2 text-xs" placeholder="e.g. Color" />
                            <input type="text" name="variants[0][attr_names][]" class="neo-input !py-1.5 !px-2 text-xs mt-1" placeholder="e.g. Size" />
                            <input type="text" name="variants[0][attr_names][]" class="neo-input !py-1.5 !px-2 text-xs mt-1" placeholder="e.g. Material" />
                        </div>
                        <div class="col-span-3">
                            <label class="block font-bold text-[10px] mb-0.5">Attribute Value</label>
                            <input type="text" name="variants[0][attr_values][]" class="neo-input !py-1.5 !px-2 text-xs" placeholder="e.g. Red" />
                            <input type="text" name="variants[0][attr_values][]" class="neo-input !py-1.5 !px-2 text-xs mt-1" placeholder="e.g. XL" />
                            <input type="text" name="variants[0][attr_values][]" class="neo-input !py-1.5 !px-2 text-xs mt-1" placeholder="e.g. Cotton" />
                        </div>
                        <div class="col-span-2">
                            <label class="block font-bold text-[10px] mb-0.5">Price (override)</label>
                            <input type="number" name="variants[0][price]" class="neo-input !py-1.5 !px-2 text-xs" placeholder="Base price" step="0.01" min="0" />
                        </div>
                        <div class="col-span-2">
                            <label class="block font-bold text-[10px] mb-0.5">Stock</label>
                            <input type="number" name="variants[0][stock]" class="neo-input !py-1.5 !px-2 text-xs" value="0" min="0" required />
                        </div>
                        <div class="col-span-2">
                            <label class="block font-bold text-[10px] mb-0.5">SKU / Order</label>
                            <input type="text" name="variants[0][sku]" class="neo-input !py-1.5 !px-2 text-xs" placeholder="SKU" />
                            <input type="number" name="variants[0][sort_order]" class="neo-input !py-1.5 !px-2 text-xs mt-1" value="0" placeholder="Order" min="0" />
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <button type="button" id="add-variant" class="neo-btn-white text-sm mt-3">+ Add Variant</button>

            <div class="mt-6 flex gap-3">
                <button type="submit" class="neo-btn-cyan flex-1">💾 Save All Variants</button>
                <a href="<?= base_url('admin/products') ?>" class="neo-btn-white">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
let variantIndex = <?= $hasVariants ? count($variants) : 1 ?>;

document.getElementById('add-variant')?.addEventListener('click', function() {
    const container = document.getElementById('variants-container');
    const row = document.createElement('div');
    row.className = 'variant-row neo-card !border-2 !shadow-[2px_2px_0px_0px_#000]';
    const i = variantIndex;
    row.innerHTML = `
        <div class="flex items-center justify-between mb-2">
            <span class="font-bold text-sm">Variant #${i + 1}</span>
            <button type="button" class="remove-variant neo-btn-red text-xs !px-2 !py-1">✕ Remove</button>
        </div>
        <div class="grid grid-cols-12 gap-2 items-start">
            <div class="col-span-3">
                <label class="block font-bold text-[10px] mb-0.5">Attribute Name</label>
                <input type="text" name="variants[${i}][attr_names][]" class="neo-input !py-1.5 !px-2 text-xs" placeholder="e.g. Color" />
                <input type="text" name="variants[${i}][attr_names][]" class="neo-input !py-1.5 !px-2 text-xs mt-1" placeholder="e.g. Size" />
                <input type="text" name="variants[${i}][attr_names][]" class="neo-input !py-1.5 !px-2 text-xs mt-1" placeholder="e.g. Material" />
            </div>
            <div class="col-span-3">
                <label class="block font-bold text-[10px] mb-0.5">Attribute Value</label>
                <input type="text" name="variants[${i}][attr_values][]" class="neo-input !py-1.5 !px-2 text-xs" placeholder="e.g. Red" />
                <input type="text" name="variants[${i}][attr_values][]" class="neo-input !py-1.5 !px-2 text-xs mt-1" placeholder="e.g. XL" />
                <input type="text" name="variants[${i}][attr_values][]" class="neo-input !py-1.5 !px-2 text-xs mt-1" placeholder="e.g. Cotton" />
            </div>
            <div class="col-span-2">
                <label class="block font-bold text-[10px] mb-0.5">Price (override)</label>
                <input type="number" name="variants[${i}][price]" class="neo-input !py-1.5 !px-2 text-xs" placeholder="Base price" step="0.01" min="0" />
            </div>
            <div class="col-span-2">
                <label class="block font-bold text-[10px] mb-0.5">Stock</label>
                <input type="number" name="variants[${i}][stock]" class="neo-input !py-1.5 !px-2 text-xs" value="0" min="0" required />
            </div>
            <div class="col-span-2">
                <label class="block font-bold text-[10px] mb-0.5">SKU / Order</label>
                <input type="text" name="variants[${i}][sku]" class="neo-input !py-1.5 !px-2 text-xs" placeholder="SKU" />
                <input type="number" name="variants[${i}][sort_order]" class="neo-input !py-1.5 !px-2 text-xs mt-1" value="0" placeholder="Order" min="0" />
            </div>
        </div>
    `;
    container.appendChild(row);
    variantIndex++;
    attachRemoveHandlers();
});

function attachRemoveHandlers() {
    document.querySelectorAll('.remove-variant').forEach(btn => {
        btn.removeEventListener('click', handleRemove);
        btn.addEventListener('click', handleRemove);
    });
}

function handleRemove(e) {
    const rows = document.querySelectorAll('.variant-row');
    if (rows.length > 1) {
        this.closest('.variant-row').remove();
    } else {
        alert('At least one variant row is required');
    }
}

attachRemoveHandlers();
</script>
<?= $this->endSection() ?>
