/**
 * Product cards — quantity dropdown & add to cart
 */
(function () {
    'use strict';

    const cart = window.EdiliciousCart;
    const wishlist = window.EdiliciousWishlist;

    let openDropdown = null;

    function closeDropdown(dropdown) {
        if (!dropdown) {
            return;
        }

        const trigger = dropdown.querySelector('.qty-dropdown__trigger');
        const menu = dropdown.querySelector('.qty-dropdown__menu');

        dropdown.classList.remove('is-open');
        if (trigger) {
            trigger.setAttribute('aria-expanded', 'false');
        }
        if (menu) {
            menu.hidden = true;
        }

        if (openDropdown === dropdown) {
            openDropdown = null;
        }
    }

    function closeAllDropdowns() {
        document.querySelectorAll('.qty-dropdown.is-open').forEach(closeDropdown);
    }

    function openDropdownMenu(dropdown) {
        closeAllDropdowns();

        const trigger = dropdown.querySelector('.qty-dropdown__trigger');
        const menu = dropdown.querySelector('.qty-dropdown__menu');

        dropdown.classList.add('is-open');
        if (trigger) {
            trigger.setAttribute('aria-expanded', 'true');
        }
        if (menu) {
            menu.hidden = false;
        }

        openDropdown = dropdown;
    }

    function syncDropdownOptionPrices(card, prices) {
        if (!cart) {
            return;
        }

        card.querySelectorAll('.qty-dropdown__option').forEach(function (option) {
            const qty = option.dataset.value;
            const sub = option.querySelector('.qty-dropdown__option-sub');
            if (!qty || !sub || prices[qty] === undefined || prices[qty] === null) {
                return;
            }

            sub.textContent = cart.formatPrice(Number(prices[qty]));
        });
    }

    function applyQuantity(card, select, labelEl, priceEl, cartBtn, prices, qty, labelText) {
        select.value = qty;

        if (!qty || !prices[qty]) {
            labelEl.textContent = 'Pick a size';
            labelEl.classList.add('qty-dropdown__value--placeholder');
            priceEl.classList.remove('is-visible');
            priceEl.innerHTML = '';
            cartBtn.disabled = true;
            return;
        }

        labelEl.textContent = labelText;
        labelEl.classList.remove('qty-dropdown__value--placeholder');
        priceEl.classList.add('is-visible');
        if (cart) {
            priceEl.innerHTML = '<span class="product-card__price-value">' + cart.formatPrice(prices[qty]) + '</span>';
        }
        cartBtn.disabled = false;
    }

    function initCustomDropdown(card, dropdown, select, labelEl, priceEl, cartBtn, prices) {
        const trigger = dropdown.querySelector('.qty-dropdown__trigger');
        const menu = dropdown.querySelector('.qty-dropdown__menu');
        const options = dropdown.querySelectorAll('.qty-dropdown__option');

        if (!trigger || !menu) {
            return;
        }

        trigger.addEventListener('click', function (event) {
            event.stopPropagation();
            if (dropdown.classList.contains('is-open')) {
                closeDropdown(dropdown);
            } else {
                openDropdownMenu(dropdown);
            }
        });

        options.forEach(function (option) {
            option.addEventListener('click', function () {
                const qty = option.dataset.value;
                const labelText = option.querySelector('.qty-dropdown__option-main')?.textContent?.trim() || '';

                options.forEach(function (opt) {
                    opt.setAttribute('aria-selected', opt === option ? 'true' : 'false');
                });

                applyQuantity(card, select, labelEl, priceEl, cartBtn, prices, qty, labelText);
                closeDropdown(dropdown);
            });

            option.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    option.click();
                }
            });
        });

        trigger.addEventListener('keydown', function (event) {
            if (event.key === 'ArrowDown' || event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                openDropdownMenu(dropdown);
                const firstOption = menu.querySelector('.qty-dropdown__option');
                if (firstOption) {
                    firstOption.focus();
                }
            }
        });
    }

    function flashAdded(cartBtn) {
        if (!cart) {
            return;
        }
        cartBtn.textContent = 'Added!';
        cartBtn.classList.add('is-added');

        window.setTimeout(function () {
            cartBtn.textContent = 'Add to Cart';
            cartBtn.classList.remove('is-added');
        }, 1600);
    }

    function initWishlistButton(card, productData) {
        if (!wishlist) {
            return;
        }

        card.querySelectorAll('[data-action="wishlist"]').forEach(function (btn) {
            function syncState() {
                const saved = wishlist.hasItem(productData.id);
                btn.classList.toggle('is-active', saved);
                btn.setAttribute('aria-pressed', saved ? 'true' : 'false');
            }

            btn.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();
                wishlist.toggleItem(productData);
                syncState();
            });

            syncState();
        });
    }

    function initProductCard(card) {
        const prices = JSON.parse(card.dataset.prices || '{}');
        const select = card.querySelector('[data-qty-select]');
        const dropdown = card.querySelector('[data-qty-dropdown]');
        const labelEl = card.querySelector('[data-qty-label]');
        const priceEl = card.querySelector('[data-price-display]');
        const cartBtn = card.querySelector('[data-add-to-cart]');
        const productId = card.dataset.productId;
        const productName = card.querySelector('.product-card__name')?.textContent?.trim() || '';
        const productImage = card.querySelector('.product-card__image')?.getAttribute('src') || '';

        if (!select || !dropdown || !labelEl || !priceEl || !cartBtn) {
            return;
        }

        initCustomDropdown(card, dropdown, select, labelEl, priceEl, cartBtn, prices);
        syncDropdownOptionPrices(card, prices);

        if (cart) {
            cartBtn.addEventListener('click', function () {
                const qty = select.value;
                if (!qty || !prices[qty]) {
                    return;
                }

                cart.addItem({
                    id: productId,
                    name: productName,
                    quantity: qty + ' Litres',
                    price: prices[qty],
                    image: productImage,
                });

                flashAdded(cartBtn);
            });
        }

        const priceValues = Object.values(prices).map(Number);
        initWishlistButton(card, {
            id: productId,
            name: productName,
            image: productImage,
            type: 'litre',
            prices: prices,
            priceMin: priceValues.length ? Math.min.apply(null, priceValues) : 0,
            priceMax: priceValues.length ? Math.max.apply(null, priceValues) : 0,
        });
    }

    function initPlatterCard(card) {
        const cartBtn = card.querySelector('[data-add-to-cart]');
        const productId = card.dataset.productId;
        const productName = card.querySelector('.product-card__name')?.textContent?.trim() || '';
        const productImage = card.querySelector('.product-card__image')?.getAttribute('src') || '';
        const price = Number(card.dataset.price);

        if (!cartBtn || !productId || !price) {
            return;
        }

        if (cart) {
            cartBtn.addEventListener('click', function () {
                cart.addItem({
                    id: productId,
                    name: productName,
                    quantity: '1 Tray',
                    price: price,
                    image: productImage,
                });

                flashAdded(cartBtn);
            });
        }

        initWishlistButton(card, {
            id: productId,
            name: productName,
            image: productImage,
            type: 'platter',
            price: price,
            priceMin: price,
            priceMax: price,
        });
    }

    function initCards(root) {
        const scope = root || document;

        scope.querySelectorAll('.product-card:not(.product-card--platter):not([data-product-init])').forEach(function (card) {
            card.dataset.productInit = 'true';
            initProductCard(card);
        });

        scope.querySelectorAll('.product-card--platter:not([data-product-init])').forEach(function (card) {
            card.dataset.productInit = 'true';
            initPlatterCard(card);
        });

        scope.querySelectorAll('.product-card--drink:not([data-product-init])').forEach(function (card) {
            card.dataset.productInit = 'true';
            initProductCard(card);
        });
    }

    function initCardVideos() {
        const cards = document.querySelectorAll('.product-card');

        cards.forEach(function (card) {
            const video = card.querySelector('[data-product-card-video]');
            if (!video) {
                return;
            }

            const previewTime = parseFloat(video.dataset.previewTime || '0.35') || 0.35;
            const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            function showPreviewFrame() {
                if (video.readyState < 1) {
                    return;
                }

                try {
                    if (Math.abs(video.currentTime - previewTime) > 0.05) {
                        video.currentTime = previewTime;
                    }
                } catch (error) {
                    /* Seek can fail before metadata is ready */
                }
            }

            video.addEventListener('loadedmetadata', showPreviewFrame);
            video.addEventListener('loadeddata', showPreviewFrame);

            if (video.readyState >= 1) {
                showPreviewFrame();
            }

            video.pause();

            if (reducedMotion) {
                return;
            }

            function playVideo() {
                const playPromise = video.play();
                if (playPromise && typeof playPromise.catch === 'function') {
                    playPromise.catch(function () {});
                }
            }

            function pauseVideo() {
                video.pause();
                showPreviewFrame();
            }

            card.addEventListener('mouseenter', playVideo);
            card.addEventListener('mouseleave', pauseVideo);
            card.addEventListener('focusin', playVideo);
            card.addEventListener('focusout', pauseVideo);
        });
    }

    document.addEventListener('click', function () {
        closeAllDropdowns();
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeAllDropdowns();
        }
    });

    window.EdiliciousProducts = {
        initCards: initCards,
        initCardVideos: initCardVideos,
    };

    initCards();
    initCardVideos();
})();
