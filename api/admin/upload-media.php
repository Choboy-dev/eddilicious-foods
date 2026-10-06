<?php

require_once dirname(__DIR__, 2) . '/includes/admin/helpers.php';
require_once dirname(__DIR__, 2) . '/includes/admin/MealMediaUpload.php';

admin_require_api();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    auth_json_response(['ok' => false, 'error' => 'Method not allowed.'], 405);
}

$csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf'] ?? null);
if (!auth_verify_csrf(is_string($csrf) ? $csrf : null)) {
    auth_json_response(['ok' => false, 'error' => 'Your session expired. Refresh the page and try again.'], 403);
}

if (empty($_FILES['file'])) {
    auth_json_response(['ok' => false, 'error' => 'Choose a file to upload.'], 422);
}

$result = MealMediaUpload::storeUploadedFile($_FILES['file']);

if (empty($result['ok'])) {
    auth_json_response(['ok' => false, 'error' => $result['error'] ?? 'Upload failed.'], 422);
}

auth_json_response([
    'ok' => true,
    'path' => $result['path'],
    'type' => $result['type'],
    'mime' => $result['mime'],
]);
