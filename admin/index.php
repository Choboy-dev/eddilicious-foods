<?php
require_once dirname(__DIR__) . '/includes/config.php';

AuthSession::requireAdmin();

$admin_current = 'overview';
$admin_title = 'Overview';
$admin_body_class = 'admin-page-overview';
$admin_stylesheets = ['../assets/css/admin-overview-mobile.css'];
$admin_scripts = ['../assets/js/admin-dashboard.js'];

include dirname(__DIR__) . '/includes/admin/shell-start.php';
?>

<div class="admin-overview">
    <section class="admin-period-panel admin-overview__period" aria-label="Filter sales by date">
        <div class="admin-period-panel__head">
            <div>
                <h2 class="admin-period-panel__title">Sales overview</h2>
                <p class="admin-period-panel__range" data-admin-period-label>Last 7 days</p>
            </div>
            <p class="admin-period-panel__hint">Paid orders via Stripe</p>
        </div>

        <div class="admin-period-filter admin-overview__period-filter" role="tablist" aria-label="Pick a time period">
            <button type="button" class="admin-period-filter__btn" role="tab" data-admin-period="today" aria-selected="false">Today</button>
            <button type="button" class="admin-period-filter__btn is-active" role="tab" data-admin-period="7d" aria-selected="true">7 days</button>
            <button type="button" class="admin-period-filter__btn" role="tab" data-admin-period="30d" aria-selected="false">30 days</button>
            <button type="button" class="admin-period-filter__btn" role="tab" data-admin-period="all" aria-selected="false">All time</button>
            <button type="button" class="admin-period-filter__btn" role="tab" data-admin-period="custom" aria-selected="false">Custom</button>
        </div>

        <div class="admin-period-custom" data-admin-period-custom hidden>
            <div class="admin-period-custom__fields">
                <div class="admin-period-custom__field">
                    <label for="admin-period-from">From</label>
                    <input id="admin-period-from" type="date" data-admin-period-from>
                </div>
                <div class="admin-period-custom__field">
                    <label for="admin-period-to">To</label>
                    <input id="admin-period-to" type="date" data-admin-period-to>
                </div>
                <button type="button" class="admin-btn admin-btn--primary admin-btn--small" data-admin-period-apply>Apply dates</button>
            </div>
            <p class="admin-period-custom__error" data-admin-period-error hidden role="alert"></p>
        </div>
    </section>

    <section class="admin-stats admin-stats--overview admin-overview__metrics" data-admin-stats aria-label="Key figures">
        <article class="admin-stat admin-stat--accent admin-stat--hero admin-overview__revenue">
            <span class="admin-stat__label">Revenue</span>
            <strong class="admin-stat__value" data-stat="revenue_total">—</strong>
            <span class="admin-stat__hint" data-admin-revenue-hint>Money from paid orders in this period</span>
        </article>

        <div class="admin-overview__status-grid">
            <article class="admin-stat admin-overview__status admin-overview__status--paid">
                <span class="admin-stat__label">Paid orders</span>
                <strong class="admin-stat__value" data-stat="orders_paid">0</strong>
            </article>
            <article class="admin-stat admin-overview__status admin-overview__status--pending">
                <span class="admin-stat__label">Pending</span>
                <strong class="admin-stat__value" data-stat="orders_pending">0</strong>
            </article>
            <article class="admin-stat admin-overview__status admin-overview__status--failed">
                <span class="admin-stat__label">Failed</span>
                <strong class="admin-stat__value" data-stat="orders_failed">0</strong>
            </article>
            <article class="admin-stat admin-overview__status admin-overview__status--cancelled">
                <span class="admin-stat__label">Cancelled</span>
                <strong class="admin-stat__value" data-stat="orders_cancelled">0</strong>
            </article>
        </div>
    </section>

    <div class="admin-grid admin-grid--2 admin-overview__panels">
        <section class="admin-panel admin-panel--chart admin-overview__chart" aria-labelledby="revenue-chart-title" data-admin-chart-panel>
            <div class="admin-panel__head">
                <div>
                    <h2 id="revenue-chart-title" class="admin-panel__title" data-admin-chart-title>Revenue chart</h2>
                    <p class="admin-panel__lead" data-admin-chart-lead>Daily totals from paid orders.</p>
                </div>
            </div>
            <div class="admin-chart-wrap">
                <div class="admin-chart" data-admin-chart aria-hidden="true"></div>
            </div>
            <p class="admin-field__hint" data-stripe-hint hidden>Stripe keys are not in your .env file yet. Revenue will show after you connect Stripe and get paid orders.</p>
        </section>

        <section class="admin-panel admin-overview__breakdown" aria-labelledby="order-counts-title">
            <div class="admin-panel__head">
                <div>
                    <h2 id="order-counts-title" class="admin-panel__title">In this period</h2>
                    <p class="admin-panel__lead" data-admin-orders-lead>Orders placed in this period.</p>
                </div>
            </div>
            <ul class="admin-order-breakdown admin-overview__breakdown-list" data-admin-order-breakdown>
                <li class="admin-order-breakdown__row admin-overview__breakdown-row admin-overview__breakdown-row--paid">
                    <span class="admin-order-breakdown__label">Paid</span>
                    <strong class="admin-order-breakdown__value" data-order-bar="paid">0</strong>
                </li>
                <li class="admin-order-breakdown__row admin-overview__breakdown-row admin-overview__breakdown-row--pending">
                    <span class="admin-order-breakdown__label">Pending</span>
                    <strong class="admin-order-breakdown__value" data-order-bar="pending">0</strong>
                </li>
                <li class="admin-order-breakdown__row admin-overview__breakdown-row admin-overview__breakdown-row--failed">
                    <span class="admin-order-breakdown__label">Failed</span>
                    <strong class="admin-order-breakdown__value" data-order-bar="failed">0</strong>
                </li>
                <li class="admin-order-breakdown__row admin-overview__breakdown-row admin-overview__breakdown-row--cancelled">
                    <span class="admin-order-breakdown__label">Cancelled</span>
                    <strong class="admin-order-breakdown__value" data-order-bar="cancelled">0</strong>
                </li>
            </ul>
            <p class="admin-period-summary" data-admin-period-summary></p>
            <div class="admin-panel__actions">
                <a href="orders.php?status=failed" class="admin-btn admin-btn--ghost admin-btn--small">View failed orders</a>
            </div>
        </section>
    </div>

    <section class="admin-panel admin-overview__recent" aria-labelledby="recent-orders-title">
        <div class="admin-panel__head">
            <div>
                <h2 id="recent-orders-title" class="admin-panel__title">Recent orders</h2>
                <p class="admin-panel__lead">The latest orders from your shop.</p>
            </div>
            <a href="orders.php" class="admin-btn admin-btn--ghost admin-btn--small">View all</a>
        </div>
        <div class="admin-table-wrap admin-overview__recent-list" data-recent-orders>
            <p class="admin-empty">Loading orders…</p>
        </div>
    </section>
</div>

<?php include dirname(__DIR__) . '/includes/admin/shell-end.php'; ?>
