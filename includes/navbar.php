<?php
/**
 * Edilicious Foods - Navbar
 * Logo left | Menu pills center | Action icons right
 */

$menu_dropdown = [
    ['name' => 'Soups',             'url' => 'menu.php?category=soups'],
    ['name' => 'Stews',             'url' => 'menu.php?category=stews'],
    ['name' => 'Rice Dishes',       'url' => 'menu.php?category=rice'],
    ['name' => 'Platters',          'url' => 'menu.php?category=platters'],
    ['name' => 'Peppered Proteins', 'url' => 'menu.php?category=peppered-proteins'],
];

$nav_items = [
    ['name' => 'Home',     'url' => 'index.php',    'icon' => 'home'],
    ['name' => 'Menu',     'url' => 'menu.php',     'icon' => 'utensils', 'children' => $menu_dropdown],
    ['name' => 'Catering', 'url' => 'catering.php', 'icon' => 'chef-hat'],
    ['name' => 'About',    'url' => 'about.php',    'icon' => 'info'],
    ['name' => 'Contact',  'url' => 'contact.php',  'icon' => 'mail'],
];

$current_page = basename($_SERVER['PHP_SELF']);
$current_category = $_GET['category'] ?? '';
$icon_dir = __DIR__ . '/icons';

require_once __DIR__ . '/auth/bootstrap.php';
$account_entry_url = AuthSession::entryUrl();

function nav_item_is_active(array $item, string $current_page, string $current_category): bool
{
    if (!empty($item['children'])) {
        if ($current_page === basename($item['url'])) {
            return true;
        }
        foreach ($item['children'] as $child) {
            $child_category = '';
            if (preg_match('/category=([^&]+)/', $child['url'], $matches)) {
                $child_category = $matches[1];
            }
            if ($current_page === 'menu.php' && $current_category === $child_category) {
                return true;
            }
        }
        return false;
    }

    return $current_page === basename($item['url']);
}
?>

<!-- Mobile top bar: hamburger + logo + action icons -->
<div class="navbar-mobile-actions">
    <?php
    $logo_link_class = 'navbar-logo';
    $logo_img_class = 'navbar-logo__img';
    $logo_href = 'index.php';
    $logo_asset_prefix = '';
    include __DIR__ . '/partials/site-logo.php';
    unset($logo_link_class, $logo_img_class, $logo_href, $logo_asset_prefix, $logo_aria_label);
    ?>
    <div class="navbar-actions">
        <button type="button" class="navbar-action" data-search-open aria-label="Search">
            <?php include $icon_dir . '/search.php'; ?>
        </button>
        <button
            type="button"
            class="navbar-mobile-toggle"
            data-mobile-nav-toggle
            aria-expanded="false"
            aria-controls="mobile-nav-drawer"
            aria-label="Open menu"
        >
            <span class="navbar-mobile-toggle__bar"></span>
            <span class="navbar-mobile-toggle__bar"></span>
            <span class="navbar-mobile-toggle__bar"></span>
        </button>
    </div>
</div>

<div class="mobile-nav" data-mobile-nav hidden>
    <button type="button" class="mobile-nav__backdrop" data-mobile-nav-close aria-label="Close menu"></button>
    <aside id="mobile-nav-drawer" class="mobile-nav__drawer" aria-label="Mobile navigation">
        <div class="mobile-nav__header">
            <?php
            $logo_link_class = 'mobile-nav__logo';
            $logo_img_class = 'mobile-nav__logo__img';
            $logo_href = 'index.php';
            $logo_asset_prefix = '';
            include __DIR__ . '/partials/site-logo.php';
            unset($logo_link_class, $logo_img_class, $logo_href, $logo_asset_prefix, $logo_aria_label);
            ?>
            <button type="button" class="mobile-nav__close" data-mobile-nav-close aria-label="Close menu">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
                </svg>
            </button>
        </div>

        <nav class="mobile-nav__menu" aria-label="Main menu">
            <ul class="mobile-nav__list">
                <?php foreach ($nav_items as $item): ?>
                    <?php
                    $is_active = nav_item_is_active($item, $current_page, $current_category);
                    $has_children = !empty($item['children']);
                    ?>
                    <li class="mobile-nav__item<?= $has_children ? ' mobile-nav__item--has-children' : '' ?>">
                        <?php if ($has_children): ?>
                            <?php
                            $submenu_id = 'mobile-submenu-' . preg_replace('/[^a-z0-9]+/i', '-', strtolower($item['name']));
                            $submenu_expanded = $is_active;
                            ?>
                            <button
                                type="button"
                                class="mobile-nav__link mobile-nav__link--toggle<?= $is_active ? ' is-active' : '' ?>"
                                data-mobile-submenu-toggle
                                aria-expanded="<?= $submenu_expanded ? 'true' : 'false' ?>"
                                aria-controls="<?= htmlspecialchars($submenu_id) ?>"
                            >
                                <span><?= htmlspecialchars($item['name']) ?></span>
                                <svg class="mobile-nav__chevron" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="m6 9 6 6 6-6"/>
                                </svg>
                            </button>
                            <ul id="<?= htmlspecialchars($submenu_id) ?>" class="mobile-nav__sublist"<?= $submenu_expanded ? '' : ' hidden' ?>>
                                <?php foreach ($item['children'] as $child): ?>
                                    <?php
                                    $child_active = false;
                                    if (preg_match('/category=([^&]+)/', $child['url'], $matches)) {
                                        $child_active = ($current_page === 'menu.php' && $current_category === $matches[1]);
                                    }
                                    ?>
                                    <li>
                                        <a href="<?= htmlspecialchars($child['url']) ?>" class="mobile-nav__sublink<?= $child_active ? ' is-active' : '' ?>">
                                            <?= htmlspecialchars($child['name']) ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <a href="<?= htmlspecialchars($item['url']) ?>" class="mobile-nav__link<?= $is_active ? ' is-active' : '' ?>">
                                <?= htmlspecialchars($item['name']) ?>
                            </a>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </aside>
