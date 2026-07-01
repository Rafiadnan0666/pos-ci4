<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= isset($title) ? esc($title) . ' - ' : '' ?>Outdoor Gear Store</title>
    <meta name="description" content="Premium outdoor gear for every adventure" />
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        neo: { yellow: '#FFDE4D', cyan: '#06B6D4', orange: '#F97316', green: '#22C55E', pink: '#EC4899', lime: '#A3E635', violet: '#8B5CF6', red: '#EF4444', white: '#F4F2EE', black: '#000000' },
                    },
                    fontFamily: { heading: ['"Space Grotesk"', 'Inter', 'sans-serif'], body: ['Inter', 'system-ui', 'sans-serif'] },
                    boxShadow: { neo: '4px 4px 0px 0px rgba(0,0,0,1)', 'neo-lg': '6px 6px 0px 0px rgba(0,0,0,1)', 'neo-sm': '2px 2px 0px 0px rgba(0,0,0,1)' },
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700&family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet" />
    <?= $this->renderSection('styles') ?>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🏔️</text></svg>" />
    <style>
        body { font-family: Inter, system-ui, sans-serif; background-color: #F4F2EE; color: #000; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Space Grotesk', Inter, sans-serif; font-weight: 800; text-transform: uppercase; letter-spacing: -0.02em; }
        a { color: #000; text-decoration: underline; }
        a:hover { color: #06B6D4; }

        /* ─── NEO CARDS ─── */
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
        .neo-card-pink { background: #EC4899; border: 4px solid #000; box-shadow: 4px 4px 0px 0px rgba(0,0,0,1); padding: 1rem; color: #fff; transition: all 150ms; }
        .neo-card-pink:hover { translate: -1px -1px; box-shadow: 6px 6px 0px 0px rgba(0,0,0,1); }
        .neo-card-lime { background: #A3E635; border: 4px solid #000; box-shadow: 4px 4px 0px 0px rgba(0,0,0,1); padding: 1rem; transition: all 150ms; }
        .neo-card-lime:hover { translate: -1px -1px; box-shadow: 6px 6px 0px 0px rgba(0,0,0,1); }
        .neo-card-violet { background: #8B5CF6; border: 4px solid #000; box-shadow: 4px 4px 0px 0px rgba(0,0,0,1); padding: 1rem; color: #fff; transition: all 150ms; }
        .neo-card-violet:hover { translate: -1px -1px; box-shadow: 6px 6px 0px 0px rgba(0,0,0,1); }

        /* ─── NEO BUTTONS ─── */
        .neo-btn { font-family: 'Space Grotesk', Inter, sans-serif; font-weight: 700; text-transform: uppercase; font-size: .875rem; padding: .75rem 1.5rem; border: 4px solid #000; box-shadow: 4px 4px 0px 0px rgba(0,0,0,1); cursor: pointer; transition: all 75ms; display: inline-flex; align-items: center; justify-content: center; gap: .5rem; text-decoration: none; line-height: 1.2; }
        .neo-btn:hover { translate: 2px 2px; box-shadow: 2px 2px 0px 0px #000; text-decoration: none; }
        .neo-btn:active { translate: 4px 4px; box-shadow: none; }
        .neo-btn:disabled { opacity: 0.5; cursor: not-allowed; pointer-events: none; }
        .neo-btn-yellow { background: #FFDE4D; color: #000; }
        .neo-btn-cyan { background: #06B6D4; color: #fff; }
        .neo-btn-orange { background: #F97316; color: #fff; }
        .neo-btn-green { background: #22C55E; color: #000; }
        .neo-btn-red { background: #EF4444; color: #fff; }
        .neo-btn-white { background: #fff; color: #000; }
        .neo-btn-pink { background: #EC4899; color: #fff; }
        .neo-btn-lime { background: #A3E635; color: #000; }
        .neo-btn-violet { background: #8B5CF6; color: #fff; }
        .neo-btn-black { background: #000; color: #fff; }

        /* ─── NEO INPUTS ─── */
        .neo-input { width: 100%; padding: .75rem 1rem; border: 4px solid #000; background: #fff; font-weight: 700; font-size: .875rem; transition: all 100ms; }
        .neo-input:focus { outline: none; box-shadow: 4px 4px 0px 0px rgba(0,0,0,1); background: #FFDE4D; }

        /* ─── NEO BADGES ─── */
        .neo-badge { display: inline-block; padding: .25rem .75rem; border: 2px solid #000; font-family: 'Space Grotesk', Inter, sans-serif; font-weight: 700; font-size: .75rem; text-transform: uppercase; transition: all 100ms; background: #fff; }
        .neo-badge:hover { translate: -1px -1px; box-shadow: 2px 2px 0px 0px #000; }
        .neo-badge-red { background: #EF4444; color: #fff; }
        .neo-badge-green { background: #22C55E; color: #000; }
        .neo-badge-yellow { background: #FFDE4D; color: #000; }
        .neo-badge-cyan { background: #06B6D4; color: #fff; }
        .neo-badge-orange { background: #F97316; color: #fff; }
        .neo-badge-pink { background: #EC4899; color: #fff; }
        .neo-badge-lime { background: #A3E635; color: #000; }
        .neo-badge-violet { background: #8B5CF6; color: #fff; }

        .neo-divider { border-top: 4px solid #000; }

        @keyframes float { 0%,100%{translate:0} 50%{translate:0 -6px} }
        @keyframes wiggle { 0%,100%{rotate:0} 25%{rotate:-3deg} 75%{rotate:3deg} }
        @keyframes pulse-neo { 0%,100%{box-shadow:4px 4px 0px 0px #000} 50%{box-shadow:6px 6px 0px 0px #000} }
        .animate-float { animation: float 3s ease-in-out infinite; }
        .animate-wiggle:hover { animation: wiggle .3s ease-in-out; }
        .animate-pulse-neo { animation: pulse-neo 1.5s ease-in-out infinite; }
        .neo-card, .neo-btn { animation: none; }
        [data-aos] { pointer-events: none; }
        [data-aos].aos-animate { pointer-events: auto; }
    </style>
</head>
<body class="bg-[#F4F2EE] text-black min-h-screen flex flex-col">
    <header class="bg-[#FFDE4D] border-b-4 border-black">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="<?= base_url('/') ?>" class="flex items-center gap-3 no-underline text-black">
                <span class="text-3xl">🏔️</span>
                <h1 class="text-xl md:text-2xl font-black tracking-tighter">OUTDOOR GEAR</h1>
            </a>
            <nav class="flex items-center gap-2 flex-wrap justify-end">
                <a href="<?= base_url('/products') ?>" class="neo-btn-white text-xs !px-3 !py-1.5">Products</a>
                <?php
                $cartCount = 0;
                $buyerCart = session()->get('buyer_cart') ?? [];
                foreach ($buyerCart as $item) { $cartCount += $item['quantity']; }
                ?>
                <a href="<?= base_url('cart') ?>" class="neo-btn-yellow text-xs !px-3 !py-1.5 relative">
                    🛒 Cart <?php if ($cartCount > 0): ?><span class="absolute -top-2 -right-2 bg-neo-red text-white text-xs font-bold px-1.5 py-0.5 border-2 border-black"><?= $cartCount ?></span><?php endif; ?>
                </a>
                <?php if (session()->get('isLoggedIn')): ?>
                <a href="<?= base_url('orders') ?>" class="neo-btn-white text-xs !px-2 !py-1">My Orders</a>
                <?php if (session()->get('role') === 'owner'): ?>
                <a href="<?= base_url('admin/dashboard') ?>" class="neo-btn-cyan text-xs !px-2 !py-1">Admin</a>
                <?php endif; ?>
                <a href="<?= base_url('profile') ?>" class="neo-btn-white text-xs !px-2 !py-1">Profile</a>
                <a href="<?= base_url('logout') ?>" class="neo-btn-red text-xs !px-2 !py-1">Logout</a>
                <?php else: ?>
                <a href="<?= base_url('login') ?>" class="neo-btn-white text-xs !px-3 !py-1.5">Login</a>
                <a href="<?= base_url('register') ?>" class="neo-btn-cyan text-xs !px-3 !py-1.5">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="flex-1">
        <?php if (session()->has('message')): ?>
        <div class="max-w-7xl mx-auto px-4 mt-4">
            <div class="neo-card-green flex items-center gap-3">
                <span>✓</span> <?= esc(session('message')) ?>
            </div>
        </div>
        <?php endif; ?>
        <?php if (session()->has('error')): ?>
        <div class="max-w-7xl mx-auto px-4 mt-4">
            <div class="neo-card-orange flex items-center gap-3">
                <span>✕</span> <?= esc(session('error')) ?>
            </div>
        </div>
        <?php endif; ?>
        <?php if (session()->has('errors')): ?>
        <div class="max-w-7xl mx-auto px-4 mt-4">
            <div style="background:#EF4444;color:#fff;border:4px solid #000;box-shadow:4px 4px 0px 0px rgba(0,0,0,1);padding:1rem;font-weight:700;">
                <?php foreach (session('errors') as $e): ?>
                <div class="flex items-center gap-2 text-sm">✕ <?= esc($e) ?></div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        <?= $this->renderSection('content') ?>
    </main>

    <!-- Custom Neo Modal -->
    <div id="neo-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-[100] hidden">
        <div class="bg-white border-4 border-black shadow-neo w-full max-w-sm mx-4 p-6">
            <div class="flex items-center gap-3 mb-4">
                <span id="neo-modal-icon" class="text-2xl">⚠️</span>
                <h3 id="neo-modal-title" class="font-heading font-bold text-lg">Confirm</h3>
            </div>
            <p id="neo-modal-message" class="text-sm font-bold mb-6"></p>
            <div id="neo-modal-actions" class="flex gap-2 justify-end">
                <button id="neo-modal-cancel" class="neo-btn-white text-sm hidden">Cancel</button>
                <button id="neo-modal-confirm" class="neo-btn-cyan text-sm">OK</button>
            </div>
        </div>
    </div>

    <script>
    window.showModal = function(opts) {
        return new Promise(function(resolve) {
            var modal = document.getElementById('neo-modal');
            var titleEl = document.getElementById('neo-modal-title');
            var msgEl = document.getElementById('neo-modal-message');
            var iconEl = document.getElementById('neo-modal-icon');
            var confirmBtn = document.getElementById('neo-modal-confirm');
            var cancelBtn = document.getElementById('neo-modal-cancel');

            titleEl.textContent = opts.title || 'Confirm';
            msgEl.textContent = opts.message || '';
            iconEl.textContent = opts.icon || '⚠️';
            confirmBtn.textContent = opts.confirmText || (opts.type === 'alert' ? 'OK' : 'Yes');
            confirmBtn.className = (opts.confirmClass || 'neo-btn-cyan') + ' text-sm';

            if (opts.type === 'alert') {
                cancelBtn.classList.add('hidden');
            } else {
                cancelBtn.classList.remove('hidden');
                cancelBtn.textContent = opts.cancelText || 'Cancel';
            }

            modal.classList.remove('hidden');

            function cleanup() {
                modal.classList.add('hidden');
                confirmBtn.onclick = null;
                cancelBtn.onclick = null;
            }

            confirmBtn.onclick = function() { cleanup(); resolve(true); };
            cancelBtn.onclick = function() { cleanup(); resolve(false); };
            modal.addEventListener('click', function handler(e) {
                if (e.target === modal) { cleanup(); modal.removeEventListener('click', handler); resolve(false); }
            });
        });
    };

    window.showConfirm = function(message) {
        return window.showModal({ type: 'confirm', title: 'Confirm', message: message, icon: '⚠️', confirmText: 'Yes', confirmClass: 'neo-btn-green', cancelText: 'Cancel' });
    };

    window.showAlert = function(message, title) {
        return window.showModal({ type: 'alert', title: title || 'Notice', message: message, icon: 'ℹ️', confirmText: 'OK', confirmClass: 'neo-btn-cyan' });
    };
    </script>

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
                        <li style="color: white;"><a href="https://wa.me/6281295064928?message=Hello!" target="_blank">+62 812-9506-4928</a></li>
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
    <script>
    (function() {
        const origFetch = window.fetch;
        window.fetch = function(url, opts) {
            opts = opts || {};
            opts.headers = opts.headers || {};
            if (opts.method && opts.method.toUpperCase() === 'POST') {
                var meta = document.querySelector('meta[name="csrf-token"]');
                if (meta && meta.content) {
                    opts.headers['X-CSRF-TOKEN'] = meta.content;
                }
            }
            return origFetch.call(this, url, opts).then(function(response) {
                var newToken = response.headers.get('X-CSRF-TOKEN') || response.headers.get('csrf-token');
                if (newToken) {
                    var meta = document.querySelector('meta[name="csrf-token"]');
                    if (meta) meta.content = newToken;
                }
                return response;
            });
        };
    })();
    </script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
