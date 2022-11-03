<?php

namespace Elogic\Sale\Controller\Adminhtml\Index;

use Elogic\Sale\Api\Data\SaleInterface;
use Elogic\Sale\Api\SaleRepositoryInterface;
use Elogic\Sale\Model\SaleFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;

class Save extends Action implements HttpPostActionInterface
{
    /**
     * @var \Elogic\Sale\Api\SaleRepositoryInterface
     */
    private SaleRepositoryInterface $saleRepository;
    /**
     * @var \Elogic\Sale\Model\SaleFactory
     */
    private SaleFactory $saleFactory;

    /**
     * @param  \Magento\Backend\App\Action\Context  $context
     * @param  \Elogic\Sale\Api\SaleRepositoryInterface  $saleRepository
     * @param  \Elogic\Sale\Model\SaleFactory  $saleFactory
     */
    public function __construct(
        Context $context,
        SaleRepositoryInterface $saleRepository,
        SaleFactory $saleFactory,
    ) {
        parent::__construct($context);
        $this->saleRepository = $saleRepository;
        $this->saleFactory = $saleFactory;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute(): ResultInterface
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $request = $this->getRequest();
        $requestData = $request->getPost()->toArray();

        if (!$request->isPost() || empty($requestData['general'])) {
            $this->messageManager->addErrorMessage(__('Wrong request.'));
            $resultRedirect->setPath('*/*/new');

            return $resultRedirect;
        }

        try {
            $id = $requestData['general'][SaleInterface::SALE_ID];
            $sale = $this->saleRepository->get($id);
            $isNew = false;
        } catch (\Exception $e) {
            $sale = $this->saleFactory->create();
            $isNew = true;
        }

        $sale->setTitle($requestData['general'][SaleInterface::TITLE]);
        $sale->setSlug($requestData['general'][SaleInterface::SLUG]);
        $sale->setDescription($requestData['general'][SaleInterface::DESCRIPTION]);
        $sale->setPercentDiscount($requestData['general'][SaleInterface::PERCENT_DISCOUNT]);
        $sale->setValidFrom($requestData['general'][SaleInterface::VALID_FROM]);
        $sale->setValidUntil($requestData['general'][SaleInterface::VALID_UNTIL]);
        $sale->setProducts($requestData['general'][SaleInterface::PRODUCTS] ?? []);

        $image = isset($requestData['general'][SaleInterface::IMAGE_PATH]) ? json_encode($requestData['general'][SaleInterface::IMAGE_PATH]) : null;

        $sale->setImagePath($image);

        try {
            $sale = $this->saleRepository->save($sale);
            $this->processRedirectAfterSuccessSave($resultRedirect, $sale->getId());
            $this->messageManager->addSuccessMessage(__('Sale was saved.'));
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage(__('Error. Cannot save'));
            $resultRedirect->setPath('*/*/new');
        }

        $this->_eventManager->dispatch('elogic_sale_save', ['sale' => $sale, 'is_new' => $isNew]);

        return $resultRedirect;
    }

    /**
     * @param  \Magento\Framework\Controller\Result\Redirect  $resultRedirect
     * @param  string  $id
     *
     * @return void
     */
    private function processRedirectAfterSuccessSave(Redirect $resultRedirect, string $id)
    {
        if ($this->getRequest()->getParam('back')) {
            $resultRedirect->setPath(
                '*/*/edit',
                [
                    SaleInterface::SALE_ID => $id,
                    '_current'             => true,
                ]
            );
        } elseif ($this->getRequest()->getParam('redirect_to_new')) {
            $resultRedirect->setPath(
                '*/*/new',
                [
                    '_current' => true,
                ]
            );
        } else {
            $resultRedirect->setPath('*/*/');
        }
    }
}
