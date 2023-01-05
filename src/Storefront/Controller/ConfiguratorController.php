<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @package   none
 * @copyright (c) 2022 shopdriven
 */

namespace Driven\ProductConfigurator\Storefront\Controller;

use Dompdf\Exception;
use Driven\ProductConfigurator\DrivenProductConfigurator;
use Driven\ProductConfigurator\Service\Cart\LineItemFactoryService;
use Driven\ProductConfigurator\Service\SelectionServiceInterface;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\Cart as CoreCart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartException;
use Shopware\Core\Checkout\Cart\CartPersisterInterface;
use Shopware\Core\Checkout\Cart\CartRuleLoader;
use Shopware\Core\Checkout\Cart\Event\AfterLineItemAddedEvent;
use Shopware\Core\Checkout\Cart\Event\BeforeLineItemAddedEvent;
use Shopware\Core\Checkout\Cart\Exception\LineItemNotFoundException;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItemFactoryRegistry;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Feature;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\Profiling\Profiler;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * @Route(defaults={"_routeScope"={"storefront"}})
 */
class ConfiguratorController extends StorefrontController
{

    private EntityRepositoryInterface $drivenConfigurator;

    private SystemConfigService $systemConfigService;

    private LineItemFactoryRegistry $factory;

    private CartService $cartService;

    private  \Driven\ProductConfigurator\Service\Cart\LineItemFactoryService $factoryService;

    private EntityRepositoryInterface $productRepository;

    private SelectionServiceInterface $selectionService;

    private CartRuleLoader $cartRuleLoader;

    private EventDispatcherInterface $eventDispatcher;

    /**
     * ...
     *
     * @param SystemConfigService $systemConfigService
     */
    public function __construct(EntityRepositoryInterface $drivenConfigurator,
                                SystemConfigService       $systemConfigService,
                                LineItemFactoryRegistry   $factory,
                                CartService               $cartService,
                                \Driven\ProductConfigurator\Service\Cart\LineItemFactoryService               $factoryService,
                                EntityRepositoryInterface $productRepository,
                                SelectionServiceInterface $selectionService,
                                EventDispatcherInterface $eventDispatcher,
                                CartRuleLoader $cartRuleLoader
    )
    {
        // set params
        $this->drivenConfigurator = $drivenConfigurator;
        $this->systemConfigService = $systemConfigService;
        $this->factory = $factory;
        $this->cartService = $cartService;
        $this->factoryService = $factoryService;
        $this->productRepository = $productRepository;
        $this->selectionService = $selectionService;
        $this->eventDispatcher = $eventDispatcher;
        $this->cartRuleLoader = $cartRuleLoader;
    }

    /**
     * ...
     *
     * @RouteScope(scopes={"storefront"})
     * @Route("/driven/set-configurator",
     *     name="frontend.driven.set-configurator",
     *     options={"seo"="false"},
     *     methods={"GET"}
     * )
     *
     * @param Request $request
     * @param RequestDataBag $data
     * @param Context $context
     * @param SalesChannelContext $salesChannelContext
     *
     * @return Response
     * @throws \Exception
     */
    public function index(Request $request, RequestDataBag $data, Context $context, SalesChannelContext $salesChannelContext): Response
    {
        throw new \Exception('not implemented yet');
    }

    /**
     * @Route("/driven/product-configurator/save-selection/{id}", name="frontend.driven.product-configurator.save-selection", defaults={"XmlHttpRequest": true}, methods={"POST"})
     */
    public function saveSelection(CoreCart $cart, string $id, RequestDataBag $data, Request $request, SalesChannelContext $context): Response
    {
        ;
        $backhead = $_POST["backhead"] ?? "";
        $forehead = $_POST["forehead"] ?? "";
        $sealing = $_POST["sealing"] ?? 0;

        // TODO: ADD SEALING SERVICE AS PRODUCT IF SELECTED
//        dd($forehead);
        if ($this->getParentProduct($id, $context) !== null) {
            $this->selectionService->updateSelection(
                $id,
                $forehead,
                $backhead,
                $sealing,
                $context
            );
        } else {
            $this->selectionService->saveSelection(
                $id,
                $forehead,
                $backhead,
                $sealing,
                $context
            );
        }

        $this->addFlash(
            'success', "Successfully saved selection!"
        );

        // TODO: if sealing is selected add that service as line item !!!!
        // TODO: IF two black layers are selected  push warning notification
        if ($sealing != null) {
            try {
                $this->addSealingService($this->getProduct($id, $context), $context, $cart);
            } catch (\Exception $exception) {
                dd($exception);
            }
        }
        return $this->redirectToRoute("frontend.checkout.cart.page");
    }

    /**
     * ...
     *
     * @param array $ids
     * @param SalesChannelContext $salesChannelContext
     *
     * @return ProductEntity[]
     */
    private function getProducts(array $ids, SalesChannelContext $salesChannelContext): array
    {
        // create criteria
        $criteria = (new Criteria($ids))
            ->addAssociation('cover.media')
            ->addAssociation('options.group')
            ->addAssociation('customFields');

        /** @var ProductEntity[] $products */
        $products = $this->productRepository
            ->search($criteria, $salesChannelContext->getContext())
            ->getElements();

        // return it
        return $products;
    }



    /**
     * ...
     *
     * @param string $id
     * @param SalesChannelContext $salesChannelContext
     *
     * @return ProductEntity
     */
    private function getProduct(string $id, SalesChannelContext $salesChannelContext): ProductEntity
    {
        /** @var ProductEntity $productRepository */
        return $this->productRepository->search(
            (new Criteria())
                ->addFilter(new EqualsFilter('product.id', $id))
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
     * @param $product
     * @param SalesChannelContext $salesChannelContext
     * @param Cart $cart
     */
    private function addSealingService($product, SalesChannelContext $salesChannelContext, Cart $cart): void
    {

        $lineItem =  $this->factoryService->createProduct($product, 1, true, $salesChannelContext);

        $this->cartService->add(
            $cart,
            $lineItem,
            $salesChannelContext
        );

        $this->eventDispatcher->dispatch(new BeforeLineItemAddedEvent($lineItem, $cart, $salesChannelContext));

        $this->calculate($cart, $salesChannelContext);
    }

    private function calculate(Cart $cart, SalesChannelContext $context): void
    {
        $behavior = new CartBehavior();

        // validate cart against the context rules
        $cart = $this->cartRuleLoader
            ->loadByCart($context, $cart, $behavior)
            ->getCart();


//        $this->cart[$cart->getToken()] = $cart;

        $cart->markUnmodified();
        foreach ($cart->getLineItems()->getFlat() as $lineItem) {
            $lineItem->markUnmodified();
        }

    }

}
