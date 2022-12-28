<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @package   Shopware\Plugins\DrivenProductConfigurator
 * @copyright (c) 2022 shopdriven
 */

namespace Driven\ProductConfigurator\Core\Content\Configurator\Aggregate\ConfiguratorSelection;

use Driven\ProductConfigurator\Core\Content\Configurator\ConfiguratorDefinition;
use Shopware\Core\Checkout\Customer\CustomerDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\CreatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ReferenceVersionField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\UpdatedAtField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\System\SalesChannel\SalesChannelDefinition;

class ConfiguratorSelectionDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'driven_configurator_selection';

    /**
     * {}
     */
    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    /**
     * {@inheritDoc}
     */
    public function getCollectionClass(): string
    {
        return ConfiguratorSelectionCollection::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getEntityClass(): string
    {
        return ConfiguratorSelectionEntity::class;
    }

    /**
     * {}
     */
    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            // default fields
            (new IdField('id', 'id'))->addFlags(new PrimaryKey(), new Required()),

            // default
            (new StringField('key', 'key'))->addFlags(new Required()),
            (new JsonField('selection', 'selection', [], []))->addFlags(new Required()),

            // the configurator
            (new FkField('configurator_id', 'configuratorId', ConfiguratorDefinition::class)),
            (new ManyToOneAssociationField('configurator', 'configurator_id', ConfiguratorDefinition::class)),

            // shopware customer
            (new FkField('customer_id', 'customerId', CustomerDefinition::class)),
            (new ManyToOneAssociationField('customer', 'customer_id', CustomerDefinition::class)),

            // shopware sales channel
            (new FkField('sales_channel_id', 'salesChannelId', SalesChannelDefinition::class)),
            (new ManyToOneAssociationField('salesChannel', 'sales_channel_id', SalesChannelDefinition::class)),

            // product
            (new FkField('product_id', 'productId', ProductDefinition::class)),
            (new ReferenceVersionField(ProductDefinition::class)),
            (new ManyToOneAssociationField('product', 'product_id', ProductDefinition::class)),

            // inherited fields
            new CreatedAtField(),
            new UpdatedAtField(),
        ]);
    }
}
