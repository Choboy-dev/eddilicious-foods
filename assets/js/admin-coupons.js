(function () {
    'use strict';

    const Admin = window.EddiliciousAdmin;
    if (!Admin) {
        return;
    }

    const form = document.querySelector('[data-coupon-form]');
    const table = document.querySelector('[data-coupons-table]');
    const errorNode = document.querySelector('[data-coupon-form-error]');
    const livePreview = document.querySelector('[data-coupon-live-preview]');
    const liveBadge = document.querySelector('[data-coupon-live-badge]');
    const formTitle = document.querySelector('[data-coupon-form-title]');
    const formLead = document.querySelector('[data-coupon-form-lead]');
    const newBtn = document.querySelector('[data-coupon-new]');
    const liveToggle = document.querySelector('[data-coupon-live-toggle]');
    const submitBtn = document.querySelector('[data-coupon-submit]');

    let coupons = [];
    let activeCoupon = null;
    let isCreating = false;

    function isMobileCoupons() {
        return window.matchMedia('(max-width: 1023px)').matches
            && document.body.classList.contains('admin-page-coupons');
    }

    function debounce(fn, ms) {
        let timer;
        return function () {
            clearTimeout(timer);
            timer = setTimeout(fn, ms);
        };
    }

    function scrollToForm() {
        const panel = document.querySelector('.admin-coupons__form-panel');
        if (panel) {
            panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function defaultPromoHint(type, value) {
        const amount = Number(value) || 0;
        if (type === 'fixed') {
            return '£' + amount.toFixed(2) + ' off when you order from us. Type this code when you pay.';
        }
        const percent = Number.isInteger(amount) ? String(amount) : String(amount);
        return percent + '% off when you order from us the first time. Type this code when you pay.';
    }

    function discountLabel(coupon) {
        if (!coupon) {
            return '';
        }
        return coupon.type === 'fixed'
            ? '£' + Number(coupon.value).toFixed(2) + ' off'
            : Number(coupon.value).toFixed(0) + '% off';
    }

    function renderLivePreview(coupon) {
        if (!livePreview) {
            return;
        }

        if (liveBadge) {
            liveBadge.hidden = !coupon;
        }

        if (!coupon) {
            livePreview.innerHTML = '<div class="admin-empty"><p class="admin-empty__title">No live coupon</p><p>Add a coupon below to show one on your homepage.</p></div>';
            return;
        }

        livePreview.innerHTML =
            '<div class="promo-banner admin-coupon-preview">' +
                '<div class="promo-banner__container">' +
                    '<p class="promo-banner__lead">Save money on your <strong class="promo-banner__highlight">first order with us.</strong></p>' +
                    '<span class="promo-banner__code admin-coupon-preview__code" tabindex="-1">' +
                        '<span class="promo-banner__code-text">' + Admin.escapeHtml(coupon.code) + '</span>' +
                        '<span class="promo-banner__code-icon" aria-hidden="true">' +
                            '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>' +
                        '</span>' +
                    '</span>' +
                    '<p class="promo-banner__hint">' + Admin.escapeHtml(coupon.promo_hint || defaultPromoHint(coupon.type, coupon.value)) + '</p>' +
                '</div>' +
            '</div>';

        bindLivePreviewCopy();
    }

    function bindLivePreviewCopy() {
        if (!livePreview) {
            return;
        }

        const codeBtn = livePreview.querySelector('.promo-banner__code');
        if (!codeBtn) {
            return;
        }

        const codeText = codeBtn.querySelector('.promo-banner__code-text');
        const code = codeText ? codeText.textContent.trim() : '';

        codeBtn.setAttribute('role', 'button');
        codeBtn.setAttribute('tabindex', '0');
        codeBtn.setAttribute('aria-label', 'Copy code ' + code);

        function copyCode() {
            if (!code) {
                return;
            }

            navigator.clipboard.writeText(code).then(function () {
                codeBtn.classList.add('is-copied');
                setTimeout(function () {
                    codeBtn.classList.remove('is-copied');
                }, 2000);
            }).catch(function () {
                /* ignore */
            });
        }

        codeBtn.addEventListener('click', copyCode);
        codeBtn.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                copyCode();
            }
        });
    }

    function updateFormMode() {
        if (formTitle) {
            formTitle.textContent = isCreating ? 'Add new coupon' : 'Edit live coupon';
        }
        if (formLead) {
            formLead.textContent = isCreating
                ? 'This will go live and pause your current coupon.'
                : 'Change the code, discount, or banner text.';
        }
        if (newBtn) {
            newBtn.hidden = isCreating || !activeCoupon;
        }
        if (liveToggle) {
            liveToggle.hidden = isCreating;
        }
        if (submitBtn) {
            submitBtn.textContent = isCreating ? 'Save and go live' : 'Save changes';
        }
    }

    function resetForm(useActive) {
        if (!form) {
            return;
        }

        isCreating = !useActive;
        form.reset();

        if (useActive && activeCoupon) {
            fillForm(activeCoupon);
            isCreating = false;
        } else {
            document.querySelector('[data-coupon-id]').value = '0';
            document.querySelector('[data-coupon-active]').checked = true;
            document.querySelector('[data-coupon-once]').checked = true;
            document.querySelector('[data-coupon-min]').value = '0';
            document.querySelector('[data-coupon-promo-hint]').value = '';
        }

        updateFormMode();

        if (errorNode) {
            errorNode.hidden = true;
            errorNode.textContent = '';
        }
    }

    function fillForm(coupon) {
        isCreating = false;
        document.querySelector('[data-coupon-id]').value = String(coupon.id || 0);
        document.querySelector('[data-coupon-code]').value = coupon.code || '';
        document.querySelector('[data-coupon-type]').value = coupon.type || 'percent';
        window.EddiliciousCustomSelect?.refresh(document.querySelector('[data-coupon-type]'));
        document.querySelector('[data-coupon-value]').value = coupon.value || '';
        document.querySelector('[data-coupon-min]').value = ((coupon.min_order_pence || 0) / 100).toFixed(2);
        document.querySelector('[data-coupon-promo-hint]').value = coupon.promo_hint || '';
        document.querySelector('[data-coupon-message]').value = coupon.message || '';
        document.querySelector('[data-coupon-expires]').value = coupon.expires_at ? String(coupon.expires_at).slice(0, 10) : '';
        document.querySelector('[data-coupon-once]').checked = !!coupon.once_per_customer;
        document.querySelector('[data-coupon-active]').checked = !!coupon.is_active;
        updateFormMode();
    }

    function bindCouponListEvents(list) {
        if (!table) {
            return;
        }

        table.querySelectorAll('[data-coupon-edit]').forEach(function (button) {
            button.addEventListener('click', function () {
                fillForm(list[Number(button.getAttribute('data-coupon-edit'))]);
                if (isMobileCoupons()) {
                    scrollToForm();
                }
            });
        });

        table.querySelectorAll('[data-coupon-activate]').forEach(function (button) {
            button.addEventListener('click', async function () {
                const coupon = list[Number(button.getAttribute('data-coupon-activate'))];
                try {
                    const data = await Admin.fetch('coupons.php', {
                        method: 'POST',
                        body: JSON.stringify({
                            action: 'save',
                            id: coupon.id,
                            code: coupon.code,
                            type: coupon.type,
                            value: coupon.value,
                            message: coupon.message,
                            promo_hint: coupon.promo_hint,
                            expires_at: coupon.expires_at || '',
                            once_per_customer: coupon.once_per_customer,
                            is_active: true,
                            min_order_pence: coupon.min_order_pence || 0,
                        }),
                    });
                    applyResponse(data);
                } catch (error) {
                    alert(error.message);
                }
            });
        });

        table.querySelectorAll('[data-coupon-delete]').forEach(function (button) {
            button.addEventListener('click', async function () {
                const id = Number(button.getAttribute('data-coupon-delete'));
                if (!id || !window.confirm('Delete this coupon?')) {
                    return;
                }
                try {
                    const data = await Admin.fetch('coupons.php', {
                        method: 'POST',
                        body: JSON.stringify({ action: 'delete', id: id }),
                    });
                    applyResponse(data);
                } catch (error) {
                    alert(error.message);
                }
            });
        });
    }

    function renderCouponsTable(list) {
        let html = '<div class="admin-table-wrap"><table class="admin-table"><thead><tr>';
        html += '<th>Code</th><th>Discount</th><th>Status</th><th></th>';
        html += '</tr></thead><tbody>';

        list.forEach(function (coupon, index) {
            html += '<tr' + (coupon.is_active ? ' class="admin-table__row--active"' : '') + '>';
            html += '<td><span class="admin-table__primary">' + Admin.escapeHtml(coupon.code) + '</span>';
            html += '<div class="admin-table__muted">' + Admin.escapeHtml(coupon.message) + '</div></td>';
            html += '<td>' + Admin.escapeHtml(discountLabel(coupon)) + '</td>';
            html += '<td>' + (coupon.is_active
                ? '<span class="admin-badge admin-badge--live">Live</span>'
                : '<span class="admin-badge admin-badge--hidden">Paused</span>') + '</td>';
            html += '<td style="white-space:nowrap">';
            html += '<button type="button" class="admin-btn admin-btn--ghost admin-btn--small" data-coupon-edit="' + index + '">Edit</button> ';
            if (!coupon.is_active) {
                html += '<button type="button" class="admin-btn admin-btn--ghost admin-btn--small" data-coupon-activate="' + index + '">Go live</button> ';
            }
            html += '<button type="button" class="admin-btn admin-btn--ghost admin-btn--small" data-coupon-delete="' + coupon.id + '">Delete</button>';
            html += '</td>';
            html += '</tr>';
        });

        html += '</tbody></table></div>';
        return html;
    }

    function renderCouponsCards(list) {
        let html = '<div class="admin-coupons-cards">';

        list.forEach(function (coupon, index) {
            const isLive = !!coupon.is_active;

            html += '<article class="admin-coupons-card' + (isLive ? ' admin-coupons-card--live' : '') + '">';
            html += '<div class="admin-coupons-card__head">';
            html += '<span class="admin-coupons-card__code">' + Admin.escapeHtml(coupon.code) + '</span>';
            html += isLive
                ? '<span class="admin-badge admin-badge--live">Live</span>'
                : '<span class="admin-badge admin-badge--hidden">Paused</span>';
            html += '</div>';
            html += '<p class="admin-coupons-card__discount">' + Admin.escapeHtml(discountLabel(coupon)) + '</p>';
            html += '<p class="admin-coupons-card__message">' + Admin.escapeHtml(coupon.message || '') + '</p>';
            html += '<div class="admin-coupons-card__actions' + (isLive ? '' : ' admin-coupons-card__actions--paused') + '">';
            html += '<button type="button" class="admin-btn admin-btn--ghost admin-btn--small" data-coupon-edit="' + index + '">Edit</button>';
            if (isLive) {
                html += '<button type="button" class="admin-btn admin-btn--ghost admin-btn--small admin-coupons-card__delete" data-coupon-delete="' + coupon.id + '">Delete</button>';
            } else {
                html += '<button type="button" class="admin-btn admin-btn--ghost admin-btn--small admin-coupons-card__live" data-coupon-activate="' + index + '">Go live</button>';
                html += '<button type="button" class="admin-btn admin-btn--ghost admin-btn--small admin-coupons-card__delete" data-coupon-delete="' + coupon.id + '">Delete</button>';
            }
            html += '</div>';
            html += '</article>';
        });

        html += '</div>';
        return html;
    }

    function renderCoupons(list) {
        if (!table) {
            return;
        }

        if (!list.length) {
            table.innerHTML = '<div class="admin-empty"><p class="admin-empty__title">No coupons yet</p><p>Your default coupon will appear here after you save.</p></div>';
            return;
        }

        table.innerHTML = isMobileCoupons() ? renderCouponsCards(list) : renderCouponsTable(list);
        bindCouponListEvents(list);
    }

    function applyResponse(data) {
        coupons = data.coupons || [];
        activeCoupon = data.active_coupon || coupons.find(function (item) { return item.is_active; }) || null;
        renderLivePreview(activeCoupon);
        renderCoupons(coupons);
        resetForm(true);
    }

    async function loadCoupons() {
        try {
            const data = await Admin.fetch('coupons.php');
            applyResponse(data);
        } catch (error) {
            if (table) {
                table.innerHTML = '<div class="admin-alert admin-alert--warn">' + Admin.escapeHtml(error.message) + '</div>';
            }
            if (livePreview) {
                livePreview.innerHTML = '<div class="admin-alert admin-alert--warn">' + Admin.escapeHtml(error.message) + '</div>';
            }
        }
    }

    document.querySelector('[data-coupon-reset]')?.addEventListener('click', function () {
        resetForm(true);
    });

    newBtn?.addEventListener('click', function () {
        resetForm(false);
        document.querySelector('[data-coupon-code]')?.focus();
        if (isMobileCoupons()) {
            scrollToForm();
        }
    });

    document.querySelector('[data-coupon-type]')?.addEventListener('change', function () {
        const hintField = document.querySelector('[data-coupon-promo-hint]');
        const valueField = document.querySelector('[data-coupon-value]');
        if (hintField && hintField.value.trim() === '' && valueField && valueField.value) {
            hintField.placeholder = defaultPromoHint(this.value, valueField.value);
        }
    });

    document.querySelector('[data-coupon-value]')?.addEventListener('input', function () {
        const hintField = document.querySelector('[data-coupon-promo-hint]');
        const typeField = document.querySelector('[data-coupon-type]');
        if (hintField && hintField.value.trim() === '' && typeField) {
            hintField.placeholder = defaultPromoHint(typeField.value, this.value);
        }
    });

    form?.addEventListener('submit', async function (event) {
        event.preventDefault();
        if (errorNode) {
            errorNode.hidden = true;
        }

        const minOrderPounds = parseFloat(document.querySelector('[data-coupon-min]').value || '0');
        const id = Number(document.querySelector('[data-coupon-id]').value || 0);

        const payload = {
            action: 'save',
            id: id,
            code: document.querySelector('[data-coupon-code]').value.trim(),
            type: document.querySelector('[data-coupon-type]').value,
            value: document.querySelector('[data-coupon-value]').value,
            message: document.querySelector('[data-coupon-message]').value.trim(),
            promo_hint: document.querySelector('[data-coupon-promo-hint]').value.trim(),
            expires_at: document.querySelector('[data-coupon-expires]').value,
            once_per_customer: document.querySelector('[data-coupon-once]').checked,
            is_active: isCreating ? true : document.querySelector('[data-coupon-active]').checked,
            min_order_pence: Math.round(minOrderPounds * 100),
        };

        if (submitBtn) {
            submitBtn.disabled = true;
        }

        try {
            const data = await Admin.fetch('coupons.php', {
                method: 'POST',
                body: JSON.stringify(payload),
            });
            applyResponse(data);
        } catch (error) {
            if (errorNode) {
                errorNode.hidden = false;
                errorNode.textContent = error.message;
            }
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
            }
        }
    });

    loadCoupons();

    window.addEventListener('resize', debounce(function () {
        if (!coupons.length) {
            return;
        }
        renderCoupons(coupons);
    }, 150));
})();
