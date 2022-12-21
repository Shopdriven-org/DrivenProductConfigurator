<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @package   none
 * @copyright (c) 2022 shopdriven
 */

namespace Driven\ProductConfigurator\Storefront\Controller;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartException;
use Shopware\Core\Checkout\Cart\Exception\LineItemNotFoundException;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItemFactoryRegistry;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Feature;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\Profiling\Profiler;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\Routing\Annotation\Route;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * @Route(defaults={"_routeScope"={"storefront"}})
 */
class ConfiguratorController extends StorefrontController
{

    private SystemConfigService $systemConfigService;

    private LineItemFactoryRegistry $factory;

    private CartService $cartService;

    /**
     * ...
     *
     * @param SystemConfigService $systemConfigService
     */
    public function __construct(SystemConfigService     $systemConfigService,
                                LineItemFactoryRegistry $factory,
                                CartService             $cartService
    )
    {
        // set params
        $this->systemConfigService = $systemConfigService;
        $this->factory = $factory;
        $this->cartService = $cartService;
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
     */
    public function index(Request $request, RequestDataBag $data, Context $context, SalesChannelContext $salesChannelContext): Response
    {
        throw new \Exception('not implemented yet');
    }
//
//    /**
//     * ...
//     *
//     * @RouteScope(scopes={"storefront"})
//     * @Route("/driven/product-configurator/save-selection",
//     *     name="frontend.driven.product-configurator.save-selection",
//     *     options={"seo"=false},
//     *     methods={"POST"}
//     * )
//     *
//     * @param Request $request
//     * @param Context $context
//     * @param SalesChannelContext $salesChannelContext
//     *
//     * @return Response
//     */
//    public function saveSelection(Request $request, RequestDataBag $data, Context $context, SalesChannelContext $salesChannelContext): Response
//    {
//
//        $page = $request->getPathInfo();
//
//        return $this->redirectToRoute($page);
//    }

    /**
     * @Route("/driven/product-configurator/save-selection/{id}", name="frontend.driven.product-configurator.save-selection", defaults={"XmlHttpRequest": true}, methods={"POST"})
     */
    public function saveSelection(Cart $cart, string $id, Request $request, SalesChannelContext $context): Response
    {
        // TODO: 1. IF SEALING IS TRUE ADD SEALING OPTION IN THE CART
        //       2. IF THERE ARE MULTIPLE SEALING OPTIONS INCREASE quantity OR IF REMOVED REMOVE THE PRODUCT (CREATE LOGIC)

        // TODO: GRAB REQUEST DATA AND CREATE BUNDLE PRODUCTS OUT OF IT AND SAVE SELECTION (CREATE LOGIC)

        // TODO: CREATE ADDITIONAL CART SERVICE AND NECESSARY INTERFACES!
//        dd($_POST['sealing']);
//        if ($_POST['sealing'] != null) {
//
//        }
        return $this->redirectToRoute("frontend.checkout.cart.page");
    }

}
