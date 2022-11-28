<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @package   Shopware\Plugins\Driven\ProductConfigurator
 * @copyright (c) 2020 shopdriven
 */

namespace Driven\ProductConfigurator\Core\Checkout\Cart;

use Driven\ProductConfigurator\Core\Checkout\Cart\Error\InvalidSelectionError;
use Driven\ProductConfigurator\Service\Cart\LineItemFactoryServiceInterface;
use Driven\ProductConfigurator\Service\Validator\LineItemValidatorServiceInterface;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartValidatorInterface;
use Shopware\Core\Checkout\Cart\Error\ErrorCollection;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class ConfiguratorCartValidator implements CartValidatorInterface
{
    private LineItemValidatorServiceInterface $lineItemValidatorService;

    public function __construct(
        LineItemValidatorServiceInterface $lineItemValidatorService
    ) {
        $this->lineItemValidatorService = $lineItemValidatorService;
    }

    /**
     * {@inheritDoc}
     */
    public function validate(Cart $cart, ErrorCollection $errorCollection, SalesChannelContext $salesChannelContext): void
    {
        // get every line item which is a configurator
        $lineItems = $cart->getLineItems()->filterType(
            LineItemFactoryServiceInterface::CONFIGURATOR_LINE_ITEM_TYPE
        );

        // do we even have a configurator?
        if ($lineItems->count() === 0) {
            // we dont
            return;
        }

        // loop every line item
        foreach ($lineItems as $lineItem) {
            // validate it
            if ($this->lineItemValidatorService->validate($lineItem, $salesChannelContext) === true) {
                // all good
                continue;
            }

            // add error
            $errorCollection->add(
                new InvalidSelectionError($lineItem->getId())
            );

            // remove configurator
            $cart->getLineItems()->removeElement($lineItem);
        }
    }
}
