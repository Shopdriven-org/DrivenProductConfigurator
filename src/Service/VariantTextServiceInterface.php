<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @package   Shopware\Plugins\DrivenProductConfigurator
 * @copyright (c) 2022 shopdriven
 */

namespace Driven\ProductConfigurator\Service;

use Shopware\Core\Content\Product\ProductEntity;

interface VariantTextServiceInterface
{
    /**
     * ...
     *
     * @param ProductEntity $product
     * @param bool $prependDefault
     *
     * @return string;
     */
    public function getName(ProductEntity $product, $prependDefault = true): string;
}
