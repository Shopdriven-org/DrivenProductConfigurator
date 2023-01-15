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
use Shopware\Core\Checkout\Cart\Event\AfterLineItemQuantityChangedEvent;
use Shopware\Core\Checkout\Cart\Event\AfterLineItemRemovedEvent;
use Shopware\Core\Checkout\Cart\Event\BeforeLineItemQuantityChangedEvent;
use Shopware\Core\Checkout\Cart\Event\BeforeLineItemRemovedEvent;
use Shopware\Core\Checkout\Cart\Event\CartChangedEvent;
use Shopware\Core\Checkout\Cart\Event\CartSavedEvent;
use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedCriteriaEvent;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BeforeLineItemQuantityChangedSubscriber implements EventSubscriberInterface
{

    private EntityRepositoryInterface $drivenConfiguratorRepository;
    private SelectionService $selectionService;

    public function __construct(EntityRepositoryInterface $drivenConfiguratorRepository,
                                SelectionService          $selectionService)
    {
        $this->drivenConfiguratorRepository = $drivenConfiguratorRepository;
        $this->selectionService = $selectionService;
    }

    /**
     * {@inheritDoc}
     *
     */
    public static function getSubscribedEvents(): array
    {
        return [
            AfterLineItemQuantityChangedEvent::class => 'OnBeforeLineItemQuantityChangedEvent',
        ];
    }

    /**
     * ...
     * @param AfterLineItemQuantityChangedEvent $event
     */
    public function OnBeforeLineItemQuantityChangedEvent(AfterLineItemQuantityChangedEvent $event)
    {
//        $parentProduct = $event->getItems()[0]["id"];
////        dd($parentProduct);
//        $configurator = $this->selectionService->getParentProduct($parentProduct, $event->getSalesChannelContext());
//        if ($configurator !== null) {
//            $backheadProduct = $this->selectionService->getProduct($configurator->getBackhead(), $event->getSalesChannelContext());
//            $foreheadProduct = $this->selectionService->getProduct($configurator->getForehead(), $event->getSalesChannelContext());
//
//            $this->checkProductStock($foreheadProduct, $backheadProduct, $parentProduct, $event);
//        }


    }


    /**
     * @param ProductEntity $foreheadProduct
     * @param ProductEntity $backheadProduct
     * @param string $parentProduct
     * @param $event
     * @return void
     */
    private function checkProductStock(ProductEntity $foreheadProduct, ProductEntity $backheadProduct, string $parentProduct, $event)
    {
        // TODO:
        foreach ($event->getCart()->getLineItems() as $lineItem) {
            if ($parentProduct === $lineItem->getId()) {
                $configuratorQuantity = $lineItem->getQuantity();
//                    dd($foreheadProduct->getAvailableStock());
                if ($foreheadProduct->getAvailableStock() > $configuratorQuantity) {
                    $lineItem->setQuantity($lineItem->getQuantity() + $configuratorQuantity);
//                    dd($lineItem->getQuantity());
                } else {
                    $lineItem->setQuantity($foreheadProduct->getAvailableStock());
                }
//                dd($backheadProduct->getAvailableStock());
                if ($backheadProduct->getAvailableStock() > $configuratorQuantity) {
                    $lineItem->setQuantity($lineItem->getQuantity() + $configuratorQuantity);
                } else {
                    $lineItem->setQuantity($foreheadProduct->getAvailableStock());
                }
            }
        }
    }


}
