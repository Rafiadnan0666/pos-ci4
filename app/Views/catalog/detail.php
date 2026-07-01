<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="max-w-7xl mx-auto px-4 py-8">
    <a href="<?= base_url('/') ?>" class="neo-btn-white text-sm mb-6 inline-block">← Back to Catalog</a>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-12">
        <div data-aos="fade-right">
            <div class="neo-card">
                <div class="bg-gray-100 border-4 border-black h-80 flex items-center justify-center text-8xl overflow-hidden relative" id="main-image-container">
                    <?php $catIcons = ['Tents' => '⛺', 'Packs' => '🎒', 'Apparel' => '🧥', 'Cooking' => '🍳']; ?>
                    <?php if ($product->image): ?>
                    <img id="main-product-image" src="<?= base_url($product->image) ?>" alt="<?= esc($product->name) ?>" class="w-full h-full object-cover" />
                    <?php else: ?>
                    <?= $catIcons[$product->category] ?? '📦' ?>
                    <?php endif; ?>
                </div>
                <?php if (!empty($galleryImages)): ?>
                <div class="flex gap-2 mt-3 overflow-x-auto pb-2" id="gallery-thumbnails">
                    <button type="button" class="gallery-thumb w-16 h-16 border-4 border-black flex-shrink-0 overflow-hidden hover:bg-neo-yellow transition-colors <?= $product->image ? '' : 'bg-neo-yellow' ?>"
                        data-image="">
                        <?php if ($product->image): ?>
                        <img src="<?= base_url($product->image) ?>" class="w-full h-full object-cover" alt="" />
                        <?php else: ?>
                        <span class="text-2xl flex items-center justify-center h-full">📷</span>
                        <?php endif; ?>
                    </button>
                    <?php foreach ($galleryImages as $gi): ?>
                    <button type="button" class="gallery-thumb w-16 h-16 border-4 border-black flex-shrink-0 overflow-hidden hover:bg-neo-yellow transition-colors"
                        data-image="<?= base_url($gi->image) ?>">
                        <img src="<?= base_url($gi->image) ?>" class="w-full h-full object-cover" alt="" />
                    </button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div data-aos="fade-left">
            <?php if (!empty($product->brand)): ?>
            <span class="neo-badge bg-black text-white text-[10px] mb-2 inline-block"><?= strtoupper(esc($product->brand)) ?></span>
            <?php endif; ?>
            <span class="neo-badge bg-black text-white"><?= strtoupper(esc($product->category)) ?></span>
            <h1 class="text-3xl md:text-4xl font-black mt-3"><?= esc($product->name) ?></h1>

            <div class="flex flex-wrap items-center gap-2 mt-3 text-sm">
                <?php if ($ratingSum->total > 0): ?>
                <span class="flex items-center gap-1">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <span class="text-base <?= $i <= round($ratingSum->average) ? 'text-[#FFDE4D]' : 'opacity-20' ?>">★</span>
                    <?php endfor; ?>
                    <span class="font-bold ml-1"><?= $ratingSum->average ?></span>
                    <span class="opacity-60">(<?= $ratingSum->total ?> reviews)</span>
                </span>
                <?php endif; ?>
                <span class="neo-badge <?= $product->stock > 0 ? 'bg-neo-green' : 'bg-neo-red text-white' ?>">
                    <?= $product->stock > 0 ? 'In Stock: ' . $product->stock : 'Out of Stock' ?>
                </span>
            </div>

            <div class="text-4xl font-black mt-4" id="product-price">Rp <?= number_format($product->price, 0, ',', '.') ?></div>

            <div class="neo-divider my-4"></div>

            <p class="text-sm leading-relaxed"><?= esc($product->description) ?></p>

            <div class="neo-divider my-4"></div>

            <!-- ====== ADVANCED VARIANT SELECTOR ====== -->
            <?php if (!empty($variantAttrs)): ?>
            <div id="variant-selector" data-base-price="<?= $product->price ?>">
                <?php foreach ($variantAttrs as $attrName => $attrValues): ?>
                <div class="mb-4">
                    <p class="font-bold text-sm mb-2"><?= esc(ucfirst($attrName)) ?>:</p>
                    <div class="flex flex-wrap gap-2" data-attr-name="<?= esc($attrName) ?>">
                        <?php foreach ($attrValues as $val): ?>
                        <button type="button"
                            class="variant-attr-btn neo-btn-white text-xs !px-4 !py-2"
                            data-attr-name="<?= esc($attrName) ?>"
                            data-attr-value="<?= esc($val) ?>">
                            <?= esc($val) ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <input type="hidden" name="variant_id" id="selected-variant-id" value="" />
                <p id="variant-error" class="text-[#EF4444] text-xs font-bold mt-1 hidden">Please select all attributes</p>
                <p id="variant-stock-info" class="text-xs font-bold mt-1 hidden"></p>
            </div>
            <?php elseif (!empty($sizes)): ?>
            <!-- ====== SIMPLE SIZE SELECTOR (fallback) ====== -->
            <div class="mb-4">
                <p class="font-bold text-sm mb-2">Size:</p>
                <div class="flex flex-wrap gap-2" id="size-selector">
                    <?php foreach ($sizes as $s): ?>
                    <button type="button"
                        class="size-option neo-btn-white text-xs !px-4 !py-2 <?= $s->stock < 1 ? 'opacity-40 cursor-not-allowed' : '' ?>"
                        data-size="<?= esc($s->size) ?>"
                        data-stock="<?= $s->stock ?>"
                        <?= $s->stock < 1 ? 'disabled' : '' ?>>
                        <?= esc($s->size) ?>
                        <span class="text-[10px] opacity-60 block"><?= $s->stock ?> left</span>
                    </button>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="selected_size" id="selected_size" value="" />
                <p id="size-error" class="text-[#EF4444] text-xs font-bold mt-1 hidden">Please select a size</p>
            </div>
            <?php endif; ?>

            <form action="<?= base_url('cart/add') ?>" method="POST" class="flex items-center gap-3" id="add-to-cart-form">
                <?= csrf_field() ?>
                <input type="hidden" name="product_id" value="<?= $product->id ?>" />
                <input type="hidden" name="size" id="cart-size" value="" />
                <input type="hidden" name="variant_id" id="cart-variant-id" value="" />
                <div class="flex items-center border-4 border-black">
                    <button type="button" class="px-3 py-2 font-bold text-lg neo-btn-white !border-0 !shadow-none !rounded-none" onclick="this.nextElementSibling.stepDown();updateStockInfo()">−</button>
                    <input type="number" name="quantity" id="qty-input" value="1" min="1" max="<?= $product->stock ?>" class="w-16 text-center font-bold border-x-4 border-black py-2" />
                    <button type="button" class="px-3 py-2 font-bold text-lg neo-btn-white !border-0 !shadow-none !rounded-none" onclick="this.previousElementSibling.stepUp();updateStockInfo()">+</button>
                </div>
                <button type="submit" class="neo-btn-yellow flex-1 text-base" <?= $product->stock < 1 ? 'disabled' : '' ?>>
                    <?= $product->stock < 1 ? 'OUT OF STOCK' : '🛒 ADD TO CART' ?>
                </button>
            </form>

            <div class="flex flex-wrap gap-2 mt-4 text-xs">
                <?php if (!empty($product->color)): ?><span class="neo-badge bg-white">🎨 <?= esc($product->color) ?></span><?php endif; ?>
                <?php if (!empty($product->material)): ?><span class="neo-badge bg-white">🧵 <?= esc($product->material) ?></span><?php endif; ?>
                <?php if (!empty($product->weight_grams)): ?><span class="neo-badge bg-white">⚖️ <?= $product->weight_grams ?>g</span><?php endif; ?>
                <?php if (!empty($product->warranty)): ?><span class="neo-badge bg-white">🛡️ <?= esc($product->warranty) ?></span><?php endif; ?>
                <?php if (!empty($product->dimension_length)): ?><span class="neo-badge bg-white">📐 <?= $product->dimension_length ?>×<?= $product->dimension_width ?: '?' ?>×<?= $product->dimension_height ?: '?' ?> cm</span><?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (!empty($sizes)): ?>
    <div class="neo-card mb-8" data-aos="fade-up">
        <h2 class="text-xl font-black mb-4">📏 SIZE & STOCK DETAILS</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b-4 border-black">
                        <th class="py-2 px-3 font-black text-sm">Size</th>
                        <th class="py-2 px-3 font-black text-sm">Stock</th>
                        <th class="py-2 px-3 font-black text-sm">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sizes as $s): ?>
                    <tr class="border-b-2 border-black">
                        <td class="py-2 px-3 font-bold"><?= esc($s->size) ?></td>
                        <td class="py-2 px-3"><?= $s->stock ?></td>
                        <td class="py-2 px-3">
                            <span class="neo-badge text-[10px] <?= $s->stock > 0 ? 'bg-[#22C55E]' : 'bg-[#EF4444] text-white' ?>">
                                <?= $s->stock > 0 ? 'Available' : 'Out of Stock' ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($variants)): ?>
    <div class="neo-card mb-8" data-aos="fade-up">
        <h2 class="text-xl font-black mb-4">🎨 ALL VARIANT COMBINATIONS</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b-4 border-black">
                        <th class="py-2 px-3 font-black text-sm">Attributes</th>
                        <th class="py-2 px-3 font-black text-sm">Price</th>
                        <th class="py-2 px-3 font-black text-sm">Stock</th>
                        <th class="py-2 px-3 font-black text-sm">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($variants as $v):
                        $vAttrs = json_decode($v->attributes ?? '{}', true) ?? [];
                        $attrParts = [];
                        foreach ($vAttrs as $an => $av) { $attrParts[] = esc($an) . ': ' . esc($av); }
                        $vPrice = $v->price !== null ? (float) $v->price : (float) $product->price;
                    ?>
                    <tr class="border-b-2 border-black">
                        <td class="py-2 px-3 font-bold text-sm"><?= implode(' | ', $attrParts) ?></td>
                        <td class="py-2 px-3">Rp <?= number_format($vPrice, 0, ',', '.') ?></td>
                        <td class="py-2 px-3"><?= $v->stock ?></td>
                        <td class="py-2 px-3">
                            <span class="neo-badge text-[10px] <?= $v->stock > 0 ? 'bg-[#22C55E]' : 'bg-[#EF4444] text-white' ?>">
                                <?= $v->stock > 0 ? 'Available' : 'Out of Stock' ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- PRODUCT DETAILS TABS -->
    <div class="neo-card mb-8" data-aos="fade-up">
        <div class="flex flex-wrap border-b-4 border-black mb-6" id="product-tabs">
            <button type="button" class="tab-btn font-black text-sm px-6 py-3 border-r-4 border-b-4 border-black -mb-1 bg-neo-yellow active-tab" data-tab="specifications">📋 Specifications</button>
            <?php if (!empty($product->features) || !empty($features)): ?>
            <button type="button" class="tab-btn font-black text-sm px-6 py-3 border-r-4 border-b-4 border-black hover:bg-neo-yellow" data-tab="features">⭐ Features</button>
            <?php endif; ?>
            <?php if (!empty($product->care_instructions)): ?>
            <button type="button" class="tab-btn font-black text-sm px-6 py-3 border-r-4 border-b-4 border-black hover:bg-neo-yellow" data-tab="care">🧺 Care</button>
            <?php endif; ?>
            <?php if (!empty($product->video_url)): ?>
            <button type="button" class="tab-btn font-black text-sm px-6 py-3 border-r-4 border-b-4 border-black hover:bg-neo-yellow" data-tab="video">🎥 Video</button>
            <?php endif; ?>
        </div>

        <div id="tab-specifications" class="tab-content">
            <?php if (!empty($specs) && is_array($specs)): ?>
            <table class="w-full text-sm">
                <tbody>
                    <?php foreach ($specs as $key => $val): ?>
                    <tr class="border-b-2 border-black">
                        <td class="py-2 pr-4 font-bold w-1/3"><?= esc($key) ?></td>
                        <td class="py-2"><?= esc($val) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="grid grid-cols-2 gap-4 text-sm">
                <?php if (!empty($product->brand)): ?><div><span class="font-bold">Brand:</span> <?= esc($product->brand) ?></div><?php endif; ?>
                <?php if (!empty($product->material)): ?><div><span class="font-bold">Material:</span> <?= esc($product->material) ?></div><?php endif; ?>
                <?php if (!empty($product->color)): ?><div><span class="font-bold">Color:</span> <?= esc($product->color) ?></div><?php endif; ?>
                <?php if (!empty($product->size)): ?><div><span class="font-bold">Size:</span> <?= esc($product->size) ?></div><?php endif; ?>
                <div><span class="font-bold">Weight:</span> <?= $product->weight_grams ?> g</div>
                <?php if (!empty($product->dimension_length)): ?><div><span class="font-bold">Dimensions:</span> <?= $product->dimension_length ?> × <?= $product->dimension_width ?: '?' ?> × <?= $product->dimension_height ?: '?' ?> cm</div><?php endif; ?>
                <?php if (!empty($product->warranty)): ?><div><span class="font-bold">Warranty:</span> <?= esc($product->warranty) ?></div><?php endif; ?>
                <?php if (!empty($product->category)): ?><div><span class="font-bold">Category:</span> <?= esc($product->category) ?></div><?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($product->features) || !empty($features)): ?>
        <div id="tab-features" class="tab-content hidden">
            <ul class="space-y-2 text-sm">
                <?php if (!empty($features) && is_array($features)): ?>
                    <?php foreach ($features as $f): ?>
                    <li class="flex items-start gap-2">
                        <span class="text-neo-green font-bold mt-0.5">✅</span>
                        <span><?= esc($f) ?></span>
                    </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php foreach (explode("\n", $product->features) as $f): ?>
                        <?php $f = trim($f); if (!empty($f)): ?>
                        <li class="flex items-start gap-2">
                            <span class="text-neo-green font-bold mt-0.5">✅</span>
                            <span><?= esc($f) ?></span>
                        </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
        <?php endif; ?>

        <?php if (!empty($product->care_instructions)): ?>
        <div id="tab-care" class="tab-content hidden">
            <div class="bg-[#F0FDF4] border-4 border-black p-4 text-sm">
                <?= nl2br(esc($product->care_instructions)) ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($product->video_url)): ?>
        <div id="tab-video" class="tab-content hidden">
            <div class="aspect-w-16 aspect-h-9">
                <?php
                $videoUrl = $product->video_url;
                $embedUrl = '';
                if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/', $videoUrl, $m)) {
                    $embedUrl = 'https://www.youtube.com/embed/' . $m[1];
                } elseif (preg_match('/vimeo\.com\/(\d+)/', $videoUrl, $m)) {
                    $embedUrl = 'https://player.vimeo.com/video/' . $m[1];
                }
                ?>
                <?php if ($embedUrl): ?>
                <iframe src="<?= $embedUrl ?>" class="w-full h-96 border-4 border-black" allowfullscreen loading="lazy"></iframe>
                <?php else: ?>
                <a href="<?= esc($videoUrl) ?>" target="_blank" class="neo-btn-cyan inline-block text-sm">🎥 Watch Video</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- RATING SUMMARY -->
    <?php if ($ratingSum->total > 0): ?>
    <div class="neo-card mb-8" data-aos="fade-up">
        <h2 class="text-xl font-black mb-4">⭐ CUSTOMER RATINGS</h2>
        <div class="flex items-center gap-6 mb-6 flex-wrap">
            <div class="text-center">
                <div class="text-5xl font-black"><?= $ratingSum->average ?></div>
                <div class="text-sm mt-1">out of 5</div>
                <div class="flex items-center gap-1 mt-1 justify-center">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <span class="text-lg <?= $i <= round($ratingSum->average) ? 'text-[#FFDE4D]' : 'opacity-20' ?>">★</span>
                    <?php endfor; ?>
                </div>
            </div>
            <div class="flex-1 min-w-[200px]">
                <?php $levels = [5 => 'five', 4 => 'four', 3 => 'three', 2 => 'two', 1 => 'one']; ?>
                <?php foreach ($levels as $star => $key): ?>
                <div class="flex items-center gap-2 text-sm mb-1">
                    <span class="font-bold w-6"><?= $star ?>★</span>
                    <div class="flex-1 bg-gray-200 border-2 border-black h-4">
                        <div class="bg-[#FFDE4D] h-full" style="width: <?= $ratingSum->total > 0 ? ($ratingSum->$key / $ratingSum->total * 100) : 0 ?>%"></div>
                    </div>
                    <span class="text-xs opacity-60 w-8"><?= $ratingSum->$key ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- REVIEWS -->
    <div class="neo-card mb-8" data-aos="fade-up" id="reviews">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-black">💬 REVIEWS (<?= $ratingSum->total ?>)</h2>
        </div>

        <?php if (empty($reviews)): ?>
        <div class="text-center py-8">
            <span class="text-5xl block mb-3">💬</span>
            <p class="font-bold">No reviews yet. Be the first to review this product!</p>
        </div>
        <?php else: ?>
        <div class="space-y-4">
            <?php foreach ($reviews as $review): ?>
            <div class="border-4 border-black p-4 <?= $review->reply ? 'bg-[#F0FDF4]' : '' ?>">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 border-2 border-black bg-[#FFDE4D] flex items-center justify-center font-bold text-sm overflow-hidden">
                            <?php if ($review->user_avatar): ?>
                            <img src="<?= base_url($review->user_avatar) ?>" class="w-full h-full object-cover" alt="" />
                            <?php else: ?>
                            <?= strtoupper(substr($review->user_name, 0, 1)) ?>
                            <?php endif; ?>
                        </div>
                        <div>
                            <p class="font-bold text-sm"><?= esc($review->user_name) ?></p>
                            <div class="flex items-center gap-1">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="text-sm <?= $i <= $review->rating ? 'text-[#FFDE4D]' : 'opacity-20' ?>">★</span>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                    <span class="text-xs opacity-60"><?= date('d M Y', strtotime($review->created_at)) ?></span>
                </div>
                <?php if ($review->review): ?>
                <p class="mt-3 text-sm"><?= nl2br(esc($review->review)) ?></p>
                <?php endif; ?>
                <?php if ($review->reply): ?>
                <div class="mt-3 ml-6 pl-4 border-l-4 border-[#22C55E] bg-white p-3">
                    <p class="text-xs font-bold text-[#22C55E] mb-1">👤 Admin Reply</p>
                    <p class="text-sm"><?= nl2br(esc($review->reply)) ?></p>
                    <p class="text-[10px] opacity-60 mt-1"><?= date('d M Y', strtotime($review->replied_at)) ?></p>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (session()->get('isLoggedIn')): ?>
        <div class="neo-divider my-6"></div>
        <?php if ($hasReviewed): ?>
        <div class="bg-[#FFDE4D] border-4 border-black p-4 text-center">
            <p class="font-bold">✅ You have already reviewed this product</p>
        </div>
        <?php else: ?>
        <h3 class="font-black text-lg mb-3">✍️ Write a Review</h3>
        <?php if (session()->has('review_errors')): ?>
        <div class="bg-[#EF4444] text-white border-4 border-black p-3 mb-4 text-sm font-bold">
            <?php foreach (session('review_errors') as $e): ?>
            <div>⚠️ <?= $e ?></div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <form action="<?= base_url('product/review') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="product_id" value="<?= $product->id ?>" />

            <div class="mb-3">
                <p class="font-bold text-sm mb-1">Rating</p>
                <div class="flex gap-1 text-2xl" id="star-rating">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <button type="button" class="star-btn text-3xl cursor-pointer hover:scale-110 transition-transform" data-value="<?= $i ?>">★</button>
                    <?php endfor; ?>
                </div>
                <input type="hidden" name="rating" id="rating-value" value="5" />
            </div>

            <div class="mb-3">
                <label class="block font-bold text-sm mb-1">Your Review</label>
                <textarea name="review" rows="4" class="neo-input" placeholder="Share your experience with this product (min 10 characters)..."><?= esc(old('review')) ?></textarea>
            </div>

            <button type="submit" class="neo-btn-cyan">Submit Review</button>
        </form>
        <?php endif; ?>
        <?php else: ?>
        <div class="neo-divider my-6"></div>
        <div class="bg-[#FFDE4D] border-4 border-black p-4 text-center">
            <p class="font-bold">🔒 <a href="<?= base_url('login') ?>" class="underline">Login</a> to write a review</p>
        </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($related)): ?>
    <div data-aos="fade-up">
        <h2 class="text-2xl font-black mb-4">RELATED PRODUCTS</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <?php foreach ($related as $r): ?>
            <a href="<?= base_url('product/' . $r->slug) ?>" class="neo-card hover:bg-neo-yellow transition-colors">
                <div class="bg-gray-100 border-2 border-black h-24 flex items-center justify-center text-3xl mb-2 overflow-hidden">
                    <?php if ($r->image): ?>
                    <img src="<?= base_url($r->image) ?>" alt="<?= esc($r->name) ?>" class="w-full h-full object-cover" />
                    <?php else: ?>
                    <?= $catIcons[$r->category] ?? '📦' ?>
                    <?php endif; ?>
                </div>
                <?php if (!empty($r->brand)): ?>
                <p class="text-[10px] font-bold opacity-60"><?= esc($r->brand) ?></p>
                <?php endif; ?>
                <h3 class="font-heading font-bold text-xs uppercase leading-tight"><?= esc($r->name) ?></h3>
                <p class="font-bold text-sm mt-1">Rp <?= number_format($r->price, 0, ',', '.') ?></p>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Store variants as JS array
