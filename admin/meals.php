<?php
require_once dirname(__DIR__) . '/includes/config.php';

AuthSession::requireAdmin();

$admin_current = 'meals';
$admin_title = 'Meals';
$admin_body_class = 'admin-page-meals';
$admin_stylesheets = [
    '../assets/css/admin-rich-editor.css?v=' . filemtime(dirname(__DIR__) . '/assets/css/admin-rich-editor.css'),
    '../assets/css/admin-meals-mobile.css',
];
$admin_scripts = [
    '../assets/js/admin-rich-editor.js?v=' . filemtime(dirname(__DIR__) . '/assets/js/admin-rich-editor.js'),
    '../assets/js/admin-meals.js?v=' . filemtime(dirname(__DIR__) . '/assets/js/admin-meals.js'),
];

include dirname(__DIR__) . '/includes/admin/shell-start.php';
?>

<div class="admin-meals">
    <div class="admin-alert admin-alert--info admin-meals__notice">
        You can edit built-in meals or add new ones. Upload up to 5 photos or videos (5 MB each). Changes show on the shop straight away.
    </div>

    <section class="admin-panel admin-meals__panel" aria-labelledby="meals-table-title">
        <div class="admin-panel__head admin-meals__head">
            <div>
                <h2 id="meals-table-title" class="admin-panel__title">Menu meals</h2>
                <p class="admin-panel__lead admin-meals__lead">Soups, rice, platters, and more on your shop.</p>
            </div>
            <button type="button" class="admin-btn admin-btn--primary admin-meals__add" data-meal-add>Add meal</button>
        </div>
        <div class="admin-meals-bulk admin-meals__bulk" data-meals-bulk hidden>
            <p class="admin-meals-bulk__count" data-meals-bulk-count>0 meals selected</p>
            <div class="admin-meals-bulk__actions">
                <button type="button" class="admin-btn admin-btn--ghost admin-btn--small" data-meals-bulk-clear>Clear selection</button>
                <button type="button" class="admin-btn admin-btn--danger admin-btn--small" data-meals-bulk-delete>Delete selected</button>
            </div>
        </div>
        <div class="admin-meals__list" data-meals-table>
            <p class="admin-empty">Loading meals…</p>
        </div>
    </section>
</div>

