<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<div class="max-w-2xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6" data-aos="fade-down">
        <h1 class="text-3xl font-black flex items-center gap-3">
            <span>👤</span> MY PROFILE
        </h1>
        <a href="<?= base_url('/') ?>" class="neo-btn-white text-sm">← Home</a>
    </div>

    <?php if (session('errors')): ?>
    <div class="neo-card bg-[#FEE2E2] mb-6" data-aos="fade-up">
        <h3 class="font-black text-sm">⚠️ ERRORS</h3>
        <ul class="mt-2 text-sm space-y-1">
            <?php foreach (session('errors') as $e): ?><li>• <?= $e ?></li><?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <div class="neo-card mb-6 text-center" data-aos="fade-up">
        <div class="w-24 h-24 border-4 border-black mx-auto mb-3 flex items-center justify-center font-black text-3xl overflow-hidden bg-[#FFDE4D]">
            <?php if ($user->avatar): ?>
            <img src="<?= base_url(esc($user->avatar)) ?>" class="w-full h-full object-cover" alt="" />
            <?php else: ?>
            <?= strtoupper(substr($user->name, 0, 1)) ?>
            <?php endif; ?>
        </div>
        <h2 class="font-black text-xl"><?= esc($user->name) ?></h2>
        <p class="text-sm opacity-60"><?= esc($user->email) ?> · <?= strtoupper($user->role) ?></p>
    </div>

    <div class="neo-card" data-aos="fade-up">
        <form method="POST" action="<?= base_url('profile/update') ?>" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="space-y-4">
                <div>
                    <label class="block font-bold text-sm mb-1">Full Name *</label>
                    <input type="text" name="name" class="neo-input" value="<?= esc(old('name', $user->name)) ?>" required />
                </div>
                <div>
                    <label class="block font-bold text-sm mb-1">Email *</label>
                    <input type="email" name="email" class="neo-input" value="<?= esc(old('email', $user->email)) ?>" required />
                </div>
                <div>
                    <label class="block font-bold text-sm mb-1">Phone</label>
                    <input type="text" name="phone" class="neo-input" placeholder="08xxxxxxxxxx" value="<?= esc(old('phone', $user->phone ?? '')) ?>" />
                </div>
                <div>
                    <label class="block font-bold text-sm mb-1">Address</label>
                    <textarea name="address" class="neo-input" rows="3" placeholder="Your shipping address"><?= esc(old('address', $user->address ?? '')) ?></textarea>
                </div>
                <div>
                    <label class="block font-bold text-sm mb-1">Profile Photo</label>
                    <input type="file" name="avatar" class="neo-input !p-2" accept="image/jpeg,image/png,image/webp,image/gif" />
                    <p class="text-xs opacity-60 mt-1">Accepted: JPG, PNG, WebP, GIF — Max 2MB</p>
                    <?php if ($user->avatar): ?>
                    <div class="mt-2 flex items-center gap-3">
                        <img src="<?= base_url($user->avatar) ?>" class="w-12 h-12 border-4 border-black object-cover" alt="" />
                        <label class="text-xs font-bold flex items-center gap-1">
                            <input type="checkbox" name="remove_avatar" value="1" /> Remove current photo
                        </label>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mt-6 flex gap-3">
                <button type="submit" class="neo-btn-cyan flex-1">💾 Save Changes</button>
                <a href="<?= base_url('/') ?>" class="neo-btn-white">Cancel</a>
            </div>
        </form>
    </div>

    <?php if (session()->get('role') === 'buyer'): ?>
    <div class="mt-6 flex gap-3 justify-center">
        <a href="<?= base_url('orders') ?>" class="neo-btn-white text-sm">📦 My Orders</a>
    </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
