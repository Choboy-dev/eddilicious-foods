/**
 * Catering CTA — keep buffet videos playing smoothly and respect reduced motion.
 */
(function () {
    'use strict';

    const gallery = document.querySelector('.catering-cta__gallery');
    if (!gallery) {
        return;
    }

    const videos = Array.from(gallery.querySelectorAll('[data-catering-video]'));
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function playVideo(video) {
        if (prefersReducedMotion) {
            video.pause();
            return;
        }

        const playPromise = video.play();
        if (playPromise && typeof playPromise.catch === 'function') {
            playPromise.catch(function () {
                /* Autoplay may be blocked until user interacts */
            });
        }
    }

    videos.forEach(function (video) {
        video.addEventListener('loadeddata', function () {
            playVideo(video);
        });

        if (video.readyState >= 2) {
            playVideo(video);
        }
    });

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                const video = entry.target;
                if (!(video instanceof HTMLVideoElement)) {
                    return;
                }

                if (entry.isIntersecting) {
                    playVideo(video);
                } else {
                    video.pause();
                }
            });
        }, { threshold: 0.2 });

        videos.forEach(function (video) {
            observer.observe(video);
        });
    }
})();
