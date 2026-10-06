/**
 * Footer — newsletter signup via Resend
 */
(function () {
    'use strict';

    const form = document.querySelector('[data-footer-newsletter]');
    if (!form) {
        return;
    }

    const input = form.querySelector('#footer-email');
    const message = form.querySelector('[data-newsletter-message]');
    const submitBtn = form.querySelector('.site-footer__subscribe');
    const modal = document.querySelector('[data-newsletter-modal]');
    const modalTitle = document.querySelector('[data-newsletter-modal-title]');
    const modalText = document.querySelector('[data-newsletter-modal-text]');

    let lastFocus = null;

    function csrf() {
        return (window.EDDILICIOUS_AUTH && window.EDDILICIOUS_AUTH.csrf) || '';
    }

    function setInlineMessage(text, isError) {
        if (!message) {
            return;
        }

        message.hidden = !text;
        message.textContent = text || '';
        message.classList.toggle('is-error', !!isError);
    }

    function openModal(title, text) {
        if (!modal) {
            return;
        }

        lastFocus = document.activeElement;

        if (modalTitle) {
            modalTitle.textContent = title;
        }
        if (modalText) {
            modalText.textContent = text;
        }

        modal.hidden = false;
        document.body.classList.add('newsletter-modal-open');

        const closeBtn = modal.querySelector('.newsletter-modal__btn');
        if (closeBtn) {
            closeBtn.focus();
        }
    }

    function closeModal() {
        if (!modal || modal.hidden) {
            return;
        }

        modal.hidden = true;
        document.body.classList.remove('newsletter-modal-open');

        if (lastFocus && typeof lastFocus.focus === 'function') {
            lastFocus.focus();
        }
    }

    document.querySelectorAll('[data-newsletter-modal-close]').forEach(function (node) {
        node.addEventListener('click', closeModal);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal && !modal.hidden) {
            closeModal();
        }
    });

    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        if (!input) {
            return;
        }

        const email = input.value.trim();
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        setInlineMessage('', false);

        if (!emailPattern.test(email)) {
            setInlineMessage('Type a real email address.', true);
            input.focus();
            return;
        }

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Subscribing…';
        }

        try {
            const response = await fetch('api/newsletter/subscribe.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrf(),
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    email: email,
                    csrf: csrf(),
                }),
            });

            const data = await response.json().catch(function () {
                return { ok: false, error: 'Something went wrong. Try again.' };
            });

            if (!response.ok || !data.ok) {
                setInlineMessage(data.error || 'Something went wrong. Try again.', true);
                input.focus();
                return;
            }

            input.value = '';
            setInlineMessage('', false);

            const title = data.already_subscribed ? 'You are already on the list' : 'You are on our deals list';
            const text = data.message || 'We will email you when we have offers and news.';
            openModal(title, text);
        } catch (error) {
            setInlineMessage('Something went wrong. Try again.', true);
            input.focus();
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Subscribe';
            }
        }
    });
})();
