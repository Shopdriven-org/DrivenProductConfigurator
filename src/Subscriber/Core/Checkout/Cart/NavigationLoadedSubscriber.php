<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @package   Shopware\Plugins\Driven\ProductConfigurator
 * @copyright (c) 2022 shopdriven
 */

namespace Driven\ProductConfigurator\Subscriber\Core\Checkout\Cart;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Driven\ProductConfigurator\DrivenProductConfigurator;
use Driven\ProductConfigurator\Service\Cart\LineItemFactoryService;
use Driven\ProductConfigurator\Service\SelectionService;
use Dvsn\SetConfigurator\Service\Cart\LineItemFactoryServiceInterface;
use Shopware\Core\Checkout\Cart\Event\AfterLineItemRemovedEvent;
use Shopware\Core\Checkout\Cart\Event\BeforeLineItemRemovedEvent;
use Shopware\Core\Checkout\Cart\Event\CartChangedEvent;
use Shopware\Core\Checkout\Cart\Event\CartSavedEvent;
use Shopware\Core\Checkout\Cart\Event\CheckoutOrderPlacedCriteriaEvent;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Content\Category\Event\CategoryIndexerEvent;
use Shopware\Core\Content\Category\Event\NavigationLoadedEvent;
use Shopware\Core\Content\Cms\Events\CmsPageLoaderCriteriaEvent;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\LandingPage\LandingPageLoadedEvent;
use Shopware\Storefront\Page\Navigation\NavigationPageLoader;
use Shopware\Storefront\Page\PageLoadedEvent;
use Shopware\Storefront\Pagelet\Footer\FooterPageletLoadedEvent;
use Shopware\Storefront\Pagelet\Header\HeaderPageletLoadedEvent;
use Shopware\Storefront\Pagelet\PageletLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NavigationLoadedSubscriber implements EventSubscriberInterface
{

    private EntityRepositoryInterface $productRepository;
    private Connection $connection;

    const RACQUET_CATEGORY = "Hölzer";
    const TOPPING_CATEGORY = "Beläge";

    public function __construct(EntityRepositoryInterface $productRepository,
                                Connection                $connection)
    {
        $this->productRepository = $productRepository;
        $this->connection = $connection;
    }

    /**
     * {@inheritDoc}
     *
     */
    public static function getSubscribedEvents(): array
    {
        return [
            NavigationLoadedEvent::class => 'GetNavigationLoadedEvent'
        ];
    }

    /**
     * @throws Exception
     * TODO: implement better solution!
     */
    public function GetNavigationLoadedEvent(NavigationLoadedEvent $event): void
    {
        $breadcrumb = $event->getNavigation()->getActive()->getBreadcrumb();
        if (in_array(self::TOPPING_CATEGORY, $breadcrumb) || in_array(self::RACQUET_CATEGORY, $breadcrumb)) {
            foreach ($event->getNavigation()->getChildren($event->getNavigation()->getActive()->getId())->getTree() as $child) {
                $sqlQuery = "SELECT HEX(`product_id`) AS `product_id` FROM `product_category` WHERE `category_id` = UNHEX('" . $child->getId() . "')";
                $sqlResult = $this->connection->executeQuery($sqlQuery)->fetchAll();
                foreach ($sqlResult as $item) {
                    $product = $this->getProduct($item["product_id"], $event->getSalesChannelContext());
                    if (in_array(self::TOPPING_CATEGORY, $breadcrumb) || in_array("Belaege", $breadcrumb)) {
                        if (!isset($customFields["driven_product_configurator_racquet_option"])) {
                            $customFields["driven_product_configurator_racquet_option"] = "toppings";
                            $this->productRepository->upsert([[
                                'id' => $product["id"],
                                'customFields' => $product["customFields"]
                            ]], $event->getContext());
                        }
                    }
                    if (in_array(self::RACQUET_CATEGORY, $breadcrumb) || in_array("Hoelzer", $breadcrumb)) {
                        if (!isset($customFields["driven_product_configurator_racquet_option"])) {
                            $customFields["driven_product_configurator_racquet_option"] = "racquet";
                            $this->productRepository->upsert([[
                                'id' => $product["id"],
                                'customFields' => $product["customFields"]
                            ]], $event->getContext());
                        }
                    }
                }
            }
        }
    }
    /**
     * @param $id
     * @param SalesChannelContext $salesChannelContext
     * @return array
     */
    private function getProduct($id, SalesChannelContext $salesChannelContext): array
    {
        $result = $this->productRepository->search(
            (new Criteria())
                ->addFilter(new EqualsFilter('product.id', $id))
                ->addAssociation('customFields'),
            $salesChannelContext->getContext()
        )->first();
        return [
            "id" => $result->getId(),
            "customFields" => $result->getCustomFields()
        ];
    }
}
