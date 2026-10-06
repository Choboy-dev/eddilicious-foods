<?php
/**
 * Edilicious Foods — About page content
 */

/**
 * @return array<string, mixed>
 */
function get_about_page_content(): array
{
    return [
        'hero' => [
            'eyebrow' => 'About us',
            'title' => 'Eddilicious Foods',
            'lead' => 'We cook African and continental food for people who want home-cooked meals, good taste, and fair prices.',
            'image' => 'assets/images/images/eddilicious-image.png',
            'image_alt' => 'Chef Gloria from Eddilicious Foods',
        ],
        'story' => [
            'Eddilicious Foods is a catering outfit founded in 2012. The business was officially registered in Nigeria in 2019. We started to close the gap between affordability and good taste. In 2023 we brought our service to the United Kingdom for people who want home-cooked meals.',
            'Since we came to the UK, we have met the needs of many people who want homemade food. Our strength is our chef, Gloria. She is highly skilled in all aspects of catering. You can rely on us to deliver quality food on time.',
            'Our staff are trained professionals. We work to international standards. We want you to feel at peace when you book with us. We work hard to meet your expectations.',
        ],
        'milestones' => [
            ['year' => '2012', 'label' => 'We founded Eddilicious Foods'],
            ['year' => '2019', 'label' => 'We registered the business in Nigeria'],
            ['year' => '2023', 'label' => 'We launched our service in the UK'],
        ],
        'vision' => [
            'title' => 'Our vision',
            'text' => 'Our vision is to share the experience and beauty of home-made African and continental foods across as many cities as we can.',
        ],
        'mission' => [
            'title' => 'Our mission',
            'text' => 'At Eddilicious, we offer our own take on home-made African and intercontinental cuisines. We use traditional cooking methods, fine African produce, and the legacy of our shared food heritage. We want to bring traditional cuisine and continental dishes back to the table and let their flavours brighten up the world.',
        ],
        'services' => [
            [
                'id' => 'delivery',
                'title' => 'Home delivery',
                'text' => 'We bring our food to your door across the UK when you order at least 24 hours ahead.',
            ],
            [
                'id' => 'weddings',
                'title' => 'Weddings and birthdays',
                'text' => 'We cook for your big days and family celebrations.',
            ],
            [
                'id' => 'private',
                'title' => 'Private dinners',
                'text' => 'We cater small dinners with care and attention.',
            ],
            [
                'id' => 'office',
                'title' => 'Office events',
                'text' => 'We feed your team at work events and meetings.',
            ],
        ],
        'reasons' => [
            [
                'title' => 'Expert culinary team',
                'text' => 'Our chef and staff know how to cook and serve food to a high standard.',
            ],
            [
                'title' => 'Food made for you',
                'text' => 'We tailor our dishes to meet your specific needs.',
            ],
            [
                'title' => 'Quality and experience',
                'text' => 'We put quality first in every order and every event.',
            ],
            [
                'title' => 'Planned delivery',
                'text' => 'We need at least 24 hours to source ingredients and cook your food before we deliver.',
            ],
        ],
        'cta' => [
            'title' => 'Contact us today',
            'text' => 'Call us, message us on WhatsApp, or book catering for your next event.',
        ],
    ];
}
