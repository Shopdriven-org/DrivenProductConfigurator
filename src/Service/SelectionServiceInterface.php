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
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityWrittenContainerEvent;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

interface SelectionServiceInterface
{
    /**
     * @param string $id
     * @param ConfiguratorEntity $configurator
     * @param SalesChannelContext $salesChannelContext
     *
     * @return array
     */

    public const KEIN_BELAG = 'DC1B7FFCB8D64DD2AE574A21F34F6FC5';
    public function getSelectionByLineItemId(string $id, ConfiguratorEntity $configurator, SalesChannelContext $salesChannelContext): array;

    /**
     * @param string $productId
     * @param ?string $forehead
     * @param ?string $backhead
     * @param ?int $sealing
     * @param SalesChannelContext $salesChannelContext
     *
     * @return EntityWrittenContainerEvent
     */
    public function saveSelection(string $productId, ?string $forehead, ?string $backhead, ?int $sealing, SalesChannelContext $salesChannelContext): EntityWrittenContainerEvent;


    /**
     * @param string $productId
     * @param ?string $forehead
     * @param ?string $backhead
     * @param ?int $sealing
     * @param SalesChannelContext $salesChannelContext
     *
     * @return EntityWrittenContainerEvent|void
     */
    public function updateSelection(string $productId, ?string $forehead, ?string $backhead, ?int $sealing, SalesChannelContext $salesChannelContext);
}
