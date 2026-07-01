<?= $this->extend('layout/main') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-4xl font-black mb-8 flex items-center gap-3" data-aos="fade-down">
        <span>📋</span> CHECKOUT
    </h1>

    <div id="payment-success-overlay" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="neo-card-green max-w-lg w-full text-center p-8" data-aos="zoom-in">
            <span class="text-7xl block mb-4">✅</span>
            <h2 class="text-3xl font-black mb-2">PAYMENT CONFIRMED!</h2>
            <p class="font-bold text-lg mb-2" id="success-order-number"></p>
            <p class="text-sm mb-6">Your payment has been processed successfully. Thank you for your order!</p>
            <div class="flex gap-3 justify-center">
                <a href="<?= base_url('orders') ?>" class="neo-btn-white text-sm">My Orders</a>
                <a href="<?= base_url('/') ?>" class="neo-btn-yellow text-sm">Continue Shopping</a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8" id="checkout-grid">
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
                        <label class="block font-heading font-bold text-sm mb-1">📍 Search City / Area</label>
                        <input type="text" id="city_search" class="neo-input" placeholder="Type city name..." autocomplete="off" />
                        <div id="city_results" class="hidden mt-1 border-4 border-black bg-white max-h-40 overflow-y-auto"></div>
                        <input type="hidden" id="city_id" />
                        <p id="shipping-error" class="text-sm font-bold mt-2 hidden text-neo-red"></p>
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
                    <div>
                        <label class="block font-heading font-bold text-sm mb-1">📍 Pin Location on Map <span class="text-xs opacity-60">(click map or drag marker to set exact position)</span></label>
                        <div id="map" style="height: 300px;" class="border-4 border-black"></div>
                        <div class="flex gap-2 mt-2">
                            <button type="button" id="use-current-location" class="neo-btn-yellow text-xs">
                                📡 Use My Location
                            </button>
                            <button type="button" id="clear-location" class="neo-btn-white text-xs">
                                ✕ Clear Pin
                            </button>
                        </div>
                        <input type="hidden" id="latitude" value="" />
                        <input type="hidden" id="longitude" value="" />
                        <p id="location-status" class="text-xs font-bold mt-1 text-neo-green hidden">📍 Location pinned</p>
                    </div>
                </div>
            </div>

            <div class="neo-card" data-aos="fade-up">
                <h2 class="text-lg font-black mb-4">SHIPPING COURIER</h2>
                <div class="space-y-3">
                    <label class="block font-heading font-bold text-sm mb-1">Choose Courier Provider *</label>
                    <div class="flex flex-wrap gap-2" id="courier-providers">
                        <label class="neo-btn-white text-sm cursor-pointer has-[:checked]:bg-neo-yellow">
                            <input type="checkbox" name="couriers" value="jne" class="hidden" checked /> JNE
                        </label>
                        <label class="neo-btn-white text-sm cursor-pointer has-[:checked]:bg-neo-yellow">
                            <input type="checkbox" name="couriers" value="tiki" class="hidden" /> TIKI
                        </label>
                        <label class="neo-btn-white text-sm cursor-pointer has-[:checked]:bg-neo-yellow">
                            <input type="checkbox" name="couriers" value="sicepat" class="hidden" /> SiCepat
                        </label>
                        <label class="neo-btn-white text-sm cursor-pointer has-[:checked]:bg-neo-yellow">
                            <input type="checkbox" name="couriers" value="pos" class="hidden" /> POS
                        </label>
                    </div>
                    <button id="get-rates" class="neo-btn-cyan text-sm" disabled>Get Shipping Rates</button>
                    <div id="rates-loading" class="hidden text-sm font-bold animate-pulse-neo p-3 bg-neo-yellow border-4 border-black text-center">Loading rates...</div>
                    <div id="rates-list" class="space-y-2"></div>
                    <div id="selected-rate-display" class="hidden neo-card-green !p-3 text-sm font-bold">
                        Selected: <span id="selected-rate-text"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2">
            <div class="neo-card-yellow sticky top-4" data-aos="fade-left">
                <h3 class="text-lg font-black mb-4">ORDER SUMMARY</h3>
                <div class="space-y-3">
                    <?php foreach ($cart as $key => $item): ?>
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-bold flex-1"><?= esc($item['name']) ?>
                            <?php if (!empty($item['variant_label'])): ?>
                            <span class="text-xs opacity-60 block"><?= esc($item['variant_label']) ?></span>
                            <?php elseif (!empty($item['size'])): ?>
                            <span class="text-xs opacity-60">(<?= esc($item['size']) ?>)</span>
                            <?php endif; ?>
                            <span class="text-xs">x<?= $item['quantity'] ?></span>
                        </span>
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

                <div id="validation-errors" class="hidden mt-3 bg-neo-red text-white border-4 border-black p-3 text-sm font-bold"></div>

                <button id="pay-now" class="neo-btn-green w-full mt-4 text-base" disabled>
                    💳 PAY NOW
                </button>
                <p class="text-xs mt-2 text-center opacity-70">Secure payment via Midtrans</p>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let map, marker;
