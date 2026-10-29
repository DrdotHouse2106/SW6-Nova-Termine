<?php declare(strict_types=1);

namespace Nova\Termine\Controller;

use Nova\Termine\Cms\TerminListCmsElementResolver;
use Nova\Termine\Core\Content\Termin\TerminCollection;
use Nova\Termine\Core\Content\Termin\TerminDefinition;
use Nova\Termine\Service\DateRange;
use Nova\Termine\Service\ReportLimiter;
use Nova\Termine\Service\SuggestionInput;
use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotCollection;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Mail\Service\AbstractMailService;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Storefront\Framework\Routing\StorefrontRouteScope;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * „Treffen melden“: legt einen Termin mit Status „Vorschlag“ an (nie automatisch sichtbar) und
 * informiert den Shop per E-Mail. Die IP-Adresse wird nicht gespeichert.
 *
 * Schutz: Shopware-Captchas (Route-Attribut _captcha wie beim Kontaktformular), Honeypot,
 * Herkunftsprüfung (Origin/Referer = eigener Shop, ergänzt das SameSite-Session-Cookie – Shopware 6.7
 * kennt keine CSRF-Tokens mehr), RateLimiter je IP und je Verkaufskanal, Längenbegrenzung aller Felder.
 *
 * Antwortformat wie beim Kontaktformular (Liste aus type/alert), damit Shopwares FormCmsHandler
 * und die Captcha-Plugins der Storefront ohne eigenes JavaScript funktionieren.
 */
#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StorefrontRouteScope::ID]])]
class ReportController extends StorefrontController
{
    /**
     * @param EntityRepository<TerminCollection>  $terminRepository
     * @param EntityRepository<CmsSlotCollection> $cmsSlotRepository
     */
    public function __construct(
        private readonly EntityRepository $terminRepository,
        private readonly EntityRepository $cmsSlotRepository,
        private readonly AbstractMailService $mailService,
        private readonly SystemConfigService $config,
        private readonly ReportLimiter $limiter,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route(
        path: '/nova-termine/report',
        name: 'frontend.nova-termine.report',
        defaults: ['XmlHttpRequest' => true, PlatformRequest::ATTRIBUTE_CAPTCHA => true],
        methods: [Request::METHOD_POST]
    )]
    public function report(Request $request, SalesChannelContext $context): JsonResponse
    {
        $salesChannelId = $context->getSalesChannelId();

        if (!$this->isSameOrigin($request)) {
            return $this->alert('danger', 'novaTermine.report.errorGeneral');
        }
        if (!$this->reportEnabled((string) $request->request->get('slotId', ''), $context)) {
            return $this->alert('danger', 'novaTermine.report.errorGeneral');
        }

        $today = DateRange::today($this->config->getString('NovaTermine.config.timezone', $salesChannelId));
        $data = SuggestionInput::validate(
            $request->request->all(),
            $this->config->getBool('core.loginRegistration.requireDataProtectionCheckbox', $salesChannelId),
            $today
        );
        if ($data === 'spam') {
            // Honeypot ausgefüllt: so tun, als wäre alles gut
            return $this->alert('success', 'novaTermine.report.success');
        }
        if (\is_string($data)) {
            return $this->alert('danger', $data);
        }

        if (!$this->limiter->consume((string) $request->getClientIp(), $salesChannelId)) {
            return $this->alert('info', 'novaTermine.report.errorTooMany');
        }

        $note = $this->trans('novaTermine.report.noteIntro');
        if ($data['message'] !== '') {
            $note .= "\n\n" . $data['message'];
        }

        $translation = ['title' => $data['title']];
        $translations = [Defaults::LANGUAGE_SYSTEM => $translation];
        $translations[$context->getLanguageId()] = $translation;

        $id = Uuid::randomHex();
        try {
            $this->terminRepository->create([[
                'id' => $id,
                'status' => TerminDefinition::STATUS_SUGGESTION,
                'participation' => TerminDefinition::PARTICIPATION_OPEN,
                'location' => $data['location'],
                'country' => $data['country'] !== '' ? $data['country'] : null,
                'startDate' => $data['startDate']->format('Y-m-d'),
                'endDate' => $data['endDate']?->format('Y-m-d'),
                'url' => $data['url'] !== '' ? $data['url'] : null,
                'reporterEmail' => $data['email'] !== '' ? $data['email'] : null,
                'internalNote' => $note,
                'translations' => $translations,
                'salesChannels' => [['id' => $salesChannelId]],
            ]], Context::createCLIContext());
        } catch (\Throwable $e) {
            $this->logger->error('Nova Termine: Vorschlag konnte nicht gespeichert werden', ['exception' => $e]);

            return $this->alert('danger', 'novaTermine.report.errorGeneral');
        }

        $this->sendMail($data, $context);

        return $this->alert('success', 'novaTermine.report.success');
    }

