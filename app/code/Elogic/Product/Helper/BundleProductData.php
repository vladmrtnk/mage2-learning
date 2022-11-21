<?php

namespace Elogic\Product\Helper;

class BundleProductData
{
    /**#@+
     * Constants defined for data product
     */
    public const BUNDLE = [
        'sku'              => 'BNDL-PRD',
        'name'             => 'Bundle product from Patch',
        'attribute_set_id' => 4,
        'status'           => 1,
        'weight'           => 10,
        'visibility'       => 4,
        'type_id'          => 'bundle',
        'price_view'       => 0,
        'price_type'       => 0,
        'stock_data'       => [
            'use_config_manage_stock' => 0,
            'manage_stock'            => 1,
            'is_in_stock'             => 1,
        ],
    ];
    public const SIMPLES = [
        [
            'sku'              => 'BNDL-PRD-chipper',
            'name'             => 'Bundle chipper product from Patch',
            'attribute_set_id' => 4,
            'status'           => 1,
            'weight'           => 10,
            'visibility'       => 1,
            'type_id'          => 'simple',
            'price'            => 1,
            'stock_data'       => [
                'use_config_manage_stock' => 0,
                'manage_stock'            => 1,
                'is_in_stock'             => 1,
                'qty'                     => 100,
            ],
        ],
        [
            'sku'              => 'BNDL-PRD-more-expensive',
            'name'             => 'Bundle more expensive product from Patch',
            'attribute_set_id' => 4,
            'status'           => 1,
            'weight'           => 10,
            'visibility'       => 1,
            'type_id'          => 'simple',
            'price'            => 50,
            'stock_data'       => [
                'use_config_manage_stock' => 0,
                'manage_stock'            => 1,
                'is_in_stock'             => 1,
                'qty'                     => 50,
            ],
        ],
        [
            'sku'              => 'BNDL-PRD-the-most-expensive',
            'name'             => 'Bundle the most expensive product from Patch',
            'attribute_set_id' => 4,
            'status'           => 1,
            'weight'           => 10,
            'visibility'       => 1,
            'type_id'          => 'simple',
            'price'            => 1000,
            'stock_data'       => [
                'use_config_manage_stock' => 0,
                'manage_stock'            => 1,
                'is_in_stock'             => 1,
                'qty'                     => 10,
            ],
        ],
    ];
    /**#@-*/
}
