(function () {
    'use strict';

    const STORAGE_CONSENT = 'eddilicious_cookie_consent';
    const CONSENT_TTL_MS = 365 * 24 * 60 * 60 * 1000;
    const CONSENT_VISITOR_KEY = 'edd_consent_id';

    const config = window.EddiliciousCookieConsentConfig || {};
    let currentConsent = null;
    let ready = false;
    const readyQueue = [];

    function randomId() {
        if (window.crypto && typeof window.crypto.randomUUID === 'function') {
            return window.crypto.randomUUID();
        }

        return 'v' + Math.random().toString(36).slice(2) + Date.now().toString(36);
    }

    function getConsentVisitorId() {
        try {
            let id = window.localStorage.getItem(CONSENT_VISITOR_KEY);
            if (!id) {
                id = randomId();
                window.localStorage.setItem(CONSENT_VISITOR_KEY, id);
            }
            return id;
        } catch (error) {
            return randomId();
        }
    }

    function parseConsent(raw) {
        if (!raw || typeof raw !== 'object') {
            return null;
        }

        const savedAt = Number(raw.savedAt || 0);
        if (!savedAt || Date.now() - savedAt > CONSENT_TTL_MS) {
            return null;
        }

        const requiredVersion = String(config.version || '');
        if (requiredVersion && String(raw.version || '') !== requiredVersion) {
            return null;
        }

        return {
            necessary: true,
            functional: !!raw.functional,
            analytics: !!raw.analytics,
            marketing: !!raw.marketing,
            savedAt: savedAt,
            version: String(raw.version || requiredVersion || ''),
        };
    }

    function readCookie(name) {
        const prefix = name + '=';
        const parts = document.cookie.split(';');

        for (let i = 0; i < parts.length; i += 1) {
            const part = parts[i].trim();
            if (part.indexOf(prefix) === 0) {
                return decodeURIComponent(part.slice(prefix.length));
            }
        }

        return '';
    }

    function loadConsent() {
        if (currentConsent) {
            return currentConsent;
        }

        try {
            const stored = window.localStorage.getItem(STORAGE_CONSENT);
            if (stored) {
                const parsed = parseConsent(JSON.parse(stored));
                if (parsed) {
                    currentConsent = parsed;
                    return parsed;
                }
            }
        } catch (error) {
            // Ignore invalid storage.
        }

        const cookieValue = readCookie('edd_cookie_consent');
        if (cookieValue) {
            try {
                const parsed = parseConsent(JSON.parse(atob(cookieValue)));
                if (parsed) {
                    currentConsent = parsed;
                    persistConsent(parsed, false);
                    return parsed;
                }
            } catch (error) {
                // Ignore invalid cookie.
            }
        }

        return null;
    }

    function persistConsent(consent, syncServer, action) {
        currentConsent = consent;

        try {
            window.localStorage.setItem(STORAGE_CONSENT, JSON.stringify(consent));
        } catch (error) {
            // Ignore.
        }

        if (syncServer && config.saveUrl) {
            fetch(config.saveUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({
                    visitor_id: getConsentVisitorId(),
                    functional: consent.functional,
                    analytics: consent.analytics,
                    marketing: consent.marketing,
                    consent_version: consent.version || config.version || '',
                    action: action || 'save',
                    page_path: window.location.pathname + window.location.search,
                    user_agent: navigator.userAgent || '',
                }),
            }).catch(function () {
                // Ignore save errors; browser copy still works.
            });
        }
    }

    function clearCookie(name) {
        const secure = window.location.protocol === 'https:';
        document.cookie = name + '=; path=/; max-age=0; SameSite=Lax' + (secure ? '; Secure' : '');
    }

    function clearGoogleAnalyticsCookies() {
        const names = new Set(['_gid', '_gat']);

        if (config.ga4Id) {
            const suffix = config.ga4Id.replace(/^G-/, '');
            if (suffix) {
                names.add('_ga_' + suffix);
            }
        }

        document.cookie.split(';').forEach(function (part) {
            const name = part.split('=')[0].trim();
            if (!name) {
                return;
            }

            if (name.indexOf('_ga') === 0 || names.has(name)) {
                clearCookie(name);
            }
        });
    }

    function clearNonEssentialData(consent) {
        if (consent.analytics) {
            return;
        }

        try {
            window.localStorage.removeItem('edd_visitor_id');
            window.sessionStorage.removeItem('edd_session_id');
            window.sessionStorage.removeItem('edd_session_at');
        } catch (error) {
            // Ignore.
        }

        clearGoogleAnalyticsCookies();
        window.__eddGaLoaded = false;
    }

    function clearFunctionalData(consent) {
        if (consent.functional) {
            return;
        }

        try {
            window.sessionStorage.removeItem('edilicious_applied_coupon');
            window.sessionStorage.removeItem('edilicious_pending_coupon');
            window.sessionStorage.removeItem('edilicious_used_coupons');
        } catch (error) {
            // Ignore.
        }
    }

    function loadGoogleAnalytics() {
        if (!config.ga4Id || window.__eddGaLoaded) {
            return;
        }

        window.__eddGaLoaded = true;
        window.dataLayer = window.dataLayer || [];

        if (!window.gtag) {
            window.gtag = function () {
                window.dataLayer.push(arguments);
            };
        }

        const script = document.createElement('script');
        script.async = true;
        script.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(config.ga4Id);
        document.head.appendChild(script);

        window.gtag('js', new Date());
        window.gtag('config', config.ga4Id, { anonymize_ip: true });
    }

    function applyConsent(consent) {
        clearNonEssentialData(consent);
        clearFunctionalData(consent);

        if (consent.analytics) {
            loadGoogleAnalytics();
            document.dispatchEvent(new CustomEvent('edd:cookie-consent-analytics'));
        }

        if (consent.marketing) {
            const secure = window.location.protocol === 'https:';
            document.cookie = 'edd_marketing_opt_in=1; path=/; max-age=' + (365 * 24 * 60 * 60)
                + '; SameSite=Lax' + (secure ? '; Secure' : '');
        } else {
            clearCookie('edd_marketing_opt_in');
        }

        document.dispatchEvent(new CustomEvent('edd:cookie-consent', { detail: consent }));
    }

    function finishReady() {
        ready = true;
        while (readyQueue.length) {
            readyQueue.shift()();
        }
    }

    function saveConsent(partial, syncServer, action) {
        const consent = {
            necessary: true,
            functional: !!partial.functional,
            analytics: !!partial.analytics,
            marketing: !!partial.marketing,
            savedAt: Date.now(),
            version: String(config.version || ''),
        };

        persistConsent(consent, syncServer, action);
        applyConsent(consent);
        hideBanner();
        finishReady();
        return consent;
    }

    function hideBanner() {
        const root = document.querySelector('[data-cookie-consent]');
        if (root) {
            root.hidden = true;
        }
        document.body.classList.remove('cookie-consent-open');
    }

    function showBanner() {
        const root = document.querySelector('[data-cookie-consent]');
        if (root) {
            root.hidden = false;
        }
        document.body.classList.add('cookie-consent-open');

        const panel = root && root.querySelector('.cookie-consent__panel');
        if (panel && typeof panel.focus === 'function') {
            panel.setAttribute('tabindex', '-1');
            panel.focus();
        }
    }

    function readChoicesFromPanel() {
        const choices = {
            functional: false,
            analytics: false,
            marketing: false,
        };

        document.querySelectorAll('[data-cookie-choice]').forEach(function (input) {
            const key = input.getAttribute('data-cookie-choice');
            if (key && Object.prototype.hasOwnProperty.call(choices, key)) {
                choices[key] = !!input.checked;
            }
        });

        return choices;
    }

    function syncPanelFromConsent(consent) {
        const values = consent || { functional: false, analytics: false, marketing: false };

        document.querySelectorAll('[data-cookie-choice]').forEach(function (input) {
            const key = input.getAttribute('data-cookie-choice');
            if (key && Object.prototype.hasOwnProperty.call(values, key)) {
                input.checked = !!values[key];
            }
        });
    }

    function showSimpleView(root) {
        const choicesPanel = root.querySelector('[data-cookie-consent-choices]');
        const simpleActions = root.querySelector('[data-cookie-consent-simple-actions]');
        const detailActions = root.querySelector('[data-cookie-consent-detail-actions]');
        const introText = root.querySelector('[data-cookie-consent-intro]') || root.querySelector('#cookie-consent-text');

        if (choicesPanel) {
            choicesPanel.hidden = true;
        }
        if (simpleActions) {
            simpleActions.hidden = false;
        }
        if (detailActions) {
            detailActions.hidden = true;
        }
        if (introText) {
            introText.hidden = false;
        }
    }

    function showDetailView(root) {
        const choicesPanel = root.querySelector('[data-cookie-consent-choices]');
        const simpleActions = root.querySelector('[data-cookie-consent-simple-actions]');
        const detailActions = root.querySelector('[data-cookie-consent-detail-actions]');
        const introText = root.querySelector('#cookie-consent-text');

        if (choicesPanel) {
            choicesPanel.hidden = false;
        }
        if (simpleActions) {
            simpleActions.hidden = true;
        }
        if (detailActions) {
            detailActions.hidden = false;
        }
        if (introText) {
            introText.hidden = true;
        }
    }

    function openSettings(options) {
        const root = document.querySelector('[data-cookie-consent]');
        if (!root) {
            return;
        }

        syncPanelFromConsent(loadConsent());

        if (options && options.detail) {
            showDetailView(root);
        } else {
            showSimpleView(root);
        }

        showBanner();
    }

    function bindSettingsTriggers() {
        document.addEventListener('click', function (event) {
            const trigger = event.target.closest('[data-cookie-settings]');
            if (!trigger) {
                return;
            }

            event.preventDefault();
            openSettings({ detail: trigger.hasAttribute('data-cookie-settings-detail') });
        });
    }

    function bindBanner() {
        const root = document.querySelector('[data-cookie-consent]');
        if (!root) {
            finishReady();
            return;
        }

        root.querySelector('[data-cookie-consent-accept-all]')?.addEventListener('click', function () {
            saveConsent({ functional: true, analytics: true, marketing: true }, true, 'accept_all');
        });

        root.querySelector('[data-cookie-consent-reject-all]')?.addEventListener('click', function () {
            saveConsent({ functional: false, analytics: false, marketing: false }, true, 'reject_all');
        });

        root.querySelector('[data-cookie-consent-save]')?.addEventListener('click', function () {
            saveConsent(readChoicesFromPanel(), true, 'save_preferences');
        });

        root.querySelector('[data-cookie-consent-open-choices]')?.addEventListener('click', function () {
            syncPanelFromConsent(loadConsent());
            showDetailView(root);
        });

        root.querySelector('[data-cookie-consent-back]')?.addEventListener('click', function () {
            showSimpleView(root);
        });

        showSimpleView(root);
    }

    window.EddiliciousCookieConsent = {
        get: function () {
            return loadConsent();
        },
        has: function (category) {
            const consent = loadConsent();
            if (!consent) {
                return category === 'necessary';
            }

            if (category === 'necessary') {
                return true;
            }

            return !!consent[category];
        },
        whenReady: function (callback) {
            if (ready) {
                callback(loadConsent());
                return;
            }

            readyQueue.push(function () {
                callback(loadConsent());
            });
        },
        openSettings: function (options) {
            openSettings(options || {});
        },
    };

    bindSettingsTriggers();
    bindBanner();

    if (window.location.hash === '#manage-cookies') {
        const existingForManage = loadConsent();
        if (existingForManage) {
            applyConsent(existingForManage);
        }
        openSettings({ detail: true });
        finishReady();
        return;
    }

    const existing = loadConsent();
    if (existing) {
        applyConsent(existing);
        hideBanner();
        finishReady();
        return;
    }

    showBanner();
    finishReady();
})();
