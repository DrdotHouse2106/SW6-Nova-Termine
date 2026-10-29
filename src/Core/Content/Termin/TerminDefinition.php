<?php declare(strict_types=1);

namespace Nova\Termine\Core\Content\Termin;

use Nova\Termine\Core\Content\Termin\Aggregate\TerminSalesChannel\TerminSalesChannelDefinition;
use Nova\Termine\Core\Content\Termin\Aggregate\TerminTranslation\TerminTranslationDefinition;
use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\SearchRanking;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\LongTextField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\SalesChannel\SalesChannelDefinition;

/**
 * Ein Termin (Treffen, Teilemarkt, Messe …).
 *
 * Teilnahme: „booth“ = mit Stand („Hier sind wir dabei“), „visitor“ = ohne Stand
 * („Weitere Treffen in der Szene“), „open“ = noch offen (wird wie „ohne Stand“ angezeigt).
 * Status: „active“ (sichtbar), „inactive“ (verborgen), „suggestion“ (Meldung aus dem Shop, nie sichtbar).
 */
class TerminDefinition extends EntityDefinition
{
    final public const ENTITY_NAME = 'nova_termin';

    final public const PARTICIPATION_BOOTH = 'booth';
    final public const PARTICIPATION_VISITOR = 'visitor';
    final public const PARTICIPATION_OPEN = 'open';
    final public const PARTICIPATIONS = [self::PARTICIPATION_BOOTH, self::PARTICIPATION_VISITOR, self::PARTICIPATION_OPEN];

    final public const STATUS_ACTIVE = 'active';
    final public const STATUS_INACTIVE = 'inactive';
    final public const STATUS_SUGGESTION = 'suggestion';
    final public const STATUSES = [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_SUGGESTION];

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return TerminEntity::class;
    }

    public function getCollectionClass(): string
    {
        return TerminCollection::class;
    }

    public function getDefaults(): array
    {
        return [
            'participation' => self::PARTICIPATION_OPEN,
            'status' => self::STATUS_ACTIVE,
        ];
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new IdField('id', 'id'))->addFlags(new ApiAware(), new PrimaryKey(), new Required()),
            (new TranslatedField('title'))->addFlags(new ApiAware(), new SearchRanking(SearchRanking::HIGH_SEARCH_RANKING)),
            (new TranslatedField('program'))->addFlags(new ApiAware()),
            (new StringField('location', 'location', 255))->addFlags(new ApiAware(), new Required(), new SearchRanking(SearchRanking::MIDDLE_SEARCH_RANKING)),
            (new StringField('country', 'country', 100))->addFlags(new ApiAware(), new SearchRanking(SearchRanking::LOW_SEARCH_RANKING)),
            (new DateField('start_date', 'startDate'))->addFlags(new ApiAware(), new Required()),
            (new DateField('end_date', 'endDate'))->addFlags(new ApiAware()),
            (new StringField('url', 'url', 500))->addFlags(new ApiAware()),
            (new StringField('participation', 'participation', 20))->addFlags(new ApiAware(), new Required()),
            (new StringField('status', 'status', 20))->addFlags(new ApiAware(), new Required()),
            (new FkField('media_id', 'mediaId', MediaDefinition::class))->addFlags(new ApiAware()),
            new ManyToOneAssociationField('media', 'media_id', MediaDefinition::class, 'id', false),
            new LongTextField('internal_note', 'internalNote'),
            new StringField('reporter_email', 'reporterEmail', 255),
            (new TranslationsAssociationField(TerminTranslationDefinition::class, 'nova_termin_id'))->addFlags(new ApiAware(), new Required()),
            (new ManyToManyAssociationField('salesChannels', SalesChannelDefinition::class, TerminSalesChannelDefinition::class, 'nova_termin_id', 'sales_channel_id'))->addFlags(new CascadeDelete()),
        ]);
    }
}
