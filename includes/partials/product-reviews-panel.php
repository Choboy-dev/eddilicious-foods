<?php
/**
 * Product reviews panel — summary, verified form, review list.
 *
 * Expects: $product, $reviews, $rating_average, $review_count, $rating_breakdown
 */
require_once dirname(__DIR__) . '/reviews/ProductReviewRepository.php';

$product_id = (string) ($product['id'] ?? '');
$rating_breakdown = $rating_breakdown ?? ($product['rating_breakdown'] ?? [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0]);
$review_user = AuthSession::user();
$review_viewer = [
    'logged_in' => $review_user !== null,
    'can_review' => false,
    'has_reviewed' => false,
    'reason' => $review_user ? 'not_purchased' : 'sign_in',
    'user_review' => null,
];

if ($review_user) {
    $existing = ProductReviewRepository::findForUserAndProduct((int) $review_user['id'], $product_id);
    if ($existing !== null) {
        $review_viewer['has_reviewed'] = true;
        $review_viewer['reason'] = 'already_reviewed';
        $review_viewer['user_review'] = $existing;
    } else {
        $eligibility = ProductReviewRepository::eligibilityForUser($review_user, $product_id);
        $review_viewer['can_review'] = $eligibility['can_review'];
        $review_viewer['reason'] = $eligibility['reason'];
    }
}

$review_return = (string) ($_SERVER['REQUEST_URI'] ?? ('product.php?id=' . urlencode($product_id)));
$review_signin_url = 'signin.php?redirect=' . rawurlencode($review_return) . '#product-panel-reviews';
$review_tab_hash = '#product-panel-reviews';
?>

<div
    class="product-tabs__panel"
    role="tabpanel"
    id="product-panel-reviews"
    aria-labelledby="product-tab-reviews"
    data-product-panel="reviews"
    data-product-reviews
    hidden
