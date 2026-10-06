/**
 * Checkout page — multi-step form, order summary, coupons
 */
(function () {
    'use strict';

    const cartApi = window.EdiliciousCart;
    const coupons = window.EdiliciousCoupons;
    const productImages = window.EDDILICIOUS_PRODUCT_IMAGES || {};
    const DELIVERY_FEE = 0;
    const FALLBACK_IMAGE = 'assets/images/products/egusi-soup.jpeg';
    const TOTAL_STEPS = 3;

    const layoutEl = document.querySelector('[data-checkout-layout]');
    const formEl = document.querySelector('[data-checkout-form]');
    const emptyEl = document.querySelector('[data-checkout-empty]');
    const successEl = document.querySelector('[data-checkout-success]');
    const successTextEl = document.querySelector('[data-checkout-success-text]');
    const addressSection = document.querySelector('[data-checkout-address]');
    const addressFields = document.querySelectorAll('[data-checkout-address-field]');
    const fulfillmentInputs = document.querySelectorAll('[data-checkout-fulfillment]');
    const stepPanels = document.querySelectorAll('[data-checkout-step-panel]');
    const itemsEl = document.querySelector('[data-checkout-items]');
    const subtotalEl = document.querySelector('[data-checkout-subtotal]');
    const discountEl = document.querySelector('[data-checkout-discount]');
    const deliveryEl = document.querySelector('[data-checkout-delivery]');
    const totalEl = document.querySelector('[data-checkout-total]');
    const mobileTotalEl = document.querySelector('[data-checkout-mobile-total]');
    const couponInput = document.querySelector('[data-checkout-coupon-input]');
    const applyCouponBtn = document.querySelector('[data-checkout-apply-coupon]');
    const removeCouponBtn = document.querySelector('[data-checkout-remove-coupon]');
    const couponMessage = document.querySelector('[data-checkout-coupon-message]');
    const sidebarActionBtn = document.querySelector('[data-checkout-sidebar-action]');
    const mobileActionBtn = document.querySelector('[data-checkout-mobile-action]');
    const formErrorEl = document.querySelector('[data-checkout-form-error]');
    const stepItems = document.querySelectorAll('[data-checkout-step]');
    const agreementCheckbox = document.querySelector('[data-checkout-agreement-checkbox]');
    const agreementWrap = document.querySelector('[data-checkout-agreement]');
    const agreementOpenBtn = document.querySelector('[data-checkout-agreement-open]');
    const agreementError = document.querySelector('[data-checkout-agreement-error]');
    const agreementModal = document.querySelector('[data-checkout-agreement-modal]');
    const agreementCloseEls = document.querySelectorAll('[data-checkout-agreement-close]');
    const submitBtn = formEl.querySelector('[data-checkout-submit]');

    const config = window.EDDILICIOUS_AUTH || {};
    const stripeConfig = window.EDDILICIOUS_STRIPE || {};
    const stripeWarningEl = document.querySelector('[data-checkout-stripe-warning]');

    let currentStep = 1;
    let allergyPolicyViewed = false;
    let agreementModalOpened = false;

    if (!cartApi || !coupons || !formEl) {
        return;
    }

    function csrf() {
        return config.csrf || '';
    }

    function prefillUserDetails() {
        const user = config.user;
        if (!user) {
            return;
        }

        const emailInput = document.getElementById('checkout-email');
        if (emailInput && user.email) {
            emailInput.value = user.email;
            emailInput.readOnly = true;
        }

        const name = (user.name || '').trim();
        if (!name) {
            return;
        }

        const parts = name.split(/\s+/);
        const firstName = document.getElementById('checkout-first-name');
        const lastName = document.getElementById('checkout-last-name');

        if (firstName && !firstName.value) {
            firstName.value = parts[0] || '';
        }
        if (lastName && !lastName.value) {
            lastName.value = parts.slice(1).join(' ') || '';
        }
    }

    function setAgreementError(message) {
        if (!agreementError) {
            return;
        }

        if (!message) {
            agreementError.hidden = true;
            agreementError.textContent = '';
            return;
        }

        agreementError.hidden = false;
        agreementError.textContent = message;
    }

    function syncAgreementUI() {
        const accepted = Boolean(agreementCheckbox && agreementCheckbox.checked && allergyPolicyViewed);

        if (submitBtn) {
            submitBtn.disabled = !accepted;
        }

        if (mobileActionBtn && currentStep === TOTAL_STEPS) {
            mobileActionBtn.disabled = !accepted;
        }

        if (agreementWrap) {
            agreementWrap.classList.toggle('is-viewed', allergyPolicyViewed && agreementCheckbox && agreementCheckbox.checked);
        }
    }

    function openAgreementModal() {
        if (!agreementModal) {
            return;
        }

        agreementModalOpened = true;
        agreementModal.hidden = false;
        document.body.style.overflow = 'hidden';

        const closeBtn = agreementModal.querySelector('[data-checkout-agreement-close]');
        if (closeBtn) {
            closeBtn.focus();
        }
    }

    function closeAgreementModal() {
        if (!agreementModal) {
            return;
        }

        agreementModal.hidden = true;
        document.body.style.overflow = '';

        if (agreementModalOpened) {
            allergyPolicyViewed = true;

            if (agreementCheckbox) {
                agreementCheckbox.checked = true;
            }

            setAgreementError('');
            syncAgreementUI();

            if (agreementCheckbox) {
                agreementCheckbox.focus();
            }
        }
    }

    function handleAgreementCheckbox() {
        if (!agreementCheckbox) {
            return;
        }

        if (agreementCheckbox.checked && !allergyPolicyViewed) {
            agreementCheckbox.checked = false;
            openAgreementModal();
            return;
        }

        if (!agreementCheckbox.checked) {
            allergyPolicyViewed = false;
            agreementModalOpened = false;
            setAgreementError('');
        }

        syncAgreementUI();
    }

    function validateAgreement() {
        if (!agreementCheckbox || !agreementCheckbox.checked || !allergyPolicyViewed) {
            setAgreementError('Please read our allergy policy and tick the box before you pay.');
            openAgreementModal();
            return false;
        }

        setAgreementError('');
        return true;
    }

    function syncStripeWarning() {
        if (!stripeWarningEl) {
            return;
        }
        stripeWarningEl.hidden = stripeConfig.configured !== false;
    }

    function getItemImage(item) {
        return item.image || productImages[item.id] || FALLBACK_IMAGE;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function escapeAttr(text) {
        return escapeHtml(text).replace(/"/g, '&quot;');
    }

    function getDeliveryFee(cartItems) {
        if (!cartItems.length || cartApi.getFulfillment() === 'collection') {
            return 0;
        }
        return DELIVERY_FEE;
    }

    function calculateTotals(cartItems) {
        const subtotal = cartApi.getSubtotal(cartItems);
        const discountResult = coupons.calculateDiscount(subtotal);
        const delivery = getDeliveryFee(cartItems);
        const total = Math.max(0, subtotal - discountResult.discount + delivery);

        return {
            subtotal: subtotal,
            discount: discountResult.discount,
            delivery: delivery,
            total: total,
            appliedCoupon: discountResult.coupon,
        };
    }

    function showCouponMessage(text, isError) {
        if (!couponMessage) {
            return;
        }
        couponMessage.hidden = !text;
        couponMessage.textContent = text || '';
        couponMessage.classList.toggle('is-error', Boolean(isError));
    }

    function syncCouponField() {
        const appliedCode = coupons.getAppliedCode();

        if (couponInput) {
            couponInput.value = appliedCode || '';
            couponInput.readOnly = Boolean(appliedCode);
        }

        if (removeCouponBtn) {
            removeCouponBtn.hidden = !appliedCode;
        }

        if (applyCouponBtn) {
            applyCouponBtn.hidden = Boolean(appliedCode);
        }
    }

    function syncFulfillmentUI() {
        const current = cartApi.getFulfillment();

        fulfillmentInputs.forEach(function (input) {
            input.checked = input.value === current;
        });

        const isDelivery = current === 'delivery';

        if (addressSection) {
            addressSection.hidden = !isDelivery;
        }

        addressFields.forEach(function (field) {
            field.required = isDelivery && currentStep === 2;
        });
    }

    function renderOrderItems(cartItems) {
        if (!itemsEl) {
            return;
        }

        itemsEl.innerHTML = '';

        cartItems.forEach(function (item) {
            const li = document.createElement('li');
            li.className = 'checkout-order__item';
            const imageSrc = getItemImage(item);

            li.innerHTML =
                '<div class="checkout-order__thumb">' +
                    '<img src="' + escapeAttr(imageSrc) + '" alt="" class="checkout-order__image" width="52" height="52" loading="lazy">' +
                '</div>' +
                '<div class="checkout-order__info">' +
                    '<span class="checkout-order__name">' + escapeHtml(item.name) + '</span>' +
                    '<span class="checkout-order__meta">' + escapeHtml(cartApi.formatLineMeta(item)) + '</span>' +
                '</div>' +
                '<span class="checkout-order__price">' + escapeHtml(cartApi.formatPrice(item.price * Math.max(1, Number(item.units || 1)))) + '</span>';

            itemsEl.appendChild(li);
        });
    }

    function renderTotals(cartItems) {
        const totals = calculateTotals(cartItems);
        const formattedTotal = cartApi.formatPrice(totals.total);

        if (subtotalEl) {
            subtotalEl.textContent = cartApi.formatPrice(totals.subtotal);
        }
        if (discountEl) {
            discountEl.textContent = totals.discount > 0
                ? '-' + cartApi.formatPrice(totals.discount)
                : '-' + cartApi.formatPrice(0);
        }
        if (deliveryEl) {
            deliveryEl.textContent = totals.delivery > 0
                ? cartApi.formatPrice(totals.delivery)
                : 'Free';
        }
        if (totalEl) {
            totalEl.textContent = formattedTotal;
        }
        if (mobileTotalEl) {
            mobileTotalEl.textContent = formattedTotal;
        }

        if (totals.appliedCoupon) {
            showCouponMessage(totals.appliedCoupon.message, false);
        }
    }

    function setCheckoutEmpty(isEmpty) {
        if (emptyEl) {
            emptyEl.hidden = !isEmpty;
        }
        if (formEl) {
            formEl.hidden = isEmpty;
        }
        if (layoutEl) {
            layoutEl.classList.toggle('is-empty', isEmpty);
        }

        if (sidebarActionBtn) {
            sidebarActionBtn.disabled = isEmpty;
        }
        if (mobileActionBtn) {
            mobileActionBtn.disabled = isEmpty;
        }
    }

    function updateStepIndicator() {
        stepItems.forEach(function (step, index) {
            const stepNumber = index + 1;
            step.classList.remove('is-active', 'is-done');

            if (stepNumber < currentStep) {
                step.classList.add('is-done');
            } else if (stepNumber === currentStep) {
                step.classList.add('is-active');
            }
        });
    }

    function updateActionButtons() {
        const isLastStep = currentStep === TOTAL_STEPS;
        const label = isLastStep ? 'Pay with Stripe' : 'Continue';
        const arrowIcon =
            '<span class="checkout-pay__icon" aria-hidden="true">' +
                '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">' +
                    '<path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>' +
                '</svg>' +
            '</span>';

        if (sidebarActionBtn) {
            sidebarActionBtn.hidden = isLastStep;
            if (!isLastStep) {
                sidebarActionBtn.innerHTML = arrowIcon + label;
            }
        }

        if (mobileActionBtn) {
            mobileActionBtn.textContent = label;
            if (isLastStep) {
                mobileActionBtn.type = 'submit';
                mobileActionBtn.setAttribute('form', 'checkout-form');
            } else {
                mobileActionBtn.type = 'button';
                mobileActionBtn.removeAttribute('form');
            }
        }
    }

    function showStep(step) {
        currentStep = Math.max(1, Math.min(TOTAL_STEPS, step));

        stepPanels.forEach(function (panel) {
            const panelStep = Number(panel.getAttribute('data-checkout-step-panel'));
            const isActive = panelStep === currentStep;
            panel.hidden = !isActive;
            panel.classList.toggle('is-active', isActive);
        });

        syncFulfillmentUI();
        updateStepIndicator();
        updateActionButtons();
        syncAgreementUI();
        clearFieldErrors();

        const header = document.querySelector('.checkout-header');
        if (header) {
            header.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function getFieldsForStep(step) {
        const panel = document.querySelector('[data-checkout-step-panel="' + step + '"]');
        if (!panel) {
            return [];
        }

        return Array.from(panel.querySelectorAll('[required]')).filter(function (field) {
            return field.offsetParent !== null;
        });
    }

    function clearFieldErrors() {
        formEl.querySelectorAll('.is-invalid').forEach(function (field) {
            field.classList.remove('is-invalid');
        });

        if (formErrorEl) {
            formErrorEl.hidden = true;
            formErrorEl.textContent = '';
        }
    }

    function showFormError(message) {
        if (!formErrorEl) {
            return;
        }
        formErrorEl.hidden = false;
        formErrorEl.textContent = message;
    }

    function validateStep(step) {
        clearFieldErrors();

        const fields = getFieldsForStep(step);
        let firstInvalid = null;

        fields.forEach(function (field) {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                if (!firstInvalid) {
                    firstInvalid = field;
                }
            }
        });

        if (step === 1) {
            const email = document.getElementById('checkout-email');
            if (email && email.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
                email.classList.add('is-invalid');
                firstInvalid = firstInvalid || email;
            }
        }

        if (firstInvalid) {
            showFormError('Please fill in the fields we need.');
            firstInvalid.focus();
            return false;
        }

        return true;
    }

    function goNext() {
        if (!validateStep(currentStep)) {
            return;
        }

        if (currentStep < TOTAL_STEPS) {
            showStep(currentStep + 1);
        }
    }

    function goBack() {
        if (currentStep > 1) {
            showStep(currentStep - 1);
        }
    }

    function render() {
        const cartItems = cartApi.getCart();
        const isEmpty = cartItems.length === 0;

        setCheckoutEmpty(isEmpty);
        syncCouponField();
        renderOrderItems(cartItems);
        renderTotals(cartItems);

        if (!isEmpty) {
            updateStepIndicator();
            updateActionButtons();
        }
    }

    function tryApplyCoupon() {
        if (!couponInput) {
            return;
        }

        const result = coupons.applyCoupon(couponInput.value);

        if (!result.ok) {
            showCouponMessage(result.message, true);
            renderTotals(cartApi.getCart());
            return;
        }

        showCouponMessage(result.message, false);
        render();
    }

    function setSubmitting(isSubmitting) {
        const submitBtn = formEl.querySelector('[data-checkout-submit]');

        [submitBtn, sidebarActionBtn, mobileActionBtn].forEach(function (btn) {
            if (!btn) {
                return;
            }
            btn.disabled = isSubmitting;
            btn.classList.toggle('is-loading', isSubmitting);
        });

        if (submitBtn && isSubmitting) {
            submitBtn.dataset.originalText = submitBtn.textContent;
            submitBtn.textContent = 'Going to Stripe…';
        } else if (submitBtn && submitBtn.dataset.originalText) {
            submitBtn.textContent = submitBtn.dataset.originalText;
        }
    }

    function collectCheckoutPayload(cartItems) {
        return {
            csrf: csrf(),
            cart: cartItems,
            fulfillment: cartApi.getFulfillment(),
            coupon_code: coupons.getAppliedCode() || '',
            first_name: document.getElementById('checkout-first-name')?.value?.trim() || '',
            last_name: document.getElementById('checkout-last-name')?.value?.trim() || '',
            email: document.getElementById('checkout-email')?.value?.trim() || '',
            phone: document.getElementById('checkout-phone')?.value?.trim() || '',
            address_line1: document.getElementById('checkout-address-line1')?.value?.trim() || '',
            address_line2: document.getElementById('checkout-address-line2')?.value?.trim() || '',
            city: document.getElementById('checkout-city')?.value?.trim() || '',
            postcode: document.getElementById('checkout-postcode')?.value?.trim() || '',
            notes: document.getElementById('checkout-notes')?.value?.trim() || '',
            allergy_agreement_accepted: Boolean(agreementCheckbox && agreementCheckbox.checked && allergyPolicyViewed),
        };
    }

    function handleSubmit(event) {
        event.preventDefault();

        if (currentStep !== TOTAL_STEPS) {
            goNext();
            return;
        }

        const cartItems = cartApi.getCart();
        if (!cartItems.length) {
            return;
        }

        if (!validateStep(1) || !validateStep(2)) {
            if (!validateStep(1)) {
                showStep(1);
            } else if (!validateStep(2)) {
                showStep(2);
            }
            return;
        }

        if (!validateAgreement()) {
            return;
        }

        clearFieldErrors();
        setSubmitting(true);

        fetch('api/checkout/create-session.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrf(),
            },
            credentials: 'same-origin',
            body: JSON.stringify(collectCheckoutPayload(cartItems)),
        })
            .then(function (response) {
                return response.text().then(function (text) {
                    let data = { ok: false, error: 'We could not read the server response.' };

                    if (text) {
                        try {
                            data = JSON.parse(text);
                        } catch (error) {
                            throw new Error('The payment server returned an unexpected response. Check that vendor/ is uploaded and storage/ is writable.');
                        }
                    }

                    return { ok: response.ok, data: data };
                });
            })
            .then(function (result) {
                if (result.data.auth_required && result.data.redirect) {
                    window.location.href = result.data.redirect;
                    return;
                }

                if (!result.data.ok || !result.data.checkout_url) {
                    showFormError(result.data.error || 'We could not start payment. Try again.');
                    setSubmitting(false);
                    return;
                }

                window.location.href = result.data.checkout_url;
            })
            .catch(function (error) {
                showFormError(error.message || 'We could not reach the payment server. Try again.');
                setSubmitting(false);
            });
    }

    if (agreementCheckbox) {
        agreementCheckbox.addEventListener('change', handleAgreementCheckbox);
    }

    if (agreementOpenBtn) {
        agreementOpenBtn.addEventListener('click', function (event) {
            event.preventDefault();
            openAgreementModal();
        });
    }

    agreementCloseEls.forEach(function (button) {
        button.addEventListener('click', closeAgreementModal);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && agreementModal && !agreementModal.hidden) {
            closeAgreementModal();
        }
    });

    formEl.querySelectorAll('[data-checkout-next]').forEach(function (btn) {
        btn.addEventListener('click', goNext);
    });

    formEl.querySelectorAll('[data-checkout-back]').forEach(function (btn) {
        btn.addEventListener('click', goBack);
    });

    if (sidebarActionBtn) {
        sidebarActionBtn.addEventListener('click', function () {
            if (currentStep === TOTAL_STEPS) {
                formEl.requestSubmit();
            } else {
                goNext();
            }
        });
    }

    if (mobileActionBtn) {
        mobileActionBtn.addEventListener('click', function () {
            if (currentStep !== TOTAL_STEPS) {
                goNext();
            }
        });
    }

    if (applyCouponBtn) {
        applyCouponBtn.addEventListener('click', tryApplyCoupon);
    }

    if (couponInput) {
        couponInput.addEventListener('keydown', function (event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                tryApplyCoupon();
            }
        });
    }

    if (removeCouponBtn) {
        removeCouponBtn.addEventListener('click', function () {
            coupons.removeCoupon();
            if (couponInput) {
                couponInput.readOnly = false;
                couponInput.value = '';
            }
            showCouponMessage('We removed your coupon.', false);
            render();
        });
    }

    fulfillmentInputs.forEach(function (input) {
        input.addEventListener('change', function () {
            if (input.checked) {
                cartApi.setFulfillment(input.value);
            }
            syncFulfillmentUI();
            renderTotals(cartApi.getCart());
        });
    });

    formEl.addEventListener('submit', handleSubmit);

    window.addEventListener('edilicious:cart-updated', render);
    window.addEventListener('edilicious:coupon-updated', render);

    showStep(1);
    syncStripeWarning();
    syncAgreementUI();
    prefillUserDetails();
    render();
})();
