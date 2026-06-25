<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<div class="max-w-2xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6" data-aos="fade-down">
        <h1 class="text-3xl font-black flex items-center gap-3">
            <span>✏️</span> EDIT USER
        </h1>
        <a href="<?= base_url('admin/users') ?>" class="neo-btn-white text-sm">← Users</a>
    </div>

    <?php if (session('errors')): ?>
    <div class="neo-card bg-[#FEE2E2] mb-6" data-aos="fade-up">
        <h3 class="font-black text-sm">⚠️ VALIDATION ERRORS</h3>
        <ul class="mt-2 text-sm space-y-1">
            <?php foreach (session('errors') as $e): ?>
            <li>• <?= $e ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <div class="neo-card" data-aos="fade-up">
        <form method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="space-y-4">
                <div>
                    <label class="block font-bold text-sm mb-1">Name *</label>
                    <input type="text" name="name" class="neo-input" value="<?= esc(old('name', $user->name)) ?>" required />
                </div>
                <div>
                    <label class="block font-bold text-sm mb-1">Email *</label>
                    <input type="email" name="email" class="neo-input" value="<?= esc(old('email', $user->email)) ?>" required />
                </div>
                <div>
                    <label class="block font-bold text-sm mb-1">Phone</label>
                    <input type="text" name="phone" class="neo-input" value="<?= esc(old('phone', $user->phone ?? '')) ?>" />
                </div>
                <div>
                    <label class="block font-bold text-sm mb-1">Address</label>
                    <textarea name="address" class="neo-input" rows="2"><?= esc(old('address', $user->address ?? '')) ?></textarea>
                </div>
                <div>
                    <label class="block font-bold text-sm mb-1">Avatar / Profile Photo</label>
                    <div class="flex items-center gap-4 mb-2">
                        <div class="w-16 h-16 border-4 border-black flex items-center justify-center font-black text-2xl overflow-hidden <?= empty($user->avatar) ? 'bg-[#FFDE4D]' : 'bg-gray-100' ?>">
                            <?php if (!empty($user->avatar)): ?>
                            <img src="<?= base_url($user->avatar) ?>" class="w-full h-full object-cover" alt="" />
                            <?php else: ?>
                            <?= strtoupper(substr($user->name, 0, 1)) ?>
                            <?php endif; ?>
                        </div>
                        <div class="text-xs">
                            <p class="font-bold">Current <?= $user->avatar ? 'photo' : 'photo' ?> (initial shown if none)</p>
                            <?php if (!empty($user->avatar)): ?>
                            <label class="flex items-center gap-1 mt-1 cursor-pointer hover:text-[#EF4444]">
                                <input type="checkbox" name="remove_avatar" value="1" /> Remove photo
                            </label>
                            <?php endif; ?>
                        </div>
                    </div>
                    <input type="file" name="avatar" class="neo-input !p-2" accept="image/jpeg,image/png,image/webp,image/gif" />
                    <p class="text-xs opacity-60 mt-1">Accepted: JPG, PNG, WebP, GIF — Max 2MB</p>
                </div>
                <div>
                    <label class="block font-bold text-sm mb-1">Role *</label>
                    <select name="role" class="neo-input" required>
                        <option value="buyer" <?= (old('role', $user->role) === 'buyer') ? 'selected' : '' ?>>Buyer</option>
                        <option value="owner" <?= (old('role', $user->role) === 'owner') ? 'selected' : '' ?>>Owner (Admin)</option>
                    </select>
                </div>
                <?php if ($user->id === session()->get('user_id')): ?>
                <p class="text-sm font-bold text-[#F97316]">⚠️ You are editing your own account</p>
                <?php endif; ?>
            </div>

            <div class="mt-6 flex gap-3">
                <button type="submit" class="neo-btn-cyan flex-1">✏️ Update User</button>
                <a href="<?= base_url('admin/users') ?>" class="neo-btn-white">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>
