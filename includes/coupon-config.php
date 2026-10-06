<?php
/**
 * Expose coupon config to JavaScript on every page.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/data/coupons.php';
?>
<script>
window.EDDILICIOUS_COUPONS = <?= json_encode(get_coupons_for_client(), JSON_UNESCAPED_UNICODE) ?>;
</script>
