<?php

namespace Elogic\SalesforceIntegration\Api;

use Elogic\SalesforceIntegration\Api\Data\SalesforceInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface SalesforceRepositoryInterface
{
    /**
     * Get salesforce entity by id
     *
     * @param  int  $id
     *
     * @return \Elogic\SalesforceIntegration\Api\Data\SalesforceInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get(int $id);

    /**
     * Retrieve salesforce integrations list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface  $searchCriteria
     *
     * @return \Elogic\Sale\Api\SalesforceSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Create salesforce integration
     *
     * @param  \Elogic\SalesforceIntegration\Api\Data\SalesforceInterface  $sale
     *
     * @return \Elogic\SalesforceIntegration\Api\Data\SalesforceInterface
     */
    public function save(SalesforceInterface $sale);

    /**
     * Delete salesforce integration
     *
     * @param  \Elogic\SalesforceIntegration\Api\Data\SalesforceInterface  $sale
     *
     * @return bool Will returned True if deleted
     * @throws \Magento\Framework\Exception\StateException
     */
    public function delete(SalesforceInterface $sale);
}
