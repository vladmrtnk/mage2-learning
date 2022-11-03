<?php

namespace Elogic\Sale\Model;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\ContextInterface;
use Magento\Catalog\Model\Layer\StateFactory;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\BlockFactory;
use Magento\Store\Model\StoreManagerInterface;

class ProductLayer extends Layer
{
    /**
     * @var \Magento\Framework\View\Element\BlockFactory
     */
    protected BlockFactory $blockFactory;

    /**
     * @param  \Magento\Catalog\Model\Layer\ContextInterface  $context
     * @param  \Magento\Catalog\Model\Layer\StateFactory  $layerStateFactory
     * @param  \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory  $attributeCollectionFactory
     * @param  \Magento\Catalog\Model\ResourceModel\Product  $catalogProduct
     * @param  \Magento\Store\Model\StoreManagerInterface  $storeManager
     * @param  \Magento\Framework\Registry  $registry
     * @param  \Magento\Catalog\Api\CategoryRepositoryInterface  $categoryRepository
     * @param  \Magento\Framework\View\Element\BlockFactory  $blockFactory
     * @param  array  $data
     */
    public function __construct(
        ContextInterface $context,
        StateFactory $layerStateFactory,
        AttributeCollectionFactory $attributeCollectionFactory,
        Product $catalogProduct,
        StoreManagerInterface $storeManager,
        Registry $registry,
        CategoryRepositoryInterface $categoryRepository,
        BlockFactory $blockFactory,
        array $data = []
    ) {
        $this->blockFactory = $blockFactory;
        parent::__construct(
            $context,
            $layerStateFactory,
            $attributeCollectionFactory,
            $catalogProduct,
            $storeManager,
            $registry,
            $categoryRepository,
            $data
        );
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductCollection()
    {
        $collection = parent::getProductCollection();

        /** @var \Elogic\Sale\Block\Sale\Sale $block */
        $block = $this->blockFactory->createBlock('Elogic\Sale\Block\Sale\Sale');

        $products = $block->getItem()->getProducts();

        $collection->addIdFilter($products);

        return $collection;
    }
}
