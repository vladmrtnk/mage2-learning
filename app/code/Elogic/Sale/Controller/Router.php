<?php

namespace Elogic\Sale\Controller;

use Elogic\Sale\Model\SaleFactory;
use Magento\Framework\App\Action\Forward;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\RouterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

class Router implements RouterInterface
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;
    /**
     * Event manager
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * Config primary
     *
     * @var \Magento\Framework\App\State
     */
    protected $_appState;
    /**
     * Url
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;
    /**
     * Sale Factory
     *
     * @var\Elogic\Sale\Model\SaleFactory
     */
    private $_saleFactory;
    /**
     * Response
     *
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;

    /**
     * @param  \Magento\Framework\App\ActionFactory  $actionFactory
     * @param  \Magento\Framework\Event\ManagerInterface  $eventManager
     * @param  \Magento\Framework\UrlInterface  $url
     * @param  \Elogic\Sale\Model\SaleFactory  $saleFactory
     * @param  \Magento\Store\Model\StoreManagerInterface  $storeManager
     * @param  \Magento\Framework\App\ResponseInterface  $response
     */
    public function __construct(
        ActionFactory $actionFactory,
        ManagerInterface $eventManager,
        UrlInterface $url,
        SaleFactory $saleFactory,
        StoreManagerInterface $storeManager,
        ResponseInterface $response
    ) {
        $this->actionFactory = $actionFactory;
        $this->_eventManager = $eventManager;
        $this->_url = $url;
        $this->_saleFactory = $saleFactory;
        $this->_storeManager = $storeManager;
        $this->_response = $response;
    }

    /**
     * @param  RequestInterface  $request
     *
     * @return ActionInterface|null
     */
    public function match(RequestInterface $request)
    {
        $identifier = trim($request->getPathInfo(), '/');

        if (str_contains($identifier, 'sale')) {
            $slug = explode('/', $identifier)[1];

            /** @var \Elogic\Sale\Model\Sale $sale */
            $sale = $this->_saleFactory->create();
            $saleId = $sale->checkIdentifier($identifier);
            if (!$saleId) {
                return null;
            }

            $request
                ->setModuleName('sale')
                ->setControllerName('index')
                ->setActionName('show')
                ->setParam('slug', $slug)
                ->setAlias(UrlInterface::REWRITE_REQUEST_PATH_ALIAS, $identifier);

            return $this->actionFactory->create(Forward::class);
        }

        return null;
    }
}
