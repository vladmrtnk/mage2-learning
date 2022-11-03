<?php

namespace Elogic\Sale\Helper;

use Magento\Customer\Model\ResourceModel\Customer\Collection;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Escaper;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;

class Email extends AbstractHelper
{
    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected StateInterface $inlineTranslation;
    /**
     * @var \Magento\Framework\Escaper
     */
    protected Escaper $escaper;
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected TransportBuilder $transportBuilder;
    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\Collection
     */
    protected Collection $customerCollection;

    /**
     * @param  \Magento\Framework\App\Helper\Context  $context
     * @param  \Magento\Framework\Translate\Inline\StateInterface  $inlineTranslation
     * @param  \Magento\Framework\Escaper  $escaper
     * @param  \Magento\Framework\Mail\Template\TransportBuilder  $transportBuilder
     * @param  \Magento\Customer\Model\ResourceModel\Customer\Collection  $customerCollection
     */
    public function __construct(
        Context $context,
        StateInterface $inlineTranslation,
        Escaper $escaper,
        TransportBuilder $transportBuilder,
        Collection $customerCollection,
    ) {
        parent::__construct($context);
        $this->inlineTranslation = $inlineTranslation;
        $this->escaper = $escaper;
        $this->transportBuilder = $transportBuilder;
        $this->customerCollection = $customerCollection;
    }

    /**
     * @param $templateId
     * @param  array  $templateVars
     * @param $recipient
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     */
    public function send($templateId, array $templateVars, $recipient)
    {
        $this->inlineTranslation->suspend();
        $transport = $this->transportBuilder
            ->setTemplateIdentifier($templateId)
            ->setTemplateOptions(
                [
                    'area'  => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                ]
            )
            ->setTemplateVars($templateVars)
            ->setFromByScope([
                'name'  => $this->scopeConfig->getValue('trans_email/ident_sales/name'),
                'email' => $this->scopeConfig->getValue('trans_email/ident_sales/email')
            ])
            ->addTo($recipient)
            ->getTransport();
        $transport->sendMessage();
        $this->inlineTranslation->resume();
    }

    /**
     * @param $templateId
     * @param  array  $templateVars
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\MailException
     */
    public function sendMailing($templateId, array $templateVars)
    {
        $enabled = $this->scopeConfig->getValue('email_notification/general/enable');

        if ($enabled) {
            foreach ($this->getCustomerEmails() as $email) {
                $this->send($templateId, $templateVars, $email);
            }
        }
    }

    /**
     * @return array
     */
    private function getCustomerEmails()
    {
        $groupId = $this->scopeConfig->getValue('email_notification/general/customer_group');

        $customers = $this->customerCollection->addFieldToFilter('group_id', $groupId)->getData();

        foreach ($customers as $customer) {
            $emails[] = $customer['email'];
        }

        return $emails;
    }
}

