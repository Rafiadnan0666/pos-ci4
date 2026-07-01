<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= isset($this->data['title']) ? esc($this->data['title']) . ' - ' : '' ?>Outdoor Gear Store</title>
    <meta name="description" content="Premium outdoor gear for your next adventure" />
    <link rel="stylesheet" href="<?= base_url('css/tailwind.css') ?>" />
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🏔️</text></svg>" />
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
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
                    <span class="font-bold text-xs"><?= esc(session()->get('name')) ?></span>
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
                <span class="font-bold"><?= esc(session('message')) ?></span>
            </div>
        </div>
        <?php endif; ?>

        <?php if (session()->has('error')): ?>
        <div class="max-w-7xl mx-auto px-4 mt-4">
            <div class="neo-card-orange flex items-center gap-3">
                <span class="text-xl">✗</span>
                <span class="font-bold"><?= esc(session('error')) ?></span>
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
    <script src="<?= base_url('js/store.js') ?>"></script>
</body>
</html>
