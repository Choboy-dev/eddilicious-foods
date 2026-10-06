<?php
/**
 * Mobile sticky bottom tab bar — Home, Cart, Wishlist, Account
 */
$icon_dir = __DIR__ . '/icons';
$current_page = basename($_SERVER['PHP_SELF']);

require_once __DIR__ . '/auth/bootstrap.php';
$account_entry_url = AuthSession::entryUrl();

$mobile_tabs = [
    [
        'label' => 'Home',
        'url' => 'index.php',
        'icon' => 'home',
        'active' => $current_page === 'index.php',
    ],
    [
        'label' => 'Cart',
        'url' => 'cart.php',
        'icon' => 'shopping-cart',
        'active' => $current_page === 'cart.php',
        'badge' => 'cart',
    ],
    [
        'label' => 'Wishlist',
        'url' => 'wishlist.php',
        'icon' => 'heart',
        'active' => $current_page === 'wishlist.php',
        'badge' => 'wishlist',
    ],
    [
        'label' => 'Account',
        'url' => $account_entry_url,
        'icon' => 'user',
        'active' => in_array($current_page, ['account.php', 'signin.php', 'signup.php'], true),
    ],
];
?>

<nav class="mobile-tab-bar" aria-label="Quick links">
    <?php foreach ($mobile_tabs as $tab): ?>
        <a
            href="<?= htmlspecialchars($tab['url']) ?>"
            class="mobile-tab-bar__item<?= $tab['active'] ? ' is-active' : '' ?>"
            <?= $tab['active'] ? 'aria-current="page"' : '' ?>
        >
            <span class="mobile-tab-bar__icon-wrap">
                <?php include $icon_dir . '/' . $tab['icon'] . '.php'; ?>
                <?php if (($tab['badge'] ?? '') === 'cart'): ?>
                    <span class="mobile-tab-bar__badge" data-cart-badge data-count="0" hidden>0</span>
                <?php elseif (($tab['badge'] ?? '') === 'wishlist'): ?>
                    <span class="mobile-tab-bar__badge mobile-tab-bar__badge--wishlist" data-wishlist-badge data-count="0" hidden>0</span>
                <?php endif; ?>
            </span>
            <span class="mobile-tab-bar__copy">
                <span class="mobile-tab-bar__label"><?= htmlspecialchars($tab['label']) ?></span>
                <span class="mobile-tab-bar__indicator" aria-hidden="true"></span>
            </span>
        </a>
    <?php endforeach; ?>
</nav>
