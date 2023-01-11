<?php

namespace Elogic\OneStepCheckout\Plugin;

use Magento\Checkout\Model\CompositeConfigProvider;
use Magento\Framework\App\Config\ScopeConfigInterface;

class DefaultPaymentConfigProvider
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param \Magento\Checkout\Model\CompositeConfigProvider $subject
     * @param $config
     * @return void
     */
    public function afterGetConfig(CompositeConfigProvider $subject, $config)
    {
        $config['oneStepCheckout'] = (bool)$this->scopeConfig->getValue('one_step_checkout/general/enable');

        return $config;
    }
}