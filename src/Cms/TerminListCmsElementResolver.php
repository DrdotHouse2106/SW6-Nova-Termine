<?php declare(strict_types=1);

namespace Nova\Termine\Cms;

use Nova\Termine\Core\Content\Termin\TerminDefinition;
use Nova\Termine\Service\CacheTags;
use Nova\Termine\Service\DateRange;
use Nova\Termine\Service\LinkValidator;
use Nova\Termine\Service\TerminLoader;
use Nova\Termine\Service\TerminPresenter;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\DataResolver\CriteriaCollection;
use Shopware\Core\Content\Cms\DataResolver\Element\AbstractCmsElementResolver;
use Shopware\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Shopware\Core\Framework\Adapter\Cache\CacheTagCollector;

/**
 * Erlebniswelten-Element „Terminliste“ (Typ „nova-termine“).
 *
 * Markiert die Seite mit dem Cache-Tag {@see CacheTags::LIST}, damit sie beim Speichern eines Termins
 * und nach Mitternacht neu aufgebaut wird.
 */
class TerminListCmsElementResolver extends AbstractCmsElementResolver
{
    public const TYPE = 'nova-termine';

    public function __construct(
        private readonly TerminLoader $loader,
        private readonly CacheTagCollector $cacheTagCollector,
    ) {
    }

    public function getType(): string
    {
        return self::TYPE;
    }

    public function collect(CmsSlotEntity $slot, ResolverContext $resolverContext): ?CriteriaCollection
    {
        return null;
    }

    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        $this->cacheTagCollector->addTag(CacheTags::LIST);

        $config = $slot->getFieldConfig();
        $area = $config->get('area')?->getValue();
        if (!\is_string($area) || !\in_array($area, [TerminPresenter::AREA_ALL, TerminPresenter::AREA_BOOTH, TerminPresenter::AREA_SCENE], true)) {
            $area = TerminPresenter::AREA_ALL;
        }
        $limit = max(0, min(200, (int) ($config->get('maxCount')?->getValue() ?? 0)));

        $salesChannelContext = $resolverContext->getSalesChannelContext();
        $termine = $this->loader->loadUpcoming(
            $salesChannelContext->getSalesChannelId(),
            $salesChannelContext->getContext(),
            $area,
            $limit
        );
        $today = $this->loader->today($salesChannelContext->getSalesChannelId());

        $booth = [];
        $scene = [];
        $jsonLd = [];
        foreach ($termine as $termin) {
            $start = $termin->getStartDate();
            if ($start === null) {
                continue;
            }
            $title = (string) ($termin->getTranslation('title') ?? $termin->getTitle() ?? '');
            $program = (string) ($termin->getTranslation('program') ?? $termin->getProgram() ?? '');
            $url = (string) $termin->getUrl();
            if (!LinkValidator::isValid($url)) {
                $url = '';
            }

            $item = [
                'id' => $termin->getId(),
                'title' => $title,
                'date' => DateRange::format($start, $termin->getEndDate()),
                'startIso' => $start->format('Y-m-d'),
                'endIso' => DateRange::effectiveEnd($start, $termin->getEndDate())->format('Y-m-d'),
                'running' => DateRange::isRunning($start, $termin->getEndDate(), $today),
                'location' => $termin->getLocation(),
                'country' => (string) $termin->getCountry(),
                'program' => $program,
                'url' => $url,
                'open' => $termin->getParticipation() === TerminDefinition::PARTICIPATION_OPEN,
                'media' => $termin->getMedia(),
            ];

            if (TerminPresenter::area($termin->getParticipation()) === TerminPresenter::AREA_BOOTH) {
                $booth[] = $item;
            } else {
                $scene[] = $item;
            }

            $jsonLd[] = TerminPresenter::jsonLd(TerminPresenter::schema(
                $title,
                $start,
                $termin->getEndDate(),
                $termin->getLocation(),
                (string) $termin->getCountry(),
                $url
            ));
        }

        $slot->setData(new TerminListStruct($booth, $scene, $jsonLd, $area));
    }
}
