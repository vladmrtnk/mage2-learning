<?php

namespace Elogic\Sale\Model\Source;

use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Option\ArrayInterface;

class Products implements ArrayInterface
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected CollectionFactory $productCollectionFactory;
    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected Visibility $productVisibility;

    /**
     * @param  \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory  $productCollectionFactory
     * @param  \Magento\Catalog\Model\Product\Visibility  $productVisibility
     */
    public function __construct(
        CollectionFactory $productCollectionFactory,
        Visibility $productVisibility,
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productVisibility = $productVisibility;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $productCollection = $this->productCollectionFactory->create();

        $productCollection
            ->addAttributeToSelect('name')
            ->setVisibility($this->productVisibility->getVisibleInSiteIds());

        $options = [];

        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($productCollection as $product) {
            $options[] = ['label' => $product->getName(), 'value' => $product->getId()];
        }

        return $options;
    }
}
