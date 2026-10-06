(function () {
    'use strict';

    const config = window.EDDILICIOUS_COMING_SOON || {};
    const form = document.querySelector('[data-coming-soon-form]');
    const message = document.querySelector('[data-coming-soon-message]');

    if (!form) {
        return;
    }

    function showMessage(text, type) {
        if (!message) {
            return;
        }

        message.hidden = false;
        message.textContent = text;
        message.classList.remove('is-success', 'is-error');
        message.classList.add(type === 'success' ? 'is-success' : 'is-error');
    }

    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        const emailInput = form.querySelector('input[type="email"]');
        const button = form.querySelector('button[type="submit"]');
        const email = emailInput ? String(emailInput.value || '').trim() : '';

        if (!email) {
            showMessage('Type your email address.', 'error');
            return;
        }

        if (button) {
            button.disabled = true;
        }

        try {
            const response = await fetch(config.apiBase, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': config.csrf || '',
                },
                body: JSON.stringify({ email: email, csrf: config.csrf || '' }),
            });

            const data = await response.json().catch(function () {
                return { ok: false, error: 'We could not read the server response.' };
            });

            if (!response.ok || !data.ok) {
                throw new Error(data.error || 'Something went wrong. Try again.');
            }

            showMessage(data.message || 'You are on our list. We will email you when we open.', 'success');
            form.reset();
        } catch (error) {
            showMessage(error.message, 'error');
        } finally {
            if (button) {
                button.disabled = false;
            }
        }
    });
})();
