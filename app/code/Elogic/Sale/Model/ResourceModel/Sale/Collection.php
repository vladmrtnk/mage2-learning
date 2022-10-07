<?php

namespace Elogic\Sale\Model\ResourceModel\Sale;

use Elogic\Sale\Model\ResourceModel\Sale as SaleResource;
use Elogic\Sale\Model\Sale;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(Sale::class, SaleResource::class);
    }
}
