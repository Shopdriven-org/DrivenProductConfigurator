<?php

/**
 * shopdriven
 *
 * @category  shopdriven
 * @package   Shopware\Plugins\Driven\ProductConfigurator
 * @copyright (c) 2022 shopdriven
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
                    'name' => 'driven_product_configurator_racquet_option',
                    'type' => CustomFieldTypes::SELECT,
                    'config' => [
                        'label' => [
                            'en-GB' => 'Select product type..',
                            'de-DE' => 'Beläge auswählen..'
                        ],
                        'placeholder' => [
                            'en-GB' => 'Select product type..',
                            'de-DE' => 'Beläge auswählen..'
                        ],
                        'helpText' => [
                            'en-GB' => 'Please select between racquet or toppings',
                            'de-DE' => 'Bitte wählen Sie zwischen Schläger oder dessen Beläge'
                        ],
                        'options' => [
                            [
                                'label' => [
                                    'en-GB' => 'Racquet',
                                    'de-DE' => 'Schläger'
                                ],
                                'value' => 'racquet'
                            ],
                            [
                                'label' => [
                                    'en-GB' => 'Toppings',
                                    'de-DE' => 'Beläge'
                                ],
                                'value' => 'toppings'
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
