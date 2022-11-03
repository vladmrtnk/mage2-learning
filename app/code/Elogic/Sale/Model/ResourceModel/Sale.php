<?php

namespace Elogic\Sale\Model\ResourceModel;

use Elogic\Sale\Api\Data\SaleInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Sale extends AbstractDb
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('elogic_sale_entity', SaleInterface::SALE_ID);
    }
}
