<?php
/**
 * Edilicious Foods — Catering services CTA
 */
require_once __DIR__ . '/config.php';

$catering_events = [
    [
        'name' => 'Weddings',
        'icon' => 'rings',
    ],
    [
        'name' => 'Birthdays',
        'icon' => 'cake',
    ],
    [
        'name' => 'Corporate Events',
        'icon' => 'building',
    ],
    [
        'name' => 'Private Events',
        'icon' => 'balloon',
    ],
];
?>

<section class="catering-cta" aria-labelledby="catering-cta-heading">
    <div class="catering-cta__container">
        <div class="catering-cta__content">
            <h2 id="catering-cta-heading" class="catering-cta__title">
                <span class="catering-cta__title-keep">Events & Catering</span> Services
            </h2>
            <ul class="catering-cta__events">
                <?php foreach ($catering_events as $event): ?>
                    <li class="catering-cta__event">
                        <span class="catering-cta__event-icon" aria-hidden="true">
                            <?php if ($event['icon'] === 'rings'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 9a7 7 0 0 1 19 0"/><path d="M5.5 9a4.5 4.5 0 0 1 13 0"/><path d="M8.5 9h7"/><circle cx="12" cy="15" r="3"/></svg>
                            <?php elseif ($event['icon'] === 'cake'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-8a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8"/><path d="M4 16s.5-1 2-1 2.5 2 4 2 2.5-2 4-2 2.5 2 4 2 2-1 2-1"/><path d="M2 21h20"/><path d="M7 8v3"/><path d="M12 8v3"/><path d="M17 8v3"/><path d="M7 4h.01"/><path d="M12 4h.01"/><path d="M17 4h.01"/></svg>
                            <?php elseif ($event['icon'] === 'building'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="16" height="20" x="4" y="2" rx="2" ry="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01"/><path d="M16 6h.01"/><path d="M12 6h.01"/><path d="M12 10h.01"/><path d="M12 14h.01"/><path d="M16 10h.01"/><path d="M16 14h.01"/><path d="M8 10h.01"/><path d="M8 14h.01"/></svg>
                            <?php else: ?>
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 17c-3 0-5-2-5-5 0-2 1-3.5 2.5-4.5C11 6.5 11.5 4 12 4s1 2.5 2.5 3.5C16 8.5 17 10 17 12c0 3-2 5-5 5z"/><path d="M12 17v3"/><path d="M9 20h6"/></svg>
                            <?php endif; ?>
                        </span>
                        <span class="catering-cta__event-name"><?= htmlspecialchars($event['name']) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="catering-cta__actions">
                <a href="catering.php" class="catering-cta__btn catering-cta__btn--primary">Book Now</a>
            </div>
        </div>

        <div class="catering-cta__gallery">
            <picture>
                <source
                    media="(min-width: 768px)"
                    srcset="assets/images/images/food-banner-desktop.jpeg"
                >
                <img
                    class="catering-cta__image"
                    src="assets/images/images/food-banner-mobile.jpeg"
                    alt="Eddilicious Foods events and catering services"
                    width="800"
                    height="600"
                    loading="lazy"
                    decoding="async"
                >
            </picture>
        </div>
    </div>
</section>
