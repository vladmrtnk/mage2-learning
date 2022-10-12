<?php

namespace Elogic\Sale\Controller\Adminhtml\Index;

use Elogic\Sale\Model\SaleRepository;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class Edit extends Action
{
    /**
     * @var \Elogic\Sale\Model\SaleRepository
     */
    private SaleRepository $saleRepository;

    /**
     * @param  \Magento\Backend\App\Action\Context  $context
     * @param  \Elogic\Sale\Model\SaleRepository  $saleRepository
     */
    public function __construct(
        Context $context,
        SaleRepository $saleRepository
    ) {
        $this->saleRepository = $saleRepository;
        parent::__construct($context);
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        try {
            $sale = $this->saleRepository->get($id);

            /** @var \Magento\Backend\Model\View\Result\Page $result */
            $result = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
            $result->setActiveMenu('Elogic_Sale::sale');
            $result->getConfig()->getTitle()->prepend(__('Edit sale: "%value"', ['value' => $sale->getTitle()]));
        } catch (NoSuchEntityException $e) {
            $result = $this->resultRedirectFactory->create();
            $this->messageManager->addErrorMessage(__('Sale with id "%value" does not exist', ['value' => $id]));
            $result->setPath('sale');
        }

        return $result;
    }
}
