<?php
require_once dirname(__DIR__) . '/includes/config.php';

AuthSession::requireAdmin();

$admin_current = 'analytics';
$admin_title = 'Analytics';
$admin_body_class = 'admin-page-analytics';
$admin_stylesheets = ['../assets/css/admin-analytics-mobile.css'];
$admin_scripts = ['../assets/js/admin-analytics.js'];

include dirname(__DIR__) . '/includes/admin/shell-start.php';
?>

<div class="admin-analytics">
    <section class="admin-period-panel admin-period-panel--analytics admin-analytics__period" aria-label="Filter traffic by date">
        <div class="admin-period-panel__head">
            <div>
                <h2 class="admin-period-panel__title">Website traffic</h2>
                <p class="admin-period-panel__range" data-admin-period-label>Last 7 days</p>
            </div>
            <p class="admin-period-panel__hint">Live visits on your shop pages</p>
        </div>

        <div class="admin-period-filter admin-analytics__period-filter" role="tablist" aria-label="Pick a time period">
            <button type="button" class="admin-period-filter__btn" role="tab" data-admin-period="today" aria-selected="false">Today</button>
            <button type="button" class="admin-period-filter__btn is-active" role="tab" data-admin-period="7d" aria-selected="true">7 days</button>
            <button type="button" class="admin-period-filter__btn" role="tab" data-admin-period="30d" aria-selected="false">30 days</button>
            <button type="button" class="admin-period-filter__btn" role="tab" data-admin-period="all" aria-selected="false">All time</button>
            <button type="button" class="admin-period-filter__btn" role="tab" data-admin-period="custom" aria-selected="false">Custom</button>
        </div>

        <div class="admin-period-custom" data-admin-period-custom hidden>
            <div class="admin-period-custom__fields">
                <div class="admin-period-custom__field">
                    <label for="admin-analytics-from">From</label>
                    <input id="admin-analytics-from" type="date" data-admin-period-from>
                </div>
                <div class="admin-period-custom__field">
                    <label for="admin-analytics-to">To</label>
                    <input id="admin-analytics-to" type="date" data-admin-period-to>
                </div>
                <button type="button" class="admin-btn admin-btn--primary admin-btn--small" data-admin-period-apply>Apply dates</button>
            </div>
            <p class="admin-period-custom__error" data-admin-period-error hidden role="alert"></p>
        </div>
    </section>

    <div class="admin-alert admin-alert--info admin-analytics__setup" data-analytics-setup hidden>
        <p><strong>Google Analytics demographics are not connected yet.</strong> Add <code>GA4_MEASUREMENT_ID</code>, <code>GA4_PROPERTY_ID</code>, and a service account JSON file to see gender and age splits.</p>
    </div>

    <section class="admin-stats admin-stats--analytics admin-analytics__metrics" data-admin-stats aria-label="Traffic summary">
        <article class="admin-stat admin-stat--accent admin-stat--hero admin-analytics__hero">
            <span class="admin-stat__label">Page views</span>
            <strong class="admin-stat__value" data-stat="pageviews">—</strong>
            <span class="admin-stat__hint">Every page someone opened in this period</span>
        </article>

        <div class="admin-analytics__stats-grid">
            <article class="admin-stat admin-analytics__stat admin-analytics__stat--sessions">
                <span class="admin-stat__label">Sessions</span>
                <strong class="admin-stat__value" data-stat="sessions">0</strong>
            </article>
            <article class="admin-stat admin-analytics__stat admin-analytics__stat--visitors">
                <span class="admin-stat__label">Visitors</span>
                <strong class="admin-stat__value" data-stat="visitors">0</strong>
            </article>
            <article class="admin-stat admin-analytics__stat admin-analytics__stat--bounce">
                <span class="admin-stat__label">Bounce rate</span>
                <strong class="admin-stat__value" data-stat="bounce_rate">0%</strong>
            </article>
            <article class="admin-stat admin-analytics__stat admin-analytics__stat--depth">
                <span class="admin-stat__label">Pages / session</span>
                <strong class="admin-stat__value" data-stat="avg_pages_per_session">0</strong>
            </article>
        </div>
    </section>

    <div class="admin-grid admin-grid--2 admin-analytics__chart-row">
        <section class="admin-panel admin-panel--chart admin-analytics__chart" aria-labelledby="traffic-chart-title" data-admin-chart-panel>
            <div class="admin-panel__head">
                <div>
                    <h2 id="traffic-chart-title" class="admin-panel__title" data-admin-chart-title>Traffic chart</h2>
                    <p class="admin-panel__lead" data-admin-chart-lead>Page views over time.</p>
                </div>
            </div>
            <div class="admin-chart-wrap">
                <div class="admin-chart admin-chart--traffic" data-admin-chart aria-hidden="true"></div>
            </div>
        </section>

        <section class="admin-panel admin-analytics__panel" aria-labelledby="traffic-sources-title">
            <div class="admin-panel__head">
                <div>
                    <h2 id="traffic-sources-title" class="admin-panel__title">Traffic sources</h2>
                    <p class="admin-panel__lead">Where people came from before they landed on your site.</p>
                </div>
            </div>
            <div data-analytics-sources></div>
        </section>
    </div>

    <div class="admin-grid admin-grid--3 admin-grid--analytics admin-analytics__demographics">
        <section class="admin-panel admin-analytics__panel" aria-labelledby="devices-title">
            <div class="admin-panel__head">
                <h2 id="devices-title" class="admin-panel__title">Devices</h2>
            </div>
            <div data-analytics-devices></div>
        </section>

        <section class="admin-panel admin-analytics__panel" aria-labelledby="locations-title">
            <div class="admin-panel__head">
                <h2 id="locations-title" class="admin-panel__title">Locations</h2>
            </div>
            <div data-analytics-countries></div>
        </section>

        <section class="admin-panel admin-analytics__panel" aria-labelledby="gender-title">
            <div class="admin-panel__head">
                <div>
                    <h2 id="gender-title" class="admin-panel__title">Gender</h2>
                    <p class="admin-panel__lead">Estimated splits from Google Analytics when connected.</p>
                </div>
            </div>
            <div data-analytics-gender></div>
        </section>
    </div>

    <div class="admin-grid admin-grid--2 admin-analytics__pages-row">
        <section class="admin-panel admin-analytics__panel" aria-labelledby="top-pages-title">
            <div class="admin-panel__head">
                <h2 id="top-pages-title" class="admin-panel__title">Top pages</h2>
            </div>
            <div data-analytics-pages></div>
        </section>

        <section class="admin-panel admin-analytics__panel" aria-labelledby="browsers-title">
            <div class="admin-panel__head">
                <h2 id="browsers-title" class="admin-panel__title">Browsers and systems</h2>
            </div>
            <div class="admin-grid admin-grid--2 admin-grid--compact admin-analytics__browsers-grid">
                <div data-analytics-browsers></div>
                <div data-analytics-os></div>
            </div>
        </section>
    </div>

    <div class="admin-grid admin-grid--2 admin-analytics__geo-row">
        <section class="admin-panel admin-analytics__panel" aria-labelledby="cities-title">
            <div class="admin-panel__head">
                <h2 id="cities-title" class="admin-panel__title">Top cities</h2>
            </div>
            <div data-analytics-cities></div>
        </section>

        <section class="admin-panel admin-analytics__panel" aria-labelledby="referrers-title">
            <div class="admin-panel__head">
                <h2 id="referrers-title" class="admin-panel__title">Referring links</h2>
            </div>
            <div data-analytics-referrers></div>
        </section>
    </div>

    <section class="admin-panel admin-analytics__panel" aria-labelledby="age-groups-title" data-analytics-age-panel hidden>
        <div class="admin-panel__head">
            <div>
                <h2 id="age-groups-title" class="admin-panel__title">Age groups</h2>
                <p class="admin-panel__lead">Estimated age brackets from Google Analytics.</p>
            </div>
        </div>
        <div data-analytics-age></div>
    </section>
</div>

<?php
include dirname(__DIR__) . '/includes/admin/shell-end.php';
