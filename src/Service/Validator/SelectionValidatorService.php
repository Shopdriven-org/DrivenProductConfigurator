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

class SelectionValidatorService implements SelectionValidatorServiceInterface
{
    /**
     * {@inheritDoc}
     */
    public function validate(ConfiguratorEntity $configurator, array $selection, SalesChannelContext $salesChannelContext): bool
    {
        // TODO
        // everything is fine
        return true;
    }
}