</div>

<header class="navbar-wrapper">
    <nav class="navbar" aria-label="Main navigation">
        <!-- Logo -->
        <?php
        $logo_link_class = 'navbar-logo';
        $logo_img_class = 'navbar-logo__img';
        $logo_href = 'index.php';
        $logo_asset_prefix = '';
        include __DIR__ . '/partials/site-logo.php';
        unset($logo_link_class, $logo_img_class, $logo_href, $logo_asset_prefix, $logo_aria_label);
        ?>

        <!-- Center menu pills -->
        <div class="navbar-menu">
            <div class="navbar-pills">
                <?php foreach ($nav_items as $item): ?>
                    <?php
                    $is_active = nav_item_is_active($item, $current_page, $current_category);
                    $active_class = $is_active ? ' is-active' : '';
                    $has_children = !empty($item['children']);
                    ?>

                    <?php if ($has_children): ?>
                        <div class="navbar-dropdown" data-navbar-dropdown>
                            <button
                                type="button"
                                class="navbar-pill navbar-pill--dropdown<?= $active_class ?>"
                                aria-expanded="false"
                                aria-haspopup="true"
                                data-nav="<?= htmlspecialchars(strtolower($item['name'])) ?>"
                            >
                                <span class="navbar-pill-icon md-hidden">
                                    <?php include $icon_dir . '/' . $item['icon'] . '.php'; ?>
                                </span>
                                <span class="navbar-pill-label"><?= htmlspecialchars($item['name']) ?></span>
                                <svg class="navbar-pill-chevron" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="m6 9 6 6 6-6"/>
                                </svg>
                                <?php if ($is_active): ?>
                                    <span class="navbar-lamp" aria-hidden="true">
                                        <span class="navbar-lamp-bar"></span>
                                        <span class="navbar-lamp-glow navbar-lamp-glow--1"></span>
                                        <span class="navbar-lamp-glow navbar-lamp-glow--2"></span>
                                        <span class="navbar-lamp-glow navbar-lamp-glow--3"></span>
                                    </span>
                                <?php endif; ?>
                            </button>

                            <ul class="navbar-dropdown__menu" role="menu" hidden>
                                <?php foreach ($item['children'] as $child): ?>
                                    <?php
                                    $child_active = false;
                                    if (preg_match('/category=([^&]+)/', $child['url'], $matches)) {
                                        $child_active = ($current_page === 'menu.php' && $current_category === $matches[1]);
                                    }
                                    ?>
                                    <li role="none">
                                        <a
                                            href="<?= htmlspecialchars($child['url']) ?>"
                                            class="navbar-dropdown__link<?= $child_active ? ' is-active' : '' ?>"
                                            role="menuitem"
                                        >
                                            <?= htmlspecialchars($child['name']) ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a
                            href="<?= htmlspecialchars($item['url']) ?>"
                            class="navbar-pill<?= $active_class ?>"
                            data-nav="<?= htmlspecialchars(strtolower($item['name'])) ?>"
                        >
                            <span class="navbar-pill-icon md-hidden">
                                <?php include $icon_dir . '/' . $item['icon'] . '.php'; ?>
                            </span>
                            <span class="navbar-pill-label"><?= htmlspecialchars($item['name']) ?></span>
                            <?php if ($is_active): ?>
                                <span class="navbar-lamp" aria-hidden="true">
                                    <span class="navbar-lamp-bar"></span>
                                    <span class="navbar-lamp-glow navbar-lamp-glow--1"></span>
                                    <span class="navbar-lamp-glow navbar-lamp-glow--2"></span>
                                    <span class="navbar-lamp-glow navbar-lamp-glow--3"></span>
                                </span>
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Right action icons -->
        <div class="navbar-actions">
            <button type="button" class="navbar-action" data-search-open aria-label="Search">
                <?php include $icon_dir . '/search.php'; ?>
            </button>
            <a href="cart.php" class="navbar-action" aria-label="Cart">
                <?php include $icon_dir . '/shopping-cart.php'; ?>
                <span class="navbar-badge" data-cart-badge data-count="0" hidden>0</span>
            </a>
            <a href="wishlist.php" class="navbar-action" aria-label="Wishlist">
                <?php include $icon_dir . '/heart.php'; ?>
                <span class="navbar-badge navbar-badge--wishlist" data-wishlist-badge data-count="0" hidden>0</span>
            </a>
            <a href="<?= htmlspecialchars($account_entry_url) ?>" class="navbar-action" aria-label="Account">
                <?php include $icon_dir . '/user.php'; ?>
            </a>
        </div>
    </nav>
</header>

<?php include __DIR__ . '/search-overlay.php'; ?>
<?php include __DIR__ . '/mobile-tab-bar.php'; ?>
