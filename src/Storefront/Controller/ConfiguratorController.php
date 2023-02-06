<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @package   none
 * @copyright (c) 2022 shopdriven
 */

namespace Driven\ProductConfigurator\Storefront\Controller;

use Driven\ProductConfigurator\DrivenProductConfigurator;
use Driven\ProductConfigurator\Service\Cart\LineItemFactoryService;
use Driven\ProductConfigurator\Service\SelectionServiceInterface;
use Exception;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\Cart as CoreCart;
use Shopware\Core\Checkout\Cart\CartBehavior;
use Shopware\Core\Checkout\Cart\CartRuleLoader;
use Shopware\Core\Checkout\Cart\Event\CartSavedEvent;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
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

    private EntityRepositoryInterface $productRepository;

    private SelectionServiceInterface $selectionService;

    private CartRuleLoader $cartRuleLoader;

    private EventDispatcherInterface $eventDispatcher;

    private lineItemFactoryService $lineItemFactoryService;

    /**
     * @param EntityRepositoryInterface $drivenConfigurator
     * @param EntityRepositoryInterface $productRepository
     * @param SelectionServiceInterface $selectionService
     * @param EventDispatcherInterface $eventDispatcher
     * @param CartRuleLoader $cartRuleLoader
     * @param LineItemFactoryService $lineItemFactoryService
     */
    public function __construct(EntityRepositoryInterface $drivenConfigurator,
                                EntityRepositoryInterface $productRepository,
                                SelectionServiceInterface $selectionService,
                                EventDispatcherInterface  $eventDispatcher,
                                CartRuleLoader            $cartRuleLoader,
                                lineItemFactoryService    $lineItemFactoryService
    )
    {
        $this->drivenConfigurator = $drivenConfigurator;
        $this->productRepository = $productRepository;
        $this->selectionService = $selectionService;
        $this->eventDispatcher = $eventDispatcher;
        $this->cartRuleLoader = $cartRuleLoader;
        $this->lineItemFactoryService = $lineItemFactoryService;
    }

    /**
     * @RouteScope(scopes={"storefront"})
     * @Route("/driven/set-configurator",
     *     name="frontend.driven.set-configurator",
     *     options={"seo"="false"},
     *     methods={"GET"})
     * @param Request $request
     * @param RequestDataBag $data
     * @param Context $context
     * @param SalesChannelContext $salesChannelContext
     * @return Response
     * @throws Exception
     */
    public function index(Request $request, RequestDataBag $data, Context $context, SalesChannelContext $salesChannelContext): Response
    {
        throw new Exception('not implemented yet');
    }

    /**
     * @Route("/driven/product-configurator/save-selection/{id}", name="frontend.driven.product-configurator.save-selection", defaults={"XmlHttpRequest": true}, methods={"POST"})
     */
    public function saveSelection(CoreCart $cart, string $id, RequestDataBag $data, Request $request, SalesChannelContext $context): Response
    {

        $backhead = $_POST["backhead"] ?? "";
        $forehead = $_POST["forehead"] ?? "";
        $sealing = $_POST["sealing"];
        $this->checkProductStock($backhead, $forehead, $sealing, $id, $context);

        $this->addFlash(
            'success', "Successfully saved selection!"
        );

//        if ($sealing != 0) {
//            try {
//                $sealingLineItem = $this->lineItemFactoryService->createSealingLineItem($this->getProduct($id, $context), 1, true, $context);
//                $cart->getLineItems()->add($sealingLineItem);
//                $this->eventDispatcher->dispatch(new CartSavedEvent($context, $cart));
//
//            } catch (Exception $exception) {
//                dd($exception);
//            }
//        }
        return $this->redirectToRoute("frontend.checkout.cart.page");
    }

    /**
     * @param $backhead
     * @param $forehead
     * @param $sealing
     * @param $id
     * @param SalesChannelContext $context
     * @return void
     */
    private function checkProductStock($backhead, $forehead, $sealing, $id, SalesChannelContext $context)
    {

        if ($this->getParentProduct($id, $context) !== null) {
            $this->selectionService->updateSelection(
                $id, $forehead, $backhead, $sealing, $context
            );
        } else {
            $this->selectionService->saveSelection(
                $id, $forehead, $backhead, $sealing, $context
            );
        }
        $configurator = $this->getParentProduct($id, $context);
        if ($backhead !== "") {
            $backheadProduct = $this->getProduct($backhead, $context);
            if ($backheadProduct != "") {
                $backheadProduct->setAvailableStock($backheadProduct->getAvailableStock() - 1);
            }
        }

        if ($forehead !== "") {
            $foreheadProduct = $this->getProduct($forehead, $context);
            if ($foreheadProduct != "") {
                $foreheadProduct->setAvailableStock($foreheadProduct->getAvailableStock() - 1);
            }
        }
    }

    /**
     * @param string $id
     * @param SalesChannelContext $salesChannelContext
     * @return ?ProductEntity
     */
    private function getProduct(string $id, SalesChannelContext $salesChannelContext): ?ProductEntity
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

    private function calculate(Cart $cart, SalesChannelContext $context): void
    {
        $behavior = new CartBehavior();

        // validate cart against the context rules
        $cart = $this->cartRuleLoader
            ->loadByCart($context, $cart, $behavior)
            ->getCart();

        $cart->markUnmodified();
        foreach ($cart->getLineItems()->getFlat() as $lineItem) {
            $lineItem->markUnmodified();
        }

    }

}
