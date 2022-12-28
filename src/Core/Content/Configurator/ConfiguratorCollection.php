<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @package   Shopware\Plugins\DrivenProductConfigurator
 * @copyright (c) 2022 shopdriven
 */

namespace Driven\ProductConfigurator\Core\Content\Configurator;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class ConfiguratorCollection extends EntityCollection
{
    /**
     * {@inheritDoc}
     */
    protected function getExpectedClass(): string
    {
        return ConfiguratorEntity::class;
    }
}
