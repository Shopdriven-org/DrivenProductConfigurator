<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @package   Shopware\Plugins\Driven\ProductConfigurator
 * @copyright (c) 2020 shopdriven
 */

namespace Driven\ProductConfigurator\Subscriber\Core\Checkout\Cart;

use Shopware\Core\Checkout\Cart\Event\CartChangedEvent;
use Shopware\Core\Checkout\Cart\Event\CartSavedEvent;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CartSavedSubscriber implements EventSubscriberInterface
{

    public function __construct()
    {
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            CartSavedEvent::class => 'OnCartSavedEvent'
        ];
    }

    /**
     * ...
     *
     * @param CartSavedEvent $event
     */
    public function OnCartSavedEvent(CartSavedEvent $event): void
    {
        $struct = new ArrayStruct();
        foreach ($event->getCart()->getLineItems() as $lineItem) {
            if ($lineItem->getType() === "product" && $lineItem->getPayload()["customFields"]["driven_product_configurator_base_racquet_product"] !== true) {
                $equipment = [
                    "id" => $lineItem->getId(),
                    "name" => $lineItem->getLabel()
                ];
                $struct->addArrayExtension("Equipment", $equipment);
                $lineItem->addExtension("racquetEquipments", $struct);

            }
        }
    }


}
