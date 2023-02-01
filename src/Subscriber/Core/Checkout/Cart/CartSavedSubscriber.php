<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @package   Shopware\Plugins\Driven\ProductConfigurator
 * @copyright (c) 2022 shopdriven
 */

namespace Driven\ProductConfigurator\Subscriber\Core\Checkout\Cart;

use Driven\ProductConfigurator\Service\Cart\LineItemFactoryService;
use Dvsn\SetConfigurator\Service\Cart\LineItemFactoryServiceInterface;
use Shopware\Core\Checkout\Cart\Event\CartSavedEvent;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CartSavedSubscriber implements EventSubscriberInterface
{
    private EntityRepositoryInterface $drivenConfiguratorRepository;
    private EntityRepositoryInterface $productRepository;
    private lineItemFactoryService $lineItemFactoryService;
    public const KEIN_BELAG = 'DC1B7FFCB8D64DD2AE574A21F34F6FC5';
    public function __construct(EntityRepositoryInterface $drivenConfiguratorRepository,
                                EntityRepositoryInterface $productRepository,
                                lineItemFactoryService    $lineItemFactoryService)
    {
        $this->drivenConfiguratorRepository = $drivenConfiguratorRepository;
        $this->productRepository = $productRepository;
        $this->lineItemFactoryService = $lineItemFactoryService;
    }
    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            CartSavedEvent::class => 'OnCartSavedEvent',
        ];
    }

    /**
     * @param CartSavedEvent $event
     */
    public function OnCartSavedEvent(CartSavedEvent $event): void
    {
        $equipments = [];
        $racquets = [];
        $configurators = [];
        $equipments_length = 0;
        $racquets_length = 0;
        // TODO $configurators_lenght
        $configurators_lenght = 0;
        $foreheadProduct = "";
        $backheadProduct = "";
        $sealing = "";

        $no_choice = [
            "id" => Uuid::fromStringToHex(self::KEIN_BELAG),
            "label" => "kein Belag"
        ];
        foreach ($event->getCart()->getLineItems() as $lineItem) {
            if ($lineItem->getType() == LineItemFactoryServiceInterface::CONFIGURATOR_LINE_ITEM_TYPE) {
                array_push($configurators, $lineItem);
                $racquets_length++;
            }
            if ($lineItem->getType() === LineItem::PRODUCT_LINE_ITEM_TYPE) {
                $options = $lineItem->getPayload()["customFields"];
                if (isset($options["driven_product_configurator_racquet_option"])) {
                    if ($options["driven_product_configurator_racquet_option"] === "toppings") {
                        array_push($equipments, $lineItem);
                        $equipments_length++;
                    }
                    if ($options["driven_product_configurator_racquet_option"] === "racquet") {
                        array_push($racquets, $lineItem);
                        $racquets_length++;
                    }
                }
            }
        }
        if (count($racquets) !== 0) {
            $event->getCart()->addArrayExtension("racquet_counter", (array)$racquets_length);
            $event->getCart()->addArrayExtension("equipment_counter", (array)$equipments_length);
            foreach ($racquets as $racquet) {
                $sealingQuantity = $racquet->getQuantity();
                $parentProduct = $this->getParentProduct($racquet->getId(), $event->getSalesChannelContext());

                if ($parentProduct !== null) {
                    $foreheadProduct = $this->getChildrenProduct($parentProduct->getForehead(), $event->getSalesChannelContext());
                    $backheadProduct = $this->getChildrenProduct($parentProduct->getBackhead(), $event->getSalesChannelContext());
                    $sealing = $parentProduct->getSealing();
                }
                if ($sealing != "") {
                    $event->getCart()->getLineItems()->add(
                        $this->lineItemFactoryService->createSealingLineItem(
                            $this->getChildrenProduct($racquet->getId(), $event->getSalesChannelContext()), $sealing, true, $event->getSalesChannelContext()
                        )
                    );
                } else {
                    $event->getCart()->getLineItems()->removeElement(
                        $this->lineItemFactoryService->createSealingLineItem(
                            $this->getChildrenProduct($racquet->getId(), $event->getSalesChannelContext()), $sealing, true, $event->getSalesChannelContext()
                        )
                    );
                }
                $sameSides = false;
                if ($foreheadProduct != null && $backheadProduct != null) {
                    if ($foreheadProduct->variation[0]["option"] == $backheadProduct->variation[0]["option"]) {
                        $sameSides = true;
                    }
                }
                $foreheadEquipments = $equipments;
                $backheadEquipments = $equipments;
                $foreheadSelection = "";
                $backheadSelection = "";
                $sealingSelection = "";

                if (isset($parentProduct)) {
                    $foreheadSelection = $parentProduct->getForehead();
                }
                if (isset($parentProduct)) {
                    $backheadSelection = $parentProduct->getBackhead();
                }
                if (isset($parentProduct)) {
                    $sealingSelection = $parentProduct->getSealing();
                }

                $this->setRacquetConfiguratorQuantity($racquet, $backheadSelection, $foreheadSelection);

                for ($i = 0; $i <= count($equipments) - 1; $i++) {
                    if ($foreheadEquipments[$i]->getId() == $backheadSelection) {
                        unset($foreheadEquipments[$i]);
                    }
                    if ($backheadEquipments[$i]->getId() == $foreheadSelection) {
                        unset($backheadEquipments[$i]);
                    }
                }

                array_unshift($backheadEquipments, $no_choice);
                array_unshift($foreheadEquipments, $no_choice);

                $racquet->addArrayExtension("Equipments",
                    ["items" => $equipments,
                        "back" => ["backheadEquipments" => $backheadEquipments, "length" => count($backheadEquipments)],
                        "front" => ["foreheadEquipments" => $foreheadEquipments, "length" => count($foreheadEquipments)],
                        "sealing" => ["length" => $sealingQuantity],
                        "length" => $equipments_length,
                        "selection" =>
                            ["parentId" => $racquet->getId(),
                                "foreheadProduct" => $foreheadProduct, "foreheadSelection" => $foreheadSelection,
                                "backheadProduct" => $backheadProduct, "backheadSelection" => $backheadSelection,
                                "sealing" => $sealing, "sealingSelection" => $sealingSelection
                            ],
                        "sameSides" => $sameSides
                    ]
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
        return $this->drivenConfiguratorRepository->search(
            (new Criteria())
                ->addFilter(new EqualsFilter('driven_product_configurator.productId', $id)),
            $salesChannelContext->getContext()
        )->first();
    }

    /**
     * @param $id
     * @param SalesChannelContext $salesChannelContext
     * @return mixed|null
     */
    private function getChildrenProduct($id, SalesChannelContext $salesChannelContext)
    {

        return $this->productRepository->search(
            (new Criteria())
                ->addFilter(new EqualsFilter('product.id', $id))
                ->addAssociation('options.group'),
            $salesChannelContext->getContext()
        )->first();
    }

    /**
     * @param LineItem $racquet
     * @param string $backheadSelection
     * @param string $foreheadSelection
     * @return void
     */
    private function setRacquetConfiguratorQuantity(LineItem $racquet, string $backheadSelection, string $foreheadSelection)
    {
        if ($backheadSelection != Uuid::fromStringToHex(self::KEIN_BELAG) || $foreheadSelection != Uuid::fromStringToHex(self::KEIN_BELAG)) {
            $racquet->setStackable(false);
        } else {
            $racquet->setStackable(true);
        }
    }

}
