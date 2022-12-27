<?php

namespace Elogic\SalesforceIntegration\Model\Salesforce;

use Elogic\SalesforceIntegration\Api\Data\SalesforceInterface;
use Magento\Framework\MessageQueue\PublisherInterface;

class SalesforcePublisher
{
    /**
     * RabbitMQ Topic name
     */
    const TOPIC_NAME = 'salesforce';
    /**
     * @var \Magento\Framework\MessageQueue\PublisherInterface
     */
    private PublisherInterface $publisher;

    /**
     * WhiteRabbitPublisher constructor
     *
     * @param \Magento\Framework\MessageQueue\PublisherInterface $publisher
     */
    public function __construct(
        PublisherInterface $publisher
    ) {
        $this->publisher = $publisher;
    }

    /**
     * @param  \Elogic\SalesforceIntegration\Api\Data\SalesforceInterface  $salesforce
     *
     * @return void
     */
    public function execute(SalesforceInterface $salesforce)
    {
        $this->publisher->publish(self::TOPIC_NAME, $salesforce);
    }
}
