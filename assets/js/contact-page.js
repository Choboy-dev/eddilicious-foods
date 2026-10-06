/**
 * Contact page — topic shortcuts, blur validation, submit feedback
 */
(function () {
    'use strict';

    const form = document.querySelector('[data-contact-form]');
    const successPanel = document.querySelector('[data-contact-success]');
    const successText = document.querySelector('[data-contact-success-text]');
    const resetBtn = document.querySelector('[data-contact-reset]');
    const submitBtn = document.querySelector('[data-contact-submit]');
    const submitLabel = document.querySelector('[data-contact-submit-label]');
    const formError = document.querySelector('[data-contact-form-error]');
    const topicSelect = document.querySelector('[data-contact-topic-select]');
    const topicCards = document.querySelectorAll('[data-contact-topic]');
    const fields = document.querySelectorAll('[data-contact-field]');

    if (!form) {
        return;
    }

    const validators = {
        name: function (value) {
            if (!value.trim()) {
                return 'Please tell us your name.';
            }
            if (value.trim().length < 2) {
                return 'Your name looks too short.';
            }
            return '';
        },
        email: function (value) {
            const email = value.trim();
            if (!email) {
                return 'Please type your email address.';
            }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                return 'Please type a real email address.';
            }
            return '';
        },
        phone: function (value) {
            const phone = value.trim();
            if (!phone) {
                return '';
            }
            if (phone.replace(/\D/g, '').length < 10) {
                return 'Please type a full UK phone number.';
            }
            return '';
        },
        topic: function (value) {
            if (!value) {
                return 'Please pick what your message is about.';
            }
            return '';
        },
        message: function (value) {
            if (!value.trim()) {
                return 'Please write your message.';
            }
            if (value.trim().length < 10) {
                return 'Please give us a bit more detail so we can help.';
            }
            return '';
        },
    };

    function getFieldErrorEl(name) {
        return document.querySelector('[data-contact-error="' + name + '"]');
    }

    function setFieldError(field, message) {
        const name = field.name;
        const errorEl = getFieldErrorEl(name);

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

    function validateField(field) {
        const name = field.name;
        const validator = validators[name];

        if (!validator) {
            return true;
        }

        return setFieldError(field, validator(field.value));
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

    function syncTopicCards(value) {
        topicCards.forEach(function (card) {
            card.classList.toggle('is-active', card.getAttribute('data-contact-topic') === value);
        });
    }

    function setSubmitting(isSubmitting) {
        if (!submitBtn || !submitLabel) {
            return;
        }

        submitBtn.disabled = isSubmitting;
        submitBtn.classList.toggle('is-loading', isSubmitting);
        submitLabel.textContent = isSubmitting ? 'Sending…' : 'Send message';
    }

    function showSuccess(email) {
        form.hidden = true;
        if (successPanel) {
            successPanel.hidden = false;
        }
        if (successText && email) {
            successText.textContent = 'Thanks for writing to us. We will reply to ' + email + ' soon.';
        }
        successPanel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function resetForm() {
        form.reset();
        form.hidden = false;
        if (successPanel) {
            successPanel.hidden = true;
        }
        clearFormError();
        fields.forEach(function (field) {
            setFieldError(field, '');
        });
        if (topicSelect) {
            syncTopicCards(topicSelect.value);
        }
        form.querySelector('#contact-name').focus();
    }

    topicCards.forEach(function (card) {
        card.addEventListener('click', function () {
            const value = card.getAttribute('data-contact-topic');
            if (!value || !topicSelect) {
                return;
            }
            topicSelect.value = value;
            window.EddiliciousCustomSelect?.refresh(topicSelect);
            syncTopicCards(value);
            validateField(topicSelect);
            window.EddiliciousCustomSelect?.focus(topicSelect);
        });
    });

    if (topicSelect) {
        topicSelect.addEventListener('change', function () {
            syncTopicCards(topicSelect.value);
            validateField(topicSelect);
        });
        syncTopicCards(topicSelect.value);
    }

    fields.forEach(function (field) {
        field.addEventListener('blur', function () {
            validateField(field);
        });

        field.addEventListener('input', function () {
            if (field.classList.contains('is-invalid')) {
                validateField(field);
            }
            clearFormError();
        });
    });

    if (resetBtn) {
        resetBtn.addEventListener('click', resetForm);
    }

    function csrf() {
        return (window.EDDILICIOUS_AUTH && window.EDDILICIOUS_AUTH.csrf) || '';
    }

    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        clearFormError();

        let isValid = true;
        let firstInvalid = null;

        fields.forEach(function (field) {
            if (!validateField(field) && !firstInvalid) {
                firstInvalid = field;
                isValid = false;
            }
        });

        if (!isValid) {
            setFormError('Please fix the fields marked in red.');
            if (firstInvalid) {
                if (firstInvalid.matches('select[data-custom-select]')) {
                    window.EddiliciousCustomSelect?.focus(firstInvalid);
                } else {
                    firstInvalid.focus();
                }
            }
            return;
        }

        const emailField = form.querySelector('#contact-email');
        const email = emailField ? emailField.value.trim() : '';

        const payload = {
            name: form.querySelector('#contact-name')?.value.trim() || '',
            email: email,
            phone: form.querySelector('#contact-phone')?.value.trim() || '',
            topic: form.querySelector('#contact-topic')?.value || '',
            message: form.querySelector('#contact-message')?.value.trim() || '',
            csrf: csrf(),
        };

        setSubmitting(true);

        try {
            const response = await fetch('api/contact/submit.php', {
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

            showSuccess(email);
        } catch (error) {
            setFormError('Something went wrong. Try again.');
        } finally {
            setSubmitting(false);
        }
    });
})();