const variants = <?= json_encode($variants) ?>;
const variantAttrs = <?= json_encode($variantAttrs) ?>;

// Gallery thumbnails
document.querySelectorAll('.gallery-thumb').forEach(function(thumb) {
    thumb.addEventListener('click', function() {
        const imgUrl = this.dataset.image;
        const mainImg = document.getElementById('main-product-image');
        if (imgUrl && mainImg) {
            mainImg.src = imgUrl;
        }
        document.querySelectorAll('.gallery-thumb').forEach(function(t) { t.classList.remove('bg-neo-yellow'); });
        this.classList.add('bg-neo-yellow');
    });
});

// Tabs
document.querySelectorAll('.tab-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.tab-btn').forEach(function(b) { b.classList.remove('bg-neo-yellow', 'active-tab'); });
        this.classList.add('bg-neo-yellow', 'active-tab');
        document.querySelectorAll('.tab-content').forEach(function(tc) { tc.classList.add('hidden'); });
        document.getElementById('tab-' + this.dataset.tab).classList.remove('hidden');
    });
});

// Variant selector (advanced)
function initVariantSelector() {
    const attrGroups = document.querySelectorAll('[data-attr-name]');
    const selectedVariantId = document.getElementById('selected-variant-id');
    const cartVariantId = document.getElementById('cart-variant-id');
    const qtyInput = document.getElementById('qty-input');
    const variantError = document.getElementById('variant-error');
    const stockInfo = document.getElementById('variant-stock-info');
    const priceDisplay = document.getElementById('product-price');
    const basePrice = parseFloat(document.getElementById('variant-selector')?.dataset?.basePrice || 0);

    function getSelectedAttributes() {
        const attrs = {};
        document.querySelectorAll('.variant-attr-btn.selected').forEach(function(btn) {
            attrs[btn.dataset.attrName] = btn.dataset.attrValue;
        });
        return attrs;
    }

    function findMatchingVariant() {
        const selected = getSelectedAttributes();
        const totalAttrs = Object.keys(variantAttrs).length;
        if (Object.keys(selected).length < totalAttrs) return null;

        for (let v of variants) {
            const vAttrs = JSON.parse(v.attributes) || {};
            let match = true;
            for (let key in selected) {
                if (vAttrs[key] !== selected[key]) { match = false; break; }
            }
            if (match) return v;
        }
        return null;
    }

    function updateVariantDisplay() {
        const variant = findMatchingVariant();
        if (variant) {
            selectedVariantId.value = variant.id;
            cartVariantId.value = variant.id;
            const vPrice = variant.price ? parseFloat(variant.price) : basePrice;
            priceDisplay.textContent = 'Rp ' + vPrice.toLocaleString('id-ID');
            qtyInput.max = variant.stock;
            if (parseInt(qtyInput.value) > variant.stock) qtyInput.value = variant.stock;
            stockInfo.textContent = '✅ ' + variant.stock + ' units available';
            stockInfo.className = 'text-xs font-bold mt-1 ' + (variant.stock > 0 ? 'text-[#22C55E]' : 'text-[#EF4444]');
            stockInfo.classList.remove('hidden');
            variantError.classList.add('hidden');
        } else {
            selectedVariantId.value = '';
            cartVariantId.value = '';
            priceDisplay.textContent = 'Rp ' + basePrice.toLocaleString('id-ID');
            stockInfo.classList.add('hidden');
        }
    }

    document.querySelectorAll('.variant-attr-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (this.classList.contains('opacity-40')) return;
            const group = this.closest('[data-attr-name]');
            group.querySelectorAll('.variant-attr-btn').forEach(function(b) {
                b.classList.remove('!bg-[#FFDE4D]', '!border-[#FFDE4D]', 'selected');
            });
            this.classList.add('!bg-[#FFDE4D]', '!border-[#FFDE4D]', 'selected');
            updateVariantDisplay();
        });
    });
}

