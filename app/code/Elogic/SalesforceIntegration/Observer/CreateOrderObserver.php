<?php

namespace Elogic\SalesforceIntegration\Observer;

use Elogic\SalesforceIntegration\Api\Data\SalesforceInterfaceFactory;
use Elogic\SalesforceIntegration\Model\Salesforce\SalesforcePublisher;
use Elogic\SalesforceIntegration\Model\SalesforceRepository;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class CreateOrderObserver implements ObserverInterface
{
    /**
     * @var \Elogic\SalesforceIntegration\Api\Data\SalesforceInterfaceFactory
     */
    private salesforceInterfaceFactory $salesforceInterfaceFactory;
    /**
     * @var \Elogic\SalesforceIntegration\Model\SalesforceRepository
     */
    private SalesforceRepository $salesforceRepository;
    /**
     * @var \Elogic\SalesforceIntegration\Model\Salesforce\SalesforcePublisher
     */
    private SalesforcePublisher $publisher;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param \Elogic\SalesforceIntegration\Api\Data\SalesforceInterfaceFactory $salesforceInterfaceFactory
     * @param \Elogic\SalesforceIntegration\Model\SalesforceRepository $salesforceRepository
     * @param \Elogic\SalesforceIntegration\Model\Salesforce\SalesforcePublisher $publisher
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        SalesforceInterfaceFactory $salesforceInterfaceFactory,
        SalesforceRepository $salesforceRepository,
        SalesforcePublisher $publisher,
        LoggerInterface $logger,
    ) {
        $this->salesforceInterfaceFactory = $salesforceInterfaceFactory;
        $this->salesforceRepository = $salesforceRepository;
        $this->publisher = $publisher;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getOrder();

        foreach ($order->getItems() as $item) {
            /** @var \Magento\Sales\Model\Order\Item $item */
            if ($item->getProductType() == 'simple') {
                $products[$item->getProductId()] = (int) $item->getQtyOrdered();
            }
        }

        $salesforce = $this->salesforceInterfaceFactory->create();
        $salesforce->setOrderId($order->getId());
        $salesforce->setCustomerId($order->getCustomerId());
        $salesforce->setProductIds(json_encode($products));
        $salesforce = $this->salesforceRepository->save($salesforce);
        file_put_contents($_SERVER['DOCUMENT_ROOT'].'/test.txt', json_encode($salesforce), FILE_APPEND);

        try {
            $this->publisher->execute($salesforce);
        } catch (\Exception $exception) {
            $this->logger->error($exception);
        }
    }
}
