/**
 * Edilicious Foods — coupon codes (validate, apply, discount)
 */
(function (window) {
    'use strict';

    const APPLIED_KEY = 'edilicious_applied_coupon';
    const USED_KEY = 'edilicious_used_coupons';
    const PENDING_KEY = 'edilicious_pending_coupon';

    function normalizeCode(code) {
        return String(code || '').trim().toUpperCase();
    }

    function getCatalog() {
        return window.EDDILICIOUS_COUPONS || {};
    }

    function getUsedCoupons() {
        try {
            const used = JSON.parse(sessionStorage.getItem(USED_KEY) || '[]');
            return Array.isArray(used) ? used : [];
        } catch {
            return [];
        }
    }

    function markCouponUsed(code) {
        const normalized = normalizeCode(code);
        const used = getUsedCoupons();

        if (!used.includes(normalized)) {
            used.push(normalized);
            sessionStorage.setItem(USED_KEY, JSON.stringify(used));
        }
    }

    function getAppliedCode() {
        const stored = sessionStorage.getItem(APPLIED_KEY);
        if (!stored) {
            return null;
        }

        const normalized = normalizeCode(stored);
        return getCatalog()[normalized] ? normalized : null;
    }

    function getAppliedCoupon() {
        const code = getAppliedCode();
        return code ? getCatalog()[code] : null;
    }

    function setPendingCoupon(code) {
        const normalized = normalizeCode(code);
        if (!normalized) {
            return;
        }
        sessionStorage.setItem(PENDING_KEY, normalized);
    }

    function clearPendingCoupon() {
        sessionStorage.removeItem(PENDING_KEY);
    }

    function applyCoupon(rawCode) {
        const code = normalizeCode(rawCode);
        const catalog = getCatalog();
        const coupon = catalog[code];

        if (!code) {
            return { ok: false, message: 'Type a coupon code first.' };
        }

        if (!coupon) {
            removeCoupon();
            return { ok: false, message: 'That code did not work. Try again.' };
        }

        if (coupon.oncePerCustomer && getUsedCoupons().includes(code)) {
            removeCoupon();
            return { ok: false, message: 'You already used this code.' };
        }

        sessionStorage.setItem(APPLIED_KEY, code);
        clearPendingCoupon();

        window.dispatchEvent(new CustomEvent('edilicious:coupon-updated', {
            detail: { code: code, coupon: coupon },
        }));

        return {
            ok: true,
            message: coupon.message,
            code: code,
            coupon: coupon,
        };
    }

    function removeCoupon() {
        sessionStorage.removeItem(APPLIED_KEY);
        window.dispatchEvent(new CustomEvent('edilicious:coupon-updated', { detail: { code: null } }));
    }

    function consumePendingCoupon() {
        const pending = sessionStorage.getItem(PENDING_KEY);
        if (!pending) {
            return null;
        }
        return applyCoupon(pending);
    }

    function applyFromQueryString() {
        const params = new URLSearchParams(window.location.search);
        const code = params.get('coupon');
        if (!code) {
            return null;
        }
        return applyCoupon(code);
    }

    function calculateDiscount(subtotal) {
        const code = getAppliedCode();
        const amount = Number(subtotal) || 0;

        if (!code || amount <= 0) {
            return { discount: 0, code: null, coupon: null };
        }

        const coupon = getCatalog()[code];
        if (!coupon) {
            return { discount: 0, code: null, coupon: null };
        }

        let discount = 0;

        if (coupon.type === 'percent') {
            discount = amount * (Number(coupon.value) / 100);
        } else if (coupon.type === 'fixed') {
            discount = Math.min(amount, Number(coupon.value));
        }

        return {
            discount: Math.round(discount * 100) / 100,
            code: code,
            coupon: coupon,
        };
    }

    function formatDiscountLabel(coupon) {
        if (!coupon) {
            return '';
        }
        if (coupon.type === 'percent') {
            return coupon.value + '% off';
        }
        if (coupon.type === 'fixed') {
            return '£' + Number(coupon.value).toFixed(2) + ' off';
        }
        return coupon.code;
    }

    window.EdiliciousCoupons = {
        normalizeCode: normalizeCode,
        getAppliedCode: getAppliedCode,
        getAppliedCoupon: getAppliedCoupon,
        applyCoupon: applyCoupon,
        removeCoupon: removeCoupon,
        setPendingCoupon: setPendingCoupon,
        consumePendingCoupon: consumePendingCoupon,
        applyFromQueryString: applyFromQueryString,
        calculateDiscount: calculateDiscount,
        formatDiscountLabel: formatDiscountLabel,
        markCouponUsed: markCouponUsed,
        getUsedCoupons: getUsedCoupons,
    };
})(window);
