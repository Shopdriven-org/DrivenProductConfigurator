<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @package   Shopware\Plugins\DrivenProductConfigurator
 * @copyright (c) 2022 shopdriven
 */

namespace Driven\ProductConfigurator\Service\Cart;

use Driven\ProductConfigurator\Service\VariantTextServiceInterface;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Cart\LineItem\QuantityInformation;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\PriceDefinitionCollection;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTaxCollection;
use Shopware\Core\Checkout\Cart\Tax\Struct\TaxRuleCollection;
use Shopware\Core\Content\Product\Aggregate\ProductMedia\ProductMediaEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\PriceCollection;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class LineItemFactoryService implements LineItemFactoryServiceInterface
{

    public function __construct(
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function createProduct(ProductEntity $product, int $quantity, bool $parent, SalesChannelContext $salesChannelContext): LineItem
    {
//        dd($quantity);
        if ($quantity === 0){
            $quantity = 1;
        }
        $lineItem = new LineItem(
            self::SEALING_UID,
            self::PRODUCT_SEALING_LINE_ITEM_TYPE,
            $product->getId()
        );
        $calculatedTaxes = new CalculatedTaxCollection();
        $taxRules = new TaxRuleCollection();
        $newPrice = new CalculatedPrice(
            $quantity * 5,
            $quantity * 5,
            $calculatedTaxes,
            $taxRules,
            $quantity
        );
        $lineItem->setLabel("Versiegelung")
            ->setPayload([
                'productNumber' => "versiegelung",
                'weight' => $product->getWeight(),
                'height' => $product->getHeight(),
                'width' => $product->getWidth(),
                'length' => $product->getLength(),
                'taxId' => $product->getTaxId(),
                'manufacturerId' => $product->getManufacturerId(),
                'propertyIds' => $product->getPropertyIds(),
                'optionIds' => $product->getOptionIds(),
                'options' => $this->getOptions($product),
                'tagIds' => $product->getTagIds()
            ]);
        $lineItem->setGood(true);
        $lineItem->setStackable(true);
        $lineItem->setPrice($newPrice);
        $lineItem->setQuantity($quantity);
        $lineItem->setType(self::PRODUCT_SEALING_LINE_ITEM_TYPE);

        return $lineItem;
    }

    /**
     * ...
     *
     * @param ProductEntity $product
     *
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
