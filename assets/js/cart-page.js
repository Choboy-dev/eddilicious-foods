/**
 * Cart page — render items, totals, coupon, fulfillment
 */
(function () {
    'use strict';

    const cartApi = window.EdiliciousCart;
    const coupons = window.EdiliciousCoupons;
    const productImages = window.EDDILICIOUS_PRODUCT_IMAGES || {};
    const DELIVERY_FEE = 0;
    const FALLBACK_IMAGE = 'assets/images/products/egusi-soup.jpeg';

    const itemsEl = document.querySelector('[data-cart-items]');
    const emptyEl = document.querySelector('[data-cart-empty]');
    const leadNoticeEl = document.querySelector('[data-cart-lead-notice]');
    const subtotalEl = document.querySelector('[data-cart-subtotal]');
    const discountEl = document.querySelector('[data-cart-discount]');
    const deliveryEl = document.querySelector('[data-cart-delivery]');
    const totalEl = document.querySelector('[data-cart-total]');
    const couponInput = document.querySelector('[data-cart-coupon-input]');
    const applyCouponBtn = document.querySelector('[data-cart-apply-coupon]');
    const removeCouponBtn = document.querySelector('[data-cart-remove-coupon]');
    const couponMessage = document.querySelector('[data-cart-coupon-message]');
    const checkoutBtn = document.querySelector('[data-cart-checkout]');
    const fulfillmentInputs = document.querySelectorAll('[data-cart-fulfillment]');

    if (!cartApi || !coupons || !itemsEl) {
        return;
    }

    function getItemImage(item) {
        return item.image || productImages[item.id] || FALLBACK_IMAGE;
    }

    function getDeliveryFee(cartItems) {
        if (!cartItems.length) {
            return 0;
        }

        if (cartApi.getFulfillment() === 'collection') {
            return 0;
        }

        return DELIVERY_FEE;
    }

    function calculateTotals(cartItems) {
        const subtotal = cartApi.getSubtotal(cartItems);
        const discountResult = coupons.calculateDiscount(subtotal);
        const delivery = getDeliveryFee(cartItems);
        const total = Math.max(0, subtotal - discountResult.discount + delivery);

        return {
            subtotal: subtotal,
            discount: discountResult.discount,
            delivery: delivery,
            total: total,
            appliedCode: discountResult.code,
            appliedCoupon: discountResult.coupon,
        };
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function escapeAttr(text) {
        return escapeHtml(text).replace(/"/g, '&quot;');
    }

    function showCouponMessage(text, isError) {
        if (!couponMessage) {
            return;
        }

        couponMessage.hidden = !text;
        couponMessage.textContent = text || '';
        couponMessage.classList.toggle('is-error', Boolean(isError));
    }

    function syncCouponField() {
        const appliedCode = coupons.getAppliedCode();

        if (couponInput) {
            couponInput.value = appliedCode || '';
            couponInput.readOnly = Boolean(appliedCode);
        }

        if (removeCouponBtn) {
            removeCouponBtn.hidden = !appliedCode;
        }

        if (applyCouponBtn) {
            applyCouponBtn.hidden = Boolean(appliedCode);
        }
    }

    function syncFulfillmentField() {
        const current = cartApi.getFulfillment();
        fulfillmentInputs.forEach(function (input) {
            input.checked = input.value === current;
        });
    }

    function renderItems(cartItems) {
        itemsEl.innerHTML = '';

        cartItems.forEach(function (item) {
            const li = document.createElement('li');
            li.className = 'cart-item';
            li.dataset.lineId = item.lineId;
            const imageSrc = getItemImage(item);

            li.innerHTML =
                '<div class="cart-item__thumb">' +
                    '<img src="' + escapeAttr(imageSrc) + '" alt="" class="cart-item__image" width="64" height="64" loading="lazy">' +
                '</div>' +
                '<div class="cart-item__info">' +
                    '<span class="cart-item__price">' + escapeHtml(cartApi.formatPrice(item.price * Math.max(1, Number(item.units || 1)))) + '</span>' +
                    '<span class="cart-item__name">' + escapeHtml(item.name) + '</span>' +
                    '<span class="cart-item__meta">' + escapeHtml(cartApi.formatLineMeta(item)) + '</span>' +
                '</div>' +
                '<button type="button" class="cart-item__remove" data-remove-line="' + escapeHtml(item.lineId) + '" aria-label="Remove ' + escapeHtml(item.name) + ' from basket">' +
                    '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
                        '<path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>' +
                    '</svg>' +
                '</button>';

            itemsEl.appendChild(li);
        });

        const hasItems = cartItems.length > 0;
        emptyEl.hidden = hasItems;
        itemsEl.hidden = !hasItems;

        if (leadNoticeEl) {
            leadNoticeEl.hidden = !hasItems;
        }

        if (checkoutBtn) {
            checkoutBtn.classList.toggle('is-disabled', !hasItems);
            checkoutBtn.setAttribute('aria-disabled', hasItems ? 'false' : 'true');
        }
    }

    function renderTotals(cartItems) {
        const totals = calculateTotals(cartItems);

        subtotalEl.textContent = cartApi.formatPrice(totals.subtotal);
        discountEl.textContent = totals.discount > 0
            ? '-' + cartApi.formatPrice(totals.discount)
            : '-' + cartApi.formatPrice(0);
        deliveryEl.textContent = totals.delivery > 0
            ? cartApi.formatPrice(totals.delivery)
            : 'Free';
        totalEl.textContent = cartApi.formatPrice(totals.total);

        if (totals.appliedCoupon) {
            showCouponMessage(totals.appliedCoupon.message, false);
        }
    }

    function render() {
        const cartItems = cartApi.getCart();
        syncCouponField();
        syncFulfillmentField();
        renderItems(cartItems);
        renderTotals(cartItems);
    }

    function tryApplyCoupon() {
        if (!couponInput) {
            return;
        }

        const result = coupons.applyCoupon(couponInput.value);

        if (!result.ok) {
            showCouponMessage(result.message, true);
            renderTotals(cartApi.getCart());
            return;
        }

        showCouponMessage(result.message, false);
        render();
    }

    itemsEl.addEventListener('click', function (event) {
        const btn = event.target.closest('[data-remove-line]');
        if (!btn) {
            return;
        }

        cartApi.removeItem(btn.getAttribute('data-remove-line'));
        render();
    });

    if (applyCouponBtn) {
        applyCouponBtn.addEventListener('click', tryApplyCoupon);
    }

    if (couponInput) {
        couponInput.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                tryApplyCoupon();
            }
        });
    }

    if (removeCouponBtn) {
        removeCouponBtn.addEventListener('click', function () {
            coupons.removeCoupon();
            if (couponInput) {
                couponInput.readOnly = false;
                couponInput.value = '';
                couponInput.focus();
            }
            showCouponMessage('We removed your coupon.', false);
            render();
        });
    }

    fulfillmentInputs.forEach(function (input) {
        input.addEventListener('change', function () {
            if (input.checked) {
                cartApi.setFulfillment(input.value);
            }
            render();
        });
    });

    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', function (event) {
            if (!cartApi.getCart().length || checkoutBtn.classList.contains('is-disabled')) {
                event.preventDefault();
                return;
            }

            const auth = window.EDDILICIOUS_AUTH || {};
            if (!auth.loggedIn) {
                event.preventDefault();
                window.location.href = 'signup.php?redirect=' + encodeURIComponent('checkout.php');
            }
        });
    }

    window.addEventListener('edilicious:cart-updated', render);
    window.addEventListener('edilicious:coupon-updated', render);

    const queryResult = coupons.applyFromQueryString();
    if (queryResult && !queryResult.ok) {
        showCouponMessage(queryResult.message, true);
    }

    const pendingResult = coupons.consumePendingCoupon();
    if (pendingResult) {
        showCouponMessage(pendingResult.message, !pendingResult.ok);
    }

    render();
})();
