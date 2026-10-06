/**
 * Edilicious Foods — wishlist (sessionStorage)
 */
(function (window) {
    'use strict';

    const WISHLIST_KEY = 'edilicious_wishlist';

    function normalizeItem(item) {
        if (!item || !item.id) {
            return null;
        }

        const normalized = {
            id: item.id,
            name: item.name || '',
            image: item.image || '',
            type: item.type || 'litre',
            priceMin: Number(item.priceMin || item.price || 0),
            priceMax: Number(item.priceMax || item.price || item.priceMin || 0),
        };

        if (item.type === 'platter' || item.price) {
            normalized.type = 'platter';
            normalized.price = Number(item.price || item.priceMin || 0);
            normalized.priceMin = normalized.price;
            normalized.priceMax = normalized.price;
        }

        if (item.prices && typeof item.prices === 'object') {
            normalized.prices = item.prices;
            const values = Object.values(item.prices).map(Number);
            if (values.length) {
                normalized.priceMin = Math.min.apply(null, values);
                normalized.priceMax = Math.max.apply(null, values);
            }
        }

        if (item.category) {
            normalized.category = item.category;
        }

        return normalized;
    }

    function getWishlist() {
        try {
            const raw = sessionStorage.getItem(WISHLIST_KEY);
            const list = JSON.parse(raw || '[]');
            if (!Array.isArray(list)) {
                return [];
            }

            return list
                .map(normalizeItem)
                .filter(Boolean);
        } catch {
            return [];
        }
    }

    function saveWishlist(list) {
        sessionStorage.setItem(WISHLIST_KEY, JSON.stringify(list));
        updateWishlistBadge(list.length);
        window.dispatchEvent(new CustomEvent('edilicious:wishlist-updated', { detail: { wishlist: list } }));
    }

    function updateWishlistBadge(count) {
        document.querySelectorAll('[data-wishlist-badge]').forEach(function (badge) {
            badge.textContent = String(count);
            badge.setAttribute('data-count', String(count));
            badge.hidden = count <= 0;
        });
    }

    function hasItem(id) {
        return getWishlist().some(function (entry) {
            return entry.id === id;
        });
    }

    function addItem(item) {
        const normalized = normalizeItem(item);
        if (!normalized || hasItem(normalized.id)) {
            return getWishlist();
        }

        const list = getWishlist();
        list.push(normalized);
        saveWishlist(list);
        return list;
    }

    function removeItem(id) {
        const list = getWishlist().filter(function (entry) {
            return entry.id !== id;
        });
        saveWishlist(list);
        return list;
    }

    function toggleItem(item) {
        const normalized = normalizeItem(item);
        if (!normalized) {
            return getWishlist();
        }

        if (hasItem(normalized.id)) {
            return removeItem(normalized.id);
        }

        return addItem(normalized);
    }

    function clearWishlist() {
        saveWishlist([]);
    }

    function initBadge() {
        updateWishlistBadge(getWishlist().length);
    }

    window.EdiliciousWishlist = {
        WISHLIST_KEY: WISHLIST_KEY,
        getWishlist: getWishlist,
        hasItem: hasItem,
        addItem: addItem,
        removeItem: removeItem,
        toggleItem: toggleItem,
        clearWishlist: clearWishlist,
        updateWishlistBadge: updateWishlistBadge,
        initBadge: initBadge,
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initBadge);
    } else {
        initBadge();
    }
})(window);
