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

    public function __construct(
    ) {
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
        //dd($event->getCart()->getLineItems()->getPayload());
        foreach ($event->getCart()->getLineItems()->getPayload() as $item) {
            //dd($item["customFields"]["driven_product_configurator_base_racquet_product"]);
            if (isset($item["customFields"]["driven_product_configurator_base_racquet_product"]) && !isset($item["customFields"]["driven_product_configurator_product_racquet_type"])){
                $event->getCart()->getLineItems()->addExtension("missingEquioments", new ArrayStruct());
            }
        }
    }


}
