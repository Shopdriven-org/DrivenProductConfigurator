<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @package   Shopware\Plugins\DrivenProductConfigurator
 * @copyright (c) 2022 shopdriven
 */

namespace Driven\ProductConfigurator\Service\Calculator;

use Driven\ProductConfigurator\Core\Content\Configurator\Aggregate\ConfiguratorStream\ConfiguratorStreamEntity;
use Driven\ProductConfigurator\Core\Content\Configurator\ConfiguratorEntity;
use Shopware\Core\Checkout\Cart\Price\QuantityPriceCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\PriceCollection;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Content\Product\SalesChannel\Price\AbstractProductPriceCalculator;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class PriceCalculatorService implements PriceCalculatorServiceInterface
{
    private QuantityPriceCalculator $quantityPriceCalculator;
    private AbstractProductPriceCalculator $productPriceCalculator;

    public function __construct(
        QuantityPriceCalculator $quantityPriceCalculator,
        AbstractProductPriceCalculator $productPriceCalculator
    ) {
        $this->quantityPriceCalculator = $quantityPriceCalculator;
        $this->productPriceCalculator = $productPriceCalculator;
    }

    /**
     * {@inheritDoc}
     */
    public function calculate(SalesChannelProductEntity $product, ConfiguratorEntity $configurator, array $streams, array $selection, int $rebate, SalesChannelContext $salesChannelContext): float
    {
        // ever price definition
        $prices = [];

        // parent product definition
        $definition = $this->getPriceDefinition(
            $product,
            1,
            $configurator->getFree(),
            $rebate,
            $salesChannelContext
        );

        // add to prices
        $prices[] = $this->quantityPriceCalculator->calculate(
            $definition,
            $salesChannelContext
        );

        // loop every selected element
        foreach ($selection as $element) {
            // element stream
            $stream = $streams[$element['streamId']];

            /** @var SalesChannelProductEntity $child */
            $child = (isset($stream['products'][$element['productId']]))
                ? $stream['products'][$element['productId']]
                : $stream['products'][$element['parentId']];

            // get definition
            $definition = $this->getPriceDefinition(
                $child,
                $element['quantity'],
                $stream['entity']->getFree(),
                $rebate,
                $salesChannelContext
            );

            // add to prices
            $prices[] = $this->quantityPriceCalculator->calculate(
                $definition,
                $salesChannelContext
            );
        }

        // sum everything up (incl. taxes)
        $priceCollection = new PriceCollection(
            $prices
        );

        // get the sum
        $price = $priceCollection->sum();

        // and return unit price
        return $price->getTotalPrice();
    }

    /**
     * ...
     *
     * @param SalesChannelProductEntity $product
     * @param int $quantity
     * @param bool $isFree
     * @param int $rebate
     * @param SalesChannelContext $salesChannelContext
     *
     * @return QuantityPriceDefinition
     */
    private function getPriceDefinition(SalesChannelProductEntity $product, int $quantity, bool $isFree, int $rebate, SalesChannelContext $salesChannelContext): QuantityPriceDefinition
    {
        if ($isFree === true) {
            return new QuantityPriceDefinition(
                0,
                $salesChannelContext->buildTaxRules($product->getTaxId()),
                $quantity
            );
        }

        $this->productPriceCalculator->calculate(
            [$product],
            $salesChannelContext
        );

        $price = $this->getCalculatedProductPrice(
            $product,
            $quantity
        );

        return new QuantityPriceDefinition(
            $price->getUnitPrice() * (1 - ($rebate / 100)),
            $salesChannelContext->buildTaxRules($product->getTaxId()),
            $quantity
        );
    }

    /**
     * ...
     *
     * @param SalesChannelProductEntity $product
     * @param int $quantity
     *
     * @return CalculatedPrice
     */
    private function getCalculatedProductPrice(SalesChannelProductEntity $product, int $quantity): CalculatedPrice
    {
        if ($product->getCalculatedPrices()->count() === 0) {
            return $product->getCalculatedPrice();
        }

        $price = $product->getCalculatedPrice();

        foreach ($product->getCalculatedPrices() as $price) {
            if ($quantity <= $price->getQuantity()) {
                break;
            }
        }

        return $price;
    }
}