    /**
     * @param array{title: string, startDate: \DateTimeImmutable, endDate: ?\DateTimeImmutable, location: string, country: string, url: string, email: string, message: string} $data
     */
    private function sendMail(array $data, SalesChannelContext $context): void
    {
        $salesChannelId = $context->getSalesChannelId();
        $recipient = trim($this->config->getString('NovaTermine.config.notificationEmail', $salesChannelId))
            ?: trim($this->config->getString('core.basicInformation.email', $salesChannelId));
        if (filter_var($recipient, \FILTER_VALIDATE_EMAIL) === false) {
            $this->logger->warning('Nova Termine: Kein gültiger Empfänger für die Info-Mail (Plugin-Konfiguration bzw. Stammdaten prüfen)');

            return;
        }

        // Nutzereingaben nur als Template-Daten übergeben und im Template escapen
        $values = [
            'title' => $data['title'],
            'date' => DateRange::format($data['startDate'], $data['endDate']),
            'location' => $data['location'],
            'country' => $data['country'],
            'url' => $data['url'],
            'email' => $data['email'],
            'message' => $data['message'],
            'shop' => (string) $context->getSalesChannel()->getTranslation('name'),
        ];
        $labels = [
            'title' => 'novaTermine.report.fieldTitle',
            'date' => 'novaTermine.report.mailDate',
            'location' => 'novaTermine.report.fieldLocation',
            'country' => 'novaTermine.report.fieldCountry',
            'url' => 'novaTermine.report.fieldUrl',
            'email' => 'novaTermine.report.fieldEmail',
            'message' => 'novaTermine.report.fieldMessage',
            'shop' => 'novaTermine.report.mailShop',
        ];

        $html = '<p>' . self::label($this->trans('novaTermine.report.mailIntro')) . '</p><table cellpadding="4">';
        $plain = self::plainLabel($this->trans('novaTermine.report.mailIntro')) . "\n\n";
        foreach ($labels as $key => $snippet) {
            if ($values[$key] === '') {
                continue;
            }
            $html .= '<tr><td valign="top"><strong>' . self::label($this->trans($snippet)) . '</strong></td><td>{{ novaTermin.' . $key . '|e|nl2br }}</td></tr>';
            $plain .= self::plainLabel($this->trans($snippet)) . ': {{ novaTermin.' . $key . ' }}' . "\n";
        }
        $html .= '</table><p>' . self::label($this->trans('novaTermine.report.mailOutro')) . '</p>';
        $plain .= "\n" . self::plainLabel($this->trans('novaTermine.report.mailOutro')) . "\n";

        $mail = [
            'recipients' => [$recipient => null],
            'senderName' => '{{ novaTermin.shop }}',
            'salesChannelId' => $salesChannelId,
            'subject' => self::plainLabel($this->trans('novaTermine.report.mailSubject')) . ' {{ novaTermin.title }}',
            'contentHtml' => $html,
            'contentPlain' => $plain,
        ];
        if ($data['email'] !== '') {
            $mail['replyTo'] = $data['email'];
        }

        try {
            $this->mailService->send($mail, $context->getContext(), ['novaTermin' => $values]);
        } catch (\Throwable $e) {
            // Der Vorschlag ist gespeichert – die Mail ist nur ein Hinweis
            $this->logger->error('Nova Termine: Info-Mail zum Vorschlag konnte nicht gesendet werden', ['exception' => $e]);
        }
    }

    /**
     * Nur zulassen, wenn das Element auf einer Erlebniswelt existiert und „Treffen melden“ dort an ist.
     */
    private function reportEnabled(string $slotId, SalesChannelContext $context): bool
    {
        if (!Uuid::isValid($slotId)) {
            return false;
        }

        $slot = $this->cmsSlotRepository->search(new Criteria([$slotId]), $context->getContext())->first();
        if (!$slot instanceof CmsSlotEntity || $slot->getType() !== TerminListCmsElementResolver::TYPE) {
            return false;
        }

        return $slot->getFieldConfig()->get('showReport')?->getValue() !== false;
    }

    /**
     * Herkunft prüfen: Origin (bzw. ersatzweise Referer) muss auf diesen Shop zeigen.
     */
    private function isSameOrigin(Request $request): bool
    {
        $source = (string) ($request->headers->get('Origin') ?: $request->headers->get('Referer'));
        if ($source === '') {
            return false;
        }

        $host = parse_url($source, \PHP_URL_HOST);

        return \is_string($host) && strcasecmp($host, $request->getHost()) === 0;
    }

    private function alert(string $type, string $snippet): JsonResponse
    {
        $html = $this->renderView('@Storefront/storefront/utilities/alert.html.twig', [
            'type' => $type,
            'content' => $this->trans($snippet),
        ]);

        return new JsonResponse([['type' => $type, 'alert' => $html]]);
    }

    /**
     * Beschriftung aus Textbausteinen für das Mail-Template: HTML und Twig-Klammern entschärfen.
     */
    private static function label(string $text): string
    {
        return str_replace(['{', '}'], ['&#123;', '&#125;'], htmlspecialchars($text));
    }

    private static function plainLabel(string $text): string
    {
        return str_replace(['{', '}', '%'], '', $text);
    }
}
