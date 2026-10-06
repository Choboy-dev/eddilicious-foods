<?php
require_once dirname(__DIR__) . '/includes/config.php';

AuthSession::requireAdmin();

$status = $_GET['status'] ?? 'all';
$allowed = ['all', 'paid', 'pending', 'failed', 'cancelled'];
if (!in_array($status, $allowed, true)) {
    $status = 'all';
}

$admin_current = 'orders';
$admin_title = 'Orders';
$admin_body_class = 'admin-page-orders';
$admin_stylesheets = ['../assets/css/admin-orders-mobile.css'];
$admin_scripts = ['../assets/js/admin-orders.js'];

include dirname(__DIR__) . '/includes/admin/shell-start.php';
?>

<div class="admin-orders">
    <div class="admin-tabs admin-orders__filters" role="tablist" aria-label="Filter orders" data-order-tabs>
        <button type="button" class="admin-tabs__btn<?= $status === 'all' ? ' is-active' : '' ?>" data-status="all" role="tab" aria-selected="<?= $status === 'all' ? 'true' : 'false' ?>">All</button>
        <button type="button" class="admin-tabs__btn<?= $status === 'paid' ? ' is-active' : '' ?>" data-status="paid" role="tab" aria-selected="<?= $status === 'paid' ? 'true' : 'false' ?>">Paid</button>
        <button type="button" class="admin-tabs__btn<?= $status === 'pending' ? ' is-active' : '' ?>" data-status="pending" role="tab" aria-selected="<?= $status === 'pending' ? 'true' : 'false' ?>">Pending</button>
        <button type="button" class="admin-tabs__btn<?= $status === 'failed' ? ' is-active' : '' ?>" data-status="failed" role="tab" aria-selected="<?= $status === 'failed' ? 'true' : 'false' ?>">Failed</button>
        <button type="button" class="admin-tabs__btn<?= $status === 'cancelled' ? ' is-active' : '' ?>" data-status="cancelled" role="tab" aria-selected="<?= $status === 'cancelled' ? 'true' : 'false' ?>">Cancelled</button>
    </div>

    <section class="admin-panel admin-orders__panel" aria-labelledby="orders-table-title">
        <div class="admin-panel__head admin-orders__panel-head">
            <div>
                <h2 id="orders-table-title" class="admin-panel__title">Order list</h2>
                <p class="admin-panel__lead admin-orders__lead">Paid orders count toward revenue. Failed orders did not go through on Stripe.</p>
            </div>
        </div>
        <div class="admin-orders__list" data-orders-table>
            <p class="admin-empty">Loading orders…</p>
        </div>
    </section>
</div>

<script>window.EDDILICIOUS_ADMIN_ORDERS = { initialStatus: <?= json_encode($status) ?> };</script>

<?php include dirname(__DIR__) . '/includes/admin/shell-end.php'; ?>
