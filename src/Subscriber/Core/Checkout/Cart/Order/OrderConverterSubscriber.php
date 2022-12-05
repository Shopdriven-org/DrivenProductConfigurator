<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @package   Shopware\Plugins\Driven\ProductConfigurator
 * @copyright (c) 2020 shopdriven
 */

namespace Driven\ProductConfigurator\Subscriber\Core\Checkout\Cart\Order;

use Shopware\Core\Checkout\Cart\Order\CartConvertedEvent;
use Shopware\Core\Checkout\Cart\Price\QuantityPriceCalculator;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderConverterSubscriber implements EventSubscriberInterface
{
    private QuantityPriceCalculator $quantityPriceCalculator;
    private SystemConfigService $systemConfigService;

    public function __construct(
        QuantityPriceCalculator $quantityPriceCalculator,
        SystemConfigService $systemConfigService
    ) {
        $this->quantityPriceCalculator = $quantityPriceCalculator;
        $this->systemConfigService = $systemConfigService;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            CartConvertedEvent::class => 'onCartConverted'
        ];
    }

    /**
     * ...
     *
     * @param CartConvertedEvent $event
     */
    public function onCartAction(CartConvertedEvent $event): void
    {
        //dd($event->getCart());
    }


}
