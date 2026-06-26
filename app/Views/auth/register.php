<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="min-h-[70vh] flex items-center justify-center px-4 py-8">
    <div class="w-full max-w-md" data-aos="fade-up">
        <div class="text-center mb-6">
            <span class="text-6xl block mb-2">🏔️</span>
            <h1 class="text-3xl font-black">JOIN THE ADVENTURE</h1>
            <p class="text-sm font-bold mt-1 opacity-60">Create your account</p>
        </div>

        <div class="neo-card">
            <?php if (session()->has('errors')): ?>
            <div class="bg-[#EF4444] text-white border-4 border-black p-3 mb-4 text-sm font-bold space-y-1">
                <?php foreach (session('errors') as $err): ?>
                <div class="flex items-center gap-2">✕ <?= esc($err) ?></div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <form action="<?= base_url('register') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="space-y-4">
                    <div>
                        <label class="block font-heading font-bold text-sm mb-1">FULL NAME</label>
                        <input type="text" name="name" value="<?= esc(old('name')) ?>" required autofocus class="neo-input" placeholder="John Doe" />
                    </div>
                    <div>
                        <label class="block font-heading font-bold text-sm mb-1">EMAIL</label>
                        <input type="email" name="email" value="<?= esc(old('email')) ?>" required class="neo-input" placeholder="your@email.com" />
                    </div>
                    <div>
                        <label class="block font-heading font-bold text-sm mb-1">PASSWORD</label>
                        <input type="password" name="password" required class="neo-input" placeholder="Min. 6 characters" />
                    </div>
                    <div>
                        <label class="block font-heading font-bold text-sm mb-1">CONFIRM PASSWORD</label>
                        <input type="password" name="password_confirm" required class="neo-input" placeholder="Repeat password" />
                    </div>
                    <button type="submit" class="neo-btn-cyan w-full text-base">CREATE ACCOUNT</button>
                </div>
            </form>

            <div class="neo-divider my-4"></div>
            <p class="text-center text-sm font-bold">
                Already a member?
                <a href="<?= base_url('login') ?>" class="underline font-black">Sign in</a>
            </p>
        </div>

        <p class="text-center mt-4">
            <a href="<?= base_url('/products') ?>" class="font-bold text-sm underline">← Back to Store</a>
        </p>
    </div>
</div>
<?= $this->endSection() ?>
