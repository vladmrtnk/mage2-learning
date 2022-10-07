<?php

namespace Elogic\Product\Model\Carrier;

use Elogic\Product\Helper\Product;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class NovaPoshta extends AbstractCarrier implements CarrierInterface
{
    protected $_code = 'nova_poshta';
    protected $_isFixed = true;
    private ResultFactory $rateResultFactory;
    private MethodFactory $rateMethodFactory;
    private CollectionFactory $productCollectionFactory;

    /**
     * @param  \Magento\Framework\App\Config\ScopeConfigInterface  $scopeConfig
     * @param  \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory  $rateErrorFactory
     * @param  \Psr\Log\LoggerInterface  $logger
     * @param  \Magento\Shipping\Model\Rate\ResultFactory  $rateResultFactory
     * @param  \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory  $rateMethodFactory
     * @param  \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory  $productCollectionFactory
     * @param  array  $data  \
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        CollectionFactory $productCollectionFactory,
        array $data = []
    ) {
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
        $this->rateResultFactory = $rateResultFactory;
        $this->rateMethodFactory = $rateMethodFactory;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * Custom Shipping Rates Collector
     *
     * @param  RateRequest  $request
     *
     * @return \Magento\Shipping\Model\Rate\Result|bool
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        if ($this->getConfigFlag('price_for_girls_active')) {
            foreach ($request->getAllItems() as $item) {
                $ids[] = $item->getProductId();
            }

            $products = $this->productCollectionFactory->create()
                ->addAttributeToSelect('child_gender')
                ->addIdFilter($ids)
                ->getItems();

            foreach ($products as $product) {
                if ($product->getAttributeText('child_gender') == 'Girl') {
                    $shippingCost = (float) $this->getConfigData('price_for_girls');
                    break;
                }
            }
        }

        if (empty($shippingCost)) {
            $shippingCost = (float) $this->getConfigData('shipping_cost');
        }

        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->rateResultFactory->create();

        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
        $method = $this->rateMethodFactory->create();

        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod($this->_code);
        $method->setMethodTitle($this->getConfigData('name'));

        $method->setPrice($shippingCost);
        $method->setCost($shippingCost);

        $result->append($method);

        return $result;
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }
}
