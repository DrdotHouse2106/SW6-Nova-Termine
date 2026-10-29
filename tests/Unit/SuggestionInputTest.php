<?php declare(strict_types=1);

namespace Nova\Termine\Tests\Unit;

use Nova\Termine\Service\LinkValidator;
use Nova\Termine\Service\SuggestionInput;
use Nova\Termine\Service\TerminPresenter;
use PHPUnit\Framework\TestCase;

class SuggestionInputTest extends TestCase
{
    private \DateTimeImmutable $today;

    protected function setUp(): void
    {
        $this->today = new \DateTimeImmutable('2027-05-01');
    }

    /**
     * @param array<string, mixed> $override
     *
     * @return array<string, mixed>
     */
    private function input(array $override = []): array
    {
        return $override + [
            'title' => 'Oldtimertreffen Musterstadt',
            'startDate' => '2027-07-17',
            'endDate' => '2027-07-18',
            'location' => 'Musterstadt',
            'country' => 'Deutschland',
            'url' => 'https://example.org/treffen',
            'email' => 'max@example.org',
            'message' => "Hallo,\nbitte aufnehmen.",
        ];
    }

    public function testValid(): void
    {
        $data = SuggestionInput::validate($this->input(), false, $this->today);

        self::assertIsArray($data);
        self::assertSame('Oldtimertreffen Musterstadt', $data['title']);
        self::assertSame('2027-07-18', $data['endDate']?->format('Y-m-d'));
        self::assertSame("Hallo,\nbitte aufnehmen.", $data['message']);
    }

    public function testHoneypot(): void
    {
        self::assertSame('spam', SuggestionInput::validate($this->input([SuggestionInput::HONEYPOT => 'http://spam']), false, $this->today));
    }

    public function testErrors(): void
    {
        self::assertSame('novaTermine.report.errorRequired', SuggestionInput::validate($this->input(['title' => '  ']), false, $this->today));
        self::assertSame('novaTermine.report.errorDate', SuggestionInput::validate($this->input(['startDate' => '17.07.2027']), false, $this->today));
        self::assertSame('novaTermine.report.errorEndBeforeStart', SuggestionInput::validate($this->input(['endDate' => '2027-07-01']), false, $this->today));
        self::assertSame('novaTermine.report.errorPast', SuggestionInput::validate($this->input(['startDate' => '2027-04-01', 'endDate' => '']), false, $this->today));
        self::assertSame('novaTermine.report.errorUrl', SuggestionInput::validate($this->input(['url' => 'javascript:alert(1)']), false, $this->today));
        self::assertSame('novaTermine.report.errorEmail', SuggestionInput::validate($this->input(['email' => 'kein-mail']), false, $this->today));
        self::assertSame('novaTermine.report.errorTooLong', SuggestionInput::validate($this->input(['message' => str_repeat('x', 1001)]), false, $this->today));
        self::assertSame('novaTermine.report.errorPrivacy', SuggestionInput::validate($this->input(), true, $this->today));
        self::assertIsArray(SuggestionInput::validate($this->input(['acceptedDataProtection' => '1']), true, $this->today));
    }

    public function testSingleLineFields(): void
    {
        $data = SuggestionInput::validate($this->input(['title' => "Treffen\r\nBcc: x@example.org"]), false, $this->today);

        self::assertIsArray($data);
        self::assertSame('Treffen Bcc: x@example.org', $data['title']);
    }

    public function testLinks(): void
    {
        self::assertTrue(LinkValidator::isValid('https://example.org/a?b=c'));
        self::assertTrue(LinkValidator::isValid('HTTP://example.org'));
        self::assertFalse(LinkValidator::isValid('ftp://example.org'));
        self::assertFalse(LinkValidator::isValid('javascript:alert(1)'));
        self::assertFalse(LinkValidator::isValid('//example.org'));
        self::assertFalse(LinkValidator::isValid('https://exa mple.org'));
        self::assertFalse(LinkValidator::isValid('https:///pfad'));
    }

    public function testAreaAndChannels(): void
    {
        self::assertSame(TerminPresenter::AREA_BOOTH, TerminPresenter::area('booth'));
        self::assertSame(TerminPresenter::AREA_SCENE, TerminPresenter::area('visitor'));
        self::assertSame(TerminPresenter::AREA_SCENE, TerminPresenter::area('open'), 'noch offen = wie ohne Stand');
        self::assertTrue(TerminPresenter::matchesArea('open', 'all'));
        self::assertFalse(TerminPresenter::matchesArea('open', 'booth'));
        self::assertTrue(TerminPresenter::visibleInSalesChannel([], 'abc'), 'keine Zuordnung = alle');
        self::assertTrue(TerminPresenter::visibleInSalesChannel(['abc'], 'abc'));
        self::assertFalse(TerminPresenter::visibleInSalesChannel(['def'], 'abc'));
    }

    public function testJsonLdIsSafe(): void
    {
        $json = TerminPresenter::jsonLd(TerminPresenter::schema(
            '</script><script>alert("x")</script>',
            new \DateTimeImmutable('2027-07-17'),
            null,
            "Muster & Co's Hof",
            'DE',
            'javascript:alert(1)'
        ));

        self::assertStringNotContainsString('<', $json);
        self::assertStringNotContainsString('>', $json);
        self::assertStringNotContainsString('&', $json);
        self::assertStringNotContainsString("'", $json);
        $decoded = json_decode($json, true);
        self::assertIsArray($decoded);
        self::assertSame('2027-07-17', $decoded['endDate']);
        self::assertArrayNotHasKey('url', $decoded, 'unsichere Links werden weggelassen');
        self::assertSame('Place', $decoded['location']['@type']);
    }
}
