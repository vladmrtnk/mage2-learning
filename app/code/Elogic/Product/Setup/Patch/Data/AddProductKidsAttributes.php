<?php

namespace Elogic\Product\Setup\Patch\Data;

use Elogic\Product\Helper\ProductKidsAttributesData;
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
        $this->moduleDataSetup        = $moduleDataSetup;
        $this->attributeSetRepository = $attributeSetRepository;
        $this->searchCriteria         = $searchCriteriaBuilder->create();
        $this->eavSetup               = $setupFactory->create(['setup' => $this->moduleDataSetup]);
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
            ProductKidsAttributesData::ATTRIBUTE_SET_DATA['name'],
            ProductKidsAttributesData::ATTRIBUTE_SET_DATA['sort_order'],
        );

        foreach (ProductKidsAttributesData::ATTRIBUTES as $attributeName => $attribute) {
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
            if (ProductKidsAttributesData::ATTRIBUTE_SET_DATA['name'] == $attributeSet->getAttributeSetName()) {
                $this->attributeSetRepository->deleteById($attributeSet->getAttributeSetId());
                break;
            }
        }

        foreach (array_keys(ProductKidsAttributesData::ATTRIBUTES) as $attributeName) {
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
        foreach (ProductKidsAttributesData::ATTRIBUTES as $attributeName => $attribute) {
            $id = $this->eavSetup->getAttributeId(Product::ENTITY, $attributeName);

            $options = [
                'values'       => $attribute['values'],
                'attribute_id' => $id,
            ];

            $this->eavSetup->addAttributeOption($options);
        }
    }
}
