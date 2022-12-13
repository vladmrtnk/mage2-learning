<?php

namespace Elogic\SalesforceIntegration\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Salesforce extends AbstractDb
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'elogic_salesforce_resource_model';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('elogic_salesforce', 'entity_id');
        $this->_useIsObjectNew = true;
    }
}
