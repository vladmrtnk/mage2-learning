<?php

namespace Elogic\Sale\Model\Source;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Option\ArrayInterface;

class Products implements ArrayInterface
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @param  \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory  $productCollectionFactory
     */
    public function __construct(
        CollectionFactory $productCollectionFactory
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $collection = $this->_productCollectionFactory
            ->create()
            ->addAttributeToSelect('name');

        $options = [];

        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($collection as $product) {
            $options[] = ['label' => $product->getName(), 'value' => $product->getId()];
        }

        return $options;
    }
}
