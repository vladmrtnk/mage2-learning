<?php

namespace Elogic\Product\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\AttributeSetRepository;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddProductKidsAttributes implements DataPatchInterface
{
    /**#@+
     * Constants defined for data product
     */
    private const ATTRIBUTE_SET_DATA = [
        'name'       => 'Kids',
        'sort_order' => null,
    ];
    private const ATTRIBUTES = [
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

    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $moduleDataSetup;
    /**
     * @var \Magento\Eav\Model\AttributeSetRepository
     */
    private AttributeSetRepository $attributeSetRepository;
    /**
     * @var \Magento\Eav\Setup\EavSetup
     */
    private EavSetup $eavSetup;
    /**
     * @var \Magento\Framework\Api\SearchCriteria
     */
    private SearchCriteria $searchCriteria;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $setupFactory,
        AttributeSetRepository $attributeSetRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->attributeSetRepository = $attributeSetRepository;
        $this->searchCriteria = $searchCriteriaBuilder->create();
        $this->eavSetup = $setupFactory->create(['setup' => $this->moduleDataSetup]);
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAliases(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function apply()
    {
        try {
            $this->deleteOldAttributes();
            $this->addAttributes();
            $this->setDefaultValues();
        } catch (\Exception $e) {
            echo 'Exception: ' . $e->getMessage();
        }
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Validate_Exception
     */
    private function addAttributes()
    {
        $this->eavSetup->addAttributeSet(
            Product::ENTITY,
            self::ATTRIBUTE_SET_DATA['name'],
            self::ATTRIBUTE_SET_DATA['sort_order'],
        );

        foreach (self::ATTRIBUTES as $attributeName => $attribute) {
            $this->eavSetup->addAttribute(
                Product::ENTITY,
                $attributeName,
                $attribute['config']
            );
        }
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function deleteOldAttributes()
    {
        $attributeSetList = $this->attributeSetRepository->getList($this->searchCriteria)->getItems();
        foreach ($attributeSetList as $attributeSet) {
            if (self::ATTRIBUTE_SET_DATA['name'] == $attributeSet->getAttributeSetName()) {
                $this->attributeSetRepository->deleteById($attributeSet->getAttributeSetId());
                break;
            }
        }

        foreach (array_keys(self::ATTRIBUTES) as $attributeName) {
            $this->eavSetup->removeAttribute(
                Product::ENTITY,
                $attributeName
            );
        }
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function setDefaultValues()
    {
        foreach (self::ATTRIBUTES as $attributeName => $attribute) {
            $id = $this->eavSetup->getAttributeId(Product::ENTITY, $attributeName);

            $options = [
                'values'       => $attribute['values'],
                'attribute_id' => $id,
            ];

            $this->eavSetup->addAttributeOption($options);
        }
    }
}
