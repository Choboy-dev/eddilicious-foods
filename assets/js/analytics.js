(function () {
    'use strict';

    const STORAGE_VISITOR = 'edd_visitor_id';
    const STORAGE_SESSION = 'edd_session_id';
    const STORAGE_SESSION_AT = 'edd_session_at';
    const SESSION_TIMEOUT_MS = 30 * 60 * 1000;

    function randomId() {
        if (window.crypto && typeof window.crypto.randomUUID === 'function') {
            return window.crypto.randomUUID();
        }

        return 'v' + Math.random().toString(36).slice(2) + Date.now().toString(36);
    }

    function getVisitorId() {
        try {
            let id = window.localStorage.getItem(STORAGE_VISITOR);
            if (!id) {
                id = randomId();
                window.localStorage.setItem(STORAGE_VISITOR, id);
            }
            return id;
        } catch (error) {
            return randomId();
        }
    }

    function getSessionId() {
        try {
            const now = Date.now();
            const lastAt = Number(window.sessionStorage.getItem(STORAGE_SESSION_AT) || 0);
            let id = window.sessionStorage.getItem(STORAGE_SESSION);

            if (!id || !lastAt || now - lastAt > SESSION_TIMEOUT_MS) {
                id = randomId();
                window.sessionStorage.setItem(STORAGE_SESSION, id);
            }

            window.sessionStorage.setItem(STORAGE_SESSION_AT, String(now));
            return id;
        } catch (error) {
            return randomId();
        }
    }

    function collectBasePath() {
        const path = window.location.pathname || '/';
        const marker = '/admin/';
        const adminIndex = path.indexOf(marker);
        if (adminIndex !== -1) {
            return path.slice(0, adminIndex + 1);
        }

        const lastSlash = path.lastIndexOf('/');
        if (lastSlash <= 0) {
            return '/';
        }

        return path.slice(0, lastSlash + 1);
    }

    function currentPath() {
        const path = window.location.pathname || '/';
        const search = window.location.search || '';
        return path + search;
    }

    function utmParams() {
        const params = new URLSearchParams(window.location.search);

        return {
            utm_source: params.get('utm_source') || '',
            utm_medium: params.get('utm_medium') || '',
            utm_campaign: params.get('utm_campaign') || '',
        };
    }

    function shouldTrack() {
        const path = window.location.pathname || '';
        if (path.includes('/admin/')) {
            return false;
        }

        if (document.documentElement && document.documentElement.dataset.noAnalytics === 'true') {
            return false;
        }

        return true;
    }

    function canTrackAnalytics() {
        if (window.EddiliciousCookieConsent && !window.EddiliciousCookieConsent.has('analytics')) {
            return false;
        }

        return shouldTrack();
    }

    function sendPageview() {
        if (!canTrackAnalytics()) {
            return;
        }

        const payload = Object.assign({
            path: currentPath(),
            page_title: document.title || '',
            referrer: document.referrer || '',
            session_id: getSessionId(),
            visitor_id: getVisitorId(),
            screen_width: window.innerWidth || null,
            language: navigator.language || '',
        }, utmParams());

        const endpoint = collectBasePath() + 'api/analytics/collect.php';

        if (navigator.sendBeacon) {
            const blob = new Blob([JSON.stringify(payload)], { type: 'application/json' });
            if (navigator.sendBeacon(endpoint, blob)) {
                return;
            }
        }

        fetch(endpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
            keepalive: true,
            credentials: 'same-origin',
        }).catch(function () {
            // Ignore tracking errors.
        });
    }

    function startTracking() {
        if (!canTrackAnalytics()) {
            return;
        }

        sendPageview();
    }

    function initAnalytics() {
        if (window.EddiliciousCookieConsent && typeof window.EddiliciousCookieConsent.whenReady === 'function') {
            window.EddiliciousCookieConsent.whenReady(function () {
                startTracking();
            });
            document.addEventListener('edd:cookie-consent-analytics', startTracking);
            return;
        }

        startTracking();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAnalytics);
    } else {
        initAnalytics();
    }
})();
