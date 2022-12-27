<?php

namespace Elogic\SalesforceIntegration\Controller\Index;

use Elogic\SalesforceIntegration\Api\Data\SalesforceInterface;
use Elogic\SalesforceIntegration\Api\SalesforceRepositoryInterface;
use Elogic\SalesforceIntegration\Model\ResourceModel\Salesforce;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

class Index implements HttpGetActionInterface
{
    private const SALESFORCE_ACCESS = [
        'grant_type'    => 'password',
        'client_id'     => '3MVG9vvlaB0y1YsKrIDS1qTLwTNff8.zV18wa7Md3y8psKO4bya_GUFftLZt.H68cfNgY0jm.Ich4BGmwMVy4',
        'client_secret' => 'A1A15760866055F21DAFFFCA29E3D5D75CE66F4A57874C6C7953E41F50F52694',
        'username'      => 'admin@abobus228.bom',
        'password'      => '6Ev8nD4u6RCfcwM',
    ];
    private const SALESFORCE_API_URL = 'https://abobus228-dev-ed.develop.my.salesforce.com';
    private const SALESFORCE_PRICEBOOK_ID = '01s68000000CuhyAAC';

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        CustomerRepositoryInterface $customerRepository,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        LoggerInterface $logger,
        Curl $curl,
        SalesforceRepositoryInterface $salesforceRepository,
        Salesforce $resourceSalesforce,
        AddressRepositoryInterface $addressRepository,
    ) {
        $this->orderRepository = $orderRepository;
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilderFactory->create();
        $this->logger = $logger;
        $this->curl = $curl;
        $this->salesforceRepository = $salesforceRepository;
        $this->resourceSalesforce = $resourceSalesforce;
        $this->addressRepository = $addressRepository;
    }

    public function execute()
    {
        $this->logger->info('Start integration wia controller');

        try {
            $salesforce = $this->salesforceRepository->get(1);
            $customer = $this->customerRepository->getById($salesforce->getCustomerId());
            file_put_contents($_SERVER['DOCUMENT_ROOT'].'/test.txt', json_encode($customer), FILE_APPEND);
//            $bearerToken = $salesforce->getToken();
//            $salesforce->setHeader($bearerToken);
            $salesforce->createSalesforceAccount($customer);
            $salesforce->createSalesforceContact($customer);
            $salesforce->createSalesforceProducts();
            $salesforce->createSalesforceContract($customer);
            $salesforce->createSalesforceOrder($customer);
            $salesforce->addProductsToSalesforceOrder();
        } catch (\Exception $exception) {
            $this->logger->error($exception);
        }

        $this->logger->info('Successful salesforce integration #' . $salesforce->getId());
    }

    public function execute2(SalesforceInterface $salesforce)
    {
        $this->salesforce = $salesforce;
        $this->order = $this->orderRepository->get($salesforce->getOrderId());
        $this->customer = $this->customerRepository->getById($salesforce->getCustomerId());

        $this->setToken();
        $this->createSalesforceAccount();
        $this->createSalesforceContact();
        $this->createSalesforceProducts();
        $this->createSalesforceContract();
        $this->createSalesforceOrder();
        $this->addProductsToSalesforceOrder();
    }

    private function createSalesforceAccount()
    {
        if (is_null($this->customer->getCustomAttribute('salesforce_account_id'))) {
            $data = [
                'Name' => $this->customer->getFirstname() . ' ' . $this->customer->getLastname(),
            ];

            $this->curl->post(
                self::SALESFORCE_API_URL . '/services/data/v56.0/sobjects/Account',
                json_encode($data)
            );

            if ($this->curl->getStatus() == 201) {
                $id = json_decode($this->curl->getBody())->id;
                $this->customer->setCustomAttribute('salesforce_account_id', $id);
                $this->customerRepository->save($this->customer);
            } else {
                $this->logger->error($this->curl->getStatus() . ' | ' . $this->curl->getBody());
            }
        }
    }

    private function createSalesforceContact()
    {
        if (is_null($this->customer->getCustomAttribute('salesforce_contact_id'))) {
            $address = $this->addressRepository->getById($this->customer->getDefaultShipping());
            $data = [
                'AccountId'         => $this->customer->getCustomAttribute('salesforce_account_id')->getValue(),
                'FirstName'         => $this->customer->getFirstname(),
                'LastName'          => $this->customer->getLastname(),
                'Email'             => $this->customer->getEmail(),
                'MobilePhone'       => $address->getTelephone(),
                'MailingStreet'     => array_first($address->getStreet()),
                'MailingCity'       => $address->getCity(),
                'MailingPostalCode' => $address->getPostcode(),
                'MailingState'      => $address->getRegion()->getRegion(),
                'MailingCountry'    => $address->getCountryId(),
            ];

            $this->curl->post(
                self::SALESFORCE_API_URL . '/services/data/v56.0/sobjects/Contact',
                json_encode($data)
            );

            if ($this->curl->getStatus() == 201) {
                $id = json_decode($this->curl->getBody())->id;
                $this->customer->setCustomAttribute('salesforce_contact_id', $id);
                $this->customerRepository->save($this->customer);
            } else {
                $this->logger->error($this->curl->getStatus() . ' | ' . $this->curl->getBody());
            }
        }
    }

    private function createSalesforceProducts()
    {
        foreach ($this->getProducts() as $product) {
            if (is_null($product->getCustomAttribute('salesforce_product_id'))) {
                $data = [
                    'Name'        => $product->getName(),
                    'ProductCode' => $product->getSku(),
                    'IsActive'    => true,
                ];

                $this->curl->post(
                    self::SALESFORCE_API_URL . '/services/data/v56.0/sobjects/Product2',
                    json_encode($data)
                );

                if ($this->curl->getStatus() == 201) {
                    $id = json_decode($this->curl->getBody())->id;
                    $product->setCustomAttribute('salesforce_product_id', $id);
                    $product = $this->productRepository->save($product);

                    $this->addProductToPriceBookEntry($product);
                } else {
                    $this->logger->error($this->curl->getStatus() . ' | ' . $this->curl->getBody());
                }
            }
        }
    }

    private function addProductToPriceBookEntry(ProductInterface $product)
    {
        if (is_null($product->getCustomAttribute('salesforce_price_book_entry_id'))) {
            $data = [
                'UnitPrice'        => $product->getPrice(),
                'Pricebook2Id'     => self::SALESFORCE_PRICEBOOK_ID,
                'Product2Id'       => $product->getCustomAttribute('salesforce_product_id')->getValue(),
                'IsActive'         => true,
                'UseStandardPrice' => false,
            ];

            $this->curl->post(
                self::SALESFORCE_API_URL . '/services/data/v56.0/sobjects/PricebookEntry',
                json_encode($data)
            );

            if ($this->curl->getStatus() == 201) {
                $id = json_decode($this->curl->getBody())->id;
                $product->setCustomAttribute('salesforce_price_book_entry_id', $id);
                $this->productRepository->save($product);
            } else {
                $this->logger->error($this->curl->getStatus() . ' | ' . $this->curl->getBody());
            }
        }
    }

    private function createSalesforceContract()
    {
        if (is_null($this->salesforce->getSalesforceContractId())) {
            $address = $this->addressRepository->getById($this->customer->getDefaultBilling());
            $data = [
                'AccountId'         => $this->customer->getCustomAttribute('salesforce_account_id')->getValue(),
                'Pricebook2Id'      => self::SALESFORCE_PRICEBOOK_ID,
                'CustomerSignedId'  => $this->customer->getCustomAttribute('salesforce_contact_id')->getValue(),
                'BillingStreet'     => array_first($address->getStreet()),
                'BillingCity'       => $address->getCity(),
                'BillingPostalCode' => $address->getPostcode(),
                'BillingState'      => $address->getRegion()->getRegion(),
                'BillingCountry'    => $address->getCountryId(),
            ];

            $this->curl->post(
                self::SALESFORCE_API_URL . '/services/data/v56.0/sobjects/Contract',
                json_encode($data)
            );

            if ($this->curl->getStatus() == 201) {
                $this->salesforce->setSalesforceContractId(json_decode($this->curl->getBody())->id);
                $this->salesforceRepository->save($this->salesforce);
            } else {
                $this->logger->error($this->curl->getStatus() . ' | ' . $this->curl->getBody());
            }
        }
    }

    private function createSalesforceOrder()
    {
        if (is_null($this->salesforce->getSalesforceOrderId())) {
            $billingAddress = $this->addressRepository->getById($this->customer->getDefaultBilling());
            $shippingAddress = $this->addressRepository->getById($this->customer->getDefaultShipping());
            $data = [
                'AccountId'          => $this->customer->getCustomAttribute('salesforce_account_id')->getValue(),
                'Pricebook2Id'       => self::SALESFORCE_PRICEBOOK_ID,
                'ContractId'         => $this->salesforce->getSalesforceContractId(),
                'BillingStreet'      => array_first($billingAddress->getStreet()),
                'BillingCity'        => $billingAddress->getCity(),
                'BillingPostalCode'  => $billingAddress->getPostcode(),
                'BillingState'       => $billingAddress->getRegion()->getRegion(),
                'BillingCountry'     => $billingAddress->getCountryId(),
                'ShippingStreet'     => array_first($shippingAddress->getStreet()),
                'ShippingCity'       => $shippingAddress->getCity(),
                'ShippingPostalCode' => $shippingAddress->getPostcode(),
                'ShippingState'      => $shippingAddress->getRegion()->getRegion(),
                'ShippingCountry'    => $shippingAddress->getCountryId(),
                'EffectiveDate'      => '2022-12-07',
                'Status'             => 'Draft',
            ];

            $this->curl->post(
                self::SALESFORCE_API_URL . '/services/data/v56.0/sobjects/Order',
                json_encode($data)
            );

            if ($this->curl->getStatus() == 201) {
                $this->salesforce->setSalesforceOrderId(json_decode($this->curl->getBody())->id);
                $this->salesforceRepository->save($this->salesforce);
            } else {
                $this->logger->error($this->curl->getStatus() . ' | ' . $this->curl->getBody());
            }
        }
    }

    private function addProductsToSalesforceOrder()
    {
        $orderId = $this->salesforce->getSalesforceOrderId();
        foreach ($this->getProducts() as $product) {
            $products_data[] = [
                'attributes'       => [
                    'type' => 'OrderItem',
                ],
                'PricebookEntryId' => $product->getCustomAttribute('salesforce_price_book_entry_id')->getValue(),
                'quantity'         => $this->salesforce->getProducts()[$product->getId()]['qty'],
                'UnitPrice'        => $product->getPrice(),
                'orderId'          => $orderId,
            ];
        }
        $data = [
            'order' => [
                [
                    'attributes' => [
                        'type' => 'Order',
                    ],
                    'Id'         => $orderId,
                    'OrderItems' => [
                        'records' => $products_data,
                    ],
                ],
            ],
        ];

        $url = self::SALESFORCE_API_URL . '/services/data/v56.0/commerce/sale/order/' . $orderId;
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->token,
        ];
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($curl);
        curl_close($curl);

        if ($response) {
            $this->logger->info('Salesforce integration #' . $this->salesforce->getId() . ' successful!');
        } else {
            $this->logger->error($response);
        }
    }

    private function setToken()
    {
        $this->curl->post(
            'https://login.salesforce.com/services/oauth2/token',
            self::SALESFORCE_ACCESS
        );

        $this->curl->setHeaders([
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . json_decode($this->curl->getBody())->access_token,
        ]);

        $this->token = json_decode($this->curl->getBody())->access_token;
    }

    private function getProducts()
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(
            'entity_id',
            array_keys($this->salesforce->getProductIds()),
            'in'
        )->create();

        return $this->productRepository->getList($searchCriteria)->getItems();
    }
}
