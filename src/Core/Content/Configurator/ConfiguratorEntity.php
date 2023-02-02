<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @package   Shopware\Plugins\DrivenProductConfigurator
 * @copyright (c) 2022 shopdriven
 */

namespace Driven\ProductConfigurator\Core\Content\Configurator;

use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;

class ConfiguratorEntity extends Entity
{

    use EntityIdTrait;

    protected ?string $forehead;
    protected ?string $backhead;
    protected ?int $sealing;
    protected string $customerId;
    protected CustomerEntity $customer;
    protected string $salesChannelId;
    protected SalesChannelEntity $salesChannel;
    protected string $productId;
    protected ProductEntity $product;


    /**
     * Getter method for the property.
     *
     * @return ?string
     */
    public function getForehead(): ?string
    {
        return $this->forehead;
    }

    /**
     * Setter method for the property.
     *
     * @param ?string $forehead
     *
     * @return void
     */
    public function setForehead(?string $forehead): void
    {
        $this->forehead = $forehead;
    }

    /**
     * Getter method for the property.
     *
     * @return ?string
     */
    public function getBackhead(): ?string
    {
        return $this->backhead;
    }

    /**
     * Setter method for the property.
     *
     * @param string $backhead
     *
     * @return void
     */
    public function setBackhead(string $backhead): void
    {
        $this->backhead = $backhead;
    }

    /**
     * Getter method for the property.
     *
     * @return ?int
     */
    public function getSealing(): ?int
    {
        return $this->sealing;
    }

    /**
     * Setter method for the property.
     *
     * @param int $sealing
     *
     * @return void
     */
    public function setSealing(int $sealing): void
    {
        $this->sealing = $sealing;
    }

    /**
     * Getter method for the property.
     *
     * @return string
     */
    public function getCustomerId(): string
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
    public function getCustomer(): CustomerEntity
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
    public function getSalesChannelId(): string
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
    public function getSalesChannel(): SalesChannelEntity
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
    public function getProductId(): string
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
    public function getProduct(): ProductEntity
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
