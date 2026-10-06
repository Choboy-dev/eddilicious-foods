/**
 * WhatsApp widget — pause attention animations after click or when tab is hidden.
 */
(function () {
    'use strict';

    const widget = document.querySelector('[data-whatsapp-widget]');
    if (!widget) {
        return;
    }

    const pauseMs = 45000;

    function pauseAnimations() {
        widget.classList.add('is-paused');
        window.setTimeout(function () {
            widget.classList.remove('is-paused');
        }, pauseMs);
    }

    widget.addEventListener('click', pauseAnimations);

    document.addEventListener('visibilitychange', function () {
        if (document.hidden) {
            widget.classList.add('is-paused');
        } else {
            widget.classList.remove('is-paused');
        }
    });
})();
