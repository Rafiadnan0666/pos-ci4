<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>POS Dashboard - Outdoor Gear Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { neo: { yellow: '#FFDE4D', cyan: '#06B6D4', orange: '#F97316', green: '#22C55E', red: '#EF4444', white: '#F4F2EE', black: '#000000' } }, fontFamily: { heading: ['"Space Grotesk"', 'Inter', 'sans-serif'] }, boxShadow: { neo: '4px 4px 0px 0px rgba(0,0,0,1)' } } } }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700&family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet" />
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🏕️</text></svg>" />
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
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
        .neo-input { width: 100%; padding: .75rem 1rem; border: 4px solid #000; background: #fff; font-weight: 700; font-size: .875rem; }
        .neo-input:focus { outline: none; box-shadow: 4px 4px 0px 0px rgba(0,0,0,1); background: #FFDE4D; }
        .neo-badge { display: inline-block; padding: .25rem .75rem; border: 2px solid #000; font-family: 'Space Grotesk', Inter, sans-serif; font-weight: 700; font-size: .75rem; text-transform: uppercase; }
        .neo-divider { border-top: 4px solid #000; }
    </style>
</head>
<body style="background:#F4F2EE;font-family:Inter,system-ui,sans-serif;min-height:100vh;">
    <header style="background:#000;color:#fff;border-bottom:4px solid #FFDE4D;">
        <div class="max-w-full mx-auto" style="padding:.75rem 1rem;display:flex;align-items:center;justify-content:space-between;">
            <div style="display:flex;align-items:center;gap:.75rem;">
                <span style="font-size:1.5rem;">🏕️</span>
                <h1 style="font-family:'Space Grotesk',Inter,sans-serif;font-weight:800;text-transform:uppercase;font-size:1.25rem;letter-spacing:-.02em;">POS DASHBOARD</h1>
                <span style="display:inline-block;padding:.25rem .75rem;border:2px solid #000;font-family:'Space Grotesk',Inter,sans-serif;font-weight:700;font-size:.75rem;text-transform:uppercase;background:#FFDE4D;color:#000;">OFFLINE</span>
            </div>
            <div style="display:flex;align-items:center;gap:.5rem;">
                <a href="<?= base_url('admin/dashboard') ?>" class="neo-btn-cyan" style="padding:.5rem 1rem;font-size:.75rem;">📊 Admin</a>
                <span style="font-size:.875rem;color:#9CA3AF;" id="clock"></span>
                <a href="<?= base_url('pos/logout') ?>" class="neo-btn-red" style="padding:.5rem 1rem;font-size:.75rem;">Logout</a>
            </div>
        </div>
    </header>

    <?php if (session()->has('message')): ?>
    <div class="max-w-full mx-auto px-4 mt-4">
        <div class="neo-card-green flex items-center gap-3">
            <span>✓</span> <?= esc(session('message')) ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (session()->has('error')): ?>
    <div class="max-w-full mx-auto px-4 mt-4">
        <div class="neo-card-orange flex items-center gap-3">
            <span>✕</span> <?= esc(session('error')) ?>
        </div>
    </div>
    <?php endif; ?>

    <?= $this->renderSection('content') ?>

    <!-- Custom Neo Modal -->
    <div id="neo-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-[100] hidden">
        <div class="bg-white border-4 border-black shadow-neo w-full max-w-sm mx-4 p-6" data-aos="zoom-in">
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
            var actionsEl = document.getElementById('neo-modal-actions');
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

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>AOS.init({ duration: 400, once: true });</script>
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="<?= trim(env('MIDTRANS_CLIENT_KEY', '')) ?>"></script>
    <script>
    window.csrfTokenName = '<?= csrf_token() ?>';
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
    <script src="<?= base_url('js/pos.js') ?>"></script>
</body>
</html>
