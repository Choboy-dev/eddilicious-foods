<?php
/**
 * Admin sign in — owner access only.
 */
require_once dirname(__DIR__) . '/includes/config.php';

$redirect = auth_admin_safe_redirect($_GET['redirect'] ?? 'index.php');

if (AuthSession::isAdmin()) {
    header('Location: ' . $redirect, true, 302);
    exit;
}

$signed_in_as_customer = AuthSession::isLoggedIn() && !AuthSession::isAdmin();
?>
<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin sign in — Eddilicious Foods</title>
    <?php include dirname(__DIR__) . '/includes/fonts.php'; ?>
    <link rel="stylesheet" href="../assets/css/variables.css">
    <link rel="stylesheet" href="../assets/css/brand-logo.css">
    <link rel="stylesheet" href="../assets/css/custom-select.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script>
        window.EDDILICIOUS_ADMIN_AUTH = {
            csrf: <?= json_encode(auth_csrf_token(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
            redirect: <?= json_encode($redirect, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
        };
    </script>
</head>
<body class="admin-body admin-signin-body">
    <main class="admin-signin" data-admin-signin data-admin-redirect="<?= htmlspecialchars($redirect) ?>">
        <div class="admin-signin__card">
            <header class="admin-signin__header">
                <?php
                $logo_link_class = 'admin-signin__logo';
                $logo_img_class = 'admin-signin__logo__img';
                $logo_href = '../index.php';
                $logo_asset_prefix = '../';
                include dirname(__DIR__) . '/includes/partials/site-logo.php';
                unset($logo_link_class, $logo_img_class, $logo_href, $logo_asset_prefix, $logo_aria_label);
                ?>
                <span class="admin-sidebar__badge">Admin</span>
                <h1 class="admin-signin__title">Sign in to admin</h1>
                <p class="admin-signin__lead">Use the owner email we set up for you. This area is not for customers.</p>
            </header>

            <?php if ($signed_in_as_customer): ?>
                <div class="admin-alert admin-alert--warn" role="status">
                    You are signed in as a customer, not an admin. Sign out first, then sign in with your admin email.
                </div>
                <form method="post" action="../api/auth/logout.php" class="admin-signin__logout-form" data-admin-customer-signout>
                    <button type="submit" class="admin-btn admin-btn--ghost admin-btn--small">Sign out of customer account</button>
                </form>
            <?php else: ?>
                <div class="auth-alert" data-admin-auth-alert hidden role="alert"></div>

                <div class="auth-step" data-admin-auth-step="email">
                    <form class="auth-form" data-admin-auth-email-form novalidate>
                        <div class="auth-field">
                            <label class="auth-label" for="admin-auth-email">Admin email</label>
                            <input
                                type="email"
                                id="admin-auth-email"
                                name="email"
                                class="auth-input"
                                inputmode="email"
                                autocomplete="email"
                                placeholder="owner@yourdomain.com"
                                required
                                data-admin-auth-email
                            >
                        </div>
                        <button type="submit" class="auth-btn auth-btn--primary" data-admin-auth-email-submit>
                            <span class="auth-btn__label">Send sign-in code</span>
                            <span class="auth-btn__spinner" hidden aria-hidden="true"></span>
                        </button>
                    </form>
                </div>

                <div class="auth-step" data-admin-auth-step="otp" hidden>
                    <button type="button" class="auth-back" data-admin-auth-back>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                        Back
                    </button>

                    <div class="auth-otp-intro">
                        <h2 class="auth-otp__title">Check your email</h2>
                        <p class="auth-otp__lead">
                            We sent a 6-digit code to
                            <strong data-admin-auth-otp-email>you@example.com</strong>.
                        </p>
                    </div>

                    <form class="auth-otp" data-admin-auth-otp-form novalidate>
                        <label class="auth-label" id="admin-auth-otp-label">Enter your code</label>
                        <div class="auth-otp__inputs" role="group" aria-labelledby="admin-auth-otp-label">
                            <?php for ($i = 0; $i < 6; $i++): ?>
                                <input
                                    type="text"
                                    class="auth-otp__digit"
                                    inputmode="numeric"
                                    pattern="[0-9]*"
                                    maxlength="1"
                                    autocomplete="one-time-code"
                                    aria-label="Digit <?= $i + 1 ?> of 6"
                                    data-admin-auth-otp-digit
                                >
                            <?php endfor; ?>
                        </div>
                        <p class="auth-otp__hint" data-admin-auth-otp-hint>Code expires in 10 minutes.</p>
                    </form>

                    <p class="auth-otp__resend">
                        Did not get it?
                        <button type="button" class="auth-link-btn" data-admin-auth-resend disabled>Resend code</button>
                        <span class="auth-otp__timer" data-admin-auth-resend-timer></span>
                    </p>
                </div>
            <?php endif; ?>

            <p class="admin-signin__footer">
                <a href="../signin.php" class="admin-signin__footer-link">Customer sign in</a>
            </p>
        </div>
    </main>

    <script src="../assets/js/admin-signin.js"></script>
</body>
</html>
