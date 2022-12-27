<?php

namespace Elogic\SalesforceIntegration\Api\Data;

interface SalesforceInterface
{
    /**#@+
     * Constants defined for keys of  data array
     */
    const ENTITY_ID = 'entity_id';
    const CUSTOMER_ID = 'customer_id';
    const PRODUCTS = 'products';
    const PRICE_BOOK_ID = 'price_book_id';
    const ORDER_ID = 'order_id';
    const SALESFORCE_ORDER_ID = 'salesforce_order_id';
    const SALESFORCE_CONTRACT_ID = 'salesforce_contract_id';
    const ATTRIBUTES = [
        self::ENTITY_ID,
        self::CUSTOMER_ID,
        self::PRODUCTS,
        self::PRICE_BOOK_ID,
        self::ORDER_ID,
        self::SALESFORCE_ORDER_ID,
        self::SALESFORCE_CONTRACT_ID,
    ];
    /**#@-*/

    /**
     * @return mixed
     */
    public function getId();

    /**
     * @param $id
     *
     * @return mixed
     */
    public function setId($id);

    /**
     * @return mixed
     */
    public function getCustomerId();

    /**
     * @param $id
     *
     * @return mixed
     */
    public function setCustomerId($id);

    /**
     * @return mixed
     */
    public function getProductIds();

    /**
     * @param $ids
     *
     * @return mixed
     */
    public function setProductIds($ids);

    /**
     * @return mixed
     */
    public function getOrderId();

    /**
     * @param $id
     *
     * @return mixed
     */
    public function setOrderId($id);

    /**
     * @return mixed
     */
    public function getSalesforceOrderId();

    /**
     * @param $id
     *
     * @return mixed
     */
    public function setSalesforceOrderId($id);

    /**
     * @return mixed
     */
    public function getSalesforceContractId();

    /**
     * Get Salesforce Contract ID
     *
     * @param $id
     *
     * @return mixed
     */
    public function setSalesforceContractId($id);
}
