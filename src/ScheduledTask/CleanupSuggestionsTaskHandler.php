<?php declare(strict_types=1);

namespace Nova\Termine\ScheduledTask;

use Nova\Termine\Core\Content\Termin\TerminCollection;
use Nova\Termine\Core\Content\Termin\TerminDefinition;
use Psr\Log\LoggerInterface;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\RangeFilter;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskCollection;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: CleanupSuggestionsTask::class)]
final class CleanupSuggestionsTaskHandler extends ScheduledTaskHandler
{
    public const MAX_AGE_DAYS = 180;

    /**
     * @param EntityRepository<ScheduledTaskCollection> $scheduledTaskRepository
     * @param EntityRepository<TerminCollection>        $terminRepository
     */
    public function __construct(
        EntityRepository $scheduledTaskRepository,
        LoggerInterface $logger,
        private readonly EntityRepository $terminRepository,
    ) {
        parent::__construct($scheduledTaskRepository, $logger);
    }

    public function run(): void
    {
        $context = Context::createCLIContext();
        $cutoff = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))
            ->modify('-' . self::MAX_AGE_DAYS . ' days')
            ->format(Defaults::STORAGE_DATE_TIME_FORMAT);

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('status', TerminDefinition::STATUS_SUGGESTION));
        $criteria->addFilter(new RangeFilter('createdAt', [RangeFilter::LT => $cutoff]));
        $criteria->setLimit(500);

        $ids = $this->terminRepository->searchIds($criteria, $context)->getIds();
        if ($ids === []) {
            return;
        }

        $this->terminRepository->delete(array_map(static fn (string $id) => ['id' => $id], $ids), $context);
    }
}
