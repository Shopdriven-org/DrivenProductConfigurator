<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @package   Shopware\Plugins\DrivenProductConfigurator
 * @copyright (c) 2022 shopdriven
 */

namespace Driven\ProductConfigurator\Service\Calculator;

use Driven\ProductConfigurator\Core\Content\Configurator\ConfiguratorEntity;
use Driven\ProductConfigurator\Service\SelectionServiceInterface;
use Driven\ProductConfigurator\Service\StreamServiceInterface;
use Driven\ProductConfigurator\Struct\ListingPriceStruct;
use Shopware\Core\Content\Product\SalesChannel\Detail\CachedProductDetailRoute;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\Adapter\Cache\CacheValueCompressor;
use Shopware\Core\Framework\DataAbstractionLayer\Cache\EntityCacheKeyGenerator;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class ListingPriceCalculatorService implements ListingPriceCalculatorServiceInterface
{
    private SelectionServiceInterface $selectionService;
    private StreamServiceInterface $streamService;
    private PriceCalculatorServiceInterface $priceCalculatorService;
    private CacheInterface $cache;
    private EntityCacheKeyGenerator $generator;
    private bool $cacheStatus = false;

    public function __construct(
        SelectionServiceInterface $selectionService,
        StreamServiceInterface $streamService,
        PriceCalculatorServiceInterface $priceCalculatorService,
        CacheInterface $cache,
        EntityCacheKeyGenerator $generator
    ) {
        $this->selectionService = $selectionService;
        $this->streamService = $streamService;
        $this->priceCalculatorService = $priceCalculatorService;
        $this->cache = $cache;
        $this->generator = $generator;
    }

    /**
     * {@inheritDoc}
     */
    public function calculate(SalesChannelProductEntity $product, ConfiguratorEntity $configurator, SalesChannelContext $salesChannelContext): ListingPriceStruct
    {
        if ($this->cacheStatus === false) {
            return $this->getStruct(
                $product,
                $configurator,
                $salesChannelContext
            );
        }

        $key = $product->getId() . '-' . $configurator->getId() . '-' . $this->generator->getSalesChannelContextHash($salesChannelContext);

        $result = $this->cache->get($key, function (ItemInterface $item) use ($salesChannelContext, $configurator, $product) {
            $struct = $this->getStruct(
                $product,
                $configurator,
                $salesChannelContext
            );

            $item->tag([
                CachedProductDetailRoute::buildName($product->getId()),
            ]);

            return CacheValueCompressor::compress($struct);
        });

        return CacheValueCompressor::uncompress($result);
    }

    /**
     * ...
     *
     * @param SalesChannelProductEntity $product
     * @param ConfiguratorEntity $configurator
     * @param SalesChannelContext $salesChannelContext
     *
     * @return ListingPriceStruct
     */
    public function getStruct(SalesChannelProductEntity $product, ConfiguratorEntity $configurator, SalesChannelContext $salesChannelContext): ListingPriceStruct
    {
        // create a struct
        $struct = new ListingPriceStruct();

        // set from price
        $struct->setFrom(
            ($configurator->getListingPrice() === 'cheapest')
        );

        // get the streams
        $streams = $this->streamService->getStreams(
            $product,
            $configurator,
            $salesChannelContext
        );

        // get the selection by configurator type
        switch ($configurator->getListingPrice()) {
            case 'preselection':
                // get pre selection
                $selection = $this->selectionService->getPreSelection(
                    $configurator,
                    $streams,
                    $salesChannelContext
                );

                // done
                break;

            case 'cheapest':
            default:
                // get the cheapest selection
                $selection = $this->selectionService->getCheapestSelection(
                    $configurator,
                    $streams,
                    $salesChannelContext
                );

                // done
                break;
        }

        // calculate the price
        $price = $this->priceCalculatorService->calculate(
            $product,
            $configurator,
            $streams,
            $selection,
            $configurator->getRebate(),
            $salesChannelContext
        );

        // set the price
        $struct->setPrice(
            $price
        );

        // do we have a rebate?
        if ($configurator->getRebate() > 0) {
            // get the list price
            $listPrice = $this->priceCalculatorService->calculate(
                $product,
                $configurator,
                $streams,
                $selection,
                0,
                $salesChannelContext
            );

            // set it
            $struct->setListPrice(
                $listPrice
            );

            // set saved
            $struct->setPercentage(
                ($listPrice > 0) ? (int) round((1 - ($price / $listPrice))* 100) : 0
            );
        }

        // and return the full struct
        return $struct;
    }
}
