<?php

/**
 * Payment protection — after the licence period the site shows a blank black screen.
 *
 * When the client pays, either:
 *   1. Set SITE_LICENSE_PAID=true in .env, or
 *   2. Delete this file and remove the require from bootstrap.php
 */
function site_license_is_expired(): bool
{
    if (auth_env('SITE_LICENSE_PAID') === 'true') {
        return false;
    }

    $start = trim((string) auth_env('SITE_LICENSE_START', ''));
    if ($start === '') {
        return false;
    }

    $days = (int) auth_env('SITE_LICENSE_DAYS', '33');
    if ($days < 1) {
        $days = 33;
    }

    $startDate = DateTimeImmutable::createFromFormat('Y-m-d', $start);
    if (!$startDate) {
        return false;
    }

    $expires = $startDate->modify('+' . $days . ' days');
    $today = new DateTimeImmutable('today');

    return $today >= $expires;
}

function site_license_enforce(): void
{
    if (!site_license_is_expired()) {
        return;
    }

    http_response_code(503);
    header('Content-Type: text/html; charset=UTF-8');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Pragma: no-cache');
    echo '<!DOCTYPE html><html lang="en-GB"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title></title></head><body style="margin:0;background:#000"></body></html>';
    exit;
}
