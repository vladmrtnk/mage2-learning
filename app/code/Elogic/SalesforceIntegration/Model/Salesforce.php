<?php

namespace Elogic\SalesforceIntegration\Model;

use Elogic\SalesforceIntegration\Api\Data\SalesforceInterface;
use Elogic\SalesforceIntegration\Model\ResourceModel\Salesforce as ResourceModel;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\Model\AbstractModel;
use Elogic\SalesforceIntegration\Helper\Salesforce as SalesforceHelper;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\HTTP\Client\Curl;
use Psr\Log\LoggerInterface;


class Salesforce extends AbstractModel implements SalesforceInterface
{
    /**
     * Initialize magento model.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

    public function __construct(
        Context $context,
        Registry $registry,
        ResourceModel $resource = null,
        ResourceModel\Collection $resourceCollection = null,
        Curl $curl,
        LoggerInterface $logger,
        AddressRepositoryInterface $addressRepository,
        CustomerRepositoryInterface $customerRepository,
        ProductRepositoryInterface $productRepository,
        SalesforceRepository $salesforceRepository,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        array $data = []
    ) {
        $this->curl = $curl;
        $this->logger = $logger;
        $this->addressRepository = $addressRepository;
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
        $this->salesforceRepository = $salesforceRepository;
        $this->searchCriteriaBuilerFactory = $searchCriteriaBuilderFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilderFactory->create();
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    public function setId($id)
    {
        $this->setData(self::ENTITY_ID, $id);
    }

    public function getCustomerId()
    {
        return $this->getData(self::CUSTOMER_ID);
    }

    public function setCustomerId($id)
    {
        $this->setData(self::CUSTOMER_ID, $id);
    }

    public function getProductIds()
    {
        return $this->getData(self::PRODUCTS);
    }

    public function setProductIds($ids)
    {
        $this->setData(self::PRODUCTS, $ids);
    }

    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    public function setOrderId($id)
    {
        $this->setData(self::ORDER_ID, $id);
    }

    public function getSalesforceOrderId()
    {
        return $this->getData(self::SALESFORCE_ORDER_ID);
    }

    public function setSalesforceOrderId($id)
    {
        $this->setData(self::SALESFORCE_ORDER_ID, $id);
    }

    public function getSalesforceContractId()
    {
        return $this->getData(self::SALESFORCE_CONTRACT_ID);
    }

    public function setSalesforceContractId($id)
    {
        $this->setData(self::SALESFORCE_CONTRACT_ID, $id);
    }

    public function createSalesforceAccount(CustomerInterface $customer)
    {
        if (is_null($customer->getCustomAttribute('salesforce_account_id'))) {
            $data = [
                'Name' => $customer->getFirstname() . ' ' . $customer->getLastname(),
            ];

            $this->curl->post(
                SalesforceHelper::API_URL . '/services/data/v56.0/sobjects/Account',
                json_encode($data)
            );

            if ($this->curl->getStatus() == 201) {
                $id = json_decode($this->curl->getBody())->id;
                $customer->setCustomAttribute('salesforce_account_id', $id);
                $this->customerRepository->save($customer);
            } else {
                $this->logger->error($this->curl->getStatus() . ' | ' . $this->curl->getBody());
            }
        }
    }

    public function createSalesforceContact(CustomerInterface $customer)
    {
        if (is_null($customer->getCustomAttribute('salesforce_contact_id'))) {
            $address = $this->addressRepository->getById($customer->getDefaultShipping());
            $data = [
                'AccountId'         => $customer->getCustomAttribute('salesforce_account_id')->getValue(),
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
                SalesforceHelper::API_URL . '/services/data/v56.0/sobjects/Contact',
                json_encode($data)
            );

            if ($this->curl->getStatus() == 201) {
                $id = json_decode($this->curl->getBody())->id;
                $customer->setCustomAttribute('salesforce_contact_id', $id);
                $this->customerRepository->save($customer);
            } else {
                $this->logger->error($this->curl->getStatus() . ' | ' . $this->curl->getBody());
            }
        }
    }

    public function createSalesforceProducts()
    {
        foreach ($this->getProducts() as $product) {
            if (is_null($product->getCustomAttribute('salesforce_product_id'))) {
                $data = [
                    'Name'        => $product->getName(),
                    'ProductCode' => $product->getSku(),
                    'IsActive'    => true,
                ];

                $this->curl->post(
                    SalesforceHelper::API_URL . '/services/data/v56.0/sobjects/Product2',
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

    public function addProductToPriceBookEntry(ProductInterface $product)
    {
        if (is_null($product->getCustomAttribute('salesforce_price_book_entry_id'))) {
            $data = [
                'UnitPrice'        => $product->getPrice(),
                'Pricebook2Id'     => SalesforceHelper::PRICEBOOK_ID,
                'Product2Id'       => $product->getCustomAttribute('salesforce_product_id')->getValue(),
                'IsActive'         => true,
                'UseStandardPrice' => false,
            ];

            $this->curl->post(
                SalesforceHelper::API_URL . '/services/data/v56.0/sobjects/PricebookEntry',
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

    public function createSalesforceContract(CustomerInterface $customer)
    {
        if (is_null($this->getSalesforceContractId())) {
            $address = $this->addressRepository->getById($customer->getDefaultBilling());
            $data = [
                'AccountId'         => $customer->getCustomAttribute('salesforce_account_id')->getValue(),
                'Pricebook2Id'      => SalesforceHelper::PRICEBOOK_ID,
                'CustomerSignedId'  => $customer->getCustomAttribute('salesforce_contact_id')->getValue(),
                'BillingStreet'     => array_first($address->getStreet()),
                'BillingCity'       => $address->getCity(),
                'BillingPostalCode' => $address->getPostcode(),
                'BillingState'      => $address->getRegion()->getRegion(),
                'BillingCountry'    => $address->getCountryId(),
            ];

            $this->curl->post(
                SalesforceHelper::API_URL . '/services/data/v56.0/sobjects/Contract',
                json_encode($data)
            );

            if ($this->curl->getStatus() == 201) {
                $this->setSalesforceContractId(json_decode($this->curl->getBody())->id);
                $this->salesforceRepository->save($this);
            } else {
                $this->logger->error($this->curl->getStatus() . ' | ' . $this->curl->getBody());
            }
        }
    }

    public function createSalesforceOrder(CustomerInterface $customer)
    {
        if (is_null($this->getSalesforceOrderId())) {
            $billingAddress = $this->addressRepository->getById($customer->getDefaultBilling());
            $shippingAddress = $this->addressRepository->getById($customer->getDefaultShipping());
            $data = [
                'AccountId'          => $customer->getCustomAttribute('salesforce_account_id')->getValue(),
                'Pricebook2Id'       => SalesforceHelper::PRICEBOOK_ID,
                'ContractId'         => $this->getSalesforceContractId(),
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
                SalesforceHelper::API_URL . '/services/data/v56.0/sobjects/Order',
                json_encode($data)
            );

            if ($this->curl->getStatus() == 201) {
                $this->setSalesforceOrderId(json_decode($this->curl->getBody())->id);
                $this->salesforceRepository->save($this);
            } else {
                $this->logger->error($this->curl->getStatus() . ' | ' . $this->curl->getBody());
            }
        }
    }

    public function addProductsToSalesforceOrder($bearerToken)
    {
        $orderId = $this->getSalesforceOrderId();
        $products = json_decode($this->getProductIds(), true);
        foreach ($this->getProducts() as $product) {
            $products_data[] = [
                'attributes'       => [
                    'type' => 'OrderItem',
                ],
                'PricebookEntryId' => $product->getCustomAttribute('salesforce_price_book_entry_id')->getValue(),
                'quantity'         => $products[$product->getId()],
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

        $url = SalesforceHelper::API_URL . '/services/data/v56.0/commerce/sale/order/' . $orderId;
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $bearerToken,
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
            $this->logger->info('Salesforce integration #' . $this->getId() . ' successful!');
        } else {
            $this->logger->error($response);
        }
    }

    public function getProducts()
    {
        $searchCriteriaBuilder = $this->searchCriteriaBuilerFactory->create();
        $searchCriteria = $searchCriteriaBuilder->addFilter(
            'entity_id',
            array_keys(json_decode($this->getProductIds(),true)),
            'in'
        )->create();

        return $this->productRepository->getList($searchCriteria)->getItems();
    }
}
