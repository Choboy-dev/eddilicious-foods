<?php

if (!function_exists('auth_env')) {
    require_once __DIR__ . '/auth/bootstrap.php';
}

require_once __DIR__ . '/analytics/GoogleAnalyticsClient.php';

$ga4MeasurementId = GoogleAnalyticsClient::measurementId();
if ($ga4MeasurementId === null) {
    return;
}
?>
<!-- Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars($ga4MeasurementId) ?>"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', <?= json_encode($ga4MeasurementId, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>, { anonymize_ip: true });
</script>
