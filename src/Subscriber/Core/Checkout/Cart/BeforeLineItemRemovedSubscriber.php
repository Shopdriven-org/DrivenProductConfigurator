<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @package   Shopware\Plugins\Driven\ProductConfigurator
 * @copyright (c) 2022 shopdriven
 */

namespace Driven\ProductConfigurator\Subscriber\Core\Checkout\Cart;

use Driven\ProductConfigurator\DrivenProductConfigurator;
use Driven\ProductConfigurator\Service\Cart\LineItemFactoryService;
use Driven\ProductConfigurator\Service\SelectionService;
use Dvsn\SetConfigurator\Service\Cart\LineItemFactoryServiceInterface;
use Shopware\Core\Checkout\Cart\Event\AfterLineItemRemovedEvent;
use Shopware\Core\Checkout\Cart\Event\BeforeLineItemRemovedEvent;
use Shopware\Core\Checkout\Cart\Event\CartChangedEvent;
use Shopware\Core\Checkout\Cart\Event\CartSavedEvent;
use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedCriteriaEvent;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BeforeLineItemRemovedSubscriber implements EventSubscriberInterface
{

    private EntityRepositoryInterface $drivenConfiguratorRepository;
    private EntityRepositoryInterface $productRepository;
    private lineItemFactoryService $lineItemFactoryService;
    private SelectionService $selectionService;

    public function __construct(EntityRepositoryInterface $drivenConfiguratorRepository,
                                EntityRepositoryInterface $productRepository,
                                lineItemFactoryService    $lineItemFactoryService,
                                SelectionService          $selectionService)
    {
        $this->drivenConfiguratorRepository = $drivenConfiguratorRepository;
        $this->productRepository = $productRepository;
        $this->lineItemFactoryService = $lineItemFactoryService;
        $this->selectionService = $selectionService;
    }

    /**
     * {@inheritDoc}
     *
     */
    public static function getSubscribedEvents(): array
    {
        return [
            BeforeLineItemRemovedEvent::class => 'OnBeforeLineItemRemovedEvent',
        ];
    }

    /**
     * ...
     * @param BeforeLineItemRemovedEvent $event
     */
    public function OnBeforeLineItemRemovedEvent(BeforeLineItemRemovedEvent $event)
    {

        $deletedLineItemId = $event->getLineItem();

        $configurator = $this->getParentProduct($deletedLineItemId->getId(), $event->getSalesChannelContext());

        if ($configurator != null) {
            $foreheadProductId = $configurator->getForehead();
            $backheadProductId = $configurator->getBackhead();

            $this->drivenConfiguratorRepository->delete([
                ["id" => $configurator->getId()]
            ], $event->getContext());

            $this->checkProductStock($foreheadProductId, $backheadProductId, $event);

        }
    }


    /**
     * @param $id
     * @param SalesChannelContext $salesChannelContext
     * @return mixed|null
     */
    private function getParentProduct($id, SalesChannelContext $salesChannelContext)
    {
        return $this->drivenConfiguratorRepository->search(
            (new Criteria())
                ->addFilter(new EqualsFilter('driven_product_configurator.productId', $id)),
            $salesChannelContext->getContext()
        )->first();
    }

    /**
     * @param string $foreheadProductId
     * @param string $backheadProductId
     * @param $event
     * @return void
     */
    private function checkProductStock(string $foreheadProductId, string $backheadProductId, $event)
    {
        foreach ($event->getCart()->getLineItems() as $lineItem) {
            if ($lineItem->getId() == $foreheadProductId) {
                if ($lineItem->getQuantity() > 1) {
                    $lineItem->setQuantity($lineItem->getQuantity() - 1);
                }
            }
            if ($lineItem->getId() == $backheadProductId) {
                if ($lineItem->getQuantity() > 1) {
                    $lineItem->setQuantity($lineItem->getQuantity() - 1);
                }
            }
        }
    }
}
