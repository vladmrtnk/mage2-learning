<?php

namespace Elogic\Sale\Ui\Component\Control\Sale;

use Elogic\Sale\Model\SaleRepository;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;

class GenericButton
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private UrlInterface $urlBuilder;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private RequestInterface $request;
    /**
     * @var \Elogic\Sale\Model\SaleRepository
     */
    private SaleRepository $saleRepository;

    /**
     * @param  \Magento\Framework\UrlInterface  $urlBuilder
     * @param  \Magento\Framework\App\RequestInterface  $request
     * @param  \Elogic\Sale\Model\SaleRepository  $saleRepository
     */
    public function __construct(
        UrlInterface $urlBuilder,
        RequestInterface $request,
        SaleRepository $saleRepository
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
        $this->saleRepository = $saleRepository;
    }

    /**
     * @param $route
     * @param $params
     *
     * @return string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->urlBuilder->getUrl($route, $params);
    }

    /**
     * @return int|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSale()
    {
        $saleId = $this->request->getParam('id');
        if ($saleId === null) {
            return 0;
        }
        $sale = $this->saleRepository->get($saleId);

        return $sale->getId() ?: null;
    }
}

