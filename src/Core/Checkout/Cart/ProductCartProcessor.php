<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @package   Shopware\Plugins\Driven\ProductConfigurator
 * @copyright (c) 2022 shopdriven
 */

namespace Driven\ProductConfigurator\Core\Checkout\Cart;

use Driven\ProductConfigurator\Service\Cart\LineItemFactoryService;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartProcessorInterface;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Cart\Price\QuantityPriceCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\PriceCollection;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class ProductCartProcessor implements CartProcessorInterface
{
    private QuantityPriceCalculator $quantityPriceCalculator;
    private LineItemFactoryService $lineItemFactoryService;
    private EntityRepositoryInterface $productRepository;

    public function __construct(
        QuantityPriceCalculator   $quantityPriceCalculator,
        LineItemFactoryService    $lineItemFactoryService,
        EntityRepositoryInterface $productRepository
    )
    {
        $this->quantityPriceCalculator = $quantityPriceCalculator;
        $this->lineItemFactoryService = $lineItemFactoryService;
        $this->productRepository = $productRepository;
    }

    /**
     * {}
     */
    public function process(CartDataCollection $data, Cart $original, Cart $toCalculate, SalesChannelContext $context, CartBehavior $behavior): void
    {
//        $id = "B9933FB7347C46FB9BB7617E30828E18";
//        $lineItem = $this->lineItemFactoryService->createProduct($this->getProduct($id, $context), 1, true, $context);
//        $original->getLineItems()->add($lineItem);
//        dd($original->getLineItems()->count());
//        foreach ($lineItems as $lineItem) {
//            $payload = (array)$lineItem->getPayload();
//        }

    }

    /**
     * ...
     *
     * @param string $id
     * @param SalesChannelContext $salesChannelContext
     *
     * @return ProductEntity
     */
    private function getProduct(string $id, SalesChannelContext $salesChannelContext): ProductEntity
    {
        /** @var ProductEntity $productRepository */
        return $this->productRepository->search(
            (new Criteria())
                ->addFilter(new EqualsFilter('product.id', $id))
                ->addAssociation('cover.media')
                ->addAssociation('options.group')
                ->addAssociation('customFields'),
            $salesChannelContext->getContext()
        )->first();
    }
}
