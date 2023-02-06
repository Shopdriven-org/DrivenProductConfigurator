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
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTaxCollection;
use Shopware\Core\Checkout\Cart\Tax\Struct\TaxRuleCollection;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class LineItemFactoryService implements LineItemFactoryServiceInterface
{

    public function __construct(){}

    /**
     * @param $quantity
     * @return LineItem
     */
    public function createSealingLineItem($quantity): LineItem
    {
        if ($quantity == 0){
            $quantity = 1;
        }
        $lineItem = new LineItem(
            self::SEALING_UID,
            self::PRODUCT_SEALING_LINE_ITEM_TYPE,
            self::SEALING_UID
        );
        $calculatedTaxes = new CalculatedTaxCollection();
        $taxRules = new TaxRuleCollection();
        $newPrice = new CalculatedPrice(
            5,
            $quantity * 5,
            $calculatedTaxes,
            $taxRules,
            $quantity
        );
        $lineItem->setLabel("Schlagflächenversiegelung")
            ->setPayload([
                'productNumber' => "versiegelung",
            ]);
        $lineItem->setGood(true);
        $lineItem->setStackable(true);
        $lineItem->setPrice($newPrice);
        $lineItem->setQuantity($quantity);
        $lineItem->setType(self::PRODUCT_SEALING_LINE_ITEM_TYPE);

        return $lineItem;
    }

    /**
     * @param $quantity
     * @return LineItem
     */
    public function createMontageLineItem($quantity): LineItem
    {
        if ($quantity == 0){
            $quantity = 1;
        }
        $lineItem = new LineItem(
            self::MONTAGE_UID,
            self::PRODUCT_MONTAGE_LINE_ITEM_TYPE,
            self::MONTAGE_UID
        );
        $calculatedTaxes = new CalculatedTaxCollection();
        $taxRules = new TaxRuleCollection();
        $newPrice = new CalculatedPrice(
            1.5,
            $quantity * 1.5,
            $calculatedTaxes,
            $taxRules,
            $quantity
        );
        $lineItem->setLabel("Montage Neuanfertigung")
            ->setPayload([
                'productNumber' => "montage"
            ]);
        $lineItem->setGood(true);
        $lineItem->setStackable(true);
        $lineItem->setPrice($newPrice);
        $lineItem->setQuantity($quantity);
        $lineItem->setType(self::PRODUCT_MONTAGE_LINE_ITEM_TYPE);

        return $lineItem;
    }

    /**
     * @param ProductEntity $product
     * @return array
     */
    private function getOptions(ProductEntity $product): array
    {
        return array_values(array_map(
            function(PropertyGroupOptionEntity $option) {
                return [
                    'group' => $option->getGroup()->getTranslation('name'),
                    'option' => $option->getTranslation('name')
                ];
            },
            $product->getOptions()->getElements()
        ));
    }
}
