<?php

namespace Elogic\SalesforceIntegration\Controller\Index;

use Elogic\SalesforceIntegration\Api\Data\SalesforceInterface;
use Elogic\SalesforceIntegration\Api\SalesforceRepositoryInterface;
use Elogic\SalesforceIntegration\Model\ResourceModel\Salesforce;
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
        'password'      => '6Ev8nD4u6RCfcwM'
    ];
    private const SALESFORCE_API_URL = 'https://abobus228-dev-ed.develop.my.salesforce.com';

    private array $salesforceIds = [];

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
//        $this->setToken();
        $this->execute2($this->salesforceRepository->get(1));
    }

    public function execute2(SalesforceInterface $salesforce)
    {
        $this->salesforce = $salesforce;
        $this->order = $this->orderRepository->get($salesforce->getOrderId());
        $this->customer = $this->customerRepository->getById($salesforce->getCustomerId());
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(
            'entity_id',
            $salesforce->getProductIds(),
            'in'
        )->create();
        $this->products = $this->productRepository->getList($searchCriteria)->getItems();

        $this->createSalesforceOrder();

        return null;
    }

    private function createSalesforceAccount($customer)
    {
        if (!is_null($customer->getCustomAttribute('salesforce_account_id'))) {
            return $this->salesforceIds['account'] = $customer->getCustomAttribute('salesforce_account_id')->getValue();
        }


        $data = [
            'Name' => $customer->getFirstname() . ' ' . $customer->getLastname()
        ];

        $this->curl->post(
            self::SALESFORCE_API_URL . '/services/data/v56.0/sobjects/Account',
            json_encode($data)
        );

        if ($this->curl->getStatus() == 201) {
            $salesforceId = json_decode($this->curl->getBody())->id;
            $customer->setCustomAttribute('salesforce_account_id', $salesforceId);
            $this->customerRepository->save($customer);

            $this->salesforceIds['account'] = $customer->getCustomAttribute('salesforce_account_id')->getValue();
        } else {
            $this->logger->error($this->curl->getStatus() . ' | ' . $this->curl->getBody());
        }
    }

    private function createSalesforceContact($customer)
    {
        if (!is_null($customer->getCustomAttribute('salesforce_contact_id'))) {
            return $this->salesforceIds['contact'] = $customer->getCustomAttribute('salesforce_contact_id')->getValue();
        }

        if (count($customer->getAddresses()) == 1) {
            /** @var \Magento\Customer\Model\Data\Address $address */
            $address = array_first($customer->getAddresses());
        }

        $data = [
            'AccountId'         => $this->salesforceIds['account'],
            'FirstName'         => $customer->getFirstname(),
            'LastName'          => $customer->getLastname(),
            'Email'             => $customer->getEmail(),
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
            $salesforceId = json_decode($this->curl->getBody())->id;
            $customer->setCustomAttribute('salesforce_contact_id', $salesforceId);
            $this->customerRepository->save($customer);

            $this->salesforceIds['contact'] = $customer->getCustomAttribute('salesforce_contact_id')->getValue();
        } else {
            $this->logger->error($this->curl->getStatus() . ' | ' . $this->curl->getBody());
        }
    }

    private function createSalesforceProducts($products)
    {
        foreach ($products as $product) {
            /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
            if (!is_null($product->getCustomAttribute('salesforce_product_id'))) {
                return $this->salesforceIds['products'][] = $product->getCustomAttribute(
                    'salesforce_product_id'
                )->getValue();
            }

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
                $salesforceId = json_decode($this->curl->getBody())->id;
                $product->setCustomAttribute('salesforce_product_id', $salesforceId);
                $product = $this->productRepository->save($product);

                $this->addProductToPriceBookEntry($product);

                $this->salesforceIds['products'][] = $product->getCustomAttribute('salesforce_product_id')->getValue();
            } else {
                $this->logger->error($this->curl->getStatus() . ' | ' . $this->curl->getBody());
            }
        }
    }

    private function addProductToPriceBookEntry($product)
    {
        if (is_null($product->getCustomAttribute('salesforce_price_book_entry_id'))) {
            /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
            $data = [
                'UnitPrice'        => $product->getPrice(),
                'Pricebook2Id'     => '01s68000000CuhyAAC',
                'Product2Id'       => $product->getCustomAttribute('salesforce_product_id')->getValue(),
                'IsActive'         => true,
                'UseStandardPrice' => false,
            ];

            $this->curl->post(
                self::SALESFORCE_API_URL . '/services/data/v56.0/sobjects/PricebookEntry',
                json_encode($data)
            );

            if ($this->curl->getStatus() == 201) {
                $salesforceId = json_decode($this->curl->getBody())->id;
                $product->setCustomAttribute('salesforce_price_book_entry_id', $salesforceId);
                $this->productRepository->save($product);
            } else {
                $this->logger->error($this->curl->getStatus() . ' | ' . $this->curl->getBody());
            }
        }
    }

    private function createSalesforceContract()
    {
        $address = $this->addressRepository->getById($this->customer->getDefaultBilling());
        $data = [
            'AccountId'         => $this->customer->getCustomAttribute('salesforce_account_id')->getValue(),
            'Pricebook2Id'      => '01s68000000CuhyAAC',
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
            $this->salesforce->setContractId(json_decode($this->curl->getBody())->id);
            $this->salesforceRepository->save($this->salesforce);
        } else {
            $this->logger->error($this->curl->getStatus() . ' | ' . $this->curl->getBody());
        }
    }

    private function createSalesforceOrder()
    {
        $this->setToken();

        $billingAddress = $this->addressRepository->getById($this->customer->getDefaultBilling());
        $shippingAddress = $this->addressRepository->getById($this->customer->getDefaultShipping());

        $data = [
            'AccountId'          => $this->customer->getCustomAttribute('salesforce_account_id')->getValue(),
            'Pricebook2Id'       => '01s68000000CuhyAAC',
            'ContractId'         => '',
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
            'Status'             => 'Draft'
        ];

        $this->curl->post(
            self::SALESFORCE_API_URL . '/services/data/v56.0/sobjects/Order',
            json_encode($data)
        );

        if ($this->curl->getStatus() == 201) {
            $this->salesforce->setOrderId(json_decode($this->curl->getBody())->id);
            $this->salesforceRepository->save($this->salesforce);
        } else {
            $this->logger->error($this->curl->getStatus() . ' | ' . $this->curl->getBody());
        }
    }

    private function addProductsToSalesforceOrder()
    {
        foreach ($this->products as $product) {
            $products_data[] = [
                'attributes'       => [
                    'type' => 'OrderItem'
                ],
                'PricebookEntryId' => $product->getCustomAttribute('salesforce_price_book_entry_id')->getValue(),
                'quantity'         => $this->salesforce->getProducts()[$product->getId()]['qty'],
                'UnitPrice'        => $product->getPrice(),
                'orderId'          => $this->salesforce->getOrderId()
            ];
        }

        $data = [
            'order' => [
                'attributes' => [
                    'type' => 'Order'
                ],
                'Id'         => '',
                'OrderItems' => [
                    'records' => $products_data
                ]
            ]
        ];
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
    }
}