// Size selector (simple)
const sizeOptions = document.querySelectorAll('.size-option');
const selectedSize = document.getElementById('selected_size');
const cartSize = document.getElementById('cart-size');
const qtyInput = document.getElementById('qty-input');
const sizeError = document.getElementById('size-error');

sizeOptions.forEach(btn => {
    btn.addEventListener('click', function() {
        if (this.disabled) return;
        sizeOptions.forEach(b => b.classList.remove('!bg-[#FFDE4D]', '!border-[#FFDE4D]'));
        this.classList.add('!bg-[#FFDE4D]', '!border-[#FFDE4D]');
        selectedSize.value = this.dataset.size;
        cartSize.value = this.dataset.size;
        const maxStock = parseInt(this.dataset.stock);
        qtyInput.max = maxStock;
        if (parseInt(qtyInput.value) > maxStock) qtyInput.value = maxStock;
        sizeError.classList.add('hidden');
    });
});

document.getElementById('add-to-cart-form')?.addEventListener('submit', function(e) {
    <?php if (!empty($variantAttrs)): ?>
    if (!document.getElementById('selected-variant-id').value) {
        e.preventDefault();
        document.getElementById('variant-error').classList.remove('hidden');
    }
    <?php elseif (!empty($sizes)): ?>
    if (!selectedSize.value) { e.preventDefault(); sizeError.classList.remove('hidden'); }
    <?php endif; ?>
});

