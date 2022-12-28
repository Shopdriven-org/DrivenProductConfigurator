<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @package   Shopware\Plugins\DrivenProductConfigurator
 * @copyright (c) 2022 shopdriven
 */

namespace Driven\ProductConfigurator\Core\Checkout\Cart\Error;

use Shopware\Core\Checkout\Cart\Error\Error;

class InvalidConfiguratorQuantityReduceQuantityError extends Error
{
    /**
     * ...
     *
     * @var string
     */
    private $key;

    /**
     * ...
     *
     * @param string $key
     */
    public function __construct(string $key)
    {
        $this->key = $key;
        $this->message = sprintf('Quantity of line item "%s" was reduced', $key);
        parent::__construct($this->message);
    }

    /**
     * {@inheritDoc}
     */
    public function getParameters(): array
    {
        return ['key' => $this->key];
    }

    /**
     * {@inheritDoc}
     */
    public function getId(): string
    {
        return $this->key;
    }

    /**
     * {@inheritDoc}
     */
    public function getMessageKey(): string
    {
        return 'invalidConfiguratorQuantityReduceQuantityError';
    }

    /**
     * {@inheritDoc}
     */
    public function getLevel(): int
    {
        return self::LEVEL_ERROR;
    }

    /**
     * {@inheritDoc}
     */
    public function blockOrder(): bool
    {
        return true;
    }
}
