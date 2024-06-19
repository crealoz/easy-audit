<?php
namespace Crealoz\EasyAudit\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

/**
 * @author Christophe Ferreboeuf <christophe@crealoz.fr>
 */
class Index extends \Magento\Backend\App\Action implements \Magento\Framework\App\Action\HttpGetActionInterface
{
    const ADMIN_RESOURCE = 'Crealoz_EasyAudit::index';

    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->prepend(__('Easy Audit'));
        $resultPage->addBreadcrumb(__('Easy Audit'), __('Easy Audit'));
        $resultPage->addBreadcrumb(__('Manage Data'), __('Manage Data'));

        return $resultPage;
    }
}
