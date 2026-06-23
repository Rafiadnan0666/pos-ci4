<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= isset($this->data['title']) ? $this->data['title'] . ' - ' : '' ?>Outdoor Gear Store</title>
    <meta name="description" content="Premium outdoor gear for your next adventure" />
    <link rel="stylesheet" href="<?= base_url('css/tailwind.css') ?>" />
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🏔️</text></svg>" />
</head>
<body class="min-h-screen flex flex-col">
    <header class="bg-neo-yellow border-b-4 border-black">
        <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="<?= base_url('/') ?>" class="flex items-center gap-3">
                <span class="text-3xl">🏔️</span>
                <h1 class="text-2xl md:text-3xl font-black tracking-tighter">OUTDOOR GEAR</h1>
            </a>
            <nav class="flex items-center gap-4">
                <a href="<?= base_url('/') ?>" class="neo-btn-white text-sm !px-4 !py-2">Catalog</a>
                <?php
                $cartCount = 0;
                $buyerCart = session()->get('buyer_cart') ?? [];
                foreach ($buyerCart as $item) { $cartCount += $item['quantity']; }
                ?>
                <a href="<?= base_url('cart') ?>" class="neo-btn-yellow text-sm !px-4 !py-2 relative">
                    Cart
                    <?php if ($cartCount > 0): ?>
                    <span class="absolute -top-2 -right-2 bg-neo-red text-white text-xs font-bold px-2 py-0.5 border-2 border-black"><?= $cartCount ?></span>
                    <?php endif; ?>
                </a>
                <?php if (session()->get('isLoggedIn')): ?>
                <div class="flex items-center gap-2">
                    <span class="font-bold text-xs"><?= session()->get('name') ?></span>
                    <a href="<?= base_url('logout') ?>" class="neo-btn-red text-xs !px-3 !py-1.5">Logout</a>
                </div>
                <?php else: ?>
                <a href="<?= base_url('login') ?>" class="neo-btn-white text-sm !px-4 !py-2">Login</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="flex-1">
        <?php if (session()->has('message')): ?>
        <div class="max-w-7xl mx-auto px-4 mt-4">
            <div class="neo-card-green flex items-center gap-3">
                <span class="text-xl">✓</span>
                <span class="font-bold"><?= session('message') ?></span>
            </div>
        </div>
        <?php endif; ?>

        <?php if (session()->has('error')): ?>
        <div class="max-w-7xl mx-auto px-4 mt-4">
            <div class="neo-card-orange flex items-center gap-3">
                <span class="text-xl">✗</span>
                <span class="font-bold"><?= session('error') ?></span>
            </div>
        </div>
        <?php endif; ?>

        <?= $this->renderSection('content') ?>
    </main>

    <footer class="bg-black text-white border-t-4 border-black mt-12">
        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-neo-yellow text-lg mb-4">OUTDOOR GEAR</h3>
                    <p class="text-sm text-gray-400">Your trusted source for premium outdoor equipment since 2024.</p>
                </div>
                <div>
                    <h4 class="text-neo-cyan text-sm mb-3">SHOP</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a href="<?= base_url('/?category=Tents') ?>" class="hover:text-white">Tents</a></li>
                        <li><a href="<?= base_url('/?category=Packs') ?>" class="hover:text-white">Backpacks</a></li>
                        <li><a href="<?= base_url('/?category=Apparel') ?>" class="hover:text-white">Apparel</a></li>
                        <li><a href="<?= base_url('/?category=Cooking') ?>" class="hover:text-white">Cooking</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-neo-orange text-sm mb-3">CONTACT</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li>hello@outdoorgear.store</li>
                        <li>+62 21 1234 5678</li>
                        <li>Jakarta, Indonesia</li>
                    </ul>
                </div>
            </div>
            <div class="neo-divider !border-gray-800 my-6"></div>
            <p class="text-center text-xs text-gray-600">&copy; <?= date('Y') ?> Outdoor Gear Store. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="<?= env('MIDTRANS_CLIENT_KEY') ?>"></script>
    <script src="<?= base_url('js/store.js') ?>"></script>
</body>
</html>
