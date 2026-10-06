<?php
/**
 * Coming soon landing — shown when SITE_UNDER_CONSTRUCTION=true and no preview cookie.
 *
 * @var array<string, mixed> $contact
 * @var list<array{label: string, url: string, network: string}> $social
 * @var string $whatsapp
 * @var string $csrf
 */
?>
<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>We are opening soon — Eddilicious Foods</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Karla:wght@400;500;600;700&family=Playfair+Display+SC:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars($asset_prefix) ?>assets/css/variables.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($asset_prefix) ?>assets/css/brand-logo.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($asset_prefix) ?>assets/css/coming-soon.css">
    <script>
        window.EDDILICIOUS_COMING_SOON = {
            csrf: <?= json_encode($csrf, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
            apiBase: <?= json_encode($asset_prefix . 'api/newsletter/subscribe.php', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
        };
    </script>
</head>
<body class="coming-soon-body">
    <div class="coming-soon">
        <div class="coming-soon__glow coming-soon__glow--left" aria-hidden="true"></div>
        <div class="coming-soon__glow coming-soon__glow--right" aria-hidden="true"></div>

        <header class="coming-soon__brand">
            <?php
            $logo_link_class = 'coming-soon__logo';
            $logo_img_class = 'coming-soon__logo__img';
            $logo_href = '#';
            $logo_asset_prefix = $asset_prefix;
            $logo_aria_label = 'Eddilicious Foods home';
            include __DIR__ . '/site-logo.php';
            unset($logo_link_class, $logo_img_class, $logo_href, $logo_asset_prefix, $logo_aria_label);
            ?>
        </header>

        <main class="coming-soon__main">
            <p class="coming-soon__eyebrow">Nigerian food across the UK</p>
            <h1 class="coming-soon__title">We are cooking up something good</h1>
            <p class="coming-soon__lead">
                Our new website is almost ready. Soon you can order soups, rice, platters, and event food from us online.
            </p>

            <div class="coming-soon__visual" aria-hidden="true">
                <img
                    src="<?= htmlspecialchars($asset_prefix) ?>assets/images/products/jollof-rice.jpeg"
                    alt=""
                    class="coming-soon__hero-image"
                    width="520"
                    height="390"
                    loading="eager"
                >
                <span class="coming-soon__badge">Opening soon</span>
            </div>

            <section class="coming-soon__notify" aria-labelledby="coming-soon-notify-title">
                <h2 id="coming-soon-notify-title" class="coming-soon__notify-title">Get an email when we go live</h2>
                <p class="coming-soon__notify-lead">We will tell you when you can order. No spam.</p>

                <form class="coming-soon__form" data-coming-soon-form novalidate>
                    <label class="coming-soon__label" for="coming-soon-email">Your email</label>
                    <div class="coming-soon__form-row">
                        <input
                            type="email"
                            id="coming-soon-email"
                            name="email"
                            class="coming-soon__input"
                            placeholder="you@example.com"
                            autocomplete="email"
                            required
                        >
                        <button type="submit" class="coming-soon__btn">Notify me</button>
                    </div>
                    <p class="coming-soon__message" data-coming-soon-message hidden role="status"></p>
                </form>
            </section>

            <div class="coming-soon__contact">
                <a href="mailto:<?= htmlspecialchars($contact['email']) ?>" class="coming-soon__contact-link">
                    <?= htmlspecialchars($contact['email']) ?>
                </a>
                <a href="<?= htmlspecialchars($whatsapp) ?>" class="coming-soon__contact-link" target="_blank" rel="noopener noreferrer">
                    WhatsApp us
                </a>
            </div>

            <ul class="coming-soon__social" aria-label="Our social media">
                <?php foreach ($social as $item): ?>
                    <li>
                        <a href="<?= htmlspecialchars($item['url']) ?>" target="_blank" rel="noopener noreferrer">
                            <?= htmlspecialchars($item['label']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </main>

        <footer class="coming-soon__footer">
            <p>&copy; <?= (int) date('Y') ?> Eddilicious Foods. All rights reserved.</p>
        </footer>
    </div>

    <script src="<?= htmlspecialchars($asset_prefix) ?>assets/js/coming-soon.js"></script>
</body>
</html>
