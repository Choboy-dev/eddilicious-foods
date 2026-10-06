/**
 * Eddilicious Foods — admin mobile fly-in navigation
 */
(function () {
    'use strict';

    const body = document.body;
    const toggle = document.querySelector('[data-admin-nav-toggle]');
    const backdrop = document.querySelector('[data-admin-nav-backdrop]');
    const sidebar = document.querySelector('[data-admin-sidebar]');
    const mobileQuery = window.matchMedia('(max-width: 1023px)');

    if (!toggle || !sidebar) {
        return;
    }

    function isMobileNav() {
        return mobileQuery.matches;
    }

    function setNavOpen(open) {
        if (!isMobileNav()) {
            body.classList.remove('admin-nav-open');
            toggle.setAttribute('aria-expanded', 'false');
            if (backdrop) {
                backdrop.hidden = true;
            }
            sidebar.removeAttribute('aria-hidden');
            return;
        }

        const shouldOpen = !!open;

        body.classList.toggle('admin-nav-open', shouldOpen);
        toggle.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
        toggle.setAttribute('aria-label', shouldOpen ? 'Close admin menu' : 'Open admin menu');

        if (backdrop) {
            backdrop.hidden = !shouldOpen;
            backdrop.setAttribute('aria-hidden', shouldOpen ? 'false' : 'true');
        }

        sidebar.setAttribute('aria-hidden', shouldOpen ? 'false' : 'true');
    }

    function closeNav() {
        setNavOpen(false);
    }

    toggle.addEventListener('click', function (event) {
        event.preventDefault();
        event.stopPropagation();
        setNavOpen(!body.classList.contains('admin-nav-open'));
    });

    if (backdrop) {
        backdrop.addEventListener('click', closeNav);
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeNav();
        }
    });

    sidebar.querySelectorAll('.admin-nav__link').forEach(function (link) {
        link.addEventListener('click', function () {
            if (isMobileNav()) {
                closeNav();
            }
        });
    });

    mobileQuery.addEventListener('change', function () {
        closeNav();
    });

    closeNav();
})();
