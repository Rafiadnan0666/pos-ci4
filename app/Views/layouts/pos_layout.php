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
    <div class="max-w-full mx-auto" style="padding:0 1rem;margin-top:1rem;">
        <div style="background:#22C55E;border:4px solid #000;box-shadow:4px 4px 0px 0px rgba(0,0,0,1);padding:1rem;display:flex;align-items:center;gap:.75rem;font-weight:700;font-size:.875rem;">
            <span>✓</span> <?= esc(session('message')) ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if (session()->has('error')): ?>
    <div class="max-w-full mx-auto" style="padding:0 1rem;margin-top:1rem;">
        <div style="background:#F97316;color:#fff;border:4px solid #000;box-shadow:4px 4px 0px 0px rgba(0,0,0,1);padding:1rem;display:flex;align-items:center;gap:.75rem;font-weight:700;font-size:.875rem;">
            <span>✕</span> <?= esc(session('error')) ?>
        </div>
    </div>
    <?php endif; ?>

    <?= $this->renderSection('content') ?>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>AOS.init({ duration: 400, once: true });</script>
    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="<?= trim(env('MIDTRANS_CLIENT_KEY', '')) ?>"></script>
    <script>
    window.csrfTokenName = '<?= csrf_token() ?>';
    (function() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        const token = meta ? meta.content : '';
        if (token) {
            const origFetch = window.fetch;
            window.fetch = function(url, opts) {
                opts = opts || {};
                opts.headers = opts.headers || {};
                if (opts.method && opts.method.toUpperCase() === 'POST') {
                    opts.headers['X-CSRF-TOKEN'] = token;
                }
                return origFetch.call(this, url, opts);
            };
        }
    })();
    </script>
    <script src="<?= base_url('js/pos.js') ?>"></script>
</body>
</html>
