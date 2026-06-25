<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6" data-aos="fade-down">
        <h1 class="text-3xl font-black flex items-center gap-3">
            <span>💬</span> PRODUCT REVIEWS
        </h1>
        <div class="flex gap-2">
            <a href="<?= base_url('admin/dashboard') ?>" class="neo-btn-white text-sm">← Dashboard</a>
        </div>
    </div>

    <?php if (empty($reviews)): ?>
    <div class="neo-card text-center py-12">
        <span class="text-6xl block mb-4">💬</span>
        <h2 class="text-2xl font-black">No Reviews Yet</h2>
        <p class="text-sm mt-2">Reviews from customers will appear here.</p>
    </div>
    <?php else: ?>
    <div class="space-y-4">
        <?php foreach ($reviews as $r): ?>
        <div class="neo-card <?= $r->status === 'pending' ? '!bg-[#FEF3C7]' : '' ?>" data-aos="fade-up">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 border-2 border-black bg-[#FFDE4D] flex items-center justify-center font-bold text-sm overflow-hidden">
                            <?php if (isset($r->user_avatar) && $r->user_avatar): ?>
                            <img src="<?= base_url(esc($r->user_avatar)) ?>" class="w-full h-full object-cover" alt="" />
                            <?php else: ?>
                            <?= strtoupper(substr($r->user_name, 0, 1)) ?>
                            <?php endif; ?>
                        </div>
                        <div>
                            <p class="font-bold text-sm"><?= esc($r->user_name) ?></p>
                            <div class="flex items-center gap-1">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="text-sm <?= $i <= $r->rating ? '' : 'opacity-20' ?>">★</span>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                    <p class="text-xs opacity-60 mb-1">
                        Product: <a href="<?= base_url('product/' . $r->product_slug) ?>" class="underline font-bold"><?= esc($r->product_name) ?></a>
                        — <?= date('d M Y H:i', strtotime($r->created_at)) ?>
                    </p>
                    <?php if ($r->review): ?>
                    <p class="text-sm mt-2 bg-white border-2 border-black p-3"><?= nl2br(esc($r->review)) ?></p>
                    <?php endif; ?>
                    <?php if ($r->reply): ?>
                    <div class="mt-2 ml-6 pl-4 border-l-4 border-[#22C55E] bg-white p-3">
                        <p class="text-xs font-bold text-[#22C55E] mb-1">👤 Admin Reply</p>
                        <p class="text-sm"><?= nl2br(esc($r->reply)) ?></p>
                        <p class="text-[10px] opacity-60 mt-1"><?= date('d M Y H:i', strtotime($r->replied_at)) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="flex flex-col items-end gap-2 ml-4">
                    <span class="neo-badge text-[10px] <?= $r->status === 'approved' ? 'bg-[#22C55E]' : 'bg-[#F97316] text-white' ?>">
                        <?= strtoupper($r->status) ?>
                    </span>
                    <div class="flex gap-1">
                        <a href="<?= base_url('admin/reviews/reply/' . $r->id) ?>" class="neo-btn-cyan text-[10px] !px-2 !py-1">💬 Reply</a>
                        <form action="<?= base_url('admin/reviews/toggle-status/' . $r->id) ?>" method="POST" class="inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="neo-btn-white text-[10px] !px-2 !py-1">
                                <?= $r->status === 'approved' ? '🔒' : '✅' ?>
                            </button>
                        </form>
                        <form action="<?= base_url('admin/reviews/delete/' . $r->id) ?>" method="POST" onsubmit="return confirm('Delete this review?')" class="inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="neo-btn-white text-[10px] !px-2 !py-1 hover:!bg-[#EF4444] hover:!text-white">🗑️</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if ($pager): ?>
    <div class="mt-6 flex justify-center gap-2">
        <?= $pager->links('default', 'default') ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
