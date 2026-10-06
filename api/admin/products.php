<?php

require_once dirname(__DIR__, 2) . '/includes/admin/helpers.php';
require_once dirname(__DIR__, 2) . '/includes/data/menu-catalog.php';

admin_require_api();

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    auth_json_response([
        'ok' => true,
        'categories' => get_menu_categories(),
        'products' => CatalogAdminRepository::listForAdmin(),
    ]);
    exit;
}

admin_require_csrf();
$body = admin_read_json_body();
$action = (string) ($body['action'] ?? 'save');

try {
    if ($action === 'delete' || $action === 'delete_custom') {
        CatalogAdminRepository::deleteProduct(
            (string) ($body['id'] ?? ''),
            (string) ($body['source'] ?? 'custom')
        );
        auth_json_response(['ok' => true, 'products' => CatalogAdminRepository::listForAdmin()]);
        exit;
    }

    if ($action === 'bulk_delete') {
        $items = is_array($body['items'] ?? null) ? $body['items'] : [];
        $result = CatalogAdminRepository::deleteProducts($items);
        auth_json_response([
            'ok' => true,
            'deleted' => $result['deleted'],
            'failed' => $result['failed'],
            'products' => CatalogAdminRepository::listForAdmin(),
        ]);
        exit;
    }

    $source = (string) ($body['source'] ?? 'custom');

    if ($source === 'built-in') {
        CatalogAdminRepository::saveBuiltInOverride((string) ($body['id'] ?? ''), $body);
        auth_json_response(['ok' => true, 'products' => CatalogAdminRepository::listForAdmin()]);
        exit;
    }

    $product = CatalogAdminRepository::saveCustom($body);
    auth_json_response(['ok' => true, 'product' => $product, 'products' => CatalogAdminRepository::listForAdmin()]);
} catch (InvalidArgumentException $exception) {
    auth_json_response(['ok' => false, 'error' => $exception->getMessage()], 422);
}
