<?php
/**
 * Edilicious Foods — About
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/data/about-content.php';
require_once __DIR__ . '/includes/data/contact-info.php';

$about = get_about_page_content();
$hero = $about['hero'];
$contact = get_contact_info();
$whatsapp = get_brand_whatsapp();
$whatsapp_url = get_brand_whatsapp_url();
$social = get_social_links();
$instagram = null;
foreach ($social as $link) {
    if ($link['network'] === 'instagram') {
        $instagram = $link;
        break;
    }
}

$page_title = 'About Us — Eddilicious Foods';
$page_styles = [
    'assets/css/navbar.css',
    'assets/css/about.css',
    'assets/css/footer.css',
];
$page_scripts = [
    'assets/js/cart-store.js',
    'assets/js/wishlist-store.js',
    'assets/js/coupon-store.js',
    'assets/js/navbar.js',
    'assets/js/footer.js',
];

include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/navbar.php';
?>

<main class="about-page">
    <section class="about-hero" aria-labelledby="about-hero-heading">
        <div class="about-page__container about-hero__grid">
            <div class="about-hero__copy">
                <p class="about-hero__eyebrow"><?= htmlspecialchars($hero['eyebrow']) ?></p>
                <h1 id="about-hero-heading" class="about-hero__title"><?= htmlspecialchars($hero['title']) ?></h1>
                <p class="about-hero__lead"><?= htmlspecialchars($hero['lead']) ?></p>
                <div class="about-hero__actions">
                    <a href="menu.php" class="about-btn about-btn--primary">Order Now</a>
                    <a href="catering.php" class="about-btn about-btn--secondary">Book Catering</a>
                </div>
            </div>
            <div class="about-hero__media">
                <img
                    class="about-hero__image"
                    src="<?= htmlspecialchars($hero['image']) ?>"
                    alt="<?= htmlspecialchars($hero['image_alt']) ?>"
                    width="600"
                    height="600"
                    loading="eager"
                    decoding="async"
                >
            </div>
        </div>
    </section>

    <section class="about-story" aria-labelledby="about-story-heading">
        <div class="about-page__container about-story__grid">
            <div class="about-story__copy">
                <h2 id="about-story-heading" class="about-story__title">Our story</h2>
                <?php foreach ($about['story'] as $paragraph): ?>
                    <p class="about-story__text"><?= htmlspecialchars($paragraph) ?></p>
                <?php endforeach; ?>
            </div>
            <ol class="about-timeline" aria-label="Company milestones">
                <?php foreach ($about['milestones'] as $milestone): ?>
                    <li class="about-timeline__item">
                        <span class="about-timeline__year"><?= htmlspecialchars($milestone['year']) ?></span>
                        <span class="about-timeline__label"><?= htmlspecialchars($milestone['label']) ?></span>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </section>

    <section class="about-purpose" aria-labelledby="about-purpose-heading">
        <div class="about-page__container">
            <h2 id="about-purpose-heading" class="visually-hidden">Our vision and mission</h2>
            <div class="about-purpose__grid">
                <article class="about-purpose__card">
                    <p class="about-purpose__eyebrow">Vision</p>
                    <h3 class="about-purpose__title"><?= htmlspecialchars($about['vision']['title']) ?></h3>
                    <p class="about-purpose__text"><?= htmlspecialchars($about['vision']['text']) ?></p>
                </article>
                <article class="about-purpose__card about-purpose__card--mission">
                    <p class="about-purpose__eyebrow">Mission</p>
                    <h3 class="about-purpose__title"><?= htmlspecialchars($about['mission']['title']) ?></h3>
                    <p class="about-purpose__text"><?= htmlspecialchars($about['mission']['text']) ?></p>
                </article>
            </div>
        </div>
    </section>

    <section class="about-services" aria-labelledby="about-services-heading">
        <div class="about-page__container">
            <header class="about-section-head">
                <p class="about-section-head__eyebrow">What we do</p>
                <h2 id="about-services-heading" class="about-section-head__title">Our services</h2>
            </header>
            <div class="about-services__grid">
                <?php foreach ($about['services'] as $service): ?>
                    <article class="about-service-card">
                        <div class="about-service-card__icon" aria-hidden="true">
                            <?php if ($service['id'] === 'delivery'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg>
                            <?php elseif ($service['id'] === 'weddings'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="m9 16 2 2 4-4"/></svg>
                            <?php elseif ($service['id'] === 'private'): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/></svg>
                            <?php else: ?>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 12h4"/><path d="M10 8h4"/><path d="M14 21v-3a2 2 0 0 0-4 0v3"/><path d="M6 10H4a2 2 0 0 0-2 2v7a1 1 0 0 0 1 1h2"/><path d="M18 10h2a2 2 0 0 1 2 2v7a1 1 0 0 1-1 1h-2"/><path d="M6 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16"/></svg>
                            <?php endif; ?>
                        </div>
                        <h3 class="about-service-card__title"><?= htmlspecialchars($service['title']) ?></h3>
                        <p class="about-service-card__text"><?= htmlspecialchars($service['text']) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="about-reasons" aria-labelledby="about-reasons-heading">
        <div class="about-page__container about-reasons__grid">
            <div class="about-reasons__intro">
                <header class="about-section-head">
                    <p class="about-section-head__eyebrow">Why choose us</p>
                    <h2 id="about-reasons-heading" class="about-section-head__title">What you get with us</h2>
                </header>
                <figure class="about-reasons__media">
                    <img
                        src="assets/images/products/peppered-chicken-platter.jpeg"
                        alt="Peppered chicken from our kitchen"
                        class="about-reasons__image"
                        width="480"
                        height="360"
                        loading="lazy"
                    >
                </figure>
            </div>
            <ul class="about-reasons__list">
                <?php foreach ($about['reasons'] as $reason): ?>
                    <li class="about-reason">
                        <span class="about-reason__mark" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        </span>
                        <div class="about-reason__body">
                            <h3 class="about-reason__title"><?= htmlspecialchars($reason['title']) ?></h3>
                            <p class="about-reason__text"><?= htmlspecialchars($reason['text']) ?></p>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>

    <section class="about-contact" aria-labelledby="about-contact-heading">
        <div class="about-page__container about-contact__inner">
            <div class="about-contact__copy">
                <h2 id="about-contact-heading" class="about-contact__title"><?= htmlspecialchars($about['cta']['title']) ?></h2>
                <p class="about-contact__text"><?= htmlspecialchars($about['cta']['text']) ?></p>
                <p class="about-contact__location"><?= htmlspecialchars($contact['address']) ?></p>
            </div>
            <div class="about-contact__channels">
                <?php foreach ($contact['phones'] as $phone): ?>
                    <a href="tel:<?= htmlspecialchars($phone['href']) ?>" class="about-contact__link">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        <?= htmlspecialchars($phone['display']) ?>
                    </a>
                <?php endforeach; ?>
                <a href="<?= htmlspecialchars($whatsapp_url) ?>" class="about-contact__link about-contact__link--whatsapp" target="_blank" rel="noopener noreferrer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
                    WhatsApp <?= htmlspecialchars($whatsapp['display']) ?>
                </a>
                <?php if ($instagram): ?>
                    <a href="<?= htmlspecialchars($instagram['url']) ?>" class="about-contact__link" target="_blank" rel="noopener noreferrer">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/></svg>
                        @eddiliciousfoods
                    </a>
                <?php endif; ?>
                <a href="contact.php" class="about-btn about-btn--light">Send a message</a>
            </div>
        </div>
    </section>
</main>

<?php
include __DIR__ . '/includes/footer.php';
include __DIR__ . '/includes/scripts.php';
