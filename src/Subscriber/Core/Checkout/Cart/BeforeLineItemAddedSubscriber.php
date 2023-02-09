<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @package   Shopware\Plugins\Driven\ProductConfigurator
 * @copyright (c) 2022 shopdriven
 */

namespace Driven\ProductConfigurator\Subscriber\Core\Checkout\Cart;

use Driven\ProductConfigurator\DrivenProductConfigurator;
use Driven\ProductConfigurator\Service\SelectionServiceInterface;
use Shopware\Core\Checkout\Cart\Event\BeforeLineItemAddedEvent;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BeforeLineItemAddedSubscriber implements EventSubscriberInterface
{

    private EntityRepositoryInterface $productRepository;
    private EntityRepositoryInterface $categoryTranslation;
    private EntityRepositoryInterface $drivenConfigurator;
    private SelectionServiceInterface $selectionService;

    const RACQUET_CATEGORY = "Hölzer";
    const CONFIGURATOR_CATEGORY = "Schläger";
    const TOPPING_CATEGORY = "Beläge";

    public function __construct(
        EntityRepositoryInterface $productRepository,
        EntityRepositoryInterface $categoryTranslation,
        EntityRepositoryInterface $drivenConfigurator,
        SelectionServiceInterface $selectionService
    )
    {
        $this->productRepository = $productRepository;
        $this->categoryTranslation = $categoryTranslation;
        $this->drivenConfigurator = $drivenConfigurator;
        $this->selectionService = $selectionService;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            BeforeLineItemAddedEvent::class => 'GetBeforeLineItemAddedEvent'
        ];
    }

    /**
     * @param BeforeLineItemAddedEvent $event
     * @return void
     */
    public function GetBeforeLineItemAddedEvent(BeforeLineItemAddedEvent $event): void
    {
        $product = $this->getProduct($event->getLineItem()->getId(), $event->getSalesChannelContext());
        $customFields = $product->getCustomFields();
        $productCatId = $product->getCategoryTree()[count($product->getCategoryTree())-1];
        $productCategory = $this->getSelectedCategory($productCatId, $event);

        $breadcrumb = $productCategory->getBreadcrumb();
        if (in_array(self::TOPPING_CATEGORY, $breadcrumb)) {
            $customFields["driven_product_configurator_racquet_option"] = "toppings";
            $this->productRepository->upsert([[
                    'id' => $product->getId(),
                    'customFields' => $customFields
                ]], $event->getContext());
        }
        if (in_array(self::RACQUET_CATEGORY, $breadcrumb) || in_array(self::CONFIGURATOR_CATEGORY, $breadcrumb)) {
            $customFields["driven_product_configurator_racquet_option"] = "racquet";
            $this->productRepository->upsert([[
                    'id' => $product->getId(),
                    'customFields' => $customFields
                ]], $event->getContext());

            if ($this->getParentProduct($event->getLineItem()->getId(), $event->getSalesChannelContext()) === null) {
                $this->selectionService->saveSelection(
                    $event->getLineItem()->getId(), Uuid::fromStringToHex($this->selectionService::KEIN_BELAG), Uuid::fromStringToHex($this->selectionService::KEIN_BELAG), null, $event->getSalesChannelContext()
                );
            }
        }
    }

    /**
     * @param $id
     * @param SalesChannelContext $salesChannelContext
     * @return mixed|null
     */
    private function getParentProduct($id, SalesChannelContext $salesChannelContext)
    {

        /** @var DrivenProductConfigurator $drivenConfigurator */
        return $this->drivenConfigurator->search(
            (new Criteria())
                ->addFilter(new EqualsFilter('driven_product_configurator.productId', $id))
                ->addAssociation('cover.media')
                ->addAssociation('options.group')
                ->addAssociation('customFields'),
            $salesChannelContext->getContext()
        )->first();
    }


    /**
     * @param $id
     * @param SalesChannelContext $salesChannelContext
     * @return mixed|null
     */
    private function getProduct($id, SalesChannelContext $salesChannelContext)
    {
        return $this->productRepository->search(
            (new Criteria())
                ->addFilter(new EqualsFilter('product.id', $id))
                ->addAssociation('customFields'),
            $salesChannelContext->getContext()
        )->first();
    }

    /**
     * ...
     *
     * @param $catId
     * @param BeforeLineItemAddedEvent $event
     * @return mixed|null
     */
    public function getSelectedCategory($catId, BeforeLineItemAddedEvent $event)
    {

        return $this->categoryTranslation->search(
            (new Criteria())
                ->addFilter(new EqualsFilter('category_translation.categoryId', $catId)),
            $event->getContext()
        )->first();
    }
}
