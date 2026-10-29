<?php declare(strict_types=1);

namespace Nova\Termine\Tests\Unit;

use Nova\Termine\Service\DateRange;
use Nova\Termine\Service\PickupText;
use PHPUnit\Framework\TestCase;

class PickupTextTest extends TestCase
{
    public function testLabelAndSummary(): void
    {
        self::assertSame("Oldtimertreffen Musterstadt \u{2013} Musterstadt \u{2013} 17.\u{2013}18.07.2027", PickupText::label('Oldtimertreffen Musterstadt', 'Musterstadt', "17.\u{2013}18.07.2027"));
        self::assertSame('Teilemarkt, Beispielhausen, 05.09.2027', PickupText::summary('Teilemarkt', 'Beispielhausen', '05.09.2027'));
        self::assertSame('Teilemarkt, 05.09.2027', PickupText::summary('Teilemarkt', '', '05.09.2027'));
    }

    public function testPrefixComment(): void
    {
        self::assertSame('ABHOLUNG: Treffen, Ort, 01.01.2028', PickupText::prefixComment('Treffen, Ort, 01.01.2028', null));
        self::assertSame("ABHOLUNG: Treffen, Ort, 01.01.2028\n\nBitte Teil 4711 einpacken", PickupText::prefixComment('Treffen, Ort, 01.01.2028', '  Bitte Teil 4711 einpacken '));
        $once = PickupText::prefixComment('A, B, C', 'Hallo');
        self::assertSame($once, PickupText::prefixComment('A, B, C', $once), 'nicht doppelt voranstellen');
    }

    public function testLeadDays(): void
    {
        self::assertSame(7, PickupText::normalizeLeadDays(null));
        self::assertSame(7, PickupText::normalizeLeadDays(''));
        self::assertSame(0, PickupText::normalizeLeadDays(-3));
        self::assertSame(14, PickupText::normalizeLeadDays('14'));
        self::assertSame(365, PickupText::normalizeLeadDays(1000));
    }

    public function testDeadline(): void
    {
        $today = new \DateTimeImmutable('2027-07-10');
        $earliest = PickupText::earliestStart($today, 7);

        self::assertSame('2027-07-17', $earliest->format('Y-m-d'));
        // Beginn genau am Stichtag ist erlaubt, einen Tag früher nicht
        self::assertTrue((new \DateTimeImmutable('2027-07-17'))->format('Y-m-d') >= $earliest->format('Y-m-d'));
        self::assertFalse((new \DateTimeImmutable('2027-07-16'))->format('Y-m-d') >= $earliest->format('Y-m-d'));
        self::assertTrue(DateRange::isUpcoming(new \DateTimeImmutable('2027-07-17'), null, $today));
    }
}
