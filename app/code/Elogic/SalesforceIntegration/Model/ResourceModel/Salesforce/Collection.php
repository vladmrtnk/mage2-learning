<?php

namespace Elogic\SalesforceIntegration\Model\ResourceModel\Salesforce;

use Elogic\SalesforceIntegration\Model\ResourceModel\Salesforce as ResourceModel;
use Elogic\SalesforceIntegration\Model\Salesforce as Model;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'elogic_salesforce_collection';

    /**
     * Initialize collection model.
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
