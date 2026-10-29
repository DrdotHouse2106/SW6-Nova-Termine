<?php declare(strict_types=1);

namespace Nova\Termine\Service;

/**
 * Texte und Regeln der Abholung ohne Shopware-Abhängigkeit (ohne Kernel testbar).
 */
final class PickupText
{
    public const DEFAULT_LEAD_DAYS = 7;

    public static function normalizeLeadDays(mixed $days): int
    {
        if ($days === null || $days === '' || !is_numeric($days)) {
            return self::DEFAULT_LEAD_DAYS;
        }

        return max(0, min(365, (int) $days));
    }

    /**
     * Frühester Beginn eines Abhol-Treffens: heute + Vorlauf.
     */
    public static function earliestStart(\DateTimeImmutable $today, int $leadDays): \DateTimeImmutable
    {
        return $today->modify('+' . max(0, $leadDays) . ' days');
    }

    /**
     * Anzeige in der Auswahl: „Titel – Ort – Datum“
     */
    public static function label(string $title, string $location, string $date): string
    {
        return implode(' – ', array_filter([$title, $location, $date], static fn (string $part) => $part !== ''));
    }

    /**
     * Text für Bestellkommentar und Bestellung: „Titel, Ort, Datum“
     */
    public static function summary(string $title, string $location, string $date): string
    {
        return implode(', ', array_filter([$title, $location, $date], static fn (string $part) => $part !== ''));
    }

    /**
     * Bestellkommentar: „ABHOLUNG: …“ vorne, ein vorhandener Kundenkommentar bleibt dahinter erhalten.
     */
    public static function prefixComment(string $summary, ?string $comment): string
    {
        $line = 'ABHOLUNG: ' . $summary;
        $comment = trim((string) $comment);

        // Bereits vorhanden (z. B. Neuberechnung): nicht doppelt voranstellen
        if (str_starts_with($comment, $line)) {
            return $comment;
        }

        return $comment === '' ? $line : $line . "\n\n" . $comment;
    }
}
