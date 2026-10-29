<?php declare(strict_types=1);

namespace Nova\Termine\Service;

/**
 * Bereitet einen Termin für die Anzeige auf (Bereich, Datumstext, schema.org-Daten).
 * Ohne Shopware-Abhängigkeit, damit die Regeln ohne Kernel testbar sind.
 */
final class TerminPresenter
{
    public const AREA_BOOTH = 'booth';
    public const AREA_SCENE = 'scene';
    public const AREA_ALL = 'all';

    /**
     * Bereich in der Liste: „Mit Stand“ → „Hier sind wir dabei“, alles andere (ohne Stand, noch offen)
     * → „Weitere Treffen in der Szene“.
     */
    public static function area(string $participation): string
    {
        return $participation === 'booth' ? self::AREA_BOOTH : self::AREA_SCENE;
    }

    public static function matchesArea(string $participation, string $area): bool
    {
        return !\in_array($area, [self::AREA_BOOTH, self::AREA_SCENE], true) || self::area($participation) === $area;
    }

    /**
     * Ist der Termin im Verkaufskanal sichtbar? Keine Zuordnung = alle Verkaufskanäle.
     *
     * @param list<string> $salesChannelIds
     */
    public static function visibleInSalesChannel(array $salesChannelIds, string $salesChannelId): bool
    {
        return $salesChannelIds === [] || \in_array($salesChannelId, $salesChannelIds, true);
    }

    /**
     * schema.org „Event“ als Array (wird mit {@see self::jsonLd()} sicher kodiert).
     *
     * @return array<string, mixed>
     */
    public static function schema(string $title, \DateTimeInterface $start, ?\DateTimeInterface $end, string $location, string $country, string $url): array
    {
        $address = ['@type' => 'PostalAddress', 'addressLocality' => $location];
        if ($country !== '') {
            $address['addressCountry'] = $country;
        }

        $event = [
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            'name' => $title,
            'startDate' => $start->format('Y-m-d'),
            'endDate' => DateRange::effectiveEnd($start, $end)->format('Y-m-d'),
            'eventStatus' => 'https://schema.org/EventScheduled',
            'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
            'location' => [
                '@type' => 'Place',
                'name' => $location,
                'address' => $address,
            ],
        ];
        if ($url !== '' && LinkValidator::isValid($url)) {
            $event['url'] = $url;
        }

        return $event;
    }

    /**
     * JSON für <script type="application/ld+json">: <, >, &, ' und " werden als \u00XX kodiert,
     * damit Nutzertext das Script-Element nicht verlassen kann.
     *
     * @param array<string, mixed> $data
     */
    public static function jsonLd(array $data): string
    {
        $json = json_encode(
            $data,
            \JSON_HEX_TAG | \JSON_HEX_AMP | \JSON_HEX_APOS | \JSON_HEX_QUOT | \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES | \JSON_INVALID_UTF8_SUBSTITUTE
        );

        return $json === false ? '{}' : $json;
    }
}
