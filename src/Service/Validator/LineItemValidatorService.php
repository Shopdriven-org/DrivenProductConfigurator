<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @package   Shopware\Plugins\DrivenProductConfigurator
 * @copyright (c) 2022 shopdriven
 */

namespace Driven\ProductConfigurator\Service\Validator;

use Driven\ProductConfigurator\Core\Content\Configurator\ConfiguratorEntity;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class LineItemValidatorService implements LineItemValidatorServiceInterface
{
    private EntityRepositoryInterface $configuratorRepository;
    private EntityRepositoryInterface $productRepository;
    private SelectionValidatorServiceInterface $selectionValidatorServiceInterface;

    public function __construct(
        EntityRepositoryInterface $configuratorRepository,
        EntityRepositoryInterface $productRepository,
        SelectionValidatorServiceInterface $selectionValidatorService
    ) {
        $this->configuratorRepository = $configuratorRepository;
        $this->productRepository = $productRepository;
        $this->selectionValidatorServiceInterface = $selectionValidatorService;
    }

    /**
     * {@inheritDoc}
     */
    public function validate(LineItem $lineItem, SalesChannelContext $salesChannelContext): bool
    {
        // the selection array
        $selection = $lineItem->getPayloadValue('DrivenProductConfiguratorSelection');

        // get the configurator
        $configurator = $this->getConfigurator(
            $lineItem->getPayloadValue('DrivenProductConfiguratorId'),
            $salesChannelContext
        );

        // get the main product
        $product = $this->getProduct(
            $lineItem->getPayloadValue('DrivenProductConfiguratorProductId'),
            $salesChannelContext
        );

        // return by selection validator
        return $this->selectionValidatorServiceInterface->validate(
            $configurator,
            $selection,
            $salesChannelContext
        );
    }

    /**
     * ...
     *
     * @param string $id
     * @param SalesChannelContext $salesChannelContext
     *
     * @return ConfiguratorEntity
     */
    private function getConfigurator(string $id, SalesChannelContext $salesChannelContext): ConfiguratorEntity
    {
        // set up criteria
        $criteria = (new Criteria())
            ->addAssociation('streams')
            ->addAssociation('streams.preselectedProducts')
            ->addAssociation('streams.blacklistedProducts')
            ->addAssociation('streams.products')
            ->addAssociation('streams.conditions')
            ->addFilter(new EqualsFilter('id', $id));

        // set sorting
        $criteria->getAssociation('streams')
            ->addSorting(new FieldSorting('position', FieldSorting::ASCENDING));

        /** @var ConfiguratorEntity $configuratorEntity */
        $configuratorEntity = $this->configuratorRepository->search(
            $criteria,
            $salesChannelContext->getContext()
        )->getEntities()->first();

        // return it
        return $configuratorEntity;
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
        /** @var ProductEntity $product */
        $product = $this->productRepository->search(
            new Criteria([$id]),
            $salesChannelContext->getContext()
        )->getEntities()->first();

        // return it
        return $product;
    }
}
