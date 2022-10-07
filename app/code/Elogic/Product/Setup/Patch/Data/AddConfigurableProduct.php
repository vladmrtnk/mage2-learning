<?php

namespace Elogic\Product\Setup\Patch\Data;

use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product as ResourceProduct;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddConfigurableProduct implements DataPatchInterface
{
    /**#@+
     * Constants defined for data product
     */
    private const CONFIGURABLE = [
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
    private const SIMPLES = [
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
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    private ResourceProduct $resourceProduct;

    /**
     * @param  \Magento\Framework\Setup\ModuleDataSetupInterface  $moduleDataSetup
     * @param  \Magento\Catalog\Api\Data\ProductInterfaceFactory  $productFactory
     * @param  \Magento\Catalog\Api\ProductRepositoryInterface  $productRepository
     * @param  \Magento\Catalog\Model\ResourceModel\Product  $resourceProduct
     * @param  \Magento\Framework\App\State  $state
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ProductInterfaceFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        ResourceProduct $resourceProduct,
        State $state,
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->resourceProduct = $resourceProduct;
        try {
            $state->getAreaCode();
        } catch (LocalizedException $e) {
            $state->setAreaCode(Area::AREA_GLOBAL);
        }
    }

    /**
     * @return string[]
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return string[]
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

        try {
            $simplesIds = $this->createSimples();
            $this->createConfigurable($simplesIds);
        } catch (\Exception $e) {
            echo '<pre>';
            print_r($e->getMessage());
            exit;
        }

        $this->moduleDataSetup->endSetup();
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Validation\ValidationException
     */
    private function createSimples()
    {
        foreach (self::SIMPLES as $simple) {
            $product = $this->productFactory->create();
            $product->setData($simple);
            $result[] = (int) $this->productRepository->save($product)->getId();
        }

        return $result;
    }

    /**
     * @param  array  $simplesIds
     *
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    private function createConfigurable(array $simplesIds)
    {
        $product = $this->productFactory->create();
        $product->setData(self::CONFIGURABLE);
        $color_attr_id = $this->resourceProduct->getAttribute('color')->getId();
        $product->getTypeInstance()->setUsedProductAttributeIds([$color_attr_id], $product);
        $productAttributesData = $product->getTypeInstance()->getConfigurableAttributesAsArray($product);
        $product->setCanSaveConfigurableAttributes(true);
        $product->setConfigurableAttributesData($productAttributesData);
        $product->setConfigurableProductsData([]);
        $product = $this->productRepository->save($product);

        $product->setAssociatedProductIds($simplesIds);
        $product->setCanSaveConfigurableAttributes(true);
        $this->productRepository->save($product);
    }
}
