<?php

namespace Elogic\Sale\Cron;

use Elogic\Product\Helper\SimpleProductData;
use Elogic\Sale\Model\ProductPublisher;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;

class PublishProductToRabbit
{
    /**
     * @var ProductPublisher
     */
    protected ProductPublisher $publisher;
    /**
     * @var ProductInterfaceFactory
     */
    protected ProductInterfaceFactory $productInterfaceFactory;

    /**
     * @param ProductPublisher $publisher
     * @param ProductInterfaceFactory $productInterfaceFactory
     */
    public function __construct(
        ProductPublisher        $publisher,
        ProductInterfaceFactory $productInterfaceFactory,
    ) {
        $this->publisher = $publisher;
        $this->productInterfaceFactory = $productInterfaceFactory;
    }

    /**
     * @return void
     */
    public function execute()
    {
        for ($i = 0; $i < 5; $i++) {
            $product = $this->productInterfaceFactory->create();
            $product->setData(SimpleProductData::PRODUCT_DATA);
            $rand = rand(1, 99999);

            $product->setSku($product->getSku() . '-RABBIT-' . $rand);
            $product->setName($product->getName() . ' by RabbitMQ ' . $rand);

            $this->publisher->execute($product);
        }
    }
}
