(function () {
    'use strict';

    const SESSION_KEY = 'eddilicious_site_terms_accepted';
    const SHOW_DELAY_MS = 10000;

    const config = window.EddiliciousSiteTermsNoticeConfig || {};
    const root = document.querySelector('[data-site-terms-notice]');
    const checkbox = document.querySelector('[data-site-terms-checkbox]');
    const continueBtn = document.querySelector('[data-site-terms-continue]');

    let showTimer = null;

    function hasAcceptedThisVisit() {
        try {
            const stored = window.sessionStorage.getItem(SESSION_KEY);
            if (!stored) {
                return false;
            }

            const parsed = JSON.parse(stored);
            const version = String(config.version || '');
            if (version && String(parsed.version || '') !== version) {
                return false;
            }

            return !!parsed.acceptedAt;
        } catch (error) {
            return false;
        }
    }

    function saveAcceptanceThisVisit() {
        const payload = {
            acceptedAt: Date.now(),
            version: String(config.version || ''),
        };

        try {
            window.sessionStorage.setItem(SESSION_KEY, JSON.stringify(payload));
        } catch (error) {
            // Ignore storage errors.
        }

        document.dispatchEvent(new CustomEvent('edd:site-terms-accepted', { detail: payload }));
    }

    function resetNoticeForm() {
        if (checkbox) {
            checkbox.checked = false;
        }
        if (continueBtn) {
            continueBtn.disabled = true;
        }
    }

    function showNotice() {
        if (!root || hasAcceptedThisVisit()) {
            return;
        }

        const cookieBanner = document.querySelector('[data-cookie-consent]');
        if (cookieBanner && !cookieBanner.hidden) {
            return;
        }

        resetNoticeForm();
        root.hidden = false;
        document.body.classList.add('site-terms-notice-open');

        const panel = root.querySelector('.site-terms-notice__panel');
        if (panel) {
            panel.setAttribute('tabindex', '-1');
            panel.focus();
        }
    }

    function hideNotice() {
        if (!root) {
            return;
        }

        root.hidden = true;
        document.body.classList.remove('site-terms-notice-open');
    }

    function scheduleShow() {
        if (hasAcceptedThisVisit() || showTimer) {
            return;
        }

        showTimer = window.setTimeout(function () {
            showTimer = null;
            showNotice();
        }, SHOW_DELAY_MS);
    }

    function bindNotice() {
        if (!root || !checkbox || !continueBtn) {
            return;
        }

        checkbox.addEventListener('change', function () {
            continueBtn.disabled = !checkbox.checked;
        });

        continueBtn.addEventListener('click', function () {
            if (!checkbox.checked) {
                return;
            }

            saveAcceptanceThisVisit();
            hideNotice();
        });
    }

    function init() {
        if (!root || hasAcceptedThisVisit()) {
            return;
        }

        bindNotice();
        resetNoticeForm();

        const cookieApi = window.EddiliciousCookieConsent;
        if (!cookieApi || typeof cookieApi.whenReady !== 'function') {
            scheduleShow();
            return;
        }

        cookieApi.whenReady(function (consent) {
            if (hasAcceptedThisVisit()) {
                return;
            }

            if (consent) {
                scheduleShow();
                return;
            }

            document.addEventListener('edd:cookie-consent', function () {
                scheduleShow();
            }, { once: true });
        });
    }

    init();
})();
