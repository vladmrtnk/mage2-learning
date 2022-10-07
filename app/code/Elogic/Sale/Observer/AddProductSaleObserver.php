<?php

namespace Elogic\Sale\Observer;

use Elogic\Sale\Api\SaleRepositoryInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class AddProductSaleObserver implements ObserverInterface
{
    private $productID;
    private $sales;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    private FilterBuilder $filterBuilder;
    /**
     * @var \Elogic\Sale\Api\SaleRepositoryInterface
     */
    private SaleRepositoryInterface $saleRepository;

    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        SaleRepositoryInterface $saleRepository
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->saleRepository = $saleRepository;
    }

    /**
     * @param  \Magento\Framework\Event\Observer  $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Catalog\Model\Product\Interceptor $product */
        $product = $observer->getData()['product'];
        $this->productID = $product->getId();
        $price = $product->getPrice();

        if ($this->hasSale()) {
            $sale = $this->getBiggerSale();
            $product->setPrice($price - ($price / 100) * $sale->getPercentDiscount());
        }
    }

    /**
     * @return \Elogic\Sale\Model\Sale|null
     */
    private function getBiggerSale()
    {
        $biggerPercent = 0;
        $biggerSale = null;
        if (is_null($this->sales)) {
            $this->getSales();
        }

        /** @var \Elogic\Sale\Model\Sale $sale */
        foreach ($this->sales as $sale) {
            if ($sale->getPercentDiscount() > $biggerPercent) {
                $biggerPercent = $sale->getPercentDiscount();
                $biggerSale = $sale;
            }
        }

        return $biggerSale;
    }

    /**
     * @return \Magento\Framework\Api\ExtensibleDataInterface[]
     */
    private function getSales()
    {
        $this->searchCriteriaBuilder->addFilter('products', '%"' . $this->productID . '"%', 'like');
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchResults = $this->saleRepository->getList($searchCriteria);
        $this->sales = $searchResults->getItems();

        return $this->sales;
    }

    /**
     * @return bool
     */
    private function hasSale()
    {
        if (is_null($this->sales)) {
            $this->getSales();
        }

        return !empty($this->sales);
    }
}
