<?php

namespace Elogic\Sale\Observer;

use Elogic\Sale\Api\SaleRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogRule\Api\CatalogRuleRepositoryInterface;
use Magento\CatalogRule\Model\RuleFactory;
use Magento\CatalogRule\Model\Rule\JobFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SetCatalogPriceRuleObserver implements ObserverInterface
{
    /**
     * @var \Magento\CatalogRule\Api\CatalogRuleRepositoryInterface
     */
    private CatalogRuleRepositoryInterface $ruleRepository;
    /**
     * @var \Magento\CatalogRule\Model\RuleFactory
     */
    private RuleFactory $ruleFactory;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    /**
     * @var \Magento\CatalogRule\Model\Rule\JobFactory
     */
    private JobFactory $ruleJobFactory;
    /**
     * @var \Elogic\Sale\Api\SaleRepositoryInterface
     */
    private SaleRepositoryInterface $saleRepository;

    /**
     * @param  \Magento\CatalogRule\Api\CatalogRuleRepositoryInterface  $ruleRepository
     * @param  \Magento\CatalogRule\Model\RuleFactory  $ruleFactory
     * @param  \Magento\Catalog\Api\ProductRepositoryInterface  $productRepository
     * @param  \Magento\Framework\Api\SearchCriteriaBuilder  $searchCriteriaBuilder
     * @param  \Magento\CatalogRule\Model\Rule\JobFactory  $ruleJobFactory
     * @param  \Elogic\Sale\Api\SaleRepositoryInterface  $saleRepository
     */
    public function __construct(
        CatalogRuleRepositoryInterface $ruleRepository,
        RuleFactory $ruleFactory,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        JobFactory $ruleJobFactory,
        SaleRepositoryInterface $saleRepository,
    ) {
        $this->ruleRepository = $ruleRepository;
        $this->ruleFactory = $ruleFactory;
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->ruleJobFactory = $ruleJobFactory;
        $this->saleRepository = $saleRepository;
    }

    /**
     * @param  \Magento\Framework\Event\Observer  $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        $sale = $observer->getData('sale');

        try {
            /** @var \Magento\CatalogRule\Model\Rule $rule */
            $rule = $this->ruleRepository->get($sale->getCatalogPriceRuleID());
        } catch (\Exception $e) {
            $rule = $this->ruleFactory->create();
        }

        try {
            $rule
                ->setName($sale->getTitle())
                ->setDescription($sale->getDescription())
                ->setIsActive(1)
                ->setCustomerGroupIds([0])
                ->setWebsiteIds([1])
                ->setFromDate($sale->getValidFrom())
                ->setToDate($sale->getValidUntil())
                ->setSimpleAction('by_percent')
                ->setDiscountAmount($sale->getPercentDiscount())
                ->setStopRulesProcessing(1);

            $sku = $this->getSkuProducts($sale->getProducts());

            $conditions["1"] = [
                "type"       => "Magento\CatalogRule\Model\Rule\Condition\Combine",
                "aggregator" => "all",
                "value"      => 1,
                "new_child"  => "",
            ];
            $conditions["1--1"] = [
                "type"      => "Magento\CatalogRule\Model\Rule\Condition\Product",
                "attribute" => "sku",
                "operator"  => "()",
                "value"     => implode(', ', $sku),
            ];

            $rule->setData('conditions', $conditions);
            $rule->loadPost($rule->getData());
            $rule->save();
            $ruleJob = $this->ruleJobFactory->create();
            $ruleJob->applyAll();

            $this->setCatalogPriceRuleId($sale, $rule->getId());
        } catch (\Exception $e) {
        }
    }

    /**
     * @param  array|null  $ids
     *
     * @return array
     */
    private function getSkuProducts(array $ids = null)
    {
        if (is_null($ids)) {
            return [];
        }

        $this->searchCriteriaBuilder->addFilter('entity_id', $ids, 'in');
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchResults = $this->productRepository->getList($searchCriteria);
        $products = $searchResults->getItems();

        foreach ($products as $product) {
            $sku[] = $product->getSku();
        }

        return $sku;
    }

    /**
     * @param $sale
     * @param  int  $id
     *
     * @return void
     */
    private function setCatalogPriceRuleId($sale, int $id)
    {
        /** @var \Elogic\Sale\Model\Sale $sale */
        $sale->setCatalogPriceRuleID($id);
        $this->saleRepository->save($sale);
    }
}
