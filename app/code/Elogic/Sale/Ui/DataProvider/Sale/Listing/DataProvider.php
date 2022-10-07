<?php

namespace Elogic\Sale\Ui\DataProvider\Sale\Listing;

use Elogic\Sale\Model\ResourceModel\Sale\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;

class DataProvider extends AbstractDataProvider
{
    /**
     * @param  string  $name
     * @param  string  $primaryFieldName
     * @param  string  $requestFieldName
     * @param  \Elogic\Sale\Model\ResourceModel\Sale\CollectionFactory  $collectionFactory
     * @param  array  $meta
     * @param  array  $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
    }

    /**
     * @return array
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->load();
        }

        return $this->getCollection()->toArray();
    }
}