(function () {
    'use strict';

    const Admin = window.EddiliciousAdmin;
    if (!Admin) {
        return;
    }

    const PERIOD_LABELS = {
        today: 'Today',
        '7d': 'Last 7 days',
        '30d': 'Last 30 days',
        all: 'All time',
        custom: 'Custom dates',
    };

    let currentPeriod = '7d';
    let loading = false;

    const periodButtons = document.querySelectorAll('[data-admin-period]');
    const customPanel = document.querySelector('[data-admin-period-custom]');
    const customFromInput = document.querySelector('[data-admin-period-from]');
    const customToInput = document.querySelector('[data-admin-period-to]');
    const customApplyBtn = document.querySelector('[data-admin-period-apply]');
    const customError = document.querySelector('[data-admin-period-error]');
    const periodLabel = document.querySelector('[data-admin-period-label]');
    const chartPanel = document.querySelector('[data-admin-chart-panel]');
    const statsPanel = document.querySelector('[data-admin-stats]');
    const setupAlert = document.querySelector('[data-analytics-setup]');
    const agePanel = document.querySelector('[data-analytics-age-panel]');

    function setDefaultCustomDates() {
        const today = new Date();
        const weekAgo = new Date(today);
        weekAgo.setDate(today.getDate() - 6);

        if (customToInput && !customToInput.value) {
            customToInput.value = formatInputDate(today);
        }
        if (customFromInput && !customFromInput.value) {
            customFromInput.value = formatInputDate(weekAgo);
        }
    }

    function formatInputDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return year + '-' + month + '-' + day;
    }

    function setLoading(isLoading) {
        loading = isLoading;
        if (chartPanel) {
            chartPanel.classList.toggle('is-loading', isLoading);
        }
        if (statsPanel) {
            statsPanel.classList.toggle('is-loading', isLoading);
        }
        periodButtons.forEach(function (button) {
            button.disabled = isLoading;
        });
        if (customApplyBtn) {
            customApplyBtn.disabled = isLoading;
        }
    }

    function setCustomError(message) {
        if (!customError) {
            return;
        }
        if (!message) {
            customError.hidden = true;
            customError.textContent = '';
            return;
        }
        customError.hidden = false;
        customError.textContent = message;
    }

    function setActivePeriod(period) {
        currentPeriod = period;
        periodButtons.forEach(function (button) {
            const isActive = button.getAttribute('data-admin-period') === period;
            button.classList.toggle('is-active', isActive);
            button.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });

        if (customPanel) {
            const showCustom = period === 'custom';
            customPanel.hidden = !showCustom;
            if (showCustom) {
                setDefaultCustomDates();
            }
        }

        if (periodLabel && period !== 'custom') {
            periodLabel.textContent = PERIOD_LABELS[period] || period;
        }
    }

    function buildQuery() {
        const params = new URLSearchParams();
        params.set('period', currentPeriod);

        if (currentPeriod === 'custom') {
            params.set('from', customFromInput?.value || '');
            params.set('to', customToInput?.value || '');
        }

        return 'analytics.php?' + params.toString();
    }

    function renderBreakdown(container, rows, emptyText, valueKey) {
        if (!container) {
            return;
        }

        const key = valueKey || 'count';
        if (!rows || !rows.length) {
            container.innerHTML = '<div class="admin-empty admin-empty--compact"><p>' + Admin.escapeHtml(emptyText) + '</p></div>';
            return;
        }

        let html = '<ul class="admin-breakdown">';
        rows.forEach(function (row) {
            const value = row[key] || 0;
            const pct = row.pct || 0;
            html += '<li class="admin-breakdown__row">';
            html += '<div class="admin-breakdown__meta">';
            html += '<span class="admin-breakdown__label">' + Admin.escapeHtml(row.label) + '</span>';
            html += '<span class="admin-breakdown__value">' + Admin.escapeHtml(String(value)) + ' <span class="admin-breakdown__pct">(' + pct + '%)</span></span>';
            html += '</div>';
            html += '<span class="admin-breakdown__bar" style="--bar-width:' + Math.max(6, pct) + '%"></span>';
            html += '</li>';
        });
        html += '</ul>';
        container.innerHTML = html;
    }

    function renderChart(rows, granularity) {
        const chart = document.querySelector('[data-admin-chart]');
        if (!chart) {
            return;
        }

        const data = rows || [];
        const max = Math.max.apply(null, data.map(function (row) {
            return row.views || 0;
        }).concat([1]));

        const barCount = Math.max(data.length, 1);
        chart.classList.toggle('admin-chart--dense', barCount > 14);
        chart.style.gridTemplateColumns = 'repeat(' + barCount + ', minmax(0, 1fr))';

        if (!data.length) {
            chart.innerHTML = '<p class="admin-chart__empty">No traffic in this period yet. Visit your shop pages to start collecting data.</p>';
            chart.removeAttribute('aria-hidden');
            return;
        }

        chart.innerHTML = data.map(function (row) {
            const views = row.views || 0;
            const height = Math.max(8, Math.round((views / max) * 120));
            return '<div class="admin-chart__bar-wrap">' +
                '<span class="admin-chart__value">' + views + '</span>' +
                '<span class="admin-chart__bar admin-chart__bar--traffic" style="height:' + height + 'px" title="' + Admin.escapeHtml(row.label) + ': ' + views + ' views"></span>' +
                '<span class="admin-chart__label">' + Admin.escapeHtml(row.label) + '</span>' +
                '</div>';
        }).join('');

        chart.removeAttribute('aria-hidden');

        const chartTitle = document.querySelector('[data-admin-chart-title]');
        const chartLead = document.querySelector('[data-admin-chart-lead]');
        if (chartTitle) {
            chartTitle.textContent = granularity === 'month' ? 'Traffic by month' : 'Traffic chart';
        }
        if (chartLead) {
            chartLead.textContent = granularity === 'month'
                ? 'Monthly page views.'
                : granularity === 'week'
                    ? 'Weekly page views.'
                    : 'Daily page views.';
        }
    }

    function applyStats(stats) {
        document.querySelectorAll('[data-stat]').forEach(function (node) {
            const key = node.getAttribute('data-stat');
            if (stats[key] !== undefined) {
                node.textContent = stats[key];
            }
        });

        if (periodLabel && stats.period_label) {
            periodLabel.textContent = stats.period_label;
        }

        renderChart(stats.views_chart || [], stats.chart_granularity || 'day');
        renderBreakdown(document.querySelector('[data-analytics-sources]'), stats.sources, 'No source data yet.');
        renderBreakdown(document.querySelector('[data-analytics-devices]'), stats.devices, 'No device data yet.');
        renderBreakdown(document.querySelector('[data-analytics-countries]'), stats.countries, 'No location data yet.');
        renderBreakdown(document.querySelector('[data-analytics-cities]'), stats.cities, 'No city data yet.');
        renderBreakdown(document.querySelector('[data-analytics-browsers]'), stats.browsers, 'No browser data yet.');
        renderBreakdown(document.querySelector('[data-analytics-os]'), stats.operating_systems, 'No system data yet.');
        renderBreakdown(document.querySelector('[data-analytics-pages]'), stats.pages, 'No page views yet.');
        renderBreakdown(document.querySelector('[data-analytics-referrers]'), stats.referrers, 'No referrer data yet.');

        const genderRows = (stats.gender || []).map(function (row) {
            return {
                label: row.label,
                count: row.sessions,
                pct: row.pct,
            };
        });
        renderBreakdown(
            document.querySelector('[data-analytics-gender]'),
            genderRows,
            stats.ga4_connected
                ? 'No gender data yet. Turn on Google Signals in GA4 and wait for enough traffic.'
                : 'Connect Google Analytics to see estimated gender splits.'
        );

        const ageRows = (stats.age_groups || []).map(function (row) {
            return {
                label: row.label,
                count: row.sessions,
                pct: row.pct,
            };
        });

        if (agePanel) {
            const showAge = ageRows.length > 0;
            agePanel.hidden = !showAge;
            if (showAge) {
                renderBreakdown(document.querySelector('[data-analytics-age]'), ageRows, 'No age data yet.', 'count');
            }
        }

        if (setupAlert) {
            setupAlert.hidden = Boolean(stats.ga4_connected);
        }
    }

    async function loadAnalytics() {
        setCustomError('');
        setLoading(true);

        try {
            const data = await Admin.fetch(buildQuery());
            applyStats(data.stats || {});
        } catch (error) {
            if (currentPeriod === 'custom') {
                setCustomError(error.message);
            }
        } finally {
            setLoading(false);
        }
    }

    periodButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            const period = button.getAttribute('data-admin-period');
            if (!period || loading) {
                return;
            }

            setActivePeriod(period);

            if (period !== 'custom') {
                loadAnalytics();
            }
        });
    });

    customApplyBtn?.addEventListener('click', function () {
        if (loading) {
            return;
        }
        setActivePeriod('custom');
        loadAnalytics();
    });

    setActivePeriod('7d');
    setDefaultCustomDates();
    loadAnalytics();
})();
