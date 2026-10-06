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
    let customFrom = '';
    let customTo = '';
    let loading = false;
    let lastRecentOrders = [];

    function isMobileOverview() {
        return window.matchMedia('(max-width: 1023px)').matches
            && document.body.classList.contains('admin-page-overview');
    }

    function debounce(fn, ms) {
        let timer;
        return function () {
            clearTimeout(timer);
            timer = setTimeout(fn, ms);
        };
    }

    const periodButtons = document.querySelectorAll('[data-admin-period]');
    const customPanel = document.querySelector('[data-admin-period-custom]');
    const customFromInput = document.querySelector('[data-admin-period-from]');
    const customToInput = document.querySelector('[data-admin-period-to]');
    const customApplyBtn = document.querySelector('[data-admin-period-apply]');
    const customError = document.querySelector('[data-admin-period-error]');
    const periodLabel = document.querySelector('[data-admin-period-label]');
    const chartPanel = document.querySelector('[data-admin-chart-panel]');
    const statsPanel = document.querySelector('[data-admin-stats]');

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

    function buildStatsQuery() {
        const params = new URLSearchParams();
        params.set('period', currentPeriod);

        if (currentPeriod === 'custom') {
            customFrom = customFromInput?.value || '';
            customTo = customToInput?.value || '';
            params.set('from', customFrom);
            params.set('to', customTo);
        }

        return 'stats.php?' + params.toString();
    }

    function renderOrdersTable(orders) {
        if (!orders.length) {
            return '<div class="admin-empty"><p class="admin-empty__title">No orders yet</p><p>When someone pays, their order will show here.</p></div>';
        }

        let html = '<table class="admin-table"><thead><tr>';
        html += '<th>Order</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th>';
        html += '</tr></thead><tbody>';

        orders.forEach(function (order) {
            html += '<tr>';
            html += '<td><span class="admin-table__primary">' + Admin.escapeHtml(order.order_ref) + '</span></td>';
            html += '<td><div class="admin-table__primary">' + Admin.escapeHtml(order.customer_name || 'Guest') + '</div>';
            html += '<div class="admin-table__muted">' + Admin.escapeHtml(order.email) + '</div></td>';
            html += '<td>' + Admin.escapeHtml(order.total_label) + '</td>';
            html += '<td>' + Admin.statusBadge(order.status) + '</td>';
            html += '<td class="admin-table__muted">' + Admin.escapeHtml(Admin.formatDate(order.created_at)) + '</td>';
            html += '</tr>';
        });

        html += '</tbody></table>';
        return html;
    }

    function renderOrdersCards(orders) {
        let html = '<div class="admin-order-cards">';

        orders.forEach(function (order) {
            html += '<article class="admin-order-card">';
            html += '<div class="admin-order-card__head">';
            html += '<span class="admin-order-card__ref">' + Admin.escapeHtml(order.order_ref) + '</span>';
            html += Admin.statusBadge(order.status);
            html += '</div>';
            html += '<div class="admin-order-card__body">';
            html += '<p class="admin-order-card__customer">' + Admin.escapeHtml(order.customer_name || 'Guest') + '</p>';
            if (order.email) {
                html += '<p class="admin-order-card__email">' + Admin.escapeHtml(order.email) + '</p>';
            }
            html += '</div>';
            html += '<div class="admin-order-card__foot">';
            html += '<strong class="admin-order-card__total">' + Admin.escapeHtml(order.total_label) + '</strong>';
            html += '<time class="admin-order-card__date">' + Admin.escapeHtml(Admin.formatDate(order.created_at)) + '</time>';
            html += '</div>';
            html += '</article>';
        });

        html += '</div>';
        return html;
    }

    function renderRecentOrders(orders) {
        if (!orders.length) {
            return '<div class="admin-empty"><p class="admin-empty__title">No orders yet</p><p>When someone pays, their order will show here.</p></div>';
        }

        if (isMobileOverview()) {
            return renderOrdersCards(orders);
        }

        return renderOrdersTable(orders);
    }

    function renderChart(rows, granularity) {
        const chart = document.querySelector('[data-admin-chart]');
        if (!chart) {
            return;
        }

        const data = rows || [];
        const max = Math.max.apply(null, data.map(function (row) {
            return row.total_pence || 0;
        }).concat([1]));

        const barCount = Math.max(data.length, 1);
        chart.classList.toggle('admin-chart--dense', barCount > 14);
        chart.style.gridTemplateColumns = 'repeat(' + barCount + ', minmax(0, 1fr))';

        if (!data.length) {
            chart.innerHTML = '<p class="admin-chart__empty">No paid orders in this period yet.</p>';
            chart.removeAttribute('aria-hidden');
            return;
        }

        chart.innerHTML = data.map(function (row) {
            const total = row.total_pence || 0;
            const height = Math.max(8, Math.round((total / max) * 120));
            const value = total > 0 ? '£' + (total / 100).toFixed(total >= 10000 ? 0 : 2) : '£0';
            return '<div class="admin-chart__bar-wrap">' +
                '<span class="admin-chart__value">' + value + '</span>' +
                '<span class="admin-chart__bar" style="height:' + height + 'px" title="' + Admin.escapeHtml(row.label) + ': ' + value + '"></span>' +
                '<span class="admin-chart__label">' + Admin.escapeHtml(row.label) + '</span>' +
                '</div>';
        }).join('');

        chart.removeAttribute('aria-hidden');

        const chartTitle = document.querySelector('[data-admin-chart-title]');
        const chartLead = document.querySelector('[data-admin-chart-lead]');
        if (chartTitle) {
            chartTitle.textContent = granularity === 'month' ? 'Revenue by month' : 'Revenue chart';
        }
        if (chartLead) {
            chartLead.textContent = granularity === 'month'
                ? 'Monthly totals from paid orders.'
                : granularity === 'week'
                    ? 'Weekly totals from paid orders.'
                    : 'Daily totals from paid orders.';
        }
    }

    function updateOrderBars(stats) {
        const values = {
            paid: stats.orders_paid || 0,
            pending: stats.orders_pending || 0,
            failed: stats.orders_failed || 0,
            cancelled: stats.orders_cancelled || 0,
        };
        const total = values.paid + values.pending + values.failed + values.cancelled;

        document.querySelectorAll('[data-order-bar]').forEach(function (node) {
            const key = node.getAttribute('data-order-bar');
            const count = values[key] || 0;
            node.textContent = String(count);

            const row = node.closest('.admin-order-breakdown__row');
            if (row) {
                const width = total > 0 ? Math.max(6, Math.round((count / total) * 100)) : 0;
                row.style.setProperty('--bar-width', width + '%');
            }
        });

        const summary = document.querySelector('[data-admin-period-summary]');
        if (summary) {
            const paid = values.paid;
            summary.textContent = total > 0
                ? paid + ' of ' + total + ' orders were paid in this period.'
                : 'No orders in this period yet.';
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

        renderChart(stats.revenue_chart || [], stats.chart_granularity || 'day');
        updateOrderBars(stats);

        const stripeHint = document.querySelector('[data-stripe-hint]');
        if (stripeHint && !stats.stripe_configured) {
            stripeHint.hidden = false;
        }
    }

    async function loadDashboard() {
        setCustomError('');
        setLoading(true);

        try {
            const data = await Admin.fetch(buildStatsQuery());
            applyStats(data.stats || {});

            const recent = document.querySelector('[data-recent-orders]');
            if (recent) {
                lastRecentOrders = data.recent_orders || [];
                recent.innerHTML = renderRecentOrders(lastRecentOrders);
            }
        } catch (error) {
            if (currentPeriod === 'custom') {
                setCustomError(error.message);
            }

            const recent = document.querySelector('[data-recent-orders]');
            if (recent) {
                recent.innerHTML = '<div class="admin-alert admin-alert--warn">' + Admin.escapeHtml(error.message) + '</div>';
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
                loadDashboard();
            }
        });
    });

    customApplyBtn?.addEventListener('click', function () {
        if (loading) {
            return;
        }
        setActivePeriod('custom');
        loadDashboard();
    });

    setActivePeriod('7d');
    setDefaultCustomDates();
    loadDashboard();

    window.addEventListener('resize', debounce(function () {
        const recent = document.querySelector('[data-recent-orders]');
        if (!recent || !lastRecentOrders.length) {
            return;
        }
        recent.innerHTML = renderRecentOrders(lastRecentOrders);
    }, 150));
})();
