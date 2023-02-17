<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @package   Shopware\Plugins\DrivenProductConfigurator
 * @copyright (c) 2022 shopdriven
 */

namespace Driven\ProductConfigurator\Service\Cart;

use Driven\ProductConfigurator\Service\VariantTextService;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Tax\Struct\CalculatedTaxCollection;
use Shopware\Core\Checkout\Cart\Tax\Struct\TaxRuleCollection;
use Shopware\Core\Content\Product\Aggregate\ProductMedia\ProductMediaEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\PriceCollection;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class LineItemFactoryService implements LineItemFactoryServiceInterface
{

    private VariantTextService $variantTextService;

    public function __construct(VariantTextService $variantTextService)
    {
        $this->variantTextService = $variantTextService;
    }

    /**
     * @param $quantity
     * @return LineItem
     */
    public function createSealingLineItem($quantity): LineItem
    {
        if ($quantity == 0) {
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
        if ($quantity == 0) {
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

        return $lineItem;
    }

    /**
     * @param ProductEntity $product
     * @param LineItem $parentLineItem
     * @return LineItem
     */
    public function createProductConfigurator(ProductEntity $product, LineItem $parentLineItem): LineItem
    {
        $lineItem = new LineItem(
            Uuid::randomHex(),
            "product",
            $product->getId(),
            $parentLineItem->getQuantity() - 1
        );

        $lineItem->setLabel(($product->getParentId() === null)
            ? $product->getTranslated()['name']
            : $product->getTranslated()['name'] . ' (' . $this->variantTextService->getName($product, false) . ')')
            ->setCover(($product->getCover() instanceof ProductMediaEntity) ? $product->getCover()->getMedia() : null)
            ->setPayload([
                'productNumber' => $product->getProductNumber(),
                'customFields' => $product->getCustomFields() ? $product->getCustomFields() : null,
                'weight' => $product->getWeight(),
                'height' => $product->getHeight(),
                'width' => $product->getWidth(),
                'length' => $product->getLength(),
                'taxId' => $product->getTaxId(),
                'manufacturerId' => $product->getManufacturerId(),
                'propertyIds' => $product->getPropertyIds(),
                'optionIds' => $product->getOptionIds(),
                'tagIds' => $product->getTagIds()
            ]);
        $lineItem->addArrayExtension("Equipments", (array)$parentLineItem->getExtensions()["Equipments"]["items"]);
        $calculatedTaxes = new CalculatedTaxCollection();
        $taxRules = new TaxRuleCollection();
        $unitPrice = 0;
        foreach ($product->getPrice()->getElements() as $element) {
            $unitPrice = $element->getGross();
        }
        $price = new CalculatedPrice(
            $unitPrice,
            $unitPrice * $parentLineItem->getQuantity() - 1,
            $calculatedTaxes,
            $taxRules,
            $parentLineItem->getQuantity()
        );
        $lineItem->setPrice($price);
        $lineItem->setGood(true);
        $lineItem->setStackable(true);
        $lineItem->setRemovable(true);
        return $lineItem;
    }
}
