(function () {
    'use strict';

    const Admin = window.EddiliciousAdmin;
    if (!Admin) {
        return;
    }

    const MAX_FILES = 5;
    const MAX_BYTES = 5 * 1024 * 1024;

    let categories = {};
    let products = [];
    let mediaItems = [];
    let editingProduct = null;
    let isNewCustom = true;
    const selectedMeals = new Set();

    const modal = document.querySelector('[data-meal-modal]');
    const form = document.querySelector('[data-meal-form]');
    const table = document.querySelector('[data-meals-table]');
    const categorySelect = document.querySelector('[data-meal-category]');
    const typeSelect = document.querySelector('[data-meal-type]');
    const litreFields = document.querySelector('[data-meal-litre-fields]');
    const platterFields = document.querySelector('[data-meal-platter-fields]');
    const nameInput = document.querySelector('[data-meal-name]');
    const descriptionEditorMount = document.querySelector('[data-meal-description-editor]');
    let descriptionEditorRoot = null;
    const slugPreview = document.querySelector('[data-meal-slug-preview]');
    const slugNote = document.querySelector('[data-meal-slug-note]');
    const errorNode = document.querySelector('[data-meal-form-error]');
    const mediaGrid = document.querySelector('[data-meal-media-grid]');
    const mediaInput = document.querySelector('[data-meal-media-input]');
    const mediaDropzone = document.querySelector('[data-meal-media-dropzone]');
    const mediaCount = document.querySelector('[data-meal-media-count]');
    const mediaStatus = document.querySelector('[data-meal-media-status]');
    const submitBtn = document.querySelector('[data-meal-submit]');
    const bulkBar = document.querySelector('[data-meals-bulk]');
    const bulkCount = document.querySelector('[data-meals-bulk-count]');
    const bulkClearBtn = document.querySelector('[data-meals-bulk-clear]');
    const bulkDeleteBtn = document.querySelector('[data-meals-bulk-delete]');

    function mealKey(product) {
        return String(product.source || 'custom') + ':' + String(product.id || '');
    }

    function pruneSelection() {
        const validKeys = new Set(products.map(mealKey));
        selectedMeals.forEach(function (key) {
            if (!validKeys.has(key)) {
                selectedMeals.delete(key);
            }
        });
    }

    function updateBulkBar() {
        const count = selectedMeals.size;

        if (bulkBar) {
            bulkBar.hidden = count === 0;
        }

        if (bulkCount) {
            bulkCount.textContent = count === 1 ? '1 meal selected' : count + ' meals selected';
        }

        if (bulkDeleteBtn) {
            bulkDeleteBtn.disabled = count === 0;
        }
    }

    function syncSelectAllCheckbox() {
        const selectAll = table ? table.querySelector('[data-meals-select-all]') : null;
        const boxes = table ? table.querySelectorAll('[data-meal-select]') : [];

        if (!selectAll || !boxes.length) {
            if (selectAll) {
                selectAll.checked = false;
                selectAll.indeterminate = false;
            }
            return;
        }

        const checkedCount = Array.from(boxes).filter(function (box) {
            return box.checked;
        }).length;

        selectAll.checked = checkedCount === boxes.length;
        selectAll.indeterminate = checkedCount > 0 && checkedCount < boxes.length;
    }

    function setMealSelected(product, selected) {
        const key = mealKey(product);
        if (selected) {
            selectedMeals.add(key);
        } else {
            selectedMeals.delete(key);
        }
        updateBulkBar();
        syncSelectAllCheckbox();
    }

    function clearMealSelection() {
        selectedMeals.clear();
        if (table) {
            table.querySelectorAll('[data-meal-select]').forEach(function (box) {
                box.checked = false;
            });
        }
        updateBulkBar();
        syncSelectAllCheckbox();
    }

    function slugify(value) {
        return String(value)
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    function updateSlugPreview() {
        if (!slugPreview) {
            return;
        }

        const existingId = document.querySelector('[data-meal-id]')?.value || '';
        const source = document.querySelector('[data-meal-source]')?.value || 'custom';
        let slug = existingId;

        if (source === 'custom' && isNewCustom) {
            slug = slugify(nameInput?.value || '') || 'new-meal';
        } else if (!slug) {
            slug = slugify(nameInput?.value || '') || 'meal';
        }

        slugPreview.textContent = 'product.php?id=' + slug;

        if (slugNote) {
            slugNote.textContent = source === 'built-in'
                ? 'Built-in meals keep their original shop link.'
                : 'We make this link from the meal name.';
        }
    }

    function setMediaStatus(message, isError) {
        if (!mediaStatus) {
            return;
        }

        if (!message) {
            mediaStatus.hidden = true;
            mediaStatus.textContent = '';
            mediaStatus.classList.remove('meal-media__status--error');
            return;
        }

        mediaStatus.hidden = false;
        mediaStatus.textContent = message;
        mediaStatus.classList.toggle('meal-media__status--error', !!isError);
    }

    function renderMediaGrid() {
        if (!mediaGrid) {
            return;
        }

        if (mediaCount) {
            mediaCount.textContent = String(mediaItems.length);
        }

        if (mediaDropzone) {
            mediaDropzone.hidden = mediaItems.length >= MAX_FILES;
        }

        mediaGrid.innerHTML = mediaItems.map(function (item, index) {
            const isVideo = item.type === 'video';
            const preview = isVideo
                ? '<div class="meal-media__video-thumb" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg></div>'
                : '<img src="../' + Admin.escapeHtml(item.path) + '" alt="" class="meal-media__thumb" width="96" height="96" loading="lazy">';

            return '<div class="meal-media__item" role="listitem" data-meal-media-item="' + index + '">' +
                preview +
                '<div class="meal-media__item-meta">' +
                    '<span class="meal-media__item-type">' + (isVideo ? 'Video' : 'Photo') + (index === 0 ? ' · Cover' : '') + '</span>' +
                    '<button type="button" class="meal-media__remove" data-meal-media-remove="' + index + '" aria-label="Remove file">Remove</button>' +
                '</div>' +
            '</div>';
        }).join('');

        mediaGrid.querySelectorAll('[data-meal-media-remove]').forEach(function (button) {
            button.addEventListener('click', function () {
                const index = Number(button.getAttribute('data-meal-media-remove'));
                mediaItems.splice(index, 1);
                renderMediaGrid();
            });
        });
    }

    async function uploadFile(file) {
        if (file.size > MAX_BYTES) {
            throw new Error(file.name + ' is too large. Max size is 5 MB.');
        }

        const formData = new FormData();
        formData.append('file', file);

        const response = await fetch('../api/admin/upload-media.php', {
            method: 'POST',
            headers: {
                'X-CSRF-Token': Admin.csrfToken(),
            },
            credentials: 'same-origin',
            body: formData,
        });

        const data = await response.json().catch(function () {
            return { ok: false, error: 'Upload failed.' };
        });

        if (!response.ok || !data.ok) {
            throw new Error(data.error || 'Upload failed.');
        }

        return {
            path: data.path,
            type: data.type,
        };
    }

    async function handleFiles(fileList) {
        const files = Array.from(fileList || []);
        if (!files.length) {
            return;
        }

        const slotsLeft = MAX_FILES - mediaItems.length;
        if (slotsLeft <= 0) {
            setMediaStatus('You can upload up to 5 files.', true);
            return;
        }

        const batch = files.slice(0, slotsLeft);
        if (files.length > slotsLeft) {
            setMediaStatus('Only ' + slotsLeft + ' more file(s) can be added.', true);
        } else {
            setMediaStatus('Uploading…', false);
        }

        if (submitBtn) {
            submitBtn.disabled = true;
        }
        if (mediaDropzone) {
            mediaDropzone.classList.add('is-busy');
        }

        try {
            for (const file of batch) {
                const uploaded = await uploadFile(file);
                mediaItems.push(uploaded);
            }
            renderMediaGrid();
            setMediaStatus('', false);
        } catch (error) {
            setMediaStatus(error.message, true);
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
            }
            if (mediaDropzone) {
                mediaDropzone.classList.remove('is-busy');
            }
            if (mediaInput) {
                mediaInput.value = '';
            }
        }
    }

    function priceLabel(product) {
        if (product.type === 'platter') {
            return '£' + Number(product.price || 0).toFixed(2);
        }
        return '£' + Number(product.price_2 || 0).toFixed(2) + ' / £' + Number(product.price_4 || 0).toFixed(2);
    }

    function isMobileMeals() {
        return window.matchMedia('(max-width: 1023px)').matches
            && document.body.classList.contains('admin-page-meals');
    }

    function debounce(fn, ms) {
        let timer;
        return function () {
            clearTimeout(timer);
            timer = setTimeout(fn, ms);
        };
    }

    function getMealThumb(product) {
        let thumb = product.image || 'assets/images/products/egusi-soup.jpeg';
        if (Array.isArray(product.media)) {
            const cover = product.media.find(function (item) { return item.type === 'image'; });
            if (cover && cover.path) {
                thumb = cover.path;
            } else if (product.media[0] && product.media[0].path) {
                thumb = product.media[0].path;
            }
        }
        return thumb;
    }

    function formatSource(source) {
        return source === 'built-in' ? 'Built-in' : 'Custom';
    }

    function updateMealSelection(box, selected) {
        const row = box.closest('tr');
        const card = box.closest('.admin-meals-card');
        if (row) {
            row.classList.toggle('admin-table__row--selected', selected);
        }
        if (card) {
            card.classList.toggle('admin-meals-card--selected', selected);
        }
    }

    function bindMealListEvents() {
        if (!table) {
            return;
        }

        const selectAll = table.querySelector('[data-meals-select-all]');
        if (selectAll) {
            selectAll.addEventListener('change', function () {
                const checked = selectAll.checked;
                products.forEach(function (product) {
                    setMealSelected(product, checked);
                });
                table.querySelectorAll('[data-meal-select]').forEach(function (box) {
                    box.checked = checked;
                    updateMealSelection(box, checked);
                });
                syncSelectAllCheckbox();
            });
        }

        table.querySelectorAll('[data-meal-select]').forEach(function (box) {
            box.addEventListener('change', function () {
                const product = products[Number(box.getAttribute('data-meal-select'))];
                if (!product) {
                    return;
                }

                setMealSelected(product, box.checked);
                updateMealSelection(box, box.checked);
            });
        });

        syncSelectAllCheckbox();
        updateBulkBar();

        table.querySelectorAll('[data-meal-edit]').forEach(function (button) {
            button.addEventListener('click', function () {
                openModal(products[Number(button.getAttribute('data-meal-edit'))]);
            });
        });

        table.querySelectorAll('[data-meal-delete]').forEach(function (button) {
            button.addEventListener('click', async function () {
                const product = products[Number(button.getAttribute('data-meal-delete'))];
                if (!product) {
                    return;
                }

                const message = product.source === 'built-in'
                    ? 'Delete "' + product.name + '" from your menu? It will be removed from the shop and this list.'
                    : 'Delete "' + product.name + '" forever? This removes it and any uploaded photos or videos.';

                if (!window.confirm(message)) {
                    return;
                }

                try {
                    const data = await Admin.fetch('products.php', {
                        method: 'POST',
                        body: JSON.stringify({
                            action: 'delete',
                            id: product.id,
                            source: product.source,
                        }),
                    });
                    products = data.products || products;
                    selectedMeals.delete(mealKey(product));
                    if (editingProduct && editingProduct.id === product.id) {
                        closeModal();
                    }
                    renderMeals();
                } catch (error) {
                    alert(error.message);
                }
            });
        });
    }

    function renderMealsTable() {
        let html = '<div class="admin-table-wrap"><table class="admin-table admin-table--meals"><thead><tr>';
        html += '<th class="admin-table__check"><input type="checkbox" class="admin-table__checkbox" data-meals-select-all aria-label="Select all meals"></th>';
        html += '<th>Meal</th><th>Category</th><th>Price</th><th>Source</th><th>Status</th><th></th>';
        html += '</tr></thead><tbody>';

        products.forEach(function (product, index) {
            const live = product.is_active && !product.is_hidden;
            const key = mealKey(product);
            const isSelected = selectedMeals.has(key);
            const thumb = getMealThumb(product);

            html += '<tr' + (isSelected ? ' class="admin-table__row--selected"' : '') + '>';
            html += '<td class="admin-table__check"><input type="checkbox" class="admin-table__checkbox" data-meal-select="' + index + '"' + (isSelected ? ' checked' : '') + ' aria-label="Select ' + Admin.escapeHtml(product.name) + '"></td>';
            html += '<td><div class="admin-meal-cell">';
            html += '<img src="../' + Admin.escapeHtml(thumb) + '" alt="" class="admin-meal-thumb" width="44" height="44">';
            html += '<div><div class="admin-table__primary">' + Admin.escapeHtml(product.name) + '</div>';
            html += '<div class="admin-table__muted">' + Admin.escapeHtml(product.id) + '</div></div></div></td>';
            html += '<td>' + Admin.escapeHtml((categories[product.category] || {}).name || product.category) + '</td>';
            html += '<td>' + Admin.escapeHtml(priceLabel(product)) + '</td>';
            html += '<td class="admin-table__muted">' + Admin.escapeHtml(product.source) + '</td>';
            html += '<td>' + (live ? '<span class="admin-badge admin-badge--live">Live</span>' : '<span class="admin-badge admin-badge--hidden">Hidden</span>') + '</td>';
            html += '<td style="white-space:nowrap">';
            html += '<button type="button" class="admin-btn admin-btn--ghost admin-btn--small" data-meal-edit="' + index + '">Edit</button> ';
            html += '<button type="button" class="admin-btn admin-btn--ghost admin-btn--small" data-meal-delete="' + index + '">Delete</button>';
            html += '</td>';
            html += '</tr>';
        });

        html += '</tbody></table></div>';
        return html;
    }

    function renderMealsCards() {
        let html = '<div class="admin-meals-toolbar">';
        html += '<label class="admin-meals-select-all">';
        html += '<input type="checkbox" class="admin-table__checkbox" data-meals-select-all aria-label="Select all meals">';
        html += '<span>Select all</span>';
        html += '</label>';
        html += '<span class="admin-meals-toolbar__count">' + products.length + ' meals</span>';
        html += '</div>';
        html += '<div class="admin-meals-cards">';

        products.forEach(function (product, index) {
            const live = product.is_active && !product.is_hidden;
            const key = mealKey(product);
            const isSelected = selectedMeals.has(key);
            const thumb = getMealThumb(product);
            const categoryName = (categories[product.category] || {}).name || product.category;

            html += '<article class="admin-meals-card' + (isSelected ? ' admin-meals-card--selected' : '') + '">';
            html += '<div class="admin-meals-card__head">';
            html += '<div class="admin-meals-card__check">';
            html += '<input type="checkbox" class="admin-table__checkbox" data-meal-select="' + index + '"' + (isSelected ? ' checked' : '') + ' aria-label="Select ' + Admin.escapeHtml(product.name) + '">';
            html += '</div>';
            html += '<div class="admin-meals-card__identity">';
            html += '<div class="admin-meals-card__media">';
            html += '<img src="../' + Admin.escapeHtml(thumb) + '" alt="" class="admin-meals-card__thumb" width="72" height="72" loading="lazy">';
            html += '</div>';
            html += '<div class="admin-meals-card__info">';
            html += '<h3 class="admin-meals-card__name">' + Admin.escapeHtml(product.name) + '</h3>';
            html += '<div class="admin-meals-card__meta">';
            html += '<span class="admin-meals-card__category">' + Admin.escapeHtml(categoryName) + '</span>';
            html += '<span class="admin-meals-card__source">' + Admin.escapeHtml(formatSource(product.source)) + '</span>';
            html += '</div>';
            html += '</div>';
            html += '</div>';
            html += '<div class="admin-meals-card__aside">';
            html += live ? '<span class="admin-badge admin-badge--live">Live</span>' : '<span class="admin-badge admin-badge--hidden">Hidden</span>';
            html += '<span class="admin-meals-card__price">' + Admin.escapeHtml(priceLabel(product)) + '</span>';
            html += '</div>';
            html += '</div>';
            html += '<div class="admin-meals-card__actions">';
            html += '<button type="button" class="admin-btn admin-btn--ghost admin-btn--small" data-meal-edit="' + index + '">Edit meal</button>';
            html += '<button type="button" class="admin-btn admin-btn--ghost admin-btn--small admin-meals-card__delete" data-meal-delete="' + index + '">Delete</button>';
            html += '</div>';
            html += '</article>';
        });

        html += '</div>';
        return html;
    }

    function renderMeals() {
        if (!table) {
            return;
        }

        if (!products.length) {
            table.innerHTML = '<div class="admin-empty"><p class="admin-empty__title">No meals found</p></div>';
            clearMealSelection();
            return;
        }

        table.innerHTML = isMobileMeals() ? renderMealsCards() : renderMealsTable();
        bindMealListEvents();
    }

    function fillCategories() {
        if (!categorySelect) {
            return;
        }
        categorySelect.innerHTML = Object.keys(categories).map(function (key) {
            const category = categories[key];
            return '<option value="' + Admin.escapeHtml(key) + '">' + Admin.escapeHtml(category.name) + '</option>';
        }).join('');
        window.EddiliciousCustomSelect?.refresh(categorySelect);
    }

    function toggleTypeFields() {
        const isPlatter = typeSelect && typeSelect.value === 'platter';
        if (litreFields) {
            litreFields.hidden = isPlatter;
        }
        if (platterFields) {
            platterFields.hidden = !isPlatter;
        }
    }

    function ensureDescriptionEditor() {
        if (!descriptionEditorMount || !window.EddiliciousRichEditor) {
            return;
        }

        if (!descriptionEditorRoot) {
            descriptionEditorRoot = descriptionEditorMount.querySelector('[data-rich-editor-root]')
                || window.EddiliciousRichEditor.init(descriptionEditorMount);
        }

        if (descriptionEditorRoot) {
            window.EddiliciousRichEditor.bind(descriptionEditorRoot);
        }
    }

    function fillDescriptionFields(product) {
        ensureDescriptionEditor();

        if (!descriptionEditorRoot || !window.EddiliciousRichEditor) {
            return;
        }

        const description = product && product.description_long ? product.description_long : null;
        const html = description && description.html ? description.html : '';

        window.EddiliciousRichEditor.setHtml(descriptionEditorRoot, html);
    }

    function openModal(product) {
        if (!modal || !form) {
            return;
        }

        editingProduct = product || null;

        const title = document.querySelector('[data-meal-modal-title]');
        if (title) {
            title.textContent = product ? 'Edit meal' : 'Add meal';
        }

        const source = product ? product.source : 'custom';
        document.querySelector('[data-meal-source]').value = source;
        document.querySelector('[data-meal-id]').value = product ? product.id : '';
        isNewCustom = !product;

        if (nameInput) {
            nameInput.value = product ? product.name : '';
        }
        categorySelect.value = product ? product.category : (Object.keys(categories)[0] || 'soups');
        typeSelect.value = product ? product.type : 'litre';
        window.EddiliciousCustomSelect?.refresh(categorySelect);
        window.EddiliciousCustomSelect?.refresh(typeSelect);
        document.querySelector('[data-meal-price-2]').value = product ? product.price_2 || '' : '';
        document.querySelector('[data-meal-price-4]').value = product ? product.price_4 || '' : '';
        document.querySelector('[data-meal-price]').value = product ? product.price || '' : '';
        document.querySelector('[data-meal-compare]').value = product && product.compare_at ? product.compare_at : '';
        document.querySelector('[data-meal-discount]').value = product && product.discount_label ? product.discount_label : '';
        document.querySelector('[data-meal-active]').checked = product ? (product.is_active && !product.is_hidden) : true;

        fillDescriptionFields(product);

        mediaItems = Array.isArray(product?.media)
            ? product.media.map(function (item) {
                return { path: item.path, type: item.type || 'image' };
            })
            : (product?.image ? [{ path: product.image, type: 'image' }] : []);

        renderMediaGrid();
        setMediaStatus('', false);
        toggleTypeFields();
        updateSlugPreview();

        if (errorNode) {
            errorNode.hidden = true;
            errorNode.textContent = '';
        }

        modal.hidden = false;
        nameInput?.focus();
    }

    function closeModal() {
        if (modal) {
            modal.hidden = true;
        }
        editingProduct = null;
        mediaItems = [];
    }

    async function loadMeals() {
        try {
            const data = await Admin.fetch('products.php');
            categories = data.categories || {};
            products = data.products || [];
            pruneSelection();
            fillCategories();
            renderMeals();
        } catch (error) {
            if (table) {
                table.innerHTML = '<div class="admin-alert admin-alert--warn">' + Admin.escapeHtml(error.message) + '</div>';
            }
        }
    }

    async function bulkDeleteMeals() {
        if (!selectedMeals.size) {
            return;
        }

        const items = products
            .filter(function (product) {
                return selectedMeals.has(mealKey(product));
            })
            .map(function (product) {
                return {
                    id: product.id,
                    source: product.source,
                };
            });

        const count = items.length;
        const message = count === 1
            ? 'Delete this meal from your menu?'
            : 'Delete ' + count + ' meals from your menu? Built-in meals are hidden. Custom meals are removed forever.';

        if (!window.confirm(message)) {
            return;
        }

        if (bulkDeleteBtn) {
            bulkDeleteBtn.disabled = true;
            bulkDeleteBtn.textContent = 'Deleting…';
        }

        try {
            const data = await Admin.fetch('products.php', {
                method: 'POST',
                body: JSON.stringify({
                    action: 'bulk_delete',
                    items: items,
                }),
            });

            products = data.products || products;
            pruneSelection();
            if (editingProduct) {
                const stillOpen = products.some(function (product) {
                    return product.id === editingProduct.id && product.source === editingProduct.source;
                });
                if (!stillOpen) {
                    closeModal();
                }
            }
            clearMealSelection();
            renderMeals();
        } catch (error) {
            alert(error.message);
        } finally {
            if (bulkDeleteBtn) {
                bulkDeleteBtn.disabled = selectedMeals.size === 0;
                bulkDeleteBtn.textContent = 'Delete selected';
            }
        }
    }

    bulkClearBtn?.addEventListener('click', clearMealSelection);
    bulkDeleteBtn?.addEventListener('click', bulkDeleteMeals);

    document.querySelector('[data-meal-add]')?.addEventListener('click', function () {
        openModal(null);
    });

    document.querySelectorAll('[data-meal-modal-close]').forEach(function (button) {
        button.addEventListener('click', closeModal);
    });

    modal?.addEventListener('click', function (event) {
        if (event.target === modal) {
            closeModal();
        }
    });

    typeSelect?.addEventListener('change', toggleTypeFields);
    nameInput?.addEventListener('input', updateSlugPreview);

    mediaInput?.addEventListener('change', function () {
        handleFiles(mediaInput.files);
    });

    mediaDropzone?.addEventListener('dragover', function (event) {
        event.preventDefault();
        mediaDropzone.classList.add('is-dragover');
    });

    mediaDropzone?.addEventListener('dragleave', function () {
        mediaDropzone.classList.remove('is-dragover');
    });

    mediaDropzone?.addEventListener('drop', function (event) {
        event.preventDefault();
        mediaDropzone.classList.remove('is-dragover');
        handleFiles(event.dataTransfer?.files);
    });

    form?.addEventListener('submit', async function (event) {
        event.preventDefault();

        if (errorNode) {
            errorNode.hidden = true;
        }

        if (!mediaItems.length) {
            if (errorNode) {
                errorNode.hidden = false;
                errorNode.textContent = 'Add at least one photo or video.';
            }
            return;
        }

        const source = document.querySelector('[data-meal-source]').value;
        const existingId = document.querySelector('[data-meal-id]').value.trim();

        const payload = {
            action: 'save',
            source: source,
            existing_id: existingId,
            id: existingId || slugify(nameInput?.value || ''),
            name: nameInput?.value.trim(),
            category: categorySelect.value,
            type: typeSelect.value,
            media: mediaItems,
            is_active: document.querySelector('[data-meal-active]').checked,
            is_hidden: !document.querySelector('[data-meal-active]').checked,
            price_2: document.querySelector('[data-meal-price-2]').value,
            price_4: document.querySelector('[data-meal-price-4]').value,
            price: document.querySelector('[data-meal-price]').value,
            compare_at: document.querySelector('[data-meal-compare]').value,
            discount_label: document.querySelector('[data-meal-discount]').value,
            description_html: (function () {
                ensureDescriptionEditor();
                return descriptionEditorRoot && window.EddiliciousRichEditor
                    ? window.EddiliciousRichEditor.getHtml(descriptionEditorRoot)
                    : '';
            })(),
        };

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Saving…';
        }

        try {
            const data = await Admin.fetch('products.php', {
                method: 'POST',
                body: JSON.stringify(payload),
            });
            products = data.products || products;
            renderMeals();
            closeModal();
        } catch (error) {
            if (errorNode) {
                errorNode.hidden = false;
                errorNode.textContent = error.message;
            }
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Save meal';
            }
        }
    });

    ensureDescriptionEditor();
    loadMeals();

    window.addEventListener('resize', debounce(function () {
        if (!products.length) {
            return;
        }
        renderMeals();
    }, 150));
})();
