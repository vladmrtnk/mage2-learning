<?php

namespace Elogic\Sale\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;

class Thumbnail extends Column
{
    public function prepareDataSource(array $dataSource)
    {
        foreach ($dataSource["data"]["items"] as &$item) {
            if (!is_null($item['sale_image_path'])) {
                $image = json_decode($item['sale_image_path'], true)[0];
                unset($item['sale_image_path']);
                $item['path_src'] = $image['url'];
                $item['path_link'] = $image['url'];
                $item['path_orig_src'] = $image['url'];
            }
            $item['path_alt'] = $item['slug'];
        }

        return $dataSource;
    }
}
