<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-4xl font-black mb-8 flex items-center gap-3" data-aos="fade-down">
        <span>📋</span> CHECKOUT
    </h1>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
        <div class="lg:col-span-3 space-y-6">
            <div class="neo-card" data-aos="fade-up">
                <h2 class="text-lg font-black mb-4">BUYER INFORMATION</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-heading font-bold text-sm mb-1">Full Name *</label>
                        <input type="text" id="buyer_name" class="neo-input" placeholder="Your name" value="<?= esc($user['name'] ?? '') ?>" required />
                    </div>
                    <div>
                        <label class="block font-heading font-bold text-sm mb-1">Phone *</label>
                        <input type="tel" id="buyer_phone" class="neo-input" placeholder="08xxxx" required />
                    </div>
                    <div class="md:col-span-2">
                        <label class="block font-heading font-bold text-sm mb-1">Email (optional)</label>
                        <input type="email" id="buyer_email" class="neo-input" placeholder="email@example.com" value="<?= esc($user['email'] ?? '') ?>" />
                    </div>
                </div>
            </div>

            <div class="neo-card" data-aos="fade-up">
                <h2 class="text-lg font-black mb-4">SHIPPING ADDRESS</h2>
                <div class="space-y-4">
                    <div>
                        <label class="block font-heading font-bold text-sm mb-1">Search City / Area (OpenStreetMap)</label>
                        <input type="text" id="city_search" class="neo-input" placeholder="Type city name..." autocomplete="off" />
                        <div id="city_results" class="hidden mt-1 border-4 border-black bg-white max-h-40 overflow-y-auto"></div>
                        <input type="hidden" id="city_id" />
                        <p id="shipping-error" class="text-sm font-bold mt-2 hidden text-[#EF4444]"></p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block font-heading font-bold text-sm mb-1">Postal Code *</label>
                            <input type="text" id="postal_code" class="neo-input" placeholder="e.g. 40115" />
                        </div>
                        <div>
                            <label class="block font-heading font-bold text-sm mb-1">City / Regency</label>
                            <input type="text" id="city_name" class="neo-input" placeholder="e.g. Bandung" />
                        </div>
                    </div>
                    <div>
                        <label class="block font-heading font-bold text-sm mb-1">Full Address *</label>
                        <textarea id="address" class="neo-input" rows="3" placeholder="Street, district, city, province"></textarea>
                    </div>
                </div>
            </div>

            <div class="neo-card" data-aos="fade-up">
                <h2 class="text-lg font-black mb-4">SHIPPING COURIER</h2>
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block font-heading font-bold text-sm mb-1">Courier *</label>
                            <input type="text" id="courier-name" class="neo-input" placeholder="JNE / TIKI / SiCepat" value="JNE" />
                        </div>
                        <div>
                            <label class="block font-heading font-bold text-sm mb-1">Service *</label>
                            <input type="text" id="courier-service" class="neo-input" placeholder="REG / ECO / OKE" value="REG" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block font-heading font-bold text-sm mb-1">Shipping Cost (Rp) *</label>
                            <input type="number" id="shipping-cost-input" class="neo-input" placeholder="e.g. 15000" min="0" />
                        </div>
                        <div>
                            <label class="block font-heading font-bold text-sm mb-1">Estimation</label>
                            <input type="text" id="shipping-estimation" class="neo-input" placeholder="e.g. 2-3 days" />
                        </div>
                    </div>
                    <button id="apply-shipping" class="neo-btn-cyan text-sm">Apply Shipping</button>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="neo-card-yellow sticky top-4" data-aos="fade-left">
                <h3 class="text-lg font-black mb-4">ORDER SUMMARY</h3>
                <div class="space-y-3">
                    <?php foreach ($cart as $item): ?>
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-bold flex-1"><?= $item['name'] ?> <span class="text-xs">x<?= $item['quantity'] ?></span></span>
                        <span>Rp <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="neo-divider my-3"></div>
                <div class="flex justify-between text-sm">
                    <span>Subtotal</span>
                    <span class="font-bold">Rp <?= number_format($subtotal, 0, ',', '.') ?></span>
                </div>
                <div class="flex justify-between text-sm">
                    <span>Shipping</span>
                    <span class="font-bold" id="shipping-display">-</span>
                </div>
                <div class="neo-divider my-3"></div>
                <div class="flex justify-between font-heading font-black text-xl">
                    <span>Total</span>
                    <span id="grand-total">Rp <?= number_format($subtotal, 0, ',', '.') ?></span>
                </div>
                <input type="hidden" id="shipping-cost" value="0" />
                <input type="hidden" id="courier-name-val" value="" />
                <input type="hidden" id="courier-service-val" value="" />

                <div id="validation-errors" class="hidden mt-3 bg-[#EF4444] text-white border-4 border-black p-3 text-sm font-bold"></div>

                <button id="pay-now" class="neo-btn-green w-full mt-4 text-base" disabled>
                    PAY NOW
                </button>
                <p class="text-xs mt-2 text-center opacity-70">Secure payment via Midtrans</p>
            </div>
        </div>
    </div>
</div>

<script>
let selectedShipping = null;