>
    <div class="product-reviews-layout">
        <aside class="product-reviews-summary" data-reviews-summary aria-label="Rating summary">
            <div class="product-reviews-summary__score">
                <p class="product-reviews-summary__average" data-reviews-average><?= $review_count > 0 ? htmlspecialchars((string) $rating_average) : '—' ?></p>
                <div class="product-reviews-summary__stars" data-reviews-summary-stars aria-hidden="true">
                    <?php for ($s = 1; $s <= 5; $s++): ?>
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="<?= $review_count > 0 && $s <= round($rating_average) ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87L18.18 22 12 18.56 5.82 22 7 14.14l-5-4.87 6.91-1.01z"/></svg>
                    <?php endfor; ?>
                </div>
                <p class="product-reviews-summary__count" data-reviews-count>
                    <?= $review_count === 1 ? '1 verified review' : (int) $review_count . ' verified reviews' ?>
                </p>
            </div>

            <ul class="product-reviews-breakdown" data-reviews-breakdown>
                <?php foreach ([5, 4, 3, 2, 1] as $star): ?>
                    <?php
                    $starCount = (int) ($rating_breakdown[$star] ?? 0);
                    $pct = $review_count > 0 ? round(($starCount / $review_count) * 100) : 0;
                    ?>
                    <li class="product-reviews-breakdown__row">
                        <span class="product-reviews-breakdown__label"><?= $star ?> star</span>
                        <span class="product-reviews-breakdown__track" aria-hidden="true">
                            <span class="product-reviews-breakdown__fill" style="width: <?= $pct ?>%"></span>
                        </span>
                        <span class="product-reviews-breakdown__value" data-breakdown-count="<?= $star ?>"><?= $starCount ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </aside>

        <div class="product-reviews-main">
            <section class="product-review-form-card" data-review-form-card aria-labelledby="product-review-form-title">
                <div class="product-review-form-card__head">
                    <h3 id="product-review-form-title" class="product-review-form-card__title">Share your review</h3>
                    <p class="product-review-form-card__lead">Only customers who bought this meal can review it.</p>
                </div>

                <div class="product-review-state" data-review-state="sign_in"<?= $review_viewer['reason'] === 'sign_in' ? '' : ' hidden' ?>>
                    <p class="product-review-state__text">Sign in with the same email you used when you paid. Then you can leave a verified review.</p>
                    <a href="<?= htmlspecialchars($review_signin_url) ?>" class="product-btn product-btn--primary product-review-state__btn">Sign in to review</a>
                </div>

                <div class="product-review-state" data-review-state="not_purchased"<?= $review_viewer['reason'] === 'not_purchased' ? '' : ' hidden' ?>>
                    <p class="product-review-state__text">Order this meal first. After we mark your payment as paid, you can come back and review it here.</p>
                    <a href="#product-detail-buy" class="product-btn product-btn--primary product-review-state__btn">Order this meal</a>
                </div>

                <div class="product-review-state" data-review-state="already_reviewed"<?= $review_viewer['reason'] === 'already_reviewed' ? '' : ' hidden' ?>>
                    <p class="product-review-state__text">Thanks. You already shared your review for this meal.</p>
                    <?php if (!empty($review_viewer['user_review'])): ?>
                        <div class="product-review-state__your-review">
                            <span class="product-review__verified">
                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                                Your verified review
                            </span>
                            <p class="product-review-state__quote">“<?= htmlspecialchars($review_viewer['user_review']['text']) ?>”</p>
                        </div>
                    <?php endif; ?>
                </div>

                <form class="product-review-form" data-review-form<?= $review_viewer['can_review'] ? '' : ' hidden' ?> novalidate>
                    <fieldset class="product-review-form__rating-field">
                        <legend class="product-review-form__legend">Your rating</legend>
                        <div class="product-star-input" data-star-input role="radiogroup" aria-label="Star rating">
                            <?php for ($s = 1; $s <= 5; $s++): ?>
                                <label class="product-star-input__label">
                                    <input type="radio" name="review_rating" value="<?= $s ?>" class="product-star-input__radio" data-star-input-value="<?= $s ?>"<?= $s === 5 ? ' checked' : '' ?>>
                                    <span class="product-star-input__star" aria-hidden="true">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1.5"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87L18.18 22 12 18.56 5.82 22 7 14.14l-5-4.87 6.91-1.01z"/></svg>
                                    </span>
                                    <span class="visually-hidden"><?= $s ?> stars</span>
                                </label>
                            <?php endfor; ?>
                        </div>
                    </fieldset>

                    <div class="product-review-form__field">
                        <label for="product-review-text">Your review</label>
                        <textarea
                            id="product-review-text"
                            class="product-review-form__textarea"
                            name="review_text"
                            rows="4"
                            maxlength="1200"
                            required
                            data-review-text
                            placeholder="Tell us what you liked. How did it taste? Would you order it again?"
                        ></textarea>
                        <p class="product-review-form__hint"><span data-review-char-count>0</span> / 1200 · at least 12 characters</p>
                    </div>

                    <p class="product-review-form__error" data-review-form-error hidden role="alert"></p>
                    <p class="product-review-form__success" data-review-form-success hidden role="status"></p>

                    <button type="submit" class="product-btn product-btn--primary product-review-form__submit" data-review-submit>
                        Post verified review
                    </button>
                </form>
            </section>

            <section class="product-reviews-list-wrap" aria-labelledby="product-reviews-list-title">
                <h3 id="product-reviews-list-title" class="product-reviews-list__title">What buyers said</h3>
                <ul class="product-reviews" data-reviews-list>
                    <?php if ($reviews): ?>
                        <?php foreach ($reviews as $review): ?>
                            <li class="product-review" data-review-id="<?= (int) ($review['id'] ?? 0) ?>">
                                <div class="product-review__head">
                                    <div>
                                        <p class="product-review__name"><?= htmlspecialchars($review['name']) ?></p>
                                        <p class="product-review__meta"><?= htmlspecialchars($review['date']) ?></p>
                                    </div>
                                    <div class="product-review__rating" aria-label="<?= (int) $review['rating'] ?> out of 5">
                                        <?php for ($s = 1; $s <= 5; $s++): ?>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="<?= $s <= (int) $review['rating'] ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87L18.18 22 12 18.56 5.82 22 7 14.14l-5-4.87 6.91-1.01z"/></svg>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <span class="product-review__verified">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                                    Verified buyer
                                </span>
                                <p class="product-review__text"><?= htmlspecialchars($review['text']) ?></p>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
                <p class="product-tabs__empty" data-reviews-empty<?= $reviews ? ' hidden' : '' ?>>No reviews yet. Be the first verified buyer to share your thoughts.</p>
            </section>
        </div>
    </div>
</div>
