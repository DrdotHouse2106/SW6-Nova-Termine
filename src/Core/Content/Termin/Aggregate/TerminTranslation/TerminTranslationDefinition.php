<?php declare(strict_types=1);

namespace Nova\Termine\Core\Content\Termin\Aggregate\TerminTranslation;

use Nova\Termine\Core\Content\Termin\TerminDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityTranslationDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class TerminTranslationDefinition extends EntityTranslationDefinition
{
    final public const ENTITY_NAME = 'nova_termin_translation';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return TerminTranslationEntity::class;
    }

    public function getCollectionClass(): string
    {
        return TerminTranslationCollection::class;
    }

    protected function getParentDefinitionClass(): string
    {
        return TerminDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new StringField('title', 'title', 255))->addFlags(new ApiAware(), new Required()),
            (new StringField('program', 'program', 1000))->addFlags(new ApiAware()),
        ]);
    }
}
