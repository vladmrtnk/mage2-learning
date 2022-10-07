<?php

namespace Elogic\Sale\Controller\Index;

use Elogic\Sale\Model\SaleRepository;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;

class Show implements HttpGetActionInterface
{
    private PageFactory $pageFactory;
    private SaleRepository $saleRepository;

    public function __construct(
        PageFactory $pageFactory,
        SaleRepository $saleRepository,
    ) {
        $this->pageFactory = $pageFactory;
        $this->saleRepository = $saleRepository;
    }

    public function execute()
    {
        $result = $this->pageFactory->create();

        return $result;
    }
}
