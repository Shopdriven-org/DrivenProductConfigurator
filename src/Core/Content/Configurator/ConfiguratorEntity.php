<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @package   Shopware\Plugins\DrivenProductConfigurator
 * @copyright (c) 2022 shopdriven
 */

namespace Driven\ProductConfigurator\Core\Content\Configurator;

use Driven\ProductConfigurator\Core\Content\Configurator\Aggregate\ConfiguratorStream\ConfiguratorStreamCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class ConfiguratorEntity extends Entity
{
    public const POSITION_BUYBOX = 'buybox';
    public const POSITION_CONTENT = 'content';

    public const LISTING_PRICE_CHEAPEST = 'cheapest';
    public const LISTING_PRICE_PRESELECTION = 'preselection';

    use EntityIdTrait;

    /**
     * ...
     *
     * @var string
     */
    protected $name;

    /**
     * ...
     *
     * @var string
     */
    protected $position;

    /**
     * ...
     *
     * @var string
     */
    protected $listingPrice;

    /**
     * ...
     *
     * @var bool
     */
    protected $summary;

    /**
     * ...
     *
     * @var bool
     */
    protected $free;

    /**
     * ...
     *
     * @var int
     */
    protected $rebate;

    /**
     * ...
     *
     * @var bool
     */
    protected $collapsibleStreams;

    /**
     * Getter method for the property.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Setter method for the property.
     *
     * @param string $name
     *
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Getter method for the property.
     *
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Setter method for the property.
     *
     * @param string $position
     *
     * @return void
     */
    public function setPosition(string $position): void
    {
        $this->position = $position;
    }

    /**
     * Getter method for the property.
     *
     * @return string
     */
    public function getListingPrice()
    {
        return $this->listingPrice;
    }

    /**
     * Setter method for the property.
     *
     * @param string $listingPrice
     *
     * @return void
     */
    public function setListingPrice(string $listingPrice): void
    {
        $this->listingPrice = $listingPrice;
    }

    /**
     * Getter method for the property.
     *
     * @return bool
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * Setter method for the property.
     *
     * @param bool $summary
     *
     * @return void
     */
    public function setSummary(bool $summary): void
    {
        $this->summary = $summary;
    }

    /**
     * Getter method for the property.
     *
     * @return bool
     */
    public function getFree()
    {
        return $this->free;
    }

    /**
     * Setter method for the property.
     *
     * @param bool $free
     *
     * @return void
     */
    public function setFree(bool $free): void
    {
        $this->free = $free;
    }

    /**
     * Getter method for the property.
     *
     * @return int
     */
    public function getRebate()
    {
        return $this->rebate;
    }

    /**
     * Setter method for the property.
     *
     * @param int $rebate
     *
     * @return void
     */
    public function setRebate(int $rebate): void
    {
        $this->rebate = $rebate;
    }
}
