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
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class ConfiguratorCartProcessor implements CartProcessorInterface
{
    private QuantityPriceCalculator $quantityPriceCalculator;
    private PercentagePriceCalculator $percentagePriceCalculator;
    private SystemConfigService $systemConfigService;

    public function __construct(
        QuantityPriceCalculator $quantityPriceCalculator,
        PercentagePriceCalculator $percentagePriceCalculator,
        SystemConfigService $systemConfigService
    ) {
        $this->quantityPriceCalculator = $quantityPriceCalculator;
        $this->percentagePriceCalculator = $percentagePriceCalculator;
        $this->systemConfigService = $systemConfigService;
    }

    /**
     * {}
     */
    public function process(CartDataCollection $data, Cart $original, Cart $toCalculate, SalesChannelContext $context, CartBehavior $behavior): void
    {


        // get every line item which is a configurator
        $lineItems = $original->getLineItems()->filterType(
            "LineItemFactoryServiceInterface::CONFIGURATOR_LINE_ITEM_TYPE"
        );

        // do we even have a configurator?
        if ($lineItems->count() === 0) {
            // we dont
            return;
        }

        // loop every configurator
        foreach ($lineItems as $lineItem) {
            // loop every child
            foreach ($lineItem->getChildren() as $child) {
                // set defaults
                $this->setChildDefaults($child);

                /** @var QuantityPriceDefinition $definition */
                $definition = $child->getPriceDefinition();

                // calculate by type of price definition
                switch (\get_class($definition)) {
                    case QuantityPriceDefinition::class:
                        $price = $this->quantityPriceCalculator->calculate(
                            $definition,
                            $context
                        );
                        break;

                    case PercentagePriceDefinition::class:
                        $price = $this->percentagePriceCalculator->calculate(
                            $child->getPayloadValue('DrivenProductConfiguratorPercentalSurcharge')['value'],
                            $this->getPercentagePrices(
                                $lineItem,
                                $lineItem->getChildren(),
                                $child->getQuantity(),
                                $context
                            ),
                            $context
                        );
                        break;

                    default:
                        throw new MissingLineItemPriceException($child->getId());
                }

                // set the price
                $child->setPrice($price);
            }

            // sum everything up (incl. taxes)
            $priceCollection = new PriceCollection(
                $this->getPrices(
                    $lineItem
                )
            );

            // get the summed up prices
            $priceSum = $priceCollection->sum();

            // create a new price with fixed unit price
            // the unit price is calculated wrong when we have child items with multiple
            // quantity. their unit price (1x) and total price is correct - but if we sum
            // up their unit prices, we will have a faulty unit price for our configurator.
            $newPrice = new CalculatedPrice(
                round($priceSum->getTotalPrice() / $lineItem->getQuantity(), $context->getCurrency()->getItemRounding()->getDecimals()),
                $priceSum->getTotalPrice(),
                $priceSum->getCalculatedTaxes(),
                $priceSum->getTaxRules(),
                $lineItem->getQuantity(),
                $priceSum->getReferencePrice(),
                $priceSum->getListPrice()
            );

            // set this one
            $lineItem->setPrice(
                $newPrice
            );

            // do we want to clear every price of every child?
            if ($this->systemConfigService->get('DrivenProductConfigurator.config.cartRemoveChildrenPrices', $context->getSalesChannel()->getId()) === true) {
                // clear every child price to have it 0,- EUR
                foreach ($lineItem->getChildren() as $child) {
                    // create a free-of-charge definition
                    $definition = new QuantityPriceDefinition(
                        0,
                        $context->buildTaxRules($child->getPayloadValue('taxId')),
                        $child->getQuantity()
                    );

                    // set the defintion
                    $child->setPriceDefinition($definition);

                    // and set the price
                    $child->setPrice(
                        $this->quantityPriceCalculator->calculate(
                            $definition,
                            $context
                        )
                    );
                }
            }

            // now add the configurator to the cart
            $toCalculate->add($lineItem);
        }
    }

    /**
     * Old cart entries may have invalid data before an update.
     * This method makes sure that every child line item has valid payload.
     *
     * @param LineItem $child
     */
    private function setChildDefaults(LineItem $child): void
    {
        // set valid percental surcharge
        if (!$child->hasPayloadValue('DrivenProductConfiguratorPercentalSurcharge')) {
            // set defaults
            $child->setPayloadValue('DrivenProductConfiguratorPercentalSurcharge', [
                'status' => false,
                'value' => 0
            ]);
        }
    }

    /**
     * ...
     *
     * @param LineItem $configurator
     * @param LineItemCollection $children
     * @param int $quantity
     * @param SalesChannelContext $context
     *
     * @return PriceCollection
     */
    private function getPercentagePrices(LineItem $configurator, LineItemCollection $children, int $quantity, SalesChannelContext $context): PriceCollection
    {
        // get every price
        $prices = array_filter(array_map(static function (LineItem $lineItem) {
            return (!$lineItem->hasPayloadValue('DrivenProductConfiguratorPercentalSurcharge') || $lineItem->getPayloadValue('DrivenProductConfiguratorPercentalSurcharge')['status'] === false) ? $lineItem->getPrice() : null;
        }, array_values($children->getElements())));

        // all unit prices
        $unitPrices = [];

        // loop every quantity price
        foreach ($prices as $price) {
            // set definition
            $unitPriceDefinition = new QuantityPriceDefinition(
                $price->getUnitPrice(),
                $price->getTaxRules(),
                $price->getQuantity() * 1
            );

            // calculate and att
            $unitPrices[] = $this->quantityPriceCalculator->calculate(
                $unitPriceDefinition,
                $context
            );
        }

        // return as collection
        return new PriceCollection($unitPrices);
    }

    /**
     * ...
     *
     * @param LineItem $lineItem
     *
     * @return array
     */
    private function getPrices(LineItem $lineItem): array
    {
        // every price for every child
        $prices = [];

        // loop the children
        foreach ($lineItem->getChildren() as $childLineItem) {
            // we need a valid price
            if (!$childLineItem->getPrice() instanceof CalculatedPrice) {
                // ignore it
                continue;
            }

            // add this price
            $prices[] = $childLineItem->getPrice();
        }

        // return them
        return $prices;
    }
}
