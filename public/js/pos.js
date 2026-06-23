document.addEventListener('DOMContentLoaded', function () {
    updateClock();

    document.querySelectorAll('.product-card').forEach(function (card) {
        card.addEventListener('click', function () {
            const id = this.dataset.id;
            addToCart(id);
        });
    });

    document.querySelectorAll('.qty-inc').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            const id = this.dataset.id;
            updateCartItem(id, 'inc');
        });
    });

    document.querySelectorAll('.qty-dec').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            const id = this.dataset.id;
            updateCartItem(id, 'dec');
        });
    });

    document.querySelectorAll('.remove-item').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            const id = this.dataset.id;
            removeCartItem(id);
        });
    });

    document.getElementById('clear-cart')?.addEventListener('click', function () {
        if (!confirm('Clear entire cart?')) return;
        fetch('/pos/clearCart', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function () { location.reload(); });
    });

    function addToCart(productId) {
        fetch('/pos/addToCart', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'product_id=' + productId
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

    function updateCartItem(productId, action) {
        fetch('/pos/updateCart', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'product_id=' + productId
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success) {
                location.reload();
            }
        });
    }

    function removeCartItem(productId) {
        fetch('/pos/removeFromCart', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'product_id=' + productId
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
