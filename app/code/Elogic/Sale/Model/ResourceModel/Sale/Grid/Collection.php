<?php

namespace Elogic\Sale\Model\ResourceModel\Sale\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Psr\Log\LoggerInterface as Logger;

class Collection extends SearchResult
{
    /**
     * @param  \Magento\Framework\Data\Collection\EntityFactoryInterface  $entityFactory
     * @param  \Psr\Log\LoggerInterface  $logger
     * @param  \Magento\Framework\Data\Collection\Db\FetchStrategyInterface  $fetchStrategy
     * @param  \Magento\Framework\Event\ManagerInterface  $eventManager
     * @param $mainTable
     * @param $resourceModel
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        $mainTable = 'learning_sample_item',
        $resourceModel = 'Learning\FirstUnit\Model\ResourceModel\Item',
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);
    }
}
