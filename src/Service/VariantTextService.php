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

class VariantTextService implements VariantTextServiceInterface
{
    /**
     * {@inheritDoc}
     */
    public function getName(ProductEntity $product, $prependDefault = true): string
    {
        // our options
        $options = [];

        // loop the options
        foreach ($product->getOptions()->getElements() as $option) {
            // add it
            $options[] = [
                'group' => $option->getGroup()->getTranslation('name'),
                'option' => $option->getTranslation('name')
            ];
        }

        // always sort options by group name
        usort($options, function ($a, $b) {
            return strcmp($a['group'], $b['group']);
        });

        // set additional name
        $name = implode(' / ', array_map(
            function ($arr) {
                return $arr['group'] . ': ' . $arr['option'];
            },
            $options
        ));

        // return
        return ($prependDefault === true)
            ? trim($product->getTranslation('name') . ' ' . $name)
            : $name;
    }
}
