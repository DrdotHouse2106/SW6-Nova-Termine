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
     *
     * Zeilen, die im Kommentar selbst mit „ABHOLUNG:“ beginnen, werden entfernt – sowohl eine
     * frühere eigene Zeile (Neuberechnung, ggf. mit anderem Termin) als auch vom Kunden
     * eingetippte, die einen anderen Termin vortäuschen könnten. Maßgeblich bleibt die eine
     * vom Shop gesetzte erste Zeile (und die Zusatzfelder der Bestellung).
     */
    public static function prefixComment(string $summary, ?string $comment): string
    {
        $line = 'ABHOLUNG: ' . $summary;
        $comment = self::stripPickupLines((string) $comment);

        return $comment === '' ? $line : $line . "\n\n" . $comment;
    }

    /**
     * Entfernt alle Zeilen, die mit „ABHOLUNG:“ beginnen (Groß-/Kleinschreibung, führende
     * Leerzeichen/unsichtbare Zeichen und Doppelpunkt-Varianten egal).
     */
    public static function stripPickupLines(string $comment): string
    {
        $comment = str_replace(["\r\n", "\r"], "\n", $comment);
        $kept = array_filter(
            explode("\n", $comment),
            static fn (string $row): bool => !preg_match('/^[\s\x{00A0}\x{200B}-\x{200D}\x{2060}\x{FEFF}>*_#-]*ABHOLUNG[\s\x{00A0}]*[:：﹕꞉]/iu', $row),
        );

        return trim((string) preg_replace("/\n{3,}/", "\n\n", implode("\n", $kept)));
    }
}
