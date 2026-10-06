/**
 * Navbar — menu dropdown (click + hover on desktop)
 */
(function () {
    'use strict';

    const CLOSE_DELAY_MS = 350;
    const closeTimers = new WeakMap();
    const mobileNav = document.querySelector('[data-mobile-nav]');
    const mobileNavToggle = document.querySelector('[data-mobile-nav-toggle]');
    const mobileNavCloseButtons = document.querySelectorAll('[data-mobile-nav-close]');

    const isDesktop = function () {
        return window.matchMedia('(min-width: 768px)').matches;
    };

    function closeMobileNav() {
        if (!mobileNav || !mobileNavToggle) {
            return;
        }

        mobileNav.hidden = true;
        mobileNavToggle.setAttribute('aria-expanded', 'false');
        mobileNavToggle.setAttribute('aria-label', 'Open menu');
        document.body.classList.remove('mobile-nav-open');
    }

    function openMobileNav() {
        if (!mobileNav || !mobileNavToggle) {
            return;
        }

        mobileNav.hidden = false;
        mobileNavToggle.setAttribute('aria-expanded', 'true');
        mobileNavToggle.setAttribute('aria-label', 'Close menu');
        document.body.classList.add('mobile-nav-open');
    }

    function toggleMobileSubmenu(button) {
        const controls = button.getAttribute('aria-controls');
        if (!controls) {
            return;
        }

        const submenu = document.getElementById(controls);
        if (!submenu) {
            return;
        }

        const expanded = button.getAttribute('aria-expanded') === 'true';
        button.setAttribute('aria-expanded', expanded ? 'false' : 'true');
        submenu.hidden = expanded;
    }

    function closeDropdown(dropdown) {
        if (!dropdown) {
            return;
        }

        const trigger = dropdown.querySelector('.navbar-pill--dropdown');
        const menu = dropdown.querySelector('.navbar-dropdown__menu');

        dropdown.classList.remove('is-open');
        if (trigger) {
            trigger.setAttribute('aria-expanded', 'false');
        }
        if (menu) {
            menu.hidden = true;
        }

        cancelCloseTimer(dropdown);
    }

    function closeAllDropdowns() {
        document.querySelectorAll('[data-navbar-dropdown].is-open').forEach(closeDropdown);
    }

    function openDropdownMenu(dropdown) {
        closeAllDropdowns();

        const trigger = dropdown.querySelector('.navbar-pill--dropdown');
        const menu = dropdown.querySelector('.navbar-dropdown__menu');

        dropdown.classList.add('is-open');
        if (trigger) {
            trigger.setAttribute('aria-expanded', 'true');
        }
        if (menu) {
            menu.hidden = false;
        }
    }

    function cancelCloseTimer(dropdown) {
        const timer = closeTimers.get(dropdown);
        if (timer) {
            clearTimeout(timer);
            closeTimers.delete(dropdown);
        }
    }

    function scheduleClose(dropdown) {
        cancelCloseTimer(dropdown);
        closeTimers.set(
            dropdown,
            window.setTimeout(function () {
                closeDropdown(dropdown);
            }, CLOSE_DELAY_MS)
        );
    }

    function bindDesktopHover(dropdown) {
        const trigger = dropdown.querySelector('.navbar-pill--dropdown');
        const menu = dropdown.querySelector('.navbar-dropdown__menu');
        const hoverTargets = [trigger, menu].filter(Boolean);

        hoverTargets.forEach(function (target) {
            target.addEventListener('mouseenter', function () {
                if (!isDesktop()) {
                    return;
                }
                cancelCloseTimer(dropdown);
                openDropdownMenu(dropdown);
            });

            target.addEventListener('mouseleave', function () {
                if (!isDesktop()) {
                    return;
                }
                scheduleClose(dropdown);
            });
        });
    }

    document.querySelectorAll('[data-navbar-dropdown]').forEach(function (dropdown) {
        const trigger = dropdown.querySelector('.navbar-pill--dropdown');
        const menu = dropdown.querySelector('.navbar-dropdown__menu');

        if (!trigger || !menu) {
            return;
        }

        trigger.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();

            if (dropdown.classList.contains('is-open')) {
                closeDropdown(dropdown);
            } else {
                openDropdownMenu(dropdown);
            }
        });

        bindDesktopHover(dropdown);

        menu.addEventListener('click', function (event) {
            event.stopPropagation();
        });
    });

    document.addEventListener('click', function (event) {
        if (!event.target.closest('[data-navbar-dropdown]')) {
            closeAllDropdowns();
        }
    });

    if (mobileNav && mobileNavToggle) {
        mobileNavToggle.addEventListener('click', function () {
            const isOpen = mobileNavToggle.getAttribute('aria-expanded') === 'true';
            if (isOpen) {
                closeMobileNav();
            } else {
                openMobileNav();
            }
        });

        mobileNavCloseButtons.forEach(function (button) {
            button.addEventListener('click', closeMobileNav);
        });

        mobileNav.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', closeMobileNav);
        });

        mobileNav.querySelectorAll('[data-mobile-submenu-toggle]').forEach(function (button) {
            button.addEventListener('click', function () {
                toggleMobileSubmenu(button);
            });
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeAllDropdowns();
            closeMobileNav();
        }
    });

    window.addEventListener('resize', function () {
        if (isDesktop()) {
            closeMobileNav();
        }
    });
})();
