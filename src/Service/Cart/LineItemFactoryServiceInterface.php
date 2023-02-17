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
    public const PRODUCT_SEALING_LINE_ITEM_TYPE = 'driven-product-sealing-service';
    public const PRODUCT_MONTAGE_LINE_ITEM_TYPE = 'driven-product-montage-service';
    public const SEALING_UID = 'DC1B7FFCB8D64DD2AE574A21F34F6FC5';
    public const MONTAGE_UID = 'AC1B7FFCB8D64DD2AE574A21F34F6FC5';

    /**
     * @param $quantity
     *
     * @return LineItem
     */
    public function createSealingLineItem($quantity): LineItem;

    /**
     * @param $quantity
     * @return LineItem
     */
    public function createMontageLineItem($quantity): LineItem;

    /**
     * @param ProductEntity $product
     * @param LineItem $parentLineItem
     * @return LineItem
     */
    public function createProductConfigurator(ProductEntity $product, LineItem $parentLineItem): LineItem;
}
