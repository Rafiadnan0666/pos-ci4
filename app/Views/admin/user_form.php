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
        <form method="POST">
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
