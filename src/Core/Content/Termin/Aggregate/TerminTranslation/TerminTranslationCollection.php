<?php declare(strict_types=1);

namespace Nova\Termine\Core\Content\Termin\Aggregate\TerminTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @extends EntityCollection<TerminTranslationEntity>
 */
class TerminTranslationCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return TerminTranslationEntity::class;
    }
}
