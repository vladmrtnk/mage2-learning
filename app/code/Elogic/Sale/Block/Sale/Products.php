<?php

namespace Elogic\Sale\Block\Sale;

use Elogic\Sale\Model\Layer\Resolver;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Url\Helper\Data;

class Products extends \Magento\Catalog\Block\Product\ListProduct
{
    /**
     * @param  \Magento\Catalog\Block\Product\Context  $context
     * @param  \Magento\Framework\Data\Helper\PostHelper  $postDataHelper
     * @param  \Elogic\Sale\Model\Layer\Resolver  $layerResolver
     * @param  \Magento\Catalog\Api\CategoryRepositoryInterface  $categoryRepository
     * @param  \Magento\Framework\Url\Helper\Data  $urlHelper
     * @param  array  $data
     */
    public function __construct(
        Context $context,
        PostHelper $postDataHelper,
        Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        Data $urlHelper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $postDataHelper,
            $layerResolver,
            $categoryRepository,
            $urlHelper,
            $data
        );
    }
}
