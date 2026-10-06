/**
 * Product detail page — gallery, tabs, quantity, cart, wishlist
 */
(function () {
    'use strict';

    const page = document.querySelector('[data-product-page]');
    if (!page) {
        return;
    }

    const cart = window.EdiliciousCart;
    const wishlist = window.EdiliciousWishlist;

    const productId = page.dataset.productId;
    const productType = page.dataset.productType || 'litre';
    const isPlatter = productType === 'platter';

    function videoMimeType(src) {
        const lower = String(src || '').toLowerCase();
        if (lower.endsWith('.webm')) {
            return 'video/webm';
        }
        if (lower.endsWith('.mov')) {
            return 'video/quicktime';
        }
        return 'video/mp4';
    }

    const titleEl = page.querySelector('.product-detail__title');
    const priceDisplay = page.querySelector('[data-product-price-display]');
    const addCartBtn = page.querySelector('[data-product-add-cart]');
    const mobileAddBtn = page.querySelector('[data-product-mobile-add]');
    const mobilePriceValue = page.querySelector('[data-product-mobile-price-value]');
    const wishlistBtn = page.querySelector('[data-product-wishlist]');
    const sizeOptions = page.querySelectorAll('[data-size-option]');
    const qtyInput = page.querySelector('[data-qty-input]');
    const qtyDecrease = page.querySelector('[data-qty-decrease]');
    const qtyIncrease = page.querySelector('[data-qty-increase]');

    const productName = titleEl ? titleEl.textContent.trim() : '';
    const galleryRoot = page.querySelector('[data-product-gallery]');

    let selectedSize = null;
    let selectedPrice = isPlatter ? Number(page.dataset.productPrice || 0) : null;
    let selectedLabel = isPlatter ? '1 Tray' : '';
    let unitCount = 1;
    let productImage = '';

    const prices = !isPlatter ? JSON.parse(page.dataset.productPrices || '{}') : {};

    function formatPrice(amount) {
        if (cart && typeof cart.formatPrice === 'function') {
            return cart.formatPrice(amount);
        }

        return '£' + Number(amount).toFixed(2);
    }

    function getActiveImageSrc() {
        const img = page.querySelector('[data-gallery-image]');
        if (img) {
            return img.getAttribute('src') || '';
        }

        const video = page.querySelector('[data-gallery-video]');
        if (video) {
            return video.getAttribute('poster') || '';
        }

        return '';
    }

    productImage = getActiveImageSrc();

    function flashButton(btn) {
        if (!btn) {
            return;
        }

        const original = btn.textContent;
        btn.classList.add('is-added');
        btn.textContent = 'Added';

        window.setTimeout(function () {
            btn.classList.remove('is-added');
            btn.textContent = original;
        }, 1400);
    }

    function setAddButtonsEnabled(enabled) {
        [addCartBtn, mobileAddBtn].forEach(function (btn) {
            if (btn) {
                btn.disabled = !enabled;
            }
        });
    }

    function getUnitCount() {
        return Math.max(1, Math.min(99, unitCount));
    }

    function setUnitCount(value) {
        unitCount = Math.max(1, Math.min(99, value));
        if (qtyInput) {
            qtyInput.value = String(unitCount);
        }
        renderPrice();
    }

    function renderPrice() {
        if (selectedPrice === null) {
            return;
        }

        const units = getUnitCount();
        const lineTotal = selectedPrice * units;
        let html = '<span class="product-detail__price-value">' + formatPrice(lineTotal) + '</span>';

        if (units > 1) {
            html += '<span class="product-detail__price-unit">' + units + ' × ' + formatPrice(selectedPrice) + '</span>';
        }

        if (isPlatter && page.dataset.productCompare) {
            const compare = Number(page.dataset.productCompare);
            if (compare > selectedPrice) {
                html = '<span class="product-detail__price-compare">' + formatPrice(compare * units) + '</span>' + html;
            }
        }

        if (priceDisplay) {
            priceDisplay.innerHTML = html;
        }

        if (mobilePriceValue) {
            mobilePriceValue.textContent = formatPrice(lineTotal);
        }
    }

    function updatePriceUI(price, label) {
        selectedPrice = price;
        selectedLabel = label;
        renderPrice();
        setAddButtonsEnabled(true);
    }

    function selectSize(option) {
        sizeOptions.forEach(function (opt) {
            const isSelected = opt === option;
            opt.classList.toggle('is-selected', isSelected);
            opt.setAttribute('aria-checked', isSelected ? 'true' : 'false');
        });

        selectedSize = option.dataset.value;
        selectedLabel = option.dataset.label || (selectedSize + ' Litres');
        selectedPrice = Number(option.dataset.price);

        updatePriceUI(selectedPrice, selectedLabel);
    }

    sizeOptions.forEach(function (option) {
        option.addEventListener('click', function () {
            selectSize(option);
        });

        option.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                selectSize(option);
            }
        });
    });

    if (qtyDecrease) {
        qtyDecrease.addEventListener('click', function () {
            setUnitCount(getUnitCount() - 1);
        });
    }

    if (qtyIncrease) {
        qtyIncrease.addEventListener('click', function () {
            setUnitCount(getUnitCount() + 1);
        });
    }

    function addToCart() {
        if (!cart || selectedPrice === null) {
            return;
        }

        productImage = getActiveImageSrc() || productImage;

        cart.addItem({
            id: productId,
            name: productName,
            quantity: selectedLabel,
            units: getUnitCount(),
            price: selectedPrice,
            image: productImage,
        });

        flashButton(addCartBtn);
        flashButton(mobileAddBtn);
    }

    if (addCartBtn) {
        addCartBtn.addEventListener('click', addToCart);
    }

    if (mobileAddBtn) {
        mobileAddBtn.addEventListener('click', addToCart);
    }

    function buildWishlistItem() {
        productImage = getActiveImageSrc() || productImage;

        if (isPlatter) {
            return {
                id: productId,
                name: productName,
                image: productImage,
                type: 'platter',
                price: selectedPrice,
                priceMin: selectedPrice,
                priceMax: selectedPrice,
            };
        }

        const priceValues = Object.values(prices).map(Number);

        return {
            id: productId,
            name: productName,
            image: productImage,
            type: 'litre',
            prices: prices,
            priceMin: priceValues.length ? Math.min.apply(null, priceValues) : 0,
            priceMax: priceValues.length ? Math.max.apply(null, priceValues) : 0,
        };
    }

    function syncWishlistButton() {
        if (!wishlistBtn || !wishlist) {
            return;
        }

        const active = wishlist.hasItem(productId);
        wishlistBtn.classList.toggle('is-active', active);
        wishlistBtn.setAttribute('aria-pressed', active ? 'true' : 'false');
    }

    if (wishlistBtn && wishlist) {
        syncWishlistButton();

        wishlistBtn.addEventListener('click', function () {
            if (wishlist.hasItem(productId)) {
                wishlist.removeItem(productId);
            } else {
                wishlist.addItem(buildWishlistItem());
            }

            syncWishlistButton();
        });

        window.addEventListener('edilicious:wishlist-updated', syncWishlistButton);
    }

  // ---- Gallery ----
    function initGallery() {
        if (!galleryRoot) {
            return;
        }

        let items = [];
        try {
            items = JSON.parse(galleryRoot.dataset.galleryItems || '[]');
        } catch {
            items = [];
        }

        if (!items.length) {
            return;
        }

        const heroEl = galleryRoot.querySelector('[data-gallery-hero]');
        const counterEl = galleryRoot.querySelector('[data-gallery-counter]');
        const thumbButtons = Array.from(galleryRoot.querySelectorAll('[data-gallery-thumb]'));
        const prevBtn = galleryRoot.querySelector('[data-gallery-prev]');
        const nextBtn = galleryRoot.querySelector('[data-gallery-next]');
        let activeIndex = 0;

        function pauseVideos() {
            galleryRoot.querySelectorAll('video').forEach(function (video) {
                video.pause();
            });
        }

        function buildGalleryVideo(item) {
            const attrs = [
                'class="product-gallery__video"',
                'data-gallery-video',
                'controls',
                'muted',
                'loop',
                'playsinline',
                'autoplay',
                'preload="auto"',
            ];

            if (item.poster) {
                attrs.push('poster="' + item.poster + '"');
            }

            return '<video ' + attrs.join(' ') + '>' +
                '<source src="' + item.src + '" type="' + videoMimeType(item.src) + '">' +
                '</video>';
        }

        function playHeroVideo() {
            const video = heroEl ? heroEl.querySelector('[data-gallery-video]') : null;
            if (!video || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }

            const playPromise = video.play();
            if (playPromise && typeof playPromise.catch === 'function') {
                playPromise.catch(function () {});
            }
        }

        function renderHero(index) {
            const item = items[index];
            if (!item || !heroEl) {
                return;
            }

            activeIndex = index;
            pauseVideos();

            if ((item.type || 'image') === 'video') {
                heroEl.innerHTML = buildGalleryVideo(item);
                playHeroVideo();
            } else {
                heroEl.innerHTML =
                    '<img src="' + item.src + '" alt="' + (item.alt || '') + '" class="product-gallery__image" data-gallery-image width="640" height="480" loading="eager">';
            }

            if (counterEl) {
                counterEl.textContent = (index + 1) + ' / ' + items.length;
            }

            thumbButtons.forEach(function (btn) {
                const thumbIndex = Number(btn.dataset.galleryIndex);
                const isActive = thumbIndex === index;
                btn.classList.toggle('is-active', isActive);
                btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            });
        }

        function step(delta) {
            const next = (activeIndex + delta + items.length) % items.length;
            renderHero(next);
        }

        thumbButtons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                renderHero(Number(btn.dataset.galleryIndex));
            });
        });

        if (prevBtn) {
            prevBtn.addEventListener('click', function () {
                step(-1);
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', function () {
                step(1);
            });
        }

        renderHero(0);
    }

    // ---- Tabs ----
    function initTabs() {
        const tabsRoot = page.querySelector('[data-product-tabs]');
        if (!tabsRoot) {
            return;
        }

        const tabs = Array.from(tabsRoot.querySelectorAll('[data-product-tab]'));
        const panels = Array.from(tabsRoot.querySelectorAll('[data-product-panel]'));

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                const target = tab.dataset.productTab;

                tabs.forEach(function (entry) {
                    const isActive = entry === tab;
                    entry.classList.toggle('is-active', isActive);
                    entry.setAttribute('aria-selected', isActive ? 'true' : 'false');
                });

                panels.forEach(function (panel) {
                    const isActive = panel.dataset.productPanel === target;
                    panel.classList.toggle('is-active', isActive);
                    panel.hidden = !isActive;
                });
            });
        });
    }

    if (isPlatter) {
        setAddButtonsEnabled(selectedPrice > 0);
        renderPrice();
    }

    initGallery();
    initTabs();
})();
