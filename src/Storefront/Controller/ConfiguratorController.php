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
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\Routing\Annotation\Route;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ConfiguratorController extends StorefrontController
{

    /**
     * ...
     *
     * @var SystemConfigService
     */
    private $systemConfigService;

    /**
     * ...
     *
     * @param SystemConfigService $systemConfigService
     */
    public function __construct( SystemConfigService $systemConfigService)
    {
        // set params
        $this->systemConfigService = $systemConfigService;
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

    /**
     * ...
     *
     * @RouteScope(scopes={"storefront"})
     * @Route("/driven/set-configurator/save-selection",
     *     name="frontend.driven.set-configurator.save-selection",
     *     options={"seo"=false},
     *     methods={"POST"}
     * )
     *
     * @param Request $request
     * @param Context $context
     * @param SalesChannelContext $salesChannelContext
     *
     * @return Response
     */
    public function saveSelection(Request $request, RequestDataBag $data, Context $context, SalesChannelContext $salesChannelContext): Response
    {
        throw new \Exception('TODO: ');
    }


}
