<?php

namespace Elogic\SalesforceIntegration\Model;

use Elogic\SalesforceIntegration\Api\Data\SalesforceInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

class SalesforceConsumer
{
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        CustomerRepositoryInterface $customerRepository,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        LoggerInterface $logger,
    ) {
        $this->orderRepository = $orderRepository;
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilderFactory->create();
        $this->logger = $logger;
    }

    public function execute(SalesforceInterface $salesforce)
    {
        $order = $this->orderRepository->get($salesforce->getOrderId());
        $customer = $this->customerRepository->get($salesforce->getCustomerId());

        $searchCriteria = $this->searchCriteriaBuilder->addFilter('entity_id', $salesforce->getProducts(), 'in');
        $products = $this->productRepository->getList($searchCriteria)->getItems();


    }
}
