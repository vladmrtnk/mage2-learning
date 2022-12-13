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

    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    public function setCustomerId(int $id)
    {
        $this->setData(self::CUSTOMER_ID, $id);
    }

    public function getProducts()
    {
        return json_decode($this->getData(self::PRODUCTS));
    }

    public function setProducts(array $ids)
    {
        $this->setData(self::PRODUCTS, json_encode($ids));
    }

    public function getProductIds()
    {
        return array_keys(json_decode($this->getData(self::PRODUCTS)));
    }

    public function getPriceBookId()
    {
        return $this->getData(self::PRICE_BOOK_ID);
    }

    public function setPriceBookId($id)
    {
        $this->setData(self::PRICE_BOOK_ID, $id);
    }

    public function getContractId()
    {
        return $this->getData(self::CONTRACT_ID);
    }

    public function setContractId($id)
    {
        $this->setData(self::CONTRACT_ID, $id);
    }

    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    public function setOrderId(int $id)
    {
        $this->setData(self::ORDER_ID, $id);
    }
}
