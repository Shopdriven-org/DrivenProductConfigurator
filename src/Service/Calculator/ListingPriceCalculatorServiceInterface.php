<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @package   Shopware\Plugins\DrivenProductConfigurator
 * @copyright (c) 2022 shopdriven
 */

namespace Driven\ProductConfigurator\Service\Calculator;

use Driven\ProductConfigurator\Core\Content\Configurator\ConfiguratorEntity;
use Driven\ProductConfigurator\Struct\ListingPriceStruct;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

interface ListingPriceCalculatorServiceInterface
{
    /**
     * ...
     *
     * @param SalesChannelProductEntity $product
     * @param ConfiguratorEntity        $configurator
     * @param SalesChannelContext       $salesChannelContext
     *
     * @return ListingPriceStruct
     */
    public function calculate(SalesChannelProductEntity $product, ConfiguratorEntity $configurator, SalesChannelContext $salesChannelContext): ListingPriceStruct;
}
