<?php

namespace Elogic\Sale\Block\Sale;

use Elogic\Sale\Api\SaleRepositoryInterface;
use Elogic\Sale\Model\ResourceModel\Sale\CollectionFactory;
use Magento\Framework\View\Element\Template;

class Sale extends Template
{
    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    private Template\Context $context;
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
        Template\Context $context,
        CollectionFactory $saleCollection,
        SaleRepositoryInterface $saleRepository,
        array $data = []
    ) {
        $this->context = $context;
        $this->saleCollection = $saleCollection;
        $this->saleRepository = $saleRepository;
        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Framework\DataObject[]
     */
    public function getItems()
    {
        $items = $this->saleCollection->create()->getItems();

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
