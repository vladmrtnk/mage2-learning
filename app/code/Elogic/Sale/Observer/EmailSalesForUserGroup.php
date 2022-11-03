<?php

namespace Elogic\Sale\Observer;

use Elogic\Sale\Helper\Email;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class EmailSalesForUserGroup implements ObserverInterface
{
    /**
     * @var \Elogic\Sale\Helper\Email
     */
    protected Email $email;

    /**
     * @param  \Elogic\Sale\Helper\Email  $emailHelper
     */
    public function __construct(
        Email $emailHelper,
    ) {
        $this->email = $emailHelper;
    }

    /**
     * @param  \Magento\Framework\Event\Observer  $observer
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     */
    public function execute(Observer $observer)
    {
        if ($observer->getData('is_new')) {
            $sale = $observer->getData('sale');
            $vars = [
                'title'       => $sale->getTitle(),
                'image'       => $sale->getImagePath(),
                'description' => $sale->getDescription(),
                'percent'     => $sale->getPercentDiscount(),
                'date_valid'  => date('j M Y', strtotime($sale->getValidFrom())) . ' - ' .
                    date('j M Y', strtotime($sale->getValidUntil())),
            ];

            $this->email->sendMailing('elogic_sales_for_user_group', $vars);
        }
    }
}
