<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @package   Shopware\Plugins\Driven\ProductConfigurator
 * @copyright (c) 2020 shopdriven
 */

namespace Driven\ProductConfigurator\Core\Checkout\Cart;

use Driven\ProductConfigurator\Core\Checkout\Cart\Error\InvalidComponentQuantityReduceQuantityError;
use Driven\ProductConfigurator\Core\Checkout\Cart\Error\InvalidComponentQuantityRemoveConfiguratorError;
use Driven\ProductConfigurator\Core\Checkout\Cart\Error\InvalidConfiguratorQuantityReduceQuantityError;
use Driven\ProductConfigurator\Core\Checkout\Cart\Error\InvalidConfiguratorQuantityRemoveConfiguratorError;
use Driven\ProductConfigurator\Core\Checkout\Cart\Error\InvalidSelectionError;
use Driven\ProductConfigurator\Core\Content\Configurator\Aggregate\ConfiguratorStream\ConfiguratorStreamEntity;
use Driven\ProductConfigurator\Core\Content\Configurator\ConfiguratorEntity;
use Driven\ProductConfigurator\Service\Cart\LineItemFactoryServiceInterface;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartDataCollectorInterface;
use Shopware\Core\Checkout\Cart\Delivery\Struct\DeliveryInformation;
use Shopware\Core\Checkout\Cart\Delivery\Struct\DeliveryTime;
use Shopware\Core\Checkout\Cart\Error\ErrorCollection;
use Shopware\Core\Checkout\Cart\LineItem\CartDataCollection;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItem\LineItemCollection;
use Shopware\Core\Checkout\Cart\LineItem\QuantityInformation;
use Shopware\Core\Checkout\Cart\Price\Struct\CalculatedPrice;
use Shopware\Core\Checkout\Cart\Price\Struct\PercentagePriceDefinition;
use Shopware\Core\Checkout\Cart\Price\Struct\QuantityPriceDefinition;
use Shopware\Core\Content\Product\Cart\ProductCartProcessor;
use Shopware\Core\Content\Product\SalesChannel\Price\AbstractProductPriceCalculator;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\DeliveryTime\DeliveryTimeEntity;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepositoryInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;

class ConfiguratorCartDataCollector implements CartDataCollectorInterface
{
    private EntityRepositoryInterface $configuratorRepository;
    private SalesChannelRepositoryInterface $salesChannelProductRepository;
    private AbstractProductPriceCalculator $productPriceCalculator;
    private SystemConfigService $systemConfigService;

    public function __construct(
        EntityRepositoryInterface $configuratorRepository,
        SalesChannelRepositoryInterface $salesChannelProductRepository,
        AbstractProductPriceCalculator $productPriceCalculator,
        SystemConfigService $systemConfigService
    ) {
        $this->configuratorRepository = $configuratorRepository;
        $this->salesChannelProductRepository = $salesChannelProductRepository;
        $this->productPriceCalculator = $productPriceCalculator;
        $this->systemConfigService = $systemConfigService;
    }

