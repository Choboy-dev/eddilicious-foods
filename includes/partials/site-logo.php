<?php
/**
 * Brand logo image link.
 *
 * @var string $logo_link_class  Wrapper class (e.g. navbar-logo)
 * @var string $logo_img_class   Image class (e.g. navbar-logo__img)
 * @var string $logo_href        Link target
 * @var string $logo_asset_prefix '' or '../'
 */
$logo_link_class = $logo_link_class ?? 'brand-logo';
$logo_img_class = $logo_img_class ?? 'brand-logo__img';
$logo_href = $logo_href ?? 'index.php';
$logo_asset_prefix = $logo_asset_prefix ?? '';
$logo_aria_label = $logo_aria_label ?? 'Eddilicious Foods - Home';
?>
<a href="<?= htmlspecialchars($logo_href) ?>" class="<?= htmlspecialchars($logo_link_class) ?>" aria-label="<?= htmlspecialchars($logo_aria_label) ?>">
    <img
        src="<?= htmlspecialchars(site_logo_src($logo_asset_prefix)) ?>"
        alt="Eddilicious Foods"
        class="<?= htmlspecialchars($logo_img_class) ?>"
        width="200"
        height="76"
        decoding="async"
    >
</a>
