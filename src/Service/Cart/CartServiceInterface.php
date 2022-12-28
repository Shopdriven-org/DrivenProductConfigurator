<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @package   Shopware\Plugins\DrivenProductConfigurator
 * @copyright (c) 2022 shopdriven
 */

namespace Driven\ProductConfigurator\Service\Cart;

use Shopware\Core\Checkout\Cart\Cart as CoreCart;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

interface CartServiceInterface
{
    /**
     * ...
     *
     * @param string $productId
     * @param int $quantity
     * @param array $selection
     * @param CoreCart $cart
     * @param SalesChannelContext $salesChannelContext
     */
    public function addToCart(string $productId, int $quantity, array $selection, CoreCart $cart, SalesChannelContext $salesChannelContext): void;
}