    /**
     * {@inheritDoc}
     */
    public function collect(CartDataCollection $data, Cart $original, SalesChannelContext $salesChannelContext, CartBehavior $behavior): void
    {
        // do we want to skip recalculation?
        if ($behavior->hasPermission(ProductCartProcessor::SKIP_PRODUCT_RECALCULATION)) {
            // skip it
            return;
        }

        // get every line item which is a configurator
        $lineItems = $original->getLineItems()->filterType(
            LineItemFactoryServiceInterface::CONFIGURATOR_LINE_ITEM_TYPE
        );

        // do we even have a configurator?
        if ($lineItems->count() === 0) {
            // we dont
            return;
        }

        // get every product from the configurator
        $products = $this->getProducts(
            $lineItems,
            $salesChannelContext
        );

        // check limited stock?!
        if ($this->systemConfigService->get('DvsnSetConfigurator.config.cartCheckLimitedStock', $salesChannelContext->getSalesChannel()->getId()) === true) {
            // do it and drop configurators
            $this->validateMaxPurchase($original, $original->getErrors(), $products);
        }

        // loop every configurator
        foreach ($lineItems as $lineItem) {
            // the product is no longer available?! might have been removed within administration
            if (!isset($products[$lineItem->getReferencedId()]) || !$products[$lineItem->getReferencedId()] instanceof SalesChannelProductEntity) {
                // add error
                $original->getErrors()->add(
                    new InvalidSelectionError($lineItem->getId())
                );

                // we have to remove the configurator itself
                $original->getLineItems()->removeElement($lineItem);
                continue;
            }

            // get the configurator product
            $product = $products[$lineItem->getReferencedId()];

            // set delivery and quantity information
            $lineItem->setDeliveryInformation($this->getDeliveryInformation($lineItem, $product, $salesChannelContext));
            $lineItem->setQuantityInformation($this->getQuantityInformation($lineItem, $product, $salesChannelContext));

            // get the configurator itself
            $configurator = $this->getConfigurator(
                $lineItem->getPayloadValue('dvsnSetConfiguratorId'),
                $salesChannelContext
            );

            // create an empty definition first
            $definition = new QuantityPriceDefinition(
                0,
                $salesChannelContext->buildTaxRules($product->getTaxId()),
                $lineItem->getQuantity()
            );

            // and set this as price
            $lineItem->setPriceDefinition(
                $definition
            );

            $prices = [
                'total' => [],
                'children' => []
            ];

            // loop every child to get those prices
            foreach ($lineItem->getChildren() as $child) {
                // set defaults
                $this->setChildDefaults($child);

                // the product is no longer available?! might have been removed within administration
                if (!isset($products[$child->getReferencedId()]) || !$products[$child->getReferencedId()] instanceof SalesChannelProductEntity) {
                    // add error
                    $original->getErrors()->add(
                        new InvalidSelectionError($lineItem->getId())
                    );

                    // we have to remove the configurator itself
                    $original->getLineItems()->removeElement($lineItem);
                    continue;
                }

                // get the product
                $product = $products[$child->getReferencedId()];

                // is this product free-of-charge?
                if ($this->isFree($child, $configurator)) {
                    // create a free-of-charge definition
                    $definition = new QuantityPriceDefinition(
                        0,
                        $salesChannelContext->buildTaxRules($product->getTaxId()),
                        $child->getQuantity()
                    );

                    // set it
                    $child->setPriceDefinition($definition);

                    // next child
                    continue;
                }

                // is this a percental surcharge?
                if ($child->getPayloadValue('dvsnSetConfiguratorPercentalSurcharge')['status'] === true) {
                    // set definition
                    $definition = new PercentagePriceDefinition(
                        (float) $child->getPayloadValue('dvsnSetConfiguratorPercentalSurcharge')['value']
                    );

                    // set price definition
                    $child->setPriceDefinition($definition);

                    // next child
                    continue;
                }

                $this->productPriceCalculator->calculate(
                    [$product],
                    $salesChannelContext
                );

                $price = $this->getCalculatedProductPrice(
                    $product,
                    $child->getQuantity() * $lineItem->getQuantity()
                );

                $definition = new QuantityPriceDefinition(
                    $price->getUnitPrice() * (1 - ($configurator->getRebate() / 100)),
                    $salesChannelContext->buildTaxRules($product->getTaxId()),
                    $child->getQuantity()
                );

                $child->setPriceDefinition($definition);

                $child->setPayloadValue('dvsnSetConfiguratorUnitPrice', $price->getUnitPrice() * (1 - ($configurator->getRebate() / 100)));

                if (!isset($prices['total'][$product->getTaxId()])) {
                    $prices['total'][$product->getTaxId()] = 0.0;
                }

                $prices['total'][$product->getTaxId()] += ($price->getUnitPrice() * (1 - ($configurator->getRebate() / 100))) * $child->getQuantity();

                $prices['children'][$product->getId()] = [
                    'taxId' => $product->getTaxId(),
                    'price' => $price->getUnitPrice() * (1 - ($configurator->getRebate() / 100))
                ];
            }

            $lineItem->setPayloadValue('dvsnSetConfiguratorPrices', $prices);
        }
    }

