<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<div class="max-w-3xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6" data-aos="fade-down">
        <h1 class="text-3xl font-black flex items-center gap-3">
            <span>💬</span> REPLY TO REVIEW
        </h1>
        <a href="<?= base_url('admin/reviews') ?>" class="neo-btn-white text-sm">← All Reviews</a>
    </div>

    <div class="neo-card mb-6" data-aos="fade-up">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 border-2 border-black bg-[#FFDE4D] flex items-center justify-center font-bold text-sm overflow-hidden">
                <?php if ($review->user_avatar ?? null): ?>
                <img src="<?= base_url($review->user_avatar) ?>" class="w-full h-full object-cover" alt="" />
                <?php else: ?>
                <?= strtoupper(substr($review->user_name ?? '?', 0, 1)) ?>
                <?php endif; ?>
            </div>
            <div>
                <p class="font-bold text-sm"><?= esc($review->user_name ?? 'User') ?></p>
                <div class="flex items-center gap-1">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                    <span class="text-sm <?= $i <= $review->rating ? '' : 'opacity-20' ?>">★</span>
                    <?php endfor; ?>
                </div>
            </div>
            <span class="text-xs opacity-60 ml-auto"><?= date('d M Y H:i', strtotime($review->created_at)) ?></span>
        </div>
        <?php if ($review->review): ?>
        <p class="text-sm bg-gray-100 border-2 border-black p-3"><?= nl2br(esc($review->review)) ?></p>
        <?php endif; ?>
    </div>

    <?php if ($review->reply): ?>
    <div class="neo-card bg-[#F0FDF4] mb-6" data-aos="fade-up">
        <p class="text-xs font-bold text-[#22C55E] mb-2">Previous Reply:</p>
        <p class="text-sm"><?= nl2br(esc($review->reply)) ?></p>
        <p class="text-[10px] opacity-60 mt-1"><?= date('d M Y H:i', strtotime($review->replied_at)) ?></p>
    </div>
    <?php endif; ?>

    <div class="neo-card" data-aos="fade-up">
        <form method="POST">
            <?= csrf_field() ?>
            <div class="mb-4">
                <label class="block font-bold text-sm mb-1">Your Reply</label>
                <textarea name="reply" rows="5" class="neo-input" placeholder="Write your reply to this review..."><?= old('reply', $review->reply ?? '') ?></textarea>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="neo-btn-cyan flex-1">💬 Submit Reply</button>
                <a href="<?= base_url('admin/reviews') ?>" class="neo-btn-white">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
