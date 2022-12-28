<?php

namespace Elogic\SalesforceIntegration\Model\Salesforce;

use Elogic\SalesforceIntegration\Api\Data\SalesforceInterface;
use Elogic\SalesforceIntegration\Helper\Communication;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Psr\Log\LoggerInterface;

class SalesforceConsumer
{
    /**
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Elogic\SalesforceIntegration\Helper\Communication $communication
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        Communication $communication,
        LoggerInterface $logger,
    ) {
        $this->customerRepository = $customerRepository;
        $this->logger = $logger;
        $this->communication = $communication;
    }

    /**
     * @param \Elogic\SalesforceIntegration\Api\Data\SalesforceInterface $salesforce
     * @return void
     */
    public function process(SalesforceInterface $salesforce)
    {
        try {
            $customer = $this->customerRepository->getById($salesforce->getCustomerId());
            $bearerToken = $this->communication->getToken();
            $this->communication->setHeader($bearerToken);
            $this->communication->setSalesforce($salesforce);
            $this->communication->setCustomer($customer);

            $this->communication->createSalesforceAccount();
            $this->communication->createSalesforceContact();
            $this->communication->createSalesforceProducts();
            $this->communication->createSalesforceContract();
            $this->communication->createSalesforceOrder();
            $this->communication->addProductsToSalesforceOrder($bearerToken);
        } catch (\Exception $exception) {
            $this->logger->error($exception);
        }
    }
}
