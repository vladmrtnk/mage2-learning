<?php

namespace Elogic\Product\Helper;

class ProductKidsAttributesData
{
    /**#@+
     * Constants defined for data product
     */
    public const ATTRIBUTE_SET_DATA = [
        'name'       => 'Kids',
        'sort_order' => null,
    ];
    public const ATTRIBUTES = [
        'child_size'   => [
            'config' => [
                'group'            => 'General',
                'label'            => 'Child size',
                'type'             => 'int',
                'input'            => 'select',
                'required'         => false,
                'visible_on_front' => true,
            ],
            'values' => [
                '1' => 'XS',
                '2' => 'S',
                '3' => 'M',
                '4' => 'L',
                '5' => 'XL',
            ],
        ],
        'child_gender' => [
            'config' => [
                'group'            => 'General',
                'label'            => 'Child gender',
                'type'             => 'int',
                'input'            => 'select',
                'required'         => false,
                'visible_on_front' => true,
            ],
            'values' => [
                '1' => 'Boy',
                '2' => 'Girl',
            ],
        ],
    ];
    /**#@-*/
}
