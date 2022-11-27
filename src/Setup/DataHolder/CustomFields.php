<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @package   Shopware\Plugins\DrivenProductConfigurator
 * @copyright (c) 2020 shopdriven
 */

namespace Driven\ProductConfigurator\Setup\DataHolder;

use Shopware\Core\System\CustomField\CustomFieldTypes;

class CustomFields
{
    /**
     * ...
     *
     * @var array
     */
    public static $customFields = [
        [
            'id' => null,
            'name' => 'driven_configurator_product',
            'position' => 10,
            'config' => [
                'label' => [
                    'en-GB' => 'ShopDriven Configurator',
                    'de-DE' => 'ShopDriven Konfigurator'
                ]
            ],
            'customFields' => [
                [
                    'id' => null,
                    'name' => 'driven_product_type_main',
                    'type' => CustomFieldTypes::BOOL,
                    'config' => [
                        'label' => [
                            'en-GB' => 'Base product',
                            'de-DE' => 'Basisprodukt'
                        ],
                        'customFieldPosition' => 2
                    ]
                ],
                [
                    'id' => null,
                    'name' => 'driven_product_type_variant',
                    'type' => CustomFieldTypes::BOOL,
                    'config' => [
                        'label' => [
                            'en-GB' => 'Base product',
                            'de-DE' => 'Variantenprodukt'
                        ],
                        'customFieldPosition' => 2
                    ]
                ]
            ],
            'relations' => [
                [
                    'entityName' => 'product',
                ],
            ],
        ],
    ];
}