    /**
     * ...
     *
     * @param SalesChannelProductEntity $product
     * @param int $quantity
     *
     * @return CalculatedPrice
     */
    private function getCalculatedProductPrice(SalesChannelProductEntity $product, int $quantity): CalculatedPrice
    {
        if ($product->getCalculatedPrices()->count() === 0) {
            return $product->getCalculatedPrice();
        }

        $price = $product->getCalculatedPrice();

        foreach ($product->getCalculatedPrices() as $price) {
            if ($quantity <= $price->getQuantity()) {
                break;
            }
        }

        return $price;
    }

    /**
     * Old cart entries may have invalid data before an update.
     * This method makes sure that every child line item has valid payload.
     *
     * @param LineItem $child
     */
    private function setChildDefaults(LineItem $child): void
    {
        // set valid percental surcharge
        if (!$child->hasPayloadValue('dvsnSetConfiguratorPercentalSurcharge')) {
            // set defaults
            $child->setPayloadValue('dvsnSetConfiguratorPercentalSurcharge', [
                'status' => false,
                'value' => 0
            ]);
        }
    }

    /**
     * ...
     *
     * @param LineItem $child
     * @param ConfiguratorEntity $configurator
     *
     * @return bool
     */
    private function isFree(LineItem $child, ConfiguratorEntity $configurator): bool
    {
        // is this the parent which should be free-of-charge?
        if ($child->getType() === LineItemFactoryServiceInterface::PRODUCT_PARENT_LINE_ITEM_TYPE && $configurator->getFree() === true) {
            // its free
            return true;
        }

        // is this a child which -may- be free-of-charge?
        if ($child->getType() === LineItemFactoryServiceInterface::PRODUCT_CHILD_LINE_ITEM_TYPE) {
            /** @var ConfiguratorStreamEntity $stream */
            $stream = $configurator->getStreams()->getElements()[$child->getPayloadValue('dvsnConfiguratorStreamId')];

            // we need to check the stream and check if its free
            if ($stream->getFree() === true) {
                // its free!
                return true;
            }
        }

        // not free
        return false;
    }

    /**
     * ...
     *
     * @param LineItemCollection $lineItems
     * @param SalesChannelContext $salesChannelContext
     *
     * @return SalesChannelProductEntity[]
     */
    private function getProducts(LineItemCollection $lineItems, SalesChannelContext $salesChannelContext): array
    {
        // every product if from every configurator
        $productIds = array();

        // loop every configurator
        foreach ($lineItems as $lineItem) {
            // the configurator product first
            array_push(
                $productIds,
                $lineItem->getReferencedId()
            );

            // and every child
            foreach ($lineItem->getChildren() as $child) {
                array_push(
                    $productIds,
                    $child->getReferencedId()
                );
            }
        }

        // we always should have products...
        if (count($productIds) === 0) {
            // shouldnt be happening
            return [];
        }

        // set up criteria
        $criteria = (new Criteria(array_unique($productIds)));

        /** @var SalesChannelProductEntity[] $product */
        $products = $this->salesChannelProductRepository
            ->search($criteria, $salesChannelContext)
            ->getElements();

        // return them
        return $products;
    }

    /**
     * ...
     *
     * @param string $id
     * @param SalesChannelContext $salesChannelContext
     *
     * @return ConfiguratorEntity
     */
    private function getConfigurator(string $id, SalesChannelContext $salesChannelContext)
    {
        // set up criteria
        $criteria = (new Criteria([$id]))
            ->addAssociation('streams');

        /** @var ConfiguratorEntity $configuratorEntity */
        $configuratorEntity = $this->configuratorRepository->search(
            $criteria,
            $salesChannelContext->getContext()
        )->getEntities()->first();

        // return it
        return $configuratorEntity;
    }

