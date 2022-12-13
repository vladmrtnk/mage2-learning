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
    const CONTRACT_ID = 'contract_id';
    const ORDER_ID = 'order_id';
    /**#@-*/

    /**
     * Salesforce integration ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get Customer
     *
     * @return string|null
     */
    public function getCustomerId();

    /**
     * Set customer
     *
     * @param int $id
     * @return $this
     */
    public function setCustomerId(int $id);

    /**
     * Get Products
     *
     * @return array|null
     */
    public function getProducts();

    /**
     * Set products
     *
     * @param array $ids
     * @return $this
     */
    public function setProducts(array $ids);

    /**
     * Get Product ids
     *
     * @return string|null
     */
    public function getProductIds();

    /**
     * Get PriceBook
     *
     * @return string|null
     */
    public function getPriceBookId();

    /**
     * Set PriceBook
     *
     * @param $id
     * @return $this
     */
    public function setPriceBookId($id);

    /**
     * Get Contract
     *
     * @return string|null
     */
    public function getContractId();

    /**
     * Set Contract
     *
     * @param int $id
     * @return $this
     */
    public function setContractId(int $id);

    /**
     * Get Order
     *
     * @return string|null
     */
    public function getOrderId();

    /**
     * Set Order
     *
     * @param int $id
     * @return $this
     */
    public function setOrderId(int $id);
}
