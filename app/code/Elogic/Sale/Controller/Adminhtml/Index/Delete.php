<?php

namespace Elogic\Sale\Controller\Adminhtml\Index;

use Elogic\Sale\Api\SaleRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\CatalogRule\Api\CatalogRuleRepositoryInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Delete extends Action implements HttpPostActionInterface
{
    /**
     * @var \Elogic\Sale\Api\SaleRepositoryInterface
     */
    private SaleRepositoryInterface $saleRepository;
    /**
     * @var \Magento\CatalogRule\Api\CatalogRuleRepositoryInterface
     */
    private CatalogRuleRepositoryInterface $ruleRepository;

    /**
     * @param  \Magento\Backend\App\Action\Context  $context
     * @param  \Elogic\Sale\Api\SaleRepositoryInterface  $saleRepository
     * @param  \Magento\CatalogRule\Api\CatalogRuleRepositoryInterface  $ruleRepository
     */
    public function __construct(
        Context $context,
        SaleRepositoryInterface $saleRepository,
        CatalogRuleRepositoryInterface $ruleRepository,
    ) {
        parent::__construct($context);
        $this->saleRepository = $saleRepository;
        $this->ruleRepository = $ruleRepository;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function execute(): ResultInterface
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $saleId = (int) $this->getRequest()->getParam('id');

        if (!$saleId) {
            $this->messageManager->addErrorMessage(__('Error.'));

            return $resultRedirect->setPath('*/*/index');
        }

        try {
            $sale = $this->saleRepository->get($saleId);
            $this->saleRepository->delete($sale);
            $this->messageManager->addSuccessMessage(__('You deleted the sale.'));
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('Cannot delete sale'));
        }

        try {
            $this->ruleRepository->deleteById($sale->getCatalogPriceRuleID());
        } catch (NoSuchEntityException $e) {
        }

        return $resultRedirect->setPath('*/*/index');
    }
}
