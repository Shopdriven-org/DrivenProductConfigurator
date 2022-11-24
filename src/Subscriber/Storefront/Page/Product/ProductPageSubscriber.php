<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @copyright (c) 2022 shopdriven
 */

namespace Driven\ProductConfigurator\Subscriber\Storefront\Page\Product;

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
    private EntityRepositoryInterface $productRepository;
    private Session $session;

    public function __construct(
        Session $session,
        EntityRepositoryInterface $productRepository
    ) {
        $this->session = $session;
        $this->productRepository = $productRepository;
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
        //todo
    }

}

