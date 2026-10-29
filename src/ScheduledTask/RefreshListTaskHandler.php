<?php declare(strict_types=1);

namespace Nova\Termine\ScheduledTask;

use Nova\Termine\Service\CacheTags;
use Nova\Termine\Service\DateRange;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Adapter\Cache\CacheInvalidator;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskCollection;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: RefreshListTask::class)]
final class RefreshListTaskHandler extends ScheduledTaskHandler
{
    private const STATE_KEY = 'nova_termine_list_day';

    /**
     * @param EntityRepository<ScheduledTaskCollection> $scheduledTaskRepository
     */
    public function __construct(
        EntityRepository $scheduledTaskRepository,
        LoggerInterface $logger,
        private readonly CacheInvalidator $cacheInvalidator,
        private readonly CacheItemPoolInterface $cache,
        private readonly SystemConfigService $config,
    ) {
        parent::__construct($scheduledTaskRepository, $logger);
    }

    public function run(): void
    {
        $day = DateRange::today($this->config->getString('NovaTermine.config.timezone'))->format('Y-m-d');

        $item = $this->cache->getItem(self::STATE_KEY);
        if ($item->isHit() && $item->get() === $day) {
            return;
        }

        // Neuer Tag (oder Zustand unbekannt, z. B. nach Cache leeren): einmal neu aufbauen lassen
        $this->cacheInvalidator->invalidate([CacheTags::LIST], true);

        $item->set($day);
        $item->expiresAfter(3 * 86400);
        $this->cache->save($item);
    }
}
