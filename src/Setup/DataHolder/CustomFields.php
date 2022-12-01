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
                    'en-GB' => 'Single product configurator',
                    'de-DE' => 'Einzelproduktkonfigurator'
                ]
            ],
            'customFields' => [
                [
                    'id' => null,
                    'name' => 'dvsn_set_configurator_product_component_percental_display',
                    'type' => CustomFieldTypes::SELECT,
                    'config' => [
                        'label' => [
                            'en-GB' => 'Percentage surcharge - display method',
                            'de-DE' => 'Prozentualer Aufschlag - Anzeige'
                        ],
                        'placeholder' => [
                            'en-GB' => 'Select display method...',
                            'de-DE' => 'Anzeige wählen...'
                        ],
                        'options' => [
                            'label' => [
                                'en-GB' => 'Racquet',
                                'de-DE' => 'Schläger'
                            ],
                            'value' => "racquet",
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
                        'customFieldPosition' => 30
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
