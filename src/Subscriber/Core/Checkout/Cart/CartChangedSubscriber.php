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
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CartChangedSubscriber implements EventSubscriberInterface
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
            CartChangedEvent::class => 'onCartChange'
        ];
    }

    /**
     * ...
     *
     * @param CartChangedEvent $event
     */
    public function onCartChange(CartChangedEvent $event): void
    {
        //dd($event->getCart()->getLineItems()->getPayload());
//        foreach ($event->getCart()->getLineItems()->getPayload() as $item) {
//            dd($item["customFields"]);
//        }
    }


}
