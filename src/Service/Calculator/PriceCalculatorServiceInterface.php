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
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

interface PriceCalculatorServiceInterface
{
    /**
     * ...
     *
     * @param SalesChannelProductEntity $product
     * @param ConfiguratorEntity $configurator
     * @param array $streams
     * @param array $selection
     * @param int $rebate
     * @param SalesChannelContext $salesChannelContext
     *
     * @return float
     */
    public function calculate(SalesChannelProductEntity $product, ConfiguratorEntity $configurator, array $streams, array $selection, int $rebate, SalesChannelContext $salesChannelContext): float;
}
