<?php

namespace Elogic\Sale\Block\Sale;

use Elogic\Sale\Api\SaleRepositoryInterface;
use Elogic\Sale\Model\ResourceModel\Sale\CollectionFactory;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Sale extends Template
{
    /**
     * @var \Elogic\Sale\Model\ResourceModel\Sale\CollectionFactory
     */
    private CollectionFactory $saleCollection;
    /**
     * @var \Elogic\Sale\Api\SaleRepositoryInterface
     */
    private SaleRepositoryInterface $saleRepository;

    /**
     * @param  \Magento\Framework\View\Element\Template\Context  $context
     * @param  \Elogic\Sale\Model\ResourceModel\Sale\CollectionFactory  $saleCollection
     * @param  \Elogic\Sale\Api\SaleRepositoryInterface  $saleRepository
     * @param  array  $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $saleCollection,
        SaleRepositoryInterface $saleRepository,
        array $data = []
    ) {
        $this->saleCollection = $saleCollection;
        $this->saleRepository = $saleRepository;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Framework\DataObject[]
     */
    public function getItems()
    {
        $collection = $this->saleCollection->create();
        $items = $collection->addFieldToFilter('valid_until', ['gt' => date_create()])->getItems();

        return $items;
    }

    /**
     * @return \Elogic\Sale\Api\Data\SaleInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getItem()
    {
        $slug = $this->getRequest()->getParam('slug');

        $sale = $this->saleRepository->getBySlug($slug);

        return $sale;
    }
}
