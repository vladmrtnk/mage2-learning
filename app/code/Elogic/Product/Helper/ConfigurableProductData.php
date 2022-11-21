<?php

namespace Elogic\Product\Helper;

class ConfigurableProductData
{
    /**#@+
     * Constants defined for data product
     */
    public const CONFIGURABLE = [
        'sku'              => 'CFG-PRD',
        'name'             => 'Configurable product from Patch',
        'attribute_set_id' => 22,
        'status'           => 1,
        'weight'           => 10,
        'visibility'       => 4,
        'type_id'          => 'configurable',
        'stock_data'       => [
            'use_config_manage_stock' => 0,
            'manage_stock'            => 1,
            'is_in_stock'             => 1,
        ],
    ];
    public const SIMPLES = [
        [
            'sku'              => 'CFG-PRD-red',
            'name'             => 'Configurable red product from Patch',
            'attribute_set_id' => 22,
            'status'           => 1,
            'weight'           => 10,
            'visibility'       => 1,
            'type_id'          => 'simple',
            'price'            => 5,
            'color'            => '58',
            'stock_data'       => [
                'use_config_manage_stock' => 0,
                'manage_stock'            => 1,
                'is_in_stock'             => 1,
                'qty'                     => 999,
            ],
        ],
        [
            'sku'              => 'CFG-PRD-black',
            'name'             => 'Configurable black product from Patch',
            'attribute_set_id' => 22,
            'status'           => 1,
            'weight'           => 10,
            'visibility'       => 1,
            'type_id'          => 'simple',
            'price'            => 5,
            'color'            => '49',
            'stock_data'       => [
                'use_config_manage_stock' => 0,
                'manage_stock'            => 1,
                'is_in_stock'             => 1,
                'qty'                     => 500,
            ],
        ],
    ];
    /**#@-*/
}
