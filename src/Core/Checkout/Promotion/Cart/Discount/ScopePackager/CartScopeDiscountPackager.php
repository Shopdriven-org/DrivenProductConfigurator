<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @package   Shopware\Plugins\DrivenProductConfigurator
 * @copyright (c) 2022 shopdriven
 */

namespace Driven\ProductConfigurator\Core\Checkout\Promotion\Cart\Discount\ScopePackager;

use Driven\ProductConfigurator\Service\Cart\LineItemFactoryServiceInterface;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\LineItem\Group\LineItemQuantity;
use Shopware\Core\Checkout\Cart\LineItem\Group\LineItemQuantityCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\Struct\PriceDefinitionInterface;
use Shopware\Core\Checkout\Cart\Rule\LineItemScope;
use Shopware\Core\Checkout\Promotion\Cart\Discount\DiscountLineItem;
use Shopware\Core\Checkout\Promotion\Cart\Discount\DiscountPackage;
use Shopware\Core\Checkout\Promotion\Cart\Discount\DiscountPackageCollection;
use Shopware\Core\Checkout\Promotion\Cart\Discount\DiscountPackager;
use Shopware\Core\Framework\Rule\Rule;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class CartScopeDiscountPackager extends DiscountPackager
{
    private DiscountPackager $cartScopeDiscountPackager;

    public function __construct(
        DiscountPackager $cartScopeDiscountPackager
    ) {
        $this->cartScopeDiscountPackager = $cartScopeDiscountPackager;
    }

    /**
     * {@inheritDoc}
     */
    public function getResultContext(): string
    {
        return $this->cartScopeDiscountPackager->getResultContext();
    }

    /**
     * {@inheritDoc}
     */
    public function getDecorated(): DiscountPackager
    {
        return $this->cartScopeDiscountPackager;
    }

    /**
     * {@inheritDoc}
     */
    public function getMatchingItems(DiscountLineItem $discount, Cart $cart, SalesChannelContext $context): DiscountPackageCollection
    {
        // get parent items for default products
        $matchingItems = $this->cartScopeDiscountPackager->getMatchingItems($discount, $cart, $context);

        // get our configurator items
        $configuratorItems = $cart->getLineItems()->filterType(LineItemFactoryServiceInterface::CONFIGURATOR_LINE_ITEM_TYPE);

        // we have none?
        if ($configuratorItems->count() === 0) {
            // return default
            return $matchingItems;
        }

        // our items to add here
        $items = [];

        // check every configuration
        foreach ($configuratorItems as $lineItem) {
            // create a default product item to make it work with the default packager
            $productLineItem = new LineItem(
                $lineItem->getId(),
                LineItem::PRODUCT_LINE_ITEM_TYPE,
                $lineItem->getReferencedId(),
                $lineItem->getQuantity()
            );

            // set it up
            $productLineItem->setPrice($lineItem->getPrice());
            $productLineItem->setQuantityInformation($lineItem->getQuantityInformation());
            $productLineItem->setDeliveryInformation($lineItem->getDeliveryInformation());
            $productLineItem->setPayload($lineItem->getPayload());
            $productLineItem->setGood($lineItem->isGood());
            $productLineItem->setPriceDefinition($lineItem->getPriceDefinition());
            $productLineItem->setRemovable($lineItem->isRemovable());
            $productLineItem->setRequirement($lineItem->getRequirement());
            $productLineItem->setStackable($lineItem->isStackable());

            // are the discount rules valid?!
            if (!$this->isRulesFilterValid($productLineItem, $discount->getPriceDefinition(), $context)) {
                // they arent
                continue;
            }

            // in order to make the promotions work we need to make it stackable
            $productLineItem->setStackable(true);

            // add to the items
            $items[] = new LineItemQuantity($productLineItem->getId(), $productLineItem->getQuantity());
        }

        // do we have any items that matched?
        if ($items !== []) {
            // add them
            $matchingItems->add(new DiscountPackage(new LineItemQuantityCollection($items)));
        }

        // and return all
        return $matchingItems;
    }

    /**
     * ...
     *
     * @param LineItem                 $item
     * @param PriceDefinitionInterface $priceDefinition
     * @param SalesChannelContext      $context
     *
     * @return bool
     */
    private function isRulesFilterValid(LineItem $item, PriceDefinitionInterface $priceDefinition, SalesChannelContext $context): bool
    {
        if (!\method_exists($priceDefinition, 'getFilter')) {
            return true;
        }

        $filter = $priceDefinition->getFilter();
        if (!$filter instanceof Rule) {
            return true;
        }

        $scope = new LineItemScope($item, $context);
        return $filter->match($scope);
    }
}
