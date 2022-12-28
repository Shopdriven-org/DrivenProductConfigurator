<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @package   Shopware\Plugins\DrivenProductConfigurator
 * @copyright (c) 2022 shopdriven
 */

namespace Driven\ProductConfigurator\Service;

use Driven\ProductConfigurator\Core\Content\Configurator\ConfiguratorEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

interface SelectionServiceInterface
{
    /**
     * ...
     *
     * @param string $id
     * @param ConfiguratorEntity $configurator
     * @param SalesChannelContext $salesChannelContext
     *
     * @return array
     */
    public function getSelectionByLineItemId(string $id, ConfiguratorEntity $configurator, SalesChannelContext $salesChannelContext): array;

    /**
     * ...
     *
     * @param string $key
     * @param ConfiguratorEntity $configurator
     * @param SalesChannelContext $salesChannelContext
     *
     * @return array
     */
    public function getSelectionByKey(string $key, ConfiguratorEntity $configurator, SalesChannelContext $salesChannelContext): array;

    /**
     * ...
     *
     * @param string $productId
     * @param string  $configuratorId
     * @param array  $selection
     * @param SalesChannelContext $salesChannelContext
     *
     * @return string
     */
    public function saveSelection(string $productId, string $configuratorId, array $selection, SalesChannelContext $salesChannelContext): string;
}
