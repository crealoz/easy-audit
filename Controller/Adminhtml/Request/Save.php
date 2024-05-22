<?php
namespace Crealoz\EasyAudit\Controller\Adminhtml\Request;

use Magento\Backend\App\Action;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;

class Save extends Action
{
    protected $dataPersistor;

    public function __construct(
        Action\Context $context,
        DataPersistorInterface $dataPersistor
    ) {
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context);
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            try {
                // Save audit request in the database
                // You will need to create a model and resource model for the audit request table

                $this->messageManager->addSuccessMessage(__('You saved the audit request.'));
                $this->dataPersistor->clear('easyaudit_audit');
                return $this->resultRedirectFactory->create()->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the audit request.'));
            }

            $this->dataPersistor->set('easyaudit_audit', $data);
            return $this->resultRedirectFactory->create()->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }
}