    /**
     * ...
     *
     * @param LineItem $lineItem
     * @param SalesChannelProductEntity $product
     * @param SalesChannelContext $salesChannelContext
     *
     * @return DeliveryInformation
     */
    private function getDeliveryInformation(LineItem $lineItem, SalesChannelProductEntity $product, SalesChannelContext $salesChannelContext): DeliveryInformation
    {
        $weight = (float) $product->getWeight();
        $maxHeight = (float) $product->getHeight();
        $maxWidth = (float) $product->getWidth();
        $maxLength = (float) $product->getLength();
        $volume = 0.0;

        foreach ($lineItem->getChildren() as $child) {
            if ($child->getType() === LineItemFactoryServiceInterface::PRODUCT_PARENT_LINE_ITEM_TYPE) {
                continue;
            }

            $weight += ((int) $child->getQuantity() / (int) $lineItem->getQuantity()) * (float) $child->getPayloadValue('weight');

            $maxHeight = max([(float) $maxHeight, (float) $child->getPayloadValue('height')]);
            $maxWidth = max([(float) $maxWidth, (float) $child->getPayloadValue('width')]);
            $maxLength = max([(float) $maxLength, (float) $child->getPayloadValue('length')]);
        }

        return new DeliveryInformation(
            (int) $product->getAvailableStock(),
            (float) $weight,
            (bool) $product->getShippingFree(),
            $product->getRestockTime(),
            ($product->getDeliveryTime() instanceof DeliveryTimeEntity) ? DeliveryTime::createFromEntity($product->getDeliveryTime()) : null,
            ($maxHeight > 0.0) ? $maxHeight : null,
            ($maxWidth > 0.0) ? $maxWidth : null,
            ($maxLength > 0.0) ? $maxLength : null
        );
    }

    /**
     * ...
     *
     * @param LineItem $lineItem
     * @param SalesChannelProductEntity $product
     * @param SalesChannelContext $salesChannelContext
     *
     * @return QuantityInformation
     */
    private function getQuantityInformation(LineItem $lineItem, SalesChannelProductEntity $product, SalesChannelContext $salesChannelContext): QuantityInformation
    {
        // create quantity information
        $quantityInformation = new QuantityInformation();

        // set min purchase if valid
        if ($product->getMinPurchase() > 0) {
            $quantityInformation->setMinPurchase($product->getMinPurchase());
        }

        // set max purchase if valid
        $quantityInformation->setMaxPurchase($product->getCalculatedMaxPurchase());

        // set purchase steps if valid
        if ($product->getPurchaseSteps() !== null) {
            $quantityInformation->setPurchaseSteps($product->getPurchaseSteps());
        }

        // return the quantity information
        return $quantityInformation;
    }

    /**
     * Validates the max purchase of every configurator parent and every child
     * within the configurations. Reduces the quantity for the configurators
     * or drops them.
     *
     * @param Cart $cart
     * @param ErrorCollection $errorCollection
     * @param SalesChannelProductEntity[] $products
     */
    private function validateMaxPurchase(Cart $cart, ErrorCollection $errorCollection, array $products): void
    {
        // call everything
        $this->dropConfigurators($cart, $errorCollection);
        $this->fixConfigurators($cart, $errorCollection);
        $this->fixComponents($cart, $errorCollection, $products);
    }

