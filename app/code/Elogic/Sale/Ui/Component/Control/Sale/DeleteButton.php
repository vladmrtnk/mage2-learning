<?php

namespace Elogic\Sale\Ui\Component\Control\Sale;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getButtonData()
    {
        if ($this->getSale()) {
            return [
                'id'         => 'delete',
                'label'      => __('Delete'),
                'on_click'   => "deleteConfirm('" . __('Are you sure you want to delete this sale?') . "', '"
                    . $this->getUrl('*/*/delete', ['id' => $this->getSale()]) . "', {data: {}})",
                'class'      => 'delete',
                'sort_order' => 10,
            ];
        }

        return [];
    }
}
