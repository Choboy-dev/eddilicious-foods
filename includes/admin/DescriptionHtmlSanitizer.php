<?php
/**
 * Sanitize and convert meal description HTML for admin + storefront.
 */
final class DescriptionHtmlSanitizer
{
    private const ALLOWED_TAGS = '<p><br><strong><b><em><i><ul><ol><li><h1><h2><h3><h4><h5>';

    public static function sanitize(string $html): string
    {
        $html = trim($html);
        if ($html === '') {
            return '';
        }

        $html = preg_replace('/\r\n?/', "\n", $html) ?? $html;
        $html = strip_tags($html, self::ALLOWED_TAGS);
        $html = preg_replace('/<(br)\b[^>]*>/i', '<$1>', $html) ?? $html;
        $html = preg_replace('/<(\/?)(p|strong|b|em|i|ul|ol|li|h1|h2|h3|h4|h5)\b[^>]*>/i', '<$1$2>', $html) ?? $html;
        $html = preg_replace('/\n{3,}/', "\n\n", $html) ?? $html;

        // Remove empty paragraphs and list items.
        $html = preg_replace('/<p>\s*<\/p>/i', '', $html) ?? $html;
        $html = preg_replace('/<li>\s*<\/li>/i', '', $html) ?? $html;

        return trim($html);
    }

    public static function hasContent(string $html): bool
    {
        return trim(strip_tags($html)) !== '';
    }

    /**
     * @param array{intro?: string, paragraphs?: list<string>, bullets?: list<string>, html?: string} $description
     */
    public static function fromLegacy(array $description): string
    {
        if (!empty($description['html']) && is_string($description['html'])) {
            return self::sanitize($description['html']);
        }

        $parts = [];

        $intro = trim((string) ($description['intro'] ?? ''));
        if ($intro !== '') {
            $parts[] = '<p>' . self::escapeText($intro) . '</p>';
        }

        foreach ((array) ($description['paragraphs'] ?? []) as $paragraph) {
            $paragraph = trim((string) $paragraph);
            if ($paragraph === '') {
                continue;
            }

            $parts[] = '<p>' . nl2br(self::escapeText($paragraph), false) . '</p>';
        }

        $bullets = array_values(array_filter(
            array_map(static fn($line): string => trim((string) $line), (array) ($description['bullets'] ?? [])),
            static fn(string $line): bool => $line !== ''
        ));

        if ($bullets !== []) {
            $items = '';
            foreach ($bullets as $bullet) {
                $items .= '<li>' . self::escapeText($bullet) . '</li>';
            }
            $parts[] = '<ul>' . $items . '</ul>';
        }

        return self::sanitize(implode('', $parts));
    }

    /**
     * @param array<string, mixed> $description
     * @return array{html: string}
     */
    public static function normalize(array $description): array
    {
        $html = self::fromLegacy($description);

        return ['html' => $html];
    }

    private static function escapeText(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
