<?php
/**
 * Global search overlay — included from navbar
 */
$icon_dir = __DIR__ . '/icons';
?>
<div class="search-overlay" data-search-overlay hidden aria-hidden="true">
    <div class="search-overlay__backdrop" data-search-close tabindex="-1"></div>

    <div class="search-overlay__panel" role="dialog" aria-modal="true" aria-labelledby="search-overlay-title">
        <div class="search-overlay__header">
            <h2 id="search-overlay-title" class="visually-hidden">Search our menu</h2>
            <form class="search-overlay__form" data-search-form action="search.php" method="get" role="search">
                <label class="visually-hidden" for="search-overlay-input">Search our menu</label>
                <span class="search-overlay__icon" aria-hidden="true">
                    <?php include $icon_dir . '/search.php'; ?>
                </span>
                <input
                    type="search"
                    id="search-overlay-input"
                    name="q"
                    class="search-overlay__input"
                    placeholder="Search soups, rice, platters…"
                    data-search-input
                    autocomplete="off"
                    enterkeyhint="search"
                >
                <button type="button" class="search-overlay__close" data-search-close aria-label="Close search">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
                    </svg>
                </button>
            </form>
        </div>

        <div class="search-overlay__body">
            <div class="search-overlay__popular" data-search-popular>
                <p class="search-overlay__label">Popular searches</p>
                <div class="search-overlay__chips" data-search-chips role="list"></div>
            </div>

            <div class="search-overlay__results" data-search-results hidden>
                <p class="search-overlay__label" data-search-results-label>Results</p>
                <ul class="search-overlay__list" data-search-results-list role="list"></ul>
                <a href="search.php" class="search-overlay__view-all" data-search-view-all hidden>
                    View all results
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                    </svg>
                </a>
            </div>

            <div class="search-overlay__empty" data-search-empty hidden>
                <p>We could not find that. Try another word or browse our menu.</p>
                <a href="menu.php?category=soups" class="search-overlay__menu-link">Go to our menu</a>
            </div>
        </div>
    </div>
</div>
