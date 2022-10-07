<?php

namespace Elogic\Sale\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class UpdateProductSaleObserver implements ObserverInterface
{
    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Checkout\Model\Cart\Interceptor $data */
        $data = $observer->getData()['cart'];

        /** @var \Magento\Quote\Model\Quote\Interceptor $quote */
        $quote = $data->getQuote();

        $items = $quote->getAllVisibleItems();

        if (!is_null($items)) {
            foreach ($items as $item) {
                $product = $item->getProduct();
                $product->setPrice($item->getPrice());
            }
        }
    }
}
