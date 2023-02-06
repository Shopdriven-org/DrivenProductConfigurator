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
use Driven\ProductConfigurator\Service\Cart\LineItemFactoryServiceInterface;
use Driven\ProductConfigurator\Service\SelectionService;
use Exception;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartProcessorInterface;
use Shopware\Core\Checkout\Cart\Event\CartSavedEvent;
use Shopware\Core\Checkout\Cart\Exception\MissingLineItemPriceException;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Cart\Price\PercentagePriceCalculator;
use Shopware\Core\Checkout\Cart\Price\QuantityPriceCalculator;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\PercentagePriceDefinition;
use Shopware\Core\Checkout\Cart\Price\Struct\PriceCollection;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
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
                if ($options["driven_product_configurator_racquet_option"] === "racquet") {
                    $configuratorProduct = $this->selectionService->getParentProduct($lineItem->getId(), $context);
                    if ($configuratorProduct != null) {
                        $foreheadProduct = $configuratorProduct->getForehead();
                        $backheadProduct = $configuratorProduct->getBackhead();
                        $sealingSelection = $configuratorProduct->getSealing();

                        if ($foreheadProduct != $keinBelag || $backheadProduct != $keinBelag){
                            $montageQuantity += $lineItem->getQuantity();
                        }
                        if ($foreheadProduct != $keinBelag && $backheadProduct != $keinBelag ){
                            $montageQuantity = 2 * $lineItem->getQuantity();
                        }
                        if ($foreheadProduct == $keinBelag && $backheadProduct == $keinBelag){
                            $montageQuantity = 0;
                        }
                        if ($sealingSelection > 0){
                            $sealingQuantity += $sealingSelection;
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
                $sealingLineItem = $this->lineItemFactoryService->createSealingLineItem($sealingQuantity, $context);
                $toCalculate->add($sealingLineItem);

            } catch (Exception $exception) {
                dd($exception);
            }
        }
    }

}
