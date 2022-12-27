<?php

namespace Elogic\SalesforceIntegration\Model\Salesforce;

use Elogic\SalesforceIntegration\Api\Data\SalesforceInterface;
use Elogic\SalesforceIntegration\Api\SalesforceRepositoryInterface;
use Elogic\SalesforceIntegration\Helper\Curl;
use Elogic\SalesforceIntegration\Model\SalesforceRepository;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Psr\Log\LoggerInterface;

class SalesforceConsumer
{
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        SalesforceRepositoryInterface $salesforceRepository,
        LoggerInterface $logger,
        Curl $curlHelper,
    ) {
        $this->customerRepository = $customerRepository;
        $this->salesforceRepository = $salesforceRepository;
        $this->logger = $logger;
        $this->curlHelper = $curlHelper;
    }

    public function process(SalesforceInterface $salesforce)
    {
        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/salesforce_1.txt', json_encode($salesforce), FILE_APPEND);

        $this->logger->info('Start integration');
        $salesforce = $this->salesforceRepository->get($salesforce->getId());
        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/salesforce_2.txt', json_encode($salesforce), FILE_APPEND);

        try {
            $customer = $this->customerRepository->getById($salesforce->getCustomerId());

            $bearerToken = $this->curlHelper->getSalesforceToken();
            $this->curlHelper->setHeader($bearerToken);
            $salesforce->createSalesforceAccount($customer);
            $salesforce->createSalesforceContact($customer);
            $salesforce->createSalesforceProducts();
            $salesforce->createSalesforceContract($customer);
            $salesforce->createSalesforceOrder($customer);
            $salesforce->addProductsToSalesforceOrder($bearerToken);
        } catch (\Exception $exception) {
            $this->logger->error($exception);
        }

        $this->logger->info('Successful salesforce integration #' . $salesforce->getId());
    }
}
