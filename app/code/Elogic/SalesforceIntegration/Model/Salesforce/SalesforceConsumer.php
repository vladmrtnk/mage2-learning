<?php

namespace Elogic\SalesforceIntegration\Model\Salesforce;

use Elogic\SalesforceIntegration\Api\Data\SalesforceInterface;
use Elogic\SalesforceIntegration\Helper\Curl;
use Elogic\SalesforceIntegration\Helper\SalesforceCommunication;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Psr\Log\LoggerInterface;

class SalesforceConsumer
{
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        SalesforceCommunication $salesforceCommunication,
        LoggerInterface $logger,
    ) {
        $this->customerRepository = $customerRepository;
        $this->logger = $logger;
        $this->salesforceCommunication = $salesforceCommunication;
    }

    public function process(SalesforceInterface $salesforce)
    {
        $this->logger->info('Start integration');

        try {
            $customer = $this->customerRepository->getById($salesforce->getCustomerId());

            $bearerToken = $this->salesforceCommunication->getToken();
            $this->salesforceCommunication->setHeader($bearerToken);
            $this->salesforceCommunication->createSalesforceAccount($customer);
            $this->salesforceCommunication->createSalesforceContact($customer);
            $this->salesforceCommunication->createSalesforceProducts($salesforce);
            $this->salesforceCommunication->createSalesforceContract($salesforce, $customer);
            $this->salesforceCommunication->createSalesforceOrder($salesforce, $customer);
            $this->salesforceCommunication->addProductsToSalesforceOrder($salesforce, $bearerToken);
        } catch (\Exception $exception) {
            $this->logger->error($exception);
        }

        $this->logger->info('Successful salesforce integration #' . $salesforce->getId());
    }
}
