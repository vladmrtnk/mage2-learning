<?php

namespace Elogic\Sale\Controller;

use Elogic\Sale\Api\SaleRepositoryInterface;
use Magento\Framework\App\Action\Forward;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\RouterInterface;
use Magento\Framework\UrlInterface;

class Router implements RouterInterface
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected ActionFactory $actionFactory;
    /**
     * @var \Elogic\Sale\Api\SaleRepositoryInterface
     */
    protected SaleRepositoryInterface $saleRepository;

    /**
     * @param  \Magento\Framework\App\ActionFactory  $actionFactory
     * @param  \Elogic\Sale\Api\SaleRepositoryInterface  $saleRepository
     */
    public function __construct(
        ActionFactory $actionFactory,
        SaleRepositoryInterface $saleRepository,
    ) {
        $this->actionFactory = $actionFactory;
        $this->saleRepository = $saleRepository;
    }

    /**
     * @param  \Magento\Framework\App\RequestInterface  $request
     *
     * @return \Magento\Framework\App\ActionInterface|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function match(RequestInterface $request)
    {
        $identifier = trim($request->getPathInfo(), '/');

        if (str_contains($identifier, 'sale')) {
            $slug = explode('/', $identifier)[1];

            try {
                $this->saleRepository->getBySlug($slug);
            } catch (\Exception $e) {
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
