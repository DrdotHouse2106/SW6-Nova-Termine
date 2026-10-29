<?php declare(strict_types=1);

namespace Nova\Termine\Service;

use Nova\Termine\Core\Content\Termin\TerminEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Contracts\Service\ResetInterface;

/**
 * „Abholung auf Treffen/Messe“: welche Versandart ist die Abhol-Versandart, welche Termine stehen
 * zur Auswahl (aktiv, Verkaufskanal passt, „Mit Stand“, Beginn >= heute + Vorlauf)?
 */
class PickupService implements ResetInterface
{
    /** Formular-/Bestellfeld mit der gewählten Termin-ID */
    public const FIELD = 'novaTerminePickupId';

    /** Sitzungsschlüssel (je Verkaufskanal) */
    public const SESSION_KEY = 'novaTerminePickup';

    /** Felder an der Bestellung (order.customFields) */
    public const CF_ID = 'nova_termine_pickup_id';
    public const CF_TITLE = 'nova_termine_pickup_title';
    public const CF_LOCATION = 'nova_termine_pickup_location';
    public const CF_DATE = 'nova_termine_pickup_date';
    public const CF_START = 'nova_termine_pickup_start';
    public const CF_TEXT = 'nova_termine_pickup_text';

    /** @var array<string, list<TerminEntity>> */
    private array $available = [];

    public function __construct(
        private readonly TerminLoader $loader,
        private readonly SystemConfigService $config,
    ) {
    }

    public function getShippingMethodId(string $salesChannelId): ?string
    {
        $id = $this->config->get('NovaTermine.config.pickupShippingMethodId', $salesChannelId);

        return \is_string($id) && $id !== '' ? $id : null;
    }

    public function getLeadDays(string $salesChannelId): int
    {
        $days = $this->config->get('NovaTermine.config.pickupLeadDays', $salesChannelId);

        return PickupText::normalizeLeadDays($days);
    }

    public function isPickupShippingMethod(string $shippingMethodId, string $salesChannelId): bool
    {
        $pickupId = $this->getShippingMethodId($salesChannelId);

        return $pickupId !== null && strtolower($pickupId) === strtolower($shippingMethodId);
    }

    /**
     * Ist im Kontext die Abhol-Versandart gewählt?
     */
    public function isActive(SalesChannelContext $context): bool
    {
        return $this->isPickupShippingMethod($context->getShippingMethod()->getId(), $context->getSalesChannelId());
    }

    /**
     * Termine, auf denen abgeholt werden kann – aufsteigend nach Datum.
     *
     * @return list<TerminEntity>
     */
    public function getAvailable(SalesChannelContext $context): array
    {
        $key = $context->getSalesChannelId() . '|' . $context->getLanguageId();
        if (isset($this->available[$key])) {
            return $this->available[$key];
        }

        $salesChannelId = $context->getSalesChannelId();
        $minStart = PickupText::earliestStart($this->loader->today($salesChannelId), $this->getLeadDays($salesChannelId));

        return $this->available[$key] = $this->loader->loadUpcoming(
            $salesChannelId,
            $context->getContext(),
            TerminPresenter::AREA_BOOTH,
            0,
            $minStart
        );
    }

    public function find(SalesChannelContext $context, ?string $terminId): ?TerminEntity
    {
        if ($terminId === null || $terminId === '') {
            return null;
        }

        foreach ($this->getAvailable($context) as $termin) {
            if (strtolower($termin->getId()) === strtolower($terminId)) {
                return $termin;
            }
        }

        return null;
    }

    /**
     * @return array{id: string, title: string, location: string, date: string, start: string, text: string}
     */
    public function snapshot(TerminEntity $termin): array
    {
        $start = $termin->getStartDate() ?? new \DateTimeImmutable();
        $title = (string) ($termin->getTranslation('title') ?? $termin->getTitle() ?? '');
        $date = DateRange::format($start, $termin->getEndDate());

        return [
            'id' => $termin->getId(),
            'title' => $title,
            'location' => $termin->getLocation(),
            'date' => $date,
            'start' => $start->format('Y-m-d'),
            'text' => PickupText::summary($title, $termin->getLocation(), $date),
        ];
    }

    public function reset(): void
    {
        $this->available = [];
    }
}
