(function () {
    'use strict';

    const config = window.EDDILICIOUS_ADMIN || {};
    const apiBase = config.apiBase || '../api/admin/';

    function csrfToken() {
        return config.csrf || '';
    }

    async function adminFetch(path, options) {
        const opts = options || {};
        const headers = Object.assign({ Accept: 'application/json' }, opts.headers || {});

        if (opts.method && opts.method !== 'GET') {
            headers['Content-Type'] = 'application/json';
            headers['X-CSRF-Token'] = csrfToken();
        }

        const response = await fetch(apiBase + path, Object.assign({}, opts, { headers: headers }));
        const data = await response.json().catch(function () {
            return { ok: false, error: 'We could not read the server response.' };
        });

        if (!response.ok || !data.ok) {
            throw new Error(data.error || 'Something went wrong. Try again.');
        }

        return data;
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function statusBadge(status) {
        const label = String(status || 'pending');
        return '<span class="admin-badge admin-badge--' + escapeHtml(label) + '">' + escapeHtml(label) + '</span>';
    }

    function formatDate(value) {
        if (!value) {
            return '—';
        }
        const date = new Date(value);
        if (Number.isNaN(date.getTime())) {
            return value;
        }
        return date.toLocaleString('en-GB', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    }

    function bindSignOut() {
        const button = document.querySelector('[data-admin-signout]');
        if (!button) {
            return;
        }

        button.addEventListener('click', async function () {
            try {
                await fetch('../api/auth/logout.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': csrfToken(),
                    },
                    body: '{}',
                });
            } catch (error) {
                // Still redirect if logout request fails.
            }
            window.location.href = 'signin.php';
        });
    }

    window.EddiliciousAdmin = {
        fetch: adminFetch,
        escapeHtml: escapeHtml,
        statusBadge: statusBadge,
        formatDate: formatDate,
        csrfToken: csrfToken,
    };

    bindSignOut();
})();
