<?php declare(strict_types=1);

namespace Nova\Termine\Service;

/**
 * Prüft die Eingaben des Formulars „Treffen melden“ (ohne Shopware-Abhängigkeit, testbar).
 *
 * Ergebnis: bereinigte Daten, „spam“ (Honeypot ausgefüllt) oder der Snippet-Schlüssel des Fehlers.
 */
final class SuggestionInput
{
    public const HONEYPOT = 'novaTermineWebsite';

    /** Höchstlängen je Feld (Zeichen) */
    public const MAX = [
        'title' => 150,
        'location' => 150,
        'country' => 80,
        'url' => LinkValidator::MAX_LENGTH,
        'email' => 200,
        'message' => 1000,
    ];

    /**
     * @param array<mixed> $input
     *
     * @return array{title: string, startDate: \DateTimeImmutable, endDate: ?\DateTimeImmutable, location: string, country: string, url: string, email: string, message: string}|string
     */
    public static function validate(array $input, bool $privacyRequired, \DateTimeImmutable $today): array|string
    {
        $text = static function (string $key, int $max) use ($input): string {
            $value = $input[$key] ?? null;
            if (!\is_string($value)) {
                return '';
            }

            return mb_substr(trim(str_replace("\0", '', $value)), 0, $max);
        };

        // Verstecktes Feld: Menschen sehen es nicht, Bots füllen es aus
        if ($text(self::HONEYPOT, 200) !== '') {
            return 'spam';
        }

        // Zu lange Eingaben ablehnen statt still zu kürzen
        foreach (self::MAX as $key => $max) {
            $value = $input[$key] ?? null;
            if (\is_string($value) && mb_strlen(trim($value)) > $max) {
                return 'novaTermine.report.errorTooLong';
            }
        }

        $data = [
            'title' => self::singleLine($text('title', self::MAX['title'])),
            'location' => self::singleLine($text('location', self::MAX['location'])),
            'country' => self::singleLine($text('country', self::MAX['country'])),
            'url' => $text('url', self::MAX['url']),
            'email' => self::singleLine($text('email', self::MAX['email'])),
            'message' => $text('message', self::MAX['message']),
        ];

        if ($data['title'] === '' || $data['location'] === '') {
            return 'novaTermine.report.errorRequired';
        }

        $start = DateRange::parse($text('startDate', 10));
        if ($start === null) {
            return 'novaTermine.report.errorDate';
        }
        $endText = $text('endDate', 10);
        $end = $endText === '' ? null : DateRange::parse($endText);
        if ($endText !== '' && $end === null) {
            return 'novaTermine.report.errorDate';
        }
        if ($end !== null && $end < $start) {
            return 'novaTermine.report.errorEndBeforeStart';
        }
        if (!DateRange::isUpcoming($start, $end, $today)) {
            return 'novaTermine.report.errorPast';
        }
        // Plausibilität: nicht mehr als drei Jahre im Voraus, höchstens 31 Tage Dauer
        if ($start > $today->modify('+3 years')) {
            return 'novaTermine.report.errorDate';
        }
        if ($end !== null && $end > $start->modify('+31 days')) {
            return 'novaTermine.report.errorDate';
        }

        if ($data['url'] !== '' && !LinkValidator::isValid($data['url'])) {
            return 'novaTermine.report.errorUrl';
        }
        if ($data['email'] !== '' && filter_var($data['email'], \FILTER_VALIDATE_EMAIL) === false) {
            return 'novaTermine.report.errorEmail';
        }
        if ($privacyRequired && empty($input['acceptedDataProtection'])) {
            return 'novaTermine.report.errorPrivacy';
        }

        return $data + ['startDate' => $start, 'endDate' => $end];
    }

    private static function singleLine(string $value): string
    {
        return trim((string) preg_replace('/[\r\n\t]+/', ' ', $value));
    }
}
