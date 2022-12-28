<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @package   Shopware\Plugins\Driven\ProductConfigurator
 * @copyright (c) 2022 shopdriven
 */

namespace Driven\ProductConfigurator\Core\Checkout\Cart\LineItemFactoryHandler;

use Driven\ProductConfigurator\Service\Cart\LineItemFactoryServiceInterface;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItemFactoryHandler\LineItemFactoryInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class ConfiguratorLineItemFactory implements LineItemFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function supports(string $type): bool
    {
        return $type === LineItemFactoryServiceInterface::CONFIGURATOR_LINE_ITEM_TYPE;
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $data, SalesChannelContext $context): LineItem
    {
        throw new \Exception('create is not supported - use the plugin service instead');
    }

    /**
     * {@inheritDoc}
     */
    public function update(LineItem $lineItem, array $data, SalesChannelContext $context): void
    {
        // we only support updating quantity
        if (isset($data['quantity'])) {
            // update quantity
            $lineItem->setQuantity((int) $data['quantity']);
        }
    }
}
