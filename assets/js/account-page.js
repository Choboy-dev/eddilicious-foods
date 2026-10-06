/**
 * Account dashboard — live stats and sign out
 */
(function () {
    'use strict';

    const signOutBtn = document.querySelector('[data-account-signout]');
    const cartCountEl = document.querySelector('[data-dashboard-cart-count]');
    const wishlistCountEl = document.querySelector('[data-dashboard-wishlist-count]');
    const config = window.EDDILICIOUS_AUTH || {};

    function updateCounts() {
        if (cartCountEl && window.EdiliciousCart) {
            const cart = window.EdiliciousCart.getCart();
            const totalItems = cart.reduce(function (sum, item) {
                return sum + Math.max(1, Number(item.units || item.quantity || 1));
            }, 0);
            cartCountEl.textContent = String(totalItems);
        }

        if (wishlistCountEl && window.EdiliciousWishlist) {
            const wishlist = window.EdiliciousWishlist.getWishlist();
            wishlistCountEl.textContent = String(wishlist.length);
        }
    }

    if (signOutBtn) {
        signOutBtn.addEventListener('click', function () {
            signOutBtn.disabled = true;

            fetch('api/auth/logout.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': config.csrf || '',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ csrf: config.csrf || '' }),
            })
                .then(function (response) {
                    return response.json();
                })
                .then(function (data) {
                    window.location.href = data.redirect || 'signup.php';
                })
                .catch(function () {
                    window.location.href = 'signup.php';
                });
        });
    }

    updateCounts();
    window.addEventListener('edilicious:cart-updated', updateCounts);
    window.addEventListener('edilicious:wishlist-updated', updateCounts);
})();
