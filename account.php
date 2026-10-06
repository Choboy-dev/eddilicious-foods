<?php
/**
 * Edilicious Foods — Customer dashboard
 */
require_once __DIR__ . '/includes/config.php';

AuthSession::requireUser('signup.php');

$user = AuthSession::user();
$displayName = trim((string) ($user['name'] ?? ''));
if ($displayName === '') {
    $displayName = explode('@', (string) $user['email'])[0];
}

$initial = strtoupper(substr($displayName, 0, 1));
$avatarUrl = trim((string) ($user['avatar_url'] ?? ''));
$memberSince = date('j F Y', strtotime((string) ($user['created_at'] ?? 'now')));
$authProvider = (string) ($user['auth_provider'] ?? 'email');
$signInMethod = match ($authProvider) {
    'google' => 'Google',
    default => 'Email code',
};

$page_title = 'Your account — Eddilicious Foods';
$page_styles = [
    'assets/css/navbar.css',
    'assets/css/account.css',
    'assets/css/footer.css',
];
$page_scripts = [
    'assets/js/cart-store.js',
    'assets/js/wishlist-store.js',
    'assets/js/coupon-store.js',
    'assets/js/navbar.js',
    'assets/js/account-page.js',
    'assets/js/footer.js',
];

include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/auth-config.php';
include __DIR__ . '/includes/navbar.php';
?>

<main class="dashboard" id="dashboard-main">
    <div class="dashboard__container">
        <aside class="dashboard-sidebar" aria-label="Account navigation">
            <div class="dashboard-sidebar__profile">
                <?php if ($avatarUrl !== ''): ?>
                    <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="" class="dashboard-sidebar__avatar" width="40" height="40">
                <?php else: ?>
                    <span class="dashboard-sidebar__initial" aria-hidden="true"><?= htmlspecialchars($initial) ?></span>
                <?php endif; ?>
                <div class="dashboard-sidebar__identity">
                    <p class="dashboard-sidebar__name"><?= htmlspecialchars($displayName) ?></p>
                    <p class="dashboard-sidebar__email"><?= htmlspecialchars((string) $user['email']) ?></p>
                </div>
            </div>

            <nav class="dashboard-nav">
                <a href="account.php" class="dashboard-nav__link is-active" aria-current="page">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                    Overview
                </a>
                <a href="menu.php" class="dashboard-nav__link">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/></svg>
                    Order food
                </a>
                <a href="cart.php" class="dashboard-nav__link">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                    Cart
                </a>
                <a href="wishlist.php" class="dashboard-nav__link">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                    Wishlist
                </a>
                <a href="contact.php" class="dashboard-nav__link">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/></svg>
                    Help
                </a>
                <?php if (AuthSession::isAdmin()): ?>
                    <a href="admin/index.php" class="dashboard-nav__link">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        Admin
                    </a>
                <?php endif; ?>
            </nav>

            <button type="button" class="dashboard-nav__signout" data-account-signout>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                Sign out
            </button>
        </aside>

        <div class="dashboard-main">
            <header class="dashboard-welcome">
                <div class="dashboard-welcome__copy">
                    <p class="dashboard-welcome__eyebrow">Your dashboard</p>
                    <h1 class="dashboard-welcome__title">Welcome back, <?= htmlspecialchars($displayName) ?></h1>
                    <p class="dashboard-welcome__lead">Pick up where you left off or order something new.</p>
                </div>
                <a href="menu.php" class="dashboard-welcome__cta">Browse menu</a>
            </header>

            <section class="dashboard-stats" aria-label="At a glance">
                <article class="dashboard-stat">
                    <div class="dashboard-stat__icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                    </div>
                    <div class="dashboard-stat__body">
                        <p class="dashboard-stat__value" data-dashboard-cart-count>0</p>
                        <p class="dashboard-stat__label">Items in cart</p>
                    </div>
                    <a href="cart.php" class="dashboard-stat__link">View cart</a>
                </article>

                <article class="dashboard-stat">
                    <div class="dashboard-stat__icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                    </div>
                    <div class="dashboard-stat__body">
                        <p class="dashboard-stat__value" data-dashboard-wishlist-count>0</p>
                        <p class="dashboard-stat__label">Saved dishes</p>
                    </div>
                    <a href="wishlist.php" class="dashboard-stat__link">View wishlist</a>
                </article>

                <article class="dashboard-stat">
                    <div class="dashboard-stat__icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 3h5v5"/><path d="M8 3H3v5"/><path d="M12 22v-8.3a4 4 0 0 0-1.172-2.872L3 3"/><path d="m15 9 6-6"/></svg>
                    </div>
                    <div class="dashboard-stat__body">
                        <p class="dashboard-stat__value">0</p>
                        <p class="dashboard-stat__label">Past orders</p>
                    </div>
                    <span class="dashboard-stat__link dashboard-stat__link--muted">Coming soon</span>
                </article>
            </section>

            <div class="dashboard-grid">
                <section class="dashboard-panel" aria-labelledby="dashboard-orders-heading">
                    <div class="dashboard-panel__header">
                        <h2 id="dashboard-orders-heading" class="dashboard-panel__title">Recent orders</h2>
                    </div>

                    <div class="dashboard-empty">
                        <div class="dashboard-empty__icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                        </div>
                        <h3 class="dashboard-empty__title">No orders yet</h3>
                        <p class="dashboard-empty__text">When you place your first order with us, it will show up here.</p>
                        <a href="menu.php" class="dashboard-empty__btn">Start your first order</a>
                    </div>
                </section>

                <section class="dashboard-panel" aria-labelledby="dashboard-profile-heading">
                    <div class="dashboard-panel__header">
                        <h2 id="dashboard-profile-heading" class="dashboard-panel__title">Account details</h2>
                    </div>

                    <dl class="dashboard-details">
                        <div class="dashboard-details__row">
                            <dt>Name</dt>
                            <dd><?= htmlspecialchars($displayName) ?></dd>
                        </div>
                        <div class="dashboard-details__row">
                            <dt>Email</dt>
                            <dd><?= htmlspecialchars((string) $user['email']) ?></dd>
                        </div>
                        <div class="dashboard-details__row">
                            <dt>Sign-in method</dt>
                            <dd><?= htmlspecialchars($signInMethod) ?></dd>
                        </div>
                        <div class="dashboard-details__row">
                            <dt>Member since</dt>
                            <dd><?= htmlspecialchars($memberSince) ?></dd>
                        </div>
                    </dl>
                </section>
            </div>

            <section class="dashboard-promo" aria-label="Order again">
                <div class="dashboard-promo__copy">
                    <h2 class="dashboard-promo__title">Need food for an event?</h2>
                    <p class="dashboard-promo__text">We cook Nigerian dishes for small get-togethers and big events across the UK.</p>
                </div>
                <a href="catering.php" class="dashboard-promo__btn">Ask about catering</a>
            </section>
        </div>
    </div>
</main>

<?php
include __DIR__ . '/includes/footer.php';
include __DIR__ . '/includes/scripts.php';
