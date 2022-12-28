<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @package   Shopware\Plugins\DrivenProductConfigurator
 * @copyright (c) 2022 shopdriven
 */

namespace Driven\ProductConfigurator\Service\Validator;

use Driven\ProductConfigurator\Core\Content\Configurator\ConfiguratorEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

interface SelectionValidatorServiceInterface
{
    /**
     * ...
     *
     * @param ConfiguratorEntity $configurator
     * @param array $selection
     * @param SalesChannelContext $salesChannelContext
     *
     * @return bool
     */
    public function validate(ConfiguratorEntity $configurator, array $selection, SalesChannelContext $salesChannelContext): bool;
}
