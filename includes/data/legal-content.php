<?php
/**
 * Legal pages — shared content and navigation
 */

function get_food_allergy_policy_pdf_url(): string
{
    return 'assets/uploads/food-allergy-or-intolerance.pdf';
}

function get_food_allergy_policy_title(): string
{
    return 'Food allergy and intolerance policy';
}

/**
 * @return list<array{slug: string, label: string, url: string}>
 */
function get_legal_nav(): array
{
    return [
        ['slug' => 'terms', 'label' => 'Terms and conditions', 'url' => 'terms.php'],
        ['slug' => 'privacy', 'label' => 'Privacy', 'url' => 'privacy.php'],
        ['slug' => 'cookies', 'label' => 'Cookies', 'url' => 'cookies.php'],
    ];
}

/**
 * @return array{slug: string, title: string, eyebrow: string, lead: string, updated: string, sections: list<array<string, mixed>>}
 */
function get_legal_page(string $slug): array
{
    $pages = [
        'terms' => get_legal_terms(),
        'privacy' => get_legal_privacy(),
        'cookies' => get_legal_cookies(),
    ];

    if (!isset($pages[$slug])) {
        return $pages['terms'];
    }

    return $pages[$slug];
}

/**
 * @return array{slug: string, title: string, eyebrow: string, lead: string, updated: string, sections: list<array<string, mixed>>}
 */
function get_legal_terms(): array
{
    return [
        'slug' => 'terms',
        'title' => 'Terms & Conditions',
        'eyebrow' => 'Legal',
        'lead' => 'These terms apply to catering bookings and online orders from Eddilicious Foods. By booking with us or buying from our website, you agree to the terms below.',
        'updated' => '6 July 2026',
        'sections' => [
            [
                'id' => 'booking-deposit',
                'heading' => '1. Booking & Deposit',
                'paragraphs' => [
                    'All bookings are subject to availability and are only confirmed once a non-refundable deposit is received.',
                ],
                'list' => [
                    'A 25% deposit of the total quote is required to secure your event date.',
                    'Bookings should ideally be made at least 14 days before the event. Late bookings may be subject to availability and a surcharge.',
                ],
            ],
            [
                'id' => 'final-numbers',
                'heading' => '2. Final Numbers & Menu Selection',
                'paragraphs' => [
                    'Final guest numbers and menu choices must be confirmed no later than 7 days before the event.',
                ],
                'list' => [
                    'This number will be used for your final invoice. We do not offer refunds for guest reductions after this point.',
                    'Any special dietary requirements (e.g., vegetarian, vegan, allergies) must be communicated in writing at the time of final confirmation.',
                ],
            ],
            [
                'id' => 'payment-terms',
                'heading' => '3. Payment Terms',
                'list' => [
                    'Full payment is required no later than 5 days before the event date.',
                    'Payment can be made via bank transfer, PayPal, or card payment.',
                    'Failure to pay in full may result in cancellation of services.',
                ],
            ],
            [
                'id' => 'cancellations',
                'heading' => '4. Cancellations',
                'paragraphs' => [
                    'Cancellations must be made in writing (via email or text).',
                    'Cancellations made:',
                ],
                'list' => [
                    'More than 14 days before the event: Deposit retained, no further charges.',
                    '7–14 days before the event: 50% of the total fee will be charged.',
                    'Less than 7 days before the event: 100% of the total fee will be charged.',
                ],
            ],
            [
                'id' => 'changes-by-eddilicious',
                'heading' => '5. Changes by Eddilicious Foods',
                'paragraphs' => [
                    'In the unlikely event that we need to cancel or make significant changes due to circumstances beyond our control (e.g., illness, severe weather, equipment failure), you will be offered:',
                ],
                'list' => [
                    'A full refund, or',
                    'An alternative date, where possible.',
                ],
            ],
            [
                'id' => 'food-handling-allergies',
                'heading' => '6. Food handling, allergies, and intolerances',
                'paragraphs' => [
                    'We take care when we cook, but our kitchen handles allergens such as nuts, gluten, dairy, eggs, soy, and shellfish. Cross-contamination can still happen.',
                    'You must tell us about any allergies or intolerances before you order. If you buy online, read our allergy policy and agree to it at checkout.',
                    'We are not liable for allergic reactions if we were not told about an allergy or intolerance, or if you did not read and accept our allergy policy.',
                ],
                'list' => [
                    'Tell us about allergies in your order notes or when you book catering.',
                    'Make sure your guests know what allergens may be present.',
                    'Read our written allergy and intolerance policy before you pay.',
                ],
                'documents' => [
                    [
                        'title' => get_food_allergy_policy_title(),
                        'description' => 'This PDF explains how we handle allergens, what you need to tell us, and what we cannot guarantee.',
                        'url' => get_food_allergy_policy_pdf_url(),
                        'filename' => 'food-allergy-or-intolerance.pdf',
                    ],
                ],
            ],
            [
                'id' => 'equipment-venue',
                'heading' => '7. Equipment & Venue Access',
                'list' => [
                    'Access to the venue must be available at the agreed setup time. Any delays may impact food quality or service.',
                    'Any hired equipment (e.g., platters, chafing dishes) must be returned clean and undamaged within 48 hours unless otherwise agreed.',
                    'Damage or loss of equipment may incur a replacement fee.',
                ],
            ],
            [
                'id' => 'waste-cleanup',
                'heading' => '8. Waste & Cleanup',
                'list' => [
                    'We provide basic cleanup of food service areas unless otherwise agreed.',
                    'Large-scale waste disposal, deep cleaning, or staff service throughout the event must be requested in advance and may incur an additional charge.',
                ],
            ],
            [
                'id' => 'liability',
                'heading' => '9. Liability',
                'list' => [
                    'Eddilicious Foods holds relevant public liability insurance.',
                    'We are not liable for any loss, injury, or damage caused by third-party suppliers, clients, or guests.',
                ],
            ],
            [
                'id' => 'photography-social',
                'heading' => '10. Photography & Social Media',
                'list' => [
                    'We may take photographs of our food displays for marketing purposes.',
                    'If you do not wish images from your event to be used, please let us know in writing.',
                ],
            ],
            [
                'id' => 'force-majeure',
                'heading' => '11. Force Majeure',
                'list' => [
                    'We shall not be liable for any failure or delay in performance resulting from causes beyond our reasonable control (e.g., natural disasters, transport issues, strikes).',
                ],
            ],
            [
                'id' => 'online-orders',
                'heading' => '12. Online shop orders & delivery',
                'paragraphs' => [
                    'These notes apply when you order from our website for delivery or collection.',
                    get_order_lead_notice_text(),
                ],
                'list' => [
                    'We source local ingredients and cook fresh for each order.',
                    'We do not offer instant or same-day delivery.',
                    'We will confirm your delivery or collection date after you pay.',
                    'If we cannot fulfil your order on your requested date, we will contact you to agree another date or refund you.',
                ],
            ],
        ],
    ];
}

