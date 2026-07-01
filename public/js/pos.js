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
        var self = this;
        showConfirm('Clear entire cart?').then(function (confirmed) {
            if (!confirmed) return;
            var params = new URLSearchParams();
            var csrfMeta = document.querySelector('meta[name="csrf-token"]');
            if (csrfMeta && window.csrfTokenName) {
                params.append(window.csrfTokenName, csrfMeta.content);
            }
            fetch('/pos/clearCart', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: params.toString()
            })
            .then(function (r) {
                return r.json().catch(function () { return null; });
            })
            .then(function (data) {
                if (data && data.success) location.reload();
            })
            .catch(function () {});
        });
    });

    function addToCart(productId, variantId) {
        var params = new URLSearchParams();
        params.append('product_id', productId);
        params.append('variant_id', variantId);
        var csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (csrfMeta && window.csrfTokenName) {
            params.append(window.csrfTokenName, csrfMeta.content);
        }

        fetch('/pos/addToCart', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: params.toString()
        })
        .then(function (r) {
            return r.json().catch(function () { return null; });
        })
        .then(function (data) {
            if (data && data.success) {
                location.reload();
            } else {
                showAlert((data && data.error) || 'Failed to add item');
            }
        })
        .catch(function () { showAlert('Failed to add item'); });
    }

    function updateCartItem(cartKey, newQuantity) {
        if (newQuantity < 1) {
            removeCartItem(cartKey);
            return;
        }

        var params = new URLSearchParams();
        params.append('cart_key', cartKey);
        params.append('quantity', newQuantity);
        var csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (csrfMeta && window.csrfTokenName) {
            params.append(window.csrfTokenName, csrfMeta.content);
        }

        fetch('/pos/updateCart', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: params.toString()
        })
        .then(function (r) {
            return r.json().catch(function () { return null; });
        })
        .then(function (data) {
            if (data && data.success) {
                location.reload();
            } else {
                showAlert((data && data.error) || 'Failed to update cart');
            }
        })
        .catch(function () { showAlert('Failed to update cart'); });
    }

    function removeCartItem(cartKey) {
        var self = this;
        showConfirm('Remove this item?').then(function (confirmed) {
            if (!confirmed) return;

            var params = new URLSearchParams();
            params.append('cart_key', cartKey);
            var csrfMeta = document.querySelector('meta[name="csrf-token"]');
            if (csrfMeta && window.csrfTokenName) {
                params.append(window.csrfTokenName, csrfMeta.content);
            }

            fetch('/pos/removeFromCart', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: params.toString()
            })
            .then(function (r) {
                return r.json().catch(function () { return null; });
            })
            .then(function (data) {
                if (data && data.success) {
                    location.reload();
                } else {
                    showAlert((data && data.error) || 'Failed to remove item');
                }
            })
            .catch(function () { showAlert('Failed to remove item'); });
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
