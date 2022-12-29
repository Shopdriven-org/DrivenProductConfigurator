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
    private SalesChannelRepositoryInterface $salesChannelProductRepository;
    private CartService $cartService;

    public function __construct(
        EntityRepositoryInterface $drivenConfigurator,
        EntityRepositoryInterface $productRepository,
        SalesChannelRepositoryInterface $salesChannelProductRepository,
        CartService $cartService
    ) {
        $this->drivenConfigurator = $drivenConfigurator;
        $this->productRepository = $productRepository;
        $this->salesChannelProductRepository = $salesChannelProductRepository;
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
     * ...
     *
     * @param string $productId
     * @param string $forehead
     * @param string $backhead
     * @param int $sealing
     * @param SalesChannelContext $salesChannelContext
     *
     * @return EntityWrittenContainerEvent
     */
    public function saveSelection(string $productId, string $forehead, string $backhead, int $sealing, SalesChannelContext $salesChannelContext) : EntityWrittenContainerEvent
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
     * ...
     *
     * @param string $productId
     * @param string $forehead
     * @param string $backhead
     * @param int $sealing
     * @param SalesChannelContext $salesChannelContext
     * @return EntityWrittenContainerEvent|void
     */
    public function updateSelection(string $productId, string $forehead, string $backhead, int $sealing, SalesChannelContext $salesChannelContext)
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
     * Parse the configurator and its streams with the given selection.
     * Any parent product which has a selected variant gets replaced within the streams.
     * Returns a selected array for the template.
     *
     * @param ConfiguratorEntity $configurator
     * @param array $selection
     * @param SalesChannelContext $salesChannelContext
     *
     * @return array
     */
    private function parseSelection(ConfiguratorEntity $configurator, array $selection, SalesChannelContext $salesChannelContext): array
    {
        // replace the parents with the selected variants
        $this->replaceParents(
            $selection,
            $salesChannelContext
        );

        // our selected return array
        $selected = [];

        // loop every selection element
        foreach ($selection as $element) {
            // and set the array for the template
            $selected["__parent_id_" . $element['parentId'] . '__product_id_' . $element['productId']] = [
                'selected' => true,
                'parentId' => $element['parentId'],
                'productId' => $element['productId'],
                'quantity' => (integer) $element['quantity']
            ];
        }

        // return the array
        return $selected;
    }

    /**
     * Replace every stream parent product with its selected variant.
     *
     * @param array $selection
     * @param SalesChannelContext $salesChannelContext
     */
    private function replaceParents(array $selection, SalesChannelContext $salesChannelContext): void
    {
        // we need to replace a specific variant for a parent product
        foreach ($selection as $element) {
            // is this the parent?
            if ($element['parentId'] === $element['productId']) {
                // ignore this one
                continue;
            }

            // get full product
            $product = $this->getSalesChannelProduct(
                $element['productId'],
                $salesChannelContext
            );

            // get a full variant name
            $name = $this->variantTextService->getName(
                $product
            );

            // set the name
            $product->setName($name);

            // and the translation
            $product->setTranslated(
                array_merge(
                    $product->getTranslated(),
                    ['name' => $name]
                )
            );
        }
    }

    /**
     * ...
     *
     * @param string $id
     * @param SalesChannelContext $salesChannelContext
     *
     * @return SalesChannelProductEntity
     */
    private function getSalesChannelProduct(string $id, SalesChannelContext $salesChannelContext): SalesChannelProductEntity
    {
        // create criteria
        $criteria = (new Criteria([$id]))
            ->addAssociation('options.group');

        /** @var SalesChannelProductEntity $products */
        $product = $this->salesChannelProductRepository
            ->search($criteria, $salesChannelContext)
            ->getEntities()
            ->first();

        // retrn it
        return $product;
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
        $criteria = (new Criteria($ids));

        /** @var ProductEntity[] $products */
        $products = $this->productRepository
            ->search($criteria, $salesChannelContext->getContext())
            ->getElements();

        // retrn it
        return $products;
    }

    private function getParentProduct($id, SalesChannelContext $salesChannelContext)
    {
//        dd($id);
        /** @var DrivenProductConfigurator $drivenConfigurator */
        return $this->drivenConfigurator->search(
            (new Criteria())
                ->addFilter(new EqualsFilter('driven_product_configurator.productId', $id)),
            $salesChannelContext->getContext()
        )->first();
    }

}
