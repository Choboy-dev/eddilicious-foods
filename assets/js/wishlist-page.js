/**
 * Wishlist page — render homepage product cards for saved items
 */
(function () {
    'use strict';

    const wishlistApi = window.EdiliciousWishlist;
    const catalog = window.EDDILICIOUS_PRODUCT_CATALOG || {};
    const FALLBACK_IMAGE = 'assets/images/products/egusi-soup.jpeg';

    const layoutEl = document.querySelector('[data-wishlist-layout]');
    const itemsEl = document.querySelector('[data-wishlist-items]');
    const emptyEl = document.querySelector('[data-wishlist-empty]');
    const countEl = document.querySelector('[data-wishlist-count]');
    const countLabelEl = document.querySelector('[data-wishlist-count-label]');

    if (!wishlistApi || !itemsEl) {
        return;
    }

    const HEART_SVG =
        '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
            '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>' +
        '</svg>';

    const QUICK_VIEW_SVG =
        '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
            '<path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>' +
        '</svg>';

    const CHEVRON_SVG =
        '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round">' +
            '<path d="m6 9 6 6 6-6"/>' +
        '</svg>';

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function escapeAttr(text) {
        return escapeHtml(text).replace(/"/g, '&quot;');
    }

    function formatPrice(amount) {
        if (window.EdiliciousCart) {
            return window.EdiliciousCart.formatPrice(amount);
        }
        return '£' + Number(amount).toFixed(2);
    }

    function mergeItem(stored) {
        const fresh = catalog[stored.id] || {};
        return Object.assign({}, stored, fresh, {
            id: stored.id,
            name: fresh.name || stored.name,
            image: fresh.image || stored.image,
            type: fresh.type || stored.type,
            prices: fresh.prices || stored.prices,
            price: fresh.price !== undefined ? fresh.price : stored.price,
            priceMin: fresh.priceMin !== undefined ? fresh.priceMin : stored.priceMin,
            priceMax: fresh.priceMax !== undefined ? fresh.priceMax : stored.priceMax,
            productUrl: fresh.productUrl || stored.productUrl,
            compareAt: fresh.compareAt,
            discountLabel: fresh.discountLabel,
        });
    }

    function productUrl(item) {
        if (item.productUrl) {
            return item.productUrl;
        }

        return 'product.php?id=' + encodeURIComponent(item.id || '');
    }

    function buildLitreCard(item) {
        const prices = item.prices || {};
        const priceMin = item.priceMin !== undefined ? item.priceMin : Math.min.apply(null, Object.values(prices).map(Number));
        const priceMax = item.priceMax !== undefined ? item.priceMax : Math.max.apply(null, Object.values(prices).map(Number));
        const imageSrc = item.image || FALLBACK_IMAGE;
        const id = item.id;

        const href = productUrl(item);

        return (
            '<article class="product-card" role="listitem" data-product-id="' + escapeHtml(id) + '" ' +
                'data-prices="' + escapeAttr(JSON.stringify(prices)) + '" ' +
                'data-name="' + escapeAttr((item.name || '').toLowerCase()) + '" ' +
                'data-price-min="' + escapeHtml(String(priceMin)) + '" ' +
                'data-price-max="' + escapeHtml(String(priceMax)) + '">' +
                '<div class="product-card__media">' +
                    '<a href="' + escapeAttr(href) + '" class="product-card__image-link" tabindex="-1" aria-hidden="true">' +
                        '<img src="' + escapeAttr(imageSrc) + '" alt="' + escapeAttr(item.name) + '" class="product-card__image" width="280" height="220" loading="lazy">' +
                    '</a>' +
                    '<div class="product-card__actions" aria-label="Product actions">' +
                        '<a href="' + escapeAttr(href) + '" class="product-card__action" aria-label="View ' + escapeAttr(item.name) + '">' + QUICK_VIEW_SVG + '</a>' +
                        '<button type="button" class="product-card__action is-active" data-action="wishlist" aria-label="Remove ' + escapeAttr(item.name) + ' from wishlist" aria-pressed="true">' + HEART_SVG + '</button>' +
                    '</div>' +
                '</div>' +
                '<div class="product-card__body">' +
                    '<h3 class="product-card__name"><a href="' + escapeAttr(href) + '" class="product-card__name-link">' + escapeHtml(item.name) + '</a></h3>' +
                    '<div class="product-card__field">' +
                        '<div class="qty-dropdown" data-qty-dropdown>' +
                            '<select id="qty-' + escapeHtml(id) + '" class="qty-dropdown__native" data-qty-select aria-hidden="true" tabindex="-1">' +
                                '<option value="" selected disabled>Pick a size</option>' +
                                '<option value="2">2 Litres</option>' +
                                '<option value="4">4 Litres</option>' +
                            '</select>' +
                            '<button type="button" class="qty-dropdown__trigger" aria-haspopup="listbox" aria-expanded="false" aria-controls="qty-menu-' + escapeHtml(id) + '" id="qty-trigger-' + escapeHtml(id) + '">' +
                                '<span class="qty-dropdown__value qty-dropdown__value--placeholder" data-qty-label>Pick a size</span>' +
                                '<span class="qty-dropdown__chevron" aria-hidden="true">' + CHEVRON_SVG + '</span>' +
                            '</button>' +
                            '<ul class="qty-dropdown__menu" id="qty-menu-' + escapeHtml(id) + '" role="listbox" aria-label="Pick a size for ' + escapeAttr(item.name) + '" hidden>' +
                                '<li class="qty-dropdown__option" role="option" data-value="2" tabindex="-1" aria-selected="false">' +
                                    '<span class="qty-dropdown__option-main">2 Litres</span>' +
                                    '<span class="qty-dropdown__option-sub">' + escapeHtml(formatPrice(prices['2'])) + '</span>' +
                                '</li>' +
                                '<li class="qty-dropdown__option" role="option" data-value="4" tabindex="-1" aria-selected="false">' +
                                    '<span class="qty-dropdown__option-main">4 Litres</span>' +
                                    '<span class="qty-dropdown__option-sub">' + escapeHtml(formatPrice(prices['4'])) + '</span>' +
                                '</li>' +
                            '</ul>' +
                        '</div>' +
                    '</div>' +
                    '<p class="product-card__price" data-price-display aria-live="polite"></p>' +
                    '<button type="button" class="product-card__cart" data-add-to-cart disabled>Add to Cart</button>' +
                '</div>' +
            '</article>'
        );
    }

    function buildPlatterCard(item) {
        const price = Number(item.price || 0);
        const imageSrc = item.image || FALLBACK_IMAGE;
        const compareAt = item.compareAt ? Number(item.compareAt) : null;
        const onSale = compareAt !== null && compareAt > price;
        const id = item.id;

        let priceHtml =
            '<p class="product-card__price product-card__price--fixed is-visible">';

        if (onSale) {
            priceHtml += '<span class="product-card__price-compare">' + escapeHtml(formatPrice(compareAt)) + '</span>';
        }

        priceHtml += '<span class="product-card__price-value">' + escapeHtml(formatPrice(price)) + '</span></p>';

        const href = productUrl(item);

        return (
            '<article class="product-card product-card--platter" role="listitem" data-product-id="' + escapeHtml(id) + '" ' +
                'data-price="' + escapeHtml(String(price)) + '" ' +
                'data-name="' + escapeAttr((item.name || '').toLowerCase()) + '" ' +
                'data-price-min="' + escapeHtml(String(price)) + '" ' +
                'data-price-max="' + escapeHtml(String(price)) + '"' +
                (onSale ? ' data-compare-at="' + escapeHtml(String(compareAt)) + '"' : '') + '>' +
                '<div class="product-card__media">' +
                    (item.discountLabel ? '<span class="product-card__sale-badge">' + escapeHtml(item.discountLabel) + '</span>' : '') +
                    '<a href="' + escapeAttr(href) + '" class="product-card__image-link" tabindex="-1" aria-hidden="true">' +
                        '<img src="' + escapeAttr(imageSrc) + '" alt="' + escapeAttr(item.name) + '" class="product-card__image" width="280" height="220" loading="lazy">' +
                    '</a>' +
                    '<div class="product-card__actions" aria-label="Product actions">' +
                        '<a href="' + escapeAttr(href) + '" class="product-card__action" aria-label="View ' + escapeAttr(item.name) + '">' + QUICK_VIEW_SVG + '</a>' +
                        '<button type="button" class="product-card__action is-active" data-action="wishlist" aria-label="Remove ' + escapeAttr(item.name) + ' from wishlist" aria-pressed="true">' + HEART_SVG + '</button>' +
                    '</div>' +
                '</div>' +
                '<div class="product-card__body">' +
                    '<h3 class="product-card__name"><a href="' + escapeAttr(href) + '" class="product-card__name-link">' + escapeHtml(item.name) + '</a></h3>' +
                    priceHtml +
                    '<button type="button" class="product-card__cart" data-add-to-cart>Add to Cart</button>' +
                '</div>' +
            '</article>'
        );
    }

    function renderItems(list) {
        itemsEl.innerHTML = '';

        list.forEach(function (stored) {
            const item = mergeItem(stored);
            const isPlatter = item.type === 'platter' || (item.price && !item.prices);

            itemsEl.insertAdjacentHTML('beforeend', isPlatter ? buildPlatterCard(item) : buildLitreCard(item));
        });

        if (window.EdiliciousProducts) {
            window.EdiliciousProducts.initCards(itemsEl);
        }

        const hasItems = list.length > 0;
        emptyEl.hidden = hasItems;
        itemsEl.hidden = !hasItems;

        if (layoutEl) {
            layoutEl.classList.toggle('is-empty', !hasItems);
        }

        if (countEl) {
            countEl.textContent = String(list.length);
        }

        if (countLabelEl) {
            countLabelEl.textContent = list.length === 1 ? 'item saved' : 'items saved';
        }
    }

    function render() {
        renderItems(wishlistApi.getWishlist());
    }

    window.addEventListener('edilicious:wishlist-updated', render);
    render();
})();
