<?php
/**
 * Admin layout — set $admin_current and $admin_title before including.
 *
 * @var string $admin_current
 * @var string $admin_title
 * @var list<string> $admin_scripts
 */
$admin_scripts = $admin_scripts ?? [];
$admin_stylesheets = $admin_stylesheets ?? [];
$admin_body_class = $admin_body_class ?? '';
$user = AuthSession::user();
$displayName = trim((string) ($user['name'] ?? ''));
if ($displayName === '') {
    $displayName = explode('@', (string) $user['email'])[0];
}
$initial = strtoupper(substr($displayName, 0, 1));
?>
<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($admin_title) ?> — Admin — Eddilicious Foods</title>
    <?php include dirname(__DIR__) . '/fonts.php'; ?>
    <link rel="stylesheet" href="../assets/css/variables.css">
    <link rel="stylesheet" href="../assets/css/brand-logo.css">
    <link rel="stylesheet" href="../assets/css/custom-select.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/admin-mobile.css?v=<?= filemtime(dirname(__DIR__, 2) . '/assets/css/admin-mobile.css') ?>">
    <?php foreach ($admin_stylesheets as $stylesheet): ?>
        <?php
        $sheetRel = preg_replace('#^\.\./#', '', $stylesheet);
        $sheetPath = dirname(__DIR__, 2) . '/' . $sheetRel;
        $sheetVer = is_file($sheetPath) ? filemtime($sheetPath) : time();
        ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($stylesheet) ?>?v=<?= $sheetVer ?>">
    <?php endforeach; ?>
    <script>
        window.EDDILICIOUS_ADMIN = {
            csrf: <?= json_encode(auth_csrf_token(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
            apiBase: '../api/admin/'
        };
    </script>
</head>
<body class="admin-body<?= $admin_body_class !== '' ? ' ' . htmlspecialchars($admin_body_class) : '' ?>">
    <a href="#admin-main" class="admin-skip">Skip to main content</a>

    <div class="admin-nav-backdrop" data-admin-nav-backdrop hidden tabindex="-1" aria-hidden="true"></div>

    <header class="admin-mobile-bar" data-admin-mobile-bar>
        <div class="admin-mobile-bar__inner">
            <div class="admin-mobile-bar__brand">
                <?php
                $logo_link_class = 'admin-mobile-bar__logo';
                $logo_img_class = 'admin-mobile-bar__logo__img';
                $logo_href = '../index.php';
                $logo_asset_prefix = '../';
                include dirname(__DIR__) . '/partials/site-logo.php';
                unset($logo_link_class, $logo_img_class, $logo_href, $logo_asset_prefix, $logo_aria_label);
                ?>
                <span class="admin-mobile-bar__badge">Admin</span>
            </div>
            <button
                type="button"
                class="admin-mobile-bar__toggle"
                data-admin-nav-toggle
                aria-expanded="false"
                aria-controls="admin-sidebar"
                aria-label="Open admin menu"
            >
                <span class="admin-mobile-bar__toggle-icon" aria-hidden="true">
                    <span></span>
                    <span></span>
                    <span></span>
                </span>
            </button>
        </div>
    </header>

    <div class="admin-app">
        <aside class="admin-sidebar" id="admin-sidebar" data-admin-sidebar aria-label="Admin navigation">
            <div class="admin-sidebar__brand">
                <?php
                $logo_link_class = 'admin-sidebar__logo';
                $logo_img_class = 'admin-sidebar__logo__img';
                $logo_href = '../index.php';
                $logo_asset_prefix = '../';
                include dirname(__DIR__) . '/partials/site-logo.php';
                unset($logo_link_class, $logo_img_class, $logo_href, $logo_asset_prefix, $logo_aria_label);
                ?>
                <span class="admin-sidebar__badge">Admin</span>
            </div>

            <nav class="admin-nav">
                <a href="index.php" class="admin-nav__link<?= $admin_current === 'overview' ? ' is-active' : '' ?>"<?= $admin_current === 'overview' ? ' aria-current="page"' : '' ?>>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                    Overview
                </a>
                <a href="orders.php" class="admin-nav__link<?= $admin_current === 'orders' ? ' is-active' : '' ?>"<?= $admin_current === 'orders' ? ' aria-current="page"' : '' ?>>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 11V7a4 4 0 0 0-8 0v4"/><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/></svg>
                    Orders
                </a>
                <a href="meals.php" class="admin-nav__link<?= $admin_current === 'meals' ? ' is-active' : '' ?>"<?= $admin_current === 'meals' ? ' aria-current="page"' : '' ?>>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/></svg>
                    Meals
                </a>
                <a href="coupons.php" class="admin-nav__link<?= $admin_current === 'coupons' ? ' is-active' : '' ?>"<?= $admin_current === 'coupons' ? ' aria-current="page"' : '' ?>>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 12V8H6a2 2 0 0 1-2-2 2 2 0 0 1 2-2h12v4"/><path d="M4 6v12a2 2 0 0 0 2 2h14v-4"/><path d="M18 12a2 2 0 0 0 0 4h4v-4Z"/></svg>
                    Coupons
                </a>
                <a href="analytics.php" class="admin-nav__link<?= $admin_current === 'analytics' ? ' is-active' : '' ?>"<?= $admin_current === 'analytics' ? ' aria-current="page"' : '' ?>>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3v18h18"/><path d="M7 16V9"/><path d="M12 16V5"/><path d="M17 16v-7"/></svg>
                    Analytics
                </a>
                <a href="help.php" class="admin-nav__link<?= $admin_current === 'help' ? ' is-active' : '' ?>"<?= $admin_current === 'help' ? ' aria-current="page"' : '' ?>>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                    User manual
                </a>
            </nav>

            <div class="admin-sidebar__footer">
                <a href="../account.php" class="admin-nav__link admin-nav__link--muted">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
                    Customer view
                </a>
                <a href="../menu.php" class="admin-nav__link admin-nav__link--muted" target="_blank" rel="noopener noreferrer">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>
                    View shop
                </a>
                <button type="button" class="admin-nav__signout" data-admin-signout>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                    Sign out
                </button>
            </div>
        </aside>

        <div class="admin-main-wrap">
            <header class="admin-topbar">
                <div class="admin-topbar__titles">
                    <p class="admin-topbar__eyebrow">Shop admin</p>
                    <h1 class="admin-topbar__title"><?= htmlspecialchars($admin_title) ?></h1>
                </div>
                <div class="admin-topbar__user">
                    <span class="admin-topbar__avatar" aria-hidden="true"><?= htmlspecialchars($initial) ?></span>
                    <div class="admin-topbar__meta">
                        <span class="admin-topbar__name"><?= htmlspecialchars($displayName) ?></span>
                        <span class="admin-topbar__email"><?= htmlspecialchars((string) $user['email']) ?></span>
                    </div>
                </div>
            </header>

            <main class="admin-main" id="admin-main">