let selectedShipping = null;
let rateCache = null;

// ─── Leaflet Map ──────────────────────────────────────────────────

function initMap(lat = -6.9175, lng = 107.6191) {
    if (map) { map.remove(); }
    map = L.map('map').setView([lat, lng], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19,
    }).addTo(map);

    marker = L.marker([lat, lng], { draggable: true }).addTo(map);

    marker.on('dragend', function() {
        const pos = marker.getLatLng();
        updateLocation(pos.lat, pos.lng);
    });

    map.on('click', function(e) {
        marker.setLatLng(e.latlng);
        updateLocation(e.latlng.lat, e.latlng.lng);
    });
}

function updateLocation(lat, lng) {
    document.getElementById('latitude').value = lat.toFixed(6);
    document.getElementById('longitude').value = lng.toFixed(6);
    document.getElementById('location-status').classList.remove('hidden');
    reverseGeocode(lat, lng);
    enableGetRates();
}

function reverseGeocode(lat, lng) {
    fetch('https://nominatim.openstreetmap.org/reverse?format=json&lat=' + lat + '&lon=' + lng + '&addressdetails=1&accept-language=id', {
        headers: { 'Accept': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data && data.address) {
            const addr = data.address;
            const road = addr.road || addr.path || '';
            const suburb = addr.suburb || addr.neighbourhood || '';
            const city = addr.city || addr.town || addr.county || addr.state || '';
            const postcode = addr.postcode || '';
            const display = data.display_name || '';

            if (!document.getElementById('address').value.trim()) {
                const parts = [road, suburb].filter(Boolean);
                document.getElementById('address').value = parts.join(', ') + ', ' + display.split(',')[0];
            }
            if (!document.getElementById('city_name').value.trim()) {
                document.getElementById('city_name').value = city;
            }
            if (!document.getElementById('postal_code').value.trim() && postcode) {
                document.getElementById('postal_code').value = postcode;
            }
            if (!document.getElementById('city_search').value.trim()) {
                document.getElementById('city_search').value = city + ', ' + (addr.country || 'Indonesia');
            }
        }
    })
    .catch(function() {});
}

document.getElementById('use-current-location')?.addEventListener('click', function() {
    if (!navigator.geolocation) {
        showAlert('Geolocation is not supported by your browser');
        return;
    }
    this.textContent = '📍 Locating...';
    this.disabled = true;

    navigator.geolocation.getCurrentPosition(
        function(pos) {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            initMap(lat, lng);
            document.getElementById('use-current-location').textContent = '✅ Location Found';
            document.getElementById('use-current-location').disabled = false;
            setTimeout(function() {
                document.getElementById('use-current-location').textContent = '📡 Use My Location';
            }, 3000);
        },
        function(err) {
            showAlert('Could not get location: ' + err.message);
            document.getElementById('use-current-location').textContent = '📡 Use My Location';
            document.getElementById('use-current-location').disabled = false;
        },
        { enableHighAccuracy: true, timeout: 10000 }
    );
});

document.getElementById('clear-location')?.addEventListener('click', function() {
    document.getElementById('latitude').value = '';
    document.getElementById('longitude').value = '';
    document.getElementById('location-status').classList.add('hidden');
    if (marker) marker.remove();
});

// Initialize default map
initMap();

// ─── City Search (OpenStreetMap) ─────────────────────────────────────

