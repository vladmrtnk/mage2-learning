<?php

namespace Elogic\Sale\Model;

use Elogic\Sale\Api\Data\SaleInterface;
use Elogic\Sale\Api\SaleRepositoryInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Elogic\Sale\Model\ResourceModel\Sale;
use Elogic\Sale\Model\ResourceModel\Sale\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;

class SaleRepository implements SaleRepositoryInterface
{
    /**
     * @var \Elogic\Sale\Model\SaleFactory
     */
    private SaleFactory $saleFactory;
    /**
     * @var \Elogic\Sale\Model\ResourceModel\Sale\CollectionFactory
     */
    private CollectionFactory $collectionFactory;
    /**
     * @var \Elogic\Sale\Model\ResourceModel\Sale
     */
    private Sale $saleResource;
    /**
     * @var \Magento\Framework\Api\SearchResultsInterface
     */
    private SearchResultsInterface $searchResultFactory;

    /**
     * @param  \Elogic\Sale\Model\SaleFactory  $saleFactory
     * @param  \Elogic\Sale\Model\ResourceModel\Sale\CollectionFactory  $collectionFactory
     * @param  \Elogic\Sale\Model\ResourceModel\Sale  $saleResource
     * @param  \Magento\Framework\Api\SearchResultsInterface  $searchResultInterfaceFactory
     */
    public function __construct(
        SaleFactory $saleFactory,
        CollectionFactory $collectionFactory,
        Sale $saleResource,
        SearchResultsInterface $searchResultInterfaceFactory,
    ) {
        $this->saleFactory = $saleFactory;
        $this->collectionFactory = $collectionFactory;
        $this->saleResource = $saleResource;
        $this->searchResultFactory = $searchResultInterfaceFactory;
    }

    /**
     * @param $saleId
     *
     * @return \Elogic\Sale\Api\Data\SaleInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($saleId)
    {
        $saleObject = $this->saleFactory->create();
        $this->saleResource->load($saleObject, $saleId);

        if (!$saleObject->getId()) {
            throw new NoSuchEntityException(__('Unable to find sale with ID "%value"', ['value' => $saleId]));
        }

        return $saleObject;
    }

    /**
     * @param $slug
     *
     * @return \Elogic\Sale\Api\Data\SaleInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBySlug($slug)
    {
        $saleObject = $this->saleFactory->create();
        $this->saleResource->load($saleObject, $slug, SaleInterface::SLUG);

        if (!$saleObject->getId()) {
            throw new NoSuchEntityException(__('Unable to find sale with slug "%value"', ['value' => $slug]));
        }

        return $saleObject;
    }

    /**
     * @param  \Magento\Framework\Api\SearchCriteriaInterface  $searchCriteria
     *
     * @return \Elogic\Sale\Api\SaleSearchResultInterface
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
     * @param  \Elogic\Sale\Api\Data\SaleInterface  $sale
     *
     * @return \Elogic\Sale\Api\Data\SaleInterface
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function save(SaleInterface $sale)
    {
        $this->saleResource->save($sale);

        return $sale;
    }

    /**
     * @param  \Elogic\Sale\Api\Data\SaleInterface  $sale
     *
     * @return bool
     * @throws \Magento\Framework\Exception\StateException
     */
    public function delete(SaleInterface $sale)
    {
        try {
            $this->saleResource->delete($sale);
        } catch (\Exception $e) {
            throw new StateException(__('Unable to remove entity with ID "%value"', ['value' => $sale->getId()]));
        }

        return true;
    }
}