    /**
     * Changes the quantity of configurators or drops them if any component
     * within a configuration has a limited stock (max purchase) and is in the
     * cart too often.
     *
     * @param Cart $cart
     * @param ErrorCollection $errorCollection
     * @param SalesChannelProductEntity[] $products
     */
    private function fixComponents(Cart $cart, ErrorCollection $errorCollection, array $products): void
    {
        // get the configurators
        $lineItems = $cart->getLineItems()->filterType(
            LineItemFactoryServiceInterface::CONFIGURATOR_LINE_ITEM_TYPE
        );

        // every limited product here
        $maxPurchase = [];

        // loop every configurator
        foreach ($lineItems as $lineItem) {
            // loop every child
            foreach ($lineItem->getChildren() as $child) {
                // we ignore the parent itself
                if ($child->getType() === LineItemFactoryServiceInterface::PRODUCT_PARENT_LINE_ITEM_TYPE) {
                    // next
                    continue;
                }

                // get the product
                $product = $products[$child->getReferencedId()];

                // do we have a max purchase?
                if ($product->getCalculatedMaxPurchase() < 99) {
                    // add it
                    $maxPurchase[$product->getId()] = $product->getCalculatedMaxPurchase();
                }
            }
        }

        // nothing to check?!
        if (count($maxPurchase) === 0) {
            // stop
            return;
        }

        // loop every product with limited stock
        foreach ($maxPurchase as $productId => $stock) {
            // we have to get the lines for every loop to drop the removed items
            $lineItems = $cart->getLineItems()->filterType(
                LineItemFactoryServiceInterface::CONFIGURATOR_LINE_ITEM_TYPE
            );

            // as single product within the cart?!
            if ($cart->getLineItems()->has($productId)) {
                // get the product
                $lineItem = $cart->getLineItems()->get($productId);
                // reduce the selected quantity from the max purchase stock
                $stock = $stock - $lineItem->getQuantity();
            }

            // get the sum of the selected quantity for the current product id for each configurator
            // which may be selected multiple times in multiple streams.
            // then divide it by the parent quantity and check if 1.) we can even
            // use it or 2.) we have to reduce the quantity of the parent or 3.)
            // we have to remove the configurator itself
            foreach ($lineItems as $configurator) {
                // count the product within this configurator
                $quantity = 0;

                // find and count the product
                foreach ($configurator->getChildren() as $child) {
                    if ($child->getPayloadValue('dvsnSetConfiguratorProductId') !== $productId) {
                        continue;
                    }

                    $quantity = $quantity + $child->getQuantity();
                }

                // the product is not a child for the current configurator
                if ($quantity === 0) {
                    // next configurator
                    continue;
                }

                // 1.) do we have enough?
                if ($stock >= $quantity) {
                    // we reduce the stock by the quantity within this configurator
                    $stock = $stock - $quantity;
                    continue;
                }

                // the quantity of the product for one parent
                $quantityPerParent = (int) ($quantity / $configurator->getQuantity());

                // 2.) we cant have one quantity of the configurator so we have to drop it
                if ($quantityPerParent > $stock) {
                    // add error
                    $errorCollection->add(
                        new InvalidComponentQuantityRemoveConfiguratorError($configurator->getId())
                    );

                    // we have to remove the configurator itself
                    $cart->getLineItems()->removeElement($configurator);
                    continue;
                }

                // 3.) we have to reduce the configurator quantity
                $changedQuantity = (int) floor($stock / $quantityPerParent);

                // reduce quantity of the configurator which reduces the quantity
                // of every child
                $configurator->setQuantity($changedQuantity);

                // add error
                $errorCollection->add(
                    new InvalidComponentQuantityReduceQuantityError($configurator->getId())
                );
            }
        }
    }

