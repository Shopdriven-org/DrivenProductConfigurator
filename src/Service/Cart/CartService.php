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
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
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
    public function addToCart(string $productId, int $quantity, array $selection, CoreCart $cart, SalesChannelContext $salesChannelContext): void
    {
        // get every product which is either parent or child
        $products = $this->getProducts(
            array_unique(array_merge(array_column($selection, 'productId'), [$productId])),
            $salesChannelContext
        );

        // create the configurator line item
        $lineItem = $this->lineItemFactoryService->createConfigurator(
            $products[$productId],
            $quantity,
            $selection,
            $salesChannelContext
        );

        // add the parent product as first child
        $lineItem->addChild(
            $this->lineItemFactoryService->createProduct(
                $products[$productId],
                $quantity,
                true,
                $salesChannelContext
            )
        );

        // loop every selected product
        foreach ($selection as $aktu) {
            // add that product as child
            $lineItem->addChild(
                $this->lineItemFactoryService->createProduct(
                    $products[$aktu['productId']],
                    $aktu['quantity'] * $quantity,
                    false,
                    $salesChannelContext,
                    $aktu['streamId']
                )
            );
        }

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
