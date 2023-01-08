<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @package   Shopware\Plugins\Driven\ProductConfigurator
 * @copyright (c) 2022 shopdriven
 */

namespace Driven\ProductConfigurator\Core\Checkout\Cart;

use Driven\ProductConfigurator\Core\Checkout\Cart\Error\InvalidSelectionError;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartValidatorInterface;
use Shopware\Core\Checkout\Cart\Error\ErrorCollection;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class ConfiguratorCartValidator implements CartValidatorInterface
{

    /**
     * {}
     */
    public function validate(Cart $cart, ErrorCollection $errors, SalesChannelContext $context): void
    {
//        dd($cart->get("driven-product-configurator--parent-id"));
    }
}
