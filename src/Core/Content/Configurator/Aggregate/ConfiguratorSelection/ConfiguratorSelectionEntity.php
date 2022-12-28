<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @package   Shopware\Plugins\DrivenProductConfigurator
 * @copyright (c) 2022 shopdriven
 */

namespace Driven\ProductConfigurator\Core\Content\Configurator\Aggregate\ConfiguratorSelection;

use Driven\ProductConfigurator\Core\Content\Configurator\ConfiguratorEntity;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

class ConfiguratorSelectionEntity extends Entity
{
    use EntityIdTrait;

    /**
     * ...
     *
     * @var string
     */
    protected $key;

    /**
     * ...
     *
     * @var array
     */
    protected $selection;

    /**
     * ...
     *
     * @var string
     */
    protected $configuratorId;

    /**
     * ...
     *
     * @var ConfiguratorEntity
     */
    protected $configurator;

    /**
     * ...
     *
     * @var string
     */
    protected $customerId;

    /**
     * ...
     *
     * @var CustomerEntity
     */
    protected $customer;

    /**
     * ...
     *
     * @var string
     */
    protected $salesChannelId;

    /**
     * ...
     *
     * @var SalesChannelEntity
     */
    protected $salesChannel;

    /**
     * ...
     *
     * @var string
     */
    protected $productId;

    /**
     * ...
     *
     * @var ProductEntity
     */
    protected $product;

    /**
     * Getter method for the property.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Setter method for the property.
     *
     * @param string $key
     *
     * @return void
     */
    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    /**
     * Getter method for the property.
     *
     * @return array
     */
    public function getSelection()
    {
        return $this->selection;
    }

    /**
     * Setter method for the property.
     *
     * @param array $selection
     *
     * @return void
     */
    public function setSelection(array $selection): void
    {
        $this->selection = $selection;
    }

    /**
     * Getter method for the property.
     *
     * @return string
     */
    public function getConfiguratorId()
    {
        return $this->configuratorId;
    }

    /**
     * Setter method for the property.
     *
     * @param string $configuratorId
     *
     * @return void
     */
    public function setConfiguratorId(string $configuratorId): void
    {
        $this->configuratorId = $configuratorId;
    }

    /**
     * Getter method for the property.
     *
     * @return ConfiguratorEntity
     */
    public function getConfigurator()
    {
        return $this->configurator;
    }

    /**
     * Setter method for the property.
     *
     * @param ConfiguratorEntity $configurator
     *
     * @return void
     */
    public function setConfigurator(ConfiguratorEntity $configurator): void
    {
        $this->configurator = $configurator;
    }

    /**
     * Getter method for the property.
     *
     * @return string
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * Setter method for the property.
     *
     * @param string $customerId
     *
     * @return void
     */
    public function setCustomerId(string $customerId): void
    {
        $this->customerId = $customerId;
    }

    /**
     * Getter method for the property.
     *
     * @return CustomerEntity
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Setter method for the property.
     *
     * @param CustomerEntity $customer
     *
     * @return void
     */
    public function setCustomer(CustomerEntity $customer): void
    {
        $this->customer = $customer;
    }

    /**
     * Getter method for the property.
     *
     * @return string
     */
    public function getSalesChannelId()
    {
        return $this->salesChannelId;
    }

    /**
     * Setter method for the property.
     *
     * @param string $salesChannelId
     *
     * @return void
     */
    public function setSalesChannelId(string $salesChannelId): void
    {
        $this->salesChannelId = $salesChannelId;
    }

    /**
     * Getter method for the property.
     *
     * @return SalesChannelEntity
     */
    public function getSalesChannel()
    {
        return $this->salesChannel;
    }

    /**
     * Setter method for the property.
     *
     * @param SalesChannelEntity $salesChannel
     *
     * @return void
     */
    public function setSalesChannel(SalesChannelEntity $salesChannel): void
    {
        $this->salesChannel = $salesChannel;
    }

    /**
     * Getter method for the property.
     *
     * @return string
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * Setter method for the property.
     *
     * @param string $productId
     *
     * @return void
     */
    public function setProductId(string $productId): void
    {
        $this->productId = $productId;
    }

    /**
     * Getter method for the property.
     *
     * @return ProductEntity
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Setter method for the property.
     *
     * @param ProductEntity $product
     *
     * @return void
     */
    public function setProduct(ProductEntity $product): void
    {
        $this->product = $product;
    }
}
