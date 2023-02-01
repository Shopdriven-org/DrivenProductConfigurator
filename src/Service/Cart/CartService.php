<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @package   Shopware\Plugins\DrivenProductConfigurator
 * @copyright (c) 2022 shopdriven
 */

namespace Driven\ProductConfigurator\Service\Cart;

use Shopware\Core\Checkout\Cart\Cart as CoreCart;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService as CoreCartService;

class CartService implements CartServiceInterface
{
    private EntityRepositoryInterface $productRepository;
    private LineItemFactoryServiceInterface $lineItemFactoryService;
    private CoreCartService $cartService;

    public function __construct(
        EntityRepositoryInterface $productRepository,
        LineItemFactoryServiceInterface $lineItemFactoryService,
        CoreCartService $cartService
    ) {
        $this->productRepository = $productRepository;
        $this->lineItemFactoryService = $lineItemFactoryService;
        $this->cartService = $cartService;
    }

    /**
     * {@inheritDoc}
     */
    public function addToCart(string $productId, int $quantity, CoreCart $cart, SalesChannelContext $salesChannelContext): void
    {
        $lineItem = new LineItem(
            Uuid::randomHex(),
            self::PRODUCT_LINE_ITEM_TYPE,
            $productId,
            $quantity
        );

        // add the parent product as first child
//        $lineItem->addChild(
            $this->lineItemFactoryService->createSealingLineItem(
                $productId,
                $quantity,
                true,
                $salesChannelContext
            );
//        );

        // and add the configurator to the cart
        $cart = $this->cartService->add(
            $cart,
            $lineItem,
            $salesChannelContext
        );
    }

    /**
     * ...
     *
     * @param array $ids
     * @param SalesChannelContext $salesChannelContext
     *
     * @return ProductEntity[]
     */
    private function getProducts(array $ids, SalesChannelContext $salesChannelContext): array
    {
        // create criteria
        $criteria = (new Criteria($ids))
            ->addAssociation('cover.media')
            ->addAssociation('options.group')
            ->addAssociation('customFields');

        /** @var ProductEntity[] $products */
        $products = $this->productRepository
            ->search($criteria, $salesChannelContext->getContext())
            ->getElements();

        // retrn it
        return $products;
    }
}
