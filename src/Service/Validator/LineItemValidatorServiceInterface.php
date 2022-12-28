<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @package   Shopware\Plugins\DrivenProductConfigurator
 * @copyright (c) 2022 shopdriven
 */

namespace Driven\ProductConfigurator\Service\Validator;

use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

interface LineItemValidatorServiceInterface
{
    /**
     * ...
     *
     * @param LineItem $lineItem
     * @param SalesChannelContext $salesChannelContext
     *
     * @return bool
     */
    public function validate(LineItem $lineItem, SalesChannelContext $salesChannelContext): bool;
}
