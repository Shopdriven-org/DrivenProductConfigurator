<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @package   Shopware\Plugins\Driven\ProductConfigurator
 * @copyright (c) 2020 shopdriven
 */

namespace Driven\ProductConfigurator\Subscriber\Core\Checkout\Cart;

use Shopware\Core\Checkout\Cart\Event\AfterLineItemAddedEvent;
use Shopware\Core\Checkout\Cart\Event\CartChangedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AfterLineItemAddedSubscriber implements EventSubscriberInterface
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
            AfterLineItemAddedEvent::class => 'OnAfterLineItemAdded'
        ];
    }

    /**
     * ...
     *
     * @param AfterLineItemAddedEvent $event
     */
    public function OnAfterLineItemAdded(AfterLineItemAddedEvent $event): void
    {
//        foreach ($event->getCart()->getLineItems()->getElements() as $element) {
//            dd($element->getPayload());
//        }
    }


}
