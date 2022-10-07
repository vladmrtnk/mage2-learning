<?php

namespace Elogic\Product\Setup\Patch\Data;

use Magento\Bundle\Api\Data\LinkInterfaceFactory;
use Magento\Bundle\Api\Data\OptionInterfaceFactory;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddBundleProduct implements DataPatchInterface
{
    /**#@+
     * Constants defined for data product
     */
    private const BUNDLE = [
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
    private const SIMPLES = [
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
     * @var \Magento\Bundle\Api\Data\OptionInterfaceFactory
     */
    private OptionInterfaceFactory $optionInterfaceFactory;
    /**
     * @var \Magento\Bundle\Api\Data\LinkInterfaceFactory
     */
    private LinkInterfaceFactory $linkInterfaceFactory;

    /**
     * @param  \Magento\Framework\Setup\ModuleDataSetupInterface  $moduleDataSetup
     * @param  \Magento\Catalog\Api\Data\ProductInterfaceFactory  $productFactory
     * @param  \Magento\Catalog\Api\ProductRepositoryInterface  $productRepository
     * @param  \Magento\Bundle\Api\Data\OptionInterfaceFactory  $optionInterfaceFactory
     * @param  \Magento\Bundle\Api\Data\LinkInterfaceFactory  $linkInterfaceFactory
     * @param  \Magento\Framework\App\State  $state
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ProductInterfaceFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        OptionInterfaceFactory $optionInterfaceFactory,
        LinkInterfaceFactory $linkInterfaceFactory,
        State $state,
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->optionInterfaceFactory = $optionInterfaceFactory;
        $this->linkInterfaceFactory = $linkInterfaceFactory;
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
     * @return \Elogic\Product\Setup\Patch\Data\AddBundleProduct|void
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();

        try {
            $simples = $this->createSimples();
            $this->createBundle($simples);
        } catch (\Exception $e) {
            echo '<pre>';
            print_r($e->getMessage());
            exit;
        }

        $this->moduleDataSetup->endSetup();
    }

    /**
     * @param  array  $simples
     *
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    private function createBundle(array $simples)
    {
        $product = $this->productFactory->create();
        $product->setData(self::BUNDLE);
        $product = $this->productRepository->save($product);

        foreach ($simples as $simple) {
            $selections[] = [
                'product_id'               => $simple->getId(),
                'selection_qty'            => 1,
                'selection_can_change_qty' => 1,
                'delete'                   => '',
            ];
        }
        $product->setBundleOptionsData(
            [
                [
                    'title'         => 'Bundle Product Items',
                    'default_title' => 'Bundle Product Items',
                    'type'          => 'radio',
                    'required'      => 1,
                    'delete'        => '',
                ],
            ]
        )->setBundleSelectionsData([$selections]);

        foreach ($product->getBundleOptionsData() as $key => $option_data) {
            if (!(bool) $option_data['delete']) {
                $option = $this->optionInterfaceFactory->create();
                $option->setData($option_data);
                $option->setSku($product->getSku());
                $option->setOptionId(null);
                $links_array = [];
                $bundle_links_data = $product->getBundleSelectionsData();
                if (!empty($bundle_links_data[$key])) {
                    foreach ($bundle_links_data[$key] as $linkdata) {
                        if (!(bool) $linkdata['delete']) {
                            $link = $this->linkInterfaceFactory->create();
                            $link->setData($linkdata);
                            $linkProduct = $this->productRepository->getById($linkdata['product_id']);
                            $link->setSku($linkProduct->getSku());
                            $link->setQty($linkdata['selection_qty']);
                            if (isset($linkdata['selection_can_change_qty'])) {
                                $link->setCanChangeQuantity($linkdata['selection_can_change_qty']);
                            }
                            $links_array[] = $link;
                        }
                    }
                    $option->setProductLinks($links_array);
                    $options[] = $option;
                }
            }
        }
        $extension_attribute = $product->getExtensionAttributes();
        $extension_attribute->setBundleProductOptions($options);
        $product->setExtensionAttributes($extension_attribute);

        $this->productRepository->save($product);
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     */
    private function createSimples()
    {
        foreach (self::SIMPLES as $simple) {
            $product = $this->productFactory->create();
            $product->setData($simple);
            $result[] = $this->productRepository->save($product);
        }

        return $result;
    }
}
