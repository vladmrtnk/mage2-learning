<?php

namespace Elogic\Sale\Cron;

use Elogic\Sale\Api\SaleRepositoryInterface;
use Elogic\Sale\Model\ResourceModel\Sale\CollectionFactory;
use Magento\CatalogRule\Api\CatalogRuleRepositoryInterface;

class AddValidTime
{
    /**
     * @var \Elogic\Sale\Model\ResourceModel\Sale\CollectionFactory
     */
    private CollectionFactory $saleCollection;
    /**
     * @var \Elogic\Sale\Api\SaleRepositoryInterface
     */
    private SaleRepositoryInterface $saleRepository;
    /**
     * @var \Magento\CatalogRule\Api\CatalogRuleRepositoryInterface
     */
    private CatalogRuleRepositoryInterface $ruleRepository;

    /**
     * @param  \Elogic\Sale\Model\ResourceModel\Sale\CollectionFactory  $saleCollection
     * @param  \Elogic\Sale\Api\SaleRepositoryInterface  $saleRepository
     * @param  \Magento\CatalogRule\Api\CatalogRuleRepositoryInterface  $ruleRepository
     */
    public function __construct(
        CollectionFactory $saleCollection,
        SaleRepositoryInterface $saleRepository,
        CatalogRuleRepositoryInterface $ruleRepository,
    ) {
        $this->saleCollection = $saleCollection;
        $this->saleRepository = $saleRepository;
        $this->ruleRepository = $ruleRepository;
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function execute()
    {
        $items = $this->saleCollection->create()->getItems();

        foreach ($items as $item) {
            /** @var \Elogic\Sale\Model\Sale $item */

            $timestamp = strtotime($item->getValidUntil()) + 60 * 60;

            $datetime = date('Y-m-d H:i:s', $timestamp);

            $item->setValidUntil($datetime);

            $this->saleRepository->save($item);

            $this->setCatalogPriceRuleValidTime($item->getId(), $datetime);
        }
    }

    /**
     * @param $ruleId
     * @param $datetime
     *
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    private function setCatalogPriceRuleValidTime($ruleId, $datetime)
    {
        try {
            /** @var \Magento\CatalogRule\Model\Rule $rule */
            $rule = $this->ruleRepository->get($ruleId);
        } catch (\Exception $e) {
            return;
        }

        $rule->setToDate($datetime);
        $this->ruleRepository->save($rule);
    }
}
