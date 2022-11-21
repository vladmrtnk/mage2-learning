<?php

namespace Elogic\Product\Setup\Patch\Data;

use Elogic\Product\Helper\SimpleProductData;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddSimpleProduct implements DataPatchInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $moduleDataSetup;
    /**
     * @var \Magento\Catalog\Api\Data\ProductInterfaceFactory
     */
    private ProductInterfaceFactory $productFactory;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @param  \Magento\Framework\Setup\ModuleDataSetupInterface  $moduleDataSetup
     * @param  \Magento\Catalog\Api\Data\ProductInterfaceFactory  $productFactory
     * @param  \Magento\Catalog\Api\ProductRepositoryInterface  $productRepository
     * @param  \Magento\Framework\App\State  $state
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ProductInterfaceFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        State $state,
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        try {
            $state->getAreaCode();
        } catch (LocalizedException $e) {
            $state->setAreaCode(Area::AREA_GLOBAL);
        }
    }

    /**
     * @return array|string[]
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @return void
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        $product = $this->productFactory->create();
        $product->setData(SimpleProductData::PRODUCT_DATA);
        try {
            $this->productRepository->save($product);
        } catch (\Exception $e) {
            echo '<pre>';
            print_r($e->getMessage());
            exit;
        }

        $this->moduleDataSetup->endSetup();
    }
}
