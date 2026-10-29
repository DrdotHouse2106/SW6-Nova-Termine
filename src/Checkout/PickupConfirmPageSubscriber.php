<?php declare(strict_types=1);

namespace Nova\Termine\Checkout;

use Nova\Termine\Service\DateRange;
use Nova\Termine\Service\PickupService;
use Nova\Termine\Service\PickupText;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Storefront\Page\Checkout\Confirm\CheckoutConfirmPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Bestätigungsseite: Daten für die Auswahl „Auf welchem Treffen möchten Sie abholen?“
 * (Seiten-Erweiterung „novaTerminePickup“).
 */
class PickupConfirmPageSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly PickupService $pickup)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [CheckoutConfirmPageLoadedEvent::class => 'onConfirmPage'];
    }

    public function onConfirmPage(CheckoutConfirmPageLoadedEvent $event): void
    {
        $context = $event->getSalesChannelContext();
        if (!$this->pickup->isActive($context)) {
            return;
        }

        $request = $event->getRequest();
        $selected = $request->hasSession()
            ? $request->getSession()->get(PickupService::SESSION_KEY . '.' . $context->getSalesChannelId())
            : null;

        $options = [];
        $selectedValid = false;
        foreach ($this->pickup->getAvailable($context) as $termin) {
            $start = $termin->getStartDate();
            if ($start === null) {
                continue;
            }
            $id = $termin->getId();
            $options[] = [
                'id' => $id,
                'label' => PickupText::label(
                    (string) ($termin->getTranslation('title') ?? $termin->getTitle() ?? ''),
                    $termin->getLocation(),
                    DateRange::format($start, $termin->getEndDate())
                ),
            ];
            $selectedValid = $selectedValid || $id === $selected;
        }

        $event->getPage()->addExtension('novaTerminePickup', new ArrayStruct([
            'options' => $options,
            'selected' => $selectedValid ? $selected : null,
            'leadDays' => $this->pickup->getLeadDays($context->getSalesChannelId()),
        ]));
    }
}
