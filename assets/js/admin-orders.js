(function () {
    'use strict';

    const Admin = window.EddiliciousAdmin;
    const config = window.EDDILICIOUS_ADMIN_ORDERS || {};
    if (!Admin) {
        return;
    }

    let currentStatus = config.initialStatus || 'all';
    let lastOrders = [];
    let lastTotal = 0;

    function isMobileOrders() {
        return window.matchMedia('(max-width: 1023px)').matches
            && document.body.classList.contains('admin-page-orders');
    }

    function debounce(fn, ms) {
        let timer;
        return function () {
            clearTimeout(timer);
            timer = setTimeout(fn, ms);
        };
    }

    function formatFulfillment(value) {
        if (value === 'collection') {
            return 'Collection';
        }
        if (value === 'delivery') {
            return 'Delivery';
        }
        return value ? String(value) : '—';
    }

    function phoneHref(phone) {
        const digits = String(phone).replace(/[^\d+]/g, '');
        return digits ? 'tel:' + digits : '';
    }

    function renderOrdersTable(orders, total) {
        let html = '<div class="admin-table-wrap"><table class="admin-table"><thead><tr>';
        html += '<th>Order</th><th>Customer</th><th>Items</th><th>Total</th><th>Coupon</th><th>Status</th><th>Date</th>';
        html += '</tr></thead><tbody>';

        orders.forEach(function (order) {
            html += '<tr>';
            html += '<td><span class="admin-table__primary">' + Admin.escapeHtml(order.order_ref) + '</span>';
            html += '<div class="admin-table__muted">' + Admin.escapeHtml(order.fulfillment) + '</div></td>';
            html += '<td><div class="admin-table__primary">' + Admin.escapeHtml(order.customer_name || 'Guest') + '</div>';
            html += '<div class="admin-table__muted">' + Admin.escapeHtml(order.email) + '</div>';
            if (order.phone) {
                html += '<div class="admin-table__muted">' + Admin.escapeHtml(order.phone) + '</div>';
            }
            html += '</td>';
            html += '<td>' + Admin.escapeHtml(String(order.item_count)) + '</td>';
            html += '<td>' + Admin.escapeHtml(order.total_label) + '</td>';
            html += '<td class="admin-table__muted">' + Admin.escapeHtml(order.coupon_code || '—') + '</td>';
            html += '<td>' + Admin.statusBadge(order.status) + '</td>';
            html += '<td class="admin-table__muted">' + Admin.escapeHtml(Admin.formatDate(order.created_at)) + '</td>';
            html += '</tr>';
        });

        html += '</tbody></table></div>';
        html += '<p class="admin-field__hint" style="margin-top:12px">Showing ' + orders.length + ' of ' + total + ' orders.</p>';
        return html;
    }

    function renderOrdersCards(orders, total) {
        let html = '<div class="admin-orders-cards">';

        orders.forEach(function (order) {
            const status = String(order.status || 'pending');
            const fulfillment = String(order.fulfillment || '');
            const itemLabel = order.item_count === 1 ? '1 item' : order.item_count + ' items';

            html += '<article class="admin-orders-card admin-orders-card--' + Admin.escapeHtml(status) + '">';
            html += '<div class="admin-orders-card__head">';
            html += '<div class="admin-orders-card__identity">';
            html += '<h3 class="admin-orders-card__customer">' + Admin.escapeHtml(order.customer_name || 'Guest') + '</h3>';
            html += '<p class="admin-orders-card__ref">' + Admin.escapeHtml(order.order_ref) + '</p>';
            html += '</div>';
            html += '<div class="admin-orders-card__aside">';
            html += Admin.statusBadge(status);
            html += '<strong class="admin-orders-card__total">' + Admin.escapeHtml(order.total_label) + '</strong>';
            html += '</div>';
            html += '</div>';

            html += '<div class="admin-orders-card__tags">';
            html += '<span class="admin-orders-card__tag admin-orders-card__tag--' + Admin.escapeHtml(fulfillment) + '">';
            html += Admin.escapeHtml(formatFulfillment(fulfillment));
            html += '</span>';
            html += '<span class="admin-orders-card__meta">' + Admin.escapeHtml(itemLabel) + '</span>';
            html += '</div>';

            if (order.email || order.phone) {
                html += '<div class="admin-orders-card__contact">';
                if (order.email) {
                    html += '<a class="admin-orders-card__link" href="mailto:' + Admin.escapeHtml(order.email) + '">';
                    html += Admin.escapeHtml(order.email);
                    html += '</a>';
                }
                if (order.phone) {
                    const href = phoneHref(order.phone);
                    html += '<a class="admin-orders-card__link" href="' + Admin.escapeHtml(href) + '">';
                    html += Admin.escapeHtml(order.phone);
                    html += '</a>';
                }
                html += '</div>';
            }

            html += '<div class="admin-orders-card__foot">';
            html += '<time class="admin-orders-card__date">' + Admin.escapeHtml(Admin.formatDate(order.created_at)) + '</time>';
            if (order.coupon_code) {
                html += '<span class="admin-orders-card__coupon">Coupon: <strong>' + Admin.escapeHtml(order.coupon_code) + '</strong></span>';
            }
            html += '</div>';
            html += '</article>';
        });

        html += '</div>';
        html += '<p class="admin-orders__count">Showing ' + orders.length + ' of ' + total + ' orders</p>';
        return html;
    }

    function renderOrders(orders, total) {
        const wrap = document.querySelector('[data-orders-table]');
        if (!wrap) {
            return;
        }

        if (!orders.length) {
            wrap.innerHTML = '<div class="admin-empty"><p class="admin-empty__title">No orders in this list</p><p>Try another filter.</p></div>';
            return;
        }

        wrap.innerHTML = isMobileOrders()
            ? renderOrdersCards(orders, total)
            : renderOrdersTable(orders, total);
    }

    async function loadOrders(status) {
        currentStatus = status;
        const wrap = document.querySelector('[data-orders-table]');
        if (wrap) {
            wrap.innerHTML = '<p class="admin-empty">Loading orders…</p>';
        }

        try {
            const data = await Admin.fetch('orders.php?status=' + encodeURIComponent(status) + '&limit=100');
            lastOrders = data.orders || [];
            lastTotal = data.total || 0;
            renderOrders(lastOrders, lastTotal);
        } catch (error) {
            if (wrap) {
                wrap.innerHTML = '<div class="admin-alert admin-alert--warn">' + Admin.escapeHtml(error.message) + '</div>';
            }
        }
    }

    document.querySelectorAll('[data-order-tabs] [data-status]').forEach(function (button) {
        button.addEventListener('click', function () {
            const status = button.getAttribute('data-status') || 'all';
            document.querySelectorAll('[data-order-tabs] .admin-tabs__btn').forEach(function (tab) {
                const isActive = tab === button;
                tab.classList.toggle('is-active', isActive);
                tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });
            const url = new URL(window.location.href);
            url.searchParams.set('status', status);
            window.history.replaceState({}, '', url.toString());
            loadOrders(status);
        });
    });

    loadOrders(currentStatus);

    window.addEventListener('resize', debounce(function () {
        if (!lastOrders.length) {
            return;
        }
        renderOrders(lastOrders, lastTotal);
    }, 150));
})();
