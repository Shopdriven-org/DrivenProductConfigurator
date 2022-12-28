<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @package   Shopware\Plugins\DrivenProductConfigurator
 * @copyright (c) 2022 shopdriven
 */

namespace Driven\ProductConfigurator\Service\Cart;

use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

interface LineItemFactoryServiceInterface
{
    public const CONFIGURATOR_LINE_ITEM_TYPE = 'driven-product-configurator';
    public const PRODUCT_PARENT_LINE_ITEM_TYPE = 'driven-product-configurator-parent';
    public const PRODUCT_CHILD_LINE_ITEM_TYPE = 'driven-product-configurator-child';

    /**
     * ...
     *
     * @param ProductEntity $product
     * @param int $quantity
     * @param array $selection
     * @param SalesChannelContext $salesChannelContext
     *
     * @return LineItem
     */
    public function createConfigurator(ProductEntity $product, int $quantity, array $selection, SalesChannelContext $salesChannelContext): LineItem;

    /**
     * ...
     *
     * @param ProductEntity $product
     * @param int $quantity
     * @param bool $parent
     * @param SalesChannelContext $salesChannelContext
     *
     * @return LineItem
     */
    public function createProduct(ProductEntity $product, int $quantity, bool $parent, SalesChannelContext $salesChannelContext): LineItem;
}
