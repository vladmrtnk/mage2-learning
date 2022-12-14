<?php

namespace Elogic\SalesforceIntegration\Helper;

use Elogic\SalesforceIntegration\Api\Data\SalesforceInterface;
use Elogic\SalesforceIntegration\Helper\Salesforce as SalesforceHelper;
use Elogic\SalesforceIntegration\Model\SalesforceRepository;
use Laminas\View\Helper\AbstractHelper;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Api\SearchCriteriaBuilderFactory;
use Magento\Framework\HTTP\Client\Curl as MagentoCurl;
use Psr\Log\LoggerInterface;


class Communication extends AbstractHelper
{
    /**
     * @var \Elogic\SalesforceIntegration\Api\Data\SalesforceInterface
     */
    private SalesforceInterface $salesforce;
    /**
     * @var \Magento\Customer\Api\Data\CustomerInterface
     */
    private CustomerInterface $customer;
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private CustomerRepositoryInterface $customerRepository;
    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    private AddressRepositoryInterface $addressRepository;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;
    /**
     * @var \Elogic\SalesforceIntegration\Model\SalesforceRepository
     */
    private SalesforceRepository $salesforceRepository;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilderFactory
     */
    private SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private LoggerInterface $logger;
    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    private MagentoCurl $curl;

    /**
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Elogic\SalesforceIntegration\Model\SalesforceRepository $salesforceRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory
     *
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        AddressRepositoryInterface $addressRepository,
        ProductRepositoryInterface $productRepository,
        SalesforceRepository $salesforceRepository,
        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory,
        LoggerInterface $logger,
        MagentoCurl $curl,
    ) {
        $this->addressRepository = $addressRepository;
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
        $this->salesforceRepository = $salesforceRepository;
        $this->searchCriteriaBuilderFactory = $searchCriteriaBuilderFactory;
        $this->logger = $logger;
        $this->curl = $curl;
    }

    /**
     * Using OAuth2 get bearer token
     *
     * @return mixed
     */
    public function getToken()
    {
        $this->curl->post(
            'https://login.salesforce.com/services/oauth2/token',
            SalesforceHelper::ACCESS,
        );

        $result = json_decode($this->curl->getBody())->access_token;

        return $result;
    }

    /**
     * Set header for API requests
     *
     * @param $bearerToken
     * @return void
     */
    public function setHeader($bearerToken)
    {
        $this->curl->setHeaders([
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $bearerToken,
        ]);
    }

    /**
     * Set Salesforce model for using in other methods
     *
     * @param \Elogic\SalesforceIntegration\Api\Data\SalesforceInterface $salesforce
     * @return void
     */
    public function setSalesforce(SalesforceInterface $salesforce)
    {
        $this->salesforce = $salesforce;
    }

    /**
     * Set Salesforce model for using in other methods
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return void
     */
    public function setCustomer(CustomerInterface $customer)
    {
        $this->customer = $customer;
    }

    /**
     * Send request for creating Account
     *
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    public function createSalesforceAccount()
    {
        if (is_null($this->customer->getCustomAttribute('salesforce_account_id'))) {
            $data = [
                'Name' => $this->customer->getFirstname() . ' ' . $this->customer->getLastname(),
            ];

            $this->curl->post(
                SalesforceHelper::API_URL . '/services/data/v56.0/sobjects/Account',
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

    /**
     * Send request for creating Contact
     *
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\State\InputMismatchException
     */
    public function createSalesforceContact()
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
                SalesforceHelper::API_URL . '/services/data/v56.0/sobjects/Contact',
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

    /**
     * Send requests for creating Products
     *
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function createSalesforceProducts()
    {
        foreach ($this->getProducts($this->salesforce) as $product) {
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

    /**
     * Send request for adding Product to PriceBookEntry
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     */
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

    /**
     * Send request for creating Contract
     *
     * @return void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createSalesforceContract()
    {
        if (is_null($this->salesforce->getSalesforceContractId())) {
            $address = $this->addressRepository->getById($this->customer->getDefaultBilling());
            $data = [
                'AccountId'         => $this->customer->getCustomAttribute('salesforce_account_id')->getValue(),
                'Pricebook2Id'      => SalesforceHelper::PRICEBOOK_ID,
                'CustomerSignedId'  => $this->customer->getCustomAttribute('salesforce_contact_id')->getValue(),
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
                $this->salesforce->setSalesforceContractId(json_decode($this->curl->getBody())->id);
                $this->salesforceRepository->save($this->salesforce);
            } else {
                $this->logger->error($this->curl->getStatus() . ' | ' . $this->curl->getBody());
            }
        }
    }

    /**
     * Send request for creating Order
     *
     * @return void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createSalesforceOrder()
    {
        if (is_null($this->salesforce->getSalesforceOrderId())) {
            $billingAddress = $this->addressRepository->getById($this->customer->getDefaultBilling());
            $shippingAddress = $this->addressRepository->getById($this->customer->getDefaultShipping());
            $data = [
                'AccountId'          => $this->customer->getCustomAttribute('salesforce_account_id')->getValue(),
                'Pricebook2Id'       => SalesforceHelper::PRICEBOOK_ID,
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
                SalesforceHelper::API_URL . '/services/data/v56.0/sobjects/Order',
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

    /**
     * Send request for adding products to Order
     *
     * @param $bearerToken
     * @return void
     */
    public function addProductsToSalesforceOrder($bearerToken)
    {
        $orderId = $this->salesforce->getSalesforceOrderId();
        $products = json_decode($this->salesforce->getProductIds(), true);
        foreach ($this->getProducts($this->salesforce) as $product) {
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
            $this->logger->info('Salesforce integration #' . $this->salesforce->getId() . ' successful!');
        } else {
            $this->logger->error($response);
        }
    }

    /**
     * Get Product Collection
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface[]
     */
    public function getProducts()
    {
        $searchCriteriaBuilder = $this->searchCriteriaBuilderFactory->create();
        $searchCriteria = $searchCriteriaBuilder->addFilter(
            'entity_id',
            array_keys(json_decode($this->salesforce->getProductIds(), true)),
            'in'
        )->create();

        return $this->productRepository->getList($searchCriteria)->getItems();
    }
}