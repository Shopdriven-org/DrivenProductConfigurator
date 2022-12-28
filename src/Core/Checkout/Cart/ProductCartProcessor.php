<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @package   Shopware\Plugins\Driven\ProductConfigurator
 * @copyright (c) 2022 shopdriven
 */

namespace Driven\ProductConfigurator\Core\Checkout\Cart;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartProcessorInterface;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\Price\QuantityPriceCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\PriceCollection;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class ProductCartProcessor implements CartProcessorInterface
{
    private QuantityPriceCalculator $quantityPriceCalculator;

    public function __construct(
        QuantityPriceCalculator $quantityPriceCalculator
    ) {
        $this->quantityPriceCalculator = $quantityPriceCalculator;
    }

    /**
     * {@inheritDoc}
     */
    public function process(CartDataCollection $data, Cart $original, Cart $toCalculate, SalesChannelContext $context, CartBehavior $behavior): void
    {
        $lineItems = $original->getLineItems()->filterType(
            'product'
        );

        foreach ($lineItems as $lineItem) {
            $payload = (array) $lineItem->getPayload();

            if (!isset($payload['DrivenProductConfigurator']) || $payload['DrivenProductConfigurator'] !== true) {
                continue;
            }

            if (!isset($payload['DrivenProductConfiguratorPrices']) || !is_array($payload['DrivenProductConfiguratorPrices'])) {
                continue;
            }

            // if we only have the parent but no children, then the plugin configuration is most likely
            // set to "flatten children" and we dont want to rewrite the price because the parent should
            // only have the single price of the product itself and not the price of the complete configuration
            if ($lineItem->hasChildren() === false) {
                continue;
            }

            $prices = [];

            foreach ($payload['DrivenProductConfiguratorPrices']['total'] as $taxId => $price) {
                $definition = new QuantityPriceDefinition(
                    $price,
                    $context->buildTaxRules($taxId),
                    1
                );

                $calculatedPrice = $this->quantityPriceCalculator->calculate(
                    $definition,
                    $context
                );

                $prices[] = $calculatedPrice;
            }

            $priceCollection = new PriceCollection(
                $prices
            );

            $priceSum = $priceCollection->sum();

            $newPrice = new CalculatedPrice(
                round($priceSum->getTotalPrice() / $lineItem->getQuantity(), $context->getCurrency()->getItemRounding()->getDecimals()),
                $priceSum->getTotalPrice(),
                $priceSum->getCalculatedTaxes(),
                $priceSum->getTaxRules(),
                $lineItem->getQuantity(),
                $priceSum->getReferencePrice(),
                $priceSum->getListPrice()
            );

            $lineItem->setPrice(
                $newPrice
            );
        }
    }
}
