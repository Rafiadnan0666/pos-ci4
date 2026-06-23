<?php
/** @var string $midtransKey */
/** @var string $midtransClient */
/** @var string $biteshipKey */
/** @var bool $envExists */
/** @var string $envPath */
/** @var array $midtransResult */
/** @var array $biteshipResult */
?>
<?= $this->extend('layout/main') ?>
<?= $this->section('content') ?>
<div class="max-w-4xl mx-auto px-4 py-8" data-aos="fade-up">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-black flex items-center gap-3">
            <span>🔌</span> API TEST
        </h1>
        <a href="<?= base_url('admin/dashboard') ?>" class="neo-btn-white text-sm">← Dashboard</a>
    </div>

    <div class="neo-card mb-6" data-aos="zoom-in">
        <h2 class="text-lg font-black mb-3 flex items-center gap-2">
            <span>📋</span> CONFIG STATUS
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div class="border-4 border-black p-3" style="background:<?= $midtransKey !== '(empty)' ? '#22C55E' : '#EF4444' ?>;color:#fff;">
                <span class="font-bold">Midtrans Server Key:</span>
                <span class="block font-mono text-xs mt-1"><?= $midtransKey ?></span>
            </div>
            <div class="border-4 border-black p-3" style="background:<?= $midtransClient !== '(empty)' ? '#22C55E' : '#EF4444' ?>;color:#fff;">
                <span class="font-bold">Midtrans Client Key:</span>
                <span class="block font-mono text-xs mt-1"><?= $midtransClient ?></span>
            </div>
            <div class="border-4 border-black p-3" style="background:<?= $biteshipKey !== '(empty)' ? '#22C55E' : '#EF4444' ?>;color:#fff;">
                <span class="font-bold">Biteship API Key:</span>
                <span class="block font-mono text-xs mt-1"><?= $biteshipKey ?></span>
            </div>
            <div class="border-4 border-black p-3" style="background:<?= $envExists ? '#22C55E' : '#EF4444' ?>;color:#fff;">
                <span class="font-bold">.env File:</span>
                <span class="block font-mono text-xs mt-1"><?= $envPath ?></span>
                <span class="block font-bold mt-1"><?= $envExists ? '✓ EXISTS' : '✕ NOT FOUND' ?></span>
            </div>
        </div>
    </div>

    <div class="neo-card mb-6" data-aos="fade-up">
        <h2 class="text-lg font-black mb-3 flex items-center gap-2">
            <span>💳</span> MIDTRANS TEST
        </h2>
        <?php if ($midtransKey === '(empty)'): ?>
        <div class="bg-[#EF4444] text-white border-4 border-black p-3 font-bold text-sm">MIDTRANS_SERVER_KEY is empty! Check .env file.</div>
        <?php elseif ($midtransResult['success'] ?? false): ?>
        <div class="bg-[#22C55E] border-4 border-black p-3 font-bold text-sm flex items-center gap-2">
            <span>✅</span> Snap token created successfully: <span class="font-mono text-xs"><?= substr($midtransResult['token'] ?? '', 0, 30) ?>...</span>
        </div>
        <?php else: ?>
        <div class="bg-[#F97316] text-white border-4 border-black p-3 font-bold text-sm">
            <span>❌</span> Failed: <?= esc($midtransResult['error'] ?? 'Unknown error') ?>
            <?php if (isset($midtransResult['response'])): ?>
            <pre class="text-xs mt-2 font-mono"><?= esc(json_encode($midtransResult['response'], JSON_PRETTY_PRINT)) ?></pre>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="neo-card" data-aos="fade-up">
        <h2 class="text-lg font-black mb-3 flex items-center gap-2">
            <span>🚚</span> BITESHIP TEST
        </h2>
        <?php if ($biteshipKey === '(empty)'): ?>
        <div class="bg-[#EF4444] text-white border-4 border-black p-3 font-bold text-sm">BITESHIP_API_KEY is empty! Check .env file.</div>
        <?php elseif ($biteshipResult['tested'] ?? false): ?>
        <div class="bg-[#22C55E] border-4 border-black p-3 font-bold text-sm flex items-center gap-2">
            <span>✅</span> Found <?= $biteshipResult['count'] ?? 0 ?> areas for "Jakarta"
        </div>
        <?php else: ?>
        <div class="bg-[#F97316] text-white border-4 border-black p-3 font-bold text-sm">
            <span>❌</span> No areas returned. API key may be invalid.
        </div>
        <?php endif; ?>
    </div>

    <div class="neo-card-yellow mt-6" data-aos="fade-up">
        <h3 class="font-black mb-2">💡 Troubleshooting</h3>
        <ul class="text-sm space-y-1 font-bold">
            <li>1. Run <code class="bg-black text-white px-2">php spark cache:clear</code> to clear config cache</li>
            <li>2. Ensure <code class="bg-black text-white px-2">.env</code> is in project root (same folder as <code class="bg-black text-white px-2">app/</code>)</li>
            <li>3. Restart your PHP server after editing .env</li>
            <li>4. For Biteship: go to <a href="https://dashboard.biteship.com/integrations" target="_blank" class="underline">dashboard.biteship.com/integrations</a> → Tambah Kunci API → copy full JWT to <code class="bg-black text-white px-2">BITESHIP_API_KEY</code> in .env</li>
            <li>5. For Midtrans: set Payment Notification URL in <a href="https://dashboard.sandbox.midtrans.com/settings/vtweb_configuration" target="_blank" class="underline">Midtrans Dashboard → Settings</a> to <code class="bg-black text-white px-2"><?= base_url('midtrans/callback') ?></code></li>
            <li>6. Also verify IS_PRODUCTION is not set to true (or set it ONLY when using production keys)</li>
            <li>7. Check that Midtrans keys are sandbox keys (start with <code>SB-Mid-</code>)</li>
        </ul>
    </div>
</div>
<?= $this->endSection() ?>
