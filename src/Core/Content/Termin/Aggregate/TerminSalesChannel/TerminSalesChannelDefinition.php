<?php declare(strict_types=1);

namespace Nova\Termine\Core\Content\Termin\Aggregate\TerminSalesChannel;

use Nova\Termine\Core\Content\Termin\TerminDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Framework\DataAbstractionLayer\MappingEntityDefinition;
use Shopware\Core\System\SalesChannel\SalesChannelDefinition;

/**
 * Zuordnung Termin ↔ Verkaufskanal. Keine Zuordnung = Termin in allen Verkaufskanälen.
 */
class TerminSalesChannelDefinition extends MappingEntityDefinition
{
    final public const ENTITY_NAME = 'nova_termin_sales_channel';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new FkField('nova_termin_id', 'novaTerminId', TerminDefinition::class))->addFlags(new PrimaryKey(), new Required()),
            (new FkField('sales_channel_id', 'salesChannelId', SalesChannelDefinition::class))->addFlags(new PrimaryKey(), new Required()),
            new ManyToOneAssociationField('novaTermin', 'nova_termin_id', TerminDefinition::class, 'id', false),
            new ManyToOneAssociationField('salesChannel', 'sales_channel_id', SalesChannelDefinition::class, 'id', false),
        ]);
    }
}
