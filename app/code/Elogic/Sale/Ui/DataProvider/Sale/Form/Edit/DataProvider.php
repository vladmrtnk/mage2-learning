<?php

namespace Elogic\Sale\Ui\DataProvider\Sale\Form\Edit;

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
    public function getDataSourceData()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getData()
    {
        $data = $this->getCollection()->toArray();

        foreach ($data['items'] as &$item) {
            $item['products'] = json_decode($item['products']);
            if (!is_null($item['sale_image_path'])) {
                $item['sale_image_path'] = json_decode($item['sale_image_path']);
            }
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        return $this->meta;
    }
}
