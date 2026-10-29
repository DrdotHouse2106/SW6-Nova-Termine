<?php declare(strict_types=1);

namespace Nova\Termine\Checkout;

use Nova\Termine\Service\PickupService;
use Nova\Termine\Service\PickupText;
use Shopware\Core\Checkout\Cart\Order\CartConvertedEvent;
use Shopware\Core\Framework\Api\Context\AdminSalesChannelApiSource;
use Shopware\Core\Framework\Api\Context\SalesChannelApiSource;
use Shopware\Core\Framework\Validation\BuildValidationEvent;
use Shopware\Core\Framework\Validation\Exception\ConstraintViolationException;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * Bestellabschluss mit „Abholung auf Treffen/Messe“:
 *
 * 1. BuildValidationEvent (Formular „Zahlungspflichtig bestellen“): Termin gewählt und noch gültig?
 *    Sonst bleibt der Kunde mit Fehlermeldung auf der Bestätigungsseite.
 * 2. CartConvertedEvent (unmittelbar vor dem Anlegen der Bestellung, auch über die Store-API):
 *    erneute Prüfung, dann Termin als Schnappschuss in order.customFields und „ABHOLUNG: …“ vorne in
 *    den Bestellkommentar (für Warenwirtschaften, die eigene Felder nicht übernehmen).
 */
class PickupOrderSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly PickupService $pickup,
        private readonly RequestStack $requestStack,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'framework.validation.order.create' => 'onBuildValidation',
            CartConvertedEvent::class => 'onCartConverted',
        ];
    }

    public function onBuildValidation(BuildValidationEvent $event): void
    {
        $request = $this->requestStack->getMainRequest();
        $context = $request?->attributes->get(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_CONTEXT_OBJECT);
        if (!$context instanceof SalesChannelContext || !$this->pickup->isActive($context)) {
            return;
        }

        $ids = array_map(static fn ($termin) => $termin->getId(), $this->pickup->getAvailable($context));

        $event->getDefinition()->add(
            PickupService::FIELD,
            new NotBlank(message: 'Bitte wählen Sie das Treffen für die Abholung.'),
            new Choice(choices: $ids, message: 'Das gewählte Treffen ist für die Abholung nicht mehr verfügbar.')
        );
    }

    public function onCartConverted(CartConvertedEvent $event): void
    {
        $context = $event->getSalesChannelContext();
        if (!$this->pickup->isActive($context)) {
            return;
        }

        $terminId = $this->selectedId($context);
        $termin = $this->pickup->find($context, $terminId);
        if ($termin === null) {
            // Bestellungen aus der Administration (Anlegen, Neuberechnen) nicht blockieren
            $source = $context->getContext()->getSource();
            if (!$source instanceof SalesChannelApiSource || $source instanceof AdminSalesChannelApiSource) {
                return;
            }

            $violations = new ConstraintViolationList([
                new ConstraintViolation(
                    'Bitte wählen Sie ein verfügbares Treffen für die Abholung.',
                    'Bitte wählen Sie ein verfügbares Treffen für die Abholung.',
                    [],
                    null,
                    '/' . PickupService::FIELD,
                    $terminId
                ),
            ]);

            throw new ConstraintViolationException($violations, [PickupService::FIELD => $terminId]);
        }

        $snapshot = $this->pickup->snapshot($termin);
        $converted = $event->getConvertedCart();

        $customFields = \is_array($converted['customFields'] ?? null) ? $converted['customFields'] : [];
        $customFields[PickupService::CF_ID] = $snapshot['id'];
        $customFields[PickupService::CF_TITLE] = $snapshot['title'];
        $customFields[PickupService::CF_LOCATION] = $snapshot['location'];
        $customFields[PickupService::CF_DATE] = $snapshot['date'];
        $customFields[PickupService::CF_START] = $snapshot['start'];
        $customFields[PickupService::CF_TEXT] = $snapshot['text'];
        $converted['customFields'] = $customFields;

        $comment = $converted['customerComment'] ?? null;
        $converted['customerComment'] = PickupText::prefixComment($snapshot['text'], \is_string($comment) ? $comment : null);

        $event->setConvertedCart($converted);
    }

    /**
     * Gewählter Termin: aus dem Bestellformular bzw. der Store-API-Anfrage, sonst aus der Sitzung.
     */
    private function selectedId(SalesChannelContext $context): ?string
    {
        $request = $this->requestStack->getMainRequest();
        if (!$request instanceof Request) {
            return null;
        }

        $posted = $request->request->get(PickupService::FIELD);
        if (\is_string($posted) && $posted !== '') {
            return $posted;
        }

        if (!$request->hasSession()) {
            return null;
        }

        $stored = $request->getSession()->get(PickupService::SESSION_KEY . '.' . $context->getSalesChannelId());

        return \is_string($stored) && $stored !== '' ? $stored : null;
    }
}
