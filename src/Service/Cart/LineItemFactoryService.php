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
use Shopware\Core\Checkout\Cart\LineItem\QuantityInformation;
use Shopware\Core\Content\Product\Aggregate\ProductMedia\ProductMediaEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionEntity;
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
        $lineItem = new LineItem(
            Uuid::randomHex(),
            ($parent === true) ? self::PRODUCT_PARENT_LINE_ITEM_TYPE : self::PRODUCT_CHILD_LINE_ITEM_TYPE,
            $product->getId(),
            $quantity
        );

        $lineItem->setLabel("Sealing service product")
            ->setCover(($product->getCover() instanceof ProductMediaEntity) ? $product->getCover()->getMedia() : null)
            ->setPayload([
                'DrivenProductConfiguratorProductId' => $product->getId(),
                'DrivenProductConfiguratorUnitPrice' => 0.0,
                'productNumber' => $product->getProductNumber(),
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