document.getElementById('city_search').addEventListener('input', function () {
    const query = this.value.trim();
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
                div.className = 'p-3 border-b-2 border-black cursor-pointer hover:bg-neo-yellow font-bold text-sm';
                div.textContent = place.display_name;
                div.dataset.postalCode = place.address?.postcode || '';
                div.dataset.city = place.address?.city || place.address?.town || place.address?.county || '';
                div.dataset.lat = place.lat;
                div.dataset.lon = place.lon;
                div.addEventListener('click', function () {
                    document.getElementById('city_search').value = this.textContent;
                    document.getElementById('postal_code').value = this.dataset.postalCode;
                    document.getElementById('city_name').value = this.dataset.city;
                    container.classList.add('hidden');
                    if (this.dataset.lat && this.dataset.lon) {
                        const lat = parseFloat(this.dataset.lat);
                        const lng = parseFloat(this.dataset.lon);
                        initMap(lat, lng);
                        updateLocation(lat, lng);
                    }
                    enableGetRates();
                });
                container.appendChild(div);
            });
        } else {
            container.classList.add('hidden');
        }
    })
    .catch(function () {});
});

document.addEventListener('click', function (e) {
    if (!e.target.closest('#city_results') && !e.target.closest('#city_search')) {
        document.getElementById('city_results').classList.add('hidden');
    }
});

document.getElementById('postal_code').addEventListener('input', enableGetRates);

// ─── Courier Selection & Rates ─────────────────────────────────────

function getSelectedCouriers() {
    const checked = document.querySelectorAll('#courier-providers input:checked');
    return Array.from(checked).map(function (cb) { return cb.value; }).join(',');
}

document.querySelectorAll('#courier-providers input').forEach(function (cb) {
    cb.addEventListener('change', enableGetRates);
});

function enableGetRates() {
    const postalCode = document.getElementById('postal_code').value.trim();
    const couriers = getSelectedCouriers();
    document.getElementById('get-rates').disabled = !postalCode || !couriers;
}

document.getElementById('get-rates').addEventListener('click', function () {
    const postalCode = document.getElementById('postal_code').value.trim();
    const couriers = getSelectedCouriers();

    if (!postalCode) {
        document.getElementById('shipping-error').textContent = 'Enter a postal code first';
        document.getElementById('shipping-error').classList.remove('hidden');
        return;
    }
    if (!couriers) {
        document.getElementById('shipping-error').textContent = 'Select at least one courier';
        document.getElementById('shipping-error').classList.remove('hidden');
        return;
    }
    document.getElementById('shipping-error').classList.add('hidden');

    const btn = this;
    btn.disabled = true;
    document.getElementById('rates-loading').classList.remove('hidden');
    document.getElementById('rates-list').innerHTML = '';

    const formData = new FormData();
    formData.append('postal_code', postalCode);
    formData.append('courier', couriers);

    fetch('<?= base_url('shipping/getRates') ?>', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(function (r) { return r.json(); })
    .then(function (data) {
        document.getElementById('rates-loading').classList.add('hidden');
        btn.disabled = false;

        if (!data.success) {
            document.getElementById('rates-list').innerHTML = '<p class="text-sm font-bold text-neo-red">⚠️ ' + (data.error || 'No rates found for the selected couriers.') + '</p>';
            return;
        }

        if (!data.rates || data.rates.length === 0) {
            document.getElementById('rates-list').innerHTML = '<p class="text-sm font-bold text-neo-red">⚠️ No courier rates available for this destination. Try a different postal code.</p>';
            return;
        }

        rateCache = data.rates;
        renderRates(data.rates);
    })
    .catch(function () {
        document.getElementById('rates-loading').classList.add('hidden');
        btn.disabled = false;
        document.getElementById('rates-list').innerHTML = '<p class="text-sm font-bold text-neo-red">⚠️ Network error — check your connection and try again.</p>';
    });
});

function renderRates(rates) {
    const container = document.getElementById('rates-list');
    container.innerHTML = '';

    const grouped = {};
    rates.forEach(function (r) {
        if (!grouped[r.courier_name]) grouped[r.courier_name] = [];
        grouped[r.courier_name].push(r);
    });

    Object.keys(grouped).forEach(function (courier) {
        const items = grouped[courier];
        const header = document.createElement('p');
        header.className = 'font-black text-xs mt-3 mb-1 uppercase';
        header.textContent = courier;
        container.appendChild(header);

        items.forEach(function (rate) {
            const div = document.createElement('div');
            div.className = 'neo-card !p-3 cursor-pointer hover:bg-neo-yellow transition-colors rate-option text-sm';
            div.dataset.name = courier;
            div.dataset.service = rate.service_name;
            div.dataset.cost = rate.shipping_fee;
            div.dataset.duration = rate.duration_text || rate.duration || '';
            div.innerHTML = '<div class="flex items-center justify-between"><span class="font-bold">' + rate.service_name + '</span><span class="font-black">Rp ' + rate.shipping_fee.toLocaleString('id-ID') + '</span></div><div class="text-xs opacity-60 mt-1">' + (rate.duration_text || rate.duration || '—') + '</div>';
            div.addEventListener('click', function () {
                document.querySelectorAll('.rate-option').forEach(function (el) { el.classList.remove('bg-neo-yellow'); });
                this.classList.add('bg-neo-yellow');
                selectedShipping = {
                    name: this.dataset.name,
                    service: this.dataset.service,
                    cost: parseInt(this.dataset.cost),
                    estimation: this.dataset.duration
                };
                updateTotal();
            });
            container.appendChild(div);
        });
    });
}

// ─── Total Update ──────────────────────────────────────────────────

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
    document.getElementById('selected-rate-text').textContent = selectedShipping.name + ' - ' + selectedShipping.service + ' (Rp ' + shipping.toLocaleString('id-ID') + ', ' + selectedShipping.estimation + ')';
    document.getElementById('selected-rate-display').classList.remove('hidden');
    document.getElementById('pay-now').disabled = false;
}

