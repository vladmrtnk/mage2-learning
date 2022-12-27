<?php

namespace Elogic\SalesforceIntegration\Model;

use Elogic\SalesforceIntegration\Api\Data\SalesforceInterface;
use Elogic\SalesforceIntegration\Model\ResourceModel\Salesforce;
use Elogic\SalesforceIntegration\Model\ResourceModel\Salesforce\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;

class SalesforceRepository implements \Elogic\SalesforceIntegration\Api\SalesforceRepositoryInterface
{
    /**
     * @var \Elogic\SalesforceIntegration\Model\SalesforceFactory
     */
    private SalesforceFactory $salesforceFactory;
    /**
     * @var \Elogic\SalesforceIntegration\Model\ResourceModel\Salesforce\CollectionFactory
     */
    private CollectionFactory $collectionFactory;
    /**
     * @var \Elogic\SalesforceIntegration\Model\ResourceModel\Salesforce
     */
    private Salesforce $salesforceResource;
    /**
     * @var \Magento\Framework\Api\SearchResultsInterface
     */
    private SearchResultsInterface $searchResultFactory;

    /**
     * @param \Elogic\SalesforceIntegration\Model\SalesforceFactory $salesforceFactory
     * @param \Elogic\SalesforceIntegration\Model\ResourceModel\Salesforce\CollectionFactory $collectionFactory
     * @param \Elogic\SalesforceIntegration\Model\ResourceModel\Salesforce $salesforceResource
     * @param \Magento\Framework\Api\SearchResultsInterface $searchResultInterfaceFactory
     */
    public function __construct(
        SalesforceFactory $salesforceFactory,
        CollectionFactory $collectionFactory,
        Salesforce $salesforceResource,
        SearchResultsInterface $searchResultInterfaceFactory,
    ) {
        $this->salesforceFactory = $salesforceFactory;
        $this->collectionFactory = $collectionFactory;
        $this->salesforceResource = $salesforceResource;
        $this->searchResultFactory = $searchResultInterfaceFactory;
    }

    /**
     * @param int $id
     *
     * @return \Elogic\SalesforceIntegration\Api\Data\SalesforceInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get(int $id)
    {
        $salesforce = $this->salesforceFactory->create();
        $this->salesforceResource->load($salesforce, $id);

        if (!$salesforce->getId()) {
            throw new NoSuchEntityException(__('Unable to find sale with ID "%value"', ['value' => $id]));
        }

        return $salesforce;
    }

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Elogic\Sale\Api\SalesforceSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->collectionFactory->create();

        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }

        $searchResult = $this->searchResultFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }

    /**
     * @param \Elogic\SalesforceIntegration\Api\Data\SalesforceInterface $salesforce
     *
     * @return \Elogic\SalesforceIntegration\Api\Data\SalesforceInterface
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(SalesforceInterface $salesforce)
    {
        $this->salesforceResource->save($salesforce);

        return $salesforce;
    }

    /**
     * @param \Elogic\SalesforceIntegration\Api\Data\SalesforceInterface $salesforce
     *
     * @return true
     * @throws \Magento\Framework\Exception\StateException
     */
    public function delete(SalesforceInterface $salesforce)
    {
        try {
            $this->salesforceResource->delete($salesforce);
        } catch (\Exception $e) {
            throw new StateException(__('Unable to remove entity with ID "%value"', ['value' => $salesforce->getId()]));
        }

        return true;
    }
}
