<?php
/**
 * Shared auth shell — signin and signup.
 *
 * Expects:
 * - $auth_mode: 'signin' | 'signup'
 * - $auth_title
 * - $auth_lead
 * - $auth_switch_label
 * - $auth_switch_href
 * - $auth_switch_link_text
 */
require_once __DIR__ . '/../auth/bootstrap.php';

AuthSession::requireGuest(auth_safe_redirect($_GET['redirect'] ?? 'account.php'));

$auth_mode = $auth_mode ?? 'signin';
$auth_title = $auth_title ?? 'Sign in';
$auth_lead = $auth_lead ?? 'Use your email or Google to access your account.';
$auth_switch_label = $auth_switch_label ?? 'New here?';
$auth_switch_href = $auth_switch_href ?? 'signup.php';
$auth_switch_link_text = $auth_switch_link_text ?? 'Create an account';
$redirect = auth_safe_redirect($_GET['redirect'] ?? 'account.php');
if (str_contains($redirect, 'checkout.php') && ($auth_lead ?? '') === '') {
    $auth_lead = $auth_mode === 'signup'
        ? 'Create an account to finish your order. Your basket stays saved.'
        : 'Sign in to continue to checkout. Your basket stays saved.';
}
$google_enabled = GoogleOAuth::isConfigured();
$auth_error_code = isset($_GET['auth_error']) ? (string) $_GET['auth_error'] : '';
$auth_error_message = $auth_error_code !== '' ? GoogleOAuth::errorMessage($auth_error_code) : '';
$google_start_href = 'auth/google-start.php?purpose=' . urlencode($auth_mode) . '&redirect=' . urlencode($redirect);
?>

<main class="auth-page" data-auth-page data-auth-mode="<?= htmlspecialchars($auth_mode) ?>" data-auth-redirect="<?= htmlspecialchars($redirect) ?>">
    <div class="auth-shell">
        <aside class="auth-shell__brand" aria-hidden="true"></aside>

        <section class="auth-shell__panel" aria-labelledby="auth-heading">
            <div class="auth-card">
                <header class="auth-card__header">
                    <?php
                    $logo_link_class = 'auth-card__logo';
                    $logo_img_class = 'auth-card__logo__img';
                    $logo_href = 'index.php';
                    $logo_asset_prefix = '';
                    include __DIR__ . '/partials/site-logo.php';
                    unset($logo_link_class, $logo_img_class, $logo_href, $logo_asset_prefix, $logo_aria_label);
                    ?>
                    <h1 id="auth-heading" class="auth-card__title"><?= htmlspecialchars($auth_title) ?></h1>
                    <?php if ($auth_lead !== ''): ?>
                        <p class="auth-card__lead"><?= htmlspecialchars($auth_lead) ?></p>
                    <?php endif; ?>
                </header>

                <div class="auth-alert" data-auth-alert<?= $auth_error_message === '' ? ' hidden' : '' ?> role="alert"><?= htmlspecialchars($auth_error_message) ?></div>

                <!-- Step 1: email + social -->
                <div class="auth-step" data-auth-step="email">
                    <?php if ($google_enabled): ?>
                        <a href="<?= htmlspecialchars($google_start_href) ?>" class="auth-btn auth-btn--google">
                            <svg class="auth-btn__google-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="20" height="20" aria-hidden="true">
                                <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303c-1.649 4.657-6.08 8-11.303 8-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z"/>
                                <path fill="#FF3D00" d="m6.306 14.691 6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 16.318 4 9.656 8.337 6.306 14.691z"/>
                                <path fill="#4CAF50" d="M24 44c5.166 0 9.86-1.977 13.409-5.192l-6.19-5.238A11.91 11.91 0 0 1 24 36c-5.202 0-9.619-3.317-11.283-7.946l-6.522 5.025C9.505 39.556 16.227 44 24 44z"/>
                                <path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303a12.04 12.04 0 0 1-4.087 5.571l.003-.002 6.19 5.238C36.971 39.205 44 34 44 24c0-1.341-.138-2.65-.389-3.917z"/>
                            </svg>
                            <span>Continue with Google</span>
                        </a>
                        <div class="auth-divider" role="presentation">
                            <span>or</span>
                        </div>
                    <?php endif; ?>

                    <form class="auth-form" data-auth-email-form novalidate>
                        <div class="auth-field">
                            <label class="auth-label" for="auth-email">Email</label>
                            <input
                                type="email"
                                id="auth-email"
                                name="email"
                                class="auth-input"
                                inputmode="email"
                                autocomplete="email"
                                placeholder="you@example.com"
                                required
                                data-auth-email
                            >
                        </div>
                        <button type="submit" class="auth-btn auth-btn--primary" data-auth-email-submit>
                            <span class="auth-btn__label">Continue with email</span>
                            <span class="auth-btn__spinner" hidden aria-hidden="true"></span>
                        </button>
                    </form>

                    <p class="auth-switch">
                        <?= htmlspecialchars($auth_switch_label) ?>
                        <a href="<?= htmlspecialchars($auth_switch_href) ?><?= $redirect !== 'account.php' ? '?redirect=' . urlencode($redirect) : '' ?>" class="auth-switch__link">
                            <?= htmlspecialchars($auth_switch_link_text) ?>
                        </a>
                    </p>
                </div>

                <!-- Step 2: OTP -->
                <div class="auth-step" data-auth-step="otp" hidden>
                    <button type="button" class="auth-back" data-auth-back>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                        Back
                    </button>

                    <div class="auth-otp-intro">
                        <h2 class="auth-otp__title">Check your email</h2>
                        <p class="auth-otp__lead">
                            We sent a 6-digit code to
                            <strong data-auth-otp-email>you@example.com</strong>.
                        </p>
                    </div>

                    <form class="auth-otp" data-auth-otp-form novalidate>
                        <label class="auth-label" id="auth-otp-label">Enter your code</label>
                        <div class="auth-otp__inputs" role="group" aria-labelledby="auth-otp-label" data-auth-otp-inputs>
                            <?php for ($i = 0; $i < 6; $i++): ?>
                                <input
                                    type="text"
                                    class="auth-otp__digit"
                                    inputmode="numeric"
                                    pattern="[0-9]*"
                                    maxlength="1"
                                    autocomplete="one-time-code"
                                    aria-label="Digit <?= $i + 1 ?> of 6"
                                    data-auth-otp-digit
                                >
                            <?php endfor; ?>
                        </div>
                        <p class="auth-otp__hint" data-auth-otp-hint>Code expires in 10 minutes.</p>
                        <button type="submit" class="auth-btn auth-btn--primary" data-auth-otp-submit hidden>
                            Verify code
                        </button>
                    </form>

                    <p class="auth-otp__resend">
                        Did not get it?
                        <button type="button" class="auth-link-btn" data-auth-resend disabled>Resend code</button>
                        <span class="auth-otp__timer" data-auth-resend-timer></span>
                    </p>
                </div>
            </div>

            <p class="auth-legal">
                By continuing, you agree to our
                <a href="terms.php">terms</a>,
                <a href="privacy.php">privacy policy</a>,
                and
                <a href="cookies.php">cookie policy</a>.
            </p>
        </section>
    </div>
</main>
