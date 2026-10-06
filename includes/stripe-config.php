<?php
/**
 * Expose Stripe publishable key to checkout (public key only).
 */
require_once __DIR__ . '/auth/bootstrap.php';

$publishableKey = auth_env('STRIPE_PUBLISHABLE_KEY', '');
$configured = auth_stripe_configured();
?>
<script>
window.EDDILICIOUS_STRIPE = {
    publishableKey: <?= json_encode($publishableKey, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>,
    configured: <?= $configured ? 'true' : 'false' ?>
};
</script>
