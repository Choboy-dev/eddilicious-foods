<?php
/**
 * Edilicious Foods — Contact page content and site contact details
 */

/**
 * @return array{display: string, href: string, chat_message: string}
 */
function get_brand_whatsapp(): array
{
    return [
        'display' => '+44 7424 414028',
        'href' => '+447424414028',
        'chat_message' => 'Hello Eddilicious Foods! I\'m interested in placing an order. Can you assist me?',
    ];
}

function get_brand_whatsapp_url(): string
{
    $whatsapp = get_brand_whatsapp();
    $digits = preg_replace('/\D+/', '', $whatsapp['href']) ?? '';

    return 'https://wa.me/' . $digits . '?text=' . rawurlencode($whatsapp['chat_message']);
}

/**
 * @return array<string, mixed>
 */
function get_contact_info(): array
{
    return [
        'email' => 'eddiliciousfoods@gmail.com',
        'address' => 'Swansea, United Kingdom',
        'collection_address' => 'Swansea, United Kingdom',
        'phones' => [
            ['display' => '+44 7424414028', 'href' => '+447424414028'],
            ['display' => '+44 7823 807721', 'href' => '+447823807721'],
        ],
        'hours' => [
            ['days' => 'Monday to Saturday', 'times' => '9:00 am – 8:00 pm'],
            ['days' => 'Sunday', 'times' => '10:00 am – 6:00 pm'],
        ],
    ];
}

/**
 * @return list<array{label: string, url: string, network: string}>
 */
function get_social_links(): array
{
    return [
        [
            'label' => 'TikTok',
            'url' => 'https://www.tiktok.com/@eddiliciousfoods',
            'network' => 'tiktok',
        ],
        [
            'label' => 'Instagram',
            'url' => 'https://www.instagram.com/eddiliciousfoods/',
            'network' => 'instagram',
        ],
        [
            'label' => 'Facebook',
            'url' => 'https://web.facebook.com/profile.php?id=100069181024687',
            'network' => 'facebook',
        ],
    ];
}

/**
 * @return list<array{value: string, label: string, hint: string}>
 */
function get_contact_topics(): array
{
    return [
        [
            'value' => 'order',
            'label' => 'Help with an order',
            'hint' => 'Questions about your basket, delivery, or payment.',
        ],
        [
            'value' => 'catering',
            'label' => 'Catering for an event',
            'hint' => 'Weddings, birthdays, work events, and home events.',
        ],
        [
            'value' => 'complaint',
            'label' => 'Make a complaint',
            'hint' => 'Tell us what went wrong and we will put it right.',
        ],
        [
            'value' => 'general',
            'label' => 'General question',
            'hint' => 'Menu, allergens, or anything else.',
        ],
        [
            'value' => 'other',
            'label' => 'Something else',
            'hint' => 'Tell us what you need and we will help.',
        ],
    ];
}

function get_contact_topic_label(string $value): string
{
    foreach (get_contact_topics() as $topic) {
        if ($topic['value'] === $value) {
            return $topic['label'];
        }
    }

    return 'General question';
}

/**
 * @return list<array{id: string, question: string, answer: string}>
 */
function get_contact_faqs(): array
{
    return [
        [
            'id' => 'how-to-order',
            'question' => 'How do I order food?',
            'answer' => 'Go to our menu, pick what you want, and add it to your basket. When you are ready, go to checkout and pay. Please order at least 24 hours before you need your food. We will cook it fresh and arrange delivery or collection.',
        ],
        [
            'id' => 'delivery-area',
            'question' => 'Do you deliver to my area?',
            'answer' => 'We deliver across the UK. Type your postcode at checkout and we will tell you if we can reach you. If you are not sure, send us a message and we will check for you.',
        ],
        [
            'id' => 'catering',
            'question' => 'How do I book catering?',
            'answer' => 'Choose "Catering for an event" in the form on this page. Tell us your date, how many guests you have, and what food you want. We will get back to you with a quote.',
        ],
        [
            'id' => 'delivery-time',
            'question' => 'How far in advance should I order?',
            'answer' => get_order_lead_notice_text() . ' We will confirm your delivery or collection date after you pay.',
        ],
        [
            'id' => 'instant-delivery',
            'question' => 'Do you offer instant or same-day delivery?',
            'answer' => 'No. We need at least 24 hours to source local ingredients and cook your food. We do not offer instant or same-day delivery.',
        ],
    ];
}
