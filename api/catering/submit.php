<?php

require_once dirname(__DIR__, 2) . '/includes/auth/bootstrap.php';
require_once dirname(__DIR__, 2) . '/includes/data/catering-info.php';
require_once dirname(__DIR__, 2) . '/includes/payments/TwilioWhatsAppNotifier.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    auth_json_response(['ok' => false, 'error' => 'Method not allowed.'], 405);
}

$body = auth_read_json_body();
$csrf = $body['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);

if (!auth_verify_csrf(is_string($csrf) ? $csrf : null)) {
    auth_json_response(['ok' => false, 'error' => 'Your session expired. Refresh the page and try again.'], 403);
}

$name = trim((string) ($body['name'] ?? ''));
$email = auth_sanitize_email((string) ($body['email'] ?? ''));
$phone = trim((string) ($body['phone'] ?? ''));
$eventType = trim((string) ($body['event_type'] ?? ''));
$eventDate = trim((string) ($body['event_date'] ?? ''));
$eventTime = trim((string) ($body['event_time'] ?? ''));
$location = trim((string) ($body['location'] ?? ''));
$guestCount = (int) ($body['guest_count'] ?? 0);
$channel = trim((string) ($body['channel'] ?? ''));
$notes = trim((string) ($body['notes'] ?? ''));
$drinks = $body['drinks'] ?? [];

$validEventTypes = array_column(get_catering_event_types(), 'value');
$validChannels = array_column(get_catering_channels(), 'value');

if ($name === '' || mb_strlen($name) < 2) {
    auth_json_response(['ok' => false, 'error' => 'Please tell us your name.'], 422);
}

if (!auth_is_valid_email($email)) {
    auth_json_response(['ok' => false, 'error' => 'Please type a real email address.'], 422);
}

if ($phone === '' || strlen(preg_replace('/\D/', '', $phone) ?? '') < 10) {
    auth_json_response(['ok' => false, 'error' => 'Please type a full UK phone number.'], 422);
}

if (!in_array($eventType, $validEventTypes, true)) {
    auth_json_response(['ok' => false, 'error' => 'Please pick an event type.'], 422);
}

if ($eventDate === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $eventDate)) {
    auth_json_response(['ok' => false, 'error' => 'Please pick a valid event date.'], 422);
}

$eventDateObj = DateTimeImmutable::createFromFormat('Y-m-d', $eventDate);
$today = new DateTimeImmutable('today');
if ($eventDateObj === false || $eventDateObj < $today) {
    auth_json_response(['ok' => false, 'error' => 'Please pick a date in the future.'], 422);
}

if ($eventTime === '' || !preg_match('/^(0[1-9]|1[0-2]):[0-5]\d\s(AM|PM)$/', $eventTime)) {
    auth_json_response(['ok' => false, 'error' => 'Please pick a valid event time.'], 422);
}

if ($guestCount < 1 || $guestCount > 5000) {
    auth_json_response(['ok' => false, 'error' => 'Please tell us how many guests you expect.'], 422);
}

if (!in_array($channel, $validChannels, true)) {
    auth_json_response(['ok' => false, 'error' => 'Please pick how we should reach you.'], 422);
}

if (!is_array($drinks)) {
    $drinks = [];
}

$cleanDrinks = [];
foreach ($drinks as $drink) {
    if (!is_array($drink)) {
        continue;
    }

    $id = trim((string) ($drink['id'] ?? ''));
    $drinkName = trim((string) ($drink['name'] ?? ''));
    $size = trim((string) ($drink['size'] ?? ''));
    $sizeLabel = trim((string) ($drink['size_label'] ?? ''));

    if ($id === '' || $drinkName === '') {
        continue;
    }

    if (!in_array($size, ['1', '2'], true)) {
        $size = '1';
    }

    if ($sizeLabel === '') {
        $sizeLabel = $size === '2' ? '2 litres' : '1 litre';
    }

    $cleanDrinks[] = [
        'id' => $id,
        'name' => $drinkName,
        'size' => $size,
        'size_label' => $sizeLabel,
    ];
}

$booking = [
    'name' => $name,
    'email' => $email,
    'phone' => $phone,
    'event_type' => $eventType,
    'event_type_label' => get_catering_event_type_label($eventType),
    'event_date' => $eventDate,
    'event_time' => $eventTime,
    'location' => $location,
    'guest_count' => $guestCount,
    'channel' => $channel,
    'channel_label' => get_catering_channel_label($channel),
    'drinks' => $cleanDrinks,
    'notes' => $notes,
];

$emailSend = ResendMailer::sendCateringBooking($booking);
if (!$emailSend['ok']) {
    auth_json_response(['ok' => false, 'error' => $emailSend['error']], 502);
}

$whatsappSend = TwilioWhatsAppNotifier::sendCateringBookingAlert($booking);
if (!$whatsappSend['ok']) {
    error_log('Catering WhatsApp alert failed: ' . ($whatsappSend['error'] ?? 'unknown'));
}

auth_json_response([
    'ok' => true,
    'message' => 'Thanks for your booking request. We will be in touch soon.',
]);
