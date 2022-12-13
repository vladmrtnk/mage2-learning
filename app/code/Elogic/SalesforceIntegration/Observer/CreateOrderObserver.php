<?php

namespace Elogic\SalesforceIntegration\Observer;

use Elogic\SalesforceIntegration\Api\Data\SalesforceInterfaceFactory;
use Elogic\SalesforceIntegration\Model\SalesforcePublisher;
use Elogic\SalesforceIntegration\Model\SalesforceRepository;
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
     * @var \Elogic\SalesforceIntegration\Model\SalesforcePublisher
     */
    private SalesforcePublisher $publisher;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private LoggerInterface $logger;

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
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getOrder();

        foreach ($order->getItems() as $item) {
            if ($item->getProductType() == 'simple') {
                $products[$item->getProductId()] = [
                    'id'  => $item->getProductId(),
                    'qty' => $item->getQtyOrdered()
                ];
            }
        }

        $salesforce = $this->salesforceInterfaceFactory->create();
        $salesforce->setOrderId($order->getId());
        $salesforce->setCustomerId($order->getCustomerId());
        $salesforce->setProducts($products);
        $salesforce = $this->salesforceRepository->save($salesforce);

        try {
            $this->publisher->execute($salesforce);
        } catch (\Exception $exception) {
            $this->logger->error($exception);
        }
    }
}
