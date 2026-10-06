<div class="cookie-consent" data-cookie-consent hidden>
    <div class="cookie-consent__backdrop" aria-hidden="true"></div>
    <div class="cookie-consent__panel" role="dialog" aria-modal="true" aria-labelledby="cookie-consent-title" aria-describedby="cookie-consent-text">
        <h2 id="cookie-consent-title" class="cookie-consent__title">We use cookies</h2>
        <p id="cookie-consent-text" class="cookie-consent__text">
            We use cookies and similar tools to run our site, remember your basket, see how people use our pages, and show offers. We only turn on non-essential cookies if you agree. You can decline or change your choices at any time.
        </p>

        <div class="cookie-consent__choices" data-cookie-consent-choices hidden>
            <div class="cookie-consent__choice">
                <div class="cookie-consent__choice-copy">
                    <strong>Necessary</strong>
                    <span>Needed for the site to work, like your basket and sign-in.</span>
                </div>
                <span class="cookie-consent__always-on">Always on</span>
            </div>
            <label class="cookie-consent__choice">
                <div class="cookie-consent__choice-copy">
                    <strong>Functional</strong>
                    <span>Remembers helpful settings on your device.</span>
                </div>
                <input type="checkbox" class="cookie-consent__toggle" data-cookie-choice="functional">
            </label>
            <label class="cookie-consent__choice">
                <div class="cookie-consent__choice-copy">
                    <strong>Analytics</strong>
                    <span>Helps us see which pages people visit.</span>
                </div>
                <input type="checkbox" class="cookie-consent__toggle" data-cookie-choice="analytics">
            </label>
            <label class="cookie-consent__choice">
                <div class="cookie-consent__choice-copy">
                    <strong>Marketing</strong>
                    <span>Lets us use cookies for offers and ads that may interest you.</span>
                </div>
                <input type="checkbox" class="cookie-consent__toggle" data-cookie-choice="marketing">
            </label>
        </div>

        <div class="cookie-consent__actions" data-cookie-consent-simple-actions>
            <button type="button" class="cookie-consent__btn cookie-consent__btn--green" data-cookie-consent-accept-all>I agree</button>
            <button type="button" class="cookie-consent__btn cookie-consent__btn--green" data-cookie-consent-reject-all>I decline</button>
            <button type="button" class="cookie-consent__btn cookie-consent__btn--muted" data-cookie-consent-open-choices>Change my preferences</button>
        </div>

        <div class="cookie-consent__actions" data-cookie-consent-detail-actions hidden>
            <button type="button" class="cookie-consent__btn cookie-consent__btn--green" data-cookie-consent-save>Save choices</button>
            <button type="button" class="cookie-consent__btn cookie-consent__btn--muted" data-cookie-consent-back>Back</button>
        </div>

        <p class="cookie-consent__fine-print">
            Read our <a href="<?= htmlspecialchars($cookie_consent_policy_url) ?>">Cookie Policy</a>
        </p>
    </div>
</div>
