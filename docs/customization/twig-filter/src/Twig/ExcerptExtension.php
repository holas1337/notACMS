<?php

declare(strict_types=1);

namespace NotACms\Local\Twig;

use Twig\Attribute\AsTwigFilter;

/**
 * Twig filter for generating text excerpts from HTML content.
 */
final readonly class ExcerptExtension
{
    /**
     * Generate a plain text excerpt from HTML content.
     *
     * @param string $html   The HTML content to excerpt
     * @param int    $length Maximum length in characters (default: 200)
     *
     * @return string Plain text excerpt, truncated if necessary
     */
    #[AsTwigFilter('excerpt')]
    public function excerpt(string $html, int $length = 200): string
    {
        // Strip HTML tags
        $text = strip_tags($html);

        // Normalize whitespace (multiple spaces/newlines become single space)
        $text = (string) preg_replace('/\s+/', ' ', trim($text));

        // Truncate with ellipsis if longer than specified length
        if (mb_strlen($text) > $length) {
            return mb_substr($text, 0, $length) . '…';
        }

        return $text;
    }
}
