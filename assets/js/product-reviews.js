/**
 * Verified buyer reviews on product pages.
 */
(function () {
    'use strict';

    const root = document.querySelector('[data-product-reviews]');
    const page = document.querySelector('[data-product-page]');
    if (!root || !page) {
        return;
    }

    const productId = page.dataset.productId || '';
    const auth = window.EDDILICIOUS_AUTH || {};
    const apiBase = (function () {
        const path = window.location.pathname || '/';
        const marker = '/admin/';
        const adminIndex = path.indexOf(marker);
        if (adminIndex !== -1) {
            return path.slice(0, adminIndex + 1) + 'api/reviews/';
        }
        const lastSlash = path.lastIndexOf('/');
        if (lastSlash <= 0) {
            return '/api/reviews/';
        }
        return path.slice(0, lastSlash + 1) + 'api/reviews/';
    })();

    const form = root.querySelector('[data-review-form]');
    const formError = root.querySelector('[data-review-form-error]');
    const formSuccess = root.querySelector('[data-review-form-success]');
    const submitBtn = root.querySelector('[data-review-submit]');
    const textArea = root.querySelector('[data-review-text]');
    const charCount = root.querySelector('[data-review-char-count]');
    const reviewsList = root.querySelector('[data-reviews-list]');
    const reviewsEmpty = root.querySelector('[data-reviews-empty]');
    const summaryAverage = root.querySelector('[data-reviews-average]');
    const summaryCount = root.querySelector('[data-reviews-count]');
    const summaryStars = root.querySelector('[data-reviews-summary-stars]');
    const breakdownRoot = root.querySelector('[data-reviews-breakdown]');
    const starInput = root.querySelector('[data-star-input]');
    const states = {
        sign_in: root.querySelector('[data-review-state="sign_in"]'),
        not_purchased: root.querySelector('[data-review-state="not_purchased"]'),
        already_reviewed: root.querySelector('[data-review-state="already_reviewed"]'),
    };

    function openReviewsTab() {
        const tab = document.querySelector('[data-product-tab="reviews"]');
        if (tab) {
            tab.click();
        }
    }

    function bindReviewsTabLink(link) {
        if (!link || link.dataset.reviewsTabBound === 'true') {
            return;
        }

        link.dataset.reviewsTabBound = 'true';
        link.addEventListener('click', function (event) {
            event.preventDefault();
            openReviewsTab();
            root.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    }

    document.querySelectorAll('[data-open-reviews-tab]').forEach(bindReviewsTabLink);

    if (window.location.hash === '#product-panel-reviews') {
        window.setTimeout(openReviewsTab, 80);
    }

    function showState(reason) {
        Object.keys(states).forEach(function (key) {
            if (states[key]) {
                states[key].hidden = key !== reason;
            }
        });

        if (form) {
            form.hidden = reason !== 'eligible';
        }
    }

    function showEligibleForm() {
        Object.keys(states).forEach(function (key) {
            if (states[key]) {
                states[key].hidden = true;
            }
        });
        if (form) {
            form.hidden = false;
        }
    }

    function setFormError(message) {
        if (!formError) {
            return;
        }

        if (!message) {
            formError.hidden = true;
            formError.textContent = '';
            return;
        }

        formError.hidden = false;
        formError.textContent = message;
    }

    function setFormSuccess(message) {
        if (!formSuccess) {
            return;
        }

        if (!message) {
            formSuccess.hidden = true;
            formSuccess.textContent = '';
            return;
        }

        formSuccess.hidden = false;
        formSuccess.textContent = message;
    }

    function selectedRating() {
        const checked = starInput ? starInput.querySelector('input[type="radio"]:checked') : null;
        return checked ? Number(checked.value) : 0;
    }

    function syncStarInputVisual() {
        if (!starInput) {
            return;
        }

        const value = selectedRating();
        starInput.querySelectorAll('[data-star-input-value]').forEach(function (input) {
            const label = input.closest('.product-star-input__label');
            const starValue = Number(input.value);
            if (label) {
                label.classList.toggle('is-active', starValue <= value);
            }
        });
    }

    function updateCharCount() {
        if (!textArea || !charCount) {
            return;
        }

        charCount.textContent = String(textArea.value.length);
    }

    function renderStars(container, rating, size) {
        if (!container) {
            return;
        }

        const width = size || 14;
        let html = '';
        for (let s = 1; s <= 5; s += 1) {
            const fill = s <= Math.round(rating) ? 'currentColor' : 'none';
            html += '<svg xmlns="http://www.w3.org/2000/svg" width="' + width + '" height="' + size + '" viewBox="0 0 24 24" fill="' + fill + '" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87L18.18 22 12 18.56 5.82 22 7 14.14l-5-4.87 6.91-1.01z"/></svg>';
        }
        container.innerHTML = html;
    }

    function updateSummary(summary) {
        if (!summary) {
            return;
        }

        const count = Number(summary.review_count || 0);
        const average = Number(summary.rating_average || 0);

        if (summaryAverage) {
            summaryAverage.textContent = count > 0 ? String(average) : '—';
        }

        if (summaryCount) {
            summaryCount.textContent = count === 1 ? '1 verified review' : count + ' verified reviews';
        }

        renderStars(summaryStars, average, 18);

        if (breakdownRoot && summary.breakdown) {
            breakdownRoot.querySelectorAll('[data-breakdown-count]').forEach(function (node) {
                const star = Number(node.getAttribute('data-breakdown-count'));
                const starCount = Number(summary.breakdown[star] || 0);
                const pct = count > 0 ? Math.round((starCount / count) * 100) : 0;
                node.textContent = String(starCount);
                const fill = node.parentElement ? node.parentElement.querySelector('.product-reviews-breakdown__fill') : null;
                if (fill) {
                    fill.style.width = pct + '%';
                }
            });
        }

        const ratingSlot = document.querySelector('[data-product-rating-slot]');
        let headerLink = document.querySelector('[data-open-reviews-tab]');

        if (count <= 0) {
            if (ratingSlot) {
                ratingSlot.innerHTML = '';
            }
        } else if (!headerLink && ratingSlot) {
            ratingSlot.innerHTML =
                '<a href="#product-panel-reviews" class="product-detail__rating" data-open-reviews-tab>' +
                    '<span class="product-detail__stars" aria-hidden="true"></span>' +
                    '<span class="product-detail__rating-text"></span>' +
                '</a>';
            headerLink = ratingSlot.querySelector('[data-open-reviews-tab]');
            bindReviewsTabLink(headerLink);
        }

        const headerRating = headerLink ? headerLink.querySelector('.product-detail__rating-text') : null;

        if (count > 0 && headerLink && headerRating) {
            headerLink.setAttribute('aria-label', average + ' out of 5 from ' + count + ' reviews');
            headerRating.textContent = average + ' · ' + count + ' reviews';
            renderStars(headerLink.querySelector('.product-detail__stars'), average, 16);
        }

        const reviewsTab = document.querySelector('[data-product-tab="reviews"]');
        if (reviewsTab) {
            reviewsTab.textContent = count > 0 ? 'Reviews (' + count + ')' : 'Reviews';
        }
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function buildReviewItem(review) {
        let stars = '';
        for (let s = 1; s <= 5; s += 1) {
            const fill = s <= Number(review.rating) ? 'currentColor' : 'none';
            stars += '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="' + fill + '" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87L18.18 22 12 18.56 5.82 22 7 14.14l-5-4.87 6.91-1.01z"/></svg>';
        }

        return (
            '<li class="product-review" data-review-id="' + escapeHtml(review.id) + '">' +
                '<div class="product-review__head">' +
                    '<div>' +
                        '<p class="product-review__name">' + escapeHtml(review.name) + '</p>' +
                        '<p class="product-review__meta">' + escapeHtml(review.date) + '</p>' +
                    '</div>' +
                    '<div class="product-review__rating" aria-label="' + escapeHtml(review.rating) + ' out of 5">' + stars + '</div>' +
                '</div>' +
                '<span class="product-review__verified">' +
                    '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>' +
                    'Verified buyer' +
                '</span>' +
                '<p class="product-review__text">' + escapeHtml(review.text) + '</p>' +
            '</li>'
        );
    }

    function prependReview(review) {
        if (!reviewsList || !review) {
            return;
        }

        reviewsList.insertAdjacentHTML('afterbegin', buildReviewItem(review));
        if (reviewsEmpty) {
            reviewsEmpty.hidden = true;
        }
    }

    function applyViewer(viewer) {
        if (!viewer) {
            return;
        }

        if (viewer.has_reviewed) {
            showState('already_reviewed');
            return;
        }

        if (viewer.can_review) {
            showEligibleForm();
            return;
        }

        showState(viewer.reason || 'sign_in');
    }

    if (starInput) {
        starInput.addEventListener('change', syncStarInputVisual);
        starInput.addEventListener('mouseover', function (event) {
            const label = event.target.closest('.product-star-input__label');
            if (!label) {
                return;
            }

            const input = label.querySelector('input[type="radio"]');
            if (!input) {
                return;
            }

            const hoverValue = Number(input.value);
            starInput.querySelectorAll('[data-star-input-value]').forEach(function (entry) {
                const entryLabel = entry.closest('.product-star-input__label');
                const entryValue = Number(entry.value);
                if (entryLabel) {
                    entryLabel.classList.toggle('is-hover', entryValue <= hoverValue);
                }
            });
        });

        starInput.addEventListener('mouseleave', function () {
            starInput.querySelectorAll('.product-star-input__label').forEach(function (label) {
                label.classList.remove('is-hover');
            });
            syncStarInputVisual();
        });

        syncStarInputVisual();
    }

    if (textArea) {
        textArea.addEventListener('input', updateCharCount);
        updateCharCount();
    }

    if (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            setFormError('');
            setFormSuccess('');

            const rating = selectedRating();
            const text = textArea ? textArea.value.trim() : '';

            if (!rating) {
                setFormError('Pick a star rating.');
                return;
            }

            if (text.length < 12) {
                setFormError('Write at least 12 characters about your meal.');
                return;
            }

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Posting…';
            }

            fetch(apiBase + 'submit.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': auth.csrf || '',
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    csrf: auth.csrf || '',
                    product_id: productId,
                    rating: rating,
                    text: text,
                }),
            })
                .then(function (response) {
                    return response.json().then(function (payload) {
                        return { ok: response.ok, payload: payload };
                    });
                })
                .then(function (result) {
                    if (!result.payload || !result.payload.ok) {
                        throw new Error((result.payload && result.payload.error) || 'Could not post your review.');
                    }

                    prependReview(result.payload.review);
                    updateSummary(result.payload.summary);
                    showState('already_reviewed');
                    setFormSuccess('Thanks. Your verified review is live.');
                    form.reset();
                    updateCharCount();
                    syncStarInputVisual();
                })
                .catch(function (error) {
                    setFormError(error.message || 'Could not post your review.');
                })
                .finally(function () {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Post verified review';
                    }
                });
        });
    }

    if (auth.loggedIn) {
        fetch(apiBase + 'product.php?product_id=' + encodeURIComponent(productId), {
            credentials: 'same-origin',
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (payload) {
                if (!payload || !payload.ok) {
                    return;
                }

                applyViewer(payload.viewer);
            })
            .catch(function () {
                // Server-rendered state is enough.
            });
    }
})();
