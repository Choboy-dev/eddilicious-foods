<?php
/**
 * Edilicious Foods — Site footer
 */
require_once __DIR__ . '/data/contact-info.php';

$footer_contact = get_contact_info();
$footer_social_links = get_social_links();
$footer_legal_links = [
    ['label' => 'Terms and conditions', 'url' => 'terms.php'],
    ['label' => 'Privacy', 'url' => 'privacy.php'],
    ['label' => 'Cookies', 'url' => 'cookies.php'],
    ['label' => 'Cookie settings', 'url' => 'cookies.php#manage-cookies', 'attrs' => 'data-cookie-settings data-cookie-settings-detail'],
];

$footer_bar_links = [
    ['label' => 'Privacy Policy', 'url' => 'privacy.php'],
    ['label' => 'Terms', 'url' => 'terms.php'],
    ['label' => 'Cookies', 'url' => 'cookies.php'],
];

$current_year = date('Y');
?>

<footer class="site-footer" aria-label="Site footer">
    <div class="site-footer__main">
        <div class="site-footer__container">
            <div class="site-footer__grid">
                <!-- Brand + newsletter -->
                <div class="site-footer__brand">
                    <?php
                    $logo_link_class = 'site-footer__logo';
                    $logo_img_class = 'site-footer__logo__img';
                    $logo_href = 'index.php';
                    $logo_asset_prefix = '';
                    include __DIR__ . '/partials/site-logo.php';
                    unset($logo_link_class, $logo_img_class, $logo_href, $logo_asset_prefix, $logo_aria_label);
                    ?>

                    <div class="site-footer__newsletter">
                        <h2 class="site-footer__heading">We send our best deals to your inbox</h2>

                        <form class="site-footer__form" data-footer-newsletter novalidate>
                            <label class="site-footer__form-label" for="footer-email">Your email</label>
                            <div class="site-footer__form-row">
                                <input
                                    type="email"
                                    id="footer-email"
                                    name="email"
                                    class="site-footer__input"
                                    placeholder="youremail@gmail.com"
                                    autocomplete="email"
                                    required
                                >
                                <button type="submit" class="site-footer__subscribe">Subscribe</button>
                            </div>
                            <p class="site-footer__form-note" data-newsletter-message hidden></p>
                        </form>

                        <p class="site-footer__policy">
                            We won't spam you. Read our
                            <a href="privacy.php#email">email policy</a>.
                        </p>

                        <ul class="site-footer__social" aria-label="Our social media">
                            <?php foreach ($footer_social_links as $social): ?>
                                <li>
                                    <a
                                        href="<?= htmlspecialchars($social['url']) ?>"
                                        class="site-footer__social-link"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        aria-label="<?= htmlspecialchars($social['label']) ?>"
                                    >
                                        <?php
                                        $social_network = $social['network'];
                                        include __DIR__ . '/partials/social-icon.php';
                                        unset($social_network);
                                        ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <!-- Legal -->
                <nav class="site-footer__nav" aria-label="Legal pages">
                    <h2 class="site-footer__heading">Legal pages</h2>
                    <ul class="site-footer__links">
                        <?php foreach ($footer_legal_links as $link): ?>
                            <li>
                                <a href="<?= htmlspecialchars($link['url']) ?>"<?= !empty($link['attrs']) ? ' ' . $link['attrs'] : '' ?>><?= htmlspecialchars($link['label']) ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </nav>

                <!-- Contact -->
                <div class="site-footer__nav" aria-label="Contact us">
                    <h2 class="site-footer__heading">Contact us</h2>
                    <ul class="site-footer__contact">
                        <li class="site-footer__contact-item">
                            <span class="site-footer__contact-icon" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                            </span>
                            <span class="site-footer__contact-text">
                                <span class="site-footer__contact-label">Address:</span>
                                <?= htmlspecialchars($footer_contact['address']) ?>
                            </span>
                        </li>
                        <li class="site-footer__contact-item">
                            <span class="site-footer__contact-icon" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            </span>
                            <span class="site-footer__contact-text">
                                <span class="site-footer__contact-label">Phone:</span>
                                <?php foreach ($footer_contact['phones'] as $index => $phone): ?>
                                    <?php if ($index > 0): ?><br><?php endif; ?>
                                    <a href="tel:<?= htmlspecialchars($phone['href']) ?>" class="site-footer__contact-link"><?= htmlspecialchars($phone['display']) ?></a>
                                <?php endforeach; ?>
                            </span>
                        </li>
                        <li class="site-footer__contact-item">
                            <span class="site-footer__contact-icon" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                            </span>
                            <span class="site-footer__contact-text">
                                <span class="site-footer__contact-label">Email:</span>
                                <a href="mailto:<?= htmlspecialchars($footer_contact['email']) ?>" class="site-footer__contact-link"><?= htmlspecialchars($footer_contact['email']) ?></a>
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="site-footer__bar">
        <div class="site-footer__bar-inner">
            <p class="site-footer__copyright">
                Eddilicious Foods &copy; <?= (int) $current_year ?>, All Rights Reserved.
            </p>
            <ul class="site-footer__bar-links">
                <?php foreach ($footer_bar_links as $link): ?>
                    <li>
                        <a href="<?= htmlspecialchars($link['url']) ?>"><?= htmlspecialchars($link['label']) ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</footer>

<div class="newsletter-modal" data-newsletter-modal hidden>
    <div class="newsletter-modal__backdrop" data-newsletter-modal-close tabindex="-1" aria-hidden="true"></div>
    <div
        class="newsletter-modal__dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="newsletter-modal-title"
        aria-describedby="newsletter-modal-text"
    >
        <button type="button" class="newsletter-modal__close" data-newsletter-modal-close aria-label="Close">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
        </button>
        <div class="newsletter-modal__icon" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <h2 id="newsletter-modal-title" class="newsletter-modal__title" data-newsletter-modal-title>You are on our deals list</h2>
        <p id="newsletter-modal-text" class="newsletter-modal__text" data-newsletter-modal-text>We will email you when we have offers and news.</p>
        <button type="button" class="newsletter-modal__btn" data-newsletter-modal-close>Got it</button>
    </div>
</div>

<?php include __DIR__ . '/partials/whatsapp-widget.php'; ?>
