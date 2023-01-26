<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @package   Shopware\Plugins\Driven\ProductConfigurator
 * @copyright (c) 2022 shopdriven
 */

namespace Driven\ProductConfigurator\Subscriber\Core\Checkout\Cart;

use Shopware\Core\Checkout\Cart\Event\BeforeLineItemRemovedEvent;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BeforeLineItemRemovedSubscriber implements EventSubscriberInterface
{

    private EntityRepositoryInterface $drivenConfiguratorRepository;

    public function __construct(EntityRepositoryInterface $drivenConfiguratorRepository)
    {
        $this->drivenConfiguratorRepository = $drivenConfiguratorRepository;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            BeforeLineItemRemovedEvent::class => 'OnBeforeLineItemRemovedEvent',
        ];
    }

    /**
     * @param BeforeLineItemRemovedEvent $event
     */
    public function OnBeforeLineItemRemovedEvent(BeforeLineItemRemovedEvent $event)
    {

        $deletedLineItemId = $event->getLineItem();

        $configurator = $this->getParentProduct($deletedLineItemId->getId(), $event->getSalesChannelContext());

        if ($configurator != null) {
            $foreheadProductId = $configurator->getForehead();
            $backheadProductId = $configurator->getBackhead();

            $this->drivenConfiguratorRepository->delete([
                ["id" => $configurator->getId()]
            ], $event->getContext());

            $this->checkProductStock($foreheadProductId, $backheadProductId, $event);
        }
    }

    /**
     * @param $id
     * @param SalesChannelContext $salesChannelContext
     * @return mixed|null
     */
    private function getParentProduct($id, SalesChannelContext $salesChannelContext)
    {
        return $this->drivenConfiguratorRepository->search(
            (new Criteria())
                ->addFilter(new EqualsFilter('driven_product_configurator.productId', $id)),
            $salesChannelContext->getContext()
        )->first();
    }

    /**
     * @param string $foreheadProductId
     * @param string $backheadProductId
     * @param $event
     * @return void
     */
    private function checkProductStock(string $foreheadProductId, string $backheadProductId, $event)
    {
        foreach ($event->getCart()->getLineItems() as $lineItem) {
            if ($lineItem->getId() == $foreheadProductId) {
                if ($lineItem->getQuantity() > 1) {
                    $lineItem->setQuantity(1);
                }
            }
            if ($lineItem->getId() == $backheadProductId) {
                if ($lineItem->getQuantity() > 1) {
                    $lineItem->setQuantity(1);
                }
            }
        }
    }
}