/**
 * @return array{slug: string, title: string, eyebrow: string, lead: string, updated: string, sections: list<array<string, mixed>>}
 */
function get_legal_privacy(): array
{
    return [
        'slug' => 'privacy',
        'title' => 'Privacy policy',
        'eyebrow' => 'Legal',
        'lead' => 'This policy explains what personal data we collect, why we use it, and the choices you have.',
        'updated' => '17 June 2026',
        'sections' => [
            [
                'id' => 'introduction',
                'heading' => '1. Who we are',
                'paragraphs' => [
                    'Eddilicious Foods is the data controller for personal data described in this policy.',
                    'We follow UK data protection law, including UK GDPR and the Data Protection Act 2018.',
                ],
            ],
            [
                'id' => 'what-we-collect',
                'heading' => '2. What we collect',
                'paragraphs' => [
                    'We collect data you give us and data created when you use our site.',
                ],
                'list' => [
                    'Name, email address, phone number, and delivery address',
                    'Order history, basket contents, and wishlist items stored in your browser',
                    'Account sign-in details (email codes or Google sign-in where used)',
                    'Messages you send through our contact form',
                    'Technical data such as IP address, browser type, and cookies (see our cookie policy)',
                ],
            ],
            [
                'id' => 'how-we-use',
                'heading' => '3. How we use your data',
                'paragraphs' => [
                    'We use personal data to run our business and serve you.',
                ],
                'list' => [
                    'Take and deliver orders',
                    'Manage your account and sign you in',
                    'Reply to questions and support requests',
                    'Send service emails such as order updates and sign-in codes',
                    'Improve our website and fix problems',
                    'Meet legal and food safety obligations',
                ],
            ],
            [
                'id' => 'legal-bases',
                'heading' => '4. Why we are allowed to use your data',
                'paragraphs' => [
                    'We rely on one or more of the following legal bases:',
                ],
                'list' => [
                    'Contract — to provide the food and services you ask for',
                    'Legitimate interests — to run and improve our business in a fair way',
                    'Consent — for optional marketing emails where we ask for permission',
                    'Legal obligation — where the law requires us to keep or share data',
                ],
            ],
            [
                'id' => 'email',
                'heading' => '5. Email and marketing',
                'paragraphs' => [
                    'We send emails you need for your account and orders, such as one-time sign-in codes and order updates. You cannot opt out of these while you have an active order or account that needs them.',
                    'If you join our newsletter, we use your email to send offers and news. You can unsubscribe at any time using the link in each email or by contacting us.',
                    'We do not sell your email address to other companies.',
                ],
            ],
            [
                'id' => 'sharing',
                'heading' => '6. Who we share data with',
                'paragraphs' => [
                    'We share data only when needed to run our service.',
                ],
                'list' => [
                    'Payment providers to process card payments',
                    'Email providers to send sign-in codes and service messages',
                    'Delivery partners where we use them',
                    'IT providers that host our website and tools',
                    'Authorities when the law requires it',
                ],
            ],
            [
                'id' => 'retention',
                'heading' => '7. How long we keep data',
                'paragraphs' => [
                    'We keep personal data only as long as we need it for the reasons above, including legal, tax, and food safety records.',
                    'Account data is kept while your account is open and for a reasonable period after closure.',
                ],
            ],
            [
                'id' => 'personal-data',
                'heading' => '8. Your rights',
                'paragraphs' => [
                    'Under UK law you have rights over your personal data, including the right to access, correct, delete, or restrict use in some cases.',
                    'You can also object to certain processing and withdraw consent where we rely on it.',
                    'We do not sell your personal information. This site is aimed at UK customers and UK privacy law applies.',
                    'To use your rights, contact us through the contact page. You can also complain to the Information Commissioner\'s Office (ICO) if you are unhappy with how we handle data.',
                ],
            ],
            [
                'id' => 'contact',
                'heading' => '9. Contact',
                'paragraphs' => [
                    'For privacy questions, use our contact page and choose a general enquiry.',
                ],
            ],
        ],
    ];
}