function updateStockInfo() {
    const selectedBtn = document.querySelector('.size-option.!bg-\\[\\#FFDE4D\\]');
    if (selectedBtn) {
        const maxStock = parseInt(selectedBtn.dataset.stock);
        if (parseInt(qtyInput.value) > maxStock) qtyInput.value = maxStock;
    }
}

// Star rating
const starBtns = document.querySelectorAll('.star-btn');
const ratingValue = document.getElementById('rating-value');

starBtns.forEach((btn, index) => {
    btn.addEventListener('click', function() {
        const val = parseInt(this.dataset.value);
        ratingValue.value = val;
        starBtns.forEach((s, i) => {
            if (i < val) { s.style.opacity = '1'; s.style.color = '#FFDE4D'; }
            else { s.style.opacity = '0.3'; s.style.color = ''; }
        });
    });
    btn.addEventListener('mouseenter', function() {
        const val = parseInt(this.dataset.value);
        starBtns.forEach((s, i) => { s.style.opacity = i < val ? '0.7' : '0.2'; });
    });
    btn.addEventListener('mouseleave', function() {
        const curVal = parseInt(ratingValue.value);
        starBtns.forEach((s, i) => { s.style.opacity = i < curVal ? '1' : '0.3'; });
    });
});

(function() {
    const curVal = parseInt(ratingValue.value);
    starBtns.forEach((s, i) => {
        if (i < curVal) {
            s.style.opacity = '1';
            s.style.color = '#FFDE4D';
        } else {
            s.style.opacity = '0.3';
            s.style.color = '';
        }
    });
    if (typeof initVariantSelector === 'function') initVariantSelector();
})();
</script>
<?= $this->endSection() ?>
