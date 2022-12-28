<?php

namespace Elogic\SalesforceIntegration\Model;

use Elogic\SalesforceIntegration\Api\Data\SalesforceInterface;
use Elogic\SalesforceIntegration\Model\ResourceModel\Salesforce as ResourceModel;
use Magento\Framework\Model\AbstractModel;


class Salesforce extends AbstractModel implements SalesforceInterface
{
    /**
     * Initialize magento model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    public function setId($id)
    {
        $this->setData(self::ENTITY_ID, $id);
    }

    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    public function setCustomerId($id)
    {
        $this->setData(self::CUSTOMER_ID, $id);
    }

    public function getProductIds()
    {
        return $this->getData(self::PRODUCTS);
    }

    public function setProductIds($ids)
    {
        $this->setData(self::PRODUCTS, $ids);
    }

    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    public function setOrderId($id)
    {
        $this->setData(self::ORDER_ID, $id);
    }

    public function getSalesforceOrderId()
    {
        return $this->getData(self::SALESFORCE_ORDER_ID);
    }

    public function setSalesforceOrderId($id)
    {
        $this->setData(self::SALESFORCE_ORDER_ID, $id);
    }

    public function getSalesforceContractId()
    {
        return $this->getData(self::SALESFORCE_CONTRACT_ID);
    }

    public function setSalesforceContractId($id)
    {
        $this->setData(self::SALESFORCE_CONTRACT_ID, $id);
    }
}
