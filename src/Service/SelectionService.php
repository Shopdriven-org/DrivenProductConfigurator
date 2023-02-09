<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @package   Shopware\Plugins\DrivenProductConfigurator
 * @copyright (c) 2022 shopdriven
 */

namespace Driven\ProductConfigurator\Service;

use Driven\ProductConfigurator\Core\Content\Configurator\Aggregate\ConfiguratorSelection\ConfiguratorSelectionEntity;
use Driven\ProductConfigurator\Core\Content\Configurator\Aggregate\ConfiguratorStream\ConfiguratorStreamEntity;
use Driven\ProductConfigurator\Core\Content\Configurator\Aggregate\ConfiguratorStream\ConfiguratorStreamPreselectedProductEntity;
use Driven\ProductConfigurator\Core\Content\Configurator\ConfiguratorEntity;
use Driven\ProductConfigurator\DrivenProductConfigurator;
use Driven\ProductConfigurator\Exception\InvalidSelectionException;
use Driven\ProductConfigurator\Service\Validator\SelectionValidatorServiceInterface;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepositoryInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class SelectionService implements SelectionServiceInterface
{
    private EntityRepositoryInterface $drivenConfigurator;
    private EntityRepositoryInterface $productRepository;
    private CartService $cartService;

    public function __construct(
        EntityRepositoryInterface $drivenConfigurator,
        EntityRepositoryInterface $productRepository,
        CartService $cartService
    ) {
        $this->drivenConfigurator = $drivenConfigurator;
        $this->productRepository = $productRepository;
        $this->cartService = $cartService;
    }

    /**
     * {@inheritDoc}
     */
    public function getSelectionByLineItemId(string $id, ConfiguratorEntity $configurator, SalesChannelContext $salesChannelContext): array
    {
        // get the current cart
        $cart = $this->cartService->getCart(
            $salesChannelContext->getToken(),
            $salesChannelContext
        );

        // does that id even exist in our cart?
        if (!isset($cart->getLineItems()->getElements()[$id])) {
            // it doesnt
            throw new InvalidSelectionException();
        }

        // get the line item
        $lineItem = $cart->getLineItems()->getElements()[$id];

        // get the selection
        $selection = $lineItem->getPayloadValue('DrivenProductConfiguratorSelection');

        // validate it
        if ($this->selectionValidatorService->validate($configurator, $selection, $salesChannelContext) === false) {
            // invalid
            throw new InvalidSelectionException();
        }

        // and parse it
        return $this->parseSelection(
            $configurator,
            $selection,
            $salesChannelContext
        );
    }


    /**
     * @param string $productId
     * @param string $forehead
     * @param string $backhead
     * @param int $sealing
     * @param SalesChannelContext $salesChannelContext
     *
     * @return EntityWrittenContainerEvent
     */
    public function saveSelection(string $productId, $forehead, $backhead, $sealing, SalesChannelContext $salesChannelContext) : EntityWrittenContainerEvent
    {
        return $this->drivenConfigurator->create([[
            'id' => Uuid::randomHex(),
            'forehead' => $forehead,
            'backhead' => $backhead,
            'sealing' => $sealing,
            'customerId' => ($salesChannelContext->getCustomer() instanceof CustomerEntity) ? $salesChannelContext->getCustomer()->getId() : null,
            'productId' => $productId,
            'salesChannelId' => $salesChannelContext->getSalesChannel()->getId()
        ]], $salesChannelContext->getContext());

    }


    /**
     * @param string $productId
     * @param string $forehead
     * @param string $backhead
     * @param int $sealing
     * @param SalesChannelContext $salesChannelContext
     * @return EntityWrittenContainerEvent|void
     */
    public function updateSelection(string $productId, $forehead, $backhead, $sealing, SalesChannelContext $salesChannelContext)
    {
        $parentProduct = $this->getParentProduct($productId, $salesChannelContext);
        if ($parentProduct !== null) {
            return $this->drivenConfigurator->update([[
                'id' => $parentProduct->id,
                'forehead' => $forehead,
                'backhead' => $backhead,
                'sealing' => $sealing,
                'customerId' => ($salesChannelContext->getCustomer() instanceof CustomerEntity) ? $salesChannelContext->getCustomer()->getId() : null,
                'productId' => $productId,
                'salesChannelId' => $salesChannelContext->getSalesChannel()->getId()
            ]], $salesChannelContext->getContext());
        }
    }

    /**
     * @param array $ids
     * @param SalesChannelContext $salesChannelContext
     *
     * @return ProductEntity[]
     */
    private function getProducts(array $ids, SalesChannelContext $salesChannelContext): array
    {
        // create criteria
        $criteria = (new Criteria($ids));

        /** @var ProductEntity[] $products */
        $products = $this->productRepository
            ->search($criteria, $salesChannelContext->getContext())
            ->getElements();

        // return it
        return $products;
    }

    public function getParentProduct($id, SalesChannelContext $salesChannelContext)
    {
        /** @var DrivenProductConfigurator $drivenConfigurator */
        return $this->drivenConfigurator->search(
            (new Criteria())
                ->addFilter(new EqualsFilter('driven_product_configurator.productId', $id)),
            $salesChannelContext->getContext()
        )->first();
    }

    /**
     * @param string $id
     * @param SalesChannelContext $salesChannelContext
     * @return ?ProductEntity
     */
    public function getProduct(string $id, SalesChannelContext $salesChannelContext): ?ProductEntity
    {
        /** @var ProductEntity $productRepository */
        return $this->productRepository->search(
            (new Criteria())
                ->addFilter(new EqualsFilter('product.id', $id))
                ->addAssociation('cover.media')
                ->addAssociation('options.group')
                ->addAssociation('productTranslation')
                ->addAssociation('customFields'),
            $salesChannelContext->getContext()
        )->first();
    }

}
