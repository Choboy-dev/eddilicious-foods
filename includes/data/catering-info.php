<?php
/**
 * Catering booking page — event types, channels, copy helpers.
 */

/**
 * @return list<array{value: string, label: string, hint: string}>
 */
function get_catering_event_types(): array
{
    return [
        ['value' => 'wedding', 'label' => 'Wedding', 'hint' => 'Your big day'],
        ['value' => 'birthday', 'label' => 'Birthday', 'hint' => 'An event for someone special'],
        ['value' => 'corporate', 'label' => 'Work event', 'hint' => 'Office lunch or meeting'],
        ['value' => 'private', 'label' => 'Home event', 'hint' => 'Friends and family at home'],
        ['value' => 'other', 'label' => 'Other', 'hint' => 'Tell us more in your notes'],
    ];
}

/**
 * @return list<array{value: string, label: string}>
 */
function get_catering_channels(): array
{
    return [
        ['value' => 'whatsapp', 'label' => 'WhatsApp chat'],
        ['value' => 'email', 'label' => 'Email'],
        ['value' => 'both', 'label' => 'Email and WhatsApp'],
        ['value' => 'call', 'label' => 'Phone call'],
    ];
}

function get_catering_event_type_label(string $value): string
{
    foreach (get_catering_event_types() as $type) {
        if (($type['value'] ?? '') === $value) {
            return (string) ($type['label'] ?? $value);
        }
    }

    return $value;
}

function get_catering_channel_label(string $value): string
{
    foreach (get_catering_channels() as $channel) {
        if (($channel['value'] ?? '') === $value) {
            return (string) ($channel['label'] ?? $value);
        }
    }

    return $value;
}
