<?php

require_once dirname(__DIR__, 2) . '/includes/auth/bootstrap.php';
require_once dirname(__DIR__, 2) . '/includes/data/contact-info.php';

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
$topic = trim((string) ($body['topic'] ?? ''));
$message = trim((string) ($body['message'] ?? ''));

$validTopics = array_column(get_contact_topics(), 'value');

if ($name === '' || mb_strlen($name) < 2) {
    auth_json_response(['ok' => false, 'error' => 'Please tell us your name.'], 422);
}

if (!auth_is_valid_email($email)) {
    auth_json_response(['ok' => false, 'error' => 'Please type a real email address.'], 422);
}

if ($phone !== '' && strlen(preg_replace('/\D/', '', $phone) ?? '') < 10) {
    auth_json_response(['ok' => false, 'error' => 'Please type a full UK phone number.'], 422);
}

if (!in_array($topic, $validTopics, true)) {
    auth_json_response(['ok' => false, 'error' => 'Please pick what your message is about.'], 422);
}

if ($message === '' || mb_strlen($message) < 10) {
    auth_json_response(['ok' => false, 'error' => 'Please give us a bit more detail so we can help.'], 422);
}

$send = ResendMailer::sendContactMessage([
    'name' => $name,
    'email' => $email,
    'phone' => $phone,
    'topic' => $topic,
    'topic_label' => get_contact_topic_label($topic),
    'message' => $message,
]);

if (!$send['ok']) {
    auth_json_response(['ok' => false, 'error' => $send['error']], 502);
}

auth_json_response([
    'ok' => true,
    'message' => 'Thanks for writing to us. We will reply to your email soon.',
]);
