/**
 * Checkout success — clear basket after paid Stripe return
 */
(function () {
    'use strict';

    const cartApi = window.EdiliciousCart;
    const coupons = window.EdiliciousCoupons;

    if (cartApi) {
        const appliedCode = coupons && typeof coupons.getAppliedCode === 'function'
            ? coupons.getAppliedCode()
            : null;

        if (appliedCode && typeof coupons.markCouponUsed === 'function') {
            coupons.markCouponUsed(appliedCode);
        }

        cartApi.clearCart();
    }

    if (coupons && typeof coupons.removeCoupon === 'function') {
        coupons.removeCoupon();
    }
})();
