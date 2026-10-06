/**
 * Edilicious Foods — shared cart (sessionStorage)
 */
(function (window) {
    'use strict';

    const CART_KEY = 'edilicious_cart';
    const FULFILLMENT_KEY = 'edilicious_fulfillment';
    const CURRENCY = '£';

    function formatPrice(amount) {
        return CURRENCY + Number(amount).toFixed(2);
    }

    function getCart() {
        try {
            const raw = sessionStorage.getItem(CART_KEY);
            const cart = JSON.parse(raw || '[]');
            if (!Array.isArray(cart)) {
                return [];
            }

            return cart.map(function (item, index) {
                return {
                    lineId: item.lineId || ('legacy_' + index + '_' + (item.id || 'item')),
                    id: item.id,
                    name: item.name,
                    quantity: item.quantity,
                    units: Math.max(1, Number(item.units || 1)),
                    price: Number(item.price || 0),
                    image: item.image || '',
                };
            });
        } catch {
            return [];
        }
    }

    function saveCart(cart) {
        sessionStorage.setItem(CART_KEY, JSON.stringify(cart));
        updateCartBadge(cart.length);
        window.dispatchEvent(new CustomEvent('edilicious:cart-updated', { detail: { cart } }));
    }

    function updateCartBadge(count) {
        document.querySelectorAll('[data-cart-badge]').forEach(function (badge) {
            badge.textContent = String(count);
            badge.setAttribute('data-count', String(count));
            badge.hidden = count <= 0;
        });
    }

    function createLineId() {
        return 'line_' + Date.now() + '_' + Math.random().toString(36).slice(2, 9);
    }

    function addItem(item) {
        const cart = getCart();
        const units = Math.max(1, Number(item.units || 1));
        cart.push({
            lineId: createLineId(),
            id: item.id,
            name: item.name,
            quantity: item.quantity,
            units: units,
            price: Number(item.price),
            image: item.image || '',
        });
        saveCart(cart);
        return cart;
    }

    function removeItem(lineId) {
        const cart = getCart().filter(function (entry) {
            return entry.lineId !== lineId;
        });
        saveCart(cart);
        return cart;
    }

    function clearCart() {
        saveCart([]);
    }

    function getSubtotal(cart) {
        return cart.reduce(function (sum, item) {
            const units = Math.max(1, Number(item.units || 1));
            return sum + Number(item.price || 0) * units;
        }, 0);
    }

    function formatLineMeta(item) {
        const units = Math.max(1, Number(item.units || 1));
        if (units > 1) {
            return units + ' × ' + item.quantity;
        }
        return item.quantity;
    }

    function getFulfillment() {
        const stored = sessionStorage.getItem(FULFILLMENT_KEY);
        return stored === 'collection' ? 'collection' : 'delivery';
    }

    function setFulfillment(value) {
        const normalized = value === 'collection' ? 'collection' : 'delivery';
        sessionStorage.setItem(FULFILLMENT_KEY, normalized);
        window.dispatchEvent(new CustomEvent('edilicious:fulfillment-updated', {
            detail: { fulfillment: normalized },
        }));
    }

    function initBadge() {
        updateCartBadge(getCart().length);
    }

    window.EdiliciousCart = {
        CART_KEY: CART_KEY,
        formatPrice: formatPrice,
        getCart: getCart,
        saveCart: saveCart,
        addItem: addItem,
        removeItem: removeItem,
        clearCart: clearCart,
        getSubtotal: getSubtotal,
        formatLineMeta: formatLineMeta,
        getFulfillment: getFulfillment,
        setFulfillment: setFulfillment,
        updateCartBadge: updateCartBadge,
        initBadge: initBadge,
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initBadge);
    } else {
        initBadge();
    }
})(window);
