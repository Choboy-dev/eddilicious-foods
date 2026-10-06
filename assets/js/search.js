/**
 * Edilicious Foods — search overlay + product lookup
 */
(function (window) {
    'use strict';

    const OVERLAY_LIMIT = 6;
    const catalog = window.EDDILICIOUS_PRODUCT_CATALOG || {};
    const popular = window.EDDILICIOUS_POPULAR_SEARCHES || [];
    const FALLBACK_IMAGE = 'assets/images/products/egusi-soup.jpeg';

    const overlay = document.querySelector('[data-search-overlay]');
    const form = document.querySelector('[data-search-form]');
    const input = document.querySelector('[data-search-input]');
    const chipsEl = document.querySelector('[data-search-chips]');
    const popularSection = document.querySelector('[data-search-popular]');
    const resultsSection = document.querySelector('[data-search-results]');
    const resultsList = document.querySelector('[data-search-results-list]');
    const resultsLabel = document.querySelector('[data-search-results-label]');
    const viewAllLink = document.querySelector('[data-search-view-all]');
    const emptyEl = document.querySelector('[data-search-empty]');

    let debounceTimer = null;
    let focusedIndex = -1;

    function formatPrice(amount) {
        if (window.EdiliciousCart) {
            return window.EdiliciousCart.formatPrice(amount);
        }
        return '£' + Number(amount).toFixed(2);
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function escapeAttr(text) {
        return escapeHtml(text).replace(/"/g, '&quot;');
    }

    function priceLabel(item) {
        if (item.type === 'platter' || item.price) {
            return formatPrice(Number(item.price || item.priceMin || 0));
        }

        const min = Number(item.priceMin || 0);
        const max = Number(item.priceMax || 0);

        if (min === max) {
            return formatPrice(min);
        }

        return formatPrice(min) + ' – ' + formatPrice(max);
    }

    function normalizeQuery(query) {
        return String(query || '').trim().toLowerCase();
    }

    function searchProducts(query) {
        const needle = normalizeQuery(query);
        if (!needle) {
            return [];
        }

        return Object.values(catalog).filter(function (item) {
            const name = (item.name || '').toLowerCase();
            const category = (item.categoryLabel || item.category || '').toLowerCase();
            return name.includes(needle) || category.includes(needle);
        });
    }

    function openOverlay(prefill) {
        if (!overlay) {
            return;
        }

        overlay.hidden = false;
        overlay.setAttribute('aria-hidden', 'false');
        overlay.classList.add('is-open');
        document.body.classList.add('search-overlay-open');

        if (typeof prefill === 'string') {
            input.value = prefill;
            runSearch(prefill);
        } else {
            input.value = '';
            showPopular();
        }

        window.setTimeout(function () {
            input.focus();
            input.select();
        }, 50);
    }

    function closeOverlay() {
        if (!overlay) {
            return;
        }

        overlay.hidden = true;
        overlay.setAttribute('aria-hidden', 'true');
        overlay.classList.remove('is-open');
        document.body.classList.remove('search-overlay-open');
        focusedIndex = -1;
    }

    function showPopular() {
        if (popularSection) {
            popularSection.hidden = false;
        }
        if (resultsSection) {
            resultsSection.hidden = true;
        }
        if (emptyEl) {
            emptyEl.hidden = true;
        }
    }

    function renderPopular() {
        if (!chipsEl) {
            return;
        }

        chipsEl.innerHTML = popular.map(function (term) {
            return (
                '<button type="button" class="search-overlay__chip" data-search-chip="' + escapeAttr(term) + '">' +
                    escapeHtml(term) +
                '</button>'
            );
        }).join('');
    }

    function renderResults(items, query) {
        if (!resultsList) {
            return;
        }

        const limited = items.slice(0, OVERLAY_LIMIT);
        focusedIndex = -1;

        resultsList.innerHTML = limited.map(function (item, index) {
            const imageSrc = item.image || FALLBACK_IMAGE;
            const href = item.productUrl || item.menuUrl || ('menu.php?category=' + encodeURIComponent(item.category || 'soups'));

            return (
                '<li role="listitem">' +
                    '<a class="search-overlay__item" href="' + escapeAttr(href) + '" data-search-result data-index="' + index + '">' +
                        '<div class="search-overlay__thumb">' +
                            '<img src="' + escapeAttr(imageSrc) + '" alt="" class="search-overlay__image" width="56" height="56" loading="lazy">' +
                        '</div>' +
                        '<div class="search-overlay__info">' +
                            '<span class="search-overlay__name">' + escapeHtml(item.name) + '</span>' +
                            '<span class="search-overlay__meta">' + escapeHtml(item.categoryLabel || '') + '</span>' +
                        '</div>' +
                        '<span class="search-overlay__price">' + escapeHtml(priceLabel(item)) + '</span>' +
                    '</a>' +
                '</li>'
            );
        }).join('');

        if (popularSection) {
            popularSection.hidden = true;
        }
        if (resultsSection) {
            resultsSection.hidden = false;
        }
        if (emptyEl) {
            emptyEl.hidden = true;
        }

        if (resultsLabel) {
            const count = items.length;
            resultsLabel.textContent = count === 1 ? '1 result' : count + ' results';
        }

        if (viewAllLink) {
            viewAllLink.hidden = items.length <= OVERLAY_LIMIT;
            viewAllLink.href = 'search.php?q=' + encodeURIComponent(query);
        }
    }

    function showEmpty() {
        if (popularSection) {
            popularSection.hidden = true;
        }
        if (resultsSection) {
            resultsSection.hidden = true;
        }
        if (emptyEl) {
            emptyEl.hidden = false;
        }
    }

    function runSearch(query) {
        const normalized = normalizeQuery(query);

        if (!normalized) {
            showPopular();
            return;
        }

        const results = searchProducts(normalized);

        if (!results.length) {
            showEmpty();
            return;
        }

        renderResults(results, query.trim());
    }

    function debouncedSearch(query) {
        window.clearTimeout(debounceTimer);
        debounceTimer = window.setTimeout(function () {
            runSearch(query);
        }, 180);
    }

    function setFocusedItem(index) {
        const items = resultsList ? resultsList.querySelectorAll('[data-search-result]') : [];
        items.forEach(function (el, i) {
            el.classList.toggle('is-focused', i === index);
        });
        focusedIndex = index;
    }

    document.querySelectorAll('[data-search-open]').forEach(function (btn) {
        btn.addEventListener('click', function (event) {
            event.preventDefault();
            openOverlay();
        });
    });

    document.querySelectorAll('[data-search-close]').forEach(function (el) {
        el.addEventListener('click', closeOverlay);
    });

    if (chipsEl) {
        chipsEl.addEventListener('click', function (event) {
            const chip = event.target.closest('[data-search-chip]');
            if (!chip) {
                return;
            }
            const term = chip.getAttribute('data-search-chip') || '';
            input.value = term;
            runSearch(term);
        });
    }

    if (input) {
        input.addEventListener('input', function () {
            debouncedSearch(input.value);
        });

        input.addEventListener('keydown', function (event) {
            const items = resultsList ? resultsList.querySelectorAll('[data-search-result]') : [];

            if (event.key === 'ArrowDown' && items.length) {
                event.preventDefault();
                const next = focusedIndex < items.length - 1 ? focusedIndex + 1 : 0;
                setFocusedItem(next);
                items[next].focus();
                return;
            }

            if (event.key === 'ArrowUp' && items.length) {
                event.preventDefault();
                const prev = focusedIndex > 0 ? focusedIndex - 1 : items.length - 1;
                setFocusedItem(prev);
                items[prev].focus();
                return;
            }

            if (event.key === 'Escape') {
                event.preventDefault();
                closeOverlay();
            }
        });
    }

    if (form) {
        form.addEventListener('submit', function (event) {
            const query = normalizeQuery(input.value);
            if (!query) {
                event.preventDefault();
                return;
            }
            closeOverlay();
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && overlay && !overlay.hidden) {
            event.preventDefault();
            closeOverlay();
        }

        if ((event.metaKey || event.ctrlKey) && event.key === 'k') {
            event.preventDefault();
            openOverlay();
        }
    });

    renderPopular();

    window.EdiliciousSearch = {
        open: openOverlay,
        close: closeOverlay,
        search: searchProducts,
        formatPrice: formatPrice,
        priceLabel: priceLabel,
    };

    const params = new URLSearchParams(window.location.search);
    if (params.get('open') === 'search') {
        openOverlay(params.get('q') || '');
    }
})(window);