<div class="admin-modal admin-modal--meal" data-meal-modal hidden>
    <div class="admin-modal__dialog admin-modal__dialog--meal" role="dialog" aria-modal="true" aria-labelledby="meal-modal-title">
        <div class="admin-modal__head">
            <div>
                <h2 id="meal-modal-title" class="admin-modal__title" data-meal-modal-title>Add meal</h2>
                <p class="admin-modal__subtitle">Photos and videos help people choose what to order.</p>
            </div>
            <button type="button" class="admin-modal__close" data-meal-modal-close aria-label="Close">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>

        <form class="admin-form admin-meal-form" data-meal-form>
            <input type="hidden" name="source" data-meal-source value="custom">
            <input type="hidden" name="existing_id" data-meal-id value="">

            <section class="meal-media" aria-labelledby="meal-media-title">
                <div class="meal-media__head">
                    <h3 id="meal-media-title" class="meal-media__title">Photos and videos</h3>
                    <p class="meal-media__hint"><span data-meal-media-count>0</span> of 5 · 5 MB max each</p>
                </div>

                <div class="meal-media__grid" data-meal-media-grid role="list" aria-label="Uploaded meal media"></div>

                <label class="meal-media__dropzone" data-meal-media-dropzone>
                    <input type="file" class="meal-media__input" data-meal-media-input accept="image/jpeg,image/png,image/webp,image/gif,video/mp4,video/webm,video/quicktime" multiple hidden>
                    <span class="meal-media__dropzone-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                    </span>
                    <span class="meal-media__dropzone-title">Drop files here or click to upload</span>
                    <span class="meal-media__dropzone-meta">JPG, PNG, WebP, GIF, MP4, WebM, MOV</span>
                </label>
                <p class="admin-field__hint meal-media__status" data-meal-media-status hidden role="status"></p>
            </section>

            <div class="admin-field">
                <label for="meal-name">Meal name</label>
                <input id="meal-name" name="name" type="text" required data-meal-name placeholder="Egusi Soup">
            </div>

            <section class="meal-description meal-description--rich" aria-labelledby="meal-description-title">
                <div class="meal-description__head">
                    <h3 id="meal-description-title" class="meal-description__title">Description for customers</h3>
                    <p class="admin-field__hint">This text shows on the product page under Description.</p>
                </div>
                <div class="admin-field">
                    <label class="rich-editor__label" id="meal-description-label">Description</label>
                    <div data-meal-description-editor aria-labelledby="meal-description-label">
                        <?php
                        $rich_editor_placeholder = 'Tell customers what makes this meal good. Use bold text and lists where it helps.';
                        include dirname(__DIR__) . '/includes/admin/partials/rich-editor.php';
                        ?>
                    </div>
                    <p class="admin-field__hint">Click in the box, type your text, then use the toolbar. Open the style menu on the right for Normal text or Heading 1–5. Press Enter for a new line.</p>
                </div>
            </section>

            <div class="meal-slug-preview" data-meal-slug-wrap>
                <span class="meal-slug-preview__label">Shop link</span>
                <code class="meal-slug-preview__value" data-meal-slug-preview>product.php?id=new-meal</code>
                <p class="admin-field__hint" data-meal-slug-note>We make this link from the meal name.</p>
            </div>

            <div class="admin-form__grid admin-form__grid--2">
                <div class="admin-field">
                    <label for="meal-category">Category</label>
                    <select id="meal-category" name="category" data-meal-category data-custom-select required></select>
                </div>
                <div class="admin-field">
                    <label for="meal-type">Sold by</label>
                    <select id="meal-type" name="type" data-meal-type data-custom-select required>
                        <option value="litre">2 litres / 4 litres</option>
                        <option value="platter">1 tray</option>
                    </select>
                </div>
            </div>

            <div data-meal-litre-fields>
                <div class="admin-form__grid admin-form__grid--2">
                    <div class="admin-field">
                        <label for="meal-price-2">2 litres price (£)</label>
                        <input id="meal-price-2" name="price_2" type="number" min="0" step="0.01" data-meal-price-2>
                    </div>
                    <div class="admin-field">
                        <label for="meal-price-4">4 litres price (£)</label>
                        <input id="meal-price-4" name="price_4" type="number" min="0" step="0.01" data-meal-price-4>
                    </div>
                </div>
            </div>

            <div data-meal-platter-fields hidden>
                <div class="admin-form__grid admin-form__grid--2">
                    <div class="admin-field">
                        <label for="meal-price">Tray price (£)</label>
                        <input id="meal-price" name="price" type="number" min="0" step="0.01" data-meal-price>
                    </div>
                    <div class="admin-field">
                        <label for="meal-compare">Compare at price (£)</label>
                        <input id="meal-compare" name="compare_at" type="number" min="0" step="0.01" data-meal-compare>
                    </div>
                </div>
                <div class="admin-field">
                    <label for="meal-discount">Discount label (optional)</label>
                    <input id="meal-discount" name="discount_label" type="text" placeholder="10%" data-meal-discount>
                </div>
            </div>

            <label class="admin-check">
                <input type="checkbox" name="is_active" data-meal-active checked>
                Show this meal on the shop
            </label>

            <div class="admin-form__actions">
                <button type="submit" class="admin-btn admin-btn--primary" data-meal-submit>Save meal</button>
                <button type="button" class="admin-btn admin-btn--ghost" data-meal-modal-close>Cancel</button>
            </div>
            <p class="admin-field__hint admin-form__error" data-meal-form-error hidden role="alert"></p>
        </form>
    </div>
</div>

<?php include dirname(__DIR__) . '/includes/admin/shell-end.php'; ?>
