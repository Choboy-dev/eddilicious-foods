/**
 * Menu shop — sort, price filter, load more (6 on mobile, 12 on desktop)
 */
(function () {
    'use strict';

    const MOBILE_QUERY = window.matchMedia('(max-width: 768px)');

    const grid = document.querySelector('[data-shop-grid]');
    const sortSelect = document.querySelector('[data-shop-sort]');
    const priceFilter = document.querySelector('[data-shop-price-filter]');
    const viewMoreBtn = document.querySelector('[data-shop-view-more]');
    const countEl = document.querySelector('[data-shop-count]');
    const emptyEl = document.querySelector('[data-shop-empty]');

    if (!grid) {
        return;
    }

    let visibleLimit = getPageSize();
    const cards = Array.from(grid.querySelectorAll('[data-shop-item]'));

    function getPageSize() {
        return MOBILE_QUERY.matches ? 6 : 12;
    }

    function matchesPriceFilter(card, filterValue) {
        const min = parseFloat(card.dataset.priceMin || '0');

        switch (filterValue) {
            case 'under-50':
                return min < 50;
            case '50-100':
                return min >= 50 && min <= 100;
            case '100-150':
                return min >= 100 && min <= 150;
            case 'over-150':
                return min > 150;
            default:
                return true;
        }
    }

    function getFilteredCards() {
        const filterValue = priceFilter ? priceFilter.value : 'all';

        return cards.filter(function (card) {
            return matchesPriceFilter(card, filterValue);
        });
    }

    function sortCards(list, sortValue) {
        const sorted = list.slice();

        sorted.sort(function (a, b) {
            const nameA = a.dataset.name || '';
            const nameB = b.dataset.name || '';
            const priceA = parseFloat(a.dataset.priceMin || '0');
            const priceB = parseFloat(b.dataset.priceMin || '0');

            switch (sortValue) {
                case 'name-desc':
                    return nameB.localeCompare(nameA);
                case 'price-asc':
                    return priceA - priceB;
                case 'price-desc':
                    return priceB - priceA;
                default:
                    return nameA.localeCompare(nameB);
            }
        });

        return sorted;
    }

    function updateCount(visibleTotal) {
        if (!countEl) {
            return;
        }

        const label = visibleTotal === 1 ? 'item' : 'items';
        countEl.textContent = visibleTotal + ' ' + label;
    }

    function resetPagination() {
        visibleLimit = getPageSize();
    }

    function render() {
        const sortValue = sortSelect ? sortSelect.value : 'name-asc';
        const filtered = getFilteredCards();
        const sorted = sortCards(filtered, sortValue);

        cards.forEach(function (card) {
            card.classList.add('is-filtered-out');
            card.classList.remove('is-paginated-out');
        });

        sorted.forEach(function (card, index) {
            card.classList.remove('is-filtered-out');

            if (index >= visibleLimit) {
                card.classList.add('is-paginated-out');
            }

            grid.appendChild(card);
        });

        const visibleTotal = sorted.length;
        const remaining = Math.max(0, visibleTotal - visibleLimit);

        if (emptyEl) {
            emptyEl.hidden = visibleTotal > 0;
        }

        if (viewMoreBtn) {
            viewMoreBtn.hidden = remaining <= 0;
        }

        updateCount(visibleTotal);
    }

    if (sortSelect) {
        sortSelect.addEventListener('change', function () {
            resetPagination();
            render();
        });
    }

    if (priceFilter) {
        priceFilter.addEventListener('change', function () {
            resetPagination();
            render();
        });
    }

    if (viewMoreBtn) {
        viewMoreBtn.addEventListener('click', function () {
            visibleLimit += getPageSize();
            render();
        });
    }

    if (typeof MOBILE_QUERY.addEventListener === 'function') {
        MOBILE_QUERY.addEventListener('change', function () {
            resetPagination();
            render();
        });
    } else if (typeof MOBILE_QUERY.addListener === 'function') {
        MOBILE_QUERY.addListener(function () {
            resetPagination();
            render();
        });
    }

    render();
})();
