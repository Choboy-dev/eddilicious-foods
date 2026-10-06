<?php
/**
 * Order lead time — we need at least 24 hours to source and cook.
 */

function get_order_lead_time_hours(): int
{
    return 24;
}

/**
 * @return list<string>
 */
function get_order_lead_notice_paragraphs(): array
{
    return [
        'To give you our best food, we prepare each order based on what is available.',
        'Please place your order at least 24 hours ahead. That gives us time to source local ingredients, cook your food, and arrange delivery or collection.',
        'We do not offer instant or same-day delivery. Thanks for planning ahead. We look forward to serving you.',
    ];
}

function get_order_lead_notice_text(): string
{
    return implode(' ', get_order_lead_notice_paragraphs());
}

function get_order_lead_notice_short(): string
{
    return 'Please order at least 24 hours ahead. We do not offer instant delivery.';
}

function get_order_lead_delivery_meta(): string
{
    return 'UK-wide delivery.';
}

function get_order_lead_collection_meta(): string
{
    return 'Pick up from us.';
}

function get_order_lead_product_suffix(): string
{
    return 'Order at least 24 hours ahead. We deliver across the UK.';
}

function get_order_success_message(string $fulfillment): string
{
    if ($fulfillment === 'collection') {
        return 'We will confirm when your food is ready to collect. We need at least 24 hours to prepare your order.';
    }

    return 'We will confirm your delivery date and time. We need at least 24 hours to source ingredients and cook your food.';
}
