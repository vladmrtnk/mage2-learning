<?php

namespace Elogic\Product\Helper;

class SimpleProductData
{
    /**#@+
     * Constants defined for data product
     */
    public const PRODUCT_DATA = [
        'sku'              => 'SMPL-PRD',
        'name'             => 'Simple Product from Patch',
        'attribute_set_id' => 4,
        'status'           => 1,
        'weight'           => 10,
        'visibility'       => 4,
        'type_id'          => 'simple',
        'price'            => 5,
        'stock_data'       => [
            'use_config_manage_stock' => 0,
            'manage_stock'            => 1,
            'is_in_stock'             => 1,
            'qty'                     => 999,
        ],
    ];
    /**#@-*/
}
