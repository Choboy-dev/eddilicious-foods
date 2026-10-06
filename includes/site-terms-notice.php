<div class="site-terms-notice" data-site-terms-notice hidden>
    <div class="site-terms-notice__backdrop" aria-hidden="true"></div>
    <div
        class="site-terms-notice__panel"
        role="dialog"
        aria-modal="true"
        aria-labelledby="site-terms-notice-title"
        aria-describedby="site-terms-notice-text"
    >
        <h2 id="site-terms-notice-title" class="site-terms-notice__title">Before you continue</h2>
        <p id="site-terms-notice-text" class="site-terms-notice__text">
            Please read our terms and our food allergy policy. When you buy from us, you agree to both.
        </p>
        <p class="site-terms-notice__links">
            <a href="<?= htmlspecialchars($site_terms_notice_terms_url) ?>" target="_blank" rel="noopener noreferrer">Terms and conditions</a>
            <span class="site-terms-notice__links-sep" aria-hidden="true">·</span>
            <a href="<?= htmlspecialchars($site_terms_notice_allergy_url) ?>" target="_blank" rel="noopener noreferrer">Food allergy policy</a>
        </p>
        <label class="site-terms-notice__check">
            <input type="checkbox" class="site-terms-notice__checkbox" data-site-terms-checkbox>
            <span>I have read and agree to the terms and the food allergy policy</span>
        </label>
        <button type="button" class="site-terms-notice__btn" data-site-terms-continue disabled>Continue</button>
    </div>
</div>
