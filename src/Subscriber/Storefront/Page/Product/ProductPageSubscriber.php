<?php

/**
 * digitvision
 *
 * @category  digitvision
 * @package   Shopware\Plugins\DvsnSetConfigurator
 * @copyright (c) 2020 digitvision
 */

namespace Dvsn\SetConfigurator\Subscriber\Storefront\Page\Product;

use Dvsn\SetConfigurator\Core\Content\Configurator\ConfiguratorEntity;
use Dvsn\SetConfigurator\Exception\InvalidSelectionException;
use Dvsn\SetConfigurator\Service\SelectionServiceInterface;
use Dvsn\SetConfigurator\Service\StreamServiceInterface;
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
    private EntityRepositoryInterface $configuratorRepository;
    private EntityRepositoryInterface $productRepository;
    private SelectionServiceInterface $selectionService;
    private StreamServiceInterface $streamService;
    private SystemConfigService $systemConfigService;
    private Session $session;
    private TranslatorInterface $translator;

    public function __construct(
        EntityRepositoryInterface $configuratorRepository,
        EntityRepositoryInterface $productRepository,
        SelectionServiceInterface $selectionService,
        StreamServiceInterface $streamService,
        SystemConfigService $systemConfigService,
        Session $session,
        TranslatorInterface $translator
    ) {
        $this->configuratorRepository = $configuratorRepository;
        $this->productRepository = $productRepository;
        $this->selectionService = $selectionService;
        $this->streamService = $streamService;
        $this->systemConfigService = $systemConfigService;
        $this->session = $session;
        $this->translator = $translator;
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
     */
    public function onPageLoaded(ProductPageLoadedEvent $event)
    {
        // plugin has to be active
        if ((boolean) $this->systemConfigService->get('DvsnSetConfigurator.config.status', $event->getSalesChannelContext()->getSalesChannel()->getId()) === false) {
            // nothing to do
            return;
        }

        // get params
        $salesChannelContext = $event->getSalesChannelContext();
        $product = $event->getPage()->getProduct();
        $request = $event->getRequest();

        // get the default custom fields
        $customFields = (array) $product->getCustomFields();
        $translatedCustomFields = (array) $product->getTranslation('customFields');

        // check if the configuration even exists (after an update) or if its true
        if (!isset($this->systemConfigService->get('DvsnSetConfigurator.config', $salesChannelContext->getSalesChannel()->getId())['productDetailInheritVariants']) || (bool) $this->systemConfigService->get('DvsnSetConfigurator.config.productDetailInheritVariants', $salesChannelContext->getSalesChannel()->getId()) === true) {
            // are we just a variant?
            if ($product->getParentId() !== null) {
                // try to find the parent
                $parent = $this->productRepository->search(
                    (new Criteria([$product->getParentId()]))->addAssociation('customFields'),
                    $salesChannelContext->getContext()
                )->getEntities()->first();

                // get the parent custom fields
                $customFields = (array) $parent->getCustomFields();
                $translatedCustomFields = (array) $parent->getTranslation('customFields');
            }
        }

        // try to get the status by custom field or its translation
        $status = ((isset($customFields['dvsn_set_configurator_product_status']) && $customFields['dvsn_set_configurator_product_status'] === true) ||
            (isset($translatedCustomFields['dvsn_set_configurator_product_status']) && $translatedCustomFields['dvsn_set_configurator_product_status'] === true));

        // same with id
        $id = (isset($customFields['dvsn_set_configurator_product_configurator']) && !empty($customFields['dvsn_set_configurator_product_configurator']))
            ? (string) $customFields['dvsn_set_configurator_product_configurator']
            : null;

        // try translation
        $id = (isset($translatedCustomFields['dvsn_set_configurator_product_configurator']) && !empty($translatedCustomFields['dvsn_set_configurator_product_configurator']))
            ? (string) $translatedCustomFields['dvsn_set_configurator_product_configurator']
            : $id;

        // is this a configurator?
        if ($status === false || empty($id)) {
            // it isnt...
            return;
        }

        // get the configurator
        $configurator = $this->getConfigurator(
            $id,
            $salesChannelContext
        );

        // no configurator found?!
        if (!$configurator instanceof ConfiguratorEntity) {
            // nothing to do
            return;
        }

        // get the streams
        $streams = $this->streamService->getStreams(
            $product,
            $configurator,
            $salesChannelContext
        );

        // get the selected products
        try {
            $selection = $this->getSelection(
                $configurator,
                $streams,
                $request,
                $salesChannelContext
            );
        } catch (InvalidSelectionException $exception) {
            // get the default pre-selection
            $selection = $this->selectionService->getPreSelection(
                $configurator,
                $streams,
                $salesChannelContext
            );

            // and add an error to the page
            $this->session->getFlashBag()->add(
                'warning',
                $this->translator->trans('dvsn-set-configurator.flash.invalid-selection')
            );
        }

        // assign to page
        $event->getPage()->assign([
            'dvsnConfigurator' => $configurator,
            'dvsnConfiguratorStreams' => $streams,
            'dvsnConfiguratorSelection' => $selection,
            'dvsnConfiguratorConfiguration' => $this->systemConfigService->get('DvsnSetConfigurator.config', $salesChannelContext->getSalesChannel()->getId())
        ]);
    }

    /**
     * ...
     *
     * @param ConfiguratorEntity  $configurator
     * @param array               $streams
     * @param Request             $request
     * @param SalesChannelContext $salesChannelContext
     *
     * @return array
     */
    private function getSelection(ConfiguratorEntity $configurator, array &$streams, Request $request, SalesChannelContext $salesChannelContext): array
    {
        // we have a cart id? this would be a product from the cart
        if ($request->query->has('dvsnscId')) {
            // return by cart
            return $this->selectionService->getSelectionByLineItemId(
                (string) $request->query->get('dvsnscId'),
                $configurator,
                $streams,
                $salesChannelContext
            );
        }

        // we have a selection to load? when we have a url with a coded key (= id from selection entity)
        if ($request->query->has('dvsnscKey')) {
            // return by saved selection
            return $this->selectionService->getSelectionByKey(
                (string) $request->query->get('dvsnscKey'),
                $configurator,
                $streams,
                $salesChannelContext
            );
        }

        // return default pre-selection
        return $this->selectionService->getPreSelection(
            $configurator,
            $streams,
            $salesChannelContext
        );
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
        $criteria = (new Criteria())
            ->addAssociation('streams')
            ->addAssociation('streams.preselectedProducts')
            ->addAssociation('streams.blacklistedProducts')
            ->addAssociation('streams.products')
            ->addAssociation('streams.conditions')
            ->addFilter(new EqualsFilter('id', $id));

        // set sorting
        $criteria->getAssociation('streams')
            ->addSorting(new FieldSorting('position', FieldSorting::ASCENDING));

        /** @var ConfiguratorEntity $configurator */
        $configurator = $this->configuratorRepository->search(
            $criteria,
            $salesChannelContext->getContext()
        )->getEntities()->first();

        // return it
        return $configurator;
    }
}
