document.addEventListener('DOMContentLoaded', function () {
    updateClock();

    document.querySelectorAll('.product-card').forEach(function (card) {
        card.addEventListener('click', function () {
            var id = this.dataset.id;
            var hasVariants = this.dataset.hasVariants === 'true';
            if (hasVariants) {
                if (typeof openVariantModal === 'function') {
                    openVariantModal(id);
                }
            } else {
                addToCart(id, 0);
            }
        });
    });

    document.querySelectorAll('.qty-inc').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            var key = this.dataset.key;
            var qtyEl = this.closest('.cart-item').querySelector('.qty');
            var current = parseInt(qtyEl.textContent);
            updateCartItem(key, current + 1);
        });
    });

    document.querySelectorAll('.qty-dec').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            var key = this.dataset.key;
            var qtyEl = this.closest('.cart-item').querySelector('.qty');
            var current = parseInt(qtyEl.textContent);
            if (current > 1) {
                updateCartItem(key, current - 1);
            }
        });
    });

    document.querySelectorAll('.remove-item').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            var key = this.dataset.key;
            removeCartItem(key);
        });
    });

    document.getElementById('clear-cart')?.addEventListener('click', function () {
        if (!confirm('Clear entire cart?')) return;
        fetch('/pos/clearCart', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function () { location.reload(); });
    });

    function addToCart(productId, variantId) {
        var params = new URLSearchParams();
        params.append('product_id', productId);
        params.append('variant_id', variantId);

        fetch('/pos/addToCart', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: params.toString()
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                location.reload();
            } else {
                alert(data.error);
            }
        });
    }

    function updateCartItem(cartKey, newQuantity) {
        if (newQuantity < 1) {
            removeCartItem(cartKey);
            return;
        }

        var params = new URLSearchParams();
        params.append('cart_key', cartKey);
        params.append('quantity', newQuantity);

        fetch('/pos/updateCart', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: params.toString()
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                location.reload();
            } else {
                alert(data.error);
            }
        });
    }

    function removeCartItem(cartKey) {
        if (!confirm('Remove this item?')) return;

        var params = new URLSearchParams();
        params.append('cart_key', cartKey);

        fetch('/pos/removeFromCart', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: params.toString()
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                location.reload();
            }
        });
    }

    function updateClock() {
        var now = new Date();
        document.getElementById('clock').textContent = now.toLocaleString('id-ID', {
            weekday: 'short', year: 'numeric', month: 'short',
            day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit'
        });
        setTimeout(updateClock, 1000);
    }
});
