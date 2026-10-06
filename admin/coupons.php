<?php
require_once dirname(__DIR__) . '/includes/config.php';

AuthSession::requireAdmin();

$admin_current = 'coupons';
$admin_title = 'Coupons';
$admin_body_class = 'admin-page-coupons';
$admin_stylesheets = ['../assets/css/admin-coupons-mobile.css'];
$admin_scripts = ['../assets/js/admin-coupons.js'];

include dirname(__DIR__) . '/includes/admin/shell-start.php';
?>

<div class="admin-coupons">
    <div class="admin-alert admin-alert--info admin-coupons__notice">
        Only one coupon can be live at a time. It shows on your homepage and works at checkout. When you add a new one, the old live coupon is paused.
    </div>

    <section class="admin-coupon-live admin-coupons__live" aria-labelledby="coupon-live-title">
        <div class="admin-coupon-live__head admin-coupons__live-head">
            <div>
                <h2 id="coupon-live-title" class="admin-panel__title">Live on your shop</h2>
                <p class="admin-panel__lead admin-coupons__live-lead">This is what customers see on your homepage right now.</p>
            </div>
            <span class="admin-badge admin-badge--live" data-coupon-live-badge hidden>Live</span>
        </div>
        <div class="admin-coupon-live__preview admin-coupons__live-preview" data-coupon-live-preview>
            <p class="admin-empty">Loading live coupon…</p>
        </div>
    </section>

    <div class="admin-split admin-split--coupons admin-coupons__body">
        <section class="admin-panel admin-coupons__list-panel" aria-labelledby="coupon-list-title">
            <div class="admin-panel__head admin-coupons__list-head">
                <div>
                    <h2 id="coupon-list-title" class="admin-panel__title">All coupons</h2>
                    <p class="admin-panel__lead admin-coupons__list-lead">Live and paused codes in your shop.</p>
                </div>
            </div>
            <div class="admin-coupons__list" data-coupons-table>
                <p class="admin-empty">Loading coupons…</p>
            </div>
        </section>

        <section class="admin-panel admin-coupons__form-panel" aria-labelledby="coupon-form-title">
            <div class="admin-panel__head admin-coupons__form-head">
                <div>
                    <h2 id="coupon-form-title" class="admin-panel__title" data-coupon-form-title>Edit live coupon</h2>
                    <p class="admin-panel__lead admin-coupons__form-lead" data-coupon-form-lead>Change the code, discount, or banner text.</p>
                </div>
                <button type="button" class="admin-btn admin-btn--primary admin-coupons__new" data-coupon-new hidden>New coupon</button>
            </div>
            <form class="admin-form admin-coupon-form admin-coupons__form" data-coupon-form>
                <input type="hidden" name="id" data-coupon-id value="0">

                <div class="admin-form__grid admin-form__grid--2">
                    <div class="admin-field">
                        <label for="coupon-code">Code</label>
                        <input id="coupon-code" name="code" type="text" required placeholder="EDDFIRST15" data-coupon-code autocapitalize="characters" spellcheck="false">
                    </div>
                    <div class="admin-field">
                        <label for="coupon-type">Type</label>
                        <select id="coupon-type" name="type" data-coupon-type data-custom-select>
                            <option value="percent">Percent off</option>
                            <option value="fixed">Fixed amount off</option>
                        </select>
                    </div>
                </div>

                <div class="admin-form__grid admin-form__grid--2">
                    <div class="admin-field">
                        <label for="coupon-value">Value</label>
                        <input id="coupon-value" name="value" type="number" min="0" step="0.01" required data-coupon-value>
                        <p class="admin-field__hint">Percent (e.g. 15) or pounds (e.g. 5).</p>
                    </div>
                    <div class="admin-field">
                        <label for="coupon-min">Min order (£)</label>
                        <input id="coupon-min" name="min_order" type="number" min="0" step="0.01" value="0" data-coupon-min>
                    </div>
                </div>

                <div class="admin-field">
                    <label for="coupon-promo-hint">Homepage banner text</label>
                    <textarea id="coupon-promo-hint" name="promo_hint" rows="2" placeholder="15% off when you order from us the first time. Type this code when you pay." data-coupon-promo-hint></textarea>
                    <p class="admin-field__hint">Shown next to the code on your homepage. We fill this in from the discount if you leave it blank.</p>
                </div>

                <div class="admin-field">
                    <label for="coupon-message">Checkout success message</label>
                    <input id="coupon-message" name="message" type="text" required placeholder="We took 15% off your order." data-coupon-message>
                </div>

                <div class="admin-field">
                    <label for="coupon-expires">Expires (optional)</label>
                    <input id="coupon-expires" name="expires_at" type="date" data-coupon-expires>
                </div>

                <label class="admin-check">
                    <input type="checkbox" name="once_per_customer" data-coupon-once>
                    One use per customer email
                </label>

                <label class="admin-check" data-coupon-live-toggle hidden>
                    <input type="checkbox" name="is_active" data-coupon-active checked>
                    Make this the live coupon
                </label>

                <div class="admin-form__actions admin-coupons__form-actions">
                    <button type="submit" class="admin-btn admin-btn--primary" data-coupon-submit>Save coupon</button>
                    <button type="button" class="admin-btn admin-btn--ghost" data-coupon-reset>Cancel</button>
                </div>
                <p class="admin-field__hint admin-form__error" data-coupon-form-error hidden role="alert"></p>
            </form>
        </section>
    </div>
</div>

<?php include dirname(__DIR__) . '/includes/admin/shell-end.php'; ?>
