<?php declare(strict_types=1);

namespace Nova\Termine\Service;

/**
 * Links von Terminen: nur http:// und https:// mit Host, keine Leer- oder Steuerzeichen.
 */
final class LinkValidator
{
    public const MAX_LENGTH = 500;

    public static function isValid(string $url): bool
    {
        if ($url === '' || mb_strlen($url) > self::MAX_LENGTH) {
            return false;
        }
        if (preg_match('/[\s\x00-\x1F\x7F]/u', $url) === 1) {
            return false;
        }

        $parts = parse_url($url);
        if (!\is_array($parts)) {
            return false;
        }

        $scheme = strtolower($parts['scheme'] ?? '');

        return \in_array($scheme, ['http', 'https'], true)
            && ($parts['host'] ?? '') !== ''
            && preg_match('#^https?://#i', $url) === 1;
    }
}
