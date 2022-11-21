<?php

namespace Elogic\Sale\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Psr\Log\LoggerInterface;

class ProductConsumer
{
    public function __construct(
        ProductRepositoryInterface $productRepository,
        LoggerInterface $logger,
    ) {
        $this->productRepository = $productRepository;
        $this->logger = $logger;
    }

    public function execute(ProductInterface $product)
    {
        try {
            $this->productRepository->save($product);
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }

        $this->logger->info('Product name: ' . $product->getName());
    }
}
