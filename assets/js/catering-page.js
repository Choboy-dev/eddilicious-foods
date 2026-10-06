/**
 * Catering booking — multi-step form, validation, submit
 */
(function () {
    'use strict';

    const form = document.querySelector('[data-catering-form]');
    if (!form) {
        return;
    }

    const shell = document.querySelector('[data-catering-shell]');
    const successPanel = document.querySelector('[data-catering-success]');
    const successText = document.querySelector('[data-catering-success-text]');
    const panels = Array.from(document.querySelectorAll('[data-catering-panel]'));
    const stepIndicators = Array.from(document.querySelectorAll('[data-catering-step-indicator]'));
    const backBtn = document.querySelector('[data-catering-back]');
    const nextBtn = document.querySelector('[data-catering-next]');
    const nextLabel = document.querySelector('[data-catering-next-label]');
    const submitBtn = document.querySelector('[data-catering-submit]');
    const submitLabel = document.querySelector('[data-catering-submit-label]');
    const formError = document.querySelector('[data-catering-form-error]');
    const reviewList = document.querySelector('[data-catering-review-list]');
    const drinkChecks = Array.from(document.querySelectorAll('[data-catering-drink-check]'));

    let currentStep = 1;
    const totalSteps = panels.length;

    const eventTypeSelect = document.getElementById('catering-event-type');
    const channelRadios = Array.from(form.querySelectorAll('input[name="channel"]'));

    const eventTypeLabels = {};
    if (eventTypeSelect) {
        Array.from(eventTypeSelect.options).forEach(function (option) {
            if (option.value) {
                eventTypeLabels[option.value] = option.textContent.trim();
            }
        });
    }

    const channelLabels = {};
    channelRadios.forEach(function (radio) {
        const card = radio.parentElement ? radio.parentElement.querySelector('.catering-channel__card') : null;
        channelLabels[radio.value] = card ? card.textContent.trim() : radio.value;
    });

    const sizeLabels = { '1': '1 litre', '2': '2 litres' };

    function csrf() {
        return (window.EDDILICIOUS_AUTH && window.EDDILICIOUS_AUTH.csrf) || '';
    }

    function getFieldErrorEl(name) {
        return document.querySelector('[data-catering-error="' + name + '"]');
    }

    function setFieldError(field, message) {
        if (!field) {
            return !message;
        }

        const name = field.name || field.getAttribute('data-catering-error-key');
        const errorEl = name ? getFieldErrorEl(name) : null;

        if (message) {
            field.classList.add('is-invalid');
            field.setAttribute('aria-invalid', 'true');
            if (errorEl) {
                errorEl.textContent = message;
                errorEl.hidden = false;
            }
            return false;
        }

        field.classList.remove('is-invalid');
        field.removeAttribute('aria-invalid');
        if (errorEl) {
            errorEl.textContent = '';
            errorEl.hidden = true;
        }
        return true;
    }

    function clearFormError() {
        if (formError) {
            formError.hidden = true;
            formError.textContent = '';
        }
    }

    function setFormError(message) {
        if (formError) {
            formError.textContent = message;
            formError.hidden = false;
        }
    }

    function padTime(value) {
        const num = parseInt(value, 10);
        if (Number.isNaN(num)) {
            return '';
        }
        return String(num).padStart(2, '0');
    }

    function getEventTimeString() {
        const hour = document.getElementById('catering-event-hour')?.value.trim() || '';
        const minute = document.getElementById('catering-event-minute')?.value.trim() || '';
        const ampm = document.getElementById('catering-event-ampm')?.value || 'AM';
        if (!hour || minute === '') {
            return '';
        }
        return padTime(hour) + ':' + padTime(minute) + ' ' + ampm;
    }

    function validateEventTime() {
        const hourField = document.getElementById('catering-event-hour');
        const minuteField = document.getElementById('catering-event-minute');
        const hour = parseInt(hourField?.value || '', 10);
        const minute = parseInt(minuteField?.value || '', 10);

        if (!hourField || !minuteField) {
            return true;
        }

        if (Number.isNaN(hour) || hour < 1 || hour > 12) {
            setFieldError(hourField, 'Use an hour from 1 to 12.');
            setFieldError(minuteField, '');
            const timeError = getFieldErrorEl('event_time');
            if (timeError) {
                timeError.hidden = true;
                timeError.textContent = '';
            }
            return false;
        }

        if (Number.isNaN(minute) || minute < 0 || minute > 59) {
            return setFieldError(minuteField, 'Use minutes from 0 to 59.');
        }

        setFieldError(hourField, '');
        setFieldError(minuteField, '');
        const errorEl = getFieldErrorEl('event_time');
        if (errorEl) {
            errorEl.hidden = true;
            errorEl.textContent = '';
        }
        return true;
    }

    function validateChannel() {
        const checked = channelRadios.find(function (radio) {
            return radio.checked;
        });
        const errorEl = getFieldErrorEl('channel');
        if (!checked) {
            if (errorEl) {
                errorEl.textContent = 'Please pick how we should reach you.';
                errorEl.hidden = false;
            }
            return false;
        }
        if (errorEl) {
            errorEl.hidden = true;
            errorEl.textContent = '';
        }
        return true;
    }

    function validateStep(step) {
        clearFormError();
        let valid = true;
        let firstInvalid = null;

        if (step === 1) {
            const stepFields = panels[0].querySelectorAll('[data-catering-field]');
            stepFields.forEach(function (field) {
                if (field.type === 'radio' && field.name === 'channel') {
                    return;
                }
                if (field.id === 'catering-event-hour' || field.id === 'catering-event-minute' || field.id === 'catering-event-ampm') {
                    return;
                }

                let message = '';
                const value = field.value.trim();

                if (field.required && !value) {
                    message = 'This field is required.';
                } else if (field.id === 'catering-name' && value.length < 2) {
                    message = 'Please tell us your name.';
                } else if (field.id === 'catering-email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                    message = 'Please type a real email address.';
                } else if (field.id === 'catering-phone' && value.replace(/\D/g, '').length < 10) {
                    message = 'Please type a full UK phone number.';
                } else if (field.id === 'catering-event-date' && value) {
                    const picked = new Date(value + 'T12:00:00');
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    if (picked < today) {
                        message = 'Please pick a date in the future.';
                    }
                } else if (field.id === 'catering-guests') {
                    const guests = parseInt(value, 10);
                    if (Number.isNaN(guests) || guests < 1) {
                        message = 'Please tell us how many guests you expect.';
                    }
                }

                if (!setFieldError(field, message) && !firstInvalid) {
                    firstInvalid = field;
                    valid = false;
                }
            });

            if (!validateEventTime()) {
                valid = false;
                if (!firstInvalid) {
                    firstInvalid = document.getElementById('catering-event-hour');
                }
            }

            if (!validateChannel()) {
                valid = false;
            }
        }

        if (!valid && firstInvalid) {
            if (firstInvalid.matches('select[data-custom-select]')) {
                window.EddiliciousCustomSelect?.focus(firstInvalid);
            } else {
                firstInvalid.focus();
            }
            setFormError('Please fix the fields marked in red.');
        }

        return valid;
    }

    function getSelectedDrinks() {
        const drinks = [];

        drinkChecks.forEach(function (checkbox) {
            if (!checkbox.checked) {
                return;
            }

            const row = checkbox.closest('[data-catering-drink-row]');
            const select = row ? row.querySelector('[data-catering-drink-size-select]') : null;
            const size = select ? select.value : '1';
            const name = select ? select.getAttribute('data-drink-name') || checkbox.value : checkbox.value;

            drinks.push({
                id: checkbox.value,
                name: name,
                size: size,
                size_label: sizeLabels[size] || size,
            });
        });

        return drinks;
    }

    function buildReview() {
        if (!reviewList) {
            return;
        }

        const drinks = getSelectedDrinks();
        const drinksText = drinks.length
            ? drinks.map(function (item) {
                return item.name + ' (' + item.size_label + ')';
            }).join(', ')
            : 'No drinks picked yet';

        const rows = [
            ['Name', document.getElementById('catering-name')?.value.trim() || ''],
            ['Email', document.getElementById('catering-email')?.value.trim() || ''],
            ['Phone', document.getElementById('catering-phone')?.value.trim() || ''],
            ['Event type', eventTypeLabels[document.getElementById('catering-event-type')?.value || ''] || ''],
            ['Date', document.getElementById('catering-event-date')?.value || ''],
            ['Time', getEventTimeString()],
            ['Location', document.getElementById('catering-location')?.value.trim() || 'Not given'],
            ['Guests', document.getElementById('catering-guests')?.value.trim() || ''],
            ['Contact via', channelLabels[form.querySelector('input[name="channel"]:checked')?.value || ''] || ''],
            ['Drinks', drinksText],
            ['Notes', document.getElementById('catering-notes')?.value.trim() || 'None'],
        ];

        reviewList.innerHTML = rows.map(function (row) {
            return '<div class="catering-review__row"><dt>' + row[0] + '</dt><dd>' + escapeHtml(row[1]) + '</dd></div>';
        }).join('');
    }

    function escapeHtml(text) {
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function updateStepUi() {
        panels.forEach(function (panel) {
            const step = parseInt(panel.getAttribute('data-catering-panel') || '0', 10);
            const active = step === currentStep;
            panel.hidden = !active;
            panel.classList.toggle('is-active', active);
        });

        stepIndicators.forEach(function (item) {
            const step = parseInt(item.getAttribute('data-catering-step-indicator') || '0', 10);
            item.classList.toggle('is-active', step === currentStep);
            item.classList.toggle('is-done', step < currentStep);
        });

        if (backBtn) {
            backBtn.hidden = currentStep === 1;
        }
        if (nextBtn) {
            nextBtn.hidden = currentStep >= totalSteps;
        }
        if (submitBtn) {
            submitBtn.hidden = currentStep < totalSteps;
        }
        if (nextLabel) {
            nextLabel.textContent = currentStep === totalSteps - 1 ? 'Review' : 'Next';
        }
    }

    function goToStep(step) {
        currentStep = Math.max(1, Math.min(totalSteps, step));
        if (currentStep === totalSteps) {
            buildReview();
        }
        updateStepUi();
        shell?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function setSubmitting(isSubmitting) {
        if (!submitBtn || !submitLabel) {
            return;
        }
        submitBtn.disabled = isSubmitting;
        submitBtn.classList.toggle('is-loading', isSubmitting);
        submitLabel.textContent = isSubmitting ? 'Sending…' : 'Send booking request';
    }

    function showSuccess(email) {
        if (shell) {
            shell.hidden = true;
        }
        if (successPanel) {
            successPanel.hidden = false;
        }
        if (successText && email) {
            successText.textContent = 'Thanks for your request. We will reply to ' + email + ' soon using the contact method you picked.';
        }
        successPanel?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    drinkChecks.forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            const row = checkbox.closest('[data-catering-drink-row]');
            const sizeWrap = row ? row.querySelector('[data-catering-drink-size]') : null;
            const select = row ? row.querySelector('[data-catering-drink-size-select]') : null;

            if (sizeWrap) {
                sizeWrap.hidden = !checkbox.checked;
            }
            if (select) {
                select.disabled = !checkbox.checked;
                window.EddiliciousCustomSelect?.refresh(select);
            }
        });
    });

    if (backBtn) {
        backBtn.addEventListener('click', function () {
            goToStep(currentStep - 1);
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', function () {
            if (!validateStep(currentStep)) {
                return;
            }
            goToStep(currentStep + 1);
        });
    }

    form.querySelectorAll('[data-catering-field]').forEach(function (field) {
        field.addEventListener('input', function () {
            if (field.classList.contains('is-invalid')) {
                if (field.id === 'catering-event-hour' || field.id === 'catering-event-minute') {
                    validateEventTime();
                } else {
                    setFieldError(field, '');
                }
            }
            clearFormError();
        });

        field.addEventListener('blur', function () {
            if (field.id === 'catering-event-hour' || field.id === 'catering-event-minute') {
                validateEventTime();
            }
        });
    });

    const dateField = document.getElementById('catering-event-date');
    if (dateField) {
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        dateField.min = tomorrow.toISOString().split('T')[0];
    }

    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        clearFormError();

        if (!validateStep(1)) {
            currentStep = 1;
            updateStepUi();
            return;
        }

        const payload = {
            name: document.getElementById('catering-name')?.value.trim() || '',
            email: document.getElementById('catering-email')?.value.trim() || '',
            phone: document.getElementById('catering-phone')?.value.trim() || '',
            event_type: document.getElementById('catering-event-type')?.value || '',
            event_date: document.getElementById('catering-event-date')?.value || '',
            event_time: getEventTimeString(),
            location: document.getElementById('catering-location')?.value.trim() || '',
            guest_count: parseInt(document.getElementById('catering-guests')?.value || '0', 10),
            channel: form.querySelector('input[name="channel"]:checked')?.value || '',
            drinks: getSelectedDrinks(),
            notes: document.getElementById('catering-notes')?.value.trim() || '',
            csrf: csrf(),
        };

        setSubmitting(true);

        try {
            const response = await fetch('api/catering/submit.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrf(),
                },
                credentials: 'same-origin',
                body: JSON.stringify(payload),
            });

            const data = await response.json().catch(function () {
                return { ok: false, error: 'Something went wrong. Try again.' };
            });

            if (!response.ok || !data.ok) {
                setFormError(data.error || 'Something went wrong. Try again.');
                return;
            }

            showSuccess(payload.email);
        } catch (error) {
            setFormError('Something went wrong. Try again.');
        } finally {
            setSubmitting(false);
        }
    });

    updateStepUi();
})();
