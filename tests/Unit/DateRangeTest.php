<?php declare(strict_types=1);

namespace Nova\Termine\Tests\Unit;

use Nova\Termine\Service\DateRange;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DateRangeTest extends TestCase
{
    /**
     * @return iterable<string, array{string, ?string, string}>
     */
    public static function formatCases(): iterable
    {
        yield 'ein Tag' => ['2027-07-17', null, '17.07.2027'];
        yield 'Ende = Beginn' => ['2027-07-17', '2027-07-17', '17.07.2027'];
        yield 'gleicher Monat' => ['2027-07-17', '2027-07-18', "17.\u{2013}18.07.2027"];
        yield 'Monatswechsel' => ['2027-06-30', '2027-07-02', "30.06.\u{2013}02.07.2027"];
        yield 'Jahreswechsel' => ['2027-12-30', '2028-01-02', "30.12.2027\u{2013}02.01.2028"];
        yield 'Ende vor Beginn = ein Tag' => ['2027-07-17', '2027-07-10', '17.07.2027'];
    }

    #[DataProvider('formatCases')]
    public function testFormat(string $start, ?string $end, string $expected): void
    {
        self::assertSame($expected, DateRange::format(new \DateTimeImmutable($start), $end !== null ? new \DateTimeImmutable($end) : null));
    }

    public function testUpcoming(): void
    {
        $today = new \DateTimeImmutable('2027-07-18');

        self::assertTrue(DateRange::isUpcoming(new \DateTimeImmutable('2027-07-20'), null, $today), 'zukünftig');
        self::assertTrue(DateRange::isUpcoming(new \DateTimeImmutable('2027-07-18'), null, $today), 'heute');
        self::assertTrue(DateRange::isUpcoming(new \DateTimeImmutable('2027-07-17'), new \DateTimeImmutable('2027-07-18'), $today), 'letzter Tag heute');
        self::assertFalse(DateRange::isUpcoming(new \DateTimeImmutable('2027-07-17'), null, $today), 'gestern');
        self::assertFalse(DateRange::isUpcoming(new \DateTimeImmutable('2027-07-10'), new \DateTimeImmutable('2027-07-17'), $today), 'gestern zu Ende');
        self::assertFalse(DateRange::isUpcoming(new \DateTimeImmutable('2027-07-17'), new \DateTimeImmutable('2027-07-01'), $today), 'Ende vor Beginn');
    }

    public function testUpcomingIgnoresTimeOfDay(): void
    {
        $today = new \DateTimeImmutable('2027-07-18 00:00:00');

        self::assertTrue(DateRange::isUpcoming(new \DateTimeImmutable('2027-07-18 00:00:00'), null, new \DateTimeImmutable('2027-07-18 23:59:59')));
        self::assertFalse(DateRange::isUpcoming(new \DateTimeImmutable('2027-07-17 23:59:59'), null, $today));
    }

    public function testRunning(): void
    {
        $today = new \DateTimeImmutable('2027-07-18');

        self::assertTrue(DateRange::isRunning(new \DateTimeImmutable('2027-07-17'), new \DateTimeImmutable('2027-07-19'), $today));
        self::assertFalse(DateRange::isRunning(new \DateTimeImmutable('2027-07-19'), new \DateTimeImmutable('2027-07-20'), $today));
    }

    public function testTodayUsesShopTimezone(): void
    {
        // 23:30 UTC am 17.07. ist in Berlin (Sommerzeit) schon der 18.07.
        $now = new \DateTimeImmutable('2027-07-17 23:30:00', new \DateTimeZone('UTC'));

        self::assertSame('2027-07-18', DateRange::today('Europe/Berlin', $now)->format('Y-m-d'));
        self::assertSame('2027-07-17', DateRange::today('UTC', $now)->format('Y-m-d'));
        self::assertSame('2027-07-18', DateRange::today('Keine/Zone', $now)->format('Y-m-d'), 'ungültig → Europe/Berlin');
    }

    public function testParse(): void
    {
        self::assertSame('2027-02-28', DateRange::parse('2027-02-28')?->format('Y-m-d'));
        self::assertNull(DateRange::parse('2027-02-30'));
        self::assertNull(DateRange::parse('28.02.2027'));
        self::assertNull(DateRange::parse(''));
    }
}
