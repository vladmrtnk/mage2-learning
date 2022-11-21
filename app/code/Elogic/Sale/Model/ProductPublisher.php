<?php

namespace Elogic\Sale\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\MessageQueue\PublisherInterface;

class ProductPublisher
{
    /**
     * RabbitMQ Topic name
     */
    const TOPIC_NAME = 'product';
    /**
     * @var \Magento\Framework\MessageQueue\PublisherInterface
     */
    private PublisherInterface $publisher;

    /**
     * WhiteRabbitPublisher constructor
     *
     * @param  \Magento\Framework\MessageQueue\PublisherInterface  $publisher
     */
    public function __construct(
        PublisherInterface $publisher
    ) {
        $this->publisher = $publisher;
    }

    /**
     * Add message to queue
     *
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @return void
     */
    public function execute(ProductInterface $product)
    {
        $this->publisher->publish(self::TOPIC_NAME, $product);
    }
}
