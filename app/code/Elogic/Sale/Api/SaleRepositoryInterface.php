<?php

namespace Elogic\Sale\Api;

use Elogic\Sale\Api\Data\SaleInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface SaleRepositoryInterface
{
    /**
     * Get info about sale by id
     *
     * @param  int  $saleId
     *
     * @return \Elogic\Sale\Api\Data\SaleInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($saleId);

    /**
     * Get info about sale by slug
     *
     * @param  string  $slug
     *
     * @return \Elogic\Sale\Api\Data\SaleInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBySlug($slug);

    /**
     * Retrieve sales list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface  $searchCriteria
     *
     * @return \Elogic\Sale\Api\SaleSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Create sale
     *
     * @param  \Elogic\Sale\Api\Data\SaleInterface  $sale
     *
     * @return \Elogic\Sale\Api\Data\SaleInterface
     */
    public function save(SaleInterface $sale);

    /**
     * Delete sale
     *
     * @param  \Elogic\Sale\Api\Data\SaleInterface  $sale
     *
     * @return bool Will returned True if deleted
     * @throws \Magento\Framework\Exception\StateException
     */
    public function delete(SaleInterface $sale);
}
