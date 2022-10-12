<?php

namespace Elogic\Sale\Controller\Index;

use Elogic\Sale\Model\SaleRepository;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;

class Show implements HttpGetActionInterface
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private PageFactory $pageFactory;
    /**
     * @var \Elogic\Sale\Model\SaleRepository
     */
    private SaleRepository $saleRepository;

    /**
     * @param  \Magento\Framework\View\Result\PageFactory  $pageFactory
     * @param  \Elogic\Sale\Model\SaleRepository  $saleRepository
     */
    public function __construct(
        PageFactory $pageFactory,
        SaleRepository $saleRepository,
    ) {
        $this->pageFactory = $pageFactory;
        $this->saleRepository = $saleRepository;
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $result = $this->pageFactory->create();

        return $result;
    }
}
