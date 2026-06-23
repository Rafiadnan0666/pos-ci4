<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= isset($title) ? $title . ' - ' : '' ?>Outdoor Gear Store</title>
    <meta name="description" content="Premium outdoor gear for every adventure" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        neo: { yellow: '#FFDE4D', cyan: '#06B6D4', orange: '#F97316', green: '#22C55E', pink: '#EC4899', lime: '#A3E635', violet: '#8B5CF6', red: '#EF4444', white: '#F4F2EE', black: '#000000' },
                    },
                    fontFamily: { heading: ['"Space Grotesk"', 'Inter', 'sans-serif'], body: ['Inter', 'system-ui', 'sans-serif'] },
                    boxShadow: { neo: '4px 4px 0px 0px rgba(0,0,0,1)', 'neo-lg': '6px 6px 0px 0px rgba(0,0,0,1)' },
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700&family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet" />
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🏔️</text></svg>" />
    <style>
        body { font-family: Inter, system-ui, sans-serif; background-color: #F4F2EE; color: #000; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Space Grotesk', Inter, sans-serif; font-weight: 800; text-transform: uppercase; letter-spacing: -0.02em; }
        .neo-card { background: #fff; border: 4px solid #000; box-shadow: 4px 4px 0px 0px rgba(0,0,0,1); padding: 1rem; transition: all 150ms; }
        .neo-card:hover { translate: -1px -1px; box-shadow: 6px 6px 0px 0px rgba(0,0,0,1); }
        .neo-card-yellow { background: #FFDE4D; border: 4px solid #000; box-shadow: 4px 4px 0px 0px rgba(0,0,0,1); padding: 1rem; transition: all 150ms; }
        .neo-card-yellow:hover { translate: -1px -1px; box-shadow: 6px 6px 0px 0px rgba(0,0,0,1); }
        .neo-card-cyan { background: #06B6D4; border: 4px solid #000; box-shadow: 4px 4px 0px 0px rgba(0,0,0,1); padding: 1rem; color: #fff; transition: all 150ms; }
        .neo-card-cyan:hover { translate: -1px -1px; box-shadow: 6px 6px 0px 0px rgba(0,0,0,1); }
        .neo-card-orange { background: #F97316; border: 4px solid #000; box-shadow: 4px 4px 0px 0px rgba(0,0,0,1); padding: 1rem; color: #fff; transition: all 150ms; }
        .neo-card-orange:hover { translate: -1px -1px; box-shadow: 6px 6px 0px 0px rgba(0,0,0,1); }
        .neo-card-green { background: #22C55E; border: 4px solid #000; box-shadow: 4px 4px 0px 0px rgba(0,0,0,1); padding: 1rem; transition: all 150ms; }
        .neo-card-green:hover { translate: -1px -1px; box-shadow: 6px 6px 0px 0px rgba(0,0,0,1); }
        .neo-btn { font-family: 'Space Grotesk', Inter, sans-serif; font-weight: 700; text-transform: uppercase; font-size: .875rem; padding: .75rem 1.5rem; border: 4px solid #000; box-shadow: 4px 4px 0px 0px rgba(0,0,0,1); cursor: pointer; transition: all 75ms; display: inline-flex; align-items: center; justify-content: center; gap: .5rem; }
        .neo-btn:hover { translate: 2px 2px; box-shadow: 2px 2px 0px 0px #000; }
        .neo-btn:active { translate: 4px 4px; box-shadow: none; }
        .neo-btn-yellow { background: #FFDE4D; color: #000; }
        .neo-btn-cyan { background: #06B6D4; color: #fff; }
        .neo-btn-orange { background: #F97316; color: #fff; }
        .neo-btn-green { background: #22C55E; color: #000; }
        .neo-btn-red { background: #EF4444; color: #fff; }
        .neo-btn-white { background: #fff; color: #000; }
        .neo-input { width: 100%; padding: .75rem 1rem; border: 4px solid #000; background: #fff; font-weight: 700; font-size: .875rem; transition: all 100ms; }
        .neo-input:focus { outline: none; box-shadow: 4px 4px 0px 0px rgba(0,0,0,1); background: #FFDE4D; }
        .neo-badge { display: inline-block; padding: .25rem .75rem; border: 2px solid #000; font-family: 'Space Grotesk', Inter, sans-serif; font-weight: 700; font-size: .75rem; text-transform: uppercase; transition: all 100ms; }
        .neo-badge:hover { translate: -1px -1px; box-shadow: 2px 2px 0px 0px #000; }
        .neo-divider { border-top: 4px solid #000; }
        @keyframes float { 0%,100%{translate:0} 50%{translate:0 -6px} }
        @keyframes wiggle { 0%,100%{rotate:0} 25%{rotate:-3deg} 75%{rotate:3deg} }
        .animate-float { animation: float 3s ease-in-out infinite; }
        .animate-wiggle:hover { animation: wiggle .3s ease-in-out; }
        .neo-card, .neo-btn { animation: none; }
        [data-aos] { pointer-events: none; }
        [data-aos].aos-animate { pointer-events: auto; }
    </style>
</head>
<body class="bg-[#F4F2EE] text-black min-h-screen flex flex-col">
    <header class="bg-[#FFDE4D] border-b-4 border-black">
        <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="<?= base_url('/') ?>" class="flex items-center gap-3 no-underline text-black">
                <span class="text-3xl">🏔️</span>
                <h1 class="text-2xl md:text-3xl font-black tracking-tighter">OUTDOOR GEAR</h1>
            </a>
            <nav class="flex items-center gap-4">
                <a href="<?= base_url('/products') ?>" class="neo-btn-white text-sm !px-4 !py-2">Products</a>
                <?php
                $cartCount = 0;
                $buyerCart = session()->get('buyer_cart') ?? [];
                foreach ($buyerCart as $item) { $cartCount += $item['quantity']; }
                ?>
                <a href="<?= base_url('cart') ?>" class="neo-btn-yellow text-sm !px-4 !py-2 relative">
                    Cart <?php if ($cartCount > 0): ?><span class="absolute -top-2 -right-2 bg-[#EF4444] text-white text-xs font-bold px-2 py-0.5 border-2 border-black"><?= $cartCount ?></span><?php endif; ?>
                </a>
                <?php if (session()->get('isLoggedIn')): ?>
                <a href="<?= base_url('orders') ?>" class="neo-btn-white text-xs !px-3 !py-1.5">My Orders</a>
                <?php if (session()->get('role') === 'owner'): ?>
                <a href="<?= base_url('admin/dashboard') ?>" class="neo-btn-cyan text-xs !px-3 !py-1.5">Admin</a>
                <a href="<?= base_url('admin/products') ?>" class="neo-btn-white text-xs !px-3 !py-1.5">Products</a>
                <a href="<?= base_url('admin/categories') ?>" class="neo-btn-white text-xs !px-3 !py-1.5">Categories</a>
                <a href="<?= base_url('pos') ?>" class="neo-btn-white text-xs !px-3 !py-1.5">POS</a>
                <?php endif; ?>
                <span class="font-bold text-xs"><?= session()->get('name') ?></span>
                <a href="<?= base_url('logout') ?>" class="neo-btn-red text-xs !px-3 !py-1.5">Logout</a>
                <?php else: ?>
                <a href="<?= base_url('login') ?>" class="neo-btn-white text-sm !px-4 !py-2">Login</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="flex-1">
        <?php if (session()->has('message')): ?>
        <div class="max-w-7xl mx-auto px-4 mt-4">
            <div style="background:#22C55E;border:4px solid #000;box-shadow:4px 4px 0px 0px rgba(0,0,0,1);padding:1rem;display:flex;align-items:center;gap:.75rem;font-weight:700;">
                <span>✓</span> <?= session('message') ?>
            </div>
        </div>
        <?php endif; ?>
        <?php if (session()->has('error')): ?>
        <div class="max-w-7xl mx-auto px-4 mt-4">
            <div style="background:#F97316;color:#fff;border:4px solid #000;box-shadow:4px 4px 0px 0px rgba(0,0,0,1);padding:1rem;display:flex;align-items:center;gap:.75rem;font-weight:700;">
                <span>✕</span> <?= session('error') ?>
            </div>
        </div>
        <?php endif; ?>
        <?= $this->renderSection('content') ?>
    </main>

    <footer style="background:#000;color:#fff;border-top:4px solid #000;margin-top:3rem;">
        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div><h3 style="color:#FFDE4D;font-size:1.125rem;margin-bottom:1rem;">OUTDOOR GEAR</h3><p class="text-sm" style="color:#9CA3AF;">Your trusted source for premium outdoor equipment.</p></div>
                <div><h4 style="color:#06B6D4;font-size:.875rem;margin-bottom:.75rem;text-transform:uppercase;font-weight:700;">SHOP</h4>
                    <ul class="space-y-2 text-sm" style="color:#9CA3AF;">
                        <li><a href="<?= base_url('/products?category=Tents') ?>" class="hover:text-white">Tents</a></li>
                        <li><a href="<?= base_url('/products?category=Packs') ?>" class="hover:text-white">Backpacks</a></li>
                    </ul>
                </div>
                <div><h4 style="color:#F97316;font-size:.875rem;margin-bottom:.75rem;text-transform:uppercase;font-weight:700;">CONTACT</h4>
                    <ul class="space-y-2 text-sm" style="color:#9CA3AF;">
                        <li>hello@outdoorgear.store</li>
                        <li>+62 21 1234 5678</li>
                    </ul>
                </div>
            </div>
            <div class="neo-divider" style="border-color:#374151;margin:1.5rem 0;"></div>
            <p class="text-center text-xs" style="color:#6B7280;">&copy; <?= date('Y') ?> Outdoor Gear Store</p>
        </div>
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>AOS.init({ duration: 600, once: true });</script>
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="<?= trim(env('MIDTRANS_CLIENT_KEY', '')) ?>"></script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
