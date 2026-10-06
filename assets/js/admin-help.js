(function () {
    'use strict';

    const root = document.querySelector('[data-admin-help]');
    if (!root) {
        return;
    }

    const searchInput = root.querySelector('[data-admin-help-search]');
    const clearBtn = root.querySelector('[data-admin-help-search-clear]');
    const meta = root.querySelector('[data-admin-help-search-meta]');
    const emptyState = root.querySelector('[data-admin-help-empty]');
    const articles = Array.from(root.querySelectorAll('[data-admin-help-article]'));
    const categories = Array.from(root.querySelectorAll('[data-admin-help-category]'));
    const totalArticles = window.EDDILICIOUS_ADMIN_HELP?.articleCount || articles.length;

    function normalizeQuery(value) {
        return String(value || '').trim().toLowerCase();
    }

    function tokensFromQuery(query) {
        return query.split(/\s+/).filter(Boolean);
    }

    function articleMatches(article, tokens) {
        const blob = article.getAttribute('data-search') || '';
        return tokens.every(function (token) {
            return blob.indexOf(token) !== -1;
        });
    }

    function updateSearch() {
        const query = normalizeQuery(searchInput?.value);
        const tokens = tokensFromQuery(query);
        const hasQuery = tokens.length > 0;
        let visibleCount = 0;

        if (clearBtn) {
            clearBtn.hidden = !hasQuery;
        }

        articles.forEach(function (article) {
            const show = !hasQuery || articleMatches(article, tokens);
            article.classList.toggle('is-hidden', !show);
            if (show) {
                visibleCount += 1;
            }
        });

        categories.forEach(function (category) {
            const visibleInCategory = Array.from(
                category.querySelectorAll('[data-admin-help-article]')
            ).some(function (article) {
                return !article.classList.contains('is-hidden');
            });

            category.classList.toggle('is-hidden', hasQuery && !visibleInCategory);
        });

        if (emptyState) {
            emptyState.hidden = !hasQuery || visibleCount > 0;
        }

        if (meta) {
            if (!hasQuery) {
                meta.textContent = totalArticles + ' topics';
            } else {
                meta.textContent = visibleCount + ' of ' + totalArticles + ' topics match';
            }
        }
    }

    function clearSearch() {
        if (!searchInput) {
            return;
        }

        searchInput.value = '';
        searchInput.focus();
        updateSearch();
    }

    if (searchInput) {
        searchInput.addEventListener('input', updateSearch);
        searchInput.addEventListener('search', updateSearch);
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', clearSearch);
    }

    const hash = window.location.hash.replace(/^#/, '');
    if (hash && hash.indexOf('help-') === 0) {
        const target = document.getElementById(hash);
        if (target) {
            window.setTimeout(function () {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);
        }
    }

    updateSearch();
})();
