<?php
/**
 * Eddilicious Foods — Catering booking
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/data/catering-info.php';
require_once __DIR__ . '/includes/data/catering-drinks.php';

$event_types = get_catering_event_types();
$channels = get_catering_channels();
$drinks = get_catering_drinks();

$page_title = 'Book Catering — Eddilicious Foods';
$page_styles = [
    'assets/css/navbar.css',
    'assets/css/catering-booking.css',
    'assets/css/footer.css',
];
$page_scripts = [
    'assets/js/cart-store.js',
    'assets/js/wishlist-store.js',
    'assets/js/coupon-store.js',
    'assets/js/navbar.js',
    'assets/js/custom-select.js',
    'assets/js/catering-page.js',
    'assets/js/footer.js',
];

include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/navbar.php';
?>

<main class="catering-page">
    <div class="catering-banner">
        <picture>
            <source
                media="(min-width: 768px)"
                srcset="assets/images/images/food-banner-desktop.jpeg"
            >
            <img
                class="catering-banner__image"
                src="assets/images/images/food-banner-mobile.jpeg"
                alt="Eddilicious Foods events and catering services"
                width="1600"
                height="900"
                loading="eager"
                decoding="async"
            >
        </picture>
    </div>

    <div class="catering-page__container">
        <div class="catering-success" data-catering-success hidden role="status" aria-live="polite">
            <div class="catering-success__icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/>
                </svg>
            </div>
            <h2 class="catering-success__title">We got your booking request</h2>
            <p class="catering-success__text" data-catering-success-text>
                Thanks for getting in touch. We will reply soon using the contact method you picked.
            </p>
            <a href="index.php" class="catering-success__home">Back to home</a>
        </div>

        <div class="catering-form-shell" data-catering-shell>
            <header class="catering-form-header">
                <h2 class="catering-form-header__title">Book catering</h2>
                <p class="catering-form-header__lead">Fill in the form below to get started.</p>
            </header>

            <ol class="catering-steps" aria-label="Booking steps">
                <li class="catering-steps__item is-active" data-catering-step-indicator="1">
                    <span class="catering-steps__dot" aria-hidden="true">1</span>
                    <span class="catering-steps__label">Your event</span>
                </li>
                <li class="catering-steps__item" data-catering-step-indicator="2">
                    <span class="catering-steps__dot" aria-hidden="true">2</span>
                    <span class="catering-steps__label">Drinks &amp; notes</span>
                </li>
                <li class="catering-steps__item" data-catering-step-indicator="3">
                    <span class="catering-steps__dot" aria-hidden="true">3</span>
                    <span class="catering-steps__label">Review</span>
                </li>
            </ol>

            <form class="catering-form" data-catering-form novalidate>
                <div class="catering-panel is-active" data-catering-panel="1">
                    <fieldset class="catering-fieldset">
                        <legend class="catering-fieldset__legend">Contact details</legend>

                        <div class="catering-field">
                            <label class="catering-label" for="catering-name">Name (you or your organisation) <span class="catering-required">*</span></label>
                            <input type="text" id="catering-name" name="name" class="catering-input" autocomplete="name" required data-catering-field>
                            <p class="catering-error" data-catering-error="name" hidden></p>
                        </div>

                        <div class="catering-grid catering-grid--2">
                            <div class="catering-field">
                                <label class="catering-label" for="catering-email">Email address <span class="catering-required">*</span></label>
                                <input type="email" id="catering-email" name="email" class="catering-input" autocomplete="email" required data-catering-field>
                                <p class="catering-error" data-catering-error="email" hidden></p>
                            </div>
                            <div class="catering-field">
                                <label class="catering-label" for="catering-phone">Phone number <span class="catering-required">*</span></label>
                                <input type="tel" id="catering-phone" name="phone" class="catering-input" autocomplete="tel" required data-catering-field>
                                <p class="catering-error" data-catering-error="phone" hidden></p>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="catering-fieldset">
                        <legend class="catering-fieldset__legend">Event details</legend>

                        <div class="catering-field">
                            <label class="catering-label" for="catering-event-type">Type of event <span class="catering-required">*</span></label>
                            <select id="catering-event-type" name="event_type" class="catering-select" required data-catering-field data-custom-select>
                                <option value="">Pick an event type</option>
                                <?php foreach ($event_types as $event): ?>
                                    <option value="<?= htmlspecialchars($event['value']) ?>"><?= htmlspecialchars($event['label']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="catering-error" data-catering-error="event_type" hidden></p>
                        </div>

                        <div class="catering-grid catering-grid--2">
                            <div class="catering-field">
                                <label class="catering-label" for="catering-event-date">Date of event <span class="catering-required">*</span></label>
                                <input type="date" id="catering-event-date" name="event_date" class="catering-input" required data-catering-field>
                                <p class="catering-error" data-catering-error="event_date" hidden></p>
                            </div>
                            <div class="catering-field">
                                <span class="catering-label">Time <span class="catering-required">*</span></span>
                                <div class="catering-time" role="group" aria-label="Event time">
                                    <input type="number" id="catering-event-hour" name="event_hour" class="catering-input catering-input--time" min="1" max="12" placeholder="08" inputmode="numeric" required data-catering-field aria-label="Hour">
                                    <span class="catering-time__sep" aria-hidden="true">:</span>
                                    <input type="number" id="catering-event-minute" name="event_minute" class="catering-input catering-input--time" min="0" max="59" placeholder="00" inputmode="numeric" required data-catering-field aria-label="Minutes">
                                    <select id="catering-event-ampm" name="event_ampm" class="catering-select catering-select--ampm" required data-catering-field data-custom-select aria-label="AM or PM">
                                        <option value="AM">AM</option>
                                        <option value="PM">PM</option>
                                    </select>
                                </div>
                                <p class="catering-error" data-catering-error="event_time" hidden></p>
                            </div>
                        </div>

                        <div class="catering-field">
                            <label class="catering-label" for="catering-location">Location of event</label>
                            <input type="text" id="catering-location" name="location" class="catering-input" placeholder="Town, postcode, or full address" data-catering-field>
                        </div>

                        <div class="catering-field">
                            <label class="catering-label" for="catering-guests">Number of guests <span class="catering-required">*</span></label>
                            <input type="number" id="catering-guests" name="guest_count" class="catering-input" min="1" max="5000" placeholder="e.g. 50" required data-catering-field>
                            <p class="catering-error" data-catering-error="guest_count" hidden></p>
                        </div>
                    </fieldset>

                    <fieldset class="catering-fieldset">
                        <legend class="catering-fieldset__legend">How should we reach you? <span class="catering-required">*</span></legend>
                        <div class="catering-channel-grid">
                            <?php foreach ($channels as $channel): ?>
                                <label class="catering-channel">
                                    <input type="radio" name="channel" value="<?= htmlspecialchars($channel['value']) ?>" class="catering-channel__input" data-catering-field required>
                                    <span class="catering-channel__card"><?= htmlspecialchars($channel['label']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        <p class="catering-error" data-catering-error="channel" hidden></p>
                    </fieldset>
                </div>

                <div class="catering-panel" data-catering-panel="2" hidden>
                    <fieldset class="catering-fieldset">
                        <legend class="catering-fieldset__legend">Pick drinks for your event</legend>
                        <p class="catering-fieldset__hint">Drinks are part of our catering packages. Tick what you want and pick a size.</p>

                        <ul class="catering-drinks">
                            <?php foreach ($drinks as $drink): ?>
                                <?php
                                $thumb = is_file(__DIR__ . '/' . $drink['image'])
                                    ? $drink['image']
                                    : CATALOG_PRODUCT_PLACEHOLDER;
                                ?>
                                <li class="catering-drink" data-catering-drink-row="<?= htmlspecialchars($drink['id']) ?>">
                                    <label class="catering-drink__pick">
                                        <input
                                            type="checkbox"
                                            class="catering-drink__checkbox"
                                            name="drink_ids[]"
                                            value="<?= htmlspecialchars($drink['id']) ?>"
                                            data-catering-drink-check
                                        >
                                        <span class="catering-drink__media">
                                            <img src="<?= htmlspecialchars($thumb) ?>" alt="" width="72" height="72" loading="lazy" decoding="async">
                                        </span>
                                        <span class="catering-drink__name"><?= htmlspecialchars($drink['name']) ?></span>
                                    </label>
                                    <div class="catering-drink__size" data-catering-drink-size hidden>
                                        <label class="catering-label catering-label--small" for="catering-drink-size-<?= htmlspecialchars($drink['id']) ?>">Size</label>
                                        <select
                                            id="catering-drink-size-<?= htmlspecialchars($drink['id']) ?>"
                                            class="catering-select catering-select--compact"
                                            data-catering-drink-size-select
                                            data-drink-id="<?= htmlspecialchars($drink['id']) ?>"
                                            data-drink-name="<?= htmlspecialchars($drink['name']) ?>"
                                            data-custom-select
                                            disabled
                                        >
                                            <?php foreach ($drink['sizes'] as $sizeKey => $sizeLabel): ?>
                                                <option value="<?= htmlspecialchars((string) $sizeKey) ?>"><?= htmlspecialchars($sizeLabel) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </fieldset>

                    <fieldset class="catering-fieldset">
                        <legend class="catering-fieldset__legend">Anything else we should know?</legend>
                        <div class="catering-field">
                            <label class="catering-label" for="catering-notes">Food wishes, allergies, or setup notes</label>
                            <textarea id="catering-notes" name="notes" class="catering-textarea" rows="5" placeholder="Tell us what food you want, dietary needs, or how the venue is set up." data-catering-field></textarea>
                        </div>
                    </fieldset>
                </div>

                <div class="catering-panel" data-catering-panel="3" hidden>
                    <div class="catering-review" data-catering-review>
                        <h3 class="catering-review__title">Check your details</h3>
                        <dl class="catering-review__list" data-catering-review-list></dl>
                    </div>
                </div>

                <p class="catering-form-error" data-catering-form-error hidden role="alert"></p>

                <div class="catering-form-actions">
                    <button type="button" class="catering-btn catering-btn--ghost" data-catering-back hidden>Back</button>
                    <button type="button" class="catering-btn catering-btn--primary" data-catering-next>
                        <span data-catering-next-label>Next</span>
                    </button>
                    <button type="submit" class="catering-btn catering-btn--primary" data-catering-submit hidden>
                        <span data-catering-submit-label>Send booking request</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php
include __DIR__ . '/includes/footer.php';
include __DIR__ . '/includes/scripts.php';
