/**
 * Custom select — styled dropdown that keeps the native <select> for forms and JS.
 */
(function () {
    'use strict';

    const CHEVRON_SVG = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>';

    const ENHANCE_SELECTOR = 'select[data-custom-select]';
    let openWrapper = null;

    function getLabel(select) {
        const option = select.options[select.selectedIndex];
        return option ? option.textContent.trim() : '';
    }

    function buildOptions(select, menu) {
        menu.innerHTML = '';

        Array.from(select.options).forEach(function (option) {
            if (option.disabled) {
                return;
            }

            const item = document.createElement('li');
            item.className = 'app-select__option';
            item.setAttribute('role', 'option');
            item.setAttribute('tabindex', '-1');
            item.dataset.value = option.value;
            item.textContent = option.textContent.trim();
            item.setAttribute('aria-selected', option.selected ? 'true' : 'false');
            menu.appendChild(item);
        });
    }

    function syncDisplay(select, wrapper) {
        const labelEl = wrapper.querySelector('[data-app-select-label]');
        const value = select.value;
        const label = getLabel(select);

        if (labelEl) {
            labelEl.textContent = label;
            labelEl.classList.toggle('app-select__value--placeholder', value === '');
        }

        wrapper.querySelectorAll('.app-select__option').forEach(function (option) {
            option.setAttribute('aria-selected', option.dataset.value === value ? 'true' : 'false');
        });
    }

    function resetMenuPosition(wrapper, menu) {
        menu.style.position = '';
        menu.style.top = '';
        menu.style.left = '';
        menu.style.width = '';
        menu.style.right = '';
        menu.style.zIndex = '';
        wrapper.classList.remove('is-fixed-menu');
    }

    function positionMenu(wrapper, trigger, menu) {
        const useFixed = !!wrapper.closest('.admin-modal');

        if (!useFixed) {
            resetMenuPosition(wrapper, menu);
            return;
        }

        const rect = trigger.getBoundingClientRect();
        wrapper.classList.add('is-fixed-menu');
        menu.style.position = 'fixed';
        menu.style.top = rect.bottom + 4 + 'px';
        menu.style.left = rect.left + 'px';
        menu.style.width = rect.width + 'px';
        menu.style.right = 'auto';
        menu.style.zIndex = '300';
    }

    function closeWrapper(wrapper) {
        if (!wrapper) {
            return;
        }

        const trigger = wrapper.querySelector('.app-select__trigger');
        const menu = wrapper.querySelector('.app-select__menu');

        wrapper.classList.remove('is-open');
        if (trigger) {
            trigger.setAttribute('aria-expanded', 'false');
        }
        if (menu) {
            menu.hidden = true;
            resetMenuPosition(wrapper, menu);
        }

        if (openWrapper === wrapper) {
            openWrapper = null;
        }
    }

    function closeAll() {
        document.querySelectorAll('.app-select.is-open').forEach(closeWrapper);
    }

    function openWrapperMenu(wrapper) {
        closeAll();

        const trigger = wrapper.querySelector('.app-select__trigger');
        const menu = wrapper.querySelector('.app-select__menu');
        const select = wrapper.querySelector('select');

        if (!trigger || !menu || !select || select.disabled) {
            return;
        }

        wrapper.classList.add('is-open');
        trigger.setAttribute('aria-expanded', 'true');
        menu.hidden = false;
        positionMenu(wrapper, trigger, menu);
        openWrapper = wrapper;
    }

    function chooseOption(wrapper, select, optionEl) {
        const value = optionEl.dataset.value;
        const previous = select.value;

        select.value = value;
        syncDisplay(select, wrapper);

        if (previous !== value) {
            select.dispatchEvent(new Event('change', { bubbles: true }));
        }

        closeWrapper(wrapper);
        wrapper.querySelector('.app-select__trigger')?.focus();
    }

    function bindWrapper(wrapper, select) {
        const trigger = wrapper.querySelector('.app-select__trigger');
        const menu = wrapper.querySelector('.app-select__menu');
        const labelEl = wrapper.querySelector('[data-app-select-label]');
        const triggerId = select.id ? select.id + '-trigger' : 'app-select-trigger-' + Math.random().toString(36).slice(2, 9);
        const menuId = select.id ? select.id + '-menu' : triggerId + '-menu';

        trigger.id = triggerId;
        menu.id = menuId;
        trigger.setAttribute('aria-controls', menuId);

        if (select.id) {
            const label = document.querySelector('label[for="' + CSS.escape(select.id) + '"]');
            if (label) {
                label.setAttribute('for', triggerId);
            }
        }

        trigger.addEventListener('click', function (event) {
            event.stopPropagation();
            if (wrapper.classList.contains('is-open')) {
                closeWrapper(wrapper);
            } else {
                openWrapperMenu(wrapper);
            }
        });

        menu.addEventListener('click', function (event) {
            const optionEl = event.target.closest('.app-select__option');
            if (!optionEl) {
                return;
            }
            chooseOption(wrapper, select, optionEl);
        });

        menu.addEventListener('keydown', function (event) {
            const options = Array.from(menu.querySelectorAll('.app-select__option'));
            const currentIndex = options.indexOf(document.activeElement);

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                const next = options[Math.min(currentIndex + 1, options.length - 1)] || options[0];
                next?.focus();
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                const prev = options[Math.max(currentIndex - 1, 0)] || options[options.length - 1];
                prev?.focus();
            } else if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                if (document.activeElement.classList.contains('app-select__option')) {
                    chooseOption(wrapper, select, document.activeElement);
                }
            } else if (event.key === 'Escape') {
                event.preventDefault();
                closeWrapper(wrapper);
                trigger.focus();
            }
        });

        trigger.addEventListener('keydown', function (event) {
            if (event.key === 'ArrowDown' || event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                openWrapperMenu(wrapper);
                const firstOption = menu.querySelector('.app-select__option');
                firstOption?.focus();
            }
        });

        syncDisplay(select, wrapper);

        if (labelEl && !labelEl.textContent) {
            labelEl.textContent = getLabel(select);
        }
    }

    function enhance(select) {
        if (!select || select.tagName !== 'SELECT') {
            return null;
        }

        if (select.closest('[data-app-select]')) {
            refresh(select);
            return select.closest('[data-app-select]');
        }

        const wrapper = document.createElement('div');
        wrapper.className = 'app-select';
        wrapper.setAttribute('data-app-select', '');

        select.parentNode.insertBefore(wrapper, select);
        wrapper.appendChild(select);

        select.classList.add('app-select__native');

        const trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.className = 'app-select__trigger';
        trigger.setAttribute('aria-haspopup', 'listbox');
        trigger.setAttribute('aria-expanded', 'false');
        trigger.innerHTML =
            '<span class="app-select__value" data-app-select-label></span>' +
            '<span class="app-select__chevron" aria-hidden="true">' + CHEVRON_SVG + '</span>';

        const menu = document.createElement('ul');
        menu.className = 'app-select__menu';
        menu.setAttribute('role', 'listbox');
        menu.hidden = true;

        if (select.getAttribute('aria-label')) {
            menu.setAttribute('aria-label', select.getAttribute('aria-label'));
        } else if (select.id) {
            const label = document.querySelector('label[for="' + CSS.escape(select.id) + '"]');
            if (label) {
                menu.setAttribute('aria-label', label.textContent.trim());
            }
        }

        wrapper.appendChild(trigger);
        wrapper.appendChild(menu);

        buildOptions(select, menu);
        bindWrapper(wrapper, select);
        select.dataset.customSelectEnhanced = 'true';

        return wrapper;
    }

    function refresh(select) {
        const wrapper = select?.closest('[data-app-select]');
        if (!wrapper) {
            enhance(select);
            return;
        }

        const menu = wrapper.querySelector('.app-select__menu');
        if (menu) {
            buildOptions(select, menu);
        }

        syncDisplay(select, wrapper);
    }

    function enhanceAll(root) {
        (root || document).querySelectorAll(ENHANCE_SELECTOR).forEach(function (select) {
            enhance(select);
        });
    }

    document.addEventListener('click', function (event) {
        if (!event.target.closest('[data-app-select]')) {
            closeAll();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeAll();
        }
    });

    window.addEventListener('resize', closeAll);
    window.addEventListener('scroll', function () {
        if (openWrapper && openWrapper.classList.contains('is-fixed-menu')) {
            const trigger = openWrapper.querySelector('.app-select__trigger');
            const menu = openWrapper.querySelector('.app-select__menu');
            if (trigger && menu) {
                positionMenu(openWrapper, trigger, menu);
            }
        }
    }, true);

    function focusSelect(select) {
        const trigger = select?.closest('[data-app-select]')?.querySelector('.app-select__trigger');
        if (trigger) {
            trigger.focus();
            return;
        }
        select?.focus();
    }

    window.EddiliciousCustomSelect = {
        enhance: enhance,
        refresh: refresh,
        sync: refresh,
        focus: focusSelect,
        enhanceAll: enhanceAll,
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            enhanceAll();
        });
    } else {
        enhanceAll();
    }
})();
