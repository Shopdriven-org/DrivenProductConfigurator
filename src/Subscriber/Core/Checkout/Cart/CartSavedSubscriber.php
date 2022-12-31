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
use Shopware\Core\Checkout\Cart\Event\CartChangedEvent;
use Shopware\Core\Checkout\Cart\Event\CartSavedEvent;
use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedCriteriaEvent;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CartSavedSubscriber implements EventSubscriberInterface
{

    private EntityRepositoryInterface $drivenConfiguratorRepository;
    private EntityRepositoryInterface $productRepository;

    public function __construct(EntityRepositoryInterface $drivenConfiguratorRepository,
                                EntityRepositoryInterface  $productRepository)
    {
        $this->drivenConfiguratorRepository = $drivenConfiguratorRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * {@inheritDoc}
     *
     */
    public static function getSubscribedEvents(): array
    {
        return [
            CartSavedEvent::class => 'OnCartSavedEvent',
            CheckoutOrderPlacedCriteriaEvent::class => 'OnCheckoutOrderPlacedCriteriaEvent'
        ];
    }

    /**
     * ...
     * @param CheckoutOrderPlacedCriteriaEvent $event
     */
    public function OnOrderPlacedEvent(CheckoutOrderPlacedCriteriaEvent $event)
    {
//        dd($event);
    }

    /**
     * ...
     * @param CartSavedEvent $event
     */
    public function OnCartSavedEvent(CartSavedEvent $event): void
    {
        $equipments = [];
        $racquets = [];
        $equipments_length = 0;
        $racquets_length = 0;

        foreach ($event->getCart()->getLineItems() as $lineItem) {

            if ($lineItem->getType() === "product") {
                $options = $lineItem->getPayload()["customFields"];
                if (isset($options["driven_product_configurator_racquet_option"])) {
                    if ($options["driven_product_configurator_racquet_option"] === "toppings") {
                        array_push($equipments, $lineItem);
                        $equipments_length++;
                    }
                    if ($options["driven_product_configurator_racquet_option"] === "racquet") {
                        array_push($racquets, $lineItem);
                        $racquets_length++;
                    }
                }
            }
        }
        if (count($racquets) !== 0) {
            $event->getCart()->addArrayExtension("racquet_counter", (array)$racquets_length);
            $event->getCart()->addArrayExtension("equipment_counter", (array)$equipments_length);

            foreach ($racquets as $racquet) {
                $parentProduct = $this->getParentProduct($racquet->getId(), $event->getSalesChannelContext());

                if ($parentProduct == null) {
                    $foreheadProduct = "";
                    $backheadProduct = "";
                    $sealing = "";
                }else{
                    $foreheadProduct = $this->getChildrenProduct($parentProduct->getForehead(), $event->getSalesChannelContext());
                    $backheadProduct = $this->getChildrenProduct($parentProduct->getBackhead(), $event->getSalesChannelContext());
                    $sealing = $parentProduct->getSealing();
                }
                $racquet->addArrayExtension("Equipments",
                    ["items" => $equipments, "length" => $equipments_length,
                        "selection" =>  ["foreheadProduct" => $foreheadProduct, "backheadProduct" => $backheadProduct, "sealing" => $sealing]
                    ]
                );
            }
        }


    }


    private function getParentProduct($id, SalesChannelContext $salesChannelContext)
    {
//        dd($id);
        return $this->drivenConfiguratorRepository->search(
            (new Criteria())
                ->addFilter(new EqualsFilter('driven_product_configurator.productId', $id)),
            $salesChannelContext->getContext()
        )->first();
    }

    private function getChildrenProduct($id, SalesChannelContext $salesChannelContext)
    {

        return $this->productRepository->search(
            (new Criteria())
                ->addFilter(new EqualsFilter('product.id', $id)),
            $salesChannelContext->getContext()
        )->first();
    }

}
