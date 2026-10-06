<?php
/**
 * Edilicious Foods — Contact
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/data/contact-info.php';

$contact = get_contact_info();
$topics = get_contact_topics();
$faqs = get_contact_faqs();
$topic_param = $_GET['topic'] ?? '';
$valid_topics = array_column($topics, 'value');
$selected_topic = in_array($topic_param, $valid_topics, true) ? $topic_param : 'general';

$page_title = 'Contact Us — Eddilicious Foods';
$page_styles = [
    'assets/css/navbar.css',
    'assets/css/contact.css',
    'assets/css/footer.css',
];
$page_scripts = [
    'assets/js/cart-store.js',
    'assets/js/wishlist-store.js',
    'assets/js/coupon-store.js',
    'assets/js/navbar.js',
    'assets/js/custom-select.js',
    'assets/js/contact-page.js',
    'assets/js/footer.js',
];

include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/navbar.php';
?>

<main class="contact-page">
    <div class="contact-page__container">
        <header class="contact-header">
            <span class="contact-header__eyebrow">Contact</span>
            <h1 class="contact-header__title">Get in touch with us</h1>
            <p class="contact-header__lead">
                Have a question about an order or need food for an event? Send us a message and we will get back to you.
            </p>
        </header>

        <div class="contact-quick" aria-label="What do you need help with?">
            <?php foreach (array_slice($topics, 0, 3) as $topic): ?>
                <button
                    type="button"
                    class="contact-quick__card<?= $selected_topic === $topic['value'] ? ' is-active' : '' ?>"
                    data-contact-topic="<?= htmlspecialchars($topic['value']) ?>"
                >
                    <span class="contact-quick__label"><?= htmlspecialchars($topic['label']) ?></span>
                    <span class="contact-quick__hint"><?= htmlspecialchars($topic['hint']) ?></span>
                </button>
            <?php endforeach; ?>
        </div>

        <div class="contact-layout">
            <div class="contact-main">
                <div class="contact-success" data-contact-success hidden role="status" aria-live="polite">
                    <div class="contact-success__icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/>
                        </svg>
                    </div>
                    <h2 class="contact-success__title">We got your message</h2>
                    <p class="contact-success__text" data-contact-success-text>
                        Thanks for writing to us. We will reply to your email soon.
                    </p>
                    <button type="button" class="contact-success__again" data-contact-reset>Send another message</button>
                </div>

                <form class="contact-form" data-contact-form novalidate>
                    <section class="contact-card" aria-labelledby="contact-form-heading">
                        <h2 id="contact-form-heading" class="contact-card__title">Send us a message</h2>

                        <div class="contact-grid contact-grid--2">
                            <div class="contact-field">
                                <label class="contact-label" for="contact-name">Your name</label>
                                <input type="text" id="contact-name" name="name" class="contact-input" autocomplete="name" required data-contact-field>
                                <p class="contact-field__error" data-contact-error="name" role="alert" hidden></p>
                            </div>
                            <div class="contact-field">
                                <label class="contact-label" for="contact-phone">Phone <span class="contact-label__optional">(optional)</span></label>
                                <input type="tel" id="contact-phone" name="phone" class="contact-input" autocomplete="tel" data-contact-field>
                                <p class="contact-field__error" data-contact-error="phone" role="alert" hidden></p>
                            </div>
                        </div>

                        <div class="contact-field">
                            <label class="contact-label" for="contact-email">Email</label>
                            <input type="email" id="contact-email" name="email" class="contact-input" autocomplete="email" required data-contact-field>
                            <p class="contact-field__error" data-contact-error="email" role="alert" hidden></p>
                        </div>

                        <div class="contact-field">
                            <label class="contact-label" for="contact-topic">What is this about?</label>
                            <div class="contact-select-wrap">
                                <select id="contact-topic" name="topic" class="contact-select" required data-contact-field data-contact-topic-select data-custom-select>
                                    <?php foreach ($topics as $topic): ?>
                                        <option value="<?= htmlspecialchars($topic['value']) ?>"<?= $selected_topic === $topic['value'] ? ' selected' : '' ?>>
                                            <?= htmlspecialchars($topic['label']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <p class="contact-field__error" data-contact-error="topic" role="alert" hidden></p>
                        </div>

                        <div class="contact-field">
                            <label class="contact-label" for="contact-message">Your message</label>
                            <textarea
                                id="contact-message"
                                name="message"
                                class="contact-textarea"
                                rows="5"
                                required
                                data-contact-field
                                placeholder="Tell us what you need. For catering, include your date and how many guests you have."
                            ></textarea>
                            <p class="contact-field__error" data-contact-error="message" role="alert" hidden></p>
                        </div>

                        <p class="contact-form__error" data-contact-form-error role="alert" hidden></p>

                        <button type="submit" class="contact-submit" data-contact-submit>
                            <span data-contact-submit-label>Send message</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/>
                            </svg>
                        </button>
                    </section>
                </form>
            </div>

            <aside class="contact-aside" aria-label="Other ways to reach us">
                <div class="contact-info-card contact-info-card--highlight">
                    <div class="contact-info-card__icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                        </svg>
                    </div>
                    <div class="contact-info-card__body">
                        <h2 class="contact-info-card__title">Call us</h2>
                        <?php foreach ($contact['phones'] as $index => $phone): ?>
                            <a href="tel:<?= htmlspecialchars($phone['href']) ?>" class="contact-info-card__link<?= $index > 0 ? ' contact-info-card__link--stacked' : '' ?>">
                                <?= htmlspecialchars($phone['display']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="contact-info-card">
                    <div class="contact-info-card__icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                        </svg>
                    </div>
                    <div class="contact-info-card__body">
                        <h2 class="contact-info-card__title">Email us</h2>
                        <a href="mailto:<?= htmlspecialchars($contact['email']) ?>" class="contact-info-card__link">
                            <?= htmlspecialchars($contact['email']) ?>
                        </a>
                    </div>
                </div>

                <div class="contact-info-card">
                    <div class="contact-info-card__icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                        </svg>
                    </div>
                    <div class="contact-info-card__body">
                        <h2 class="contact-info-card__title">Opening hours</h2>
                        <ul class="contact-hours">
                            <?php foreach ($contact['hours'] as $slot): ?>
                                <li class="contact-hours__row">
                                    <span class="contact-hours__days"><?= htmlspecialchars($slot['days']) ?></span>
                                    <span class="contact-hours__times"><?= htmlspecialchars($slot['times']) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <div class="contact-info-card">
                    <div class="contact-info-card__icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>
                        </svg>
                    </div>
                    <div class="contact-info-card__body">
                        <h2 class="contact-info-card__title">Where we are</h2>
                        <p class="contact-info-card__address"><?= htmlspecialchars($contact['collection_address']) ?></p>
                    </div>
                </div>
            </aside>
        </div>

        <section class="contact-faq" aria-labelledby="contact-faq-heading">
            <h2 id="contact-faq-heading" class="contact-faq__title">Common questions</h2>
            <div class="contact-faq__list">
                <?php foreach ($faqs as $faq): ?>
                    <details class="contact-faq__item">
                        <summary class="contact-faq__question"><?= htmlspecialchars($faq['question']) ?></summary>
                        <p class="contact-faq__answer"><?= htmlspecialchars($faq['answer']) ?></p>
                    </details>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="contact-cta" aria-labelledby="contact-cta-heading">
            <div class="contact-cta__content">
                <h2 id="contact-cta-heading" class="contact-cta__title">Need food for a big group?</h2>
                <p class="contact-cta__text">We cook for weddings, birthdays, work events, and home events anywhere in the UK.</p>
            </div>
            <a href="catering.php" class="contact-cta__btn">Book Now</a>
        </section>
    </div>
</main>

<?php
include __DIR__ . '/includes/footer.php';
include __DIR__ . '/includes/scripts.php';
