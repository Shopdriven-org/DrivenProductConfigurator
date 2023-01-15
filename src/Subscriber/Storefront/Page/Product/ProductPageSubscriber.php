<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @copyright (c) 2022 shopdriven
 */

namespace Driven\ProductConfigurator\Subscriber\Storefront\Page\Product;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProductPageSubscriber implements EventSubscriberInterface
{
    private EntityRepositoryInterface $productRepository;
    private Session $session;
    private EntityRepositoryInterface $categoryTranslation;
    private EntityRepositoryInterface $productCategory;
    private Connection $connection;

    const RACQUET_CATEGORY = "Hölzer";
    const TOPPING_CATEGORY = "Beläge";

    public function __construct(
        Session $session,
        EntityRepositoryInterface $productRepository,
        EntityRepositoryInterface $categoryTranslation,
        EntityRepositoryInterface $productCategory,
        Connection $connection
    ) {
        $this->session = $session;
        $this->productRepository = $productRepository;
        $this->categoryTranslation = $categoryTranslation;
        $this->productCategory = $productCategory;
        $this->connection = $connection;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ProductPageLoadedEvent::class => 'onPageLoaded',

        ];
    }

    /**
     * ...
     *
     * @param ProductPageLoadedEvent $event
     * @return void
     * @throws Exception
     */
    public function onPageLoaded(ProductPageLoadedEvent $event)
    {
//        $racquetCategories = $this->getRacquetCategories($event);
//        $toppingsCategories = $this->getToppingsCategories($event);
//
//        $racquetCategoryIds = [];
//        $toppingsCategoryIds = [];
//        foreach ($racquetCategories as $racquetCategory) {
//            array_push($racquetCategoryIds, $racquetCategory->getCategoryId());
//        }
//
//        foreach ($toppingsCategories as $toppingsCategory) {
//            array_push($toppingsCategoryIds, $toppingsCategory->getCategoryId());
//        }
//
//        $racquetproducts = $this->getProductIds(array_unique($racquetCategoryIds), $event);
//        $toppingsproducts = $this->getProductIds(array_unique($toppingsCategoryIds), $event);


//        dd($racquetproducts);
//        foreach ($racquetproducts as $racquetproduct) {
//
//            $racquetproduct->update(["driven_product_configurator_racquet_option" => "racquet"], $event->getContext());
//
//            dd($racquetproduct);
//
//        }
//        dd($racquetCategories->getCategoryId());
    }
//
//
//    /**
//     * ...
//     *
//     * @param ProductPageLoadedEvent $event
//     * @return array
//     */
//    public function getRacquetCategories(ProductPageLoadedEvent $event): array
//    {
//
//        return $this->categoryTranslation->search(
//            (new Criteria())
//                ->addFilter(new EqualsFilter('category_translation.name', self::RACQUET_CATEGORY)),
//            $event->getContext()
//        )->getElements();
//    }
//
//    /**
//     * ...
//     *
//     * @param ProductPageLoadedEvent $event
//     * @return array
//     */
//    public function getToppingsCategories(ProductPageLoadedEvent $event): array
//    {
//
//        return $this->categoryTranslation->search(
//            (new Criteria())
//                ->addFilter(new EqualsFilter('category_translation.name', self::TOPPING_CATEGORY)),
//            $event->getContext()
//        )->getElements();
//    }
//
//    /**
//     * ...
//     *
//     * @param array $categoryIds
//     * @param ProductPageLoadedEvent $event
//     * @return array
//     * @throws Exception
//     */
//    public function getProductIds(array $categoryIds, ProductPageLoadedEvent $event): array
//    {
//        $products = [];
//        foreach ($categoryIds as $categoryId) {
//
////            $sqlQuery = "SELECT product_id FROM `product_category` WHERE `category_id` = '".$categoryId."'";
//            $sqlQuery = "SELECT HEX(`product_id`) AS `product_id` FROM `product_category` WHERE `category_id` = UNHEX('".$categoryId."')";
//
//            $sqlResult = $this->connection->executeQuery($sqlQuery)->fetchAll();
//            foreach ( $sqlResult as $item) {
//                $product = $this->getProduct($item["product_id"], $event->getSalesChannelContext());
//                array_push($products, $product);
//
//            }
//        }
//        return $products;
//    }
//
//    /**
//     * @param $id
//     * @param SalesChannelContext $salesChannelContext
//     * @return mixed|null
//     */
//    private function getProduct($id, SalesChannelContext $salesChannelContext)
//    {
//
//        return $this->productRepository->search(
//            (new Criteria())
//                ->addFilter(new EqualsFilter('product.id', $id))
//                ->addAssociation('customFields'),
//            $salesChannelContext->getContext()
//        )->first();
//    }
}

