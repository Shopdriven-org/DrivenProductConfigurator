<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @package   Shopware\Plugins\Driven\ProductConfigurator
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
            'position' => 3,
            'config' => [
                'label' => [
                    'en-GB' => 'Single product configurator',
                    'de-DE' => 'Einzelproduktkonfigurator'
                ]
            ],
            'customFields' => [
                [
                    'id' => null,
                    'name' => 'driven_product_configurator_base_racquet_product',
                    'type' => CustomFieldTypes::BOOL,
                    'config' => [
                        'label' => [
                            'en-GB' => 'Is Racquet',
                            'de-DE' => 'Ist Racquet'
                        ],
                        'customFieldPosition' => 1
                    ]
                ],
                [
                    'id' => null,
                    'name' => 'driven_product_configurator_product_racquet_type',
                    'type' => CustomFieldTypes::SELECT,
                    'config' => [
                        'label' => [
                            'en-GB' => 'Select Racquet type',
                            'de-DE' => 'Produktart auswählen'
                        ],
                        'placeholder' => [
                            'en-GB' => 'Select product type..',
                            'de-DE' => 'Produktart auswählen..'
                        ],
                        'options' => [
                            [
                                'label' => [
                                    'en-GB' => 'Forehand',
                                    'de-DE' => 'Rückhand'
                                ],
                                'value' => 'forehand'
                            ],
                            [
                                'label' => [
                                    'en-GB' => 'Backhand',
                                    'de-DE' => 'Versiegelung'
                                ],
                                'value' => 'backhand'
                            ]
                        ],
                        'componentName' => "sw-single-select",
                        'customFieldType' => CustomFieldTypes::SELECT,
                        'customFieldPosition' => 2
                    ]
                ],
            ],
            'relations' => [
                [
                    'entityName' => 'product',
                ],
            ],
        ],
    ];
}
