<?php declare(strict_types=1);

namespace Nova\Termine\Core\Content\Termin;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @extends EntityCollection<TerminEntity>
 */
class TerminCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return TerminEntity::class;
    }
}