document.getElementById('city_search').addEventListener('input', function () {
    const query = this.value;
    if (query.length < 3) {
        document.getElementById('city_results').classList.add('hidden');
        document.getElementById('city_results').innerHTML = '';
        return;
    }

    fetch('https://nominatim.openstreetmap.org/search?q=' + encodeURIComponent(query + ', Indonesia') + '&format=json&countrycodes=id&limit=8&addressdetails=1', {
        headers: { 'Accept': 'application/json' },
        signal: AbortSignal.timeout(5000)
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        const container = document.getElementById('city_results');
        container.innerHTML = '';
        if (data && data.length > 0) {
            container.classList.remove('hidden');
            data.forEach(function (place) {
                const div = document.createElement('div');
                div.className = 'p-3 border-b-2 border-black cursor-pointer hover:bg-[#FFDE4D] font-bold text-sm';
                div.textContent = place.display_name;
                div.dataset.lat = place.lat;
                div.dataset.lon = place.lon;
                div.dataset.postalCode = place.address?.postcode || '';
                div.dataset.city = place.address?.city || place.address?.town || place.address?.county || '';
                div.addEventListener('click', function () {
                    document.getElementById('city_search').value = this.textContent;
                    document.getElementById('postal_code').value = this.dataset.postalCode;
                    document.getElementById('city_name').value = this.dataset.city;
                    container.classList.add('hidden');
                });
                container.appendChild(div);
            });
        } else {
            container.classList.add('hidden');
        }
    })
    .catch(function () {
        // silent fail — user can type postal code manually
    });
});

document.addEventListener('click', function (e) {
    if (!e.target.closest('#city_results') && !e.target.closest('#city_search')) {
        document.getElementById('city_results').classList.add('hidden');
    }
});

document.getElementById('apply-shipping')?.addEventListener('click', function () {
    const cost = parseInt(document.getElementById('shipping-cost-input').value);
    const courier = document.getElementById('courier-name').value.trim();
    const service = document.getElementById('courier-service').value.trim();

    if (!cost || cost < 0) {
        document.getElementById('shipping-error').textContent = 'Enter a valid shipping cost';
        document.getElementById('shipping-error').classList.remove('hidden');
        return;
    }
    if (!courier) {
        document.getElementById('shipping-error').textContent = 'Enter courier name';
        document.getElementById('shipping-error').classList.remove('hidden');
        return;
    }
    document.getElementById('shipping-error').classList.add('hidden');

    selectedShipping = {
        name: courier,
        service: service || 'Standard',
        cost: cost,
        estimation: document.getElementById('shipping-estimation').value || ''
    };
    updateTotal();
});

function updateTotal() {
    if (!selectedShipping) return;

    const subtotal = <?= $subtotal ?>;
    const shipping = selectedShipping.cost;
    const total = subtotal + shipping;

    document.getElementById('shipping-display').textContent = 'Rp ' + shipping.toLocaleString('id-ID');
    document.getElementById('grand-total').textContent = 'Rp ' + total.toLocaleString('id-ID');
    document.getElementById('shipping-cost').value = shipping;
    document.getElementById('courier-name-val').value = selectedShipping.name;
    document.getElementById('courier-service-val').value = selectedShipping.service;
    document.getElementById('pay-now').disabled = false;
}

document.getElementById('pay-now').addEventListener('click', function () {
    const errors = [];
    const name = document.getElementById('buyer_name').value.trim();
    const phone = document.getElementById('buyer_phone').value.trim();
    const address = document.getElementById('address').value.trim();
    const postalCode = document.getElementById('postal_code').value.trim();
    const shippingCost = document.getElementById('shipping-cost').value;

    if (!name) errors.push('Full name is required');
    if (!phone) errors.push('Phone number is required');
    if (!address) errors.push('Shipping address is required');
    if (!postalCode) errors.push('Enter a postal code');
    if (!shippingCost || shippingCost == 0) errors.push('Apply shipping first');

    const errEl = document.getElementById('validation-errors');
    if (errors.length > 0) {
        errEl.innerHTML = errors.map(function (e) { return '⚠️ ' + e; }).join('<br>');
        errEl.classList.remove('hidden');
        errEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }
    errEl.classList.add('hidden');

    const btn = this;
    btn.disabled = true;
    btn.textContent = 'Processing...';

    const formData = new FormData();
    formData.append('buyer_name', name);
    formData.append('buyer_phone', phone);
    formData.append('buyer_email', document.getElementById('buyer_email').value.trim());
    formData.append('address', address);
    formData.append('postal_code', postalCode);
    formData.append('city_name', document.getElementById('city_name').value.trim());
    formData.append('courier_name', document.getElementById('courier-name-val').value);
    formData.append('courier_service', document.getElementById('courier-service-val').value);
    formData.append('shipping_cost', shippingCost);

    fetch('<?= base_url('payment/createTransaction') ?>', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        if (data.success && data.snap_token) {
            window.snap.pay(data.snap_token, {
                onSuccess: function () {
                    window.location.href = '<?= base_url('order/success') ?>' + '/' + data.order_number;
                },
                onPending: function () {
                    window.location.href = '<?= base_url('order/success') ?>' + '/' + data.order_number;
                },
                onClose: function () {
                    window.location.href = '<?= base_url('order/success') ?>' + '/' + data.order_number;
                }
            });
        } else {
            errEl.innerHTML = '⚠️ ' + (data.error || 'Payment failed');
            errEl.classList.remove('hidden');
            btn.disabled = false;
            btn.textContent = 'PAY NOW';
        }
    })
    .catch(function () {
        errEl.innerHTML = '⚠️ Network error — check your connection';
        errEl.classList.remove('hidden');
        btn.disabled = false;
        btn.textContent = 'PAY NOW';
    });
});
</script>
<?= $this->endSection() ?>
