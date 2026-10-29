<?php declare(strict_types=1);

namespace Nova\Termine\Service;

/**
 * Datumslogik ohne Shopware-Abhängigkeit (deshalb ohne Kernel testbar).
 *
 * Termine haben nur ein Datum (keine Uhrzeit). Verglichen wird immer das reine Kalenderdatum
 * („Y-m-d“), die Uhrzeit eines DateTime-Objekts spielt keine Rolle.
 */
final class DateRange
{
    private const DASH = "\u{2013}"; // Halbgeviertstrich „–“

    /**
     * Deutsche Kurzschreibweise eines Zeitraums:
     * - ein Tag:            17.07.2027
     * - gleicher Monat:     17.–18.07.2027
     * - gleiches Jahr:      30.06.–02.07.2027
     * - Jahreswechsel:      30.12.2027–02.01.2028
     * Ein Ende vor dem Beginn wird wie „kein Ende“ behandelt.
     */
    public static function format(\DateTimeInterface $start, ?\DateTimeInterface $end = null): string
    {
        $end = self::effectiveEnd($start, $end);

        if ($start->format('Y-m-d') === $end->format('Y-m-d')) {
            return $start->format('d.m.Y');
        }
        if ($start->format('Y-m') === $end->format('Y-m')) {
            return $start->format('d.') . self::DASH . $end->format('d.m.Y');
        }
        if ($start->format('Y') === $end->format('Y')) {
            return $start->format('d.m.') . self::DASH . $end->format('d.m.Y');
        }

        return $start->format('d.m.Y') . self::DASH . $end->format('d.m.Y');
    }

    /**
     * Kommend bzw. laufend: Das (effektive) Ende liegt heute oder später.
     * Ein Termin verschwindet also erst am Tag nach seinem letzten Tag.
     */
    public static function isUpcoming(\DateTimeInterface $start, ?\DateTimeInterface $end, \DateTimeInterface $today): bool
    {
        return self::effectiveEnd($start, $end)->format('Y-m-d') >= $today->format('Y-m-d');
    }

    /**
     * Läuft der Termin heute (Beginn <= heute <= Ende)?
     */
    public static function isRunning(\DateTimeInterface $start, ?\DateTimeInterface $end, \DateTimeInterface $today): bool
    {
        $day = $today->format('Y-m-d');

        return $start->format('Y-m-d') <= $day && self::effectiveEnd($start, $end)->format('Y-m-d') >= $day;
    }

    /**
     * Ende = Beginn, wenn kein Ende gesetzt ist oder das Ende vor dem Beginn liegt.
     */
    public static function effectiveEnd(\DateTimeInterface $start, ?\DateTimeInterface $end): \DateTimeInterface
    {
        if ($end === null || $end->format('Y-m-d') < $start->format('Y-m-d')) {
            return $start;
        }

        return $end;
    }

    /**
     * Heutiges Datum in der Zeitzone des Shops (ungültige Angabe → Europe/Berlin).
     */
    public static function today(string $timezone, ?\DateTimeImmutable $now = null): \DateTimeImmutable
    {
        try {
            $zone = new \DateTimeZone($timezone !== '' ? $timezone : 'Europe/Berlin');
        } catch (\Throwable) {
            $zone = new \DateTimeZone('Europe/Berlin');
        }

        $now ??= new \DateTimeImmutable('now');

        return $now->setTimezone($zone)->setTime(0, 0);
    }

    /**
     * Liest ein Datum „JJJJ-MM-TT“ (HTML-Datumsfeld) streng ein. Ungültig → null.
     */
    public static function parse(string $value): ?\DateTimeImmutable
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) !== 1) {
            return null;
        }

        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value, new \DateTimeZone('UTC'));
        if ($date === false || $date->format('Y-m-d') !== $value) {
            return null;
        }

        return $date;
    }
}
