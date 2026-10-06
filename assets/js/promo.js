/**
 * Promo banner — copy coupon code and queue it for the cart
 */
(function () {
    'use strict';

    const coupons = window.EdiliciousCoupons;

    document.querySelectorAll('[data-copy-code]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const code = btn.getAttribute('data-copy-code');
            if (!code) {
                return;
            }

            if (coupons) {
                coupons.setPendingCoupon(code);
            }

            const copyText = btn.querySelector('.promo-banner__code-text');
            const originalText = copyText ? copyText.textContent : code;

            function onCopied() {
                btn.classList.add('is-copied');
                if (copyText) {
                    copyText.textContent = 'Copied!';
                }

                window.setTimeout(function () {
                    btn.classList.remove('is-copied');
                    if (copyText) {
                        copyText.textContent = originalText;
                    }
                }, 1800);
            }

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(code).then(onCopied).catch(function () {
                    fallbackCopy(code, onCopied);
                });
            } else {
                fallbackCopy(code, onCopied);
            }
        });
    });

    function fallbackCopy(text, callback) {
        const input = document.createElement('textarea');
        input.value = text;
        input.setAttribute('readonly', '');
        input.style.position = 'absolute';
        input.style.left = '-9999px';
        document.body.appendChild(input);
        input.select();

        try {
            document.execCommand('copy');
            callback();
        } catch {
            /* ignore */
        }

        document.body.removeChild(input);
    }
})();
