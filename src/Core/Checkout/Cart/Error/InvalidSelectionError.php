<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @package   Shopware\Plugins\Driven\ProductConfigurator
 * @copyright (c) 2022 shopdriven
 */

namespace Driven\ProductConfigurator\Core\Checkout\Cart\Error;

use Shopware\Core\Checkout\Cart\Error\Error;

class InvalidSelectionError extends Error
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
        $this->message = sprintf('Line item "%s" with invalid selection', $key);
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
        return 'invalidSelectionError';
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
