<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @package   Shopware\Plugins\DrivenProductConfigurator
 * @copyright (c) 2022 shopdriven
 */

namespace Driven\ProductConfigurator\Core\Content\Configurator\Aggregate\ConfiguratorSelection;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class ConfiguratorSelectionCollection extends EntityCollection
{
    /**
     * {@inheritDoc}
     */
    protected function getExpectedClass(): string
    {
        return ConfiguratorSelectionEntity::class;
    }
}