    /**
     * Reduce the quantity of configurators if they are more than the max
     * purchase of the parent products. If we have too many configurators, then
     * the dropConfigurators() will take care of that.
     *
     * @param Cart $cart
     * @param ErrorCollection $errorCollection
     */
    private function fixConfigurators(Cart $cart, ErrorCollection $errorCollection): void
    {
        // group them by parent to check the max purchase
        $grouped = $this->getGroupedConfigurators($cart);

        /** @var $lineItems LineItemCollection  */
        foreach ($grouped as $productId => $productLineItems) {
            // if we have only 1 configurator, then shopware will take care
            // of the max purchase by default. the only problem is having
            // the parent multiple times in the cart
            if (count($productLineItems) <= 1) {
                // ignore it
                continue;
            }

            // get the max purchase
            $maxPurchase = (int) $productLineItems->first()->getQuantityInformation()->getMaxPurchase();

            // default value?
            if ($maxPurchase >= 99) {
                // ignore it
                continue;
            }

            // the total quantity in the cart which we count
            $totalQuantity = 0;

            // count our parents
            /** @var LineItem $lineItem */
            foreach ($productLineItems as $lineItem) {
                $totalQuantity += $lineItem->getQuantity();
            }

            // no problem at all?!
            if ($maxPurchase >= $totalQuantity) {
                // ignore it
                continue;
            }

            // so we have to reduce quantity...
            $reduce = $totalQuantity - $maxPurchase;

            /** @var LineItem $lineItem */
            foreach ($productLineItems as $lineItem) {
                // abort condition
                if ($reduce === 0) {
                    // stop
                    break;
                }

                // ignore line items which have only 1 quantity
                if ($lineItem->getQuantity() <= 1) {
                    // we cant reduce quantity
                    continue;
                }

                // 1.) we only need to reduce this product and for this
                // we need at least 1 more than the reduce
                if ($lineItem->getQuantity() > $reduce) {
                    // set quantity
                    $lineItem->setQuantity($lineItem->getQuantity() - $reduce);

                    // clear reduce
                    $reduce = 0;

                    // add error
                    $errorCollection->add(
                        new InvalidConfiguratorQuantityReduceQuantityError($lineItem->getId())
                    );

                    // and let the next loop break
                    continue;
                }

                // 2.) reduce this product to 1 and decrease the reduce
                // and continue with the next configurator
                $reduce = $reduce - ($lineItem->getQuantity() - 1);

                // set quantity
                $lineItem->setQuantity(1);

                // add error
                $errorCollection->add(
                    new InvalidConfiguratorQuantityReduceQuantityError($lineItem->getId())
                );
            }
        }
    }

    /**
     * Drops configurators which shouldnt be in the basket because they are
     * more than the max purchase of the parent product. This will not change
     * quantities and only remove configurators.
     *
     * @param Cart $cart
     * @param ErrorCollection $errorCollection
     */
    private function dropConfigurators(Cart $cart, ErrorCollection $errorCollection): void
    {
        // group by parent to drop them or change quantity
        $grouped = $this->getGroupedConfigurators($cart);

        /** @var $lineItems LineItemCollection  */
        foreach ($grouped as $productId => $productLineItems) {
            // if we have only 1 configurator, then shopware will take care
            // of the max purchase by default. the only problem is having
            // the parent multiple times in the cart
            if (count($productLineItems) <= 1) {
                // ignore it
                continue;
            }

            // get the max purchase
            $maxPurchase = (int) $productLineItems->first()->getQuantityInformation()->getMaxPurchase();

            // default value?
            if ($maxPurchase >= 99) {
                // ignore it
                continue;
            }

            // if we have more elements than the actual max purchase
            // we have to remove the elements after the max anyways
            // and fix the quantity later in the default run
            if ($productLineItems->count() > $maxPurchase) {
                // the configuratos without the rmoved ones
                $newProductLineItems = new LineItemCollection();

                // loop every configurator
                foreach ($productLineItems->getElements() as $lineItem) {
                    // still below the max
                    if ($newProductLineItems->count() < $maxPurchase) {
                        // add it
                        $newProductLineItems->add($lineItem);
                        continue;
                    }

                    // already maxed so we add an error
                    $errorCollection->add(
                        new InvalidConfiguratorQuantityRemoveConfiguratorError($lineItem->getId())
                    );

                    // and remove it from the cart
                    $cart->getLineItems()->removeElement($lineItem);
                }
            }
        }
    }

    /**
     * Group every configurator by the parent in an array.
     *
     * @param Cart $cart
     *
     * @return LineItemCollection[]
     */
    private function getGroupedConfigurators(Cart $cart): array
    {
        $lineItems = $cart->getLineItems()->filterType(
            LineItemFactoryServiceInterface::CONFIGURATOR_LINE_ITEM_TYPE
        );

        $grouped = [];

        foreach ($lineItems as $lineItem) {
            $id = (string) $lineItem->getPayloadValue('dvsnSetConfiguratorProductId');
            if (!isset($grouped[$id])) {
                $grouped[$id] = new LineItemCollection();
            }
            $grouped[$id]->add($lineItem);
        }

        return $grouped;
    }
}