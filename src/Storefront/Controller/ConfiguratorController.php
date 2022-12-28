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
use Driven\ProductConfigurator\Service\SelectionServiceInterface;
use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Checkout\Cart\CartException;
use Shopware\Core\Checkout\Cart\Exception\LineItemNotFoundException;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\LineItemFactoryRegistry;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
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

    private EntityRepositoryInterface $productRepository;

    private SelectionServiceInterface $selectionService;

    /**
     * ...
     *
     * @param SystemConfigService $systemConfigService
     */
    public function __construct(SystemConfigService     $systemConfigService,
                                LineItemFactoryRegistry $factory,
                                CartService             $cartService,
                                EntityRepositoryInterface $productRepository,
                                SelectionServiceInterface $selectionService
    )
    {
        // set params
        $this->systemConfigService = $systemConfigService;
        $this->factory = $factory;
        $this->cartService = $cartService;
        $this->productRepository = $productRepository;
        $this->selectionService = $selectionService;
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
    public function saveSelection(Cart $cart, string $id, RequestDataBag $data, Request $request, SalesChannelContext $context): Response
    {
        // TODO: get forehead and backhead and save that as childrens of parent racquet as bundle
        // TODO:  before that please check entities and run migration (reinstall plugin)
        dd($data->get("backhead"));
//        $selection = $this->parseSelectionString(
//            (string) $data->get('dvsn-set-configurator--selection')
//        );
//
//        // save the selection and get the key
//        $key = $this->selectionService->saveSelection(
//            (string) $data->get('dvsn-set-configurator--product-id'),
//            (string) $data->get('dvsn-set-configurator--configurator-id'),
//            $selection,
//            $context
//        );

        // add flash with the url
        // we wont have a cache because the key is always unique
        $this->addFlash(
            'success', "Successfully saved selection!"
        );
//        dd($_POST['sealing']);
        if ($_POST['sealing'] != null) {
            $sealingID = strtolower("DC1B7FFCB8D64DD2AE574A21F34F6FC5");
            $sealing = $this->getProducts([$sealingID], $context);
//            dd($sealing);
            try {
                $lineItem = new LineItem(
                    $sealingID,
                    LineItem::PRODUCT_LINE_ITEM_TYPE,
                    "",
                    1
                );
                $cart->getLineItems()->add($lineItem);
            } catch (\Exception $exception) {
                 dd($exception);
            }
        }
//        dd($cart->getLineItems());
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

        // retrn it
        return $products;
    }


    /**
     * ...
     *
     * @param string $selectionString
     *
     * @return array
     */
    private function parseSelectionString(string $selectionString): array
    {
        // create a selection array here
        $selection = [];

        // split the string
        foreach (explode(',', $selectionString) as $element) {
            // split by type
            $arr = explode(':', $element);

            // has to be valid
            if (!is_array($arr) || count($arr) != 4) {
                // ignore it
                continue;
            }

            // add to selection
            array_push($selection, [
                'parentId' => (string) $arr[1],
                'productId' => (string) $arr[2],
                'quantity' => (int) $arr[3]
            ]);
        }

        // return selection
        return $selection;
    }

}
