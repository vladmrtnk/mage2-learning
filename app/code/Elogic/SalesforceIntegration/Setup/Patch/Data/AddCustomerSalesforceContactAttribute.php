<?php

namespace Elogic\SalesforceIntegration\Setup\Patch\Data;


use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Setup\Patch\Data\UpdateIdentifierCustomerAttributesVisibility;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddCustomerSalesforceContactAttribute implements DataPatchInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private ModuleDataSetupInterface $moduleDataSetup;


    /**
     * @var \Magento\Customer\Setup\CustomerSetupFactory
     */
    private customerSetupFactory $customerSetupFactory;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory
    ) {
        $this->moduleDataSetup      = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies()
    {
        return [
            UpdateIdentifierCustomerAttributesVisibility::class,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function apply()
    {
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $customerSetup->addAttribute(
            Customer::ENTITY,
            'salesforce_contact_id',
            [
                'group'    => 'General',
                'label'    => 'Salesforce Contact Id',
                'type'     => 'varchar',
                'required' => false,
                'unique'   => true,
                'system'   => false,
            ]
        );
    }
}