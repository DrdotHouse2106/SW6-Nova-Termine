<?php declare(strict_types=1);

namespace Nova\Termine\Service;

use Nova\Termine\Core\Content\Termin\TerminCollection;
use Nova\Termine\Core\Content\Termin\TerminDefinition;
use Nova\Termine\Core\Content\Termin\TerminEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\System\SystemConfig\SystemConfigService;

/**
 * Lädt die sichtbaren Termine eines Verkaufskanals: aktiv, für den Kanal freigegeben
 * (keine Zuordnung = alle Kanäle) und Ende >= heute in der Zeitzone des Shops.
 */
class TerminLoader
{
    /** Obergrenze je Abfrage – eine Terminliste hat realistisch einige Dutzend Einträge */
    private const MAX_LOAD = 500;

    /**
     * @param EntityRepository<TerminCollection> $terminRepository
     */
    public function __construct(
        private readonly EntityRepository $terminRepository,
        private readonly SystemConfigService $config,
    ) {
    }

    public function today(?string $salesChannelId = null): \DateTimeImmutable
    {
        return DateRange::today($this->config->getString('NovaTermine.config.timezone', $salesChannelId));
    }

    /**
     * @return list<TerminEntity>
     */
    public function loadUpcoming(string $salesChannelId, Context $context, string $area = TerminPresenter::AREA_ALL, int $limit = 0, ?\DateTimeImmutable $minStart = null): array
    {
        $today = $this->today($salesChannelId);
        $day = $today->format('Y-m-d');

        $criteria = new Criteria();
        $criteria->setLimit(self::MAX_LOAD);
        $criteria->addAssociation('salesChannels');
        $criteria->addAssociation('media');
        $criteria->addFilter(new EqualsFilter('status', TerminDefinition::STATUS_ACTIVE));
        $criteria->addFilter(new MultiFilter(MultiFilter::CONNECTION_OR, [
            new RangeFilter('endDate', [RangeFilter::GTE => $day]),
            new MultiFilter(MultiFilter::CONNECTION_AND, [
                new EqualsFilter('endDate', null),
                new RangeFilter('startDate', [RangeFilter::GTE => $day]),
            ]),
        ]));
        if ($minStart !== null) {
            $criteria->addFilter(new RangeFilter('startDate', [RangeFilter::GTE => $minStart->format('Y-m-d')]));
        }
        $criteria->addSorting(new FieldSorting('startDate', FieldSorting::ASCENDING));
        $criteria->addSorting(new FieldSorting('endDate', FieldSorting::ASCENDING));

        $result = [];
        foreach ($this->terminRepository->search($criteria, $context)->getEntities() as $termin) {
            if ($termin->getStartDate() === null) {
                continue;
            }
            if (!DateRange::isUpcoming($termin->getStartDate(), $termin->getEndDate(), $today)) {
                continue;
            }
            $channels = $termin->getSalesChannels() !== null ? array_values($termin->getSalesChannels()->getIds()) : [];
            if (!TerminPresenter::visibleInSalesChannel(array_map('strval', $channels), $salesChannelId)) {
                continue;
            }
            if (!TerminPresenter::matchesArea($termin->getParticipation(), $area)) {
                continue;
            }

            $result[] = $termin;
            if ($limit > 0 && \count($result) >= $limit) {
                break;
            }
        }

        return $result;
    }
}
