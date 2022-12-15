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
        $event->getCart()->addArrayExtension("racquet_counter", (array)$racquets_length);
        $event->getCart()->addArrayExtension("equipment_counter", (array)$equipments_length);
        foreach ($racquets as $racquet) {
            $racquet->addArrayExtension("Equipments", ["items" => $equipments, "length" => $equipments_length]);
        }


    }
}
