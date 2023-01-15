<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @package   Shopware\Plugins\Driven\ProductConfigurator
 * @copyright (c) 2022 shopdriven
 */

namespace Driven\ProductConfigurator\Core\Checkout\Cart;

use Driven\ProductConfigurator\Service\Cart\LineItemFactoryServiceInterface;
use Driven\ProductConfigurator\Service\SelectionService;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartProcessorInterface;
use Shopware\Core\Checkout\Cart\Exception\MissingLineItemPriceException;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Cart\Price\PercentagePriceCalculator;
use Shopware\Core\Checkout\Cart\Price\QuantityPriceCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\PercentagePriceDefinition;
use Shopware\Core\Checkout\Cart\Price\Struct\PriceCollection;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class ConfiguratorCartProcessor implements CartProcessorInterface
{
//    private QuantityPriceCalculator $quantityPriceCalculator;
//    private PercentagePriceCalculator $percentagePriceCalculator;
//    private SystemConfigService $systemConfigService;
    private SelectionService $selectionService;

    public function __construct(
//        QuantityPriceCalculator $quantityPriceCalculator,
//        PercentagePriceCalculator $percentagePriceCalculator,
//        SystemConfigService $systemConfigService,
        SelectionService $selectionService
    ) {
//        $this->quantityPriceCalculator = $quantityPriceCalculator;
//        $this->percentagePriceCalculator = $percentagePriceCalculator;
//        $this->systemConfigService = $systemConfigService;
        $this->selectionService = $selectionService;
    }

    /**
     * {}
     */
    public function process(CartDataCollection $data, Cart $original, Cart $toCalculate, SalesChannelContext $context, CartBehavior $behavior): void
    {

    }

}
