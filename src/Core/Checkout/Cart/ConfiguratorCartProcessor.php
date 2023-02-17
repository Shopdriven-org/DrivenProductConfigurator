<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @package   Shopware\Plugins\Driven\ProductConfigurator
 * @copyright (c) 2022 shopdriven
 */

namespace Driven\ProductConfigurator\Core\Checkout\Cart;

use Driven\ProductConfigurator\Service\Cart\LineItemFactoryService;
use Driven\ProductConfigurator\Service\SelectionService;
use Exception;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartProcessorInterface;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\Price\PercentagePriceCalculator;
use Shopware\Core\Checkout\Cart\Price\QuantityPriceCalculator;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ConfiguratorCartProcessor implements CartProcessorInterface
{
    private QuantityPriceCalculator $quantityPriceCalculator;
    private PercentagePriceCalculator $percentagePriceCalculator;
    private LineItemFactoryService $lineItemFactoryService;
    private SelectionService $selectionService;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        QuantityPriceCalculator   $quantityPriceCalculator,
        PercentagePriceCalculator $percentagePriceCalculator,
        LineItemFactoryService    $lineItemFactoryService,
        SelectionService          $selectionService,
        EventDispatcherInterface  $eventDispatcher
    )
    {
        $this->quantityPriceCalculator = $quantityPriceCalculator;
        $this->percentagePriceCalculator = $percentagePriceCalculator;
        $this->lineItemFactoryService = $lineItemFactoryService;
        $this->selectionService = $selectionService;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {}
     */
    public function process(CartDataCollection $data, Cart $original, Cart $toCalculate, SalesChannelContext $context, CartBehavior $behavior): void
    {
        $montageQuantity = 0;
        $sealingQuantity = 0;
        $configuratorProduct = null;
        $keinBelag = Uuid::fromStringToHex($this->selectionService::KEIN_BELAG);
        foreach ($original->getLineItems() as $lineItem) {
            if ($lineItem->getType() === LineItem::PRODUCT_LINE_ITEM_TYPE) {
                $options = $lineItem->getPayload()["customFields"];
                if (isset($options["driven_product_configurator_racquet_option"])) {
                    if ($options["driven_product_configurator_racquet_option"] === "racquet") {

                        $configuratorProduct = $this->selectionService->getParentProduct($lineItem->getId(), $context);
                        $parentProduct = $this->selectionService->getProduct($lineItem->getId(), $context);
                        if ($configuratorProduct != null) {
                            $foreheadProduct = $configuratorProduct->getForehead();
                            $backheadProduct = $configuratorProduct->getBackhead();
                            $sealingSelection = $configuratorProduct->getSealing();

                            if ($foreheadProduct != $keinBelag || $backheadProduct != $keinBelag) {
                                $montageQuantity += $lineItem->getQuantity();
                                if ($lineItem->getQuantity() > 1) {
                                    //TODO
//                                    $this->ConfiguratorChanges($parentProduct, $lineItem, $toCalculate, $context);
                                }
                            }
                            if ($foreheadProduct != $keinBelag && $backheadProduct != $keinBelag) {
                                $montageQuantity += $lineItem->getQuantity();
                            }
                            if ($sealingSelection > 0) {
                                $sealingQuantity += $sealingSelection;
                            }
                            if ($lineItem->getQuantity() < $configuratorProduct->getSealing()) {
                                $sealingQuantity = $lineItem->getQuantity();
                            }
                        }
                    }
                }
            }
        }
        if ($configuratorProduct != null && $montageQuantity > 0) {
            try {
                $montageLineItem = $this->lineItemFactoryService->createMontageLineItem($montageQuantity);
                $toCalculate->add($montageLineItem);
            } catch (Exception $exception) {
                dd($exception);
            }
        }
        if ($sealingQuantity != 0) {
            try {
                $sealingLineItem = $this->lineItemFactoryService->createSealingLineItem($sealingQuantity);
                $toCalculate->add($sealingLineItem);
            } catch (Exception $exception) {
                dd($exception);
            }
        }
    }

    /**
     * @param ProductEntity|null $parentProduct
     * @param LineItem $lineItem
     * @param Cart $toCalculate
     * @param SalesChannelContext $context
     * @return void
     */
    private function ConfiguratorChanges(?ProductEntity $parentProduct, LineItem $lineItem, Cart $toCalculate, SalesChannelContext $context)
    {
      try {
            if ($lineItem->getExtensions()["Equipments"]["selection"]["backheadSelection"] != Uuid::fromStringToHex($this->selectionService::KEIN_BELAG) || $lineItem->getExtensions()["Equipments"]["selection"]["foreheadSelection"] != Uuid::fromStringToHex($this->selectionService::KEIN_BELAG)) {
                $configurator = $this->lineItemFactoryService->createProductConfigurator($parentProduct, $lineItem);
                $this->selectionService->saveSelection(
                    $lineItem->getId(), Uuid::fromStringToHex($this->selectionService::KEIN_BELAG), Uuid::fromStringToHex($this->selectionService::KEIN_BELAG), "", $context
                );
                $lineItem->setQuantity(1);
                $toCalculate->add($configurator);
            }
        } catch (Exception $exception) {
            dd($exception);
        }
    }
}