/**
 * @return array{slug: string, title: string, eyebrow: string, lead: string, updated: string, sections: list<array<string, mixed>>}
 */
function get_legal_cookies(): array
{
    require_once __DIR__ . '/contact-info.php';
    $contact = get_contact_info();

    return [
        'slug' => 'cookies',
        'title' => 'Cookie policy',
        'eyebrow' => 'Legal',
        'lead' => 'This page tells you what cookies and similar tools we use on eddiliciousfoods.co.uk, why we use them, and how you can control them.',
        'updated' => '17 June 2026',
        'page_class' => 'legal-page--cookies',
        'callout' => [
            'id' => 'manage-cookies',
            'title' => 'Change your cookie choices',
            'text' => 'You can open your settings at any time. We will only use non-essential cookies if you switch them on.',
            'button' => 'Manage cookie settings',
            'button_detail' => true,
        ],
        'sections' => [
            [
                'id' => 'who-we-are',
                'heading' => '1. Who we are',
                'paragraphs' => [
                    'Eddilicious Foods is a UK food business based in Swansea. We run this website so you can order food and book catering.',
                    'We are the data controller for personal data collected through cookies and similar tools on this site.',
                    'Questions? Email us at ' . $contact['email'] . ' or use our contact page.',
                ],
            ],
            [
                'id' => 'uk-law',
                'heading' => '2. UK law we follow',
                'paragraphs' => [
                    'We follow UK privacy rules, including UK GDPR, the Data Protection Act 2018, and the Privacy and Electronic Communications Regulations (PECR).',
                    'Under PECR we must ask for your consent before we use non-essential cookies or similar tools, unless the law allows us to use them without consent.',
                    'Strictly necessary cookies do not need consent because the site cannot work without them.',
                ],
            ],
            [
                'id' => 'what-are-cookies',
                'heading' => '3. What cookies and similar tools are',
                'paragraphs' => [
                    'Cookies are small text files saved on your phone or computer when you visit a website.',
                    'We also use similar browser storage, such as local storage and session storage, to remember your basket and sign-in.',
                    'In this policy, we call all of these "cookies" for short.',
                ],
            ],
            [
                'id' => 'how-we-ask',
                'heading' => '4. How we ask for your consent',
                'paragraphs' => [
                    'When you first visit, we show a cookie banner. You can tap I agree, I decline, or Change my preferences.',
                    'We do not switch on analytics, marketing, or optional functional tools until you agree to them.',
                    'Rejecting cookies is as easy as accepting them. You do not have to accept non-essential cookies to use our site.',
                    'We keep a record of your choice in your browser and in our database so we can show you the right tools on return visits and meet our legal duties.',
                ],
            ],
            [
                'id' => 'categories',
                'heading' => '5. Cookie categories',
                'paragraphs' => [
                    'We group cookies into four categories. You can turn optional categories on or off in your settings.',
                ],
                'list' => [
                    'Necessary — needed for security, checkout, your basket, and sign-in. Always on.',
                    'Functional — remembers helpful choices, such as coupon codes you applied.',
                    'Analytics — helps us count visits and see which pages people use. Includes our own stats and Google Analytics when you agree.',
                    'Marketing — lets us use cookies for offers and ads that may interest you.',
                ],
            ],
            [
                'id' => 'cookie-list',
                'heading' => '6. Cookies and storage we use',
                'paragraphs' => [
                    'The table below lists the main cookies and browser storage we use today. Third-party names may change when those companies update their services.',
                ],
                'table' => [
                    'caption' => 'Cookie and storage list for Eddilicious Foods',
                    'headers' => ['Name', 'Purpose', 'Category', 'Duration', 'Provider'],
                    'rows' => [
                        ['PHPSESSID (or similar)', 'Keeps you signed in and protects forms', 'Necessary', 'Session', 'Eddilicious Foods'],
                        ['edd_cookie_consent', 'Remembers your cookie choices', 'Necessary', 'Up to 12 months', 'Eddilicious Foods'],
                        ['edd_consent_id', 'Anonymous ID for consent records', 'Necessary', 'Up to 12 months', 'Eddilicious Foods'],
                        ['edilicious_cart / edilicious_wishlist (session storage)', 'Remembers items while you browse', 'Necessary', 'Until you close the browser tab', 'Eddilicious Foods'],
                        ['edilicious_applied_coupon / edilicious_pending_coupon (session storage)', 'Remembers coupon codes you applied', 'Functional', 'Until you close the browser tab', 'Eddilicious Foods'],
                        ['edd_visitor_id / edd_session_id (browser storage)', 'Counts visits for our own site stats', 'Analytics', 'Visitor ID up to 12 months; session up to 30 minutes idle', 'Eddilicious Foods'],
                        ['_ga, _ga_*', 'Google Analytics visit stats', 'Analytics', 'Up to 24 months (Google may vary this)', 'Google'],
                        ['edd_marketing_opt_in', 'Shows you agreed to marketing cookies', 'Marketing', 'Up to 12 months', 'Eddilicious Foods'],
                    ],
                ],
            ],
            [
                'id' => 'third-parties',
                'heading' => '7. Third-party cookies',
                'paragraphs' => [
                    'When you agree to analytics, we may load Google Analytics (Google Ireland Limited). Google may set cookies on your device and process data in the UK, EU, or US under its own terms.',
                    'Read Google\'s privacy information at policies.google.com/privacy.',
                    'If you sign in with Google, Google may set its own cookies under its privacy policy.',
                    'Links to social media sites may set cookies when you leave our site.',
                ],
            ],
            [
                'id' => 'lawful-basis',
                'heading' => '8. Lawful basis',
                'paragraphs' => [
                    'We rely on legitimate interests for strictly necessary cookies because we need them to run a safe online shop.',
                    'We rely on your consent for functional, analytics, and marketing cookies. You can withdraw consent at any time.',
                ],
            ],
            [
                'id' => 'retention',
                'heading' => '9. How long we keep data',
                'paragraphs' => [
                    'Your cookie choice is stored in your browser for up to 12 months, unless you clear it sooner.',
                    'We store consent logs in our secure database for up to 24 months so we can prove what you agreed to and answer questions from regulators if needed.',
                    'Analytics data kept in our database is described in our privacy policy.',
                ],
            ],
            [
                'id' => 'control',
                'heading' => '10. How to control or delete cookies',
                'paragraphs' => [
                    'Use Manage cookie settings at the top of this page, or Cookie settings in the footer, to change your choices.',
                    'You can also block or delete cookies in your browser settings. If you block all cookies, parts of our site may not work.',
                    'To clear basket or wishlist data stored in your browser, use your browser\'s site data settings or clear browsing data for this site.',
                ],
            ],
            [
                'id' => 'your-rights',
                'heading' => '11. Your rights',
                'paragraphs' => [
                    'Under UK data protection law you have rights over your personal data, including the right to access, correct, or delete data in some cases.',
                    'You can complain to the Information Commissioner\'s Office (ICO) at ico.org.uk if you are unhappy with how we use cookies.',
                    'For wider data questions, see our privacy policy.',
                ],
            ],
            [
                'id' => 'contact',
                'heading' => '12. Contact',
                'paragraphs' => [
                    'Email: ' . $contact['email'],
                    'Address: ' . $contact['address'],
                    'You can also reach us through our contact page.',
                ],
            ],
        ],
    ];
}