// ─── Pay Now ───────────────────────────────────────────────────────

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
    if (!shippingCost || shippingCost == 0) errors.push('Select a shipping rate first');

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
    formData.append('latitude', document.getElementById('latitude').value);
    formData.append('longitude', document.getElementById('longitude').value);

    fetch('<?= base_url('payment/createTransaction') ?>', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(function (r) {
        return r.json().catch(function () { return null; });
    })
    .then(function (data) {
        if (data && data.success && data.snap_token) {
            window.snap.pay(data.snap_token, {
                onSuccess: function (result) {
                    fetch('<?= base_url('payment/verifyStatus') ?>', {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'order_number=' + encodeURIComponent(data.order_number)
                    }).then(function () {
                        showPaymentSuccess(data.order_number);
                    }).catch(function () {
                        showPaymentSuccess(data.order_number);
                    });
                },
                onPending: function (result) {
                    window.location.href = '<?= base_url('order/success') ?>' + '/' + data.order_number + '?pending=1';
                },
                onError: function (result) {
                    errEl.innerHTML = '⚠️ Payment error: ' + (result.status_message || 'Unknown error') + '. Please try again or use a different payment method.';
                    errEl.classList.remove('hidden');
                    btn.disabled = false;
                    btn.textContent = '💳 PAY NOW';
                },
                onClose: function () {
                    btn.disabled = false;
                    btn.textContent = '💳 PAY NOW';
                    errEl.innerHTML = '⚠️ Payment popup closed — you can retry or complete payment later from My Orders.';
                    errEl.classList.remove('hidden');
                }
            });
        } else {
            errEl.innerHTML = '⚠️ ' + ((data && data.error) || 'Payment failed');
            errEl.classList.remove('hidden');
            btn.disabled = false;
            btn.textContent = '💳 PAY NOW';
        }
    })
    .catch(function () {
        errEl.innerHTML = '⚠️ Network error — check your connection';
        errEl.classList.remove('hidden');
        btn.disabled = false;
        btn.textContent = '💳 PAY NOW';
    });
});

function showPaymentSuccess(orderNumber) {
    document.getElementById('success-order-number').textContent = 'Order: ' + orderNumber;
    document.getElementById('payment-success-overlay').classList.remove('hidden');
    document.getElementById('checkout-grid').classList.add('opacity-30', 'pointer-events-none');
}
</script>
<?= $this->endSection() ?>
