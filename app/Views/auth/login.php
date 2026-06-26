<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="min-h-[70vh] flex items-center justify-center px-4 py-8">
    <div class="w-full max-w-md" data-aos="fade-up">
        <div class="text-center mb-6">
            <span class="text-6xl block mb-2">🏔️</span>
            <h1 class="text-3xl font-black">OUTDOOR GEAR</h1>
            <p class="text-sm font-bold mt-1 opacity-60">Sign in to your account</p>
        </div>

        <div class="neo-card-yellow">
            <?php if (session()->has('error')): ?>
            <div class="neo-card-red !bg-[#EF4444] !text-white mb-4 !p-3 text-sm font-bold flex items-center gap-2">
                <span>✕</span> <?= session('error') ?>
            </div>
            <?php endif; ?>
            <?php if (session()->has('message')): ?>
            <div class="neo-card-green mb-4 !p-3 text-sm font-bold flex items-center gap-2">
                <span>✓</span> <?= session('message') ?>
            </div>
            <?php endif; ?>

            <form action="<?= base_url('login') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="space-y-4">
                    <div>
                        <label class="block font-heading font-bold text-sm mb-1">EMAIL</label>
                        <input type="email" name="email" value="<?= esc(old('email')) ?>" required autofocus class="neo-input" placeholder="your@email.com" />
                    </div>
                    <div>
                        <label class="block font-heading font-bold text-sm mb-1">PASSWORD</label>
                        <input type="password" name="password" required class="neo-input" placeholder="••••••••" />
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="remember" id="remember" class="w-5 h-5 border-4 border-black" />
                        <label for="remember" class="font-bold text-sm">Remember me</label>
                    </div>
                    <button type="submit" class="neo-btn-cyan w-full text-base">SIGN IN</button>
                </div>
            </form>

            <div class="neo-divider my-4"></div>
            <p class="text-center text-sm font-bold">
                No account?
                <a href="<?= base_url('register') ?>" class="underline font-black">Register</a>
            </p>
        </div>

        <p class="text-center mt-4">
            <a href="<?= base_url('/products') ?>" class="font-bold text-sm underline">← Back to Store</a>
        </p>
        <p class="text-center mt-2 text-xs opacity-60">Demo: admin@outdoor.com / password123</p>
    </div>
</div>
<?= $this->endSection() ?>
