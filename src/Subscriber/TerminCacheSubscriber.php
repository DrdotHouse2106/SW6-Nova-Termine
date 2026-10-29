<?php declare(strict_types=1);

namespace Nova\Termine\Subscriber;

use Nova\Termine\Core\Content\Termin\Aggregate\TerminSalesChannel\TerminSalesChannelDefinition;
use Nova\Termine\Core\Content\Termin\Aggregate\TerminTranslation\TerminTranslationDefinition;
use Nova\Termine\Core\Content\Termin\TerminDefinition;
use Nova\Termine\Service\CacheTags;
use Shopware\Core\Framework\Adapter\Cache\CacheInvalidator;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Speichern oder Löschen eines Termins → alle Seiten mit Terminliste aus dem HTTP-Cache werfen.
 */
class TerminCacheSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly CacheInvalidator $cacheInvalidator)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [EntityWrittenContainerEvent::class => 'onWritten'];
    }

    public function onWritten(EntityWrittenContainerEvent $event): void
    {
        foreach ([TerminDefinition::ENTITY_NAME, TerminTranslationDefinition::ENTITY_NAME, TerminSalesChannelDefinition::ENTITY_NAME] as $entity) {
            if ($event->getEventByEntityName($entity) !== null) {
                $this->cacheInvalidator->invalidate([CacheTags::LIST]);

                return;
            }
        }
    }
}
